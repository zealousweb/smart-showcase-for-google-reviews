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
		
		function __construct() {
			
		}
	}
}

function ZWSGR() {
	return ZWSGR::instance();
}
ZWSGR();
