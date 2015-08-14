<?php
/*
Plugin Name: WPML Multilingual CMS
Plugin URI: https://wpml.org/
Description: WPML Multilingual CMS | <a href="https://wpml.org">Documentation</a> | <a href="https://wpml.org/version/wpml-3-2/">WPML 3.2 release notes</a>
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
Version: 3.2.2
Plugin Slug: sitepress-multilingual-cms
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
	return;
}
define( 'ICL_SITEPRESS_VERSION', '3.2.2' );
//define('ICL_SITEPRESS_DEV_VERSION', '3.2.2');
define( 'ICL_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'ICL_PLUGIN_FILE', basename( __FILE__ ) );
define( 'ICL_PLUGIN_FULL_PATH', basename( ICL_PLUGIN_PATH ) . '/' . ICL_PLUGIN_FILE );
define( 'ICL_PLUGIN_FOLDER', basename( ICL_PLUGIN_PATH ) );


//PHP 5.2 backward compatibility
if ( !defined( 'FILTER_SANITIZE_FULL_SPECIAL_CHARS' ) ) {
    define( 'FILTER_SANITIZE_FULL_SPECIAL_CHARS', FILTER_SANITIZE_STRING );
}

require ICL_PLUGIN_PATH . '/inc/wpml-dependencies-check/wpml-bundle-check.class.php';
require ICL_PLUGIN_PATH . '/inc/wpml-private-actions.php';
require ICL_PLUGIN_PATH . '/inc/locale/wpml-locale.class.php';
require ICL_PLUGIN_PATH . '/inc/functions.php';
require ICL_PLUGIN_PATH . '/inc/functions-security.php';
require ICL_PLUGIN_PATH . '/inc/core-abstract-classes/wpml-element-translation.class.php';
require ICL_PLUGIN_PATH . '/inc/wpml-post-comments.class.php';
require ICL_PLUGIN_PATH . '/inc/icl-admin-notifier.php';

if ( !function_exists( 'filter_input' ) ) {
    wpml_set_plugin_as_inactive();
    add_action( 'admin_notices', 'wpml_missing_filter_input_notice' );
    return;
}

define( 'ICL_PLUGIN_URL', wpml_filter_include_url( untrailingslashit( plugin_dir_url( __FILE__ ) ) ) );

if ( wpml_version_is( ICL_SITEPRESS_VERSION ) && wpml_site_uses_icl() ) {
	wpml_set_plugin_as_inactive();
	add_action( 'admin_notices', 'wpml_site_uses_icl_message_notice' );
	return;
}

require ICL_PLUGIN_PATH . '/inc/template-functions.php';
add_action( 'plugins_loaded', 'wpml_apply_include_filters' );

require ICL_PLUGIN_PATH . '/inc/lang-data.php';
require ICL_PLUGIN_PATH . '/inc/setup/sitepress-setup.class.php';

require ICL_PLUGIN_PATH . '/inc/not-compatible-plugins.php';
if(!empty($icl_ncp_plugins)){
    return;
}

require ICL_PLUGIN_PATH . '/inc/setup/sitepress-schema.php';

require ICL_PLUGIN_PATH . '/inc/functions-load.php';
require ICL_PLUGIN_PATH . '/inc/constants.php';
require ICL_PLUGIN_PATH . '/inc/taxonomy-term-translation/wpml-term-translations.class.php';
require ICL_PLUGIN_PATH . '/inc/functions-troubleshooting.php';
require ICL_PLUGIN_PATH . '/menu/term-taxonomy-menus/taxonomy-translation-display.class.php';
require ICL_PLUGIN_PATH . '/inc/taxonomy-term-translation/wpml-term-translation.class.php';

require ICL_PLUGIN_PATH . '/inc/post-translation/wpml-post-translation.class.php';
require ICL_PLUGIN_PATH . '/inc/post-translation/wpml-admin-post-actions.class.php';
require ICL_PLUGIN_PATH . '/inc/post-translation/wpml-frontend-post-actions.class.php';

require ICL_PLUGIN_PATH . '/inc/url-handling/wpml-url-filters.class.php';
require ICL_PLUGIN_PATH . '/inc/request-handling/wpml-language-resolution.class.php';
require ICL_PLUGIN_PATH . '/inc/url-handling/wpml-url-converter.class.php';
require ICL_PLUGIN_PATH . '/inc/utilities/wpml-languages.class.php';
load_essential_globals();

require ICL_PLUGIN_PATH . '/inc/query-filtering/wpml-query-utils.class.php';
require ICL_PLUGIN_PATH . '/sitepress.class.php';
require ICL_PLUGIN_PATH . '/inc/query-filtering/wpml-query-filter.class.php';
wpml_load_query_filter ( icl_get_setting ( 'setup_complete' ) );
require ICL_PLUGIN_PATH . '/inc/hacks.php';
require ICL_PLUGIN_PATH . '/inc/upgrade.php';
require ICL_PLUGIN_PATH . '/inc/language-switcher.php';
require ICL_PLUGIN_PATH . '/inc/import-xml.php';

// using a plugin version that the db can't be upgraded to
if(defined('WPML_UPGRADE_NOT_POSSIBLE') && WPML_UPGRADE_NOT_POSSIBLE) return;

if(is_admin() || defined('XMLRPC_REQUEST')){
    require ICL_PLUGIN_PATH . '/lib/icl_api.php';
    require ICL_PLUGIN_PATH . '/lib/xml2array.php';
    require ICL_PLUGIN_PATH . '/lib/Snoopy.class.php';
    require ICL_PLUGIN_PATH . '/inc/url-handling/wpml-language-domains.class.php';
    if ( !defined ( 'DOING_AJAX' ) ) {
        require ICL_PLUGIN_PATH . '/menu/wpml-admin-scripts-setup.class.php';
    }
    require ICL_PLUGIN_PATH . '/inc/pointers.php';
}elseif(preg_match('#wp-comments-post\.php$#', $_SERVER['REQUEST_URI'])){
	require_once ICL_PLUGIN_PATH . '/inc/translation-management/translation-management.class.php';
}

if ( function_exists('is_multisite') && is_multisite() ) {
    $wpmu_sitewide_plugins = (array) maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
    if(false === get_option('icl_sitepress_version', false) && isset($wpmu_sitewide_plugins[ICL_PLUGIN_FOLDER.'/'.basename(__FILE__)])){
        icl_sitepress_activate();
    }
    include_once ICL_PLUGIN_PATH . '/inc/functions-network.php';
    if(get_option('_wpml_inactive', false) && isset($wpmu_sitewide_plugins[ICL_PLUGIN_FOLDER.'/sitepress.php'])){
        wpml_set_plugin_as_inactive();
        return;
    }
}

global $sitepress;
$sitepress          = new SitePress();
$sitepress_settings = $sitepress->get_settings();
wpml_load_term_filters();
wpml_maybe_setup_post_edit();

require ICL_PLUGIN_PATH . '/modules/cache-plugins-integration/cache-plugins-integration.php';

require ICL_PLUGIN_PATH . '/inc/wp-login-filters.php';

require ICL_PLUGIN_PATH . '/inc/plugins-integration.php';
if (is_admin()) {
    activate_installer( $sitepress );
}

if(!empty($sitepress_settings['automatic_redirect'])){
    require ICL_PLUGIN_PATH . '/inc/browser-redirect.php';
}

// activation hook
register_deactivation_hook( WP_PLUGIN_DIR . '/' . ICL_PLUGIN_FOLDER . '/sitepress.php', 'icl_sitepress_deactivate');

add_filter('plugin_action_links', 'icl_plugin_action_links', 10, 2);