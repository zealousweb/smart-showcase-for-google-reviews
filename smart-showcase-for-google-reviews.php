<?php
/**
 * Plugin Name: Smart Showcase for Google Reviews
 * Plugin URL: https://wordpress.org/plugin-url/
 * Description: The Smart Showcase for Google Reviews enables users to easily embed Google Reviews on your WordPress site.
 * Version: 1.0.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com/
 * Developer: The ZealousWeb Team
 * Developer E-Mail: support@zealousweb.com
 * Text Domain: smart-showcase-for-google-reviews
 * Domain Path: /languages
 *
 * Copyright: © 2009-2020 ZealousWeb.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package Smart Showcase for Google Reviews
 * @since 1.0.0
 */

if ( !defined( 'ZWSSGR_VERSION' ) ) {
	define( 'ZWSSGR_VERSION', '1.0.0' ); // Version of plugin
}

if ( !defined( 'ZWSSGR_FILE' ) ) {
	define( 'ZWSSGR_FILE', __FILE__ ); // Plugin File
}

if ( !defined( 'ZWSSGR_DIR' ) ) {
	define( 'ZWSSGR_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if ( !defined( 'ZWSSGR_URL' ) ) {
	define( 'ZWSSGR_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if ( !defined( 'ZWSSGR_PLUGIN_BASENAME' ) ) {
	define( 'ZWSSGR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( !defined( 'ZWSSGR_META_PREFIX' ) ) {
	define( 'ZWSSGR_META_PREFIX', 'ZWSSGR_' ); // Plugin metabox prefix
}

if ( !defined( 'ZWSSGR_PREFIX' ) ) {
	define( 'ZWSSGR_PREFIX', 'zwssgr' ); // Plugin prefix
}

if( !defined( 'ZWSSGR_POST_REVIEW_TYPE' ) ) {
	define( 'ZWSSGR_POST_REVIEW_TYPE', 'ZWSSGR_reviews' ); // Plugin Google Reviews post type name
}
if( !defined( 'ZWSSGR_POST_WIDGET_TYPE' ) ) {
	define( 'ZWSSGR_POST_WIDGET_TYPE', 'ZWSSGR_data_widget' ); // Plugin Google Widget post type name
}

/**
 * Initialize the main class
 */
if ( !function_exists( 'ZWSSGR' ) ) {

	if ( is_admin() ) {
		require_once( ZWSSGR_DIR . '/inc/admin/class.' . ZWSSGR_PREFIX . '.admin.php' );
		require_once( ZWSSGR_DIR . '/inc/lib/api/class.' . ZWSSGR_PREFIX . '.api.php' );
		require_once( ZWSSGR_DIR . '/inc/lib/zwssgr-batch-processing/class.' . ZWSSGR_PREFIX . '.zqm.php' );
		require_once( ZWSSGR_DIR . '/inc/lib/class.' . ZWSSGR_PREFIX . '.gmbc.php' );
		require_once( ZWSSGR_DIR . '/inc/admin/class.' . ZWSSGR_PREFIX . '.admin.action.php' );
		require_once( ZWSSGR_DIR . '/inc/admin/class.' . ZWSSGR_PREFIX . '.admin.filter.php' );
		
	} else {
		require_once( ZWSSGR_DIR . '/inc/front/class.' . ZWSSGR_PREFIX . '.front.php' );
		require_once( ZWSSGR_DIR . '/inc/front/class.' . ZWSSGR_PREFIX . '.front.action.php' );
		require_once( ZWSSGR_DIR . '/inc/front/class.' . ZWSSGR_PREFIX . '.front.filter.php' );
	}

	require_once( ZWSSGR_DIR . '/inc/lib/class.' . ZWSSGR_PREFIX . '.cs.php' );
	require_once( ZWSSGR_DIR . '/inc/lib/class.' . ZWSSGR_PREFIX . '.dashboard.php' );
	require_once( ZWSSGR_DIR . '/inc/lib/class.' . ZWSSGR_PREFIX . '.lib.php' );

	//Initialize all the things.
	require_once( ZWSSGR_DIR . '/inc/class.' . ZWSSGR_PREFIX . '.php' );

}