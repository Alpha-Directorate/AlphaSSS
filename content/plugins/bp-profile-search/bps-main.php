<?php
/*
Plugin Name: BP Profile Search
Plugin URI: http://www.dontdream.it/bp-profile-search/
Description: Search your BuddyPress Members Directory.
Version: 4.0.3
Author: Andrea Tarantini
Author URI: http://www.dontdream.it/
Text Domain: bps
Domain Path: /languages
*/

define ('BPS_VERSION', '4.0.3');
include 'bps-functions.php';

$addons = array ('bps-custom.php');
foreach ($addons as $addon)
{
	$file = WP_PLUGIN_DIR. '/bp-profile-search-addons/'. $addon;
	if (file_exists ($file))  include $file;
}

add_action ('plugins_loaded', 'bps_translate');
function bps_translate ()
{
	load_plugin_textdomain ('bps', false, basename (dirname (__FILE__)). '/languages');
}

add_filter ('plugin_action_links', 'bps_row_meta', 10, 2);
function bps_row_meta ($links, $file)
{
	if ($file == plugin_basename (__FILE__))
	{
		$settings_link = '<a href="'. admin_url ('edit.php?post_type=bps_form'). '">'. __('Settings', 'buddypress'). '</a>';
		array_unshift ($links, $settings_link);
	}
	return $links;
}

function bps_options ($form)
{
	static $options;
	if (isset ($options[$form]))  return $options[$form];

	$default = array ();
	$default['field_name'] = array ();
	$default['field_label'] = array ();
	$default['field_desc'] = array ();
	$default['field_range'] = array ();
	$default['directory'] = 'No';
	$default['header'] = __('<h4>Advanced Search</h4>', 'bps');
	$default['toggle'] = 'Enabled';
	$default['button'] = __('Toggle Form', 'bps');
	$default['method'] = 'POST';
	$default['searchmode'] = 'LIKE';

	if (get_post_status ($form) == 'publish')  $meta = get_post_meta ($form);

	$options[$form] = isset ($meta['bps_options'])? unserialize ($meta['bps_options'][0]): $default;
	return $options[$form];
}

add_action ('init', 'bps_post_type');
function bps_post_type ()
{
	$args = array
	(
		'labels' => array
		(
			'name' => __('Profile Search Forms', 'bps'),
			'singular_name' => __('Profile Search Form', 'bps'),
			'all_items' => __('Profile Search', 'bps'),
			'add_new' => __('Add New', 'bps'),
			'add_new_item' => __('Add New Form', 'bps'),
			'edit_item' => __('Edit Form', 'bps'),
			'not_found' => __('No forms found.', 'bps'),
			'not_found_in_trash' => __('No forms found in Trash.', 'bps'),
		),
		'show_ui' => true,
		'show_in_menu' => 'users.php',
		'supports' => array ('title'),
		'rewrite' => false,
		'query_var' => false,
	);

	register_post_type ('bps_form', $args);
}

/******* edit.php */

add_filter ('manage_bps_form_posts_columns', 'bps_add_columns');
// file class-wp-posts-list-table.php
function bps_add_columns ($columns)
{
	return array
	(
		'cb' => '<input type="checkbox" />',
		'title' => __('Form', 'bps'),
		'fields' => __('Fields', 'bps'),
		'directory' => __('Add to Directory', 'bps'),
		'widget' => __('Widget', 'bps'),
		'shortcode' => __('Shortcode', 'bps'),
	);
}

add_action ('manage_posts_custom_column', 'bps_columns', 10, 2);
// file class-wp-posts-list-table.php line 675
function bps_columns ($column, $post_id)
{
	if (!bps_form ())  return;

	$options = bps_options ($post_id);
	if ($column == 'fields')  echo count ($options['field_name']);
	else if ($column == 'directory')  _e($options['directory'], 'bps');
	else if ($column == 'widget')  echo bps_get_widget ($post_id);
	else if ($column == 'shortcode')  echo "[bps_display form=$post_id]";
}

add_filter ('bulk_actions-edit-bps_form', 'bps_bulk_actions');
// file class-wp-list-table.php
function bps_bulk_actions ($actions)
{
	$actions = array ();
	$actions['trash'] = __('Move to Trash');
	$actions['untrash'] = __('Restore');
	$actions['delete'] = __('Delete Permanently');

	return $actions;
}

add_filter ('post_row_actions', 'bps_row_actions', 10, 2);
// file class-wp-posts-list-table.php
function bps_row_actions ($actions, $post)
{
	if (!bps_form ())  return $actions;

	unset ($actions['inline hide-if-no-js']);
	return $actions;
}

add_filter ('manage_edit-bps_form_sortable_columns', 'bps_sortable');
// file class-wp-list-table.php
function bps_sortable ($columns)
{
	return array ('title' => 'title');
}

add_filter ('request', 'bps_orderby');
function bps_orderby ($vars)
{
	if (!bps_form ())  return $vars;
	if (isset ($vars['orderby']))  return $vars;
	
	$vars['orderby'] = 'ID';
	$vars['order'] = 'ASC';
	return $vars;
}

/******* post.php, post-new.php */

