<?php
/*
Plugin Name: BP Profile Search
Plugin URI: http://www.dontdream.it/bp-profile-search/
Description: Search your BuddyPress Members Directory.
Version: 4.1
Author: Andrea Tarantini
Author URI: http://www.dontdream.it/
Text Domain: bps
Domain Path: /languages
*/

define ('BPS_VERSION', '4.1');
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

register_activation_hook (__FILE__, 'bps_activate');
function bps_activate ()
{
	bps_upgrade41 ();
}

function bps_upgrade41 ()
{
	$posts = get_posts (array ('post_type' => 'bps_form', 'nopaging' => true));
	foreach ($posts as $post)
	{
		$id = $post->ID;
		$options = bps_options ($id);
		if (!isset ($options['action']))
		{
			$options['action'] = 0;
			update_post_meta ($id, 'bps_options', $options);
		}
	}
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
	$default['action'] = 0;
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
		'action' => __('Directory', 'bps'),
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
	else if ($column == 'action')  echo $options['action']? get_the_title ($options['action']): '<strong style="color:red;">'. __('undefined', 'bps'). '</strong>';
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
	add_meta_box ('bps_attributes', __('Form Attributes', 'bps'), 'bps_attributes', 'bps_form', 'side');
	add_meta_box ('bps_directory', __('Add to Directory', 'bps'), 'bps_directory', 'bps_form', 'side');
	add_meta_box ('bps_searchmode', __('Text Search Mode', 'bps'), 'bps_searchmode', 'bps_form', 'side');
}

function bps_directory ($post)
{
	$options = bps_options ($post->ID);
?>
	<p><strong><?php _e('Add to Directory', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="directory"><?php _e('Add to Directory', 'bps'); ?></label>
	<select name="options[directory]" id="directory">
		<option value='Yes' <?php selected ($options['directory'], 'Yes'); ?>><?php _e('Yes', 'bps'); ?></option>
		<option value='No' <?php selected ($options['directory'], 'No'); ?>><?php _e('No', 'bps'); ?></option>
	</select>

	<p><strong><?php _e('Form Header', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="header"><?php _e('Form Header', 'bps'); ?></label>
	<textarea name="options[header]" id="header" class="large-text code" rows="4"><?php echo $options['header']; ?></textarea>

	<p><strong><?php _e('Toggle Form', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="toggle"><?php _e('Toggle Form', 'bps'); ?></label>
	<select name="options[toggle]" id="toggle">
		<option value='Enabled' <?php selected ($options['toggle'], 'Enabled'); ?>><?php _e('Enabled', 'bps'); ?></option>
		<option value='Disabled' <?php selected ($options['toggle'], 'Disabled'); ?>><?php _e('Disabled', 'bps'); ?></option>
	</select>

	<p><strong><?php _e('Toggle Form Button', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="button"><?php _e('Toggle Form Button', 'bps'); ?></label>
	<input type="text" name="options[button]" id="button" value="<?php echo $options['button']; ?>" />
<?php
}

function bps_attributes ($post)
{
	$options = bps_options ($post->ID);
?>
	<p><strong><?php _e('Form Method', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="method"><?php _e('Form Method', 'bps'); ?></label>
	<select name="options[method]" id="method">
		<option value='POST' <?php selected ($options['method'], 'POST'); ?>><?php _e('POST', 'bps'); ?></option>
		<option value='GET' <?php selected ($options['method'], 'GET'); ?>><?php _e('GET', 'bps'); ?></option>
	</select>

	<p><strong><?php _e('Form Action (Results Directory)', 'bps'); ?></strong></p>
	<label class="screen-reader-text" for="action"><?php _e('Form Action (Results Directory)', 'bps'); ?></label>
<?php
	$bp_pages = array ();
	$default = 0;
	if (function_exists ('bp_core_get_directory_page_ids'))
	{
		$bp_pages = bp_core_get_directory_page_ids ();
		$default = $bp_pages['members'];
		unset ($bp_pages['members']);
	}
	$selected = $options['action']? $options['action']: $default;
	$args = array ('name' => 'options[action]', 'id' => 'action', 'selected' => $selected, 'exclude' => $bp_pages);
	wp_dropdown_pages ($args);
?>
	<p><?php _e('Need help? Use the Help tab in the upper right of your screen.'); ?></p>
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
	foreach (array ('directory', 'header', 'toggle', 'button', 'method', 'action', 'searchmode') as $key)
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
		.fixed .column-fields {width: 8%;}
		.fixed .column-action {width: 14%;}
		.fixed .column-directory {width: 16%;}
		.fixed .column-widget {width: 14%;}
		.fixed .column-shortcode {width: 18%;}
	</style>
<?php
}
