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

		}

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_total_reviews() {
            $zwsgr_reviews_count = wp_count_posts('zwsgr_reviews')->publish;
            return $zwsgr_reviews_count;
        }

        public function zwsgr_average_ratings() {
            $zwsgr_reviews_count = wp_count_posts('zwsgr_reviews')->publish;
            return $zwsgr_reviews_count;
        }

        public function zwsgr_date_range_filter() {
            return '<div class="zwsgr-range-filter-wrapper">
                <h1 class="zwsgr-range-filter-title">
                    ' . esc_html__( 'Date Range Filter', 'zw-smart-google-reviews' ) . '
                </h1>
                <ul class="zwsgr-filters-list">
                    <li class="zwsgr-filter-item">
                        ' . esc_html__( 'Daily', 'zw-smart-google-reviews' ) . '
                    </li>
                    <li class="zwsgr-filter-item">
                        ' . esc_html__( 'Weekly', 'zw-smart-google-reviews' ) . '
                    </li>
                    <li class="zwsgr-filter-item">
                        ' . esc_html__( 'Monthly', 'zw-smart-google-reviews' ) . '
                    </li>
                    <li class="zwsgr-filter-item">
                        ' . esc_html__( 'Custom', 'zw-smart-google-reviews' ) . '
                    </li>
                </ul>
            </div>
            <div class="zwsgr-separator"></div>';
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