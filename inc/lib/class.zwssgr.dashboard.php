<?php
/**
 * ZWSSGR_Dashboard Class
 *
 * Handles the dashboard functionality for Smart Showcase for Google Reviews plugin.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR_Dashboard' ) ) {

	/**
	 * The main ZWSSGR_Dashboard class
	 */
	class ZWSSGR_Dashboard {

        private static $instance = null;

		/**
		 * Constructor
		 */
		function __construct() {

            add_action('wp_ajax_zwssgr_gmb_dashboard_data_filter', array($this,'zwssgr_gmb_dashboard_data_filter'));
            add_action('wp_ajax_zwssgr_data_render', array($this,'zwssgr_data_render'));

		}

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwssgr_data_render() {

            // Ensure it's an AJAX request
            if (defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer( 'zwssgr_data_render', 'security' );

                $zwssgr_filter_data = isset( $_POST['zwssgr_filter_data'] ) && is_array( $_POST['zwssgr_filter_data'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['zwssgr_filter_data'] ) ) : [];

                
            } else {

                $zwssgr_filter_data = [
					'zwssgr_gmb_account_number'   => null,
					'zwssgr_gmb_account_location' => null,
					'zwssgr_range_filter_type'    => 'rangeofdays',
					'zwssgr_range_filter_data'    => 'monthly'
				];

            }

            $zwssgr_data_render_args = $this->zwssgr_data_render_query($zwssgr_filter_data);

            $zwssgr_data_render_output = '';

            $zwssgr_chart_data = $this->zwssgr_dynamic_chart_data($zwssgr_data_render_args);

            $zwssgr_data_render_output .= '<div id="render-dynamic" class="render-dynamic">
                <div class="zwgr-dashboard-body">'
                    . $this->zwssgr_total_reviews($zwssgr_data_render_args) .
                    $this->zwssgr_average_ratings($zwssgr_data_render_args) . 
                '</div>
                <div class="zwgr-dashboard-footer">'
                    . $this->zwssgr_reviews_statics_chart($zwssgr_chart_data) .
                    $this->zwssgr_top_reviews($zwssgr_data_render_args) .
                '</div>
            </div>';

            if (defined('DOING_AJAX') && DOING_AJAX) {

                wp_send_json_success([
                    'html' => $zwssgr_data_render_output, 
                    'zwssgr_chart_data' => $zwssgr_chart_data
                ]);

            } else {
                return $zwssgr_data_render_output;
            }

        }

        public function zwssgr_data_render_query($zwssgr_filter_data) {

            $zwssgr_data_render_args = array();

            $zwssgr_gmb_email            = get_option('zwssgr_gmb_email');

            // Sanitize data from user input
            $zwssgr_gmb_account_number   = isset($zwssgr_filter_data['zwssgr_gmb_account_number'])   ? sanitize_text_field($zwssgr_filter_data['zwssgr_gmb_account_number']) : '';
            $zwssgr_gmb_account_location = isset($zwssgr_filter_data['zwssgr_gmb_account_location']) ? sanitize_text_field($zwssgr_filter_data['zwssgr_gmb_account_location']) : '';
            $zwssgr_range_filter_type    = isset($zwssgr_filter_data['zwssgr_range_filter_type'])    ? sanitize_text_field($zwssgr_filter_data['zwssgr_range_filter_type']) : '';
            $zwssgr_range_filter_data    = isset($zwssgr_filter_data['zwssgr_range_filter_data'])    ? sanitize_text_field($zwssgr_filter_data['zwssgr_range_filter_data']) : '';

            // Set up the query arguments
            $zwssgr_data_render_args = array(
                'post_type'   => 'zwssgr_reviews',
                'fields'      => 'ids',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'zwssgr_gmb_email',
                        'value'   => $zwssgr_gmb_email,
                        'compare' => '=',
                    )
                ),
            );

            if (!empty($zwssgr_range_filter_data) && $zwssgr_range_filter_type == 'rangeofdays') {

                if ($zwssgr_range_filter_data == 'daily') {
                    $zwssgr_data_render_args['date_query'] = array(
                        array(
                            'after'     => 'today', // Posts from today
                            'inclusive' => true,
                        ),
                    );
                } elseif ($zwssgr_range_filter_data == 'weekly') {
                    $zwssgr_data_render_args['date_query'] = array(
                        array(
                            'after'     => '7 days ago',
                            'inclusive' => true,
                        ),
                    );
                } elseif ($zwssgr_range_filter_data == 'monthly') {
                    $zwssgr_data_render_args['date_query'] = array(
                        array(
                            'after'     => '30 days ago',
                            'inclusive' => true,
                        ),
                    );
                }

            } else if (!empty($zwssgr_range_filter_data) && $zwssgr_range_filter_type == 'rangeofdate') {

                $zwssgr_range_dates = explode(' - ', $zwssgr_range_filter_data);
                $zwssgr_start_date  = $zwssgr_range_dates[0]; 
                $zwssgr_end_date    = isset($zwssgr_range_dates[1]) ? $zwssgr_range_dates[1] : ''; 
                $zwssgr_start_date = gmdate('Y-m-d', strtotime($zwssgr_start_date));
                $zwssgr_end_date = gmdate('Y-m-d', strtotime($zwssgr_end_date));
                $zwssgr_data_render_args['date_query'] = array(
                    'relation' => 'AND',
                    array(
                        'after'     => $zwssgr_start_date,
                        'inclusive' => true,
                    ),
                    array(
                        'before'    => $zwssgr_end_date,
                        'inclusive' => true,
                    ),
                );
                
            }

            // Add the account filter only if it exists
            if (!empty($zwssgr_gmb_account_number)) {
                $zwssgr_data_render_args['meta_query'][] = array(
                    'key'     => 'zwssgr_account_number',
                    'value'   => $zwssgr_gmb_account_number,
                    'compare' => '=',
                );
            }

            // Add the location filter only if it exists
            if (!empty($zwssgr_gmb_account_location)) {
                $zwssgr_data_render_args['meta_query'][] = array(
                    'key'     => 'zwssgr_location_number',
                    'value'   => $zwssgr_gmb_account_location,
                    'compare' => '=',
                );
            }

            return $zwssgr_data_render_args;

        }

        public function zwssgr_get_reviews_ratings($zwssgr_data_render_args) {

            $zwssgr_total_reviews        = 0;
            $zwssgr_average_rating       = 0;

            $zwssgr_data_render_query = new WP_Query($zwssgr_data_render_args);

            $zwssgr_total_reviews = $zwssgr_data_render_query->found_posts;
            $zwssgr_total_rating = 0;

            $zwssgr_rating_map = array(
                'ONE'   => 1,
                'TWO'   => 2,
                'THREE' => 3,
                'FOUR'  => 4,
                'FIVE'  => 5,
            );

            // Loop through the reviews to sum the star ratings
            if ($zwssgr_data_render_query->have_posts()) {
                while ($zwssgr_data_render_query->have_posts()) {
                    $zwssgr_data_render_query->the_post();

                    $zwssgr_review_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
                    if (isset($zwssgr_rating_map[$zwssgr_review_rating])) {
                        $zwssgr_total_rating += $zwssgr_rating_map[$zwssgr_review_rating];
                    }

                }
                wp_reset_postdata();
            }

            $zwssgr_average_rating = ($zwssgr_total_reviews > 0) ? ($zwssgr_total_rating / $zwssgr_total_reviews) : 0;
            $zwssgr_average_rating = floor($zwssgr_average_rating * 10) / 10;

            $zwssgr_reviews_ratings = array(
                'reviews'  => $zwssgr_total_reviews,
                'ratings'  => $zwssgr_average_rating
            );

            return $zwssgr_reviews_ratings;

        }

        public function zwssgr_total_reviews($zwssgr_data_render_args) {

            $zwssgr_reviews_ratings = $this->zwssgr_get_reviews_ratings($zwssgr_data_render_args);

            return '
            <div class="zwssgr-data-card zwssgr-total-reviews-card">
                <div class="zwssgr-card-icon">
                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="72" height="72" rx="36" fill="#EDF8FA"/>
                    <path d="M48.3077 23H23.6923C22.7134 23.0013 21.7748 23.4727 21.0826 24.3108C20.3904 25.149 20.0011 26.2853 20 27.4706V39.3922C20.0011 40.5774 20.3904 41.7138 21.0826 42.5519C21.7748 43.39 22.7134 43.8615 23.6923 43.8628H31.798L35.1298 47.8967C35.2441 48.0351 35.3797 48.1449 35.529 48.2199C35.6783 48.2948 35.8384 48.3333 36 48.3333C36.1616 48.3333 36.3217 48.2948 36.471 48.2199C36.6203 48.1449 36.7559 48.0351 36.8702 47.8967L40.202 43.8628H48.3077C49.2866 43.8615 50.2252 43.39 50.9174 42.5519C51.6096 41.7138 51.9989 40.5774 52 39.3922V27.4706C51.9989 26.2853 51.6096 25.149 50.9174 24.3108C50.2252 23.4727 49.2866 23.0013 48.3077 23ZM45.8462 37.902H26.1538C25.8274 37.902 25.5144 37.745 25.2836 37.4655C25.0527 37.186 24.9231 36.807 24.9231 36.4118C24.9231 36.0165 25.0527 35.6375 25.2836 35.358C25.5144 35.0786 25.8274 34.9216 26.1538 34.9216H45.8462C46.1726 34.9216 46.4856 35.0786 46.7164 35.358C46.9473 35.6375 47.0769 36.0165 47.0769 36.4118C47.0769 36.807 46.9473 37.186 46.7164 37.4655C46.4856 37.745 46.1726 37.902 45.8462 37.902ZM45.8462 31.9412H26.1538C25.8274 31.9412 25.5144 31.7842 25.2836 31.5047C25.0527 31.2252 24.9231 30.8462 24.9231 30.451C24.9231 30.0558 25.0527 29.6767 25.2836 29.3973C25.5144 29.1178 25.8274 28.9608 26.1538 28.9608H45.8462C46.1726 28.9608 46.4856 29.1178 46.7164 29.3973C46.9473 29.6767 47.0769 30.0558 47.0769 30.451C47.0769 30.8462 46.9473 31.2252 46.7164 31.5047C46.4856 31.7842 46.1726 31.9412 45.8462 31.9412Z" fill="#0074A2"/>
                </svg>
                </div>
                <div class="zwssgr-card-content">
                    <h3 class="zwssgr-card-title">' . esc_html__( 'Total Reviews', 'smart-showcase-for-google-reviews' ) . '</h3>
                    <p class="zwssgr-card-value">' . esc_html( number_format( $zwssgr_reviews_ratings['reviews'] ) ) . '</p>
                </div>
            </div>';

        }
        

        public function zwssgr_average_ratings($zwssgr_data_render_args) {

            $zwssgr_reviews_ratings = $this->zwssgr_get_reviews_ratings($zwssgr_data_render_args);

            return'
            <div class="zwssgr-data-card zwssgr-average-rating-card">
                <div class="zwssgr-card-icon orange">
                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="72" height="72" rx="36" fill="#FCF7EB"/>
                    <path d="M49.5113 29.9421L41.9751 28.8015L38.6059 21.6867C38.3661 21.1804 37.995 20.7541 37.5348 20.456C37.0745 20.158 36.5433 20 36.0013 20C35.4594 20 34.9282 20.158 34.4679 20.456C34.0076 20.7541 33.6366 21.1804 33.3968 21.6867L30.0241 28.8015L22.4876 29.9421C21.9511 30.0232 21.4471 30.2591 21.0325 30.623C20.618 30.987 20.3096 31.4645 20.1421 32.0016C19.9746 32.5387 19.9548 33.114 20.0848 33.6622C20.2148 34.2105 20.4896 34.7098 20.8779 35.1039L26.3319 40.6411L25.0441 48.4624C24.9524 49.0189 25.012 49.5911 25.2162 50.1142C25.4204 50.6373 25.7611 51.0904 26.1996 51.4223C26.6382 51.7542 27.1572 51.9516 27.6979 51.9922C28.2385 52.0328 28.7793 51.9149 29.2591 51.652L36.0012 47.9607L42.7419 51.652C43.2216 51.9149 43.7624 52.0328 44.3031 51.9922C44.8438 51.9516 45.3627 51.7542 45.8013 51.4223C46.2399 51.0904 46.5805 50.6373 46.7848 50.1142C46.989 49.5911 47.0486 49.0189 46.9569 48.4624L45.669 40.6411L51.1233 35.1042C51.5113 34.71 51.7858 34.2106 51.9155 33.6624C52.0453 33.1142 52.0252 32.5391 51.8576 32.0022C51.69 31.4653 51.3815 30.988 50.967 30.6242C50.5525 30.2604 50.0477 30.0231 49.5113 29.9421Z" fill="#F08C3C"/>
                </svg>
                </div>
                <div class="zwssgr-card-content">
                    <h3 class="zwssgr-card-title">' . esc_html__( 'Average Rating', 'smart-showcase-for-google-reviews' ) . '</h3>
                    <p class="zwssgr-card-value">' . esc_html( $zwssgr_reviews_ratings['ratings'] ) . '</p> <!-- Ensure to define $average_rating in your function -->
                </div>
            </div>';

        }
        

        public function zwssgr_date_range_filter() {
            return '
            <div class="zwssgr-date-range-filter-wrapper">
                <!-- Title Section -->
                <div class="zwssgr-title-wrapper">
                    <h1 class="zwssgr-range-filter-title">
                        ' . esc_html__( 'Smart Showcase for Google Reviews', 'smart-showcase-for-google-reviews' ) . '
                    </h1>
                </div>

                <!-- Filters List Section -->
                <div class="zwssgr-filters-wrapper">
                    <ul class="zwssgr-filters-list">
                        <li class="zwssgr-filter-item filter-text">
                                ' . esc_html__( 'Filters', 'smart-showcase-for-google-reviews' ) . '
                        </li>
                        <li class="zwssgr-filter-item">
                            <button class="zwssgr-filter-button" data-filter="daily" data-type="rangeofdays">
                                ' . esc_html__( 'Daily', 'smart-showcase-for-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwssgr-filter-item">
                            <button class="zwssgr-filter-button" data-filter="weekly" data-type="rangeofdays">
                                ' . esc_html__( 'Weekly', 'smart-showcase-for-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwssgr-filter-item">
                            <button class="zwssgr-filter-button active" data-filter="monthly" data-type="rangeofdays">
                                ' . esc_html__( 'Monthly', 'smart-showcase-for-google-reviews' ) . '
                            </button>
                        </li>
                        <li class="zwssgr-filter-item">
                            <input type="text" name="dates" value="" id="zwssgr-date-range-picker" class="zwssgr-date-range-picker" data-type="rangeofdate">
                        </li>
                    </ul>
                </div> 
            </div>' . 
            $this->zwssgr_gmb_data_filter();
        }

        public function zwssgr_gmb_data_filter() {

            // Get the email from the WordPress options table
            $zwssgr_gmb_email = get_option('zwssgr_gmb_email');

            $zwssgr_request_data = get_posts(array(
                'post_type'      => 'zwssgr_request_data',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => array(
                    array(
                        'key'     => 'zwssgr_gmb_email',
                        'value'   => $zwssgr_gmb_email,
                        'compare' => '=',
                    ),
                ),
                'fields'         => 'ids',
            ));

            // Build the select dropdown
            $data_filter_output = '<div id="gmb-data-filter" class="gmb-data-filter">
                <select id="zwssgr-account-select" name="zwssgr_account" class="zwssgr-input-text zwssgr-account-select">
                    <option value="">Select an Account</option>';
                    foreach ($zwssgr_request_data as $zwssgr_widget_id) {
                        $zwssgr_account_number = get_post_meta($zwssgr_widget_id, 'zwssgr_account_number', true);
                        $zwssgr_account_name   = get_the_title($zwssgr_widget_id);
                        $data_filter_output .= '<option value="' . esc_attr($zwssgr_account_number) . '">' . esc_html($zwssgr_account_name) . '</option>';
                    }
                $data_filter_output .= '</select>
            </div>';

            return $data_filter_output;

        }        

        public function zwssgr_top_reviews($zwssgr_data_render_args) {

            // Ensure the query limits the number of posts to 5
            $zwssgr_data_render_args['posts_per_page'] = 5;

            // Add star rating filters without overwriting existing meta_query
            $zwssgr_star_rating_query = array(
                'relation' => 'OR',
                array(
                    'key'     => 'zwssgr_review_star_rating',
                    'value'   => 'FIVE',
                    'compare' => '=',
                ),
                array(
                    'key'     => 'zwssgr_review_star_rating',
                    'value'   => 'FOUR',
                    'compare' => '=',
                ),
                array(
                    'key'     => 'zwssgr_review_star_rating',
                    'value'   => 'THREE',
                    'compare' => '=',
                ),
                array(
                    'key'     => 'zwssgr_review_star_rating',
                    'value'   => 'TWO',
                    'compare' => '=',
                ),
                array(
                    'key'     => 'zwssgr_review_star_rating',
                    'value'   => 'ONE',
                    'compare' => '=',
                ),
            );

            // Merge the existing meta_query with the new star rating query
            if (isset($zwssgr_data_render_args['meta_query']) && is_array($zwssgr_data_render_args['meta_query'])) {
                $zwssgr_data_render_args['meta_query'][] = $zwssgr_star_rating_query;
            } else {
                $zwssgr_data_render_args['meta_query'] = array($zwssgr_star_rating_query);
            }

            // Add ordering by star rating and then by date
            $zwssgr_data_render_args['orderby'] = array(
                'meta_value' => 'ASC'
            );

            $zwssgr_data_render_args['meta_key'] = 'zwssgr_review_star_rating';

            $zwssgr_data_render_query = new WP_Query($zwssgr_data_render_args);

            $output = '<div class="zwssgr-reviews-wrapper zwssgr-flex-column">
                <div class="zwssgr-flex-inner-container">';
            
            // Start the output for top reviews
            $output .= '<div class="zwssgr-header-container">
                <h4>' . esc_html__('Top Reviews', 'smart-showcase-for-google-reviews') . '</h4>
            </div>';
            $plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
            // Check if there are reviews to display
            if ($zwssgr_data_render_query->have_posts()) {
                $output .= '<div class="zwssgr-reviews-container">';

                    while ($zwssgr_data_render_query->have_posts()) {
                        $zwssgr_data_render_query->the_post();
                        $zwssgr_review_id          = get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
                        $zwssgr_reviewer_name      = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
                        $zwssgr_review_content     = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
                        $zwssgr_review_star_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
                        $zwssgr_attachment_id      = get_post_thumbnail_id(get_the_ID());
                        $zwssgr_published_date     = get_the_date('F j, Y');
                        $zwssgr_post_date          = get_the_date('U');
                        $zwssgr_days_ago = floor((time() - $zwssgr_post_date) / (60 * 60 * 24));

                        $zwssgr_rating_map = [
                            'ONE'   => 1,
                            'TWO'   => 2,
                            'THREE' => 3,
                            'FOUR'  => 4,
                            'FIVE'  => 5,
                        ];

                        $zwssgr_numeric_rating = isset($zwssgr_rating_map[$zwssgr_review_star_rating]) ? $zwssgr_rating_map[$zwssgr_review_star_rating] : 0;

                        $zwssgr_gmb_reviewer_image_dir = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
                        $zwssgr_gmb_reviewer_image_uri = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';

                        // Generate stars HTML
                        $zwssgr_stars_html = '';
                        for ($i = 0; $i < 5; $i++) {
                            $zwssgr_stars_html .= $i < $zwssgr_numeric_rating 
                                ? '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.48826 1.02997C6.69837 0.607079 7.30163 0.60708 7.51174 1.02997L9.22774 4.48369C9.31094 4.65115 9.47082 4.76731 9.6558 4.7947L13.4708 5.35944C13.9379 5.4286 14.1243 6.00233 13.787 6.33284L11.0326 9.0321C10.8991 9.16299 10.838 9.35094 10.8691 9.53532L11.5109 13.3381C11.5895 13.8037 11.1014 14.1583 10.6829 13.9397L7.26456 12.1542C7.09881 12.0676 6.90119 12.0676 6.73544 12.1542L3.31713 13.9397C2.89857 14.1583 2.41053 13.8037 2.48911 13.3381L3.13089 9.53532C3.16201 9.35094 3.10094 9.16299 2.96738 9.0321L0.212968 6.33284C-0.124294 6.00233 0.0621234 5.4286 0.529244 5.35944L4.3442 4.7947C4.52918 4.76731 4.68906 4.65115 4.77226 4.48369L6.48826 1.02997Z" fill="#F6BB06"/>
                                </svg>' 
                                : '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.48826 1.02997C6.69837 0.607079 7.30163 0.60708 7.51174 1.02997L9.22774 4.48369C9.31094 4.65115 9.47082 4.76731 9.6558 4.7947L13.4708 5.35944C13.9379 5.4286 14.1243 6.00233 13.787 6.33284L11.0326 9.0321C10.8991 9.16299 10.838 9.35094 10.8691 9.53532L11.5109 13.3381C11.5895 13.8037 11.1014 14.1583 10.6829 13.9397L7.26456 12.1542C7.09881 12.0676 6.90119 12.0676 6.73544 12.1542L3.31713 13.9397C2.89857 14.1583 2.41053 13.8037 2.48911 13.3381L3.13089 9.53532C3.16201 9.35094 3.10094 9.16299 2.96738 9.0321L0.212968 6.33284C-0.124294 6.00233 0.0621234 5.4286 0.529244 5.35944L4.3442 4.7947C4.52918 4.76731 4.68906 4.65115 4.77226 4.48369L6.48826 1.02997Z" fill="#76879D"/>
                                </svg>';
                        }

                        $output .= '<div class="zwssgr-review-item">    
                            <div class="zwssgr-review-header">
                                <div class="zwssgr-profile">';

                                if (file_exists($zwssgr_gmb_reviewer_image_dir)) {
                                    $image_id = attachment_url_to_postid($zwssgr_gmb_reviewer_image_uri);
                                    if ($image_id) {
                                        $output .= wp_get_attachment_image($image_id, 'thumbnail', false, [
                                            'class' => 'fallback-user-dp',
                                        ]);
                                    } else {
                                        $output .= '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp">';
                                    }
                                } else {
                                    $fallback_image_id = attachment_url_to_postid(ZWSSGR_URL . '/assets/images/fallback-user-dp.svg');
                                    if ($fallback_image_id) {
                                        $output .= wp_get_attachment_image($fallback_image_id, 'thumbnail', false, [
                                            'class' => 'fallback-user-dp',
                                        ]);
                                    } else {
                                        $output .= '<img src="' . esc_url(ZWSSGR_URL . '/assets/images/fallback-user-dp.svg') . '" class="fallback-user-dp">';
                                    }
                                }

                        $output .= '</div>
                            <div class="zwssgr-review-info">
                                <h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>
                                <h5 class="zwssgr-date">
                                    <span>' . esc_html($zwssgr_published_date) . ' (' . esc_html($zwssgr_days_ago) . ' days ago)</span>
                                </h5>
                            </div>
                        </div>';
            
                        // Add stars if available
                        if (!empty($zwssgr_stars_html)) {
                            $output .= '<div class="zwssgr-rating">
                                '.$zwssgr_stars_html.'
                            </div>';
                        }
        
                        // Review content
                        if (!empty($zwssgr_review_content)) {
                            $output .= '<p class="zwssgr-content">' . esc_html($zwssgr_review_content) . '</p>';
                        }

                        $output .= '</div>';

                    }

                wp_reset_postdata();
        
                $output .= '</div>';

            } else {

                $output .= '<p>' . esc_html__('No reviews available.', 'smart-showcase-for-google-reviews') . '</p>';

            }

            $output .= '</div>
            </div>';
            
            return $output;

        }        
        
        public function zwssgr_reviews_statics_chart($zwssgr_chart_data) {
            return '<div class="zwssgr-flex-container zwssgr-flex-column">
                <div class="zwssgr-flex-inner-container">
                    <h4>' . 
                        esc_html__( 'Review Statistics Chart', 'smart-showcase-for-google-reviews' ) . 
                    '</h4>
                    <div class="zwssgr_outer_wrapper">
                        <div id="zwssgr_chart_wrapper" class="zwssgr_chart_wrapper">
                            <div class="zwssgr-dashboard-text">No enough data available</div>
                        </div>
                        <div id="zwsr_chart_legend_wrapper" class="zwsr_chart_legend_wrapper">
                            <div class="zwssgr_chart_legend">
                                <div class="marker zwssgr-chart-lengend-orange"></div>
                                <div class="guide">5 Star</div>
                            </div>
                            <div class="zwssgr_chart_legend">
                                <div class="marker zwssgr-chart-lengend-cian"></div>
                                <div class="guide">4 Star</div>
                            </div>
                            <div class="zwssgr_chart_legend">
                                <div class="marker zwssgr-chart-lengend-grey"></div>
                                <div class="guide">3 Star</div>
                            </div>
                            <div class="zwssgr_chart_legend">
                                <div class="marker zwssgr-chart-lengend-blue"></div>
                                <div class="guide">2 Star</div>
                            </div>
                            <div class="zwssgr_chart_legend">
                                <div class="marker zwssgr-chart-lengend-red"></div>
                                <div class="guide">1 Star</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        public function zwssgr_gmb_dashboard_data_filter() {

            // Ensure it's an AJAX request
            if (defined('DOING_AJAX') && DOING_AJAX) {
                check_ajax_referer('zwssgr_gmb_dashboard_filter', 'security');
            }

            $zwssgr_account_number = isset( $_POST['zwssgr_account_number'] ) ? sanitize_text_field( wp_unslash( $_POST['zwssgr_account_number'] ) ) : '';
        
            if (empty($zwssgr_account_number)) {
                wp_send_json_error('An account number is required to retrieve location data.');
                wp_die();
            }
        
            $zwssgr_request_data_id = get_posts(array(
                'post_type'      => 'zwssgr_request_data',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'meta_key'       => 'zwssgr_account_number',
                'meta_value'     => $zwssgr_account_number,
                'fields'         => 'ids',
            ))[0] ?? null;
        
            if (empty($zwssgr_request_data_id)) {
                wp_send_json_error('No locations found for this account.');
                wp_die();
            }
        
            $zwssgr_account_locations = get_post_meta($zwssgr_request_data_id, 'zwssgr_account_locations', true);
        
            $output = '';
        
            // Check if the custom field has a value
            if ($zwssgr_account_locations) {
                $output .= '<select id="zwssgr-location-select" name="zwssgr_location" class="zwssgr-input-text zwssgr-location-select">';
                $output .= '<option value="">Select a Location</option>';
                
                foreach ($zwssgr_account_locations as $zwssgr_account_location) {
                    // Use the title field for the location name
                    $location_title = $zwssgr_account_location['title'] ?? ''; // Ensure the title is retrieved
                    $zwssgr_account_location_id = $zwssgr_account_location['name'] ? ltrim(strrchr($zwssgr_account_location['name'], '/'), '/') : '';
                    $output .= '<option value="' . esc_attr($zwssgr_account_location_id) . '">' . esc_html($location_title) . '</option>';
                }
        
                $output .= '</select>';
            }
        
            wp_send_json_success($output);
        
            die();
        }

        public function zwssgr_dynamic_chart_data($zwssgr_data_render_args) {
        
            // Query the posts
            $zwssgr_query = new WP_Query($zwssgr_data_render_args);
        
            // Initialize ratings count array
            $zwssgr_ratings_count = [
                'FIVE' => 0,
                'FOUR' => 0,
                'THREE' => 0,
                'TWO' => 0,
                'ONE' => 0
            ];
        
            // Loop through the posts to count the ratings
            foreach ($zwssgr_query->posts as $post_id) {
                $zwssgr_rating = get_post_meta($post_id, 'zwssgr_review_star_rating', true);
                if (array_key_exists($zwssgr_rating, $zwssgr_ratings_count)) {
                    $zwssgr_ratings_count[$zwssgr_rating]++;
                }
            }
        
            // Return the chart data
            $zwssgr_chart_data = [
                ['5 Stars', $zwssgr_ratings_count['FIVE']],
                ['4 Stars', $zwssgr_ratings_count['FOUR']],
                ['3 Stars', $zwssgr_ratings_count['THREE']],
                ['2 Stars', $zwssgr_ratings_count['TWO']],
                ['1 Star', $zwssgr_ratings_count['ONE']],
            ];
        
            return $zwssgr_chart_data;
        }
                

	}

    new ZWSSGR_Dashboard();

}