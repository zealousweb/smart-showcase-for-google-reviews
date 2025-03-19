<?php
/**
 * ZWSSGR_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'ZWSSGR_Admin_Action' ) ){

	/**
	 *  The ZWSSGR_Admin_Action Class
	 */
	class ZWSSGR_Admin_Action {

		function __construct()  {

			add_action('admin_menu', 	 array($this, 'zwssgr_admin_menu_registration'));
			add_action('add_meta_boxes', array($this, 'zwssgr_action__add_oauth_meta_box'));
			add_action('init', 			 array($this, 'zwssgr_register_oauth_connections_cpt'));
			add_action('init', 			 array($this, 'zwssgr_register_oauth_accounts_cpt'));			

		}

		/**
		 * Action: admin_menu
		 * Used for Add Menu Page
		 * @method action_admin_menu
		 */
		function zwssgr_admin_menu_registration()
		{

			add_menu_page(
				__('Smart Showcase for Google Reviews', 'smart-showcase-for-google-reviews'),
				__('Smart Showcase for Google Reviews', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'zwssgr_oauth_dashboard',
				array($this, 'zwssgr_oauth_dashboard_callback'),
				'data:image/svg+xml;base64,' . base64_encode('
				<svg xmlns="http://www.w3.org/2000/svg" width="986.612" height="699.837" viewBox="0 0 986.612 699.837"><path d="M779.4,699.837h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.9,33.9,0,0,1,31.307,20.757,33.371,33.371,0,0,1,2.592,13.143v9.9Zm-102.5,0h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.939,33.939,0,0,1,33.9,33.9v9.9Zm-102.6,0h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.938,33.938,0,0,1,33.9,33.9v9.9ZM448.583,660.731c-22.626-9.023-113.855-34.606-124.182-37.493a217.782,217.782,0,0,1-35-11.7h.5a267.391,267.391,0,0,0,28.2,1.5c90.276,0,186.394-44.829,321.4-149.9,35.6,5,77.895,8.731,122.3,10.8a431.88,431.88,0,0,0-100.975,20.8,365.261,365.261,0,0,0-52.526,22.3,253.325,253.325,0,0,0-25.187,14.675A280.3,280.3,0,0,0,545.1,561.838c-.607.607-1.2,1.169-1.779,1.714l-.008.007,0,0c-.864.816-1.68,1.586-2.51,2.475a232.453,232.453,0,0,1-46.15,33.812,238.382,238.382,0,0,1-51.55,21.288c.495,0,29.507,21.582,29.8,21.8-4.66-1.854-8.267-3.457-11.449-4.871-9.245-4.108-15.925-7.076-38.35-11.829-7.608,1.3-22.312,2.7-30,3.2h.2c11.151,8.118,53.487,30.349,55.284,31.292Zm447.971-23.584h0a129.646,129.646,0,0,1-24.65-2.309c-3.758-.813-7.632-1.835-11.6-2.9l-.3-.1C819.738,622.592,785.045,602,751.494,582.09l-.594-.352-.415-.247c-29.474-17.525-57.313-34.077-84.585-39.253a152.638,152.638,0,0,0-58.7.3,312.637,312.637,0,0,1,47-26.862A357.143,357.143,0,0,1,705.13,496.7a382.972,382.972,0,0,1,52.684-11.265,374.822,374.822,0,0,1,52.247-3.718c40.5,0,77.889,6.855,108.14,19.825a155.355,155.355,0,0,1,32.919,18.7A100.343,100.343,0,0,1,971.7,540.925a79.176,79.176,0,0,1,10.906,20.814,75.647,75.647,0,0,1,3.893,19.1c.809,10.009-2.757,19.362-10.6,27.8-7.957,8.611-19.712,15.839-33.993,20.9A137.22,137.22,0,0,1,896.554,637.147ZM314.6,603.938c-21.8,0-44-5.148-66-15.3a99.589,99.589,0,0,1-12.125-6.775,110.083,110.083,0,0,1-37.143-40.871,113.1,113.1,0,0,1-11.92-37.292,117.773,117.773,0,0,1,1.223-39.373A119.711,119.711,0,0,1,203.2,426.538a182,182,0,0,1,12.966-18.913A212.262,212.262,0,0,1,233.2,388.413a256.428,256.428,0,0,1,21.609-19.25,318.762,318.762,0,0,1,26.69-19.025c9.564-6.119,19.878-12.06,30.655-17.656s22.3-11,34.268-16.064,24.7-9.93,37.847-14.463,27.072-8.856,41.392-12.854,29.425-7.778,44.9-11.237,31.755-6.693,48.38-9.611C552.222,262.41,588.24,257.594,626,253.938c-40.351,94.253-83.148,171.4-127.2,229.3C437.708,563.329,375.734,603.938,314.6,603.938ZM449.311,567.53v0c9.052-8.306,18.188-17.493,27.153-27.3s18.027-20.538,26.938-31.888c45.206-57.708,89.18-134.654,130.7-228.7l12.3-27.7c19.11-1.057,38.085-1.83,56.4-2.3,16.342-.531,32.323-.8,47.5-.8-19.457,27.137-39.477,53.774-59.5,79.173-24.461,31.021-49.007,60.28-72.958,86.964C557.857,481.8,501.156,533.128,449.311,567.53Zm127.6-87.1v0C654.5,404.566,723.868,314.138,768.4,251.637c4.411.053,9.526.1,15.448.165l.288,0h.012c16.165.164,36.284.368,55.551.732a3.155,3.155,0,0,1,2.956,2.051,3.308,3.308,0,0,1-.756,3.649c-25.5,25.1-52.047,50.069-78.913,74.2-37.263,33.469-74.335,64.591-110.187,92.5C626.72,445.244,601.187,463.915,576.91,480.431Zm-409.011-7.2c-.829-2.57-1.715-5.578-2.74-9.061q-.321-1.089-.658-2.232a237.732,237.732,0,0,1-5.465-24.733l-.135-.767c-.1-.693-.2-1.2-.3-1.681l0-.019c-.327-1.835-1.131-8.032-2.147-15.878l0-.019c-.529-4.082-1.128-8.709-1.75-13.4-1.1-8.81-2.239-17.207-3-22.7-1.788-13.6-5.186-30.088-10.1-49a179.766,179.766,0,0,0-6.374-19.362c-2.722-7.063-5.719-13.937-8.026-19.137-5.586-12.6-14.122-27.78-20.3-38.3a182.617,182.617,0,0,0-15.2-21.2,128.107,128.107,0,0,0-18.988-18.161,89.852,89.852,0,0,0-15.837-9.827c-9-4.288-14.817-4.808-14.875-4.812a104.262,104.262,0,0,0-24.622-2.869A93.988,93.988,0,0,0,0,201.538a134.727,134.727,0,0,1,7.537-27.925A185.323,185.323,0,0,1,17.1,153.638a12.093,12.093,0,0,1,.614-1.19c.138-.244.269-.475.386-.711a96.518,96.518,0,0,1,12.25-17.35,113.86,113.86,0,0,1,11.95-11.95c.091-.091.2-.184.324-.282a6.174,6.174,0,0,0,.476-.419l.005,0c2.011-1.706,5.378-4.561,10.258-7.976A133.564,133.564,0,0,1,72.388,102.69a141.769,141.769,0,0,1,27.8-9.822,156.459,156.459,0,0,1,36.585-4.238c.513,0,1.012,0,1.527.007a180.953,180.953,0,0,1,37.992,4.13,133.357,133.357,0,0,1,28.345,9.708,116.347,116.347,0,0,1,20.3,12.389A137.871,137.871,0,0,1,238.8,127.038c6.352,6.266,13.926,15.148,22.513,26.4,7.035,9.218,12.685,17.337,14.788,20.4,4.55,6.528,13.213,20.064,13.3,20.2s8.206,13.168,15.8,25.2c-11.243,10.139-21.884,20.363-31.625,30.388-9.909,10.2-19.187,20.5-27.575,30.613-53.1,63.5-80,128.439-77.8,187.8-.035.633-.083,1.256-.129,1.858-.088,1.146-.171,2.229-.171,3.335ZM90,141a11,11,0,1,0,11,11A11.012,11.012,0,0,0,90,141ZM204.5,407.138h0l.005-.007c11.177-24,27.542-47.45,48.643-69.709a381.465,381.465,0,0,1,38.614-35.268,500.788,500.788,0,0,1,49.464-34.627,664.544,664.544,0,0,1,61.3-33.3c22.951-11.011,47.895-21.538,74.136-31.289,27.444-10.2,57.038-19.817,87.96-28.591,32.264-9.155,66.842-17.636,102.775-25.208-4.183,12.278-8.076,23.269-11.9,33.6a64.812,64.812,0,0,0-3.1,8.092l0,.008c-2.257,5.958-4.48,12.043-6.629,17.927-3.7,10.128-7.194,19.7-10.471,27.573C369.949,257.1,273.734,317.084,204.5,407.138Zm25.109-72.41h0c6.312-9.815,13.415-19.8,21.11-29.664,7.953-10.2,16.752-20.55,26.154-30.768,9.7-10.546,20.312-21.235,31.531-31.771,11.566-10.863,24.1-21.859,37.247-32.685,13.544-11.151,28.113-22.425,43.3-33.51,15.63-11.406,32.352-22.928,49.7-34.246,17.832-11.633,36.819-23.373,56.435-34.893C515.237,95.356,536.6,83.429,558.6,71.737c22.082-11.72,45.41-23.588,69.338-35.275C652.371,24.529,678.2,12.408,704.7.437A6.328,6.328,0,0,1,707,0a4.631,4.631,0,0,1,3.766,1.66,3.344,3.344,0,0,1,.434,3.077c-3.371,12.972-16,60.18-36.9,123l-10.7,2.2c-175.739,37.108-308.064,92.555-393.3,164.8A377.76,377.76,0,0,0,229.61,334.727ZM653.2,234.838h0c2.415-6.263,5.692-14.428,8.861-22.324l.01-.024c3.94-9.817,7.661-19.089,9.429-23.952l.7-2a54.811,54.811,0,0,1,2.4-6.6l.4-1c4.478-12.807,8.347-24.146,11.5-33.7,8.08-1.591,80.519-15.78,135.3-24.2a4.269,4.269,0,0,1,.717-.062,4.359,4.359,0,0,1,3.787,2.4,6.284,6.284,0,0,1,.8,3.065,6.354,6.354,0,0,1-.9,3.3c-3.061,5.11-6.835,11.527-11.2,18.958C801.6,171.468,783.26,202.657,765.1,230.837c-1.911-.008-3.878-.012-5.848-.012-21.126,0-42.292.457-62.909,1.358-19.46.85-34.2,1.913-43.142,2.655Z" fill="#a7aaad"/></svg>'),
			);

			add_submenu_page(
				'zwssgr_oauth_dashboard',
				__('Connections', 'smart-showcase-for-google-reviews'),
				__('Connections', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'edit.php?post_type=zwssgr_oauth_data',
				null
			);

			add_submenu_page(
				'zwssgr_oauth_dashboard',
				__('Accounts', 'smart-showcase-for-google-reviews'),
				__('Accounts', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'edit.php?post_type=zwssgr_oauth_accdata',
				null
			);

		}

		function zwssgr_dashboard_callback () {
		}

		function zwssgr_action__add_oauth_meta_box() {

			add_meta_box(
				'zwssgr_oauth_meta_box',
				__('OAuth Details', 'zw-smart-google-reviews'),
				array($this, 'zwssgr_display_oauth_meta_box__callback'),
				'zwssgr_oauth_data',
				'normal',
				'high'
			);

			add_meta_box(
				'zwssgr_oauth_meta_box',
				__('OAuth Details', 'zw-smart-google-reviews'),
				array($this, 'zwssgr_display_oauth_accounts_meta_box__callback'),
				'zwssgr_oauth_accdata',
				'normal',
				'high'
			);

		}

		/**
		 * Displays OAuth connection details in the meta box.
		 *
		 * This function retrieves and displays metadata related to 
		 * the OAuth connection for a given post. The metadata includes:
		 * - Connected GMB Accounts with an "Edit" button.
		 *
		 * @param WP_Post $zwssgr_oauth_post The current post object.
		 */
		function zwssgr_display_oauth_meta_box__callback($zwssgr_oauth_post) {

			$zwssgr_oauth_gmb_accounts = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_oauth_gmb_accounts', true);
			$zwssgr_oauth_gmb_accounts = json_decode($zwssgr_oauth_gmb_accounts, true);

			echo '<table class="form-table">
				<tr>
					<th> <strong>' . __('Connected GMB Accounts', 'zwssgr-smart-google-reviews') . '</strong> </th>
				</tr>';
				if (!empty($zwssgr_oauth_gmb_accounts) && is_array($zwssgr_oauth_gmb_accounts)) {
					foreach ($zwssgr_oauth_gmb_accounts as $zwssgr_account_id) {
						$zwssgr_account_post = get_page_by_path($zwssgr_account_id, OBJECT, 'zwssgr_gmb_account');
						$zwssgr_edit_link  	 = get_edit_post_link($zwssgr_account_id, 'raw');
						$zwssgr_user_email 	 = get_post_meta($zwssgr_account_id, 'zwssgr_user_email', true);
						echo '<tr>
							<td>' . $zwssgr_user_email . '</td>
							<td><a href="' . esc_url($zwssgr_edit_link) . '" class="button button-primary">' . __('Edit', 'zwssgr-smart-google-reviews') . '</a></td>
						</tr>';
					}
				} else {
					echo '<tr><td>' . __('No connected GMB accounts.', 'zwssgr-smart-google-reviews') . '</td></tr>';
				}
			echo '</table>';
		}

		/**
		 * Displays OAuth connection details in the meta box.
		 *
		 * This function retrieves and displays metadata related to 
		 * the OAuth connection for a given post. The metadata includes:
		 * - Personal information: user email, name, and site URL.
		 * - Access information: access token, refresh token, JWT secret, JWT token, and OAuth status.
		 *
		 * All fields are displayed as read-only inputs for informational purposes.
		 *
		 * @param WP_Post $zwssgr_oauth_post The current post object.
		 */
		function zwssgr_display_oauth_accounts_meta_box__callback($zwssgr_oauth_post) {

			// Retrieve stored meta values for OAuth connection details
			$zwssgr_user_email    	  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_user_email', true);
			$zwssgr_user_name 	      = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_user_name', true);
			$zwssgr_user_site_url 	  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_user_site_url', true);
			$zwssgr_gmb_access_token  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_gmb_access_token', true);
			$zwssgr_gmb_refresh_token = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_gmb_refresh_token', true);
			$zwssgr_jwt_secret 		  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_jwt_secret', true);
			$zwssgr_jwt_token 		  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_jwt_token', true);
			$zwssgr_oauth_status 	  = get_post_meta($zwssgr_oauth_post->ID, 'zwssgr_oauth_status', true);

			// Output the HTML for the meta box content, displaying the OAuth connection details
			echo '<table class="form-table">
				<tr>
					<th colspan="2"> <strong>' . __('Personal Information', 'zw-smart-google-reviews') . '</strong> </th>
				</tr>
				<tr>
					<th><label for="zwssgr_user_email">' . __('Email', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_user_email) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_user_name">' . __('User Name', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_user_name) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_user_site_url">' . __('Site URL', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_user_site_url) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th colspan="2">
						<strong>' . __('Access Information', 'zw-smart-google-reviews') . '</strong>
					</th>
				</tr>
				<tr>
					<th><label for="zwssgr_gmb_access_token">' . __('Access Token', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_gmb_access_token) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_gmb_refresh_token">' . __('Refresh Token', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_gmb_refresh_token) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_jwt_secret">' . __('JWT Secret', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_jwt_secret) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_jwt_token">' . __('JWT Token', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_jwt_token) . '" readonly class="regular-text" style="width:100%;" /></td>
				</tr>
				<tr>
					<th><label for="zwssgr_oauth_status">' . __('OAuth Status', 'zw-smart-google-reviews') . '</label></th>
					<td><input type="text" value="' . esc_attr($zwssgr_oauth_status) . '" readonly class="regular-text" style="width:15%;margin-right: 15px;" /></td>
				</tr>
			</table>';

		}

		/**
		 * Registers the custom post type for OAuth Connections.
		 *
		 * Creates a secure, non-public custom post type to store OAuth connection data
		 *
		 * Key Features:
		 * - Non-public and excluded from search.
		 * - Accessible via the WordPress admin menu.
		 * - Supports only 'read' capability for restricted access.
		 *
		 * @return void
		 */
		function zwssgr_register_oauth_connections_cpt() {

			// Define labels for the custom post type in different contexts (e.g., menu, admin bar, etc.)
			$labels = array(
				'name'                  => _x('OAuth Connections', 'zwssgr-oauth-connections', 'zw-smart-google-reviews'),
				'singular_name'         => _x('OAuth Connection', 'zwssgr-oauth-connections', 'zw-smart-google-reviews'),
				'menu_name'             => _x('OAuth Connections', 'admin menu', 'zw-smart-google-reviews'),
				'name_admin_bar'        => _x('OAuth Connection', 'add new on admin bar', 'zw-smart-google-reviews'),
				'add_new'               => _x('Add New', 'oauth connection', 'zw-smart-google-reviews'),
				'add_new_item'          => __('Add New OAuth Connection', 'zw-smart-google-reviews'),
				'new_item'              => __('New OAuth Connection', 'zw-smart-google-reviews'),
				'edit_item'             => __('Edit OAuth Connection', 'zw-smart-google-reviews'),
				'view_item'             => __('View OAuth Connection', 'zw-smart-google-reviews'),
				'all_items'             => __('All OAuth Connections', 'zw-smart-google-reviews'),
				'search_items'          => __('Search OAuth Connections', 'zw-smart-google-reviews'),
				'not_found'             => __('No OAuth Connections found.', 'zw-smart-google-reviews'),
				'not_found_in_trash'    => __('No OAuth Connections found in Trash.', 'zw-smart-google-reviews')
			);

			// Define the arguments for registering the custom post type		
			$args = array(
				'label'                 => __('OAuth Connections', 'zw-smart-google-reviews'),
				'labels'                => $labels,
				'description'           => 'Store OAuth connection data securely.',
				'public'                => false,
				'publicly_queryable'    => false,
				'show_ui'               => true,
				'delete_with_user'      => false,
				'show_in_rest'          => false,
				'show_in_menu'          => false, 
				'menu_position' 		=> 79,
				'query_var'             => false,
				'rewrite'               => false,
				'capability_type'       => 'post',
				'has_archive'           => false,
				'show_in_nav_menus'     => false,
				'exclude_from_search'   => true,
				'capabilities'          => array(
					'read'                => true
				),
				'map_meta_cap'          => true,
				'hierarchical'          => false,
				'supports'              => array('title'),
			);
		
			// Register the custom post type with WordPress
			register_post_type('zwssgr_oauth_data', $args);
			
		}

		/**
		 * Registers the custom post type for OAuth Accounts.
		 *
		 * Creates a secure, non-public custom post type to store OAuth data, such as 
		 * user details, access tokens, and refresh tokens, for administrative use only.
		 *
		 * Key Features:
		 * - Non-public and excluded from search.
		 * - Accessible via the WordPress admin menu.
		 * - Supports only 'read' capability for restricted access.
		 *
		 * @return void
		 */
		function zwssgr_register_oauth_accounts_cpt() {

			// Define labels for the custom post type in different contexts (e.g., menu, admin bar, etc.)
			$zwssgr_labels = array(
				'name'                  => _x('OAuth Accounts', 'zwssgr-oauth-accounts', 'zwssgr-smart-google-reviews'),
				'singular_name'         => _x('OAuth Account', 'zwssgr-oauth-accounts', 'zwssgr-smart-google-reviews'),
				'menu_name'             => _x('OAuth Accounts', 'admin menu', 'zwssgr-smart-google-reviews'),
				'name_admin_bar'        => _x('OAuth Account', 'add new on admin bar', 'zwssgr-smart-google-reviews'),
				'add_new'               => _x('Add New', 'oAuth accounts', 'zwssgr-smart-google-reviews'),
				'add_new_item'          => __('Add New OAuth Account', 'zwssgr-smart-google-reviews'),
				'new_item'              => __('New OAuth Account', 'zwssgr-smart-google-reviews'),
				'edit_item'             => __('Edit OAuth Account', 'zwssgr-smart-google-reviews'),
				'view_item'             => __('View OAuth Accounts', 'zwssgr-smart-google-reviews'),
				'all_items'             => __('All OAuth Accounts', 'zwssgr-smart-google-reviews'),
				'search_items'          => __('Search OAuth Accounts', 'zwssgr-smart-google-reviews'),
				'not_found'             => __('No OAuth Accounts found.', 'zwssgr-smart-google-reviews'),
				'not_found_in_trash'    => __('No OAuth Accounts found in Trash.', 'zwssgr-smart-google-reviews')
			);

			$zwssgr_args = array(
				'label'                 => __('OAuth Accounts', 'zwssgr-smart-google-reviews'),
				'labels'                => $zwssgr_labels,
				'description'           => 'Store OAuth accounts data securely.',
				'public'                => false,
				'publicly_queryable'    => false,
				'show_ui'               => true,
				'delete_with_user'      => false,
				'show_in_rest'          => false,
				'show_in_menu'          => false, 
				'menu_position' 		=> 79,
				'query_var'             => false,
				'rewrite'               => false,
				'capability_type'       => 'post',
				'has_archive'           => false,
				'show_in_nav_menus'     => false,
				'exclude_from_search'   => true,
				'capabilities'          => array(
					'read'                => true
				),
				'map_meta_cap'          => true,
				'hierarchical'          => false,
				'supports'              => array('title'),
			);
		
			register_post_type('zwssgr_oauth_accdata', $zwssgr_args);
			
		}

	}
}
