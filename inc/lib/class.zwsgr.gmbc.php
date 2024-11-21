<?php
/**
 * Zwsgr_Google_My_Business_Connector Class
 *
 * Handles integration with Google My Business for the Smart Google Reviews plugin. 
 * Manages OAuth authentication, data synchronization, and admin interface for connecting 
 * Google accounts, fetching GMB data, and managing reviews.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */
if ( ! class_exists( 'Zwsgr_Google_My_Business_Connector' ) ) {

    class Zwsgr_Google_My_Business_Connector {

        private static $instance = null;

        private $client;

        public function __construct() {

            // Hook to add admin menu items
            add_action('admin_menu', [$this, 'zwsgr_gmbc_add_menu_items'], 20);

            // Hook to handle auth_code during connect google redirection
            add_action('admin_init', [$this, 'zwsgr_handle_auth_code']);

            add_action('admin_notices', [$this, 'zwsgr_connect_google_popup_callback']);


            $this->client = new ZWSGR_GMB_API('');

        }

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_gmbc_add_menu_items() {
            
            add_submenu_page (
                'zwsgr_dashboard',
                'Connect Google',
                'Connect Google',
                'manage_options',
                'zwsgr_connect_google',
                [$this, 'zwsgr_connect_google_callback'],
                1
            );
        
            add_submenu_page(
                null,
                'Fetch GMB Data',
                'Fetch GMB Data Callback',
                'manage_options',
                'zwsgr_fetch_gmb_data',
                [$this, 'zwsgr_fetch_gmb_data_callback']
            );
            
        }
        
        // Display the "Connect with Google" button and process oAuth connection request
        public function zwsgr_connect_google_callback() {

            // Check if the JWT token is present in the database
            $zwsgr_jwt_token = get_option('zwsgr_jwt_token');

            if (!empty($zwsgr_jwt_token)) {
                // Display connected to  message and disconnect button if JWT token exists
                echo '<div class="zwsgr-gmbc-outer-wrapper">
                    <div class="zwsgr-gmbc-container">
                        <div class="zwsgr-gmbc-inner-wrapper">
                            <span style="margin-right: 10px;">Congratulations! Connected to Google</span>
                            <a href="" class="button button-danger zwsgr-submit-btn disconnect-google" style="margin-left: 10px;">Disconnect</a>
                        </div>
                    </div>
                </div>';
            } else {
                // Display the "Connect with Google" button if no JWT token exists
                echo '<div class="zwsgr-gmbc-outer-wrapper">
                    <div class="zwsgr-gmbc-container">
                        <div id="fetch-gmb-auth-url-wrapper" class="zwsgr-gmbc-inner-wrapper">
                            <span style="margin-right: 10px;"> Connect with your Google account to seamlessly fetch and showcase reviews. </span>
                            <div id="fetch-gmb-auth-url-response"></div>
                            <button type="submit" class="button button-primary zwsgr-submit-btn" id="fetch-gmb-auth-url">Connect with Google</button>
                        </div>
                    </div>
                </div>';
            }

        }

        // If applicable display the "Connect with Google" button on each page of sgr plugin
        public function zwsgr_connect_google_popup_callback() {

            // Check if we're in the admin panel
            if (is_admin()) {

                // Get the current page or post type
                $zwsgr_current_page      = isset($_GET['page']) ? $_GET['page'] : '';
                $zwsgr_current_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';

                // Define the valid pages and post types
                $zwsgr_valid_pages = array(
                    'zwsgr_dashboard',
                    'zwsgr_settings'
                );
                
                $zwsgr_valid_post_types = array(
                    'zwsgr_reviews',
                    'zwsgr_data_widget'
                );

                // Check if the current page is a valid page or if it's a valid post type edit page
                if (in_array($zwsgr_current_page, $zwsgr_valid_pages) || in_array($zwsgr_current_post_type, $zwsgr_valid_post_types)) {
                    
                    // Check if the JWT token is present in the database
                    $zwsgr_jwt_token = get_option('zwsgr_jwt_token');

                    if (empty($zwsgr_jwt_token)) {
                        $this->zwsgr_connect_google_callback();
                    }

                }
            }

        }       

        // Handle the 'auth_code' flow during admin_init
        public function zwsgr_handle_auth_code() {

            if (isset($_GET['auth_code']) && isset($_GET['consent']) && $_GET['consent'] === 'true') {

                $zwsgr_auth_code = sanitize_text_field($_GET['auth_code']);

                // Fetch the JWT token
                $zwsgr_fetch_jwt_token_response = $this->client->zwsgr_fetch_jwt_token($zwsgr_auth_code);

                if (
                    isset($zwsgr_fetch_jwt_token_response['success']) &&
                    $zwsgr_fetch_jwt_token_response['success'] === true &&
                    isset($zwsgr_fetch_jwt_token_response['data']['data']['zwsgr_jwt_token'])
                ) {

                    $zwsgr_jwt_token = $zwsgr_fetch_jwt_token_response['data']['data']['zwsgr_jwt_token'];
                    
                    // Save the JWT token in the database
                    update_option('zwsgr_jwt_token', $zwsgr_jwt_token);
                    
                    // Temporarily store the authentication status in the database
                    set_transient('zwsgr_auth_status', true, 600);

                    // Create a new widget after successful auth initialization
                    $zwsgr_new_widget_id = wp_insert_post(array(
                        'post_type'   => 'zwsgr_data_widget',
                        'post_status' => 'publish',
                        'post_title'  => 'New GMB Widget ' . wp_generate_uuid4(),
                    ));

                    if ($zwsgr_new_widget_id) {
                        // Redirect back to fetch gmb data page with the widget ID as a parameter
                        wp_redirect(admin_url('admin.php?page=zwsgr_fetch_gmb_data&zwsgr_widget_id=' . $zwsgr_new_widget_id));
                        exit;
                    } else {
                        wp_redirect(admin_url('admin.php?page=zwsgr_fetch_gmb_data'));
                        exit;
                    }

                } else {

                    set_transient('zwsgr_auth_status', false, 600);

                    // Redirect back to the submenu page with error notice
                    wp_redirect(admin_url('admin.php?page=zwsgr_fetch_gmb_data'));
                    exit;

                }

            }

        }
        
        public function zwsgr_fetch_gmb_data_callback() {
        
            echo '<div id="fetch-gmb-data">';

                $zwsgr_widget_id = isset($_GET['zwsgr_widget_id']) ? sanitize_text_field($_GET['zwsgr_widget_id']) : '';

                // Check if the widget ID is not empty
                if (!empty($zwsgr_widget_id)) {

                    // Get the post meta for the widget ID
                    $zwsgr_account_number = get_post_meta($zwsgr_widget_id, 'zwsgr_account_number', true);

                    // Get all posts where the zwsgr_account_number matches
                    $zwsgr_request_data = get_posts(array(
                        'post_type'      => 'zwsgr_request_data',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'meta_key'       => 'zwsgr_account_number',
                        'meta_value'     => '', // Empty value to get all posts for the custom field
                        'fields'         => 'ids', // Only return post IDs
                    ));

                    if ($zwsgr_request_data) {

                        // Display the select dropdown if accounts are found
                        echo '<select id="zwsgr-account-select" name="zwsgr_account">
                            <option value="">Select an Account</option>';
                        // Loop through each post
                        foreach ($zwsgr_request_data as $post_id) {
                            // Get the account number and name for each post
                            $account_number = get_post_meta($post_id, 'zwsgr_account_number', true);
                            $account_name = get_the_title($post_id);

                            // Check if the account number matches the GET parameter
                            $selected = ($account_number === $zwsgr_account_number) ? 'selected' : '';

                            // Output each account option, with the selected attribute if matching
                            echo '<option value="' . esc_attr($account_number) . '" ' . $selected . '>' . esc_html($account_name) . '</option>';
                        }

                        echo '</select>';

                        // Retrieve the selected location number from the post meta
                        $zwsgr_location_number = get_post_meta($zwsgr_widget_id, 'zwsgr_location_number', true);

                        $zwsgr_request_data_id = get_posts(array(
                            'post_type'      => 'zwsgr_request_data',
                            'posts_per_page' => 1,
                            'post_status'    => 'publish',
                            'meta_key'       => 'zwsgr_account_number',
                            'meta_value'     => $zwsgr_account_number,
                            'fields'         => 'ids',
                        ))[0] ?? null;
                    
                        if (!$zwsgr_request_data_id) {
                            // If no post is found, return early
                            return;
                        }
                    
                        // Retrieve existing locations or initialize an empty array
                        $zwsgr_account_locations = get_post_meta($zwsgr_request_data_id, 'zwsgr_account_locations', true);
        
                        // Check if the custom field has a value
                        if ( $zwsgr_account_locations ) {
                            echo '<select id="zwsgr-location-select" name="zwsgr_location">
                                    <option value="">Select a Location</option>';
                                    foreach ( $zwsgr_account_locations as $zwsgr_account_location ) {
                                        $zwsgr_account_location_id = $zwsgr_account_location['name'] ? ltrim( strrchr( $zwsgr_account_location['name'], '/' ), '/' ) : '';
                                        $selected = ($zwsgr_account_location_id === $zwsgr_location_number) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($zwsgr_account_location_id) . '" ' . $selected . '>' . esc_html($zwsgr_account_location['name']) . '</option>';
                                    }                    
                            echo '</select>
                            <a href="#" class="button button-secondary" id="fetch-gmd-reviews" data-fetch-type="zwsgr_gmb_reviews">
                                Fetch Reviews
                            </a>';
                        }

                    } else {
                        echo ' <a href="#" class="button button-secondary" id="fetch-gmd-accounts" data-fetch-type="zwsgr_gmb_accounts">
                            Fetch Accounts
                        </a>';
                    }

                } else {
                    echo 'There was an error while trying to edit the widget';
                }
        
            // Reset post data
            wp_reset_postdata();
        
            echo '</div>';
        }

    }

    new Zwsgr_Google_My_Business_Connector();
        
}