<?php
/**
 * ZWSSGR_Cron_Scheduler Class
 *
 * This class handles the scheduling and execution of WordPress cron jobs for the 
 * Smart Showcase for Google Reviews plugin. It schedules the `zwssgr_data_sync` event to run 
 * daily and processes the corresponding callback for data synchronization.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once( ZWSSGR_DIR . '/inc/lib/zwssgr-batch-processing/class.' . ZWSSGR_PREFIX . '.zqm.php' );

if ( !class_exists( 'ZWSSGR_Cron_Scheduler' ) ) {

    /**
     * ZWSSGR_Cron_Scheduler Class
     *
     * This class is responsible for managing the cron job for syncing Google Reviews 
     * data on a daily basis. It ensures that the cron event is scheduled and provides 
     * the functionality for executing the scheduled task.
     */
    class ZWSSGR_Cron_Scheduler {

        private $client;

        /**
         * Constructor for the ZWSSGR_Cron_Scheduler class.
         *
         * Hooks into WordPress actions to register and execute cron events.
         * 
         * - Hooks into 'init' to schedule the cron job.
         * - Hooks into 'zwssgr_data_sync' to trigger the data synchronization task.
         */
        function __construct() {

            $this->client = new zwssgr_Queue_Manager();

            add_action( 'cron_schedules', [$this, 'zwssgr_add_custom_cron_schedules'] );

            // Hook into 'init' to set the default cron schedule if no option is selected
            add_action( 'admin_init', [$this, 'zwssgr_set_default_cron_schedule'] );

            // Update cron schedule on option change
            add_action( 'update_option_zwssgr_sync_reviews', [ $this, 'zwssgr_sync_reviews_scheduler_callback' ], 10, 2 );

            // Hook into the 'zwssgr_data_sync' action to trigger the data sync task.
            add_action( 'zwssgr_data_sync', [$this, 'zwssgr_data_sync_callback'] );
        
        }

        /**
		 * Custom log function for debugging.
		 *
		 * @param string $message The message to log.
		 */
		function zwssgr_debug_function( $message ) {
			// Define the custom log directory path.
			$log_dir = WP_CONTENT_DIR . '/plugins/smart-showcase-for-google-reviews'; // wp-content/plugins/smart-showcase-for-google-reviews
		
			// Define the log file path.
			$log_file = $log_dir . '/smart-showcase-for-google-reviews-debug.log';
		
			// Check if the directory exists, if not create it.
			if ( ! file_exists( $log_dir ) ) {
				wp_mkdir_p( $log_dir );
			}
		
			// Initialize the WP_Filesystem.
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		
			global $wp_filesystem;
		
			// Format the log entry with UTC timestamp using gmdate().
			$log_entry = sprintf( "[%s] %s\n", gmdate( 'Y-m-d H:i:s' ), $message );
		
			// Write the log entry to the file using WP_Filesystem.
			if ( $wp_filesystem->exists( $log_file ) || $wp_filesystem->put_contents( $log_file, $log_entry, FS_CHMOD_FILE ) ) {
				$wp_filesystem->put_contents( $log_file, $log_entry, FS_CHMOD_FILE );
			}
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
        function zwssgr_add_custom_cron_schedules( $zwssgr_cron_schedules ) {

            // Add a custom 'monthly' interval with a 30-day frequency
            $zwssgr_cron_schedules['monthly'] = array(
                'interval' => 30 * DAY_IN_SECONDS,
                'display'  => __( 'Once Monthly' , 'smart-showcase-for-google-reviews' ), 
            );

            // Add a custom 'weekly' interval with a 7-day frequency
            $zwssgr_cron_schedules['weekly'] = array(
                'interval' => 7 * DAY_IN_SECONDS,
                'display'  => __( 'Once Weekly', 'smart-showcase-for-google-reviews' ),
            );

            // Return the modified list of schedules
            return $zwssgr_cron_schedules;
        }

         /**
         * Sets the default cron schedule to 'monthly' if no option is selected already.
         * This method checks the value of the 'zwssgr_sync_reviews' option and schedules 
         * the cron job accordingly.
         *
         * @since 1.0.0
         */
        public function zwssgr_set_default_cron_schedule() {

            $zwssgr_new_frequency = get_option( 'zwssgr_sync_reviews');

            if ( empty( $zwssgr_new_frequency ) ) {
                update_option( 'zwssgr_sync_reviews', 'monthly' );
            }

            $this->zwssgr_sync_reviews_scheduler_callback( '', $zwssgr_new_frequency );

        }

        /**
         * Callback for the 'init' action to schedule the cron job based on the 
         * 'zwssgr_sync_reviews' option.
         *
         * This method checks the value of the 'zwssgr_sync_reviews' option and schedules 
         * the cron job accordingly. If no valid value is set, it does not schedule the cron.
         * 
         * @since 1.0.0
         */
        public function zwssgr_sync_reviews_scheduler_callback($zwssgr_old_frequency, $zwssgr_new_frequency) {

            // Ensure the new frequency is valid
            if ( !in_array( $zwssgr_new_frequency, ['daily', 'weekly', 'monthly'], true ) ) {
                $this->zwssgr_debug_function( "Invalid frequency: $zwssgr_new_frequency" );
                return;
            }

            // Check if a cron job is already scheduled
            if ( wp_next_scheduled( 'zwssgr_data_sync' ) ) {
                // If the old frequency exists, clear the existing cron event
                wp_clear_scheduled_hook( 'zwssgr_data_sync' );
                $this->zwssgr_debug_function( "Existing cron cleared with old frequency: $zwssgr_old_frequency" );
            }

             // Calculate the start time based on the selected frequency
            $zwssgr_frequency_to_time = [
                'daily' => 'tomorrow midnight',
                'weekly' => 'next week midnight',
                'monthly' => 'first day of next month midnight',
            ];

            $zwssgr_start_time = strtotime( $zwssgr_frequency_to_time[ $zwssgr_new_frequency ] );

            // Schedule a new cron event with the new frequency
            wp_schedule_event( $zwssgr_start_time, $zwssgr_new_frequency, 'zwssgr_data_sync' );
            $this->zwssgr_debug_function( "New cron scheduled with frequency: $zwssgr_new_frequency, starting at: " . gmdate( 'Y-m-d H:i:s', $zwssgr_start_time ) );

        }


        /**
         * Callback function for the 'zwssgr_data_sync' cron event.
         *
         * This function is executed when the scheduled cron event triggers. In this 
         * case, it is used to handle data synchronization tasks for Google Reviews.
         * 
         * @since 1.0.0
         */
        public function zwssgr_data_sync_callback() {

            // Set up WP_Query arguments
            $zwssgr_data_widgets_args = [
                'post_type'      => 'zwssgr_data_widget',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ];

            // Execute the query
            $zwssgr_widget_query = new WP_Query($zwssgr_data_widgets_args);

            // Check if posts were found
            if (!$zwssgr_widget_query->have_posts()) {
                return; // No posts to process
            }

            if ($zwssgr_widget_query->have_posts()) {
                while ($zwssgr_widget_query->have_posts()) {
                    $zwssgr_widget_query->the_post();

                    $zwssgr_widget_id       = get_the_ID();
                    $zwssgr_account_number  = get_post_meta($zwssgr_widget_id, 'zwssgr_account_number', true);
                    $zwssgr_location_number = get_post_meta($zwssgr_widget_id, 'zwssgr_location_number', true);

                    // Validate account and location numbers
                    if (!$zwssgr_account_number || empty($zwssgr_location_number)) {
                        continue;
                    }

                    $is_data_sync = $this->client->zwssgr_fetch_gmb_data(false, false, 'zwssgr_gmb_reviews', $zwssgr_account_number, $zwssgr_location_number);

                    sleep(20);

                    if (!$is_data_sync) {
                        $this->zwssgr_debug_function('Data sync for widget:' . $zwssgr_widget_id . 'has been successfully processed');
                    } else {
                        $this->zwssgr_debug_function('There was an error while Data sync for widget:' . $zwssgr_widget_id);
                    }
                    
                }
                wp_reset_postdata();
            } else {
                return;
            }

        }

    }

    // Instantiate the ZWSSGR_Cron_Scheduler class to ensure cron jobs are scheduled.
    new ZWSSGR_Cron_Scheduler();

}