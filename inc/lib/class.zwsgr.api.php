<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'ZWSGR_GMB_API' ) ) {
    class ZWSGR_GMB_API {

        private $access_token;
        private $base_url;

        public function __construct( $access_token ) {
            $this->access_token = $access_token;
            $this->base_url = "https://mybusiness.googleapis.com/v1/";
        }

        private function make_request( $endpoint, $params = [], $version = 'v4' ) {

            $this->$base_url = "https://mybusiness.googleapis.com/{$version}/";
            $url = $this->$base_url . $endpoint;

            $args = [
                'method'  => 'GET',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'timeout' => 15, // Increase timeout to 15 seconds
            ];

            if ( ! empty( $params ) ) {
                $url = add_query_arg( $params, $url );
            }

            $response = wp_remote_request( $url, $args );
            $status_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $response ) ) {
                throw new Exception( 'Request Error: ' . $response->get_error_message() );
            }

            if ( $status_code !== 200 ) {
                throw new Exception( "API Request failed with response code: $status_code, Response: $body" );
            }

            return json_decode( $body, true );

        }

        public function get_accounts( $page_token = null ) {
            $params = $page_token ? [ 'pageToken' => $page_token ] : [];
            return $this->make_request( 'accounts', $params, 'v1' );
        }

        public function get_locations( $account_id, $page_token = null ) {
            $endpoint = "accounts/{$account_id}/locations?readMask=name,storeCode";
            $params = $page_token ? [ 'pageToken' => $page_token ] : [];
            return $this->make_request( $endpoint, $params, 'v1' );
        }

        public function get_reviews( $account_id, $location_id, $page_token = null ) {
            // Define the endpoint for the reviews API
            $endpoint = "accounts/{$account_id}/locations/{$location_id}/reviews";
        
            // Prepare query parameters including pageToken if available
            $params = [];
            if ($page_token) {
                $params['pageToken'] = $page_token;
            }
        
            // Make the API request with the endpoint and parameters
            return $this->make_request( $endpoint, $params, 'v4' );
        }
        
    }
}