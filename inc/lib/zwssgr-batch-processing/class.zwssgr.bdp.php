<?php

require_once( ZWSSGR_DIR . '/inc/lib/zwssgr-background-processing/vendor/autoload.php' );

if (!class_exists('Zwssgr_GMB_Background_Data_Processor')) {

    class Zwssgr_GMB_Background_Data_Processor extends WP_Background_Process {

        protected $action = 'Zwssgr_GMB_Background_Data_Processor';
        private $zwsr_batch_data, $zwssgr_widget_id, $zwssgr_current_index, $zwssgr_account_number, $zwssgr_location_number, $next_page_token, $zwssgr_total_reviews, $zwssgr_average_rating;
        public $zwssgr_gmb_data_type;
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

        // Process each file by logging account names
        protected function task($zwsr_batch_data) {

            $zwssgr_gmb_data              = isset($zwsr_batch_data['zwssgr_gmb_data'])        ? $zwsr_batch_data['zwssgr_gmb_data']        : [];
            $this->zwssgr_widget_id       = isset($zwsr_batch_data['zwssgr_widget_id'])       ? $zwsr_batch_data['zwssgr_widget_id']       : null;
            $this->zwssgr_account_number  = isset($zwsr_batch_data['zwssgr_account_number'])  ? $zwsr_batch_data['zwssgr_account_number']  : [];
            $this->zwssgr_location_number = isset($zwsr_batch_data['zwssgr_location_number']) ? $zwsr_batch_data['zwssgr_location_number'] : [];
            $this->next_page_token       = isset($zwssgr_gmb_data['nextPageToken'])          ? $zwssgr_gmb_data['nextPageToken']          : null;
            $this->zwssgr_total_reviews   = isset($zwssgr_gmb_data['totalReviewCount'])       ? $zwssgr_gmb_data['totalReviewCount']       : null;
            $this->zwssgr_average_rating  = isset($zwssgr_gmb_data['averageRating'])          ? $zwssgr_gmb_data['averageRating']          : null;

            if (!empty($zwssgr_gmb_data)) {

                $zwssgr_data_key            = key($zwssgr_gmb_data);
                $this->zwssgr_gmb_data_type = 'zwssgr_gmb_'. key($zwssgr_gmb_data);
                $process_zwssgr_gmb_method = 'process_zwssgr_gmb_' . $zwssgr_data_key;
                
                if (method_exists($this, $process_zwssgr_gmb_method)) {
                    $this->$process_zwssgr_gmb_method($zwssgr_gmb_data, $this->zwssgr_account_number, $this->zwssgr_location_number);
                } else {
                    $this->zwssgr_debug_function('BDP: Method ' . $process_zwssgr_gmb_method . ' does not exist. for: ' . $this->zwssgr_gmb_data_type .' & Widget ID '. $this->zwssgr_widget_id . ' & current index ' . $this->zwssgr_current_index);
                }

            } else {

                $this->zwssgr_debug_function('BDP: No $zwssgr_gmb_data data found for: ' . $this->zwssgr_gmb_data_type .' & Widget ID '. $this->zwssgr_widget_id . ' & current index ' . $this->zwssgr_current_index);
                
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'empty_zwssgr_gmb_data',
                        'message'  => 'Empty GMb data'
                    ),
                );

            }

            return false;
        }

        public function process_zwssgr_gmb_accounts($zwssgr_gmb_data) {
            
            if (isset($zwssgr_gmb_data['accounts'])) {

                foreach ($zwssgr_gmb_data['accounts'] as $zwssgr_account) {

                    $zwssgr_gmb_email = get_option('zwssgr_gmb_email');

                    $zwssgr_request_data = array(
                        'post_title'   => sanitize_text_field($zwssgr_account['accountName']),
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_type'    => 'zwssgr_request_data',
                        'post_name'    => sanitize_title($zwssgr_account['name']),
                        'meta_input'   => array(
                            'zwssgr_gmb_email'   => $zwssgr_gmb_email
                        ),
                    );
            
                    $zwssgr_existing_post        = get_page_by_title($zwssgr_request_data['post_title'], OBJECT, 'zwssgr_request_data');
                    $zwssgr_account_name         = isset($zwssgr_account['name']) ? sanitize_text_field($zwssgr_account['name']) : '';
                    $this->zwssgr_account_number = $zwssgr_account_name ? ltrim(strrchr($zwssgr_account_name, '/'), '/') : '';
                    
                    if ($zwssgr_existing_post) {
                        
                        // Update existing post
                        $zwssgr_request_data['ID'] = $zwssgr_existing_post->ID;

                        $zwssgr_update_result = wp_update_post($zwssgr_request_data);
                        
                        if (is_wp_error($zwssgr_update_result)) {
                            $this->zwssgr_debug_function("BDP: Failed to update account ID {$zwssgr_existing_post->ID}: for Widget ID " . $this->$zwssgr_widget_id . $zwssgr_update_result->get_error_message());
                        } elseif ($zwssgr_update_result == 0) {
                            $this->zwssgr_debug_function("BDP: Failed to update account ID {$zwssgr_existing_post->ID}: for Widget ID " . $this->$zwssgr_widget_id . ' Unknown error ');
                        }

                        // Update the account number for the existing post
                        update_post_meta($zwssgr_existing_post->ID, 'zwssgr_account_number', $this->zwssgr_account_number);

                    } else {
                        
                        // Create a new post
                        $zwssgr_insert_account = wp_insert_post($zwssgr_request_data);

                        if (is_wp_error($zwssgr_insert_account)) {
                            $this->zwssgr_debug_function("BDP: Failed to create new account for Widget ID " . $this->$zwssgr_widget_id . $zwssgr_insert_account->get_error_message());
                        } elseif ($zwssgr_insert_account == 0) {
                            $this->zwssgr_debug_function("BDP: Failed to create new account for Widget ID " . $this->$zwssgr_widget_id);
                        } else {
                            // Add the account number for the new post
                            update_post_meta($zwssgr_insert_account, 'zwssgr_account_number', $this->zwssgr_account_number);
                        }

                    }

                }

                return array(
                    'success' => true,
                    'data'    => array(
                        'message'   => 'Accounts for Processed successfully'
                    ),
                );

            } else {

                $this->zwssgr_debug_function('BDP: Unexpected zwssgr_data for accounts & Widget ID '. $this->zwssgr_widget_id . ' & current index ' . $this->zwssgr_current_index);
                
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'unexpected_zwssgr_gmb_data',
                        'message'  => 'Unexpected error for zwssgr_gmb_data'
                    ),
                );

            }

        }

        protected function process_zwssgr_gmb_locations($zwssgr_gmb_data, $zwssgr_account_number = null) {

            // If $zwssgr_account_number is not provided, use the class property
            $zwssgr_account_number = $zwssgr_account_number ?? $this->zwssgr_account_number;

            $zwssgr_request_data_id = get_posts(array(
                'post_type'      => 'zwssgr_request_data',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'meta_key'       => 'zwssgr_account_number',
                'meta_value'     => $this->zwssgr_account_number,
                'fields'         => 'ids',
            ))[0] ?? null;
        
            if (!$zwssgr_request_data_id) {

                // If no post is found, return early
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'invalid_zwssgr_request_data',
                        'message'  => 'Invalid request data id'
                    ),
                );

            }
        
            // Retrieve existing locations or initialize an empty array
            $zwssgr_account_locations = get_post_meta($zwssgr_request_data_id, 'zwssgr_account_locations', true);
            
            if (!is_array($zwssgr_account_locations)) {
                $zwssgr_account_locations = [];
            }

            // Ensure the locations are unique in the provided data
            $new_locations = [];

            if (isset($zwssgr_gmb_data['locations']) && is_array($zwssgr_gmb_data['locations'])) {
                foreach ($zwssgr_gmb_data['locations'] as $zwssgr_account_location) {
                    // Only add locations that are not already in the current locations array
                    if (!in_array($zwssgr_account_location, $zwssgr_account_locations, true)) {
                        $new_locations[] = $zwssgr_account_location;
                    }
                }
            }

            if (!empty($new_locations)) {
                foreach ($new_locations as $location) {
                    // Only append the location if it is not already in the array
                    if (!in_array($location, $zwssgr_account_locations, true)) {
                        $zwssgr_account_locations[] = $location;  // Append the new unique location
                    }
                }
            
                // Update the post meta with the new locations array
                update_post_meta($zwssgr_request_data_id, 'zwssgr_account_locations', $zwssgr_account_locations);
            }
        
            return array(
                'success' => true,
                'data'    => array(
                    'message'   => 'Locations for Processed successfully'
                ),
            );

        }              

        protected function process_zwssgr_gmb_reviews($zwssgr_gmb_data, $zwssgr_location_number,$zwssgr_account_number = null) {

            // If $zwssgr_account_number is not provided, use the class property
            $zwssgr_account_number = $zwssgr_account_number ?? $this->zwssgr_account_number;

            if ( isset( $zwssgr_gmb_data['reviews'] ) && is_array( $zwssgr_gmb_data['reviews'] ) ) {
                foreach ( $zwssgr_gmb_data['reviews'] as $zwssgr_review ) {
            
                    $zwssgr_review_id = $zwssgr_review['reviewId'];
                    
                    // Check if the review already exists
                    $zwssgr_existing_review_query = new WP_Query([
                        'post_type'  => 'zwssgr_reviews',
                        'meta_query' => [
                            [
                                'key'   => 'zwssgr_review_id',
                                'value' => $zwssgr_review_id,
                            ],
                        ],
                    ]);
            
                    if ( $zwssgr_existing_review_query->have_posts() ) {
                        // Review exists, update it
                        $zwssgr_existing_review_query->the_post();
                        $zwssgr_wp_review_id = get_the_ID();
                    } else {

                        // Review does not exist, insert it
                        $zwssgr_review_data = [
                            'post_title'   => $zwssgr_review['reviewer']['displayName'] ?? 'Anonymous',
                            'post_content' => '',
                            'post_status'  => 'publish',
                            'post_type'    => 'zwssgr_reviews',
                            'post_date'      => isset( $zwssgr_review['createTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwssgr_review['createTime'] ) ) : current_time( 'mysql' ),
                            'post_modified'  => isset( $zwssgr_review['updateTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwssgr_review['updateTime'] ) ) : current_time( 'mysql' ),
                        ];
                        
                        $zwssgr_wp_review_id = wp_insert_post( $zwssgr_review_data );
                    }
            
                    // Check if post was inserted or updated successfully
                    if ( $zwssgr_wp_review_id && ! is_wp_error( $zwssgr_wp_review_id ) ) {

                        $zwssgr_gmb_email     = get_option('zwssgr_gmb_email');
                        $zwssgr_review_dp_url = isset($zwssgr_review['reviewer']['profilePhotoUrl']) ? $zwssgr_review['reviewer']['profilePhotoUrl'] : null;
                        $zwssgr_save_path     = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
                        
                        if (!empty($zwssgr_gmb_email)) {
                            update_post_meta($zwssgr_wp_review_id, 'zwssgr_gmb_email', $zwssgr_gmb_email);
                        }

                        //Update parent account & locations meta
                        update_post_meta( $zwssgr_wp_review_id, 'zwssgr_account_number', $this->zwssgr_account_number );
                        update_post_meta( $zwssgr_wp_review_id, 'zwssgr_location_number', $this->zwssgr_location_number);

                        $zwssgr_download_review_image_status = $this->zwssgr_download_review_image($zwssgr_review_dp_url, $zwssgr_save_path);

                        if (!$zwssgr_download_review_image_status['success']) {
                            $this->zwssgr_debug_function('BDP: There was an error while downoading reviewer image.' . $zwssgr_wp_review_id . $zwssgr_review['reviewer']['displayName'] );
                        }

                        // Update custom fields
                        update_post_meta( $zwssgr_wp_review_id, 'zwssgr_review_id', $zwssgr_review_id );

                        if ( isset($zwssgr_review['comment'])) {
                            update_post_meta( $zwssgr_wp_review_id, 'zwssgr_review_comment', $zwssgr_review['comment'] );
                        }

                        update_post_meta( $zwssgr_wp_review_id, 'zwssgr_reviewer_name', $zwssgr_review['reviewer']['displayName'] ?? 'Anonymous' );
                        update_post_meta( $zwssgr_wp_review_id, 'zwssgr_review_star_rating', $zwssgr_review['starRating'] );
                        
                        if ( isset( $zwssgr_review['reviewReply'] ) ) {
                            update_post_meta( $zwssgr_wp_review_id, 'zwssgr_reply_comment', $zwssgr_review['reviewReply']['comment'] );
                            update_post_meta( $zwssgr_wp_review_id, 'zwssgr_reply_update_time', $zwssgr_review['reviewReply']['updateTime'] );
                        }

                    } else {
                        $this->zwssgr_debug_function( "BDP: Failed to insert/update review: " . ( is_wp_error( $zwssgr_wp_review_id ) ? $zwssgr_wp_review_id->get_error_message() : "Unknown error" ) );   
                    }
            
                    // Reset post data after each query
                    wp_reset_postdata();
                }
            } else {

                $this->zwssgr_debug_function('BDP: Unexpected zwssgr_data for reviews & Widget ID '. $this->zwssgr_widget_id . ' & current index ' . $this->zwssgr_current_index);
                
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'unexpected_zwssgr_gmb_data',
                        'message'  => 'Unexpected error for zwssgr_gmb_data'
                    ),
                );

            }

        }

        /**
        * Downloads an image from a URL and saves it to a specified path.
        *
        * @param string $zwssgr_review_dp_url The image URL to download.
        * @param string $zwssgr_save_path The path where the image should be saved.
        * 
        * @return string|bool The path to the saved image if successful, otherwise false.
        */
        protected function zwssgr_download_review_image($zwssgr_review_dp_url, $zwssgr_save_path) {

            // Fetch image data
            $zwssgr_review_image_data = file_get_contents($zwssgr_review_dp_url);

            // Check if the image was downloaded successfully
            if ($zwssgr_review_image_data === false) {
                $this->zwssgr_debug_function("BDP: Unable to download the image from URL: " . $zwssgr_review_dp_url . " for Widget ID " . $this->$zwssgr_widget_id  . ' & current index ' . $this->zwssgr_current_index);
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'failed_to_download_image',
                        'message'  => 'Failed to download image from specified path'
                    ),
                );
            }

            $zwssgr_temp_directory = dirname($zwssgr_save_path);

            if (!is_dir($zwssgr_temp_directory)) {
                wp_mkdir_p($zwssgr_temp_directory);
            }

            // Save the image data to the file system
            $zwssgr_put_image_content = file_put_contents($zwssgr_save_path, $zwssgr_review_image_data);

            if ($zwssgr_put_image_content === false) {

                $this->zwssgr_debug_function("Error: Failed to save the image to the specified path: " . $zwssgr_save_path . " for Widget ID " . $this->$zwssgr_widget_id . ' & current index ' . $this->zwssgr_current_index);
                
                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'failed_to_save_image',
                        'message'  => 'Failed to save image to specified path'
                    ),
                );

            }

            // Return the path to the saved image
            return array(
                'success' => true,
                'data'    => array(
                    'zwssgr_save_path'   => $zwssgr_save_path
                ),
            );
            
        }

        protected function complete() {

            parent::complete();

            $zwssgr_queue_manager = new Zwssgr_Queue_Manager();

            if (!empty($this->next_page_token)) {

                update_post_meta($this->zwssgr_widget_id, 'zwgr_data_processing_init', 'true');

                if (!empty($this->zwssgr_total_reviews)) {
                    $zwssgr_batch_pages   = ceil($this->zwssgr_total_reviews / 50);
                    update_post_meta($this->zwssgr_widget_id, 'zwssgr_batch_pages', $zwssgr_batch_pages);
                }

                $this->zwssgr_current_index = $zwssgr_queue_manager->zwssgr_get_current_batch_index($this->zwssgr_widget_id);

                if (!empty($zwssgr_batch_pages) && $zwssgr_batch_pages > 0) {
                    $zwssgr_batch_progress = round(($this->zwssgr_current_index + 1) / $zwssgr_batch_pages * 100);
                    update_post_meta($this->zwssgr_widget_id, 'zwssgr_batch_progress', $zwssgr_batch_progress);
                }

                $zwssgr_queue_manager->zwssgr_update_current_batch_index($this->zwssgr_widget_id, ($this->zwssgr_current_index + 1));
                
                sleep(1);

                $zwssgr_queue_manager->zwssgr_fetch_gmb_data(true, $this->next_page_token, $this->zwssgr_gmb_data_type, $this->zwssgr_account_number, $this->zwssgr_location_number, $this->zwssgr_widget_id);

            } else {

                $zwssgr_queue_manager->zwssgr_reset_current_batch_index($this->zwssgr_widget_id);
                delete_option('zwssgr_widget_id');
                delete_option('zwssgr_batch_in_processing');
                update_post_meta($this->zwssgr_widget_id, 'zwgr_data_processing_init', 'false');
                update_post_meta($this->zwssgr_widget_id, 'zwgr_data_sync_once', 'true');
                update_post_meta($this->zwssgr_widget_id, 'zwssgr_gmb_data_type', $this->zwssgr_gmb_data_type);
                delete_post_meta($this->zwssgr_widget_id, 'zwssgr_batch_pages');
                delete_post_meta($this->zwssgr_widget_id, 'zwssgr_batch_progress');

                // Return the path to the saved image
                return array(
                    'success' => true,
                    'data'    => array(
                        'message'   => 'Data processed successfully.'
                    ),
                );

            }

        }

    }
    
}

