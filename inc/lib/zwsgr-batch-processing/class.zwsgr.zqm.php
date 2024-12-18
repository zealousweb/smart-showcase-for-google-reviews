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

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_fetch_gmb_data($zwsgr_internal_call = false, $zwsgr_next_page_token = false, $zwsgr_gmb_data_type = null, $zwsgr_account_number = null, $zwsgr_location_number = null, $zwsgr_widget_id = null) {

            // Get the values from method parameters, $_POST, or options as fallback
            $this->zwsgr_widget_id               = isset($zwsgr_widget_id)                ? sanitize_text_field($zwsgr_widget_id)       : (isset($_POST['zwsgr_widget_id'])               ? sanitize_text_field($_POST['zwsgr_widget_id'])       : get_option('zwsgr_widget_id'));
            $this->zwsgr_gmb_data_type           = isset($zwsgr_gmb_data_type)            ? sanitize_text_field($zwsgr_gmb_data_type)   : (isset($_POST['zwsgr_gmb_data_type'])           ? sanitize_text_field($_POST['zwsgr_gmb_data_type'])   : get_post_meta($this->zwsgr_widget_id, 'zwsgr_gmb_data_type', true));
            $this->zwsgr_account_number          = isset($zwsgr_account_number)           ? sanitize_text_field($zwsgr_account_number)  : (isset($_POST['zwsgr_account_number'])          ? sanitize_text_field($_POST['zwsgr_account_number'])  : get_post_meta($this->zwsgr_widget_id, 'zwsgr_account_number', true));
            $this->zwsgr_location_number         = isset($zwsgr_location_number)          ? sanitize_text_field($zwsgr_location_number) : (isset($_POST['zwsgr_location_number'])         ? sanitize_text_field($_POST['zwsgr_location_number']) : get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_number', true));
            $this->zwsgr_location_new_review_uri = isset($zwsgr_location_new_review_uri)  ? esc_url($zwsgr_location_new_review_uri)     : (isset($_POST['zwsgr_location_new_review_uri']) ? esc_url($_POST['zwsgr_location_new_review_uri'])     : esc_url(get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_new_review_uri', true)));
            $this->zwsgr_location_name           = isset($zwsgr_location_name)            ? sanitize_text_field($zwsgr_location_name)   : (isset($_POST['zwsgr_location_name'])           ? sanitize_text_field($_POST['zwsgr_location_name'])   : sanitize_text_field(get_post_meta($this->zwsgr_widget_id, 'zwsgr_location_name', true)));
            $this->zwsgr_account_name            = isset($zwsgr_account_name)             ? sanitize_text_field($zwsgr_account_name)    : (isset($_POST['zwsgr_account_name'])            ? sanitize_text_field($_POST['zwsgr_account_name'])    : sanitize_text_field(get_post_meta($this->zwsgr_widget_id, 'zwsgr_account_name', true)));

            if (!$zwsgr_internal_call && defined('DOING_AJAX') && DOING_AJAX) {

                check_ajax_referer('zwsgr_queue_manager_nounce', 'security');

                if (empty($this->zwsgr_widget_id)) {

                    error_log('ZQM: Invalid Widget ID for' . $this->zwsgr_gmb_data_type);

                    wp_send_json_error(
                        array(
                            'error'   => 'invalid_widget_id',
                            'message' =>  'Widget ID is required',
                        ), 
                    400);

                }

                $zwsgr_reset_current_batch_index_status = $this->zwsgr_reset_current_batch_index($this->zwsgr_widget_id);

                if ($zwsgr_reset_current_batch_index_status) {

                    error_log('ZQM: There was an error while resetting batch index for' . $this->zwsgr_gmb_data_type .' & Widget ID '.$this->zwsgr_widget_id);

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

                error_log('ZQM: Invalid Access Token for' . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

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
                    
                error_log("ZQM: Invalid GMB data type: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
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

                    error_log("ZQM: Failed to push data to the queue: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
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

                    error_log("ZQM: Failed to save data to the queue: " . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);
                
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

                        if (empty($this->zwsgr_location_new_review_uri)) {
                            error_log("ZQM: Missing 'zwsgr_location_new_review_uri' for widget ID: " . $this->zwsgr_widget_id);
                        }

                        if (empty($this->zwsgr_location_name)) {
                            error_log("ZQM: Missing 'zwsgr_location_name' for widget ID: " . $this->zwsgr_widget_id);
                        }

                        if (!empty($this->zwsgr_location_new_review_uri) && !empty($this->zwsgr_location_name)) {
                            update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_new_review_uri', $this->zwsgr_location_new_review_uri);
                            update_post_meta($this->zwsgr_widget_id, 'zwsgr_location_name', $this->zwsgr_location_name);
                        }

                    }

                    // For AJAX requests, send a JSON error response
                    wp_send_json_success(
                        array(
                            'message' =>  'Batch Processing started.',
                        ), 
                    200);
                
                }

                if ( !isset($this->zwsgr_gmb_data['nextPageToken']) || empty($this->zwsgr_gmb_data['nextPageToken']) ) {

                    return array(
                        'success' => true,
                        'data'    => array(
                            'message'   => 'Batch Processing Completed'
                        ),
                    );

                }
            
            } else if (isset($this->zwsgr_gmb_response) && $this->zwsgr_gmb_response['success'] && empty($this->zwsgr_gmb_response['data'])) {
                
                // Log the error before resetting the index and deleting options
                error_log("ZQM: Batch processing error: Failed for" . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

                // Define a default error message
                $zwsgr_error_message = 'An unknown error occurred. Please try again.';

                // Use a switch statement for better clarity
                switch ($this->zwsgr_gmb_data_type) {
                    case 'zwsgr_gmb_accounts':
                        $zwsgr_error_message = 'No GMB Accounts found for your GMB account.';
                        break;
                    case 'zwsgr_gmb_locations':
                        $zwsgr_error_message = 'No Locations present. Please try again with a different GMB account. Thanks.';
                        break;
                    case 'zwsgr_gmb_reviews':
                        $zwsgr_error_message = 'No reviews found. Please try again with a different GMB account. Thanks.';
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
                error_log("ZQM: Batch processing" . 'error:' . $this->zwsgr_gmb_response['error']['status'] . 'message:' . $this->zwsgr_gmb_response['error']['message'] . $this->zwsgr_gmb_data_type .' & Widget ID ' . $this->zwsgr_widget_id .'& current index ' . $this->zwsgr_current_index);

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