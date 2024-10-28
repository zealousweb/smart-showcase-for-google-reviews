<?php

if ( ! class_exists( 'Zwsgr_Batch_Processing' ) ) {

    class Zwsgr_Batch_Processing {
        
        private $client;

        function __construct() {

            add_action('wp_ajax_zwsgr_fetch_gmb_accounts', array($this, 'zwsgr_fetch_gmb_accounts'));
            add_action('wp_ajax_nopriv_zwsgr_fetch_gmb_accounts', array($this, 'zwsgr_fetch_gmb_accounts'));

        }

        function zwsgr_fetch_gmb_accounts () {

            // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gmb_fetch_accounts')) {
            //     wp_send_json_error('Invalid nonce');
            //     wp_die();
            // }

            // Initialize an array to hold all accounts
            $zwsgr_gmb_all_accounts  = [];
            $zwsgr_current_index     = 1;
            $zwsgr_continue_fetching = true;

            // Loop to fetch accounts until no more pages
            while ($zwsgr_continue_fetching) {  

                $file_path = plugin_dir_path(__FILE__) . "accounts/accounts-response-$zwsgr_current_index.json";

                $zwsgr_gmb_current_accounts = file_get_contents($file_path);

                $zwsgr_gmb_current_accounts = json_decode($zwsgr_gmb_current_accounts, true); // Decode JSON as associative array

                // Append current data to main all data without using array_merge
                if (isset($zwsgr_gmb_current_accounts['accounts'])) {
                    
                    foreach ($zwsgr_gmb_current_accounts['accounts'] as $account) {
                        // Create a slug from the account name

                        $slug = sanitize_title($account['accountName']) . '-' . $account['accountNumber'];

                        // Check if a post with this slug already exists
                        $existing_post = get_page_by_path($slug, OBJECT, 'zwsgr_account');

                        if (!$existing_post) {

                            $post_data = array(
                                'post_title'   => $account['accountName'],
                                'post_type'    => 'zwsgr_account',
                                'post_status'  => 'publish',
                                'post_name'    => $slug,
                                'meta_input'   => array(
                                    'account_id' => $account['name'],
                                    'account_number' => $account['accountNumber']
                                ),
                            );
                            
                            $post_id = wp_insert_post($post_data);

                            // Check if post was successfully created
                            if (is_wp_error($post_id)) {
                                // Handle the error if needed
                                $error_message = $post_id->get_error_message();
                                $responses[] = array(
                                    'status' => 'error',
                                    'message' => 'Error creating post for account "' . $account['accountName'] . '": ' . $error_message,
                                );

                            } else {
                                $responses[] = array(
                                    'status' => 'success',
                                    'post_id' => $post_id,
                                    'slug' => $slug,
                                    'message' => 'Post created successfully: ' . $post_id . ' with slug: ' . $slug,
                                );
                            }
                        } else {
                            $responses[] = array(
                                'status' => 'exists',
                                'slug' => $slug,
                                'message' => 'Post with slug "' . $slug . '" already exists.',
                            );
                        }
                    }
                }

                // Check for the next page token
                if (isset($zwsgr_gmb_current_accounts['nextPageToken'])) {
                    $zwsgr_current_index++; // Move to the next page
                } else {
                    $zwsgr_continue_fetching = false; // Stop fetching if no more pages
                }
            }

            wp_die();
        }

    }

    new Zwsgr_Batch_Processing();
        
}