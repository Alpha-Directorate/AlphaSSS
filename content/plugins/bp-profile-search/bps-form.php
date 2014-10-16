<?php

add_action ('bp_before_directory_members_tabs', 'bps_add_form');
function bps_add_form ()
{
	$args = array (
		'post_type' => 'bps_form',
		'orderby' => 'ID',
		'order' => 'ASC',
		'nopaging' => true,
		'meta_query' => array (
			array ('key' => 'bps_options', 'compare' => 'LIKE', 'value' => 's:9:"directory";s:3:"Yes";')
		)
	);
	$posts = get_posts ($args);

	foreach ($posts as $post)  bps_display_form ($post->ID, 'bps_auto');
}

add_action ('bps_display_form', 'bps_display_form');
function bps_display_form ($form, $mode='bps_action')
{
	if (!function_exists ('bp_has_profile'))
	{
		printf ('<p class="bps_error">'. __('%s: The BuddyPress Extended Profiles component is not active.', 'bps'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>');
		return false;
	}

	$bps_options = bps_options ($form);
	if (empty ($bps_options['field_name']))
	{
		printf ('<p class="bps_error">'. __('%s: Form %d was not found, or has no fields.', 'bps'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>', $form);
		return false;
	}

	$action = bp_get_root_domain (). '/'. bp_get_members_root_slug (). '/';

echo "\n<!-- BP Profile Search ". BPS_VERSION. " - start -->\n";
if ($mode != 'bps_auto')  echo "<div id='buddypress'>";

	if ($mode == 'bps_auto')
	{
?>
	<div class="item-list-tabs bps_header">
	<ul>
	<li><?php echo $bps_options['header']; ?></li>
<?php if ($bps_options['toggle'] == 'Enabled') { ?>
	<li class="last">
	<input id="bps_toggle<?php echo $form; ?>" type="submit" value="<?php echo $bps_options['button']; ?>" />
	</li>
<?php } ?>
	</ul>
<?php if ($bps_options['toggle'] == 'Enabled') { ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#<?php echo "$mode$form"; ?>').hide();
		$('#bps_toggle<?php echo $form; ?>').click(function(){
			$('#<?php echo "$mode$form"; ?>').toggle();
		});
	});
</script>
<?php } ?>
	</div>
<?php
	}

	list ($x, $fields) = bps_get_fields ();

echo "<form action='$action' method='$bps_options[method]' id='$mode$form' class='standard-form'>";

	$j = 0;
	foreach ($bps_options['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$field = $fields[$id];
		$field_type = apply_filters ('bps_field_html_type', $field->type, $field);

		$label = $bps_options['field_label'][$k];
		$desc = $bps_options['field_desc'][$k];
		$range = isset ($bps_options['field_range'][$k]);

		$fname = 'field_'. $id;
		$name = sanitize_title ($field->name);
		$alt = ($j++ % 2)? ' alt': '';

echo "<div class='editfield field_$id field_$name$alt'>";

		if (empty ($label))
			$label = $field->name;
		else
echo "<input type='hidden' name='label_$id' value='$label' />";

		if (empty ($desc))
			$desc = $field->description;

		if (bps_custom_field ($field_type))
		{
			$output = "<p>Your HTML code for the <em>$field_type</em> field type goes here</p>";
			$output = apply_filters ('bps_field_html', $output, $field, $label, $range);
echo $output;
		}
		else if ($range)
		{
			list ($min, $max) = bps_minmax ($_REQUEST, $id, $field_type);

echo "<label for='$fname'>$label</label>";
echo "<input style='width: 10%;' type='text' name='{$fname}_min' id='$fname' value='$min' />";
echo '&nbsp;-&nbsp;';
echo "<input style='width: 10%;' type='text' name='{$fname}_max' value='$max' />";
		}
		else switch ($field_type)
		{
		case 'textbox':
			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: '';
			$value = esc_attr (stripslashes ($posted));
echo "<label for='$fname'>$label</label>";
echo "<input type='text' name='$fname' id='$fname' value='$value' />";
			break;

		case 'number':
			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: '';
			$value = esc_attr (stripslashes ($posted));
echo "<label for='$fname'>$label</label>";
echo "<input type='number' name='$fname' id='$fname' value='$value' />";
			break;

		case 'textarea':
			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: '';
			$value = esc_textarea (stripslashes ($posted));
echo "<label for='$fname'>$label</label>";
echo "<textarea rows='5' cols='40' name='$fname' id='$fname'>$value</textarea>";
			break;

		case 'selectbox':
echo "<label for='$fname'>$label</label>";
echo "<select name='$fname' id='$fname'>";
			$selectall = apply_filters ('bps_select_all', '', $field);
			if (is_string ($selectall))
echo "<option value='$selectall'></option>";

			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: '';
			$options = bps_get_options ($id);
			foreach ($options as $option)
			{
				$option = trim ($option);
				$value = esc_attr (stripslashes ($option));
				$selected = ($option == $posted)? "selected='selected'": "";
echo "<option $selected value='$value'>$value</option>";
			}
echo "</select>";
			break;

		case 'multiselectbox':
echo "<label for='$fname'>$label</label>";
echo "<select name='{$fname}[]' id='$fname' multiple='multiple'>";

			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: array ();
			$options = bps_get_options ($id);
			foreach ($options as $option)
			{
				$option = trim ($option);
				$value = esc_attr (stripslashes ($option));
				$selected = (in_array ($option, $posted))? "selected='selected'": "";
echo "<option $selected value='$value'>$value</option>";
			}
echo "</select>";
			break;

		case 'radio':
echo "<div class='radio'>";
echo "<span class='label'>$label</span>";
echo "<div id='$fname'>";

			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: '';
			$options = bps_get_options ($id);
			foreach ($options as $option)
			{
				$option = trim ($option);
				$value = esc_attr (stripslashes ($option));
				$selected = ($option == $posted)? "checked='checked'": "";
echo "<label><input $selected type='radio' name='$fname' value='$value'>$value</label>";
			}
echo '</div>';
echo "<a class='clear-value' href='javascript:clear(\"$fname\");'>". __('Clear', 'buddypress'). "</a>";
echo '</div>';
			break;

		case 'checkbox':
echo "<div class='checkbox'>";
echo "<span class='label'>$label</span>";

			$posted = isset ($_REQUEST[$fname])? $_REQUEST[$fname]: array ();
			$options = bps_get_options ($id);
			foreach ($options as $option)
			{
				$option = trim ($option);
				$value = esc_attr (stripslashes ($option));
				$selected = (in_array ($option, $posted))? "checked='checked'": "";
echo "<label><input $selected type='checkbox' name='{$fname}[]' value='$value'>$value</label>";
			}
echo '</div>';
			break;
		}

	if ($desc != '-')
echo "<p class='description'>$desc</p>";
echo '</div>';
	}

echo "<div class='submit'>";
echo "<input type='submit' value='". __('Search', 'buddypress'). "' />";
echo '</div>';
	if ($bps_options['searchmode'] == 'LIKE')
echo "<input type='hidden' name='options[]' value='like' />";
echo "<input type='hidden' name='bp_profile_search' value='$form' />";
echo '</form>';
if ($mode != 'bps_auto')  echo '</div>';
echo "\n<!-- BP Profile Search ". BPS_VERSION. " - end -->\n";

	return true;
}

add_shortcode ('bps_display', 'bps_shortcode');
function bps_shortcode ($attr, $content)
{
	ob_start ();
	bps_display_form ($attr['form'], 'bps_shortcode');
	return ob_get_clean ();
}

class bps_widget extends WP_Widget
{
	function bps_widget ()
	{
		$widget_ops = array ('description' => __('A Profile Search form.', 'bps'));
		$this->WP_Widget ('bps_widget', __('Profile Search', 'bps'), $widget_ops);
	}

	function widget ($args, $instance)
	{
		extract ($args);
		$title = apply_filters ('widget_title', $instance['title']);
		$form = $instance['form'];

		echo $before_widget;
		if ($title)
			echo $before_title. $title. $after_title;
		bps_display_form ($form, 'bps_widget');
		echo $after_widget;
	}

	function update ($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['form'] = $new_instance['form'];
		return $instance;
	}

	function form ($instance)
	{
		$title = isset ($instance['title'])? $instance['title']: '';
		$form = isset ($instance['form'])? $instance['form']: '';
?>
	<p>
		<label for="<?php echo $this->get_field_id ('title'); ?>"><?php _e('Title:', 'bps'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id ('title'); ?>" name="<?php echo $this->get_field_name ('title'); ?>" type="text" value="<?php echo esc_attr ($title); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id ('form'); ?>"><?php _e('Form:', 'bps'); ?></label>
<?php
		$posts = get_posts (array ('post_type' => 'bps_form', 'orderby' => 'ID', 'order' => 'ASC', 'nopaging' => true));
		if (count ($posts))
		{
			echo "<select class='widefat' id='{$this->get_field_id ('form')}' name='{$this->get_field_name ('form')}'>";
			foreach ($posts as $post)
			{
				$id = $post->ID;
				$name = !empty ($post->post_title)? $post->post_title: __('(no title)');
				echo "<option value='$id'";
				if ($id == $form)  echo " selected='selected'";
				echo ">$name &nbsp;</option>\n";
			}
			echo "</select>";
		}
		else
		{
			echo '<br/>';
			_e('You have not created any form yet.', 'bps');
		}
?>
	</p>
<?php
	}
}

add_action ('widgets_init', 'bps_widget_init');
function bps_widget_init ()
{
	register_widget ('bps_widget');
}
?>
