<?php
/**
 * ZWSSGR_Admin_Filter Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR_Admin_Filter' ) ) {

	/**
	 *  The ZWSSGR_Admin_Filter Class
	 */
	class ZWSSGR_Admin_Filter {

		function __construct() 
		{
			add_filter('get_edit_post_link', array($this, 'zwssgr_change_edit_post_link'), 10, 2);
			add_filter('post_row_actions', array($this, 'zwssgr_remove_quick_edit_from_widget_listings'), 10, 2);

			add_filter('bulk_actions-edit-zwssgr_reviews', array($this, 'zwssgr_filter__remove_all_bulk_actions'));
			add_filter('bulk_actions-edit-zwssgr_data_widget', array($this, 'zwssgr_filter__remove_all_bulk_actions'));
			add_filter('months_dropdown_results', array($this,'zwssgr_my_remove_date_filter'));

			add_filter('manage_edit-zwssgr_reviews_columns', array($this, 'zwssgr_remove_bulk_actions_checkbox'));
			add_filter('manage_edit-zwssgr_data_widget_columns', array($this, 'zwssgr_remove_bulk_actions_checkbox'));
		}

		/**
		 * Modifies the edit post link for 'zwssgr_data_widget' post type to redirect to the widget configurator page.
		 *
		 * @param string $url The original URL of the post edit page.
		 * @param WP_Post $post The current post object.
		 * 
		 * @return string Modified URL for the post edit link.
		 */
		function zwssgr_change_edit_post_link($zwssgr_url, $zwssgr_post) 
		{
			// Fetch the full post object using the post ID
			$zwssgr_post = get_post($zwssgr_post);

			// Check if the post type is 'zwssgr_data_widget'
			if ($zwssgr_post && 'zwssgr_data_widget' === $zwssgr_post->post_type) {

				// Get the account number from the custom post meta
				$zwssgr_account_number = get_post_meta($zwssgr_post->ID, 'zwssgr_account_number', true);
				$zwssgr_location_number = get_post_meta($zwssgr_post->ID, 'zwssgr_location_number', true);
				$zwssgr_layout_option = get_post_meta($zwssgr_post->ID, 'layout_option', true);
				$zwssgr_current_tab2 = get_post_meta($zwssgr_post->ID, 'tab-selected', true);

				// Check if both account and location numbers are empty
				if (empty($zwssgr_account_number) || empty($zwssgr_location_number)) {
					// Redirect to the 'fetch data' page if both account and location numbers are empty
					$zwssgr_url = admin_url('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data&zwssgr_widget_id=' . $zwssgr_post->ID);
				} else if (!$zwssgr_current_tab2) {
					// Redirect to a specific page for setting the tab
					$zwssgr_url = admin_url('admin.php?page=zwssgr_widget_configurator&tab=tab-options&selectedOption=' . $zwssgr_layout_option . '&zwssgr_widget_id=' . $zwssgr_post->ID);
				}else {
					// Redirect to the widget configurator page with the selected layout option if account and location numbers are not empty
					$zwssgr_url = admin_url('admin.php?page=zwssgr_widget_configurator&tab=tab-selected&selectedOption=' . $zwssgr_layout_option . '&zwssgr_widget_id=' . $zwssgr_post->ID);
				}
			
			}
			return $zwssgr_url;
		}

		/**
		 * Remove the 'Quick Edit' option from the row actions in custom post type listings.
		 *
		 * This function modifies the row actions for a specific post type 
		 * to remove the 'Quick Edit' action from the WordPress admin area.
		 *
		 * @param array $zwssgr_actions An array of row actions.
		 * @param WP_Post $zwssgr_post The current post object being processed.
		 * @return array Modified array of row actions.
		 */
		function zwssgr_remove_quick_edit_from_widget_listings($zwssgr_actions, $zwssgr_post) {
			// Check if the post type is 'zwssgr_reviews'
			if (in_array($zwssgr_post->post_type, ['zwssgr_reviews', 'zwssgr_data_widget'])) {
				
				// Remove the 'View' link
				unset($zwssgr_actions['view']);
				
				// Remove the 'Quick Edit' link
				unset($zwssgr_actions['inline hide-if-no-js']);
			}
			return $zwssgr_actions;
		}

		function zwssgr_filter__remove_all_bulk_actions($zwssgr_actions) {
			// Check if the current screen is the list table for your custom post type
			if (get_current_screen()->post_type == 'zwssgr_reviews' || get_current_screen()->post_type == 'zwssgr_data_widget') {
				// Remove all bulk actions
				$zwssgr_actions = array();
			}
			return $zwssgr_actions;
		}

		function zwssgr_my_remove_date_filter( $zwssgr_months ) {
			global $typenow; // use this to restrict it to a particular post type
			if ($typenow == 'zwssgr_reviews' || $typenow == 'zwssgr_data_widget') {
				return array(); // return an empty array
			}
			return $zwssgr_months; // otherwise return the original for other post types
		}
		
		/**
		 * Remove the bulk actions column (checkbox) from admin table for specific post types.
		 *
		 * @param array $zwssgr_columns The columns for the admin list table.
		 * @return array Updated columns without the checkbox column.
		 */
		function zwssgr_remove_bulk_actions_checkbox($zwssgr_columns) {
			// Check if the current screen is for the custom post types
			$zwssgr_screen = get_current_screen();
			if ($zwssgr_screen->post_type == 'zwssgr_reviews' || $zwssgr_screen->post_type == 'zwssgr_data_widget') {
				// Remove the checkbox column
				if (isset($zwssgr_columns['cb'])) {
					unset($zwssgr_columns['cb']);
				}
			}
			return $zwssgr_columns;
		}
	}
}