<?php
/**
 * Zwssgr_Google_My_Business_Connector Class
 *
 * Handles integration with Google My Business for the Smart Showcase for Google Reviews plugin. 
 * Manages OAuth authentication, data synchronization, and admin interface for connecting 
 * Google accounts, fetching GMB data, and managing reviews.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Zwssgr_Google_My_Business_Connector' ) ) {

    class Zwssgr_Google_My_Business_Connector {

        private static $instance = null;

        private $zwssgr_client, $zwssgr_zqm;

        public function __construct() {

            // Hook to add admin menu items
            add_action('admin_menu', [$this, 'zwssgr_gmbc_add_menu_items'], 20);

            // Hook to handle auth_code during connect google redirection
            add_action('admin_init', [$this, 'zwssgr_handle_auth_code']);
            add_action('admin_notices', [$this, 'zwssgr_connect_google_popup_callback']);
            add_action('admin_notices', [$this, 'zwssgr_connect_google_success_callback']);

            $this->zwssgr_client = new ZWSSGR_GMB_API('');
            $this->zwssgr_zqm = new Zwssgr_Queue_Manager();
        }

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function zwssgr_gmbc_add_menu_items() {

            if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
                return;
            }

            $zwssgr_gmb_email = get_option('zwssgr_gmb_email');

            if (empty($zwssgr_gmb_email) || (isset($_GET['auth_code']) && isset($_GET['user_email']) && isset($_GET['consent']) && $_GET['consent'] === 'true')) {
                add_submenu_page (
                    'zwssgr_dashboard',
                    __('Connect Google', 'smart-showcase-for-google-reviews'),
                    __('Connect Google', 'smart-showcase-for-google-reviews'),
                    'manage_options',
                    'zwssgr_connect_google',
                    [$this, 'zwssgr_connect_google_callback'],
                    1
                );
            }
        }
        
        // Display the "Connect with Google" button and process oAuth connection request
        public function zwssgr_connect_google_callback() {

            // Check if the JWT token is present in the database
            $zwssgr_jwt_token = get_option('zwssgr_jwt_token');
            $zwssgr_gmb_email = get_option('zwssgr_gmb_email');

            if (!empty($zwssgr_jwt_token)) {
                // Display connected to  message and disconnect button if JWT token exists
                echo '<div class="zwssgr-gmbc-outer-wrapper">
                    <div class="zwssgr-gmbc-container">
                        <div id="close-gmb-wrap" class="zwssgr-close-gmb-wrap"><a href="'.esc_url(admin_url('admin.php?page=zwssgr_settings')).'" class="zwssgr-close-button"></a></div>
                        <div id="disconnect-gmb-auth" class="zwssgr-gmbc-inner-wrapper disconnect-gmb-auth">
                            <div id="disconnect-gmb-auth-response" class="zwssgr-disconnect-gmb-auth-response"></div>
                            <div class="zwssgr-caution-div">
                                <input type="checkbox" id="delete-all-data" name="delete_all_data" class="delete-all-data">
                                <label for="delete-all-data" class="zwssgr-chechbox-label">
                                    '. esc_html__('Caution: Check this box to permanently delete all data.', 'smart-showcase-for-google-reviews').'
                                </label>
                            </div>
                            <div class="danger-note">This action cannot be undone. Ensure you want to proceed.</div>
                            <div class="zwssgr-th-label">
								<label class="zwssgr-th">
									' . esc_html($zwssgr_gmb_email) . '
								</label>
							</div>
                            <a href="" class="button button-danger zwssgr-submit-btn" id="disconnect-auth">'. esc_html__('Disconnect', 'smart-showcase-for-google-reviews').'</a>
                        </div>
                    </div>
                </div>';
            } else {
                // Display the "Connect with Google" button if no JWT token exists
                echo '<div class="zwssgr-gmbc-outer-wrapper">
                    <div class="zwssgr-gmbc-container">
                        <div id="fetch-gmb-auth-url-wrapper" class="zwssgr-gmbc-inner-wrapper">
                            <span> '. esc_html__( 'The Smart Google Reviews plugin showcases reviews from your Google Business profile. It retrieves publicly available details using the Google Business API, such as the reviewer\'s name, profile picture, and review content. This plugin does not collect or store any personal data on this website. All displayed reviews are subject to Google’s Privacy Policy.', 'smart-showcase-for-google-reviews' ) . '</span>';
                            $zwssgr_error_notice = get_transient('zwssgr_error_notice');
                            if ($zwssgr_error_notice) {
                                echo '<div class="notice notice-error"><p>' . esc_html($zwssgr_error_notice) . '</p></div>';
                                delete_transient('zwssgr_error_notice');
                            }
                            echo '<div id="fetch-gmb-auth-url-response" class="zwssgr-fetch-gmb-auth-url-response"></div>   
                            <a href="" class="button button-primary zwssgr-submit-btn fetch-gmb-auth-url" id="fetch-gmb-auth-url">'. esc_html__('Connect with Google', 'smart-showcase-for-google-reviews').'</a>
                        </div>
                    </div>
                </div>';
            }

        }

        // If applicable display the "Connect with Google" button on each page of sgr plugin
        public function zwssgr_connect_google_popup_callback() {

            if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
                return;
            }

            // Check if we're in the admin panel
            if (is_admin()) {

                // Get the current page or post type
                $zwssgr_current_page      = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
                $zwssgr_current_post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';

                // Define the valid pages and post types
                $zwssgr_valid_pages = array(
                    'zwssgr_dashboard',
                    'zwssgr_settings'
                );
                
                $zwssgr_valid_post_types = array(
                    'zwssgr_reviews',
                    'zwssgr_data_widget',
                    'zwssgr_request_data'
                );

                // Check if the current page is a valid page or if it's a valid post type edit page
                if (in_array($zwssgr_current_page, $zwssgr_valid_pages) || in_array($zwssgr_current_post_type, $zwssgr_valid_post_types)) {
                    
                    // Check if the JWT token is present in the database
                    $zwssgr_jwt_token = get_option('zwssgr_jwt_token');

                    if (empty($zwssgr_jwt_token)) {
                        $this->zwssgr_connect_google_callback();
                    }

                }

                // Safely check for 'tab' and 'settings' in the query string
                if (isset($_GET['tab'], $_GET['settings']) && $_GET['tab'] === 'google' && $_GET['settings'] === 'disconnect-auth') {
                    $this->zwssgr_connect_google_callback();
                }

            }
        }       

        // Handle the 'auth_code' flow during admin_init
        public function zwssgr_handle_auth_code() {

            if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
                return;
            }

            if (isset($_GET['auth_code']) && isset($_GET['user_email']) && isset($_GET['consent']) && $_GET['consent'] === 'true') {

                $zwssgr_auth_code = sanitize_text_field(wp_unslash($_GET['auth_code']));
                $zwssgr_gmb_email = sanitize_text_field(wp_unslash($_GET['user_email']));

                update_option('zwssgr_gmb_email', $zwssgr_gmb_email);

                // Fetch the JWT token
                $zwssgr_fetch_jwt_token_response = $this->zwssgr_client->zwssgr_fetch_jwt_token($zwssgr_auth_code);

                if (
                    isset($zwssgr_fetch_jwt_token_response['success']) && 
                    $zwssgr_fetch_jwt_token_response['success'] === true && 
                    isset($zwssgr_fetch_jwt_token_response['data']['zwssgr_jwt_token'])
                ) {

                    $zwssgr_jwt_token = $zwssgr_fetch_jwt_token_response['data']['zwssgr_jwt_token'];
                    
                    // Save the JWT token in the database
                    update_option('zwssgr_jwt_token', $zwssgr_jwt_token);
                    
                    // Temporarily store the authentication status in the database
                    set_transient('zwssgr_auth_status', true, 600);

                    // Create a new widget after successful auth initialization
                    $zwssgr_new_widget_id = wp_insert_post(array(
                        'post_type'   => 'zwssgr_data_widget',
                        'post_status' => 'publish',
                        'post_title'  => 'New GMB Widget ' . wp_generate_uuid4(),
                    ));

                    if ($zwssgr_new_widget_id) {

                        $this->zwssgr_zqm->zwssgr_fetch_gmb_data(true, false, 'zwssgr_gmb_accounts');

                        // Store a flag or message in a transient or session to show the notice
                        set_transient('zwssgr_success_notice', 'Congratulations! Successfully connected to Google.', 30);

                        // Redirect back to fetch gmb data page with the widget ID as a parameter
                        wp_redirect(admin_url('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data&zwssgr_widget_id=' . $zwssgr_new_widget_id));
                        exit;
                    } else {
                        wp_redirect(admin_url('edit.php?post_type=zwssgr_data_widget'));
                        exit;
                    }
                } else {

                    set_transient('zwssgr_error_notice', $zwssgr_fetch_jwt_token_response['message'], 30);

                    // Redirect back to the submenu page with error notice
                    wp_redirect(admin_url('admin.php?page=zwssgr_dashboard'));
                    exit;
                }
            }
        }
        
        public function zwssgr_fetch_gmb_data_callback() {

            if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
                return;
            }

            $zwssgr_widget_id = isset($_GET['zwssgr_widget_id']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_widget_id'])) : '';

            if (!empty($zwssgr_widget_id)) {
                // Fetch the GMB email from the options table
                $zwssgr_gmb_email = get_option('zwssgr_gmb_email');
                $zwssgr_data_processed       = get_post_meta($zwssgr_widget_id, 'zwssgr_data_sync_once', true);
                $zwssgr_gmb_data_type       = get_post_meta($zwssgr_widget_id, 'zwssgr_gmb_data_type', true);
                $zwssgr_data_processing_init = get_post_meta($zwssgr_widget_id, 'zwssgr_data_processing_init', true);
                $zwssgr_batch_progress      = get_post_meta($zwssgr_widget_id, 'zwssgr_batch_progress', true);
                $zwssgr_disabled_class      = ($zwssgr_data_processing_init === 'true') ? 'disabled' : '';
                $zwssgr_button_text         = ($zwssgr_data_processed === 'true') && ($zwssgr_gmb_data_type == 'zwssgr_gmb_reviews') ? 'Sync Reviews' : 'Fetch Reviews';

                // If the value is empty, set it to 0
                if (empty($zwssgr_batch_progress)) {
                    $zwssgr_batch_progress = 0;
                }
            }            

            echo '<div id="fetch-gmb-data" class="fetch-gmb-data zwssgr-fetch-gmb-data">
                <div class="response zwssgr-response"></div>
                <div class="progress-bar zwssgr-progress-bar '. esc_attr( $zwssgr_data_processing_init ) .'">
                    <progress class="progress zwssgr-progress" id="progress" value="'. esc_attr( $zwssgr_batch_progress ) .'" max="100"></progress>
                    <span id="progress-percentage" class="progress-percentage zwssgr-progress-percentage"> '. esc_attr( $zwssgr_batch_progress ) .'% </span>
                </div>
                <div class="fetch-gmb-inner-data zwssgr-fetch-gmb-inner-data">';
                    // Check if the widget ID is not empty
                    if (!empty($zwssgr_widget_id)) {

                        // Get the post meta for the widget ID
                        $zwssgr_account_number = get_post_meta($zwssgr_widget_id, 'zwssgr_account_number', true);

                        // Get posts where the zwssgr_gmb_email meta key matches the value from options
                        $zwssgr_request_data = get_posts(array(
                            'post_type'      => 'zwssgr_request_data',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'zwssgr_gmb_email', // The meta key to query
                                    'value'   => $zwssgr_gmb_email,  // The value to match
                                    'compare' => '=',                // Exact match
                                ),
                            ),
                            'fields'         => 'ids', // Only return post IDs
                        ));

                        if ($zwssgr_request_data) {

                            // Display the select dropdown if accounts are found
                            echo '<select id="zwssgr-account-select" name="zwssgr_account" class="zwssgr-input-text '. esc_attr( $zwssgr_disabled_class ) .'">
                                <option value="">'. esc_html__('Select an Account', 'smart-showcase-for-google-reviews').'</option>';
                            // Loop through each post
                            foreach ($zwssgr_request_data as $zwssgr_post_id) {
                                // Get the account number and name for each post
                                $account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
                                $zwssgr_account_name = get_the_title($zwssgr_post_id);

                                // Check if the account number matches the GET parameter
                                $zwssgr_selected = ($account_number === $zwssgr_account_number) ? 'selected' : '';

                                // Output each account option, with the selected attribute if matching
                                echo '<option value="' . esc_attr($account_number) . '" ' . esc_attr( $zwssgr_selected ) . '>' . esc_html($zwssgr_account_name) . '</option>';
                            }
                            echo '</select>';

                            // Retrieve the selected location number from the post meta
                            $zwssgr_location_number = get_post_meta($zwssgr_widget_id, 'zwssgr_location_number', true);

                            $zwssgr_request_data_id = get_posts(array(
                                'post_type'      => 'zwssgr_request_data',
                                'posts_per_page' => 1,
                                'post_status'    => 'publish',
                                'meta_key'       => 'zwssgr_account_number',
                                'meta_value'     => $zwssgr_account_number,
                                'fields'         => 'ids',
                            ))[0] ?? null;
                        
                            if (!$zwssgr_request_data_id) {
                                // If no post is found, return early
                                return;
                            }

                            $zwssgr_account_meta_key = 'zwssgr_account_' . $zwssgr_account_number;

                            $zwssgr_account_locations = get_option($zwssgr_account_meta_key);

                            $zwssgr_account_locations_array = !empty($zwssgr_account_locations) ? json_decode($zwssgr_account_locations, true) : [];
                        
                            // Retrieve existing locations or initialize an empty array
                            $zwssgr_account_locations = get_post_meta($zwssgr_request_data_id, 'zwssgr_account_locations', true);
            
                            // Check if the custom field has a value
                            if ( $zwssgr_account_locations ) {
                                echo '<select id="zwssgr-location-select" name="zwssgr_location" class="zwssgr-input-text ' . esc_attr($zwssgr_disabled_class) . '">
                                    <option value="">'. esc_html__('Select a Location', 'smart-showcase-for-google-reviews').'</option>';
                                    foreach ( $zwssgr_account_locations as $zwssgr_account_location ) {

                                        if (isset($zwssgr_account_location['metadata']['newReviewUri'])) {
                                            $zwssgr_new_review_uri = $zwssgr_account_location['metadata']['newReviewUri'];
                                        } else {
                                            $zwssgr_new_review_uri = '';
                                        }
            
                                        if (isset($zwssgr_account_location['metadata']['placeId'])) {
                                            $zwssgr_place_id = $zwssgr_account_location['metadata']['placeId'];
                                        } else {
                                            $zwssgr_place_id = '';
                                        }
                                        
                                        $zwssgr_account_location_id = $zwssgr_account_location['name'] ? ltrim( strrchr( $zwssgr_account_location['name'], '/' ), '/' ) : '';

                                        $zwssgr_selected_location_number = isset($_GET['zwssgr_location_number']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_location_number'])) : '';

                                        if (!empty($zwssgr_selected_location_number)) {
                                            $zwssgr_selected = ($zwssgr_account_location_id === $zwssgr_selected_location_number) ? 'selected' : '';
                                        } else {
                                            $zwssgr_selected = ($zwssgr_account_location_id === $zwssgr_location_number) ? 'selected' : '';
                                        }

                                        $zwssgr_data_fetched_attr = in_array($zwssgr_account_location_id, $zwssgr_account_locations_array) ? 'data-fetched=true' : 'data-fetched=';

                                        echo '<option value="' . esc_attr($zwssgr_account_location_id) . '" ' . esc_attr($zwssgr_selected) . ' data-new-review-url="' . esc_url($zwssgr_new_review_uri) . '" data-all-reviews-url="http://search.google.com/local/reviews?placeid=' . esc_attr($zwssgr_place_id) . '" ' . esc_attr($zwssgr_data_fetched_attr) . '>' . esc_html($zwssgr_account_location['title']) . '</option>';

                                    }
                                echo '</select>';

                                if (in_array($zwssgr_selected_location_number, $zwssgr_account_locations_array) || (empty($zwssgr_selected_location_number) && in_array($zwssgr_location_number, $zwssgr_account_locations_array))) {
                                    echo '<a href="#" class="button button-secondary zwssgr-submit-btn zwssgr-create-widget' . esc_attr($zwssgr_disabled_class) . '" id="zwssgr-create-widget">
                                        Create Widget
                                    </a>
                                    <a href="#" class="button button-secondary zwssgr-submit-btn ' . esc_attr($zwssgr_disabled_class) . '" id="fetch-gmd-reviews" data-fetch-type="zwssgr_gmb_reviews">
                                        Sync Reviews
                                    </a>';
                                } else {
                                    echo '<a href="#" class="button button-secondary zwssgr-submit-btn ' . esc_attr($zwssgr_disabled_class) . '" id="fetch-gmd-reviews" data-fetch-type="zwssgr_gmb_reviews">
                                        Fetch Reviews
                                    </a>';
                                }
                            }
                        } else {
                            echo ' <a href="#" class="button button-secondary zwssgr-submit-btn ' . esc_attr($zwssgr_disabled_class) . '" id="fetch-gmd-accounts" data-fetch-type="zwssgr_gmb_accounts">
                                '. esc_html__('Fetch Accounts', 'smart-showcase-for-google-reviews').'
                            </a>';
                        }
                    } else {
                        echo esc_html__('There was an error while trying to edit the widget', 'smart-showcase-for-google-reviews');
                    }
                // Reset post data
                wp_reset_postdata();
                echo '</div>
            </div>';
        }

        public function zwssgr_connect_google_success_callback() {

            $zwssgr_success_notice = get_transient('zwssgr_success_notice');
            if ($zwssgr_success_notice) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($zwssgr_success_notice) . '</p></div>';
                delete_transient('zwssgr_success_notice');
            }
            
        }
    }
    Zwssgr_Google_My_Business_Connector::get_instance();
}