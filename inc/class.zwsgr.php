<?php
/**
 * ZWSGR Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR' ) ) {

	/**
	 * The main ZWSGR class
	 */
	class ZWSGR {
		
		private static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {

			add_action( 'setup_theme',   array( $this, 'action__setup_theme' ) );
			add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );
			add_filter( 'plugin_action_links',array( $this,'action__zwsgr_plugin_action_links'), 10, 2 );
			
		}

		function action__plugins_loaded() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		function action__setup_theme() {
			if ( is_admin() ) {
				ZWSGR()->admin = new ZWSGR_Admin;
				ZWSGR()->admin->action = new ZWSGR_Admin_Action;
				ZWSGR()->admin->filter = new ZWSGR_Admin_Filter;
				
			} else {
				ZWSGR()->front = new ZWSGR_Front;
				ZWSGR()->front->action = new ZWSGR_Front_Action;
				ZWSGR()->front->filter = new ZWSGR_Front_Filter;
				
			}
			ZWSGR()->lib = new ZWSGR_Lib;
				
		}

		/**
		 * Action: plugin_action_links
		 * Add Subscription link after active links.
		 * @method action__zwsgr_plugin_action_links
		 * @return $links
		*/
		function action__zwsgr_plugin_action_links( $links, $file ) 
		{

			if ( $file != ZWSGR_PLUGIN_BASENAME ) {
				return $links;
			}
			if ( is_plugin_active( 'smart-google-reviews/smart-google-reviews.php' )  )
			{
				$support_link = '<a href="https://support.zealousweb.com/" target="_blank">' .__( 'Support', 'zw-smart-google-reviews' ). '</a>';
				$document_link = '<a href="https://store.zealousweb.com/" target="_blank">' .__( 'Document', 'zw-smart-google-reviews' ). '</a>';
				$donateLink = '<a target="_blank" href="http://www.zealousweb.com/payment/">' . __( 'Donate', 'zw-smart-google-reviews' ) . '</a>';

				array_unshift( $links , $support_link, $document_link, $donateLink );
			}
			return $links;
		}
	}
}

function ZWSGR() {
	return ZWSGR::instance();
}
ZWSGR();
