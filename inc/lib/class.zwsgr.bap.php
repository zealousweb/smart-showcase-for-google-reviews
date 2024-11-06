<?php

require_once 'wp-background-processing/wp-background-processing.php';

if (!class_exists('Zwsgr_GMB_Data_Process')) {

    class Zwsgr_GMB_Data_Process extends WP_Background_Process {

        protected $action = 'zwsgr_gmb_data_process';

        // Process each file by logging account names
        protected function task($zwsr_batch_data) {

            $zwsgr_gmb_data              = isset($zwsr_batch_data['zwsgr_gmb_data']) ? $zwsr_batch_data['zwsgr_gmb_data'] : [];
            $this->$zwsgr_gmb_data_type  = isset($zwsr_batch_data['zwsgr_gmb_data_type']) ? $zwsr_batch_data['zwsgr_gmb_data_type'] : null;
            $this->zwsgr_current_index   = isset($zwsr_batch_data['zwsgr_current_index']) ? $zwsr_batch_data['zwsgr_current_index'] : null;
            $zwsgr_account_number        = isset($zwsr_batch_data['zwsgr_account_number']) ? $zwsr_batch_data['zwsgr_account_number'] : [];
            $this->next_page_token       = isset($zwsgr_gmb_data['nextPageToken']) ? $zwsgr_gmb_data['nextPageToken'] : null;

            if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_accounts') {
                $this->process_zwsgr_gmb_accounts($zwsgr_gmb_data);
            } else if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_locations') {
                $this->process_zwsgr_gmb_locations($zwsgr_gmb_data, $zwsgr_account_number);
            } else if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {
                $this->process_zwsgr_gmb_reviews($zwsgr_gmb_data);
            }

            return false;
        }

        public function process_zwsgr_gmb_accounts($zwsgr_gmb_data) {
            
            if (isset($zwsgr_gmb_data['accounts'])) {
                foreach ($zwsgr_gmb_data['accounts'] as $account) {

                    // Prepare post data
                    $zwsgr_account_data = array(
                        'post_title'   => sanitize_text_field($account['accountName']),
                        'post_content' => '', // You can add more content if needed
                        'post_status'  => 'publish',
                        'post_type'    => 'zwsgr_account',
                    );
            
                    // Check if a post with this title already exists
                    $existing_post  = get_page_by_title($zwsgr_account_data['post_title'], OBJECT, 'zwsgr_account');
                    $account_number = isset($account['accountNumber']) ? sanitize_text_field($account['accountNumber']) : '';
                    
                    if ($existing_post) {
                        // Update existing post
                        $zwsgr_account_data['ID'] = $existing_post->ID; // Set the ID to update

                        $update_result = wp_update_post($zwsgr_account_data);
                        
                        if (is_wp_error($update_result)) {
                            error_log("Failed to update post ID {$existing_post->ID}: " . $update_result->get_error_message());
                        } elseif ($update_result == 0) {
                            error_log("Failed to update post ID {$existing_post->ID}: Unknown error.");
                        }

                        // Update the account number for the existing post
                        update_post_meta($existing_post->ID, 'zwsgr_account_number', $account_number);

                    } else {
                        
                        // Create a new post
                        $insert_account = wp_insert_post($zwsgr_account_data);

                        if (is_wp_error($insert_account)) {
                            error_log("Failed to create new post: " . $insert_account->get_error_message());
                        } elseif ($insert_account == 0) {
                            error_log("Failed to create new post: Unknown error.");
                        } else {
                            // Add the account number for the new post
                            update_post_meta($insert_account, 'zwsgr_account_number', $account_number);
                        }

                    }

                }
            }

            return;

        }

        protected function process_zwsgr_gmb_locations($zwsgr_gmb_data, $zwsgr_account_number) {

            $zwsgr_account_post_id = get_posts(array(
                'post_type'      => 'zwsgr_account',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'meta_key'       => 'zwsgr_account_number',
                'meta_value'     => $zwsgr_account_number,
                'fields'         => 'ids',
            ))[0] ?? null;
        
            if (!$zwsgr_account_post_id) {
                // If no post is found, return early
                return;
            }
        
            // Retrieve existing locations or initialize an empty array
            $zwsgr_account_locations = get_post_meta($zwsgr_account_post_id, 'zwsgr_account_locations', true);
            
            if (!is_array($zwsgr_account_locations)) {
                $zwsgr_account_locations = [];
            }
        
            // Check if 'locations' data exists in the provided data
            if (isset($zwsgr_gmb_data['locations']) && is_array($zwsgr_gmb_data['locations'])) {
                foreach ($zwsgr_gmb_data['locations'] as $zwsgr_account_location) {
                    // Add each location to the account locations array
                    $zwsgr_account_locations[] = $zwsgr_account_location;
                }
                // Update the post meta with the new locations array
                update_post_meta($zwsgr_account_post_id, 'zwsgr_account_locations', $zwsgr_account_locations);
            }
        
            return;
        }              

        protected function process_zwsgr_gmb_reviews () {
            error_log('function process_zwsgr_gmb_reviews executed');
        }

        protected function complete() {

            parent::complete();

            // Update status to complete
            update_option('zwsgr_batch_status', [
                'status' => 'completed',
                'zwsgr_current_index' => $this->zwsgr_current_index
            ]);
            
            $batch_processing = new Zwsgr_Batch_Processing();

            if (!empty($this->next_page_token)) {
                
                update_option('zwsgr_batch_process_status', true);
                $batch_processing->zwsgr_update_current_index($this->zwsgr_current_index + 1);
                sleep(1);
                $batch_processing->zwsgr_fetch_gmb_data();

            } else {

                delete_option('zwsgr_batch_status');
                delete_option('zwsgr_batch_process_status');

                $batch_processing->zwsgr_reset_current_index();

            }

        }

    }
}

