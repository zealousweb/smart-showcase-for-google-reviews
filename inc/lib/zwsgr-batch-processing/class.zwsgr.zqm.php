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
require_once( ZWSGR_DIR . '/inc/lib/api/class.' . ZWSGR_PREFIX . '.api.php' );

if (!class_exists('Zwsgr_Queue_Manager')) {

    class Zwsgr_Queue_Manager {

        private static $instance = null;

        private $zwsgr_bdp;

        private $zwsgr_gmb_api;

        private $zwsgr_gmb_response;

        private $zwsgr_gmb_data;

        private $zwsgr_widget_id;

        private $zwsgr_current_index;

        private $zwsgr_gmb_data_type;
        
        private $zwsgr_account_number;
        
        private $zwsgr_location_number;
        
        private $zwsgr_location_new_review_uri;
        
        private $zwsgr_location_name;

        private $zwsgr_account_name;

        private $zwsgr_access_token;

        public function __construct() {

            $this->zwsgr_bdp = new Zwsgr_GMB_Background_Data_Processor();

            $this->zwsgr_gmb_api  = new ZWSGR_GMB_API('');

            // Initialize Ajax Data Fetch
            add_action('wp_ajax_zwsgr_fetch_gmb_data',        array($this, 'zwsgr_fetch_gmb_data'));
            add_action('wp_ajax_nopriv_zwsgr_fetch_gmb_data', array($this, 'zwsgr_fetch_gmb_data'));

        }

        /**
		 * Custom log function for debugging.
		 *
		 * @param string $message The message to log.
		 */
		function zwsgr_debug_function( $message ) {
			// Define the custom log directory path.
			$log_dir = WP_CONTENT_DIR . '/plugins/smart-google-reviews'; // wp-content/plugins/smart-google-reviews
		
			// Define the log file path.
			$log_file = $log_dir . '/smart-google-reviews-debug.log';
		
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

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_fetch_gmb_data($zwsgr_internal_call = false, $zwsgr_next_page_token = false, $zwsgr_gmb_data_type = null, $zwsgr_account_number = null, $zwsgr_location_number = null, $zwsgr_widget_id = null) {

            // Get the values from method parameters, $_POST, or options as fallback
            $this->zwsgr_widget_id               = isset($zwsgr_widget_id)                ? sanitize_text_field($zwsgr_widget_id)               : (isset($_POST['zwsgr_widget_id'])               ? sanitize_text_field($_POST['zwsgr_widget_id'])               : get_option('zwsgr_widget_id'));
            $this->zwsgr_gmb_data_type           = isset($zwsgr_gmb_data_type)            ? sanitize_text_field($zwsgr_gmb_data_type)           : (isset($_POST['zwsgr_gmb_data_type'])           ? sanitize_text_field($_POST['zwsgr_gmb_data_type'])           : get_post_meta($this->zwsgr_widget_id, 'zwsgr_gmb_data_type', true));
            $this->zwsgr_account_number          = isset($zwsgr_account_number)           ? sanitize_text_field($zwsgr_account_number)          : (isset($_POST['zwsgr_account_number'])          ? sanitize_text_field($_POST['zwsgr_account_number'])          : get_post_meta($this->zwsgr_widget_id, 'zwsgr_account_number', true));
            $this->zwsgr_location_number         = isset($zwsgr_location_number)          ? sanitize_text_field($zwsgr_location_number)         : (isset($_POST['zwsgr_location_number'])         ? sanitize_text_field($_POST['zwsgr_location_number'])         : get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_number', true));
            $this->zwsgr_location_new_review_uri = isset($zwsgr_location_new_review_uri)  ? esc_url($zwsgr_location_new_review_uri)             : (isset($_POST['zwsgr_location_new_review_uri']) ? esc_url($_POST['zwsgr_location_new_review_uri'])             : esc_url(get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_new_review_uri', true)));
            $this->zwsgr_location_name           = isset($zwsgr_location_name)            ? sanitize_text_field($zwsgr_location_name)           : (isset($_POST['zwsgr_location_name'])           ? sanitize_text_field($_POST['zwsgr_location_name'])           : sanitize_text_field(get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_name', true)));
            $this->zwsgr_account_name            = isset($zwsgr_account_name)             ? sanitize_text_field($zwsgr_account_name)            : (isset($_POST['zwsgr_account_name'])            ? sanitize_text_field($_POST['zwsgr_account_name'])            : sanitize_text_field(get_post_meta($this->zwsgr_widget_id, 'zwsgr_account_name', true)));
            $this->zwsgr_location_all_review_uri = isset($zwsgr_location_all_review_uri)  ? sanitize_text_field($zwsgr_location_all_review_uri) : (isset($_POST['zwsgr_location_all_review_uri']) ? sanitize_text_field($_POST['zwsgr_location_all_review_uri']) : sanitize_text_field(get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_all_review_uri', true)));

            if (!$zwsgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer('zwsgr_queue_manager_nounce', 'security');

                if (empty($this->zwsgr_widget_id)) {

                    $this->zwsgr_debug_function('ZQM: Invalid Widget ID for' . $this->zwsgr_gmb_data_type);

                    wp_send_json_error(
                        array(
                            'error'   => 'invalid_widget_id',
                            'message' =>  'Widget ID is required',
                        ), 
                    400);

                }

                $zwsgr_reset_current_batch_index_status = $this->zwsgr_reset_current_batch_index($this->zwsgr_widget_id);

                if ($zwsgr_reset_current_batch_index_status) {

                    $this->zwsgr_debug_function('ZQM: There was an error while resetting batch index for' . $this->zwsgr_gmb_data_type .' & Widget ID '.$this->zwsgr_widget_id);

                    wp_send_json_error(
                        array(
                            'error'   => 'reset_batch_index_errror',
                            'message' =>  'There was an error while trying to reset batch index.',
                        ), 
                    400);

                }

            }

            $this->zwsgr_access_token =  $this->zwsgr_gmb_api->zwsgr_get_access_token();

            if (!empty($this->zwsgr_access_token)) {
                
                $this->zwsgr_gmb_api->set_access_token($this->zwsgr_access_token);

                update_post_meta($this->zwsgr_widget_id, 'zwsgr_gmb_data_type', $this->zwsgr_gmb_data_type);

                if ($this->zwsgr_widget_id && $this->zwsgr_account_number) {
                    
                    update_option('zwsgr_widget_id', $this->zwsgr_widget_id);
                    update_post_meta($this->zwsgr_widget_id, 'zwsgr_account_number', $this->zwsgr_account_number);
                    if (!empty($this->zwsgr_location_number)) {
                        update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_number', $this->zwsgr_location_number);
                    }

                }

            } else {

                $this->zwsgr_debug_function('ZQM: Invalid Access Token for' . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

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
                        'zwsgr_current_index' => $zwsgr_current_index
                    ),
                );
                
            }

            switch ($this->zwsgr_gmb_data_type) {
                case 'zwsgr_gmb_accounts':
                    $this->zwsgr_gmb_response = $this->zwsgr_gmb_api->zwsgr_get_accounts($zwsgr_next_page_token);
                    break;
                case 'zwsgr_gmb_locations':
                    $this->zwsgr_gmb_response = $this->zwsgr_gmb_api->zwsgr_get_locations($this->zwsgr_account_number, $zwsgr_next_page_token);
                    break;
                case 'zwsgr_gmb_reviews':
                    $this->zwsgr_gmb_response = $this->zwsgr_gmb_api->zwsgr_get_reviews($this->zwsgr_account_number, $this->zwsgr_location_number, $zwsgr_next_page_token);        
                    break;
                default:
                    
                $this->zwsgr_debug_function("ZQM: Invalid GMB data type: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    
                    // For AJAX requests, send a JSON error response
                    wp_send_json_error(
                        array(
                            'error'  => 'invalid_gmb_data_type',
                            'message' =>  'Invalid GMB data type: ' . esc_html($this->zwsgr_gmb_data_type),
                        ), 
                    400);

                }

                return array(
                    'success' => false,
                    'data'    => array (
                        'error'   => 'invalid_gmb_data_type',
                        'message' =>  'Invalid GMB data type: ' . esc_html($this->zwsgr_gmb_data_type)
                    ),
                );

            }

            if (isset($this->zwsgr_gmb_response) && $this->zwsgr_gmb_response['success'] && !empty($this->zwsgr_gmb_response['data'])) {

                $this->zwsgr_gmb_data = $this->zwsgr_gmb_response['data'];

                // Prepare data to be pushed to the queue
                $zwsgr_push_data_to_queue = [
                    'zwsgr_widget_id'       => $this->zwsgr_widget_id,
                    'zwsgr_gmb_data'        => $this->zwsgr_gmb_data,
                    'zwsgr_account_number'  => $this->zwsgr_account_number,
                    'zwsgr_location_number' => $this->zwsgr_location_number
                ];

                // Push data to the queue
                $zwsgr_data_pushed = $this->zwsgr_bdp->push_to_queue($zwsgr_push_data_to_queue);

                if (!$zwsgr_data_pushed) {

                    $this->zwsgr_debug_function("ZQM: Failed to push data to the queue: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
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

                $zwsgr_data_saved = $this->zwsgr_bdp->save()->dispatch();

                if (!$zwsgr_data_saved) {

                    $this->zwsgr_debug_function("ZQM: Failed to save data to the queue: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
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

                    $zwsgr_widget_title = $this->zwsgr_account_name;

                    if (!empty($this->zwsgr_location_name)) {
                        $zwsgr_widget_title .= ' - ' . $this->zwsgr_location_name;
                    }
                        
                    // Prepare post data for updating the title
                    $zwsgr_widget_data = [
                        'ID'         => $this->zwsgr_widget_id,
                        'post_title' => sanitize_text_field($zwsgr_widget_title),
                    ];

                    wp_update_post($zwsgr_widget_data);

                    if ($this->zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {

                        $missing_data = [];
                    
                        // Check if any required fields are empty and log the missing fields.
                        if (empty($this->zwsgr_location_all_review_uri)) {
                            $missing_data[] = 'zwsgr_location_all_review_uri';
                        }
                    
                        if (empty($this->zwsgr_location_new_review_uri)) {
                            $missing_data[] = 'zwsgr_location_new_review_uri';
                        }
                    
                        if (empty($this->zwsgr_location_name)) {
                            $missing_data[] = 'zwsgr_location_name';
                        }
                    
                        // If any data is missing, log an error and skip the update.
                        if (!empty($missing_data)) {
                            $this->zwsgr_debug_function("ZQM: Missing data for widget ID: " . $this->zwsgr_widget_id . " - Missing: " . implode(', ', $missing_data));
                            return; // Optionally return to stop further execution.
                        }
                    
                        // Update post meta if values are not empty
                        update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_all_review_uri', $this->zwsgr_location_all_review_uri);
                        update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_new_review_uri', $this->zwsgr_location_new_review_uri);
                        update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_name', $this->zwsgr_location_name);
                    }

                    // Prepare the object to store the widget ID and processing status
                    $zwsgr_batch_data = array(
                        'zwsgr_widget_id' => $this->zwsgr_widget_id,
                        'zwsgr_batch_in_processing' => 'true'
                    );

                    // Update the option with both the widget ID and processing status
                    update_option('zwsgr_batch_in_processing', $zwsgr_batch_data);

                    // For AJAX requests, send a JSON error response
                    wp_send_json_success(
                        array(
                            'message' =>  'Batch Processing started.',
                        ), 
                    200);
                
                }

                if ( !isset($this->zwsgr_gmb_data['nextPageToken']) || empty($this->zwsgr_gmb_data['nextPageToken']) ) {

                    $zwsgr_get_location_thumbnail_response = $this->zwsgr_gmb_api->zwsgr_get_location_thumbnail($this->zwsgr_account_number, $this->zwsgr_location_number);

                    if (isset($zwsgr_get_location_thumbnail_response) && $zwsgr_get_location_thumbnail_response['success'] && !empty($zwsgr_get_location_thumbnail_response['data'])) {
                        update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_thumbnail_url', $zwsgr_get_location_thumbnail_response['data']['sourceUrl']);
                    }

                    return array(
                        'success' => true,
                        'data'    => array(
                            'message'   => 'Batch Processing Completed'
                        ),
                    );

                }
            
            } else if (isset($this->zwsgr_gmb_response) && $this->zwsgr_gmb_response['success'] && empty($this->zwsgr_gmb_response['data'])) {
                
                // Log the error before resetting the index and deleting options
                $this->zwsgr_debug_function("ZQM: Batch processing error: Failed for" . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

                // Define a default error message
                $zwsgr_error_message = 'An unknown error occurred. Please try again.';

                // Use a switch statement for better clarity
                switch ($this->zwsgr_gmb_data_type) {
                    case 'zwsgr_gmb_accounts':
                        $zwsgr_error_message = 'It looks like we couldn\'t find any Google My Business accounts linked to this profile. Please check your account settings or try again later.';
                        break;
                    case 'zwsgr_gmb_locations':
                        $zwsgr_error_message = 'No locations found under this Google My Business account. Please verify your account or try again with a different one.';
                        break;
                    case 'zwsgr_gmb_reviews':
                        $zwsgr_error_message = 'There are no reviews available for this location at the moment. Please try again later or choose a different location.';
                        break;
                }
                
                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response    
                    wp_send_json_error([
                        'error'  => 'empty_api_response',
                        'message' => $zwsgr_error_message
                    ], 200);
                    

                } else {

                    return array(
                        'success' => false,
                        'data'    => array(
                            'error'  => 'empty_api_response',
                            'message' => $zwsgr_error_message
                        ),
                    );
                    
                }

            } {

                // Log the error before resetting the index and deleting options
                $this->zwsgr_debug_function("ZQM: Batch processing" . 'error:' . $this->zwsgr_gmb_response['error']['status'] . 'message:' . $this->zwsgr_gmb_response['error']['message'] . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response    
                    wp_send_json_error([
                        'error'  => $this->zwsgr_gmb_response['error']['status'],
                        'message' => $this->zwsgr_gmb_response['error']['message'],
                    ], 400);
                    

                } else {

                    return array(
                        'success' => false,
                        'data'    => array(
                            'error'  => $this->zwsgr_gmb_response['error']['status'],
                            'message' => $this->zwsgr_gmb_response['error']['message'],
                        ),
                    );
                    
                }

            }

            return false;
        
        }

        // Helper function to get the current index from the database
        public function zwsgr_get_current_batch_index($zwsgr_widget_id) {
            $zwsgr_current_index = get_post_meta($zwsgr_widget_id, 'zwsgr_current_index', true);
            return $this->zwsgr_current_index = !empty($zwsgr_current_index) ? $zwsgr_current_index : 1;
        }

        // Helper function to update the current index in the database
        public function zwsgr_update_current_batch_index($zwsgr_widget_id, $index) {
            return update_post_meta($zwsgr_widget_id, 'zwsgr_current_index', intval($index));
        }

        // Helper function to reset current index
        public function zwsgr_reset_current_batch_index($zwsgr_widget_id) {
            return delete_post_meta($zwsgr_widget_id, 'zwsgr_current_index');
        }

    }

    Zwsgr_Queue_Manager::get_instance();

}