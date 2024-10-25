<?php

if ( ! class_exists( 'Zwsgr_Google_My_Business_Connector' ) ) {
    
    require_once ZWSGR_DIR . '/inc/lib/google-api-php-client/vendor/autoload.php';

    class Zwsgr_Google_My_Business_Connector {
        
        private $client;

        public function __construct() {

            // Initialize the Google Client
            $this->client = new Google_Client();
            $this->client->setApplicationName('Smart Google Reviews');
            $this->client->setClientId('637773123304-na845vs42isjuaeuham894p2d7v7jq4a.apps.googleusercontent.com');
            $this->client->setClientSecret('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');
            $this->client->setRedirectUri(admin_url('admin.php?page=zwsgr_handle_oauth_flow'));
            $this->client->addScope('https://www.googleapis.com/auth/business.manage');
            $this->client->setPrompt('consent');

            // Hook to add admin menu items
            add_action('admin_menu', [$this, 'zwsgr_gmbc_add_menu_items'], 20);

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
        

        // Display the "Connect with Google" button
        public function zwsgr_connect_google_callback() {
            $this->client->setAccessType('offline');
            $auth_url = $this->client->createAuthUrl();
            echo '<div class="gmbc-outer-wrapper">
                <div class="gmbc-inner-wrapper">
                    <span style="margin-right: 10px;">Connect With Google:</span>
                    <a href="' . esc_url($auth_url) . '" class="button button-primary">Connect with Google</a>
                </div>
            </div>';
        }

        // Handle the OAuth callback after user authorizes the app
        public function zwsgr_handle_oauth_flow_callback() {

            if (isset($_GET['code'])) {

                $code = sanitize_text_field($_GET['code']);
                
                $token = $this->client->fetchAccessTokenWithAuthCode($code);

                if (isset($token['access_token'])) {

                    update_option('zwsgr_access_token', sanitize_text_field($token['access_token']));
                
                    if (isset($token['refresh_token'])) {
                        
                        update_option('zwsgr_refresh_token', sanitize_text_field($token['refresh_token']));

                    } else {

                        $this->show_error_notice('There was an error please try again.');                        
                        error_log('Missing refresh token during OAuth flow callback.');

                    }

                    wp_redirect(admin_url('admin.php?page=zwsgr_widget_configurator'));
                    exit;

                } else {

                    // Handle error
                    $this->show_error_notice('Authorization failed. Please try again.');
                    error_log('Authorization failed: ' . print_r($token, true));

                }
                
            } else {

                $this->show_error_notice('We could not complete the connection process. Please try again.');
                error_log('No authorization code returned during OAuth flow callback.');

            }

        }

        public function zwsgr_widget_configurator_callback() {

            echo '<div id="fetch-gmb-data">';
                $this->show_success_notice('Successfully connected to Google. You are now authorized to access and manage your reviews.');
                echo '<a href="#" class="button button-primary" id="fetch-gmd-accounts">
                    Fetch Accounts
                </a>
            </div>';
                
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
}
