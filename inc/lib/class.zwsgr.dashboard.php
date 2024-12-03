<?php
/**
 * ZWSGR_Dashboard Class
 *
 * Handles the dashboard functionality for Smart Google Reviews plugin.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Dashboard' ) ) {

	/**
	 * The main ZWSGR_Dashboard class
	 */
	class ZWSGR_Dashboard {

        private static $instance = null;

		/**
		 * Constructor
		 */
		function __construct() {

            add_action('wp_ajax_zwsgr_gmb_dashboard_data_filter', array($this,'zwsgr_gmb_dashboard_data_filter'));
            add_action('wp_ajax_zwsgr_data_render', array($this,'zwsgr_data_render'));

		}

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_data_render() {

             // Ensure it's an AJAX request
            if (defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer( 'zwsgr_data_render', 'security' );
                $zwsgr_filter_data = isset($_POST['zwsgr_filter_data']) ? $_POST['zwsgr_filter_data'] : [];
                
            } else {

                $zwsgr_filter_data = [
					'zwsgr_gmb_account_number'   => null,
					'zwsgr_gmb_account_location' => null,
					'zwsgr_range_filter_type'    => 'rangeofdays',
					'zwsgr_range_filter_data'    => 'monthly'
				];

            }

            $zwsgr_data_render_output = '';

            $zwsgr_data_render_output .= '<div id="render-dynamic">
                <div class="zwgr-dashboard-body">'
                    . $this->zwsgr_total_reviews($zwsgr_filter_data) .
                    $this->zwsgr_average_ratings($zwsgr_filter_data) . 
                '</div>
                <div class="zwgr-dashboard-footer">'
                    . $this->zwsgr_reviews_statics_chart($zwsgr_filter_data) .
                    $this->zwsgr_top_reviews($zwsgr_filter_data) .
                '</div>
            </div>';

            if (defined('DOING_AJAX') && DOING_AJAX) {
                wp_send_json_success(['html' => $zwsgr_data_render_output]);
            } else {
                return $zwsgr_data_render_output;
            }

        }

        public function zwsgr_get_reviews_ratings($zwsgr_filter_data) {

            $zwsgr_gmb_email            = get_option('zwsgr_gmb_email');
            $zwsgr_gmb_account_number   = $zwsgr_filter_data['zwsgr_gmb_account_number'];
            $zwsgr_gmb_account_location = $zwsgr_filter_data['zwsgr_gmb_account_location'];
            $zwsgr_total_reviews        = 0;
            $zwsgr_average_rating       = 0;

            if (!isset($zwsgr_gmb_email) || empty($zwsgr_gmb_email)) {
                echo 'No GMB Email Found.';
            }

            if (!empty($zwsgr_gmb_email) && empty($zwsgr_gmb_account_number)) {
                
                // Set up the query arguments
                $zwsgr_reviews_args = array(
                    'post_type'   => 'zwsgr_reviews',
                    'fields'      => 'ids',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'zwsgr_gmb_email',
                            'value'   => $zwsgr_gmb_email,
                            'compare' => '=',
                        ),
                    ),
                );

                // Execute the query
                $zwsgr_reviews_query = new WP_Query($zwsgr_reviews_args);

                // Get the total number of posts
                $zwsgr_total_reviews = $zwsgr_reviews_query->post_count;


            } else if (!empty($zwsgr_gmb_email) && empty($zwsgr_gmb_account_location)) {

                $zwsgr_request_data_id = get_posts(array(
                    'post_type'      => 'zwsgr_request_data',
                    'posts_per_page' => 1,
                    'post_status'    => 'publish',
                    'meta_key'       => 'zwsgr_account_number',
                    'meta_value'     => $zwsgr_gmb_account_number,
                    'fields'         => 'ids',
                ))[0] ?? null;

                $zwsgr_account_locations = get_post_meta($zwsgr_request_data_id, 'zwsgr_account_locations');

                if (!empty($zwsgr_account_locations[0])) {
                    // Loop through the locations and fetch total reviews for each
                    foreach ($zwsgr_account_locations[0] as $location) {
                        $location_id = str_replace('locations/', '', $location['name']);
                        // Get the total reviews for the current location
                        $zwsgr_location_reviews = get_post_meta($zwsgr_request_data_id, 'zwsgr_location_' . $location_id . '_total_reviews', true);
                        $zwsgr_total_reviews += (int)$zwsgr_location_reviews;
                    }
                }

            } else if (!empty($zwsgr_gmb_email) && !empty($zwsgr_gmb_account_number) && !empty($zwsgr_gmb_account_location)) {

                $zwsgr_request_data_id = get_posts(array(
                    'post_type'      => 'zwsgr_request_data',
                    'posts_per_page' => 1,
                    'post_status'    => 'publish',
                    'meta_key'       => 'zwsgr_account_number',
                    'meta_value'     => $zwsgr_gmb_account_number,
                    'fields'         => 'ids',
                ))[0] ?? null;

                if ($zwsgr_request_data_id) {
                    // Fetch the total reviews meta value for the location
                    $zwsgr_total_reviews  = get_post_meta($zwsgr_request_data_id, 'zwsgr_location_' . $zwsgr_gmb_account_location . '_total_reviews', true);
                    $zwsgr_average_rating = get_post_meta($zwsgr_request_data_id, 'zwsgr_location_' . $zwsgr_gmb_account_location . '_average_rating', true);
                }

            }

            $zwsgr_data = array(
                'reviews'  => $zwsgr_total_reviews,
                'ratings'  => $zwsgr_average_rating
            );

            return $zwsgr_data;

        }

        public function zwsgr_total_reviews($zwsgr_filter_data) {

            $zwsgr_reviews_ratings = $this->zwsgr_get_reviews_ratings($zwsgr_filter_data);

            return '
            <div class="zwsgr-total-reviews-card">
                <div class="zwsgr-card-icon">
                    <span class="dashicons dashicons-format-chat"></span>
                </div>
                <div class="zwsgr-card-content">
                    <h3 class="zwsgr-card-title">' . esc_html__( 'Total Reviews', 'zw-smart-google-reviews' ) . '</h3>
                    <p class="zwsgr-card-value">' . esc_html( number_format( $zwsgr_reviews_ratings['reviews'] ) ) . '</p>
                </div>
            </div>';

        }
        

        public function zwsgr_average_ratings($zwsgr_filter_data) {

            $zwsgr_reviews_ratings = $this->zwsgr_get_reviews_ratings($zwsgr_filter_data);

            return'
            <div class="zwsgr-average-rating-card">
                <div class="zwsgr-card-icon orange">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="zwsgr-card-content">
                    <h3 class="zwsgr-card-title">' . esc_html__( 'Average Rating', 'zw-smart-google-reviews' ) . '</h3>
                    <p class="zwsgr-card-value">' . esc_html( number_format( $zwsgr_reviews_ratings['ratings'] ) ) . '</p> <!-- Ensure to define $average_rating in your function -->
                </div>
            </div>';

        }
        

        public function zwsgr_date_range_filter() {
            return '
            <div class="zwsgr-date-range-filter-wrapper">
                <!-- Title Section -->
                <div class="zwsgr-title-wrapper">
                    <h1 class="zwsgr-range-filter-title">
                        ' . esc_html__( 'Date Range Filter', 'zw-smart-google-reviews' ) . '
                    </h1>
                </div>

                <!-- Filters List Section -->
                <div class="zwsgr-filters-wrapper">
                    <ul class="zwsgr-filters-list">
                        <li class="zwsgr-filter-item">
                            <button class="zwsgr-filter-button active" data-filter="daily">
                                ' . esc_html__( 'Daily', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <button class="zwsgr-filter-button" data-filter="weekly">
                                ' . esc_html__( 'Weekly', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <button class="zwsgr-filter-button" data-filter="monthly">
                                ' . esc_html__( 'Monthly', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <input type="text" name="dates" value="" id="zwsgr-date-range-picker" class="zwsgr-filter-button">
                        </li>
                    </ul>
                </div> 
            </div>' . 
            $this->zwsgr_gmb_data_filter();
        }

        public function zwsgr_gmb_data_filter() {

            // Get the email from the WordPress options table
            $zwsgr_gmb_email = get_option('zwsgr_gmb_email');

            $zwsgr_request_data = get_posts(array(
                'post_type'      => 'zwsgr_request_data',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => array(
                    array(
                        'key'     => 'zwsgr_gmb_email',
                        'value'   => $zwsgr_gmb_email,
                        'compare' => '=',
                    ),
                ),
                'fields'         => 'ids',
            ));

            // Build the select dropdown
            $data_filter_output = '<div id="gmb-data-filter">
                <select id="zwsgr-account-select" name="zwsgr_account" class="zwsgr-input-text">
                    <option value="">Select an Account</option>';
                    foreach ($zwsgr_request_data as $zwsgr_widget_id) {
                        $zwsgr_account_number = get_post_meta($zwsgr_widget_id, 'zwsgr_account_number', true);
                        $zwsgr_account_name   = get_the_title($zwsgr_widget_id);
                        $data_filter_output .= '<option value="' . esc_attr($zwsgr_account_number) . '">' . esc_html($zwsgr_account_name) . '</option>';
                    }
                $data_filter_output .= '</select>
            </div>';

            return $data_filter_output;

        }        

        public function zwsgr_top_reviews() {
            return '<div class="zwsgr-flex-container zwsgr-flex-right">
                <div class="zwsgr-flex-inner-container">
                    <h4>' . 
                        esc_html__( 'Top Reviews', 'zw-smart-google-reviews' ) . 
                    '</h4>
                </div>
            </div>';
        }        
        
        public function zwsgr_reviews_statics_chart() {
            return '<div class="zwsgr-flex-container zwsgr-flex-left">
                <div class="zwsgr-flex-inner-container">
                    <h4>' . 
                        esc_html__( 'Review Statistics Chart', 'zw-smart-google-reviews' ) . 
                    '</h4>
                </div>
            </div>';
        }

        public function zwsgr_gmb_dashboard_data_filter() {

            // Ensure it's an AJAX request
            if (defined('DOING_AJAX') && DOING_AJAX) {
                check_ajax_referer( 'zwsgr_gmb_dashboard_filter', 'security' );
            }

            $zwsgr_account_number  = $_POST['zwsgr_account_number'];

            if (empty($zwsgr_account_number)) {
                wp_send_json_error('An account number is required to retrieve location data.');
                wp_die();
            }            

            $zwsgr_request_data_id = get_posts(array(
                'post_type'      => 'zwsgr_request_data',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'meta_key'       => 'zwsgr_account_number',
                'meta_value'     => $zwsgr_account_number,
                'fields'         => 'ids',
            ))[0] ?? null;
        
            if (empty($zwsgr_request_data_id)) {
                wp_send_json_error('No locations found for this account.');
                wp_die();
            }

            $zwsgr_account_locations = get_post_meta($zwsgr_request_data_id, 'zwsgr_account_locations', true);

            $output = '';

            // Check if the custom field has a value
            if ($zwsgr_account_locations) {
                $output .= '<select id="zwsgr-location-select" name="zwsgr_location" class="zwsgr-input-text">';
                $output .= '<option value="">Select a Location</option>';
                
                foreach ($zwsgr_account_locations as $zwsgr_account_location) {
                    $zwsgr_account_location_id = $zwsgr_account_location['name'] ? ltrim(strrchr($zwsgr_account_location['name'], '/'), '/') : '';
                    $selected = ($zwsgr_account_location_id === $zwsgr_location_number) ? 'selected' : '';
                    $output .= '<option value="' . esc_attr($zwsgr_account_location_id) . '" ' . $selected . '>' . esc_html($zwsgr_account_location['name']) . '</option>';
                }

                $output .= '</select>';
            }

            wp_send_json_success($output);

            die();

        }

	}

    new ZWSGR_Dashboard();

}