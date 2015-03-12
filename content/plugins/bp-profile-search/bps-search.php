<?php

add_action ('wp', 'bps_set_cookie');
function bps_set_cookie ()
{
	if (isset ($_REQUEST['bp_profile_search']))
		setcookie ('bps_request', serialize ($_REQUEST), 0, COOKIEPATH);
	else if (isset ($_COOKIE['bps_request']))
		setcookie ('bps_request', '', 0, COOKIEPATH);
}

function bps_get_request ()
{
	if (isset ($_REQUEST['bp_profile_search']))
		$request = $_REQUEST;
	else if (isset ($_COOKIE['bps_request']) && defined ('DOING_AJAX'))
		$request = unserialize (stripslashes ($_COOKIE['bps_request']));
	else
		$request = array ();

	return apply_filters ('bps_request', $request);
}

function bps_minmax ($request, $id, $type)
{
	$min = (isset ($request["field_{$id}_min"]) && is_numeric (trim ($request["field_{$id}_min"])))? trim ($request["field_{$id}_min"]): '';
	$max = (isset ($request["field_{$id}_max"]) && is_numeric (trim ($request["field_{$id}_max"])))? trim ($request["field_{$id}_max"]): '';

	if ($type == 'datebox')
	{
		if (is_numeric ($min))  $min = (int)$min;
		if (is_numeric ($max))  $max = (int)$max;
	}

	return array ($min, $max);
}

add_action ('bp_before_directory_members_content', 'bps_filters');
function bps_filters ()
{
	$request = bps_get_request ();
	if (empty ($request))  return false;

	$done = array ();
	$filters = '';
	$action = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

	list ($x, $fields) = bps_get_fields ();
	foreach ($request as $key => $value)
	{
		if ($value === '')  continue;

		$split = explode ('_', $key);
		if ($split[0] != 'field')  continue;

		$id = $split[1];
		$op = isset ($split[2])? $split[2]: 'eq';
		if (isset ($done[$id]) || empty ($fields[$id]))  continue;
	
		$field = $fields[$id];
		$field_type = apply_filters ('bps_field_criteria_type', $field->type, $field);
		$field_label = isset ($request['label_'. $id])? $request['label_'. $id]: $field->name;

		if (bps_custom_field ($field_type))
		{
			$output = "The search criteria for the <em>$field_type</em> field type go here<br/>\n";
			$output = apply_filters ('bps_field_criteria', $output, $field, $key, $value, $field_label);
			$filters .= $output;
		}
		else if ($op == 'min' || $op == 'max')
		{
			if ($field_type == 'multiselectbox' || $field_type == 'checkbox')  continue;

			list ($min, $max) = bps_minmax ($request, $id, $field_type);
			if ($min === '' && $max === '')  continue;

			$filters .= "<strong>$field_label:</strong>";
			if ($min !== '')
				$filters .= " <strong>". __('min', 'bps'). "</strong> $min";
			if ($max !== '')
				$filters .= " <strong>". __('max', 'bps'). "</strong> $max";
			$filters .= "<br/>\n";
		}
		else if ($op == 'eq')
		{
			if ($field_type == 'datebox')  continue;

			switch ($field_type)
			{
			case 'textbox':
			case 'number':
			case 'textarea':
			case 'selectbox':
			case 'radio':
				$filters .= "<strong>$field_label:</strong> ". esc_html (stripslashes ($value)). "<br/>\n";
				break;

			case 'multiselectbox':
			case 'checkbox':
				$values = $value;
				$filters .= "<strong>$field_label:</strong> ". esc_html (implode (', ', stripslashes_deep ($values))). "<br/>\n";
				break;
			}
		}
		else continue;

		$done [$id] = true;
	}

	if (count ($done) == 0)  return false;

	echo "\n";
	echo "<p class='bps_filters'>\n". $filters;
	echo "<a href='$action'>". __('Clear', 'buddypress'). "</a><br/>\n";
	echo "</p>\n";

	return true;
}

