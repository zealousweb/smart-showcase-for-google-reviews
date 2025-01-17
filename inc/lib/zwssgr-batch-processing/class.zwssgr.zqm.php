<?php
/**
 * Zwssgr_Queue_Manager Class
 *
 * Handles the Batch Queue functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


require_once( ZWSSGR_DIR . '/inc/lib/zwssgr-batch-processing/class.' . ZWSSGR_PREFIX . '.bdp.php' );
require_once( ZWSSGR_DIR . '/inc/lib/api/class.' . ZWSSGR_PREFIX . '.api.php' );

if (!class_exists('Zwssgr_Queue_Manager')) {

    class Zwssgr_Queue_Manager {

        private static $instance = null;
        private $zwssgr_bdp, $zwssgr_gmb_api, $zwssgr_gmb_response, $zwssgr_gmb_data, $zwssgr_widget_id, $zwssgr_current_index, $zwssgr_gmb_data_type, $zwssgr_account_number, $zwssgr_location_number, $zwssgr_location_new_review_uri, $zwssgr_location_name, $zwssgr_account_name, $zwssgr_access_token;

        public $zwssgr_location_all_review_uri;

        public function __construct() {

            $this->zwssgr_bdp = new Zwssgr_GMB_Background_Data_Processor();

            $this->zwssgr_gmb_api  = new ZWSSGR_GMB_API('');

            // Initialize Ajax Data Fetch
            add_action('wp_ajax_zwssgr_fetch_gmb_data',        array($this, 'zwssgr_fetch_gmb_data'));
            add_action('wp_ajax_nopriv_zwssgr_fetch_gmb_data', array($this, 'zwssgr_fetch_gmb_data'));

        }

        /**
		 * Custom log function for debugging.
		 *
		 * @param string $message The message to log.
		 */
		function zwssgr_debug_function( $zwssgr_message ) {
            // Define the custom log directory path.

            $zwssgr_log_dir = ZWSSGR_UPLOAD_DIR.'/smart-showcase-for-google-reviews/';
        
            // Define the log file path.
            $zwssgr_log_file = $zwssgr_log_dir . '/smart-showcase-for-google-reviews-debug.log';
        
            // Check if the directory exists, if not create it.
            if ( ! file_exists( $zwssgr_log_dir ) ) {
                wp_mkdir_p( $zwssgr_log_dir );
            }
        
            // Initialize the WP_Filesystem.
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();
        
            global $wp_filesystem;
        
            // Format the log entry with UTC timestamp using gmdate().
            $zwssgr_log_entry = sprintf( "[%s] %s\n", gmdate( 'Y-m-d H:i:s' ), $zwssgr_message );
        
            // Write the log entry to the file using WP_Filesystem.
            if ( $wp_filesystem->exists( $zwssgr_log_file ) || $wp_filesystem->put_contents( $zwssgr_log_file, $zwssgr_log_entry, FS_CHMOD_FILE ) ) {
                $wp_filesystem->put_contents( $zwssgr_log_file, $zwssgr_log_entry, FS_CHMOD_FILE );
            }
        }

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwssgr_fetch_gmb_data($zwssgr_internal_call = false, $zwssgr_next_page_token = false, $zwssgr_gmb_data_type = null, $zwssgr_account_number = null, $zwssgr_location_number = null, $zwssgr_widget_id = null) {

            if (!$zwssgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {
                check_ajax_referer('zwssgr_queue_manager_nounce', 'security');
            }

            // Get the values from method parameters, $_POST, or options as fallback
            $this->zwssgr_widget_id               = isset($zwssgr_widget_id)                ? sanitize_text_field($zwssgr_widget_id)               : (isset($_POST['zwssgr_widget_id'])               ? sanitize_text_field(wp_unslash($_POST['zwssgr_widget_id']))               : get_option('zwssgr_widget_id'));
            $this->zwssgr_gmb_data_type           = isset($zwssgr_gmb_data_type)            ? sanitize_text_field($zwssgr_gmb_data_type)           : (isset($_POST['zwssgr_gmb_data_type'])           ? sanitize_text_field(wp_unslash($_POST['zwssgr_gmb_data_type']))           : get_post_meta($this->zwssgr_widget_id, 'zwssgr_gmb_data_type', true));
            $this->zwssgr_account_number          = isset($zwssgr_account_number)           ? sanitize_text_field($zwssgr_account_number)          : (isset($_POST['zwssgr_account_number'])          ? sanitize_text_field(wp_unslash($_POST['zwssgr_account_number']))          : get_post_meta($this->zwssgr_widget_id, 'zwssgr_account_number', true));
            $this->zwssgr_location_number         = isset($zwssgr_location_number)          ? sanitize_text_field($zwssgr_location_number)         : (isset($_POST['zwssgr_location_number'])         ? sanitize_text_field(wp_unslash($_POST['zwssgr_location_number']))         : get_post_meta($this->zwssgr_widget_id, 'zwssgr_location_number', true));
            $this->zwssgr_location_new_review_uri = isset($zwssgr_location_new_review_uri)  ? esc_url_raw($zwssgr_location_new_review_uri)         : (isset($_POST['zwssgr_location_new_review_uri']) ? esc_url_raw(wp_unslash($_POST['zwssgr_location_new_review_uri']))         : esc_url_raw(get_post_meta($this->zwssgr_widget_id, 'zwssgr_location_new_review_uri', true)));
            $this->zwssgr_location_name           = isset($zwssgr_location_name)            ? sanitize_text_field($zwssgr_location_name)           : (isset($_POST['zwssgr_location_name'])           ? sanitize_text_field(wp_unslash($_POST['zwssgr_location_name']))           : sanitize_text_field(get_post_meta($this->zwssgr_widget_id, 'zwssgr_location_name', true)));
            $this->zwssgr_account_name            = isset($zwssgr_account_name)             ? sanitize_text_field($zwssgr_account_name)            : (isset($_POST['zwssgr_account_name'])            ? sanitize_text_field(wp_unslash($_POST['zwssgr_account_name']))            : sanitize_text_field(get_post_meta($this->zwssgr_widget_id, 'zwssgr_account_name', true)));
            $this->zwssgr_location_all_review_uri = isset($zwssgr_location_all_review_uri)  ? sanitize_text_field($zwssgr_location_all_review_uri) : (isset($_POST['zwssgr_location_all_review_uri']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_location_all_review_uri'])) : sanitize_text_field(get_post_meta($this->zwssgr_widget_id, 'zwssgr_location_all_review_uri', true)));

            if (!$zwssgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {

                if (empty($this->zwssgr_widget_id)) {

                    $this->zwssgr_debug_function('ZQM: Invalid Widget ID for' . $this->zwssgr_gmb_data_type);

                    wp_send_json_error(
                        array(
                            'error'   => 'invalid_widget_id',
                            'message' =>  'Widget ID is required',
                        ), 
                    400);

                }

                $zwssgr_reset_current_batch_index_status = $this->zwssgr_reset_current_batch_index($this->zwssgr_widget_id);

                if ($zwssgr_reset_current_batch_index_status) {

                    $this->zwssgr_debug_function('ZQM: There was an error while resetting batch index for' . $this->zwssgr_gmb_data_type .' & Widget ID '.$this->zwssgr_widget_id);

                    wp_send_json_error(
                        array(
                            'error'   => 'reset_batch_index_errror',
                            'message' =>  'There was an error while trying to reset batch index.',
                        ), 
                    400);

                }

            }

            $this->zwssgr_access_token =  $this->zwssgr_gmb_api->zwssgr_get_access_token();

            if (!empty($this->zwssgr_access_token)) {
                
                $this->zwssgr_gmb_api->set_access_token($this->zwssgr_access_token);

                update_post_meta($this->zwssgr_widget_id, 'zwssgr_gmb_data_type', $this->zwssgr_gmb_data_type);

                if ($this->zwssgr_widget_id && $this->zwssgr_account_number) {
                    
                    update_option('zwssgr_widget_id', $this->zwssgr_widget_id);
                    update_post_meta($this->zwssgr_widget_id, 'zwssgr_account_number', $this->zwssgr_account_number);
                    if (!empty($this->zwssgr_location_number)) {
                        update_post_meta($this->zwssgr_widget_id, 'zwssgr_location_number', $this->zwssgr_location_number);
                    }

                }

            } else {

                $this->zwssgr_debug_function('ZQM: Invalid Access Token for' . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response
                    wp_send_json_error([
                        'error'  => 'invalid_access_token',
                        'message' =>  'No Valid access token found'
                    ], 400);
                    
                }

                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'invalid_access_token',
                        'message'  => 'No Valid access token found',
                        'zwssgr_current_index' => $zwssgr_current_index
                    ),
                );
                
            }

            switch ($this->zwssgr_gmb_data_type) {
                case 'zwssgr_gmb_accounts':
                    $this->zwssgr_gmb_response = $this->zwssgr_gmb_api->zwssgr_get_accounts($zwssgr_next_page_token);
                    break;
                case 'zwssgr_gmb_locations':
                    $this->zwssgr_gmb_response = $this->zwssgr_gmb_api->zwssgr_get_locations($this->zwssgr_account_number, $zwssgr_next_page_token);
                    break;
                case 'zwssgr_gmb_reviews':
                    $this->zwssgr_gmb_response = $this->zwssgr_gmb_api->zwssgr_get_reviews($this->zwssgr_account_number, $this->zwssgr_location_number, $zwssgr_next_page_token);        
                    break;
                default:
                    
                $this->zwssgr_debug_function("ZQM: Invalid GMB data type: " . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);
                
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    
                    // For AJAX requests, send a JSON error response
                    wp_send_json_error(
                        array(
                            'error'  => 'invalid_gmb_data_type',
                            'message' =>  'Invalid GMB data type: ' . esc_html($this->zwssgr_gmb_data_type),
                        ), 
                    400);

                }

                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'invalid_gmb_data_type',
                        'message' =>  'Invalid GMB data type: ' . esc_html($this->zwssgr_gmb_data_type)
                    ),
                );

            }

            if (isset($this->zwssgr_gmb_response) && $this->zwssgr_gmb_response['success'] && !empty($this->zwssgr_gmb_response['data'])) {

                $this->zwssgr_gmb_data = $this->zwssgr_gmb_response['data'];

                // Prepare data to be pushed to the queue
                $zwssgr_push_data_to_queue = [
                    'zwssgr_widget_id'       => $this->zwssgr_widget_id,
                    'zwssgr_gmb_data'        => $this->zwssgr_gmb_data,
                    'zwssgr_account_number'  => $this->zwssgr_account_number,
                    'zwssgr_location_number' => $this->zwssgr_location_number
                ];

                // Push data to the queue
                $zwssgr_data_pushed = $this->zwssgr_bdp->push_to_queue($zwssgr_push_data_to_queue);

                if (!$zwssgr_data_pushed) {

                    $this->zwssgr_debug_function("ZQM: Failed to push data to the queue: " . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);
                
                    if (defined('DOING_AJAX') && DOING_AJAX) {
                        wp_send_json_error(
                            array(
                                'error' => 'queue_push_error',
                                'message' => 'Failed to push data to the queue.',
                            ), 
                        400);
                    }

                    return array(
                        'success' => false,
                        'data'    => array (
                            'error'   => 'queue_push_error',
                            'message' =>  'Failed to push data to the queue.'
                        ),
                    );
                
                }

                $zwssgr_data_saved = $this->zwssgr_bdp->save()->dispatch();

                if (!$zwssgr_data_saved) {

                    $this->zwssgr_debug_function("ZQM: Failed to save data to the queue: " . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);
                
                    if (defined('DOING_AJAX') && DOING_AJAX) {
                        wp_send_json_error(
                            array(
                                'error' => 'queue_data_save_error',
                                'message' => 'Failed to save data to the queue.',
                            ), 
                        400);
                    }

                    return array(
                        'success' => false,
                        'data'    => array (
                            'error'   => 'queue_data_save_error',
                            'message' =>  'Failed to save data to the queue.'
                        ),
                    );
                
                }

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    $zwssgr_account_name = !empty($this->zwssgr_account_name) ? $this->zwssgr_account_name : get_post_meta($this->zwssgr_widget_id, 'zwssgr_account_name', true);
                    $zwssgr_location_name = !empty($this->zwssgr_location_name) ? $this->zwssgr_location_name : get_post_meta($this->zwssgr_widget_id, 'zwssgr_location_name', true);

                    $zwssgr_widget_title = $zwssgr_account_name;

                    if (!empty($zwssgr_location_name)) {
                        $zwssgr_widget_title .= ' - ' . $zwssgr_location_name;
                    }

                    // Prepare the widget title only if it is not empty
                    if (!empty($zwssgr_widget_title)) {
                        $zwssgr_widget_data = [
                            'ID'         => $this->zwssgr_widget_id,
                            'post_title' => sanitize_text_field($zwssgr_widget_title),
                        ];

                        // Update the post with the new title
                        wp_update_post($zwssgr_widget_data);
                    }

                    if ($this->zwssgr_gmb_data_type == 'zwssgr_gmb_reviews') {

                        $missing_data = [];
                    
                        // Check if any required fields are empty and log the missing fields.
                        if (empty($this->zwssgr_location_all_review_uri)) {
                            $missing_data[] = 'zwssgr_location_all_review_uri';
                        }
                    
                        if (empty($this->zwssgr_location_new_review_uri)) {
                            $missing_data[] = 'zwssgr_location_new_review_uri';
                        }
                    
                        if (empty($this->zwssgr_location_name)) {
                            $missing_data[] = 'zwssgr_location_name';
                        }
                    
                        if (!empty($missing_data)) {
                            $this->zwssgr_debug_function("ZQM: Missing data for widget ID: " . $this->zwssgr_widget_id . " - Missing: " . implode(', ', $missing_data));
                            return;
                        }

                        // Update post meta if values are not empty
                        update_post_meta($this->zwssgr_widget_id, 'zwssgr_location_all_review_uri', $this->zwssgr_location_all_review_uri);
                        update_post_meta($this->zwssgr_widget_id, 'zwssgr_location_new_review_uri', $this->zwssgr_location_new_review_uri);

                        if (!empty($zwssgr_account_name)) {
                            update_post_meta($this->zwssgr_widget_id, 'zwssgr_account_name', $zwssgr_account_name);
                        }

                        if (!empty($zwssgr_location_name)) {
                            update_post_meta($this->zwssgr_widget_id, 'zwssgr_location_name', $zwssgr_location_name);
                        }

                    }

                    // Prepare the object to store the widget ID and processing status
                    $zwssgr_batch_data = array(
                        'zwssgr_widget_id' => $this->zwssgr_widget_id,
                        'zwssgr_batch_in_processing' => 'true'
                    );

                    // Update the option with both the widget ID and processing status
                    update_option('zwssgr_batch_in_processing', $zwssgr_batch_data);

                    // For AJAX requests, send a JSON error response
                    wp_send_json_success(
                        array(
                            'message' =>  'Batch Processing started.',
                        ), 
                    200);
                
                }

                if ( !isset($this->zwssgr_gmb_data['nextPageToken']) || empty($this->zwssgr_gmb_data['nextPageToken']) ) {

                    $zwssgr_get_location_thumbnail_response = $this->zwssgr_gmb_api->zwssgr_get_location_thumbnail($this->zwssgr_account_number, $this->zwssgr_location_number);

                    if (isset($zwssgr_get_location_thumbnail_response) && isset($zwssgr_get_location_thumbnail_response['success']) && $zwssgr_get_location_thumbnail_response['success'] && !empty($zwssgr_get_location_thumbnail_response['data'])) {
                        update_post_meta($this->zwssgr_widget_id, 'zwssgr_location_thumbnail_url', $zwssgr_get_location_thumbnail_response['data']['sourceUrl']);
                    }

                    return array(
                        'success' => true,
                        'data'    => array(
                            'message'   => 'Batch Processing Completed'
                        ),
                    );

                }
            
            } else if (isset($this->zwssgr_gmb_response) && $this->zwssgr_gmb_response['success'] && empty($this->zwssgr_gmb_response['data'])) {
                
                // Log the error before resetting the index and deleting options
                $this->zwssgr_debug_function("ZQM: Batch processing error: Failed for" . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);

                // Define a default error message
                $zwssgr_error_message = esc_html__('An unknown error occurred. Please try again.', 'smart-showcase-for-google-reviews');
                // Use a switch statement for better clarity
                switch ($this->zwssgr_gmb_data_type) {
                    case 'zwssgr_gmb_accounts':
                        $zwssgr_error_message = esc_html__('It looks like we couldn\'t find any Google My Business accounts linked to this profile. Please check your account settings or try again later.', 'smart-showcase-for-google-reviews');
                        break;
                    case 'zwssgr_gmb_locations':
                        $zwssgr_error_message = esc_html__('No locations found under this Google My Business account. Please verify your account or try again with a different one.', 'smart-showcase-for-google-reviews');
                        break;
                    case 'zwssgr_gmb_reviews':
                        $zwssgr_error_message = esc_html__('There are no reviews available for this location at the moment. Please try again later or choose a different location.', 'smart-showcase-for-google-reviews');
                        break;
                }
                
                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response    
                    wp_send_json_error([
                        'error'  => 'empty_api_response',
                        'message' => $zwssgr_error_message
                    ], 200);
                    

                } else {

                    return array(
                        'success' => false,
                        'data'    => array(
                            'error'  => 'empty_api_response',
                            'message' => $zwssgr_error_message
                        ),
                    );
                    
                }

            } {

                // Log the error before resetting the index and deleting options
                $this->zwssgr_debug_function("ZQM: Batch processing" . 'error:' . $this->zwssgr_gmb_response['error']['status'] . 'message:' . $this->zwssgr_gmb_response['error']['message'] . $this->zwssgr_gmb_data_type .' & Widget ID ' . $this->zwssgr_widget_id .'& current index ' . $this->zwssgr_current_index);

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response    
                    wp_send_json_error([
                        'error'  => $this->zwssgr_gmb_response['error']['status'],
                        'message' => $this->zwssgr_gmb_response['error']['message'],
                    ], 400);
                    

                } else {

                    return array(
                        'success' => false,
                        'data'    => array(
                            'error'  => $this->zwssgr_gmb_response['error']['status'],
                            'message' => $this->zwssgr_gmb_response['error']['message'],
                        ),
                    );
                    
                }

            }

            return false;
        
        }

        // Helper function to get the current index from the database
        public function zwssgr_get_current_batch_index($zwssgr_widget_id) {
            $zwssgr_current_index = get_post_meta($zwssgr_widget_id, 'zwssgr_current_index', true);
            return $this->zwssgr_current_index = !empty($zwssgr_current_index) ? $zwssgr_current_index : 1;
        }

        // Helper function to update the current index in the database
        public function zwssgr_update_current_batch_index($zwssgr_widget_id, $index) {
            return update_post_meta($zwssgr_widget_id, 'zwssgr_current_index', intval($index));
        }

        // Helper function to reset current index
        public function zwssgr_reset_current_batch_index($zwssgr_widget_id) {
            return delete_post_meta($zwssgr_widget_id, 'zwssgr_current_index');
        }

    }

    Zwssgr_Queue_Manager::get_instance();

}