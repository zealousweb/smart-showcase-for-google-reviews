<?php
/**
 * ZWSSGR_GMB_API Class
 *
 * Handles the API functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'ZWSSGR_GMB_API' ) ) {
    class ZWSSGR_GMB_API {

        private $zwssgr_access_token;
        private $zwssgr_base_url;
        private $zwssgr_api_version = 'v4';

        public function __construct( $zwssgr_access_token ) {

            $this->zwssgr_access_token = $zwssgr_access_token;
            
            $this->zwssgr_base_url = "https://mybusiness.googleapis.com/{$this->zwssgr_api_version}/";

            add_action('wp_ajax_zwssgr_fetch_oauth_url', array($this, 'zwssgr_fetch_oauth_url'));
            add_action('wp_ajax_zwssgr_delete_oauth_connection', array($this, 'zwssgr_delete_oauth_connection'));
            add_action('wp_ajax_zwssgr_add_update_review_reply', array($this, 'zwssgr_add_update_review_reply'));
            add_action('wp_ajax_zwssgr_delete_review_reply', array($this, 'zwssgr_delete_review_reply'));

        }

        /**
         * Makes an API request to the specified endpoint with optional parameters, method, version, and base URL.
         * Ensures the access token is valid before making the request.
         * 
         * @param string $zwssgr_api_endpoint The API endpoint to request.
         * @param array  $zwssgr_api_params   Optional query parameters for GET requests or body parameters for POST requests.
         * @param string $method             The HTTP method to use ('GET', 'POST', 'DELETE', etc.), default is 'GET'.
         * @param string $zwssgr_api_version  The API version to use (default: 'v4').
         * @param string $base_url           Optional base URL to override the default API URL.
         * @return array The decoded JSON response from the API.
         * @throws Exception If the API request fails or returns an error.
         */
        private function zwssgr_api_request( $zwssgr_api_endpoint, $zwssgr_api_params = [], $zwssgr_api_method = 'GET', $zwssgr_api_version = 'v4', $zwssgr_base_url = '' ) {

            // Construct the API URL
            $zwssgr_api_url = $this->build_api_url( $zwssgr_api_endpoint, $zwssgr_api_version, $zwssgr_base_url );

            $zwssgr_api_args = [
                'method'  => strtoupper($zwssgr_api_method),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->zwssgr_access_token,
                    'Accept'        => 'application/json',
                ],
                'timeout' => 30,
            ];

            if ( $zwssgr_api_method === 'GET' && ! empty( $zwssgr_api_params ) ) {
                $zwssgr_api_url = add_query_arg( $zwssgr_api_params, $zwssgr_api_url );
            } elseif ( in_array( $zwssgr_api_method, ['POST', 'PUT', 'DELETE'] ) && ! empty( $zwssgr_api_params ) ) {
                $zwssgr_api_args['body'] = json_encode( $zwssgr_api_params );
                $zwssgr_api_args['headers']['Content-Type'] = 'application/json';
            }

            try {

                // Make the API request
                $zwssgr_api_response = wp_remote_request( $zwssgr_api_url, $zwssgr_api_args );
        
                // Check if there was an error with the request
                if ( is_wp_error( $zwssgr_api_response ) ) {
                    throw new Exception( 'Request failed: ' . $zwssgr_api_response );
                }
        
                // Check the response status code
                $zwssgr_api_status_code = wp_remote_retrieve_response_code( $zwssgr_api_response );

                // Get the response body and decode it
                $zwssgr_api_response_body = wp_remote_retrieve_body( $zwssgr_api_response );

                if ($zwssgr_api_status_code !== 200) {
                    // Throw the exception with the details array
                    throw new Exception($zwssgr_api_response_body);
                }

                return array(
                    'success' => true,
                    'data' => json_decode( $zwssgr_api_response_body, true )
                );
        
            } catch (Exception $zwssgr_response_error) {

                 // Decode the JSON string back into an array for better handling
                $zwssgr_response_error = json_decode($zwssgr_response_error->getMessage(), true);

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    // For AJAX requests, send a JSON error response    
                    wp_send_json_error(array(
                        'error'  => $zwssgr_response_error['error']['status'],
                        'message' => $zwssgr_response_error['error']['message'],
                    ), 400);
                    

                } else {

                    return array(
                        'error'  => $zwssgr_response_error['error']['status'],
                        'message' => $zwssgr_response_error['error']['message'],
                    );
                    
                }

                return false;

            }

        }

        /**
         * Helper function to build the full API URL from base URL, version, and endpoint.
         * 
         * @param string $endpoint    The API endpoint.
         * @param string $api_version The API version to use.
         * @param string $base_url    The base URL to use; defaults to Google My Business API if not provided.
         * @return string The constructed API URL.
         */
        private function build_api_url( $endpoint, $api_version, $base_url = '' ) {
            // Set default base URL if not provided
            $base_url = $base_url ?: "https://mybusiness.googleapis.com/{$api_version}/";

            // Concatenate the base URL and endpoint, ensuring correct formatting
            return rtrim( $base_url, '/' ) . '/' . ltrim( $endpoint, '/' );
        }

        public function set_access_token($zwssgr_access_token) {
            $this->zwssgr_access_token = $zwssgr_access_token;
        }

        /**
         * Retrieves the valid access token, refreshing it if expired or not available.
         *
         * @return string The valid access token.
         * @throws Exception if token retrieval or refresh fails.
         */
        public function zwssgr_get_access_token() {

            // Check if the access token is valid, refresh if necessary
            if (!$this->zwssgr_is_access_token_valid()) {
                // Refresh the token if it's not valid
                return $this->zwssgr_refresh_access_token();
            }

            return $this->zwssgr_access_token;
        }

        /**
         * Checks if a valid access token exists in the transient.
         *
         * @return bool True if the access token is found and valid, false otherwise.
         */
        public function zwssgr_is_access_token_valid() {

            $zwssgr_access_token = get_transient('zwssgr_access_token');
            
            if ($zwssgr_access_token !== false) {
                $this->zwssgr_access_token = $zwssgr_access_token;
                return true;
            }
            
            return false;
        }

        /**
         * Fetches and stores a new access token using the refresh token.
         * 
         * @return string The new access token.
         * @throws Exception if the token refresh fails.
         */
        public function zwssgr_refresh_access_token() {

            // Get 'auth_code' and 'consent' from function params parameters
            $zwssgr_jwt_token = get_option('zwssgr_jwt_token');

            // Prepare the payload for the request
            $zwssgr_payload_data = [
                'zwssgr_jwt_token' => $zwssgr_jwt_token
            ];

            // Make the API request to get oauth URl.
            $zwssgr_response = $this->zwssgr_api_request( 'zwssgr/v1/get-access-token', $zwssgr_payload_data, 'POST', '', 'https://siteproofs.com/projects/zealousweb/plugindev/user-demo/wp-json/');

                // Check if the response is successful and contains the oauth URL
                if (isset($zwssgr_response['success']) && $zwssgr_response['success'] === true && isset($zwssgr_response['data']['access_token'])) {
                
                $access_token = $zwssgr_response['data']['access_token'];
                
                // Store the new access token in a transient for future use.
                set_transient('zwssgr_access_token', $access_token, 3600); // Store for 1 hour or as needed.
                
                // Return the access token.
                return $access_token;

            } else {

                return false;

            }
            

        }

        /**
         * Retrieves a list of Google My Business accounts.
         *
         * @param string|null $zwssgr_page_token Optional page token for paginated results.
         * 
         * @return array The list of accounts.
         * @throws Exception if the API request fails.
         */
        public function zwssgr_get_accounts( $zwssgr_page_token = null ) {

            // Add page token to parameters if provided.
            $zwssgr_api_params = $zwssgr_page_token ? [ 'pageToken' => $zwssgr_page_token ] : [];
            
            // Make the API request to the 'accounts' endpoint.
            $zwssgr_response = $this->zwssgr_api_request( 'accounts', $zwssgr_api_params, 'GET', 'v1' );

            return $zwssgr_response;

        }

        /**
         * Retrieves locations for a specific Google My Business account.
         *
         * @param string      $zwssgr_account_id   The ID of the account to fetch locations for.
         * @param string|null $zwssgr_page_token   Optional page token for paginated results.
         * 
         * @return array The list of locations.
         * @throws Exception if the API request fails.
         */
        public function zwssgr_get_locations( $zwssgr_account_id, $zwssgr_page_token = null ) {

            // Define the endpoint with account ID and query parameters.
            $zwssgr_api_endpoint = "accounts/{$zwssgr_account_id}/locations?readMask=name,title,metadata&pageSize=50";
            $zwssgr_api_params   = $zwssgr_page_token ? [ 'pageToken' => $zwssgr_page_token ] : [];

            // Make the API request to fetch locations.
            $zwssgr_response = $this->zwssgr_api_request( $zwssgr_api_endpoint, $zwssgr_api_params, 'GET', 'v1' );

            return $zwssgr_response;

        }

        /**
         * Fetches the profile media thumbnail for a specific location.
         *
         * @param string $zwssgr_account_id Google My Business account ID.
         * @param string $zwssgr_location_id Location ID for the desired profile thumbnail.
         * @return mixed API response with profile media data or an error.
         */
        public function zwssgr_get_location_thumbnail($zwssgr_account_id, $zwssgr_location_id, $zwssgr_page_token = null) {

            // Construct the API endpoint for profile media.
            $zwssgr_api_endpoint = "accounts/{$zwssgr_account_id}/locations/{$zwssgr_location_id}/media/profile";

            // Prepare optional query parameters (e.g., page token).
            $zwssgr_api_params = $zwssgr_page_token ? [ 'pageToken' => $zwssgr_page_token ] : [];

            // Execute the API request and return the response.
            return $this->zwssgr_api_request($zwssgr_api_endpoint, $zwssgr_api_params, 'GET', 'v4');
        }

        /**
         * Retrieves reviews for a specific location in a Google My Business account.
         *
         * @param string      $zwssgr_api_endpoint The account ID or other relevant endpoint part.
         * @param string      $zwssgr_location_id  The ID of the location to fetch reviews for.
         * @param string|null $zwssgr_page_token   Optional page token for paginated results.
         * 
         * @return array The list of reviews.
         * @throws Exception if the API request fails.
         */
        public function zwssgr_get_reviews( $zwssgr_account_id, $zwssgr_location_id, $zwssgr_page_token = null ) {

            // Define the reviews API endpoint using account and location IDs.
            $zwssgr_api_endpoint = "accounts/{$zwssgr_account_id}/locations/{$zwssgr_location_id}/reviews";
        
            // Prepare query parameters for pagination if a page token is provided.
            $zwssgr_api_params = [];
            if ($zwssgr_page_token) {
                $zwssgr_api_params['pageToken'] = $zwssgr_page_token;
            }

            // Make the API request to fetch reviews.
            $zwssgr_response = $this->zwssgr_api_request( $zwssgr_api_endpoint, $zwssgr_api_params, 'GET');

            return $zwssgr_response;
        }

        /**
         * Adds or updates a reply to a specific Google My Business review using the review ID.
         *
         * This function handles the addition or update of a reply to a review on Google My Business. 
         * It retrieves the required details (account number, location code, review ID) from the 
         * specified WordPress review post and sends the reply to the Google My Business API.
         *
         * @throws Exception If the API request fails or returns an error.
         */
        public function zwssgr_add_update_review_reply( ) {

            // Check nonce and AJAX referer
            check_ajax_referer('zwssgr_add_update_reply_nonce', 'security');

            // Retrieve POST values
            $zwssgr_wp_review_id    = isset($_POST['zwssgr_wp_review_id']) ? sanitize_text_field($_POST['zwssgr_wp_review_id']) : '';
            $zwssgr_reply_comment   = isset($_POST['zwssgr_reply_comment']) ? sanitize_text_field($_POST['zwssgr_reply_comment']) : '';
            $zwssgr_account_number  = get_post_meta($zwssgr_wp_review_id, 'zwssgr_account_number', true);
            $zwssgr_location_number   = get_post_meta($zwssgr_wp_review_id, 'zwssgr_location_number', true);
            $zwssgr_review_id       = get_post_meta($zwssgr_wp_review_id, 'zwssgr_review_id', true);

            // Check that all parameters are provided
            if ( empty( $zwssgr_wp_review_id ) || empty( $zwssgr_reply_comment ) || empty( $zwssgr_account_number ) || empty( $zwssgr_location_number ) || empty( $zwssgr_review_id ) ) {

                // For AJAX requests, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'missing_required_fields',
                        'message' => 'Missing required fields: Review ID, reply comment, account number, location code, or review ID.',
                    ), 
                400);

            }

            $zwssgr_access_token = $this->zwssgr_get_access_token();

            if ($zwssgr_access_token == false) {

                // For AJAX requests, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'invalid_access_token',
                        'message' => 'No Valid access token found.',
                ), 400);

            }

            // Prepare the payload for the request
            $zwssgr_payload_data = [
                'comment' => $zwssgr_reply_comment,
            ];

            // Construct the Google My Business endpoint URL using account, location, and review IDs
            $zwssgr_endpoint = "accounts/{$zwssgr_account_number}/locations/{$zwssgr_location_number}/reviews/{$zwssgr_review_id}/reply";

            // Make the API request to add/update the review reply on Google’s server
            $zwssgr_response = $this->zwssgr_api_request( $zwssgr_endpoint, $zwssgr_payload_data, 'PUT', 'v4');

            if (isset($zwssgr_response) && $zwssgr_response['success'] && isset($zwssgr_response['data']['comment']) && isset($zwssgr_response['data']['updateTime'])) {

                $zwssgr_reply_comment     = $zwssgr_response['data']['comment'];
                $zwssgr_reply_update_time = $zwssgr_response['data']['updateTime'];

                update_post_meta($zwssgr_wp_review_id, 'zwssgr_reply_comment', $zwssgr_reply_comment);
                update_post_meta($zwssgr_wp_review_id, 'zwssgr_reply_update_time', $zwssgr_reply_update_time);

                wp_send_json_success(
                    array(
                        'success' => true,
                        'message' =>  __('Reply updated successfully', 'smart-showcase-for-google-reviews'),
                    )
                );

            } else {

                // If there's an error in the response, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'gmb_api_error',
                        'message' => $zwssgr_response['error']['message'] ?? 'An Unknown error occurred.',
                ), 400);

            }
            
        }
        
        /**
         * Deletes a reply to a specific Google My Business review using the review ID.
         *
         * This function sends a DELETE request to the Google My Business API to remove
         * an existing reply associated with a specific review.
         *
         * @return void This function sends a JSON response back to the client.
         */
        public function zwssgr_delete_review_reply() {

            // Check nonce and AJAX referer for security
            check_ajax_referer('zwssgr_delete_review_reply', 'security');

            // Retrieve POST values
            $zwssgr_wp_review_id    = isset($_POST['zwssgr_wp_review_id']) ? sanitize_text_field($_POST['zwssgr_wp_review_id']) : '';
            $zwssgr_account_number  = get_post_meta($zwssgr_wp_review_id, 'zwssgr_account_number', true);
            $zwssgr_location_number   = get_post_meta($zwssgr_wp_review_id, 'zwssgr_location_number', true);
            $zwssgr_review_id       = get_post_meta($zwssgr_wp_review_id, 'zwssgr_review_id', true);

            // Check that all parameters are provided
            if ( empty( $zwssgr_wp_review_id ) || empty( $zwssgr_account_number ) || empty( $zwssgr_location_number ) || empty( $zwssgr_review_id ) ) {
                // For AJAX requests, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'missing_required_fields',
                        'message' => 'Missing required fields: Review ID, account number, location code, or review ID.',
                    ), 
                400);

            }

            // Get the access token required to authenticate with Google My Business API
            $zwssgr_access_token = $this->zwssgr_get_access_token();

            if ($zwssgr_access_token === false) {

                // For AJAX requests, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'invalid_access_token',
                        'message' => 'No valid access token found',
                    ), 
                400);

            }

            // Construct the Google My Business API endpoint URL for deleting the reply to the specified review
            $zwssgr_endpoint = "accounts/{$zwssgr_account_number}/locations/{$zwssgr_location_number}/reviews/{$zwssgr_review_id}/reply";

            // Send the DELETE request to the Google My Business API to delete the review reply
            $zwssgr_response = $this->zwssgr_api_request( $zwssgr_endpoint, [], 'DELETE', 'v4' );

            if (isset($zwssgr_response) && $zwssgr_response['success']) {

                // Delete the reply comment from the postmeta table if it's associated with the review
                if ( !empty( $zwssgr_wp_review_id ) ) {
                    delete_post_meta( $zwssgr_wp_review_id, 'zwssgr_reply_comment' );
                    delete_post_meta( $zwssgr_wp_review_id, 'zwssgr_reply_update_time' );
                }

                // Send a success response back to the client
                wp_send_json_success(
                    array(
                        'success' => true,
                        'message' =>  __('Reply deleted successfully', 'smart-showcase-for-google-reviews'),
                    )
                );

            } {

                // If there's an error in the response, send a JSON error response
                wp_send_json_error(
                    array(
                        'error'  => 'gmb_api_error',
                        'message' => $zwssgr_response['error']['message'] ?? 'An Unknown error occurred.',
                ), 400);

            }

        }

        public function zwssgr_fetch_oauth_url() {

            $zwssgr_current_user  = wp_get_current_user();
            $zwssgr_user_name     = $zwssgr_current_user->user_login;
            $zwssgr_user_site_url = admin_url('admin.php?page=zwssgr_connect_google');

            // Prepare the payload for the request
            $zwssgr_payload_data = [
                'zwssgr_user_name'     => $zwssgr_user_name,
                'zwssgr_user_site_url' => $zwssgr_user_site_url
            ];

            // Make the API request to get oauth URl.
            $zwssgr_response = $this->zwssgr_api_request( 'zwssgr/v1/auth', $zwssgr_payload_data, 'POST', '', 'https://siteproofs.com/projects/zealousweb/plugindev/user-demo/wp-json/');

            // Check if the response is successful and contains the oauth URL
            if (isset($zwssgr_response['success']) && $zwssgr_response['success'] === true && isset($zwssgr_response['data']['zwssgr_oauth_url'])) {
                
                $zwssgr_oauth_url = $zwssgr_response['data']['zwssgr_oauth_url'];

                // Return a success response with the OAuth URL
                wp_send_json_success(
                    array(
                        'message' => __('Redirecting to OAuth for authentication...', 'smart-showcase-for-google-reviews'),
                        'zwssgr_oauth_url' => esc_url_raw($zwssgr_oauth_url)
                    )
                );

            } else {

                // Return a failure message
                wp_send_json_error(
                    array(
                        'message' => __('Failed to generate OAuth URL or invalid response.', 'smart-showcase-for-google-reviews'),
                        'code' => 'oauth_url_error'
                    )
                );

            }

            wp_die();

        }
        
        public function zwssgr_fetch_jwt_token($zwssgr_request) {

            // Get 'auth_code' and 'consent' from function params parameters
            $zwssgr_auth_code = isset($_GET['auth_code']) ? sanitize_text_field(wp_unslash($_GET['auth_code'])) : '';

            // Prepare the payload for the request
            $zwssgr_payload_data = [
                'zwssgr_auth_code' => $zwssgr_auth_code
            ];

            // Make the API request to get oauth URl.
            $zwssgr_response = $this->zwssgr_api_request( 'zwssgr/v1/get-jwt-token', $zwssgr_payload_data, 'POST', '', 'https://siteproofs.com/projects/zealousweb/plugindev/user-demo/wp-json/');

            return $zwssgr_response;

        }

        /**
         * Handles the deletion of plugin-related data for the ZW Smart Showcase for Google Reviews plugin.
         *
         * This function:
         * 1. Deletes OAuth-related tokens (JWT token and access token) from the WordPress options and transients.
         * 2. Deletes custom post types (`zwssgr_request_data`, `zwssgr_reviews`, `zwssgr_data_widget`) 
         *    and their associated metadata from the database if requested via a POST parameter.
         * 3. Sends JSON responses to indicate success or failure of the operations.
         *
         * @return void
         */
        public function zwssgr_delete_oauth_connection() {

            // Check nonce and AJAX referer for security
            check_ajax_referer('zwssgr_delete_oauth_connection', 'security');

            global $wpdb;

            // Attempt to delete 'zwssgr_jwt_token' if it exists
            if (
                (get_option('zwssgr_jwt_token') && !delete_option('zwssgr_jwt_token')) || (get_option('zwssgr_gmb_email') && !delete_option('zwssgr_gmb_email')) || (get_transient('zwssgr_access_token') && !delete_transient('zwssgr_access_token'))
            ) {
                wp_send_json_error(
                    array(
                        'message' => __('Failed to delete JWT token.', 'smart-showcase-for-google-reviews'),
                        'code'    => 'delete_jwt_error'
                    )
                );
            }

            // Check if plugin data deletion is requested
            if (isset($_POST['zwssgr_delete_plugin_data']) && $_POST['zwssgr_delete_plugin_data'] === '1') {

                $zwssgr_post_types = array('zwssgr_request_data', 'zwssgr_reviews', 'zwssgr_data_widget');

                foreach ($zwssgr_post_types as $zwssgr_post_type) {
                    // Attempt to delete posts and metadata for each custom post type
                    $result = $wpdb->query(
                        $wpdb->prepare(
                            "DELETE p, pm 
                            FROM {$wpdb->posts} p 
                            LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
                            WHERE p.post_type = %s",
                            $zwssgr_post_type
                        )
                    );

                    if ($result === false) {
                        wp_send_json_error(
                            array(
                                'message' => sprintf(
                                    esc_html('Failed to delete data for custom post type: %s.', 'smart-showcase-for-google-reviews'),
                                    $zwssgr_post_type
                                ),
                                'code'    => 'delete_request_error'
                            )
                        );
                    }
                }
                
            }

            if (isset($_POST['zwssgr_delete_plugin_data']) && $_POST['zwssgr_delete_plugin_data'] === '1') {

                // Delete posts and their postmeta for the custom post type 'zwssgr_request_data'
                $zwssgr_delete_request_data = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE p, pm 
                        FROM {$wpdb->posts} p 
                        LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
                        WHERE p.post_type = %s",
                        'zwssgr_request_data'
                    )
                );
            
                // Delete posts and their postmeta for the custom post type 'zwssgr_reviews'
                $zwssgr_delete_reviews = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE p, pm 
                        FROM {$wpdb->posts} p 
                        LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
                        WHERE p.post_type = %s",
                        'zwssgr_reviews'
                    )
                );
            
                // Delete posts and their postmeta for the custom post type 'zwssgr_data_widget'
                $zwssgr_data_widget = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE p, pm 
                        FROM {$wpdb->posts} p 
                        LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
                        WHERE p.post_type = %s",
                        'zwssgr_data_widget'
                    )
                );

                // Check if any query failed
                if ($zwssgr_delete_request_data === false || $zwssgr_delete_reviews === false || $zwssgr_data_widget === false) {
                    wp_send_json_error(
                        array(
                            'message' => __('Failed to delete plugin data.', 'smart-showcase-for-google-reviews'),
                            'code' => 'delete_request_error'
                        )
                    );
                }

            }
        
            // If all operations succeeded, send success message
            wp_send_json_success(
                array(
                    'message' => __('Plugin data successfully deleted.', 'smart-showcase-for-google-reviews'),
                    'code' => 'delete_request_success'
                )
            );
        
            wp_die();
        }
        
        
    }
}