function bps_search ($request)
{
	global $bp, $wpdb;

	$done = array ();
	$results = array ('users' => array (0), 'validated' => true);

	list ($x, $fields) = bps_get_fields ();
	foreach ($request as $key => $value)
	{
		if ($value === '')  continue;

		$split = explode ('_', $key);
		if ($split[0] != 'field')  continue;

		$id = $split[1];
		$op = isset ($split[2])? $split[2]: 'eq';
		if (isset ($done[$id]) || empty ($fields[$id]))  continue;

		$field = $fields[$id];
		$field_type = apply_filters ('bps_field_query_type', $field->type, $field);

		if (bps_custom_field ($field_type))
		{
			$found = apply_filters ('bps_field_query', array (), $field, $key, $value);
		}
		else
		{
			$sql = $wpdb->prepare ("SELECT user_id FROM {$bp->profile->table_name_data} WHERE field_id = %d ", $id);
			$sql = apply_filters ('bps_field_sql', $sql, $field);

			if ($op == 'min' || $op == 'max')
			{
				if ($field_type == 'multiselectbox' || $field_type == 'checkbox')  continue;

				list ($min, $max) = bps_minmax ($request, $id, $field_type);
				if ($min === '' && $max === '')  continue;

				switch ($field_type)
				{
				case 'textbox':
				case 'number':
				case 'textarea':
				case 'selectbox':
				case 'radio':
					if ($min !== '')  $sql .= $wpdb->prepare ("AND value >= %f", $min);
					if ($max !== '')  $sql .= $wpdb->prepare ("AND value <= %f", $max);
					break;

				case 'datebox':
					$time = time ();
					$day = date ("j", $time);
					$month = date ("n", $time);
					$year = date ("Y", $time);
					$ymin = $year - $max - 1;
					$ymax = $year - $min;

					if ($max !== '')  $sql .= $wpdb->prepare ("AND DATE(value) > %s", "$ymin-$month-$day");
					if ($min !== '')  $sql .= $wpdb->prepare ("AND DATE(value) <= %s", "$ymax-$month-$day");
					break;
				}
			}
			else if ($op == 'eq')
			{
				if ($field_type == 'datebox')  continue;

				switch ($field_type)
				{
				case 'textbox':
				case 'textarea':
					$value = str_replace ('&', '&amp;', $value);
					$escaped = '%'. bps_esc_like ($value). '%';
					if (isset ($request['options']) && in_array ('like', $request['options']))
						$sql .= $wpdb->prepare ("AND value LIKE %s", $escaped);
					else
						$sql .= $wpdb->prepare ("AND value LIKE %s", $value);
					break;

				case 'number':
					$sql .= $wpdb->prepare ("AND value = %d", $value);
					break;

				case 'selectbox':
				case 'radio':
					$value = str_replace ('&', '&amp;', $value);
					$sql .= $wpdb->prepare ("AND value = %s", $value);
					break;

				case 'multiselectbox':
				case 'checkbox':
					$values = $value;
					$like = array ();
					foreach ($values as $value)
					{
						$value = str_replace ('&', '&amp;', $value);
						$escaped = '%'. bps_esc_like ($value). '%';
						$like[] = $wpdb->prepare ("value = %s OR value LIKE %s", $value, $escaped);
					}
					$sql .= 'AND ('. implode (' OR ', $like). ')';
					break;
				}
			}
			else continue;

			$found = $wpdb->get_col ($sql);
		}

		$users = isset ($users)? array_intersect ($users, $found): $found;
		if (count ($users) == 0)  return $results;

		$done [$id] = true;
	}

	if (count ($done) == 0)
	{
		$results['validated'] = false;
		return $results;
	}

	$results['users'] = $users;
	return $results;
}

add_action ('bp_ajax_querystring', 'bps_filter_members', 30, 2);
function bps_filter_members ($qs=false, $object=false)
{
	if ($object != 'members')  return $qs;

	$request = bps_get_request ();
	if (empty ($request))  return $qs;

	$bps_results = bps_search ($request);
	if ($bps_results['validated'])
	{
		$args = wp_parse_args ($qs);
		$users = $bps_results['users'];

		if (isset ($args['include']))
		{
			$included = explode (',', $args['include']);
			$users = array_intersect ($users, $included);
			if (count ($users) == 0)  $users = array (0);
		}

		$args['include'] = implode (',', $users);
		$qs = build_query ($args);
	}

	return $qs;
}

function bps_esc_like ($text)
{
    return addcslashes ($text, '_%\\');
}