// Register AJAX action for authenticated users only
add_action('wp_ajax_zwssgr_get_batch_processing_status', 'zwssgr_get_batch_processing_status');

/**
 * Retrieves the current batch processing status and sends it in a JSON response.
 *
 * This function checks the 'zwssgr_batch_process_status' option to determine
 * the status of batch processing, and returns the result as a JSON response.
 * Only accessible by authenticated users in the admin area with a valid nonce.
 */
function zwssgr_get_batch_processing_status() {

    // Check nonce and AJAX referer
    check_ajax_referer('zwssgr_queue_manager_nounce', 'security');

    $zwssgr_widget_id           = intval($_POST['zwssgr_widget_id']);
    $zwgr_data_processing_init = get_post_meta($zwssgr_widget_id, 'zwgr_data_processing_init', true);
    $zwgr_data_sync_once       = get_post_meta($zwssgr_widget_id, 'zwgr_data_sync_once', true);
    $zwssgr_gmb_data_type       = get_post_meta($zwssgr_widget_id, 'zwssgr_gmb_data_type', true);
    $zwssgr_batch_progress      = get_post_meta($zwssgr_widget_id, 'zwssgr_batch_progress', true);

    if ($zwgr_data_processing_init == 'false') {
        delete_post_meta($zwssgr_widget_id, 'zwgr_data_processing_init');    
    }

    $zwssgr_response = [
        'zwgr_data_processing_init'  => $zwgr_data_processing_init,
        'zwgr_data_sync_once'        => $zwgr_data_sync_once,
        'zwssgr_gmb_data_type'        => $zwssgr_gmb_data_type,
        'zwssgr_batch_progress'      => $zwssgr_batch_progress
    ];

    wp_send_json_success($zwssgr_response);

}