<?php
/**
 * ZWSGR_Cron_Scheduler Class
 *
 * This class handles the scheduling and execution of WordPress cron jobs for the 
 * Smart Google Reviews plugin. It schedules the `zwsgr_data_sync` event to run 
 * daily and processes the corresponding callback for data synchronization.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once( ZWSGR_DIR . '/inc/lib/zwsgr-batch-processing/class.' . ZWSGR_PREFIX . '.zqm.php' );

if ( !class_exists( 'ZWSGR_Cron_Scheduler' ) ) {

    /**
     * ZWSGR_Cron_Scheduler Class
     *
     * This class is responsible for managing the cron job for syncing Google Reviews 
     * data on a daily basis. It ensures that the cron event is scheduled and provides 
     * the functionality for executing the scheduled task.
     */
    class ZWSGR_Cron_Scheduler {

        private $client;

        /**
         * Constructor for the ZWSGR_Cron_Scheduler class.
         *
         * Hooks into WordPress actions to register and execute cron events.
         * 
         * - Hooks into 'init' to schedule the cron job.
         * - Hooks into 'zwsgr_data_sync' to trigger the data synchronization task.
         */
        function __construct() {

            $this->client = new Zwsgr_Queue_Manager();

            add_action( 'cron_schedules', [$this, 'zwsgr_add_custom_cron_schedules'] );

            // Hook into 'init' to set the default cron schedule if no option is selected
            add_action( 'admin_init', [$this, 'zwsgr_set_default_cron_schedule'] );

            // Update cron schedule on option change
            add_action( 'update_option_zwsgr_sync_reviews', [ $this, 'zwsgr_sync_reviews_scheduler_callback' ], 10, 2 );

            // Hook into the 'zwsgr_data_sync' action to trigger the data sync task.
            add_action( 'zwsgr_data_sync', [$this, 'zwsgr_data_sync_callback'] );
        
        }

        /**
         * Adds custom 'monthly' and 'weekly' cron schedule intervals to WordPress.
         *
         * This function hooks into the 'cron_schedules' filter to add custom
         * schedule intervals for running cron jobs once every month (approximately
         * 30 days) and once every week.
         *
         * @param array $schedules Existing cron schedules.
         * @return array Modified cron schedules with the added 'monthly' and 'weekly' intervals.
         */
        function zwsgr_add_custom_cron_schedules( $zwsgr_cron_schedules ) {

            // Add a custom 'monthly' interval with a 30-day frequency
            $zwsgr_cron_schedules['monthly'] = array(
                'interval' => 30 * DAY_IN_SECONDS,
                'display'  => __( 'Once Monthly' ), 
            );

            // Add a custom 'weekly' interval with a 7-day frequency
            $zwsgr_cron_schedules['weekly'] = array(
                'interval' => 7 * DAY_IN_SECONDS,
                'display'  => __( 'Once Weekly' ),
            );

            // Return the modified list of schedules
            return $zwsgr_cron_schedules;
        }

         /**
         * Sets the default cron schedule to 'monthly' if no option is selected already.
         * This method checks the value of the 'zwsgr_sync_reviews' option and schedules 
         * the cron job accordingly.
         *
         * @since 1.0.0
         */
        public function zwsgr_set_default_cron_schedule() {

            $zwsgr_new_frequency = get_option( 'zwsgr_sync_reviews');

            if ( empty( $zwsgr_new_frequency ) ) {
                update_option( 'zwsgr_sync_reviews', 'monthly' );
            }

            $this->zwsgr_sync_reviews_scheduler_callback( '', $zwsgr_new_frequency );

        }

        /**
         * Callback for the 'init' action to schedule the cron job based on the 
         * 'zwsgr_sync_reviews' option.
         *
         * This method checks the value of the 'zwsgr_sync_reviews' option and schedules 
         * the cron job accordingly. If no valid value is set, it does not schedule the cron.
         * 
         * @since 1.0.0
         */
        public function zwsgr_sync_reviews_scheduler_callback($zwsgr_old_frequency, $zwsgr_new_frequency) {

            // Ensure the new frequency is valid
            if ( !in_array( $zwsgr_new_frequency, ['daily', 'weekly', 'monthly'], true ) ) {
                error_log( "Invalid frequency: $zwsgr_new_frequency" );
                return;
            }

            // Check if a cron job is already scheduled
            if ( wp_next_scheduled( 'zwsgr_data_sync' ) ) {
                // If the old frequency exists, clear the existing cron event
                wp_clear_scheduled_hook( 'zwsgr_data_sync' );
                error_log( "Existing cron cleared with old frequency: $zwsgr_old_frequency" );
            }

             // Calculate the start time based on the selected frequency
            $zwsgr_frequency_to_time = [
                'daily' => 'tomorrow midnight',
                'weekly' => 'next week midnight',
                'monthly' => 'first day of next month midnight',
            ];

            $zwsgr_start_time = strtotime( $zwsgr_frequency_to_time[ $zwsgr_new_frequency ] );

            // Schedule a new cron event with the new frequency
            wp_schedule_event( $zwsgr_start_time, $zwsgr_new_frequency, 'zwsgr_data_sync' );
            error_log( "New cron scheduled with frequency: $zwsgr_new_frequency, starting at: " . date( 'Y-m-d H:i:s', $zwsgr_start_time ) );

        }


        /**
         * Callback function for the 'zwsgr_data_sync' cron event.
         *
         * This function is executed when the scheduled cron event triggers. In this 
         * case, it is used to handle data synchronization tasks for Google Reviews.
         * 
         * @since 1.0.0
         */
        public function zwsgr_data_sync_callback() {

            // Get the email value from the WordPress options
            $zwsgr_gmb_email = get_option('zwsgr_gmb_email'); 

            // Set up WP_Query arguments
            $zwsgr_data_widgets_args = [
                'post_type'      => 'zwsgr_data_widget',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => [
                    [
                        'key'     => 'zwsgr_gmb_email',
                        'value'   => $zwsgr_gmb_email,
                        'compare' => '=',
                    ],
                ],
            ];

            // Execute the query
            $zwsgr_widget_query = new WP_Query($zwsgr_data_widgets_args);

            // Check if posts were found
            if (!$zwsgr_widget_query->have_posts()) {
                return; // No posts to process
            }

            if ($zwsgr_widget_query->have_posts()) {
                while ($zwsgr_widget_query->have_posts()) {
                    $zwsgr_widget_query->the_post();

                    $zwsgr_widget_id       = get_the_ID();
                    $zwsgr_account_number  = get_post_meta($zwsgr_widget_id, 'zwsgr_account_number', true);
                    $zwsgr_location_number = get_post_meta($zwsgr_widget_id, 'zwsgr_location_number', true);

                    // Validate account and location numbers
                    if (!$zwsgr_account_number || empty($zwsgr_location_number)) {
                        continue;
                    }

                    $is_data_sync = $this->client->zwsgr_fetch_gmb_data(false, false, 'zwsgr_gmb_reviews', $zwsgr_account_number, $zwsgr_location_number);

                    if (!$is_data_sync) {
                        error_log('Data sync for widget:' . $zwsgr_widget_id . 'has been successfully processed');
                    } else {
                        error_log('There was an error while Data sync for widget:' . $zwsgr_widget_id);
                    }
                    
                }
                wp_reset_postdata();
            } else {
                return;
            }

        }

    }

    // Instantiate the ZWSGR_Cron_Scheduler class to ensure cron jobs are scheduled.
    new ZWSGR_Cron_Scheduler();

}