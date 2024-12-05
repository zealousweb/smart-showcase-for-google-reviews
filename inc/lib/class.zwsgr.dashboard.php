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
					'zwsgr_range_filter_type'    => '',
					'zwsgr_range_filter_data'    => ''
				];

            }

            $zwsgr_data_render_args = $this->zwsgr_data_render_query($zwsgr_filter_data);

            $zwsgr_data_render_output = '';

            $zwsgr_data_render_output .= '<div id="render-dynamic">
                <div class="zwgr-dashboard-body">'
                    . $this->zwsgr_total_reviews($zwsgr_data_render_args) .
                    $this->zwsgr_average_ratings($zwsgr_data_render_args) . 
                '</div>
                <div class="zwgr-dashboard-footer">'
                    . $this->zwsgr_reviews_statics_chart($zwsgr_data_render_args) .
                    $this->zwsgr_top_reviews($zwsgr_data_render_args) .
                '</div>
            </div>';

            if (defined('DOING_AJAX') && DOING_AJAX) {
                wp_send_json_success(['html' => $zwsgr_data_render_output]);
            } else {
                return $zwsgr_data_render_output;
            }

        }

        public function zwsgr_data_render_query($zwsgr_filter_data) {

            $zwsgr_gmb_email            = get_option('zwsgr_gmb_email');
            $zwsgr_gmb_account_number   = $zwsgr_filter_data['zwsgr_gmb_account_number'];
            $zwsgr_gmb_account_location = $zwsgr_filter_data['zwsgr_gmb_account_location'];
            $zwsgr_range_filter_type    = $zwsgr_filter_data['zwsgr_range_filter_type'];
            $zwsgr_range_filter_data    = $zwsgr_filter_data['zwsgr_range_filter_data'];

            if (!isset($zwsgr_gmb_email) || empty($zwsgr_gmb_email)) {
                echo 'No GMB Email Found.';
            }

            if (!empty($zwsgr_gmb_email)) {

                // Set up the query arguments
                $zwsgr_data_render_args = array(
                    'post_type'   => 'zwsgr_reviews',
                    'fields'      => 'ids',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'zwsgr_gmb_email',
                            'value'   => $zwsgr_gmb_email,
                            'compare' => '=',
                        )
                    ),
                );

                if ($zwsgr_range_filter_type == 'rangeofdays') {

                    if ($zwsgr_range_filter_data == 'daily') {
                        $zwsgr_data_render_args['date_query'] = array(
                            array(
                                'after'     => 'today', // Posts from today
                                'inclusive' => true,
                            ),
                        );
                    } elseif ($zwsgr_range_filter_data == 'weekly') {
                        $zwsgr_data_render_args['date_query'] = array(
                            array(
                                'after'     => '7 days ago',
                                'inclusive' => true,
                            ),
                        );
                    } elseif ($zwsgr_range_filter_data == 'monthly') {
                        $zwsgr_data_render_args['date_query'] = array(
                            array(
                                'after'     => '30 days ago',
                                'inclusive' => true,
                            ),
                        );
                    }

                } else if ($zwsgr_range_filter_type == 'rangeofdate' && !empty($zwsgr_range_filter_data)) {

                    $range_dates = explode(' - ', $zwsgr_range_filter_data);
                    $start_date = $range_dates[0]; 
                    $end_date = isset($range_dates[1]) ? $range_dates[1] : ''; 

                    $start_date_formatted = date('Y-m-d', strtotime($start_date));
                    $end_date_formatted = date('Y-m-d', strtotime($end_date));

                    // Set the date query with the formatted range
                    $zwsgr_data_render_args['date_query'] = array(
                        'relation' => 'AND',
                        array(
                            'after'     => $start_date_formatted,  // Posts after or on the start date
                            'inclusive' => true,
                        ),
                        array(
                            'before'    => $end_date_formatted,    // Posts before or on the end date
                            'inclusive' => true,
                        ),
                    );
                    
                }

                // Add the account filter only if it exists
                if (!empty($zwsgr_gmb_account_number)) {
                    $zwsgr_data_render_args['meta_query'][] = array(
                        'key'     => 'zwsgr_account_number',
                        'value'   => $zwsgr_gmb_account_number,
                        'compare' => '=',
                    );
                }

                // Add the location filter only if it exists
                if (!empty($zwsgr_gmb_account_location)) {
                    $zwsgr_data_render_args['meta_query'][] = array(
                        'key'     => 'zwsgr_location_code',
                        'value'   => $zwsgr_gmb_account_location,
                        'compare' => '=',
                    );
                }

                return $zwsgr_data_render_args;

        }
    }

        public function zwsgr_get_reviews_ratings($zwsgr_data_render_args) {

            $zwsgr_total_reviews        = 0;
            $zwsgr_average_rating       = 0;

            $zwsgr_accounts_query = new WP_Query($zwsgr_data_render_args);

            $zwsgr_total_reviews = $zwsgr_accounts_query->found_posts;
            $zwsgr_total_rating = 0;

            $zwsgr_rating_map = array(
                'ONE'   => 1,
                'TWO'   => 2,
                'THREE' => 3,
                'FOUR'  => 4,
                'FIVE'  => 5,
            );

            // Loop through the reviews to sum the star ratings
            if ($zwsgr_accounts_query->have_posts()) {
                while ($zwsgr_accounts_query->have_posts()) {
                    $zwsgr_accounts_query->the_post();

                    $zwsgr_review_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
                    if (isset($zwsgr_rating_map[$zwsgr_review_rating])) {
                        $zwsgr_total_rating += $zwsgr_rating_map[$zwsgr_review_rating];
                    }

                }
                wp_reset_postdata();
            }

            $zwsgr_average_rating = ($zwsgr_total_reviews > 0) ? ($zwsgr_total_rating / $zwsgr_total_reviews) : 0;
            $zwsgr_average_rating = floor($zwsgr_average_rating * 10) / 10;

            $zwsgr_reviews_ratings = array(
                'reviews'  => $zwsgr_total_reviews,
                'ratings'  => $zwsgr_average_rating
            );

            return $zwsgr_reviews_ratings;

        }

        public function zwsgr_total_reviews($zwsgr_data_render_args) {

            $zwsgr_reviews_ratings = $this->zwsgr_get_reviews_ratings($zwsgr_data_render_args);

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
        

        public function zwsgr_average_ratings($zwsgr_data_render_args) {

            $zwsgr_reviews_ratings = $this->zwsgr_get_reviews_ratings($zwsgr_data_render_args);

            return'
            <div class="zwsgr-average-rating-card">
                <div class="zwsgr-card-icon orange">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="zwsgr-card-content">
                    <h3 class="zwsgr-card-title">' . esc_html__( 'Average Rating', 'zw-smart-google-reviews' ) . '</h3>
                    <p class="zwsgr-card-value">' . esc_html( $zwsgr_reviews_ratings['ratings'] ) . '</p> <!-- Ensure to define $average_rating in your function -->
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
                            <button class="zwsgr-filter-button active" data-filter="daily" data-type="rangeofdays">
                                ' . esc_html__( 'Daily', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <button class="zwsgr-filter-button" data-filter="weekly" data-type="rangeofdays">
                                ' . esc_html__( 'Weekly', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <button class="zwsgr-filter-button" data-filter="monthly" data-type="rangeofdays">
                                ' . esc_html__( 'Monthly', 'zw-smart-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwsgr-filter-item">
                            <input type="text" name="dates" value="" id="zwsgr-date-range-picker" class="zwsgr-filter-button" data-type="rangeofdate">
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

        public function zwsgr_top_reviews($zwsgr_data_render_args) {

            // Ensure the query limits the number of posts to 5
           $zwsgr_data_render_args['posts_per_page'] = 5;

            $zwsgr_accounts_query = new WP_Query($zwsgr_data_render_args);

            $output = '<div class="zwsgr-top-reviews-wrapper">
                <div class="zwsgr-header-container">
                    <h4>' . esc_html__('Top Reviews', 'zw-smart-google-reviews') . '</h4>
                </div>';

            if ($zwsgr_accounts_query->have_posts()) {
                
                while($zwsgr_accounts_query->have_posts()) {
                    $zwsgr_accounts_query->the_post();

                    $zwsgr_reviewer_name         = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
                    $zwsgr_review_comment        = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
                    $zwsgr_review_star_rating    = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
                    $zwsgr_review_published_date = get_the_date('F j, Y');
                    $zwsgr_post_date             = get_the_date('U');

                    return $zwsgr_reviewer_name;

                }

                wp_reset_postdata();

            }

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