add_action ('add_meta_boxes', 'bps_add_meta_boxes');
function bps_add_meta_boxes ()
{
	add_meta_box ('bps_form_fields', __('Form Fields', 'bps'), 'bps_form_fields', 'bps_form', 'normal');
	add_meta_box ('bps_directory', __('Add to Directory', 'bps'), 'bps_directory', 'bps_form', 'side');
	add_meta_box ('bps_method', __('Form Method', 'bps'), 'bps_method', 'bps_form', 'side');
	add_meta_box ('bps_searchmode', __('Text Search Mode', 'bps'), 'bps_searchmode', 'bps_form', 'side');
}

function bps_directory ($post)
{
	$options = bps_options ($post->ID);
?>
	<label for="directory"><?php _e('Add to Directory', 'bps'); ?></label><br/>
	<select name="options[directory]" id="directory">
		<option value='Yes' <?php selected ($options['directory'], 'Yes'); ?>><?php _e('Yes', 'bps'); ?></option>
		<option value='No' <?php selected ($options['directory'], 'No'); ?>><?php _e('No', 'bps'); ?></option>
	</select>
	<br/>
	<label for="header"><?php _e('Form Header', 'bps'); ?></label><br/>
	<textarea name="options[header]" id="header" class="large-text code" rows="4"><?php echo $options['header']; ?></textarea>
	<br/>
	<label for="toggle"><?php _e('Toggle Form', 'bps'); ?></label><br/>
	<select name="options[toggle]" id="toggle">
		<option value='Enabled' <?php selected ($options['toggle'], 'Enabled'); ?>><?php _e('Enabled', 'bps'); ?></option>
		<option value='Disabled' <?php selected ($options['toggle'], 'Disabled'); ?>><?php _e('Disabled', 'bps'); ?></option>
	</select>
	<br/>
	<label for="button"><?php _e('Toggle Form Button', 'bps'); ?></label><br/>
	<input type="text" name="options[button]" id="button" value="<?php echo $options['button']; ?>" />
<?php
}

function bps_method ($post)
{
	$options = bps_options ($post->ID);
?>
	<select name="options[method]" id="method">
		<option value='POST' <?php selected ($options['method'], 'POST'); ?>><?php _e('POST', 'bps'); ?></option>
		<option value='GET' <?php selected ($options['method'], 'GET'); ?>><?php _e('GET', 'bps'); ?></option>
	</select>
<?php
}

function bps_searchmode ($post)
{
	$options = bps_options ($post->ID);
?>
	<select name="options[searchmode]" id="searchmode">
		<option value='LIKE' <?php selected ($options['searchmode'], 'LIKE'); ?>><?php _e('LIKE', 'bps'); ?></option>
		<option value='EQUAL' <?php selected ($options['searchmode'], 'EQUAL'); ?>><?php _e('SAME', 'bps'); ?></option>
	</select>
<?php
}

add_action ('save_post', 'bps_save_post', 10, 2);
function bps_save_post ($post_id, $post)
{
	if ($post->post_type != 'bps_form')  return false;
	if ($post->post_status != 'publish')  return false;
	if (empty ($_POST['options']) && empty ($_POST['bps_options']))  return false;

	$options = bps_update_fields ();
	foreach (array ('directory', 'header', 'toggle', 'button', 'method', 'searchmode') as $key)
		$options[$key] = stripslashes ($_POST['options'][$key]);

	update_post_meta ($post_id, 'bps_options', $options);
	return true;
}

add_filter ('post_updated_messages', 'bps_updated_messages');
function bps_updated_messages ($messages)
{
	$messages['bps_form'] = array
	(
		 0 => 'message 0',
		 1 => __('Form updated.', 'bps'),
		 2 => 'message 2',
		 3 => 'message 3',
		 4 => 'message 4',
		 5 => 'message 5',
		 6 => __('Form created.', 'bps'),
		 7 => 'message 7',
		 8 => 'message 8',
		 9 => 'message 9',
		10 => 'message 10',
	);
	return $messages;
}

add_filter ('bulk_post_updated_messages', 'bps_bulk_updated_messages', 10, 2);
function bps_bulk_updated_messages ($bulk_messages, $bulk_counts)
{
	$bulk_messages['bps_form'] = array
	(
		'updated'   => 'updated',
		'locked'    => 'locked',
		'deleted'   => _n('%s form permanently deleted.', '%s forms permanently deleted.', $bulk_counts['deleted'], 'bps'),
		'trashed'   => _n('%s form moved to the Trash.', '%s forms moved to the Trash.', $bulk_counts['trashed'], 'bps'),
		'untrashed' => _n('%s form restored from the Trash.', '%s forms restored from the Trash.', $bulk_counts['untrashed'], 'bps'),
	);
	return $bulk_messages;
}

/******* common */

function bps_form ()
{
	global $current_screen;
	return isset ($current_screen->post_type) && $current_screen->post_type == 'bps_form';
}

add_action ('admin_head', 'bps_css');
function bps_css ()
{
	global $current_screen;
	if (!bps_form ())  return;

	bps_help ();
	if ($current_screen->id == 'bps_form')  bps_admin_js ();
?>
	<style type="text/css">
		.search-box, .actions, .view-switch {display: none;}
		.bulkactions {display: block;}
		#minor-publishing {display: none;}
		.fixed .column-fields {width: 10%;}
		.fixed .column-directory {width: 16%;}
		.fixed .column-widget {width: 16%;}
		.fixed .column-shortcode {width: 20%;}
	</style>
<?php
}
?>
