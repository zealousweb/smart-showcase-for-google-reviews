<?php
/**
 * Zwsgr_Queue_Manager Class
 *
 * Handles the Batch Queue functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

require_once( ZWSGR_DIR . '/inc/lib/zwsgr-batch-processing/class.' . ZWSGR_PREFIX . '.bdp.php' );

if (!class_exists('Zwsgr_Queue_Manager')) {

    class Zwsgr_Queue_Manager {

        private $process;

        private $client;

        public function __construct() {

            $this->process = new Zwsgr_GMB_Background_Data_Processor();

            $this->client  = new ZWSGR_GMB_API( '' );

            // Initialize AJAX actions
            add_action('wp_ajax_zwsgr_fetch_gmb_data', array($this, 'zwsgr_fetch_gmb_data'));
            add_action('wp_ajax_nopriv_zwsgr_fetch_gmb_data', array($this, 'zwsgr_fetch_gmb_data'));

        }

        public function zwsgr_fetch_gmb_data($zwsgr_internal_call = false, $zwsgr_next_page_token = false) {

            $zwsgr_access_token =  $this->client->zwsgr_get_access_token();

            if (!empty($zwsgr_access_token)) {
                $this->client->set_access_token($zwsgr_access_token);
            } else {
                error_log('No valid access token found');
                exit;
            }

            if (!$zwsgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer('zwsgr_queue_manager_nounce', 'security');

                // If nonce verification fails
                if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'zwsgr_queue_manager_nounce')) {
                    error_log('Nonce verification failed for AJAX request in Zwsgr_Queue_Manager');
                    wp_send_json_error(array('message' => 'Nonce verification failed.'));
                    return;
                }

                $this->zwsgr_reset_current_batch_index();

                update_option('zwsgr_batch_process_status', true);

            }

            $zwsgr_gmb_data_type   = isset($_POST['zwsgr_gmb_data_type']) ? sanitize_text_field($_POST['zwsgr_gmb_data_type']) : get_option('zwsgr_gmb_data_type');
            $zwsgr_account_number  = isset($_POST['zwsgr_account_number']) ? sanitize_text_field($_POST['zwsgr_account_number']) : get_option('zwsgr_account_number');
            $zwsgr_location_number = isset($_POST['zwsgr_location_number']) ? sanitize_text_field($_POST['zwsgr_location_number']) : get_option('zwsgr_location_number');
            $zwsgr_widget_id       = isset($_POST['zwsgr_widget_id']) ? sanitize_text_field($_POST['zwsgr_widget_id']) : get_option('zwsgr_widget_id');

            // Add the account number as post meta for the widget ID (post_id)
            if ($zwsgr_widget_id && $zwsgr_account_number) {
                update_post_meta($zwsgr_widget_id, 'zwsgr_account_number', $zwsgr_account_number);
                // Check if location number is provided, and if so, add it as post meta
                if (!empty($zwsgr_location_number)) {
                    update_post_meta($zwsgr_widget_id, 'zwsgr_location_number', $zwsgr_location_number);
                }
            }

            // Attempt to update each option and log an error if it fails
            if (!update_option('zwsgr_gmb_data_type', $zwsgr_gmb_data_type)) {
                error_log('Failed to update zwsgr_gmb_data_type option.');
            }

            if (!update_option('zwsgr_account_number', $zwsgr_account_number)) {
                error_log('Failed to update zwsgr_account_number option.');
            }

            if (!update_option('zwsgr_location_number', $zwsgr_location_number)) {
                error_log('Failed to update zwsgr_location_number option.');
            }

            $zwsgr_current_index = $this->zwsgr_get_current_batch_index();

            switch ($zwsgr_gmb_data_type) {
                case 'zwsgr_gmb_accounts':
                    $zwsgr_gmb_data = $this->client->zwsgr_get_accounts($zwsgr_next_page_token);
                    break;
                case 'zwsgr_gmb_locations':
                    $zwsgr_gmb_data = $this->client->zwsgr_get_locations($zwsgr_account_number, $zwsgr_next_page_token);
                    break;
                case 'zwsgr_gmb_reviews':
                    $zwsgr_gmb_data = $this->client->zwsgr_get_reviews($zwsgr_account_number, $zwsgr_location_number, $zwsgr_next_page_token);                    
                    break;
                default:
                    error_log("Invalid GMB data type: " . $zwsgr_gmb_data_type);
                    wp_send_json_error(['message' => 'Invalid GMB data type: ' . esc_html($zwsgr_gmb_data_type)], 400);
                    return;
            }
            
            if (!empty($zwsgr_gmb_data)) {

                // Prepare data to be pushed to the queue
                $zwsgr_push_data_to_queue = [
                    'zwsgr_gmb_data'       => $zwsgr_gmb_data,
                    'zwsgr_account_number' => $zwsgr_account_number,
                    'zwsgr_location_number' => $zwsgr_location_number
                ];

                // Push data to the queue
                $this->process->push_to_queue($zwsgr_push_data_to_queue);

                $this->process->save()->dispatch();
                
                wp_send_json_success(
                    array(
                        'message' => "Batch Processing started.",
                    )
                );

            } else {

                // Log the error before resetting the index and deleting options
                error_log("Batch processing error: Failed at index " . $zwsgr_current_index);

                $this->zwsgr_reset_current_batch_index();

                // Log the deletion of options
                if (delete_option('zwsgr_batch_status')) {
                    error_log("Option 'zwsgr_batch_status' deleted successfully.");
                } else {
                    error_log("Failed to delete option 'zwsgr_batch_status: Failed at index " . $zwsgr_current_index);
                }

                if (delete_option('zwsgr_batch_process_status')) {
                    error_log("Option 'zwsgr_batch_process_status' deleted successfully.");
                } else {
                    error_log("Failed to delete option 'zwsgr_batch_process_status: Failed at index " . $zwsgr_current_index);
                }
                
                wp_send_json_error(
                    array(
                        'message' => 'Sorry there was an error while processing this batch', 
                        'zwsgr_current_index' => $zwsgr_current_index
                    )
                );

            }

            wp_die();
        
        }

        // Helper function to get the current index from the database
        public function zwsgr_get_current_batch_index() {
            return get_option('zwsgr_current_index', 1);
        }

        // Helper function to update the current index in the database
        public function zwsgr_update_current_batch_index($index) {
            update_option('zwsgr_current_index', intval($index));
        }

        // Helper function to reset current index
        public function zwsgr_reset_current_batch_index() {
            delete_option('zwsgr_current_index');
        }

    }

    new Zwsgr_Queue_Manager();

}