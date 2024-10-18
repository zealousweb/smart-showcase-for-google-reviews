<?php
/**
 * ZWSGR_Front_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Front_Action' ) ){

	/**
	 *  The ZWSGR_Front_Action Class
	 */
	class ZWSGR_Front_Action {

		function __construct()  {
			add_action('wp_enqueue_scripts',array( $this, 'ZWSGR_public_enqueue'));	
		}

		function ZWSGR_public_enqueue() {
			wp_enqueue_style( ZWSGR_PREFIX . '-front-css', ZWSGR_URL . 'assets/css/front.min.css', array(), ZWSGR_VERSION );
			wp_enqueue_script( ZWSGR_PREFIX . '_front_js', ZWSGR_URL . 'assets/js/front.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
		}
	}
}
