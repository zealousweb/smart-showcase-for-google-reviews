<?php
/**
 * ZWSGR_Admin_Filter Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Admin_Filter' ) ) {

	/**
	 *  The ZWSGR_Admin_Filter Class
	 */
	class ZWSGR_Admin_Filter {

		function __construct() 
		{

			add_filter('get_edit_post_link', array($this, 'zwsgr_change_edit_post_link'), 10, 2);

		}

		/**
		 * Modifies the edit post link for 'zwsgr_data_widget' post type to redirect to the widget configurator page.
		 *
		 * @param string $url The original URL of the post edit page.
		 * @param WP_Post $post The current post object.
		 * 
		 * @return string Modified URL for the post edit link.
		 */
		function zwsgr_change_edit_post_link($url, $post) 
		{
			// Fetch the full post object using the post ID
			$post = get_post($post);
		
			// Check if the post type is 'zwsgr_data_widget'
			if ($post && 'zwsgr_data_widget' === $post->post_type) {
				// Get the account number from the custom post meta
				$account_number = get_post_meta($post->ID, 'zwsgr_account_number', true);
				$layout_option = get_post_meta($post->ID, 'layout_option', true);
				
				// Modify the edit URL to redirect to the widget configurator page
				$url = admin_url('admin.php?page=zwsgr_widget_configurator&selectedOption=' . $layout_option . '&post_id=' . $post->ID);
			}
		
			return $url;
		}

	}

}
