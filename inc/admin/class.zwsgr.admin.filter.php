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
			add_filter('post_row_actions', array($this, 'zwsgr_remove_quick_edit_from_widget_listings'), 10, 2);

			add_filter('bulk_actions-edit-zwsgr_reviews', array($this, 'filter__remove_all_bulk_actions'));
			add_filter('bulk_actions-edit-zwsgr_data_widget', array($this, 'filter__remove_all_bulk_actions'));
			add_filter('months_dropdown_results', array($this,'my_remove_date_filter'));

			add_filter('manage_edit-zwsgr_reviews_columns', array($this, 'remove_bulk_actions_checkbox'));
			add_filter('manage_edit-zwsgr_data_widget_columns', array($this, 'remove_bulk_actions_checkbox'));

		}

		/**
		 * Modifies the edit post link for 'zwsgr_data_widget' post type to redirect to the widget configurator page.
		 *
		 * @param string $url The original URL of the post edit page.
		 * @param WP_Post $post The current post object.
		 * 
		 * @return string Modified URL for the post edit link.
		 */
		function zwsgr_change_edit_post_link($zwsgr_url, $zwsgr_post) 
		{
			// Fetch the full post object using the post ID
			$zwsgr_post = get_post($zwsgr_post);
		
			// Check if the post type is 'zwsgr_data_widget'
			if ($zwsgr_post && 'zwsgr_data_widget' === $zwsgr_post->post_type) {

				// Get the account number from the custom post meta
				$zwsgr_account_number = get_post_meta($zwsgr_post->ID, 'zwsgr_account_number', true);
				$zwsgr_location_number = get_post_meta($zwsgr_post->ID, 'zwsgr_location_number', true);
				$layout_option = get_post_meta($zwsgr_post->ID, 'layout_option', true);

				// Check if both account and location numbers are empty
				if (empty($zwsgr_account_number) || empty($zwsgr_location_number)) {
					// Redirect to the 'fetch data' page if both account and location numbers are empty
					$zwsgr_url = admin_url('admin.php?page=zwsgr_widget_configurator&tab=tab-fetch-data&zwsgr_widget_id=' . $zwsgr_post->ID);
				} else {
					// Redirect to the widget configurator page with the selected layout option if account and location numbers are not empty
					$zwsgr_url = admin_url('admin.php?page=zwsgr_widget_configurator&selectedOption=' . $layout_option . '&zwsgr_widget_id=' . $zwsgr_post->ID);
				}
			
			}
		
			return $zwsgr_url;
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
		function zwsgr_remove_quick_edit_from_widget_listings($zwsgr_actions, $zwsgr_post) {

			// Check if the post type is 'zwsgr_reviews'
			if ($zwsgr_post->post_type == 'zwsgr_data_widget') {
				
				// Remove the 'View' link
				unset($zwsgr_actions['view']);
				
				// // Remove the 'Trash' link
				// unset($zwsgr_actions['trash']);
				
				// Remove the 'Quick Edit' link
				unset($zwsgr_actions['inline hide-if-no-js']);
			}
			
			return $zwsgr_actions;
			
		}

		
		function filter__remove_all_bulk_actions($actions) {
			// Check if the current screen is the list table for your custom post type
			if (get_current_screen()->post_type == 'zwsgr_reviews' || get_current_screen()->post_type == 'zwsgr_data_widget') {
				// Remove all bulk actions
				$actions = array();
			}
			return $actions;
		
		}
		function my_remove_date_filter( $months ) {
			global $typenow; // use this to restrict it to a particular post type
			if ($typenow == 'zwsgr_reviews' || $typenow == 'zwsgr_data_widget') {
				return array(); // return an empty array
			}
			return $months; // otherwise return the original for other post types
		}
		
		/**
		 * Remove the bulk actions column (checkbox) from admin table for specific post types.
		 *
		 * @param array $columns The columns for the admin list table.
		 * @return array Updated columns without the checkbox column.
		 */
		function remove_bulk_actions_checkbox($columns) {
			// Check if the current screen is for the custom post types
			$screen = get_current_screen();
			if ($screen->post_type == 'zwsgr_reviews' || $screen->post_type == 'zwsgr_data_widget') {
				// Remove the checkbox column
				if (isset($columns['cb'])) {
					unset($columns['cb']);
				}
			}
			return $columns;
		}


	}

}