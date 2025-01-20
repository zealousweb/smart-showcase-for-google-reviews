<?php
/**
 * ZWSSGR_Admin Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR_Admin' ) ) {

	/**
	 * The ZWSSGR_Admin Class
	 */
	class ZWSSGR_Admin {

		public $zwssgr_action = null;
		public $zwssgr_filter = null;
		
		function __construct() 
		{

		}
	}
}
