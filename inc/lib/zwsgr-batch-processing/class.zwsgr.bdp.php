<?php

require_once( ZWSGR_DIR . '/inc/lib/zwsgr-background-processing/vendor/autoload.php' );

if (!class_exists('Zwsgr_GMB_Background_Data_Processor')) {

    class Zwsgr_GMB_Background_Data_Processor extends WP_Background_Process {

        protected $action = 'Zwsgr_GMB_Background_Data_Processor';

        // Process each file by logging account names
        protected function task($zwsr_batch_data) {

            $zwsgr_gmb_data              = isset($zwsr_batch_data['zwsgr_gmb_data']) ? $zwsr_batch_data['zwsgr_gmb_data'] : [];
            $this->zwsgr_account_number  = isset($zwsr_batch_data['zwsgr_account_number']) ? $zwsr_batch_data['zwsgr_account_number'] : [];
            $this->zwsgr_location_number = isset($zwsr_batch_data['zwsgr_location_number']) ? $zwsr_batch_data['zwsgr_location_number'] : [];
            $this->next_page_token       = isset($zwsgr_gmb_data['nextPageToken']) ? $zwsgr_gmb_data['nextPageToken'] : null;
            $this->zwsgr_widget_id       = isset($zwsr_batch_data['zwsgr_widget_id']) ? $zwsr_batch_data['zwsgr_widget_id'] : null;
            $this->zwsgr_total_reviews   = isset($zwsgr_gmb_data['totalReviewCount']) ? $zwsgr_gmb_data['totalReviewCount'] : null;
            $this->zwsgr_average_rating  = isset($zwsgr_gmb_data['averageRating']) ? $zwsgr_gmb_data['averageRating'] : null;

            if (!empty($zwsgr_gmb_data)) {

                $zwsgr_data_key            = key($zwsgr_gmb_data);
                $this->zwsgr_gmb_data_type = 'zwsgr_gmb_'. key($zwsgr_gmb_data);
                $process_zwsgr_gmb_method = 'process_zwsgr_gmb_' . $zwsgr_data_key;

                // Check if the method exists and call it
                if (method_exists($this, $process_zwsgr_gmb_method)) {
                    // If the method exists, call it
                    $this->$process_zwsgr_gmb_method($zwsgr_gmb_data, $this->zwsgr_account_number, $this->zwsgr_location_number);
                } else {
                    error_log("Method $process_zwsgr_gmb_method does not exist.");
                }

            } else {
                error_log("No data found in \$zwsgr_gmb_data.");
            }

            return false;
        }

        public function process_zwsgr_gmb_accounts($zwsgr_gmb_data) {
            
            if (isset($zwsgr_gmb_data['accounts'])) {
                foreach ($zwsgr_gmb_data['accounts'] as $zwsgr_account) {

                    $zwsgr_gmb_email = get_option('zwsgr_gmb_email');

                    // Prepare post data
                    $zwsgr_request_data = array(
                        'post_title'   => sanitize_text_field($zwsgr_account['accountName']),
                        'post_content' => '', // You can add more content if needed
                        'post_status'  => 'publish',
                        'post_type'    => 'zwsgr_request_data',
                        'post_name'    => sanitize_title($zwsgr_account['name']),
                        'meta_input'   => array(
                            'zwsgr_gmb_email'   => $zwsgr_gmb_email
                        ),
                    );
            
                    // Check if a post with this title already exists
                    $zwsgr_existing_post  = get_page_by_title($zwsgr_request_data['post_title'], OBJECT, 'zwsgr_request_data');
                    $zwsgr_account_name = isset($zwsgr_account['name']) ? sanitize_text_field($zwsgr_account['name']) : '';
                    $this->zwsgr_account_number = $zwsgr_account_name ? ltrim(strrchr($zwsgr_account_name, '/'), '/') : '';
                    
                    if ($zwsgr_existing_post) {
                        // Update existing post
                        $zwsgr_request_data['ID'] = $zwsgr_existing_post->ID; // Set the ID to update

                        $zwsgr_update_result = wp_update_post($zwsgr_request_data);
                        
                        if (is_wp_error($zwsgr_update_result)) {
                            error_log("Failed to update post ID {$zwsgr_existing_post->ID}: " . $zwsgr_update_result->get_error_message());
                        } elseif ($zwsgr_update_result == 0) {
                            error_log("Failed to update post ID {$zwsgr_existing_post->ID}: Unknown error.");
                        }

                        // Update the account number for the existing post
                        update_post_meta($zwsgr_existing_post->ID, 'zwsgr_account_number', $this->zwsgr_account_number);

                    } else {
                        
                        // Create a new post
                        $zwsgr_insert_account = wp_insert_post($zwsgr_request_data);

                        if (is_wp_error($zwsgr_insert_account)) {
                            error_log("Failed to create new post: " . $zwsgr_insert_account->get_error_message());
                        } elseif ($zwsgr_insert_account == 0) {
                            error_log("Failed to create new post: Unknown error.");
                        } else {
                            // Add the account number for the new post
                            update_post_meta($zwsgr_insert_account, 'zwsgr_account_number', $this->zwsgr_account_number);
                        }

                    }

                }
            }

            return;

        }

        protected function process_zwsgr_gmb_locations($zwsgr_gmb_data, $zwsgr_account_number = null) {

            // If $zwsgr_account_number is not provided, use the class property
            $zwsgr_account_number = $zwsgr_account_number ?? $this->zwsgr_account_number;

            $zwsgr_request_data_id = get_posts(array(
                'post_type'      => 'zwsgr_request_data',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'meta_key'       => 'zwsgr_account_number',
                'meta_value'     => $this->zwsgr_account_number,
                'fields'         => 'ids',
            ))[0] ?? null;
        
            if (!$zwsgr_request_data_id) {
                // If no post is found, return early
                return;
            }
        
            // Retrieve existing locations or initialize an empty array
            $zwsgr_account_locations = get_post_meta($zwsgr_request_data_id, 'zwsgr_account_locations', true);
            
            if (!is_array($zwsgr_account_locations)) {
                $zwsgr_account_locations = [];
            }

            // Ensure the locations are unique in the provided data
            $new_locations = [];

            if (isset($zwsgr_gmb_data['locations']) && is_array($zwsgr_gmb_data['locations'])) {
                foreach ($zwsgr_gmb_data['locations'] as $zwsgr_account_location) {
                    // Only add locations that are not already in the current locations array
                    if (!in_array($zwsgr_account_location, $zwsgr_account_locations, true)) {
                        $new_locations[] = $zwsgr_account_location;
                    }
                }
            }

            if (!empty($new_locations)) {
                foreach ($new_locations as $location) {
                    // Only append the location if it is not already in the array
                    if (!in_array($location, $zwsgr_account_locations, true)) {
                        $zwsgr_account_locations[] = $location;  // Append the new unique location
                    }
                }
            
                // Update the post meta with the new locations array
                update_post_meta($zwsgr_request_data_id, 'zwsgr_account_locations', $zwsgr_account_locations);
            }
        
            return;
        }              

        protected function process_zwsgr_gmb_reviews($zwsgr_gmb_data, $zwsgr_account_number = null, $zwsgr_location_code) {

            // If $zwsgr_account_number is not provided, use the class property
            $zwsgr_account_number = $zwsgr_account_number ?? $this->zwsgr_account_number;


            if ( isset( $zwsgr_gmb_data['reviews'] ) && is_array( $zwsgr_gmb_data['reviews'] ) ) {
                foreach ( $zwsgr_gmb_data['reviews'] as $zwsgr_review ) {
            
                    $zwsgr_review_id = $zwsgr_review['reviewId'];
                    
                    // Check if the review already exists
                    $zwsgr_existing_review_query = new WP_Query([
                        'post_type'  => 'zwsgr_reviews',
                        'meta_query' => [
                            [
                                'key'   => 'zwsgr_review_id',
                                'value' => $zwsgr_review_id,
                            ],
                        ],
                    ]);
            
                    if ( $zwsgr_existing_review_query->have_posts() ) {
                        // Review exists, update it
                        $zwsgr_existing_review_query->the_post();
                        $zwsgr_wp_review_id = get_the_ID();
                    } else {
                        // Review does not exist, insert it
                        $zwsgr_review_data = [
                            'post_title'   => $zwsgr_review['reviewer']['displayName'] ?? 'Anonymous',
                            'post_content' => '',
                            'post_status'  => 'publish',
                            'post_type'    => 'zwsgr_reviews',
                            'post_date'      => isset( $zwsgr_review['createTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwsgr_review['createTime'] ) ) : current_time( 'mysql' ),
                            'post_modified'  => isset( $zwsgr_review['updateTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwsgr_review['updateTime'] ) ) : current_time( 'mysql' ),
                        ];
                        
                        $zwsgr_wp_review_id = wp_insert_post( $zwsgr_review_data );
                    }
            
                    // Check if post was inserted or updated successfully
                    if ( $zwsgr_wp_review_id && ! is_wp_error( $zwsgr_wp_review_id ) ) {

                        $zwsgr_gmb_email = get_option('zwsgr_gmb_email');

                        if (!empty($zwsgr_gmb_email)) {
                            update_post_meta($zwsgr_wp_review_id, 'zwsgr_gmb_email', $zwsgr_gmb_email);
                        }

                        //Update parent account & locations meta
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_account_number', $this->zwsgr_account_number );
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_location_code', $zwsgr_location_code);

                        // Update custom fields
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_review_id', $zwsgr_review_id );
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_review_comment', $zwsgr_review['comment'] );
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_reviewer_name', $zwsgr_review['reviewer']['displayName'] ?? 'Anonymous' );
                        update_post_meta( $zwsgr_wp_review_id, 'zwsgr_review_star_rating', $zwsgr_review['starRating'] );
                        
                        if ( isset( $zwsgr_review['reviewReply'] ) ) {
                            update_post_meta( $zwsgr_wp_review_id, 'zwsgr_reply_comment', $zwsgr_review['reviewReply']['comment'] );
                            update_post_meta( $zwsgr_wp_review_id, 'zwsgr_reply_update_time', $zwsgr_review['reviewReply']['updateTime'] );
                        }

                    } else {
                        
                        error_log( "Failed to insert/update review: " . ( is_wp_error( $zwsgr_wp_review_id ) ? $zwsgr_wp_review_id->get_error_message() : "Unknown error" ) );
                        
                    }
            
                    // Reset post data after each query
                    wp_reset_postdata();
                }
            }            

            return;

        }

        protected function complete() {

            parent::complete();

            $zwsgr_queue_manager = new Zwsgr_Queue_Manager();

            if (!empty($this->next_page_token)) {

                update_post_meta($this->zwsgr_widget_id, 'zwgr_data_processing_init', 'true');

                if (!empty($this->zwsgr_total_reviews)) {
                    $zwsgr_batch_pages   = ceil($this->zwsgr_total_reviews / 50);
                    update_post_meta($this->zwsgr_widget_id, 'zwsgr_batch_pages', $zwsgr_batch_pages);
                }

                $zwsgr_current_index = $zwsgr_queue_manager->zwsgr_get_current_batch_index($this->zwsgr_widget_id);

                if (!empty($zwsgr_batch_pages) && $zwsgr_batch_pages > 0) {
                    $zwsgr_batch_progress = round(($zwsgr_current_index + 1) / $zwsgr_batch_pages * 100);
                    update_post_meta($this->zwsgr_widget_id, 'zwsgr_batch_progress', $zwsgr_batch_progress);
                }

                $zwsgr_queue_manager->zwsgr_update_current_batch_index($this->zwsgr_widget_id, ($zwsgr_current_index + 1));
                
                sleep(1);

                $zwsgr_queue_manager->zwsgr_fetch_gmb_data(true, $this->next_page_token);

            } else {

                $zwsgr_request_data_id = get_posts(array(
                    'post_type'      => 'zwsgr_request_data',
                    'posts_per_page' => 1,
                    'post_status'    => 'publish',
                    'meta_key'       => 'zwsgr_account_number',
                    'meta_value'     => $this->zwsgr_account_number,
                    'fields'         => 'ids',
                ))[0] ?? null;

                if (!empty($this->zwsgr_total_reviews)) {
                    update_post_meta($zwsgr_request_data_id, "zwsgr_location_{$this->zwsgr_location_number}_total_reviews", $this->zwsgr_total_reviews);
                }

                if (!empty($this->zwsgr_average_rating)) {
                    update_post_meta($zwsgr_request_data_id, "zwsgr_location_{$this->zwsgr_location_number}_average_rating", $this->zwsgr_average_rating);
                }

                $zwsgr_queue_manager->zwsgr_reset_current_batch_index($this->zwsgr_widget_id);

                update_post_meta($this->zwsgr_widget_id, 'zwgr_data_processing_init', 'false');
                update_post_meta($this->zwsgr_widget_id, 'zwgr_data_sync_once', 'true');
                update_post_meta($this->zwsgr_widget_id, 'zwsgr_gmb_data_type', $this->zwsgr_gmb_data_type);
                delete_post_meta($this->zwsgr_widget_id, 'zwsgr_batch_pages');
                delete_post_meta($this->zwsgr_widget_id, 'zwsgr_batch_progress');
                return;

            }

        }

    }
    
}

// Register AJAX action for authenticated users only
add_action('wp_ajax_zwsgr_get_batch_processing_status', 'zwsgr_get_batch_processing_status');

/**
 * Retrieves the current batch processing status and sends it in a JSON response.
 *
 * This function checks the 'zwsgr_batch_process_status' option to determine
 * the status of batch processing, and returns the result as a JSON response.
 * Only accessible by authenticated users in the admin area with a valid nonce.
 */
function zwsgr_get_batch_processing_status() {

    $zwsgr_widget_id           = $_POST['zwsgr_widget_id'];
    $zwgr_data_processing_init = get_post_meta($zwsgr_widget_id, 'zwgr_data_processing_init', true);
    $zwgr_data_sync_once       = get_post_meta($zwsgr_widget_id, 'zwgr_data_sync_once', true);
    $zwsgr_gmb_data_type       = get_post_meta($zwsgr_widget_id, 'zwsgr_gmb_data_type', true);
    $zwsgr_batch_progress      = get_post_meta($zwsgr_widget_id, 'zwsgr_batch_progress', true);
    $zwsgr_queue_manager       = new Zwsgr_Queue_Manager();

    if ($zwgr_data_processing_init == 'false') {
        delete_post_meta($zwsgr_widget_id, 'zwgr_data_processing_init');    
    }

    $zwsgr_response = [
        'zwgr_data_processing_init'  => $zwgr_data_processing_init,
        'zwgr_data_sync_once'        => $zwgr_data_sync_once,
        'zwsgr_gmb_data_type'        => $zwsgr_gmb_data_type,
        'zwsgr_batch_progress'      => $zwsgr_batch_progress
    ];

    wp_send_json_success($zwsgr_response);

}