if (!class_exists('Zwsgr_Batch_Processing')) {

    class Zwsgr_Batch_Processing {

        private $process;

        public function __construct() {

            $this->process = new Zwsgr_GMB_Data_Process();

            // Initialize AJAX actions
            add_action('wp_ajax_zwsgr_fetch_gmb_data', array($this, 'zwsgr_fetch_gmb_data'));
            add_action('wp_ajax_nopriv_zwsgr_fetch_gmb_data', array($this, 'zwsgr_fetch_gmb_data'));

        }

        public function zwsgr_fetch_gmb_data() {

            $zwsgr_gmb_data_type = isset($_POST['zwsgr_gmb_data_type']) ? sanitize_text_field($_POST['zwsgr_gmb_data_type']) : get_option('zwsgr_gmb_data_type');
            $zwsgr_account_number = isset($_POST['zwsgr_account_number']) ? sanitize_text_field($_POST['zwsgr_account_number']) : get_option('zwsgr_account_number');

            update_option('zwsgr_gmb_data_type', $zwsgr_gmb_data_type);
            update_option('zwsgr_account_number', $zwsgr_account_number);

            $zwsgr_current_index = $this->zwsgr_fetch_current_index();

            if ($zwsgr_gmb_data_type == 'zwsgr_gmb_accounts') {
                $file_path = plugin_dir_path(__FILE__) . "accounts/accounts-response-$zwsgr_current_index.json";
            } else if ($zwsgr_gmb_data_type == 'zwsgr_gmb_locations') {
                $file_path = plugin_dir_path(__FILE__) . "locations/locations-response-$zwsgr_account_number-$zwsgr_current_index.json";
            } else if ($zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {
            }

            if (file_exists($file_path)) {

                // Decode the file content
                $zwsgr_gmb_data = json_decode(file_get_contents($file_path), true);

                // Prepare data to be pushed to the queue
                $zwsgr_push_data_to_queue = [
                    'zwsgr_gmb_data'       => $zwsgr_gmb_data,
                    'zwsgr_gmb_data_type'  => $zwsgr_gmb_data_type,
                    'zwsgr_account_number' => $zwsgr_account_number,
                    'zwsgr_current_index'  => $zwsgr_current_index
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

                $this->zwsgr_reset_current_index();
                
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
        public function zwsgr_fetch_current_index() {
            return get_option('zwsgr_current_index', 1);
        }

        // Helper function to update the current index in the database
        public function zwsgr_update_current_index($index) {
            update_option('zwsgr_current_index', intval($index));
        }

        // Helper function to reset current index
        public function zwsgr_reset_current_index() {
            delete_option('zwsgr_current_index');
        }

    }

    new Zwsgr_Batch_Processing();
}

add_action('wp_ajax_zwsgr_get_batch_status', 'zwsgr_get_batch_status');
add_action('wp_ajax_nopriv_zwsgr_get_batch_status', 'zwsgr_get_batch_status');

function zwsgr_get_batch_status() {

    $zwsgr_batch_status  = get_option('zwsgr_batch_status', [
        'status' => 'in_progress',
        'current_batch' => 0
    ]);

    $zwsgr_batch_process_status = get_option('zwsgr_batch_process_status');

    // Combine current index and batch status into a response array
    $response = [
        'zwsgr_batch_status'         => $zwsgr_batch_status,
        'zwsgr_batch_process_status' => $zwsgr_batch_process_status
    ];

    // Send JSON response with the combined status
    wp_send_json_success($response);

}