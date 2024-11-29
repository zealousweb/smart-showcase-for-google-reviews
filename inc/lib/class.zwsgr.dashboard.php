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
            add_action( 'admin_init', array( $this, 'action__admin_init' ) );
		}

        function action__admin_init() 
		{
            // Enqueue Moment.js (required by daterangepicker)
            wp_enqueue_script( ZWSGR_PREFIX . '-moment-min-js', ZWSGR_URL . 'assets/js/moment.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

            // Enqueue Daterangepicker JS
            wp_enqueue_script( ZWSGR_PREFIX . '-daterangepicker-min-js', ZWSGR_URL . 'assets/js/daterangepicker.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

            // Enqueue Daterangepicker CSS
            wp_enqueue_style( ZWSGR_PREFIX . '-daterangepicker-css', ZWSGR_URL . 'assets/css/daterangepicker.css', array(), ZWSGR_VERSION );

		}

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_data_render($zwsgr_filter_data) {
            echo '<div class="zwgr-dashboard-body">'
                . $this->zwsgr_total_reviews($zwsgr_filter_data) .
                $this->zwsgr_average_ratings($zwsgr_filter_data) .
            '</div>
            <div class="zwgr-dashboard-footer">'
                . $this->zwsgr_reviews_statics_chart($zwsgr_filter_data) .
                $this->zwsgr_top_reviews($zwsgr_filter_data) .
            '</div>';
        }

        public function zwsgr_total_reviews($zwsgr_filter_data) {

            // Fetch the total number of published reviews for the custom post type 'zwsgr_reviews'
            $zwsgr_reviews_count = wp_count_posts('zwsgr_reviews')->publish;
            // return $zwsgr_reviews_count; 
        
            // Return HTML with the total reviews count dynamically
            return '
            <div class="zwsgr-total-reviews-card">
                <div class="zwsgr-card-icon">
                    <span class="total-review-icon">
                        <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="72" height="72" rx="36" fill="#EDF8FA"/>
                        <path d="M48.3077 23H23.6923C22.7134 23.0013 21.7748 23.4727 21.0826 24.3108C20.3904 25.149 20.0011 26.2853 20 27.4706V39.3922C20.0011 40.5774 20.3904 41.7138 21.0826 42.5519C21.7748 43.39 22.7134 43.8615 23.6923 43.8628H31.798L35.1298 47.8967C35.2441 48.0351 35.3797 48.1449 35.529 48.2199C35.6783 48.2948 35.8384 48.3333 36 48.3333C36.1616 48.3333 36.3217 48.2948 36.471 48.2199C36.6203 48.1449 36.7559 48.0351 36.8702 47.8967L40.202 43.8628H48.3077C49.2866 43.8615 50.2252 43.39 50.9174 42.5519C51.6096 41.7138 51.9989 40.5774 52 39.3922V27.4706C51.9989 26.2853 51.6096 25.149 50.9174 24.3108C50.2252 23.4727 49.2866 23.0013 48.3077 23ZM45.8462 37.902H26.1538C25.8274 37.902 25.5144 37.745 25.2836 37.4655C25.0527 37.186 24.9231 36.807 24.9231 36.4118C24.9231 36.0165 25.0527 35.6375 25.2836 35.358C25.5144 35.0786 25.8274 34.9216 26.1538 34.9216H45.8462C46.1726 34.9216 46.4856 35.0786 46.7164 35.358C46.9473 35.6375 47.0769 36.0165 47.0769 36.4118C47.0769 36.807 46.9473 37.186 46.7164 37.4655C46.4856 37.745 46.1726 37.902 45.8462 37.902ZM45.8462 31.9412H26.1538C25.8274 31.9412 25.5144 31.7842 25.2836 31.5047C25.0527 31.2252 24.9231 30.8462 24.9231 30.451C24.9231 30.0558 25.0527 29.6767 25.2836 29.3973C25.5144 29.1178 25.8274 28.9608 26.1538 28.9608H45.8462C46.1726 28.9608 46.4856 29.1178 46.7164 29.3973C46.9473 29.6767 47.0769 30.0558 47.0769 30.451C47.0769 30.8462 46.9473 31.2252 46.7164 31.5047C46.4856 31.7842 46.1726 31.9412 45.8462 31.9412Z" fill="#0074A2"/>
                        </svg>
                    </span>
                </div>
                <div class="zwsgr-card-content">
                    <h3 class="zwsgr-card-title">' . esc_html__( 'Total Reviews', 'zw-smart-google-reviews' ) . '</h3>
                    <p class="zwsgr-card-value">' . esc_html( number_format( $zwsgr_reviews_count ) ) . '</p> <!-- Display Review Count -->
                </div>
            </div>';

        }
        

        public function zwsgr_average_ratings() {
            $zwsgr_reviews_count = wp_count_posts('zwsgr_reviews')->publish;
            // return $zwsgr_reviews_count; 

            return'
            <div class="zwsgr-average-rating-card">
                <div class="zwsgr-card-icon">
                    <span class="star-filled">
                        <svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="72" height="72" rx="36" fill="#FCF7EB"/>
                        <path d="M49.5113 29.9421L41.9751 28.8015L38.6059 21.6867C38.3661 21.1804 37.995 20.7541 37.5348 20.456C37.0745 20.158 36.5433 20 36.0013 20C35.4594 20 34.9282 20.158 34.4679 20.456C34.0076 20.7541 33.6366 21.1804 33.3968 21.6867L30.0241 28.8015L22.4876 29.9421C21.9511 30.0232 21.4471 30.2591 21.0325 30.623C20.618 30.987 20.3096 31.4645 20.1421 32.0016C19.9746 32.5387 19.9548 33.114 20.0848 33.6622C20.2148 34.2105 20.4896 34.7098 20.8779 35.1039L26.3319 40.6411L25.0441 48.4624C24.9524 49.0189 25.012 49.5911 25.2162 50.1142C25.4204 50.6373 25.7611 51.0904 26.1996 51.4223C26.6382 51.7542 27.1572 51.9516 27.6979 51.9922C28.2385 52.0328 28.7793 51.9149 29.2591 51.652L36.0012 47.9607L42.7419 51.652C43.2216 51.9149 43.7624 52.0328 44.3031 51.9922C44.8438 51.9516 45.3627 51.7542 45.8013 51.4223C46.2399 51.0904 46.5805 50.6373 46.7848 50.1142C46.989 49.5911 47.0486 49.0189 46.9569 48.4624L45.669 40.6411L51.1233 35.1042C51.5113 34.71 51.7858 34.2106 51.9155 33.6624C52.0453 33.1142 52.0252 32.5391 51.8576 32.0022C51.69 31.4653 51.3815 30.988 50.967 30.6242C50.5525 30.2604 50.0477 30.0231 49.5113 29.9421Z" fill="#F08C3C"/>
                        </svg>
                    </span>
                </div>
                <div class="zwsgr-card-content">
                    <h3 class="zwsgr-card-title">' . esc_html__( 'Average Rating', 'zw-smart-google-reviews' ) . '</h3>
                    <p class="zwsgr-card-value">' . esc_html( number_format( $zwsgr_reviews_count ) ) . '</p> <!-- Ensure to define $average_rating in your function -->
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

            $zwsgr_gmb_data_args = [
                'post_type'      => 'zwsgr_request_data',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => [
                    [
                        'key'     => 'zwsgr_gmb_email',
                        'value'   => $zwsgr_gmb_email,
                        'compare' => '='
                    ]
                ]
            ];

            $zwsgr_gmb_data_query = new WP_Query($zwsgr_gmb_data_args);

            // Initialize output
            $output = '<div class="gmb-data-filter">';

            // Initialize array for unique accounts and their locations
            $all_accounts = [];

            if ($zwsgr_gmb_data_query->have_posts()) {
                while ($zwsgr_gmb_data_query->have_posts()) {
                    $zwsgr_gmb_data_query->the_post();

                    $zwsgr_widget_id = get_the_ID();

                    // Fetch meta data for each post
                    $zwsgr_account_number = get_post_meta($zwsgr_widget_id, 'zwsgr_account_number', true);
                    $zwsgr_account_name = get_the_title(); // Use post title as account name

                    // Add account to array if it's not already present
                    if (!array_key_exists($zwsgr_account_number, $all_accounts)) {
                        $all_accounts[$zwsgr_account_number] = [
                            'account_name' => $zwsgr_account_name,
                            'locations'    => []
                        ];
                    }

                    // Fetch location data and check its type
                    $zwsgr_account_location = get_post_meta($zwsgr_widget_id, 'zwsgr_account_locations', true);

                    // Ensure locations are an array
                    if (is_array($zwsgr_account_location)) {
                        $locations = $zwsgr_account_location;
                    } elseif (is_string($zwsgr_account_location)) {
                        $locations = unserialize($zwsgr_account_location); // Fallback in case data is serialized
                    } else {
                        $locations = [];
                    }

                    // Add locations to the account in the array
                    if (!empty($locations)) {
                        foreach ($locations as $location) {
                            $all_accounts[$zwsgr_account_number]['locations'][] = $location;
                        }
                    }
                }

                wp_reset_postdata(); // Reset post data after custom query
            } else {
                $output .= '<p>No GMB Data Found.</p>';
            }

            // Add dynamic accounts dropdown outside the loop
            $output .= '<div class="gmb-dropdowns-container" style="display: flex; gap: 20px;">'; // Use flexbox for parallel layout
            $output .= '<div class="gmb-accounts-dropdown" style="flex: 1;">'; // Flex for equal distribution
            $output .= '<h3>Select an Account</h3>';
            $output .= '<select id="zwsgr-account-select-all" name="zwsgr_account_all" class="zwsgr-input-text">';
            $output .= '<option value="">Select an Account</option>';
            foreach ($all_accounts as $account_number => $account_data) {
                $output .= '<option value="' . esc_attr($account_number) . '">' . esc_html($account_data['account_name']) . '</option>';
            }
            $output .= '</select>';
            $output .= '</div>';

            // Add dynamic locations dropdown for each account
            $output .= '<div class="gmb-locations-dropdown" style="flex: 1; display:none;">'; // Initially hide the location dropdown
            $output .= '<h3>Select a Location</h3>';
            $output .= '<select id="zwsgr-location-select" name="zwsgr_location" class="zwsgr-input-text">';
            $output .= '<option value="">Select a Location</option>';

            // Loop through each account's locations
            foreach ($all_accounts as $account_number => $account_data) {
                if (!empty($account_data['locations'])) {
                    foreach ($account_data['locations'] as $location) {
                        $value = esc_attr($location['id'] ?? '');
                        $name = esc_html($location['name'] ?? 'Unknown Location');
                        $output .= '<option data-account="' . esc_attr($account_number) . '" value="' . $value . '">' . $name . '</option>';
                    }
                }
            }

            $output .= '</select>';
            $output .= '</div>';
            $output .= '</div>'; // Close the flex container

            $output .= '</div>';

            return $output;
        }

        public function zwsgr_top_reviews() {
            // Query to fetch top 5 reviews (order by rating or however desired)
            $args = array(
                'post_type'      => ZWSGR_POST_REVIEW_TYPE,
                'posts_per_page' => 5,
                'orderby'        => 'meta_value_num', // Assuming you store rating in a custom field
                'order'          => 'DESC',
            );
        
            $reviews_query = new WP_Query($args);
            $plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
            
            $output = '<div class="zwsgr-reviews-wrapper">';
        
            // Start the output for top reviews
            $output .= '<div class="zwsgr-header-container">
                            <h4>' . esc_html__('Top Reviews', 'zw-smart-google-reviews') . '</h4>
                        </div>';
        
            // Check if there are reviews to display
            if ($reviews_query->have_posts()) :
                $output .= '<div class="zwsgr-reviews-container">';
        
                while ($reviews_query->have_posts()) : $reviews_query->the_post();
                    $zwsgr_reviewer_name = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
                    $zwsgr_review_content = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
                    $zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
                    $published_date = get_the_date('F j, Y');
                    $post_date = get_the_date('U');
                    $days_ago = floor((time() - $post_date) / (60 * 60 * 24));
        
                    // Map textual rating to numeric values
                    $rating_map = [
                        'ONE'   => 1,
                        'TWO'   => 2,
                        'THREE' => 3,
                        'FOUR'  => 4,
                        'FIVE'  => 5,
                    ];
        
                    // Convert the textual rating to numeric
                    $numeric_rating = isset($rating_map[$zwsgr_review_star_rating]) ? $rating_map[$zwsgr_review_star_rating] : 0;
        
                    // Generate stars HTML
                    $stars_html = '';
                    for ($i = 0; $i < 5; $i++) {
                        $stars_html .= $i < $numeric_rating 
                            ? '<span class="zwsgr-star filled">★</span>' 
                            : '<span class="zwsgr-star">☆</span>';
                    }
                
                    $output .= '<div class="zwsgr-review-item">
                                    <div class="zwsgr-review-header">
                                        <div class="zwsgr-profile">
                                            <img src="' . esc_url($plugin_dir_path . 'assets/images/testi-pic.png') . '" alt="Reviewer">
                                        </div>
                                        <div class="zwsgr-review-info">
                                            <h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>
                                            <h5 class="zwsgr-date">
                                                <span>' . esc_html($published_date) . ' (' . esc_html($days_ago) . ' days ago)</span>
                                            </h5>
                                        </div>
                                        <div class="zwsgr-google-icon">
                                            <img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">
                                        </div>
                                    </div>';
        
                    // Add stars if available
                    if (!empty($stars_html)) {
                        $output .= '<div class="zwsgr-rating">' . $stars_html . '</div>';
                    }
        
                    // Review content
                    if (!empty($zwsgr_review_content)) {
                        $output .= '<p class="zwsgr-content">' . esc_html($zwsgr_review_content) . '</p>';
                    }
        
                    $output .= '</div>'; // Close review item
                endwhile;
                $output .= '</div>'; // Close reviews container
            else :
                $output .= '<p>' . esc_html__('No reviews available.', 'zw-smart-google-reviews') . '</p>';
            endif;
            
            wp_reset_postdata();
            
            $output .= '</div>'; // Close reviews wrapper
            return $output;
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

	}

    new ZWSGR_Dashboard();

}