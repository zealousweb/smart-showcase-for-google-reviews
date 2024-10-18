<?php
/**
 * ZWSGR_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Admin_Action' ) ){

	/**
	 *  The ZWSGR_Admin_Action Class
	 */
	class ZWSGR_Admin_Action {

		function __construct()  {

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );
			
            		
		}
		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__admin_init() {

			// admin js
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-js', ZWSGR_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// admin css
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-css', ZWSGR_URL . 'assets/css/admin.min.css', array(), ZWSGR_VERSION );
			
		}
	}
}
