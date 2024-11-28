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

        public function zwsgr_total_reviews() {
            // Fetch the total number of published reviews for the custom post type 'zwsgr_reviews'
            $zwsgr_reviews_count = wp_count_posts('zwsgr_reviews')->publish;
            // return $zwsgr_reviews_count; 
        
            // Return HTML with the total reviews count dynamically
            return '
            <div class="zwsgr-total-reviews-card">
                <div class="zwsgr-card-icon">
                    <span class="dashicons dashicons-format-chat"></span> <!-- Icon for Reviews -->
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
                <div class="zwsgr-card-icon orange">
                    <span class="dashicons dashicons-star-filled"></span>
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
            </div>';
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

	}

    new ZWSGR_Dashboard();

}