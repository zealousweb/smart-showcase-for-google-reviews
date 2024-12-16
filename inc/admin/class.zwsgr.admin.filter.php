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
			add_filter('post_row_actions', array($this, 'zwsgr_remove_quick_edit_from_reviews_listings'), 10, 2);

			add_filter('bulk_actions-edit-zwsgr_reviews', array($this, 'filter__remove_all_bulk_actions'));
			add_filter('months_dropdown_results', array($this,'my_remove_date_filter'));
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
				$url = admin_url('admin.php?page=zwsgr_widget_configurator&selectedOption=' . $layout_option . '&zwsgr_widget_id=' . $post->ID);
			}
		
			return $url;
		}

		/**
		 * Remove the 'Quick Edit' option from the row actions in custom post type listings.
		 *
		 * This function modifies the row actions for a specific post type 
		 * to remove the 'Quick Edit' action from the WordPress admin area.
		 *
		 * @param array $zwsgr_actions An array of row actions.
		 * @param WP_Post $zwsgr_post The current post object being processed.
		 * @return array Modified array of row actions.
		 */
		function zwsgr_remove_quick_edit_from_reviews_listings($zwsgr_actions, $zwsgr_post) {

			// Check if the post type is 'zwsgr_reviews'
			if ($zwsgr_post->post_type == 'zwsgr_reviews') {
				
				// Remove the 'View' link
				unset($zwsgr_actions['view']);
				
				// Remove the 'Trash' link
				unset($zwsgr_actions['trash']);
				
				// Remove the 'Quick Edit' link
				unset($zwsgr_actions['inline hide-if-no-js']);
			}
			
			return $zwsgr_actions;
			
		}

		
		function filter__remove_all_bulk_actions($actions) {
			// Check if the current screen is the list table for your custom post type
			if (get_current_screen()->post_type == 'zwsgr_reviews') {
				// Remove all bulk actions
				$actions = array();
			}
			return $actions;
		
		}
		function my_remove_date_filter( $months ) {
			global $typenow; // use this to restrict it to a particular post type
			if ( $typenow == 'zwsgr_reviews' ) {
				return array(); // return an empty array
			}
			return $months; // otherwise return the original for other post types
		}
		

	}

}