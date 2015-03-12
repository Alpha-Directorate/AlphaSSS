<?php
include 'bps-form.php';
include 'bps-search.php';

function bps_help ()
{
    $screen = get_current_screen ();

	$title_00 = __('Overview', 'bps');
	$content_00 = '
<p>'.
__('Configure your profile search form, then display it:', 'bps'). '
<ul>
<li>'. sprintf (__('In its Members Directory page, selecting the option %s', 'bps'), '<em>'. __('Add to Directory', 'bps'). '</em>'). '</li>
<li>'. sprintf (__('In a sidebar or widget area, using the widget %s', 'bps'), '<em>'. __('Profile Search', 'bps'). '</em>'). '</li>
<li>'. sprintf (__('In a post or page, using the shortcode %s (*)', 'bps'), '<strong>[bps_display form=ID]</strong>'). '</li>
<li>'. sprintf (__('Anywhere in your theme, using the PHP code %s (*)', 'bps'), "<strong>&lt;?php do_action ('bps_display_form', ID); ?&gt;</strong>"). '</li>
</ul>'.
__('(*) Replace ID with your actual form ID.', 'bps'). '
</p>';

	$title_01 = __('Form Fields', 'bps');
	$content_01 = '
<p>'.
__('Select the profile fields to show in your search form.', 'bps'). '
<ul>
<li>'. __('Customize the field label and description, or leave them empty to use the default', 'bps'). '</li>
<li>'. __('Tick <em>Range</em> to enable the <em>Value Range Search</em> for numeric fields, or the <em>Age Range Search</em> for date fields', 'bps'). '</li>
<li>'. __('To reorder the fields in the form, drag them up or down by the handle on the left', 'bps'). '</li>
<li>'. __('To remove a field from the form, click the [x] on the right', 'bps'). '</li>
</ul>'.
__('Please note:', 'bps'). '
<ul>
<li>'. __('To leave a label or description blank, enter a single dash (-) character', 'bps'). '</li>
<li>'. __('The <em>Age Range Search</em> option is mandatory for date fields', 'bps'). '</li>
<li>'. __('The <em>Value Range Search</em> works for numeric fields only', 'bps'). '</li>
<li>'. __('The <em>Value Range Search</em> is not supported for <em>Multi Select Box</em> and <em>Checkboxes</em> fields', 'bps'). '</li>
</ul>
</p>';

	$title_02 = __('Add to Directory', 'bps');
	$content_02 = '
<p>'.
__('Insert your search form in its Members Directory page.', 'bps'). '
<ul>
<li>'. __('Specify the optional form header', 'bps'). '</li>
<li>'. __('Enable the <em>Toggle Form</em> feature', 'bps'). '</li>
<li>'. __('Enter the text for the <em>Toggle Form</em> button', 'bps'). '</li>
</ul>'.
__('If you select <em>Add to Directory: No</em>, the above options are ignored.', 'bps'). '
</p>';

	$title_03 = __('Form Attributes', 'bps');
	$content_03 = '
<p>'.
__('Select your form’s <em>method</em> attribute.', 'bps'). '
<ul>
<li>'. __('POST: the form data are not visible in the URL and it’s not possible to bookmark the results page', 'bps'). '</li>
<li>'. __('GET: the form data are sent as URL variables and it’s possible to bookmark the results page', 'bps'). '</li>
</ul>'.
__('Select your form’s <em>action</em> attribute. The <em>action</em> attribute points to your form’s results page, that could be:', 'bps'). '
<ul>
<li>'. __('The BuddyPress Members Directory page', 'bps'). '</li>
<li>'. __('A custom Members Directory page', 'bps'). '</li>
</ul>'.
sprintf (__('You can create a custom Members Directory page using the shortcode %s, and you can even use a custom directory template.', 'bps'), '<strong>[bps_directory]</strong>'). ' '.
__('To learn more, read the <a href="http://dontdream.it/bp-profile-search/custom-directories/" target="_blank">Custom Directories</a> tutorial.', 'bps'). '
</p>';

	$title_04 = __('Text Search Mode', 'bps');
	$content_04 = '
<p>'.
__('Select your text search mode.', 'bps'). '
<ul>
<li>'. __('LIKE: a search for <em>John</em> finds <em>John</em>, <em>Johnson</em>, <em>Long John Silver</em>, and so on', 'bps'). '</li>
<li>'. __('SAME: a search for <em>John</em> finds <em>John</em> only', 'bps'). '</li>
</ul>'.
__('In both modes, two wildcard characters are available:', 'bps'). '
<ul>
<li>'. __('Percent sign (%): matches any text, or no text at all', 'bps'). '</li>
<li>'. __('Underscore (_): matches any single character', 'bps'). '</li>
</ul>
</p>';

	$sidebar = '
<p><strong>'. __('For more information:', 'bps'). '</strong></p>
<p><a href="http://dontdream.it/bp-profile-search/" target="_blank">'. __('Documentation', 'bps'). '</a></p>
<p><a href="http://dontdream.it/bp-profile-search/questions-and-answers/" target="_blank">'. __('Questions and Answers', 'bps'). '</a></p>
<p><a href="http://dontdream.it/bp-profile-search/incompatible-plugins/" target="_blank">'. __('Incompatible plugins', 'bps'). '</a></p>
<p><a href="http://dontdream.it/support/forum/bp-profile-search-forum/" target="_blank">'. __('Support Forum', 'bps'). '</a></p>
<br><br>';

	$screen->add_help_tab (array ('id' => 'bps_00', 'title' => $title_00, 'content' => $content_00));
	$screen->add_help_tab (array ('id' => 'bps_01', 'title' => $title_01, 'content' => $content_01));
	$screen->add_help_tab (array ('id' => 'bps_03', 'title' => $title_03, 'content' => $content_03));
	$screen->add_help_tab (array ('id' => 'bps_02', 'title' => $title_02, 'content' => $content_02));
	$screen->add_help_tab (array ('id' => 'bps_04', 'title' => $title_04, 'content' => $content_04));

	$screen->set_help_sidebar ($sidebar);

	return true;
}

