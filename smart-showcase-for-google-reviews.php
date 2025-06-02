<?php
/**
 * Plugin Name: Smart Showcase for Google Reviews
 * Plugin URL: https://wordpress.org/plugins/smart-showcase-for-google-reviews/
 * Description: The Smart Showcase for Google Reviews enables users to easily embed Google Reviews on your WordPress site.
 * Version: 1.0.2
 * Requires at least: 5.8
 * Requires PHP: 7.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: smart-showcase-for-google-reviews
 * Domain Path: /languages
 * 
 * @package   Smart-Showcase-for-Google-Reviews
 * @author    ZealousWeb
 * @copyright 2025 ZealousWeb
 * @license   GPLv3 or later
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package Smart Showcase for Google Reviews
 * @since 1.0.2
 */

if ( !defined( 'ZWSSGR_VERSION' ) ) {
	define( 'ZWSSGR_VERSION', '1.0.2' ); // Version of plugin
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
	define( 'ZWSSGR_META_PREFIX', 'zwssgr_' ); // Plugin metabox prefix
}

if ( !defined( 'ZWSSGR_PREFIX' ) ) {
	define( 'ZWSSGR_PREFIX', 'zwssgr' ); // Plugin prefix
}

if( !defined( 'ZWSSGR_POST_REVIEW_TYPE' ) ) {
	define( 'ZWSSGR_POST_REVIEW_TYPE', 'zwssgr_reviews' ); // Plugin Google Reviews post type name
}
if( !defined( 'ZWSSGR_POST_WIDGET_TYPE' ) ) {
	define( 'ZWSSGR_POST_WIDGET_TYPE', 'zwssgr_data_widget' ); // Plugin Google Widget post type name
}
// Define the upload directory constant for plugin logs
if ( ! defined( 'ZWSSGR_UPLOAD_DIR' ) ) {
    $zwssgr_upload_dir = wp_upload_dir();
    define( 'ZWSSGR_UPLOAD_DIR', $zwssgr_upload_dir['basedir']); // Path to store logs
}
if ( !defined( 'ZWSSGR_UPLOAD_URL' ) ) {
    $zwssgr_upload_url = wp_upload_dir(); // Get the upload directory details
    define( 'ZWSSGR_UPLOAD_URL', $zwssgr_upload_url['baseurl'] ); // Define constant for the base URL
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
