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

        private $zwsgr_zqm;

        public function __construct() {

            // Hook to add admin menu items
            add_action('admin_menu', [$this, 'zwsgr_gmbc_add_menu_items'], 20);

            // Hook to handle auth_code during connect google redirection
            add_action('admin_init', [$this, 'zwsgr_handle_auth_code']);

            add_action('admin_notices', [$this, 'zwsgr_connect_google_popup_callback']);


            $this->client = new ZWSGR_GMB_API('');

            $this->zwsgr_zqm = new Zwsgr_Queue_Manager();

        }

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwsgr_gmbc_add_menu_items() {

            $zwsgr_gmb_email = get_option('zwsgr_gmb_email');

            if (empty($zwsgr_gmb_email)) {
                add_submenu_page (
                    'zwsgr_dashboard',
                    'Connect Google',
                    'Connect Google',
                    'manage_options',
                    'zwsgr_connect_google',
                    [$this, 'zwsgr_connect_google_callback'],
                    1
                );
            }

        }
        
        // Display the "Connect with Google" button and process oAuth connection request
        public function zwsgr_connect_google_callback() {

            // Check if the JWT token is present in the database
            $zwsgr_jwt_token = get_option('zwsgr_jwt_token');

            if (!empty($zwsgr_jwt_token)) {
                // Display connected to  message and disconnect button if JWT token exists
                echo '<div class="zwsgr-gmbc-outer-wrapper">
                    <div class="zwsgr-gmbc-container">
                        <div id="disconnect-gmb-auth" class="zwsgr-gmbc-inner-wrapper">
                            <span style="margin-right: 10px;">Congratulations! Connected to Google</span>
                            <div id="disconnect-gmb-auth-response"></div>
                            <div class="zwsgr-caution-div">
                                <input type="checkbox" id="delete-all-data" name="delete_all_data" style="margin-right: 7.5px;">
                                <label for="delete-all-data" style="color: red;" class="zwsgr-chechbox-label">
                                    Caution: Check this box to permanently delete all data.
                                </label>
                            </div>
                            <div class="danger-note">This action cannot be undone. Ensure you want to proceed.</div>
                            <a href="" class="button button-danger zwsgr-submit-btn" id="disconnect-auth">Disconnect</a>
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
                            <a href="" class="button button-primary zwsgr-submit-btn" id="fetch-gmb-auth-url">Connect with Google</a>
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
                    'zwsgr_data_widget',
                    'zwsgr_request_data'
                );

                // Check if the current page is a valid page or if it's a valid post type edit page
                if (in_array($zwsgr_current_page, $zwsgr_valid_pages) || in_array($zwsgr_current_post_type, $zwsgr_valid_post_types)) {
                    
                    // Check if the JWT token is present in the database
                    $zwsgr_jwt_token = get_option('zwsgr_jwt_token');

                    if (empty($zwsgr_jwt_token)) {
                        $this->zwsgr_connect_google_callback();
                    }

                }

                // Safely check for 'tab' and 'settings' in the query string
                if (isset($_GET['tab'], $_GET['settings']) && $_GET['tab'] === 'google' && $_GET['settings'] === 'disconnect-auth') {
                    $this->zwsgr_connect_google_callback();
                }

            }

        }       

        // Handle the 'auth_code' flow during admin_init
        public function zwsgr_handle_auth_code() {

            if (isset($_GET['auth_code']) && isset($_GET['user_email']) && isset($_GET['consent']) && $_GET['consent'] === 'true') {

                $zwsgr_auth_code = sanitize_text_field($_GET['auth_code']);
                $zwsgr_gmb_email = sanitize_text_field($_GET['user_email']);
                
                update_option('zwsgr_gmb_email', $zwsgr_gmb_email);

                // Fetch the JWT token
                $zwsgr_fetch_jwt_token_response = $this->client->zwsgr_fetch_jwt_token($zwsgr_auth_code);

                if (
                    isset($zwsgr_fetch_jwt_token_response['success']) && 
                    $zwsgr_fetch_jwt_token_response['success'] === true && 
                    isset($zwsgr_fetch_jwt_token_response['data']['zwsgr_jwt_token'])
                ) {

                    $zwsgr_jwt_token = $zwsgr_fetch_jwt_token_response['data']['zwsgr_jwt_token'];
                    
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

                        $this->zwsgr_zqm->zwsgr_fetch_gmb_data(true, false, 'zwsgr_gmb_accounts');

                        // Redirect back to fetch gmb data page with the widget ID as a parameter
                        wp_redirect(admin_url('admin.php?page=zwsgr_widget_configurator&tab=tab-fetch-data&zwsgr_widget_id=' . $zwsgr_new_widget_id));
                        exit;

                    } else {
                        wp_redirect(admin_url('edit.php?post_type=zwsgr_data_widget'));
                        exit;

                    }

                } else {

                    set_transient('zwsgr_auth_status', false, 600);

                    // Redirect back to the submenu page with error notice
                    wp_redirect(admin_url('admin.php?page=zwsgr_connect_google'));
                    exit;

                }

            }

        }
        
        public function zwsgr_fetch_gmb_data_callback() {

            $zwsgr_widget_id = isset($_GET['zwsgr_widget_id']) ? sanitize_text_field($_GET['zwsgr_widget_id']) : '';

            if (!empty($zwsgr_widget_id)) {
                // Fetch the GMB email from the options table
                $zwsgr_gmb_email = get_option('zwsgr_gmb_email');
                $zwgr_data_processed       = get_post_meta($zwsgr_widget_id, 'zwgr_data_sync_once', true);
                $zwsgr_gmb_data_type       = get_post_meta($zwsgr_widget_id, 'zwsgr_gmb_data_type', true);
                $zwgr_data_processing_init = get_post_meta($zwsgr_widget_id, 'zwgr_data_processing_init', true);
                $zwsgr_batch_progress      = get_post_meta($zwsgr_widget_id, 'zwsgr_batch_progress', true);
                $zwsgr_disabled_class      = ($zwgr_data_processing_init === 'true') ? 'disabled' : '';
                $zwsgr_button_text         = ($zwgr_data_processed === 'true') && ($zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') ? 'Sync Reviews' : 'Fetch Reviews';

                // If the value is empty, set it to 0
                if (empty($zwsgr_batch_progress)) {
                    $zwsgr_batch_progress = 0;
                }

            }            
        
            echo '<div id="fetch-gmb-data">
                <div class="response"></div>
                <div class="progress-bar '.$zwgr_data_processing_init.'">
                    <progress id="progress" value="'.$zwsgr_batch_progress.'" max="100"></progress>
                    <span id="progress-percentage"> '.$zwsgr_batch_progress.'% </span>
                </div>
                <div class="fetch-gmb-inner-data">';
                    // Check if the widget ID is not empty
                    if (!empty($zwsgr_widget_id)) {

                        // Get the post meta for the widget ID
                        $zwsgr_account_number = get_post_meta($zwsgr_widget_id, 'zwsgr_account_number', true);

                        

                        // Get posts where the zwsgr_gmb_email meta key matches the value from options
                        $zwsgr_request_data = get_posts(array(
                            'post_type'      => 'zwsgr_request_data',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'zwsgr_gmb_email', // The meta key to query
                                    'value'   => $zwsgr_gmb_email,  // The value to match
                                    'compare' => '=',                // Exact match
                                ),
                            ),
                            'fields'         => 'ids', // Only return post IDs
                        ));

                        if ($zwsgr_request_data) {

                            // Display the select dropdown if accounts are found
                            echo '<select id="zwsgr-account-select" name="zwsgr_account" class="zwsgr-input-text '.$zwsgr_disabled_class.'">
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
                                echo '<select id="zwsgr-location-select" name="zwsgr_location" class="zwsgr-input-text  '.$zwsgr_disabled_class.'">
                                    <option value="">Select a Location</option>';
                                    foreach ( $zwsgr_account_locations as $zwsgr_account_location ) {
                                        $zwsgr_account_location_id = $zwsgr_account_location['name'] ? ltrim( strrchr( $zwsgr_account_location['name'], '/' ), '/' ) : '';
                                        $selected = ($zwsgr_account_location_id === $zwsgr_location_number) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($zwsgr_account_location_id) . '" ' . $selected . ' data-new-review-url="'.$zwsgr_account_location['metadata']['newReviewUri'].'">' . esc_html($zwsgr_account_location['title']) . '</option>';
                                    }              
                                echo '</select>
                                <a href="#" class="button button-secondary zwsgr-submit-btn '.$zwsgr_disabled_class.'" id="fetch-gmd-reviews" data-fetch-type="zwsgr_gmb_reviews">
                                    '.$zwsgr_button_text.'
                                </a>';
                            }

                        } else {
                            echo ' <a href="#" class="button button-secondary zwsgr-submit-btn '.$zwsgr_disabled_class.'" id="fetch-gmd-accounts" data-fetch-type="zwsgr_gmb_accounts">
                                Fetch Accounts
                            </a>';
                        }

                    } else {
                        echo 'There was an error while trying to edit the widget';
                    }
                // Reset post data
                wp_reset_postdata();
                echo '</div>
            </div>';
        }

    }

    Zwsgr_Google_My_Business_Connector::get_instance();
        
}