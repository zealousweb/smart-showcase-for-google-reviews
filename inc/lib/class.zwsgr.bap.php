<?php

require_once 'wp-background-processing/vendor/autoload.php';

require_once( 'class.'   . ZWSGR_PREFIX . '.api.php' );

if (!class_exists('Zwsgr_GMB_Data_Process')) {

    class Zwsgr_GMB_Data_Process extends WP_Background_Process {

        protected $action = 'zwsgr_gmb_data_process';

        // Process each file by logging account names
        protected function task($zwsr_batch_data) {

            $zwsgr_gmb_data              = isset($zwsr_batch_data['zwsgr_gmb_data']) ? $zwsr_batch_data['zwsgr_gmb_data'] : [];
            $this->$zwsgr_gmb_data_type  = isset($zwsr_batch_data['zwsgr_gmb_data_type']) ? $zwsr_batch_data['zwsgr_gmb_data_type'] : null;
            $this->zwsgr_current_index   = isset($zwsr_batch_data['zwsgr_current_index']) ? $zwsr_batch_data['zwsgr_current_index'] : null;
            $zwsgr_account_number        = isset($zwsr_batch_data['zwsgr_account_number']) ? $zwsr_batch_data['zwsgr_account_number'] : [];
            $zwsgr_location_code         = isset($zwsr_batch_data['zwsgr_location_code']) ? $zwsr_batch_data['zwsgr_location_code'] : [];
            $this->next_page_token       = isset($zwsgr_gmb_data['nextPageToken']) ? $zwsgr_gmb_data['nextPageToken'] : null;

            if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_accounts') {
                $this->process_zwsgr_gmb_accounts($zwsgr_gmb_data);
            } else if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_locations') {
                $this->process_zwsgr_gmb_locations($zwsgr_gmb_data, $zwsgr_account_number);
            } else if ($this->$zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {
                $this->process_zwsgr_gmb_reviews($zwsgr_gmb_data, $zwsgr_account_number, '2519044039477379423');
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
                    $zwsgr_account_name = isset($account['name']) ? sanitize_text_field($account['name']) : '';
                    $account_number = $zwsgr_account_name ? ltrim(strrchr($zwsgr_account_name, '/'), '/') : '';
                    
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

        protected function process_zwsgr_gmb_reviews ($zwsgr_gmb_data, $zwsgr_account_number, $zwsgr_location_code) {

            if ( isset( $zwsgr_gmb_data['reviews'] ) && is_array( $zwsgr_gmb_data['reviews'] ) ) {
                foreach ( $zwsgr_gmb_data['reviews'] as $zwsgr_review ) {
            
                    $review_id = $zwsgr_review['reviewId'];
                    
                    // Check if the review already exists
                    $existing_review_query = new WP_Query([
                        'post_type'  => 'zwsgr_reviews',
                        'meta_query' => [
                            [
                                'key'   => 'zwsgr_review_id',
                                'value' => $review_id,
                            ],
                        ],
                    ]);
            
                    if ( $existing_review_query->have_posts() ) {
                        // Review exists, update it
                        $existing_review_query->the_post();
                        $post_id = get_the_ID();
                    } else {
                        // Review does not exist, insert it
                        $zwsgr_review_data = [
                            'post_title'   => $zwsgr_review['reviewer']['displayName'] ?? 'Anonymous',
                            'post_content' => $zwsgr_review['comment'] ?? '',
                            'post_status'  => 'publish',
                            'post_type'    => 'zwsgr_reviews',
                            'post_date'      => isset( $zwsgr_review['createTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwsgr_review['createTime'] ) ) : current_time( 'mysql' ),
                            'post_modified'  => isset( $zwsgr_review['updateTime'] ) ? date( 'Y-m-d H:i:s', strtotime( $zwsgr_review['updateTime'] ) ) : current_time( 'mysql' ),
                        ];
                        
                        $post_id = wp_insert_post( $zwsgr_review_data );
                    }
            
                    // Check if post was inserted or updated successfully
                    if ( $post_id && ! is_wp_error( $post_id ) ) {

                        // Download and set the featured image
                        // if ( ! empty( $zwsgr_review['reviewer']['profilePhotoUrl'] ) ) {
                        //     $zwsgr_image_url = $zwsgr_review['reviewer']['profilePhotoUrl'];
                        //     $zwsgr_image_id = $this->zwsgr_upload_image_to_media_library( $zwsgr_image_url, $zwsgr_post_id );

                        //     if ( $zwsgr_image_id && ! is_wp_error( $zwsgr_image_id ) ) {
                        //         set_post_thumbnail( $zwsgr_post_id, $zwsgr_image_id );
                        //     }
                        // }

                        // Update custom fields
                        update_post_meta( $post_id, 'zwsgr_review_id', $review_id );
                        update_post_meta( $post_id, 'zwsgr_reviewer_name', $zwsgr_review['reviewer']['displayName'] ?? 'Anonymous' );
                        update_post_meta( $post_id, 'zwsgr_star_rating', $zwsgr_review['starRating'] );
                        if ( isset( $zwsgr_review['reviewReply'] ) ) {
                            update_post_meta( $post_id, 'zwsgr_reply_comment', $zwsgr_review['reviewReply']['comment'] );
                            update_post_meta( $post_id, 'zwsgr_reply_update_time', $zwsgr_review['reviewReply']['updateTime'] );
                        }
                    } else {
                        error_log( "Failed to insert/update review: " . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : "Unknown error" ) );
                    }
            
                    // Reset post data after each query
                    wp_reset_postdata();
                }
            }            

            return;

        }

        public function zwsgr_upload_image_to_media_library( $zwsgr_image_url, $zwsgr_post_id ) {
            $zwsgr_image_data = file_get_contents( $zwsgr_image_url );
            $zwsgr_filename = basename( $zwsgr_image_url );
        
            // Set upload directory
            $zwsgr_upload_dir = wp_upload_dir();
            $zwsgr_img_file = $zwsgr_upload_dir['path'] . '/' . $zwsgr_filename;
        
            // Save image file
            file_put_contents( $zwsgr_img_file, $zwsgr_image_data );
        
            // Check the file type and create attachment
            $zwsgr_wp_filetype = wp_check_filetype( $zwsgr_filename, null );
            $zwsgr_attachment = array(
                'post_mime_type' => $zwsgr_wp_filetype['type'],
                'post_title'     => sanitize_file_name( $zwsgr_filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
        
            // Insert the attachment and generate metadata
            $zwsgr_attach_id = wp_insert_attachment( $zwsgr_attachment, $zwsgr_img_file, $zwsgr_post_id );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $zwsgr_attach_data = wp_generate_attachment_metadata( $zwsgr_attach_id, $zwsgr_img_file );
            wp_update_attachment_metadata( $zwsgr_attach_id, $zwsgr_attach_data );
        
            return $zwsgr_attach_id;
        }

        protected function complete() {

            parent::complete();

            // Update status to complete
            update_option('zwsgr_batch_status', [
                'status' => 'completed',
                'zwsgr_current_index' => $this->zwsgr_current_index
            ]);

            error_log('zwsgr_batch_status' . print_r(get_option('zwsgr_batch_status'), true));
            
            $batch_processing = new Zwsgr_Batch_Processing();

            if (!empty($this->next_page_token)) {
                
                update_option('zwsgr_batch_process_status', true);
                $batch_processing->zwsgr_update_current_index($this->zwsgr_current_index + 1);
                sleep(0.5);

                $batch_processing->zwsgr_fetch_gmb_data(true, $this->next_page_token);

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

        public function zwsgr_fetch_gmb_data($zwsgr_internal_call = false, $next_page_token = false) {

            $zwsgr_gmb_api = new ZWSGR_GMB_API( 'ya29.a0AeDClZDlkZimDuP8GeTEwR7L1KoXN8_l51BTnx6RFu68EPVG6acQYeV97pQvTcGcHnDLVPOoBGeBckNiXgyNm8aYE7R55DJZMhAQb9_yXdmVQjZyiSBdcBn7VaDRgOKa9QkixuMmkM_vdc9kUUUZ7ZB0iOmZGEJHd3tJDvSgcgaCgYKAVoSARMSFQHGX2Mibb5InM2u4mLk1tp22xrdPQ0177' );

            if (!$zwsgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer('zwsr_batch_processing_nonce', 'security');

                // If nonce verification fails
                if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'zwsr_batch_processing_nonce')) {
                    wp_send_json_error(array('message' => 'Nonce verification failed.'));
                    return;
                }

            }

            $zwsgr_gmb_data_type  = isset($_POST['zwsgr_gmb_data_type']) ? sanitize_text_field($_POST['zwsgr_gmb_data_type']) : get_option('zwsgr_gmb_data_type');
            $zwsgr_account_number = isset($_POST['zwsgr_account_number']) ? sanitize_text_field($_POST['zwsgr_account_number']) : get_option('zwsgr_account_number');
            $zwsgr_location_code  = isset($_POST['zwsgr_location_code']) ? sanitize_text_field($_POST['zwsgr_location_code']) : get_option('zwsgr_location_code');

            update_option('zwsgr_gmb_data_type', $zwsgr_gmb_data_type);
            update_option('zwsgr_account_number', $zwsgr_account_number);
            update_option('zwsgr_location_code', $zwsgr_location_code);

            $zwsgr_current_index = $this->zwsgr_fetch_current_index();

            if ($zwsgr_gmb_data_type == 'zwsgr_gmb_accounts') {
                $zwsgr_gmb_data = $zwsgr_gmb_api->get_accounts();
            } else if ($zwsgr_gmb_data_type == 'zwsgr_gmb_locations') {
                $zwsgr_gmb_data = $zwsgr_gmb_api->get_locations($zwsgr_account_number);
            } else if ($zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {
                $zwsgr_gmb_data = $zwsgr_gmb_api->get_reviews('116877069734578727132', '2519044039477379423', $next_page_token);
            }

            if (!empty($zwsgr_gmb_data)) {

                // Prepare data to be pushed to the queue
                $zwsgr_push_data_to_queue = [
                    'zwsgr_gmb_data'       => $zwsgr_gmb_data,
                    'zwsgr_gmb_data_type'  => $zwsgr_gmb_data_type,
                    'zwsgr_account_number' => $zwsgr_account_number,
                    'zwsgr_location_code'  => $zwsgr_location_code,
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

                delete_option('zwsgr_batch_status');
                delete_option('zwsgr_batch_process_status');
                
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

    // Verify nonce for security
    check_ajax_referer('zwsr_batch_processing_nonce', 'security');

    // If nonce verification fails
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'zwsr_batch_processing_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
        return;
    }

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

    error_log(print_r($response, true));

    // Send a successful JSON response with the batch processing status
    wp_send_json_success($response);
}