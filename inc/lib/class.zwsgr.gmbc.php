<?php



if ( ! class_exists( 'Zwsgr_Google_My_Business_Connector' ) ) {

    class Zwsgr_Google_My_Business_Connector {

        private $client;

        public function __construct() {

            // Hook to add admin menu items
            add_action('admin_menu', [$this, 'zwsgr_gmbc_add_menu_items'], 20);

            $this->client = new ZWSGR_GMB_API('');

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
                'Google OAuth Callback',
                'Google OAuth Callback',
                'manage_options',
                'zwsgr_handle_oauth_flow',
                [$this, 'zwsgr_handle_oauth_flow_callback']
            );
        
            add_submenu_page(
                null,
                'Widget Configurator',             
                'Widget Configurator',
                'manage_options',
                'zwsgr_widget_configurator',
                [$this, 'zwsgr_widget_configurator_callback']
            );
            
        }
        

        // Display the "Connect with Google" button and process connection request
        public function zwsgr_connect_google_callback() {

            // Check if the 'auth_code' is present in the URL
            if (isset($_GET['auth_code']) && isset($_GET['consent']) && $_GET['consent'] === 'true') {

                $zwsgr_auth_code = $_GET['auth_code'];

                $zwsgr_fetch_jwt_token_response = $this->client->zwsgr_fetch_jwt_token($zwsgr_auth_code);

                // Check if the response was successful and contains the JWT token
                if (isset($zwsgr_fetch_jwt_token_response['success']) && $zwsgr_fetch_jwt_token_response['success'] == true && isset($zwsgr_fetch_jwt_token_response['data']['data']['zwsgr_jwt_token'])) {
                    
                    // Extract the JWT token
                    $zwsgr_jwt_token = $zwsgr_fetch_jwt_token_response['data']['data']['zwsgr_jwt_token'];

                    // Save the JWT token in the wp_options table
                    update_option('zwsgr_jwt_token', $zwsgr_jwt_token);

                    echo '<div class="gmbc-outer-wrapper">
                        <div class="gmbc-inner-wrapper">
                            <span style="margin-right: 10px;">Congratulations! Connected to google</span>
                        </div>
                    </div>';


                } else {
                    echo 'there was an error while trying to fetch jwt token';
                }

            } else {
                echo '<div class="gmbc-outer-wrapper">
                    <div class="gmbc-inner-wrapper">
                        <span style="margin-right: 10px;">Connect With Google:</span>
                        <a href="#" class="button button-primary fetch-gmb-auth-url">Connect with Google</a>
                    </div>
                </div>';
            }
        }
        
        public function zwsgr_widget_configurator_callback() {
           
            echo '<div id="fetch-gmb-data">';
                $this->show_success_notice('Successfully connected to Google. You are now authorized to access and manage your reviews.');

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
        
        // Show error notices in the admin dashboard
        public function show_error_notice($message) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }

        // Show success notices in the admin dashboard
        public function show_success_notice($message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }

    }

    new Zwsgr_Google_My_Business_Connector();
        
}