function bps_admin_js ()
{
	$translations = array (
		'field' => __('field', 'bps'),
		'label' => __('label', 'bps'),
		'description' => __('description', 'bps'),
		'range' => __('Range', 'bps'),
	);
	wp_enqueue_script ('bps-admin', plugins_url ('bps-admin.js', __FILE__), array ('jquery-ui-sortable'), BPS_VERSION);
	wp_localize_script ('bps-admin', 'bps_strings', $translations);
}

function bps_update_fields ()
{
	$bps_options = array ();

	list ($x, $fields) = bps_get_fields ();

	$bps_options['field_name'] = array ();
	$bps_options['field_label'] = array ();
	$bps_options['field_desc'] = array ();
	$bps_options['field_range'] = array ();

	$j = 0;
	$posted = isset ($_POST['bps_options'])? $_POST['bps_options']: array ();
	if (isset ($posted['field_name']))  foreach ($posted['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$field = $fields[$id];
		$field_type = apply_filters ('bps_field_validation_type', $field->type, $field);
		$label = stripslashes ($posted['field_label'][$k]);
		$desc = stripslashes ($posted['field_desc'][$k]);

		$bps_options['field_name'][$j] = $id;
		$bps_options['field_label'][$j] = $l = $label;
		$bps_options['field_desc'][$j] = $d = $desc;
		$bps_options['field_range'][$j] = $r = isset ($posted['field_range'][$k]);

		if (bps_custom_field ($field_type))
		{
			list ($l, $d, $r) = apply_filters ('bps_field_validation', array ($l, $d, $r), $field);
			$bps_options['field_label'][$j] = $l;
			$bps_options['field_desc'][$j] = $d;
			$bps_options['field_range'][$j] = $r;
		}
		else
		{
			if ($field_type == 'datebox')  $bps_options['field_range'][$j] = true;
			if ($field_type == 'checkbox' || $field_type == 'multiselectbox')  $bps_options['field_range'][$j] = false;
		}

		if ($bps_options['field_range'][$j] == false)  $bps_options['field_range'][$j] = null;
		$j = $j + 1;
	}

	return $bps_options;
}

function bps_form_fields ($post)
{
	$bps_options = bps_options ($post->ID);

	list ($groups, $fields) = bps_get_fields ();
	echo '<script>var bps_groups = ['. json_encode ($groups). '];</script>';
?>

	<div id="field_box" class="field_box">
<?php

	foreach ($bps_options['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$field = $fields[$id];
		$label = esc_attr ($bps_options['field_label'][$k]);
		$default = esc_attr ($field->name);
		$showlabel = empty ($label)? "placeholder=\"$default\"": "value=\"$label\"";
		$desc = esc_attr ($bps_options['field_desc'][$k]);
		$default = esc_attr ($field->description);
		$showdesc = empty ($desc)? "placeholder=\"$default\"": "value=\"$desc\"";
?>

		<p id="field_div<?php echo $k; ?>" class="sortable">
			<span>&nbsp;&Xi; </span>
<?php
			bps_profile_fields ("bps_options[field_name][$k]", "field_name$k", $id);
?>
			<input type="text" name="bps_options[field_label][<?php echo $k; ?>]" id="field_label<?php echo $k; ?>" <?php echo $showlabel; ?> style="width: 16%" />
			<input type="text" name="bps_options[field_desc][<?php echo $k; ?>]" id="field_desc<?php echo $k; ?>" <?php echo $showdesc; ?> style="width: 32%" />
			<label><input type="checkbox" name="bps_options[field_range][<?php echo $k; ?>]" id="field_range<?php echo $k; ?>" value="<?php echo $k; ?>"<?php if (isset ($bps_options['field_range'][$k])) echo ' checked="checked"'; ?> /><?php _e('Range', 'bps'); ?> </label>
			<a href="javascript:hide('field_div<?php echo $k; ?>')" class="delete">[x]</a>
		</p>
<?php
	}
?>
		<input type="hidden" id="field_next" value="<?php echo count ($bps_options['field_name']); ?>" />
	</div>
	<p><a href="javascript:add_field()"><?php _e('Add Field', 'bps'); ?></a></p>
<?php
}

function bps_profile_fields ($name, $id, $value)
{
	list ($groups, $x) = bps_get_fields ();

	echo "<select name='$name' id='$id'>\n";
	foreach ($groups as $group => $fields)
	{
		$group = esc_attr ($group);
		echo "<optgroup label='$group'>\n";
		foreach ($fields as $field)
		{
			$selected = $field['id'] == $value? " selected='selected'": '';
			echo "<option value='$field[id]'$selected>$field[name]</option>\n";
		}
		echo "</optgroup>\n";
	}
	echo "</select>\n";

	return true;
}

function bps_get_fields ()
{
	global $group, $field;

	static $groups = array ();
	static $fields = array ();

	if (count ($groups))  return array ($groups, $fields);

	if (!function_exists ('bp_has_profile'))
	{
		printf ('<p class="bps_error">'. __('%s: The BuddyPress Extended Profiles component is not active.', 'bps'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>');
		return array ($groups, $fields);
	}

	if (bp_has_profile ('hide_empty_fields=0'))
	{
		while (bp_profile_groups ())
		{
			bp_the_profile_group (); 
			$group->name = str_replace ('&amp;', '&', stripslashes ($group->name));
			$groups[$group->name] = array ();

			while (bp_profile_fields ())
			{
				bp_the_profile_field ();
				$field->name = str_replace ('&amp;', '&', stripslashes ($field->name));
				$field->description = str_replace ('&amp;', '&', stripslashes ($field->description));
				$groups[$group->name][] = array ('id' => $field->id, 'name' => $field->name);
				$fields[$field->id] = $field;
			}
		}
	}

	list ($groups, $fields) = apply_filters ('bps_get_fields', array ($groups, $fields));
	return array ($groups, $fields);
}

function bps_custom_field ($type)
{
	return !in_array ($type, array ('textbox', 'number', 'textarea', 'selectbox', 'multiselectbox', 'radio', 'checkbox', 'datebox'));
}

function bps_get_widget ($form)
{
	$widgets = get_option ('widget_bps_widget');
	if ($widgets == false)  return __('unused', 'bps');

	$titles = array ();
	foreach ($widgets as $key => $widget)
		if (isset ($widget['form']) && $widget['form'] == $form)  $titles[] = !empty ($widget['title'])? $widget['title']: __('(no title)');
		
	return count ($titles)? implode ('<br/>', $titles): __('unused', 'bps');
}

function bps_get_options ($id)
{
	static $options = array ();

	if (isset ($options[$id]))  return $options[$id];

	$field = new BP_XProfile_Field ($id);
	if (empty ($field->id))  return array ();

	$options[$id] = array ();
	$rows = $field->get_children ();
	if (is_array ($rows))
		foreach ($rows as $row)
			$options[$id][] = $row->name;

	return $options[$id];
}
