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

            // Hook into WordPress 'init' action to schedule the cron job.
            add_action( 'init', [$this, 'zwsgr_cron_scheduler_callback'] );

            // Hook into the 'zwsgr_data_sync' action to trigger the data sync task.
            add_action( 'zwsgr_data_sync', [$this, 'zwsgr_data_sync_callback'] );
        
        }

        /**
         * Callback for the 'init' action to schedule the cron job.
         *
         * This method checks if the cron event for 'zwsgr_data_sync' is already scheduled.
         * If not, it schedules the cron event to run daily. The event will trigger the 
         * `zwsgr_data_sync` action that will be processed by the callback function.
         * 
         * @since 1.0.0
         */
        public function zwsgr_cron_scheduler_callback() {
            // Check if the 'zwsgr_data_sync' cron job is already scheduled
            if ( ! wp_next_scheduled( 'zwsgr_data_sync' ) ) {
                // Schedule the 'zwsgr_data_sync' event to run daily
                wp_schedule_event( time(), 'daily', 'zwsgr_data_sync' );
                error_log( 'Scheduled zwsgr_data_sync cron job' );
            } else {
                // Log if the cron job is already scheduled
                error_log( 'zwsgr_data_sync cron job already scheduled' );
            }
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

            $this->client->zwsgr_fetch_gmb_data(false, false, 'zwsgr_gmb_reviews', '111552359828900887169', '2718280351551476568');
            $this->client->zwsgr_fetch_gmb_data(false, false, 'zwsgr_gmb_reviews', '111552359828900887169', '17762185678414963374');

        }

    }

    // Instantiate the ZWSGR_Cron_Scheduler class to ensure cron jobs are scheduled.
    new ZWSGR_Cron_Scheduler();

}