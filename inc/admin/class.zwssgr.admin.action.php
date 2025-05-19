<?php
/**
 * ZWSSGR_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR_Admin_Action' ) ){

	/**
	 *  The ZWSSGR_Admin_Action Class
	 */
	class ZWSSGR_Admin_Action {

		private $client, $zwssgr_gmbc,$zwssgr_dashboard;
		
		public $zwssgr_admin_smtp_enabled,$zwssgr_smtp_opt,$zwssgr_general_opt;

		function __construct()  
		{

			add_action( 'admin_enqueue_scripts',array( $this, 'zwssgr_admin_enqueue' ));
			add_action('admin_menu', array($this, 'zwssgr_admin_menu_registration'));
			add_action('admin_init', array($this, 'zwssgr_register_settings'));
			add_action('init', array($this, 'zwssgr_register_widget_cpt'));
			add_action('load-post-new.php', array($this, 'action__custom_widget_url_on_add_new'));
			add_action('init', array($this, 'zwssgr_register_review_cpt'));

			add_action('add_meta_boxes', array($this, 'zwssgr_add_review_meta_box'));
			add_action('init', array($this, 'zwssgr_register_request_data_cpt'));

			add_filter('manage_' . ZWSSGR_POST_REVIEW_TYPE . '_posts_columns', array($this, 'zwssgr_filter_manage_data_posts_columns'), 10, 3);
			add_action('manage_' . ZWSSGR_POST_REVIEW_TYPE . '_posts_custom_column', array($this, 'zwssgr_render_hide_column_content'), 10, 2);
			add_action('wp_ajax_toggle_visibility', array($this, 'zwssgr_toggle_visibility'));

			add_filter('wp_kses_allowed_html', array($this, 'zwssgr_action_allow_svg_in_post_content'));;

			add_action('wp_ajax_zwssgr_save_widget_data',array($this, 'zwssgr_save_widget_data'));
			add_action('wp_ajax_nopriv_zwssgr_save_widget_data', array($this, 'zwssgr_save_widget_data'));

			add_action('wp_ajax_zwssgr_filter_reviews', array($this,'zwssgr_filter_reviews_ajax_handler'));
			add_action('wp_ajax_nopriv_zwssgr_filter_reviews', array($this,'zwssgr_filter_reviews_ajax_handler'));

			add_filter('manage_' . ZWSSGR_POST_WIDGET_TYPE . '_posts_columns', array($this,'zwssgr_add_shortcode_column'));
			add_action('manage_' . ZWSSGR_POST_WIDGET_TYPE . '_posts_custom_column', array($this,'zwssgr_populate_shortcode_column'), 10, 2);

			add_action('restrict_manage_posts', array( $this,'zwssgr_add_custom_meta_filters'));
			add_action('pre_get_posts', array( $this,'zwssgr_filter_posts_by_custom_meta'));

			// Initialize dashboard class
			$this->zwssgr_dashboard = ZWSSGR_Dashboard::get_instance();

			$this->zwssgr_admin_smtp_enabled = get_option('zwssgr_admin_smtp_enabled');
			$this->zwssgr_smtp_opt = get_option( 'zwssgr_smtp_option' );
			$this->zwssgr_general_opt = get_option( 'zwssgr_general_option' );
			
			if( $this->zwssgr_admin_smtp_enabled == 1) {
				add_action( 'phpmailer_init', array( $this, 'zwssgr_action__init_smtp_mailer' ), 9999 );
			}

			add_action('admin_enqueue_scripts', array( $this,'zwssgr_deactivate_hook'));
			add_action('admin_footer', array( $this,'zwssgr_custom_deactivation_popup'));
		}

		/**
		 * Action: phpmailer_init
		 *
		 * Set SMTP parameters if SMTP mailer.
		 *
		 * @method zwssgr_action__init_smtp_mailer
		 *
		 * @param  mailer object  $zwssgr_phpmailer
		 *
		 */
		function zwssgr_action__init_smtp_mailer(  $zwssgr_phpmailer ) {

			$zwssgr_phpmailer->IsSMTP();

			$zwssgr_from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];
			$zwssgr_from_name = $this->zwssgr_smtp_opt['zwssgr_from_name'];

			$zwssgr_phpmailer->From     = $zwssgr_from_email;
			$zwssgr_phpmailer->FromName = $zwssgr_from_name;
			$zwssgr_phpmailer->SetFrom( $zwssgr_phpmailer->From, $zwssgr_phpmailer->FromName );

			/* Set the SMTP Secure value */
			if ( 'none' !== $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'] ) {
				$zwssgr_phpmailer->SMTPSecure = $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'];
			}

			/* Set the other options */
			$zwssgr_phpmailer->Host = $this->zwssgr_smtp_opt['zwssgr_smtp_host'];
			$zwssgr_phpmailer->Port = $this->zwssgr_smtp_opt['zwssgr_smtp_port'];

			/* If we're using smtp auth, set the username & password */
			if ( 'yes' == $this->zwssgr_smtp_opt['zwssgr_smtp_auth'] ) {
				$zwssgr_phpmailer->SMTPAuth = true;
				$zwssgr_phpmailer->Username = $this->zwssgr_smtp_opt['zwssgr_smtp_username'];
				$zwssgr_phpmailer->Password = $this->zwssgr_smtp_opt['zwssgr_smtp_password'];
			}
			//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$zwssgr_phpmailer->SMTPAutoTLS = false;

			//set reasonable timeout
			$zwssgr_phpmailer->Timeout = 10;
			$zwssgr_phpmailer->CharSet  = "utf-8";
		}
		
		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function zwssgr_admin_enqueue() 
		{
			// Google chart JS
			wp_register_script( ZWSSGR_PREFIX . '-google-chart-js', ZWSSGR_URL .'assets/src/js/backend/google-chart.js', array('jquery-core'), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '-google-chart-js' );

			wp_register_script( ZWSSGR_PREFIX . '-main-js', ZWSSGR_URL . 'assets/dist/main.js', array(), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '-main-js' );

			wp_register_script( ZWSSGR_PREFIX . '-backend-js', ZWSSGR_URL . 'assets/dist/backend.js', array(), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '-backend-js' );

			wp_register_style( ZWSSGR_PREFIX . '-style-css', ZWSSGR_URL . 'assets/dist/styles.css', false, ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . '-style-css' );	

			// font 
			wp_register_style( ZWSSGR_PREFIX . 'custom-admin-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', false, ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . 'custom-admin-font' );

			wp_register_style( ZWSSGR_PREFIX . 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', false, ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . 'swiper-css' );

			// swiper js
			wp_register_script( ZWSSGR_PREFIX . 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array('jquery-core'), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . 'swiper-js' );


			//Toggle Ajax
			wp_localize_script(ZWSSGR_PREFIX . '-main-js', 'zwssgr_admin', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('toggle-visibility-nonce')
			));

			$zwssgr_data_render_args = $this->zwssgr_dashboard->zwssgr_data_render_query([
				'zwssgr_gmb_account_number'   => null,
				'zwssgr_gmb_account_location' => null,
				'zwssgr_range_filter_type'    => 'rangeofdays',
				'zwssgr_range_filter_data'    => 'monthly'
			]);

			//Toggle Ajax
			wp_localize_script(ZWSSGR_PREFIX . '-main-js', 'zwssgr_admin', array(
				'ajax_url' 					    => admin_url('admin-ajax.php'),
				'nonce' 					    => wp_create_nonce('toggle-visibility-nonce'),
				'zwssgr_queue_manager_nounce'    => wp_create_nonce('zwssgr_queue_manager_nounce'),
				'zwssgr_delete_oauth_connection' => wp_create_nonce('zwssgr_delete_oauth_connection'),
				'zwssgr_add_update_reply_nonce'  => wp_create_nonce('zwssgr_add_update_reply_nonce'),
				'zwssgr_delete_review_reply'	    => wp_create_nonce('zwssgr_delete_review_reply'),
				'zwssgr_gmb_dashboard_filter'	=> wp_create_nonce('zwssgr_gmb_dashboard_filter'),
				'zwssgr_data_render'				=> wp_create_nonce('zwssgr_data_render'),
				'zwssgr_dynamic_chart_data'		=> $this->zwssgr_dashboard->zwssgr_dynamic_chart_data($zwssgr_data_render_args),
				'zwssgr_redirect'				=> admin_url('admin.php?page=zwssgr_connect_google')
			));

			//Save Widget Ajax
			wp_localize_script(ZWSSGR_PREFIX . '-main-js', 'my_widget', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('my_widget_nonce'),
            ));

			wp_localize_script(ZWSSGR_PREFIX . '-main-js', 'zwssgr_filter_reviews', array(
				'ajax_url' => admin_url('admin-ajax.php'), // The AJAX handler URL
				'nonce' => wp_create_nonce('zwssgr_filter_reviews_nonce') // Security nonce
			));
		
		}

		function zwssgr_deactivate_hook($zwssgr_hook) {
			// Ensure the script is only loaded on the plugins page
			if ($zwssgr_hook === 'plugins.php') {
				
				wp_localize_script(ZWSSGR_PREFIX . '-admin-js', 'ZWSSGR_Popup_Data', [
					'nonce' => wp_create_nonce('zwssgr_deactivation_nonce'),
					'deactivateUrl' => admin_url('plugins.php?action=deactivate&plugin=smart-showcase-for-google-reviews/smart-showcase.php'),
				]);
			}
		}

		// Add a custom JavaScript function to handle the popup
		function zwssgr_custom_deactivation_popup() {
			?>
			<div id="zwssgr-plugin-deactivation-popup" class="zwssgr-plugin-popup" style="display:none;">
				<div class="zwssgr-plugin-popup-content">
					<div class="zwssgr-plugin-popup-header">
						<h2><?php esc_html_e('Confirm Deactivation', 'smart-showcase-for-google-reviews'); ?></h2>
					</div>
					<div class="zwssgr-plugin-popup-body">
						<div class="zwssgr-plugin-wrapper-outer">
							<div class="zwssgr-plugin-wrapper-container">
								<div id="zwssgr-plugin-disconnect-wrapper" class="zwssgr-plugin-inner-wrapper">
									<div class="zwssgr-plugin-caution-div">
										<input type="checkbox" id="zwssgr-delete-plugin-data" name="zwssgr_delete_plugin_data" class="zwssgr-delete-plugin-data">
										<label for="zwssgr-delete-plugin-data">
											<?php esc_html_e('Attention: Select this box to permanently delete all data.', 'smart-showcase-for-google-reviews'); ?>
										</label>
									</div>
									<div class="zwssgr-plugin-danger-note">
										<?php esc_html_e('This action is irreversible. Please double-check before proceeding.', 'smart-showcase-for-google-reviews'); ?>
									</div>
									<span class="zwssgr-btn button-danger" id="zwssgr-plugin-confirm-deactivate"><?php esc_html_e('Deactivate', 'smart-showcase-for-google-reviews'); ?></span>
									<span class="zwssgr-btn" id="zwssgr-plugin-cancel-deactivate"><?php esc_html_e('Cancel', 'smart-showcase-for-google-reviews'); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
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
				'zwssgr_dashboard',
				array($this, 'zwssgr_dashboard_callback'),
				'data:image/svg+xml;base64,' . base64_encode('
				<svg xmlns="http://www.w3.org/2000/svg" width="986.612" height="699.837" viewBox="0 0 986.612 699.837"><path d="M779.4,699.837h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.9,33.9,0,0,1,31.307,20.757,33.371,33.371,0,0,1,2.592,13.143v9.9Zm-102.5,0h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.939,33.939,0,0,1,33.9,33.9v9.9Zm-102.6,0h-54a33.938,33.938,0,0,1-33.9-33.9v-9.9h54a33.938,33.938,0,0,1,33.9,33.9v9.9ZM448.583,660.731c-22.626-9.023-113.855-34.606-124.182-37.493a217.782,217.782,0,0,1-35-11.7h.5a267.391,267.391,0,0,0,28.2,1.5c90.276,0,186.394-44.829,321.4-149.9,35.6,5,77.895,8.731,122.3,10.8a431.88,431.88,0,0,0-100.975,20.8,365.261,365.261,0,0,0-52.526,22.3,253.325,253.325,0,0,0-25.187,14.675A280.3,280.3,0,0,0,545.1,561.838c-.607.607-1.2,1.169-1.779,1.714l-.008.007,0,0c-.864.816-1.68,1.586-2.51,2.475a232.453,232.453,0,0,1-46.15,33.812,238.382,238.382,0,0,1-51.55,21.288c.495,0,29.507,21.582,29.8,21.8-4.66-1.854-8.267-3.457-11.449-4.871-9.245-4.108-15.925-7.076-38.35-11.829-7.608,1.3-22.312,2.7-30,3.2h.2c11.151,8.118,53.487,30.349,55.284,31.292Zm447.971-23.584h0a129.646,129.646,0,0,1-24.65-2.309c-3.758-.813-7.632-1.835-11.6-2.9l-.3-.1C819.738,622.592,785.045,602,751.494,582.09l-.594-.352-.415-.247c-29.474-17.525-57.313-34.077-84.585-39.253a152.638,152.638,0,0,0-58.7.3,312.637,312.637,0,0,1,47-26.862A357.143,357.143,0,0,1,705.13,496.7a382.972,382.972,0,0,1,52.684-11.265,374.822,374.822,0,0,1,52.247-3.718c40.5,0,77.889,6.855,108.14,19.825a155.355,155.355,0,0,1,32.919,18.7A100.343,100.343,0,0,1,971.7,540.925a79.176,79.176,0,0,1,10.906,20.814,75.647,75.647,0,0,1,3.893,19.1c.809,10.009-2.757,19.362-10.6,27.8-7.957,8.611-19.712,15.839-33.993,20.9A137.22,137.22,0,0,1,896.554,637.147ZM314.6,603.938c-21.8,0-44-5.148-66-15.3a99.589,99.589,0,0,1-12.125-6.775,110.083,110.083,0,0,1-37.143-40.871,113.1,113.1,0,0,1-11.92-37.292,117.773,117.773,0,0,1,1.223-39.373A119.711,119.711,0,0,1,203.2,426.538a182,182,0,0,1,12.966-18.913A212.262,212.262,0,0,1,233.2,388.413a256.428,256.428,0,0,1,21.609-19.25,318.762,318.762,0,0,1,26.69-19.025c9.564-6.119,19.878-12.06,30.655-17.656s22.3-11,34.268-16.064,24.7-9.93,37.847-14.463,27.072-8.856,41.392-12.854,29.425-7.778,44.9-11.237,31.755-6.693,48.38-9.611C552.222,262.41,588.24,257.594,626,253.938c-40.351,94.253-83.148,171.4-127.2,229.3C437.708,563.329,375.734,603.938,314.6,603.938ZM449.311,567.53v0c9.052-8.306,18.188-17.493,27.153-27.3s18.027-20.538,26.938-31.888c45.206-57.708,89.18-134.654,130.7-228.7l12.3-27.7c19.11-1.057,38.085-1.83,56.4-2.3,16.342-.531,32.323-.8,47.5-.8-19.457,27.137-39.477,53.774-59.5,79.173-24.461,31.021-49.007,60.28-72.958,86.964C557.857,481.8,501.156,533.128,449.311,567.53Zm127.6-87.1v0C654.5,404.566,723.868,314.138,768.4,251.637c4.411.053,9.526.1,15.448.165l.288,0h.012c16.165.164,36.284.368,55.551.732a3.155,3.155,0,0,1,2.956,2.051,3.308,3.308,0,0,1-.756,3.649c-25.5,25.1-52.047,50.069-78.913,74.2-37.263,33.469-74.335,64.591-110.187,92.5C626.72,445.244,601.187,463.915,576.91,480.431Zm-409.011-7.2c-.829-2.57-1.715-5.578-2.74-9.061q-.321-1.089-.658-2.232a237.732,237.732,0,0,1-5.465-24.733l-.135-.767c-.1-.693-.2-1.2-.3-1.681l0-.019c-.327-1.835-1.131-8.032-2.147-15.878l0-.019c-.529-4.082-1.128-8.709-1.75-13.4-1.1-8.81-2.239-17.207-3-22.7-1.788-13.6-5.186-30.088-10.1-49a179.766,179.766,0,0,0-6.374-19.362c-2.722-7.063-5.719-13.937-8.026-19.137-5.586-12.6-14.122-27.78-20.3-38.3a182.617,182.617,0,0,0-15.2-21.2,128.107,128.107,0,0,0-18.988-18.161,89.852,89.852,0,0,0-15.837-9.827c-9-4.288-14.817-4.808-14.875-4.812a104.262,104.262,0,0,0-24.622-2.869A93.988,93.988,0,0,0,0,201.538a134.727,134.727,0,0,1,7.537-27.925A185.323,185.323,0,0,1,17.1,153.638a12.093,12.093,0,0,1,.614-1.19c.138-.244.269-.475.386-.711a96.518,96.518,0,0,1,12.25-17.35,113.86,113.86,0,0,1,11.95-11.95c.091-.091.2-.184.324-.282a6.174,6.174,0,0,0,.476-.419l.005,0c2.011-1.706,5.378-4.561,10.258-7.976A133.564,133.564,0,0,1,72.388,102.69a141.769,141.769,0,0,1,27.8-9.822,156.459,156.459,0,0,1,36.585-4.238c.513,0,1.012,0,1.527.007a180.953,180.953,0,0,1,37.992,4.13,133.357,133.357,0,0,1,28.345,9.708,116.347,116.347,0,0,1,20.3,12.389A137.871,137.871,0,0,1,238.8,127.038c6.352,6.266,13.926,15.148,22.513,26.4,7.035,9.218,12.685,17.337,14.788,20.4,4.55,6.528,13.213,20.064,13.3,20.2s8.206,13.168,15.8,25.2c-11.243,10.139-21.884,20.363-31.625,30.388-9.909,10.2-19.187,20.5-27.575,30.613-53.1,63.5-80,128.439-77.8,187.8-.035.633-.083,1.256-.129,1.858-.088,1.146-.171,2.229-.171,3.335ZM90,141a11,11,0,1,0,11,11A11.012,11.012,0,0,0,90,141ZM204.5,407.138h0l.005-.007c11.177-24,27.542-47.45,48.643-69.709a381.465,381.465,0,0,1,38.614-35.268,500.788,500.788,0,0,1,49.464-34.627,664.544,664.544,0,0,1,61.3-33.3c22.951-11.011,47.895-21.538,74.136-31.289,27.444-10.2,57.038-19.817,87.96-28.591,32.264-9.155,66.842-17.636,102.775-25.208-4.183,12.278-8.076,23.269-11.9,33.6a64.812,64.812,0,0,0-3.1,8.092l0,.008c-2.257,5.958-4.48,12.043-6.629,17.927-3.7,10.128-7.194,19.7-10.471,27.573C369.949,257.1,273.734,317.084,204.5,407.138Zm25.109-72.41h0c6.312-9.815,13.415-19.8,21.11-29.664,7.953-10.2,16.752-20.55,26.154-30.768,9.7-10.546,20.312-21.235,31.531-31.771,11.566-10.863,24.1-21.859,37.247-32.685,13.544-11.151,28.113-22.425,43.3-33.51,15.63-11.406,32.352-22.928,49.7-34.246,17.832-11.633,36.819-23.373,56.435-34.893C515.237,95.356,536.6,83.429,558.6,71.737c22.082-11.72,45.41-23.588,69.338-35.275C652.371,24.529,678.2,12.408,704.7.437A6.328,6.328,0,0,1,707,0a4.631,4.631,0,0,1,3.766,1.66,3.344,3.344,0,0,1,.434,3.077c-3.371,12.972-16,60.18-36.9,123l-10.7,2.2c-175.739,37.108-308.064,92.555-393.3,164.8A377.76,377.76,0,0,0,229.61,334.727ZM653.2,234.838h0c2.415-6.263,5.692-14.428,8.861-22.324l.01-.024c3.94-9.817,7.661-19.089,9.429-23.952l.7-2a54.811,54.811,0,0,1,2.4-6.6l.4-1c4.478-12.807,8.347-24.146,11.5-33.7,8.08-1.591,80.519-15.78,135.3-24.2a4.269,4.269,0,0,1,.717-.062,4.359,4.359,0,0,1,3.787,2.4,6.284,6.284,0,0,1,.8,3.065,6.354,6.354,0,0,1-.9,3.3c-3.061,5.11-6.835,11.527-11.2,18.958C801.6,171.468,783.26,202.657,765.1,230.837c-1.911-.008-3.878-.012-5.848-.012-21.126,0-42.292.457-62.909,1.358-19.46.85-34.2,1.913-43.142,2.655Z" fill="#a7aaad"/></svg>'),
			);

			add_submenu_page(
				'zwssgr_dashboard',
				__('Widget', 'smart-showcase-for-google-reviews'),
				__('Widgets', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'edit.php?post_type=' . ZWSSGR_POST_WIDGET_TYPE,
				null
			);

			add_submenu_page(
				'zwssgr_dashboard',
				__('Review', 'smart-showcase-for-google-reviews'),
				__('Reviews', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'edit.php?post_type=' . ZWSSGR_POST_REVIEW_TYPE,
				null
			);

			add_submenu_page(
                'Widget Configurator',
                'Widget Configurator',             
                'Widget Configurator',
                'manage_options',
                'zwssgr_widget_configurator',
                [$this, 'zwssgr_widget_configurator_callback']
            );

			add_submenu_page(
				'zwssgr_dashboard',
				__('Settings', 'smart-showcase-for-google-reviews'),
				__('Settings', 'smart-showcase-for-google-reviews'),
				'manage_options',
				'zwssgr_settings',
				array($this, 'zwssgr_settings_callback')
			);
		}

		// Register Custom Post Type: Widget
		function zwssgr_register_widget_cpt()
		{
			$zwssgr_labels = array(
				'name' => _x('Widgets', '', 'smart-showcase-for-google-reviews'),
				'singular_name' => _x('Widget', '', 'smart-showcase-for-google-reviews'),
				'menu_name' => _x('Widgets', 'admin menu', 'smart-showcase-for-google-reviews'),
				'name_admin_bar' => _x('Widget', 'add new on admin bar', 'smart-showcase-for-google-reviews'),
				'add_new' => _x('Add New', 'widget', 'smart-showcase-for-google-reviews'),
				'add_new_item' => __('Add New Widget', 'smart-showcase-for-google-reviews'),
				'new_item' => __('New Widget', 'smart-showcase-for-google-reviews'),
				'edit_item' => __('Edit Widget', 'smart-showcase-for-google-reviews'),
				'view_item' => __('View Widget', 'smart-showcase-for-google-reviews'),
				'all_items' => __('All Widgets', 'smart-showcase-for-google-reviews'),
				'search_items' => __('Search Widgets', 'smart-showcase-for-google-reviews'),
				'not_found' => __('No widgets found.', 'smart-showcase-for-google-reviews'),
				'not_found_in_trash' => __('No widgets found in Trash.', 'smart-showcase-for-google-reviews')
			);

			$zwssgr_args = array(
				'label' => __('Widgets', 'smart-showcase-for-google-reviews'),
				'labels' => $zwssgr_labels,
				'description' => '',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'delete_with_user' => true,
				'show_in_rest' => false,
				'rest_base' => '',
				'show_in_menu' => false,  // This will prevent it from appearing in the main menu
				'query_var' => false,
				'rewrite' => false,
				'capability_type' => 'post',
				'has_archive' => true,
				'show_in_nav_menus' => false,
				'exclude_from_search' => true,
				'capabilities' => array(
					'read' => true,
					'create_posts' => true,
					'publish_posts' => true,
				),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array('title')
			);

			register_post_type(ZWSSGR_POST_WIDGET_TYPE, $zwssgr_args);
		}

		function action__custom_widget_url_on_add_new() 
		{

			$zwssgr_post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
		
			if ($zwssgr_post_type === 'zwssgr_data_widget' || isset($_POST['security-zwssgr-widget-url']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-widget-url'])), 'zwssgr_send_widget_url')) {
		
				$zwssgr_widget_id = wp_insert_post([
					'post_type'   => 'zwssgr_data_widget',
					'post_status' => 'publish',
					'post_title'  => 'Auto-Created Widget ' . wp_generate_uuid4(),
				]);
			
				if (!$zwssgr_widget_id || is_wp_error($zwssgr_widget_id)) {
					$this->zwssgr_debug_function('Failed to create a new widget');
					return;
				}
		
				$zwssgr_redirect_url = admin_url('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data&zwssgr_widget_id=' . $zwssgr_widget_id);
				wp_redirect($zwssgr_redirect_url);
				exit;
		
			}
		
		}

		function zwssgr_register_review_cpt()
		{
			$zwssgr_labels = array(
				'name' => _x('Reviews', '' , 'smart-showcase-for-google-reviews'),
				'singular_name' => _x('Review', '', 'smart-showcase-for-google-reviews'),
				'menu_name' => _x('Reviews', 'admin menu', 'smart-showcase-for-google-reviews'),
				'name_admin_bar' => _x('Review', 'add new on admin bar', 'smart-showcase-for-google-reviews'),
				'add_new' => _x('Add New', 'review', 'smart-showcase-for-google-reviews'),
				'add_new_item' => __('Add New Review', 'smart-showcase-for-google-reviews'),
				'new_item' => __('New Review', 'smart-showcase-for-google-reviews'),
				'edit_item' => __('Edit Review', 'smart-showcase-for-google-reviews'),
				'view_item' => __('View Review', 'smart-showcase-for-google-reviews'),
				'all_items' => __('All Reviews', 'smart-showcase-for-google-reviews'),
				'search_items' => __('Search Reviews', 'smart-showcase-for-google-reviews'),
				'not_found' => __('No Reviews found.', 'smart-showcase-for-google-reviews'),
				'not_found_in_trash' => __('No Reviews found in Trash.', 'smart-showcase-for-google-reviews')
			);

			$zwssgr_args = array(
				'label' => __('Reviews', 'smart-showcase-for-google-reviews'),
				'labels' => $zwssgr_labels,
				'description' => '',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'delete_with_user' => true,
				'show_in_rest' => false,
				'rest_base' => '',
				'show_in_menu' => false,  // This will prevent it from appearing in the main menu
				'query_var' => false,
				'rewrite' => false,
				'capability_type' => 'post',
				'has_archive' => true,
				'show_in_nav_menus' => false,
				'exclude_from_search' => true,
				'capabilities' => array(
					'read' => true,
					'create_posts' => false,
					'publish_posts' => true,
				),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array('')
			);
			register_post_type(ZWSSGR_POST_REVIEW_TYPE, $zwssgr_args);
		}

		// Register a single meta box to display all review details
		function zwssgr_add_review_meta_box() 
		{
			remove_meta_box(
				'submitdiv', 
				'zwssgr_reviews', 
				'side'
			);
			add_meta_box(
				'zwssgr_review_details_meta_box',
				__('Review Details', 'smart-showcase-for-google-reviews'),
				array($this, 'zwssgr_display_review_details_meta_box'),
				'zwssgr_reviews',
				'normal',
				'high'
			);
		}


		/**
		 * Custom log function for debugging.
		 *
		 * @param string $message The message to log.
		 */
		function zwssgr_debug_function( $zwssgr_message ) {
			// Define the custom log directory path.

			$zwssgr_log_dir = ZWSSGR_UPLOAD_DIR.'/smart-showcase-for-google-reviews/';
		
			// Define the log file path.
			$zwssgr_log_file = $zwssgr_log_dir . '/smart-showcase-for-google-reviews-debug.log';
		
			// Check if the directory exists, if not create it.
			if ( ! file_exists( $zwssgr_log_dir ) ) {
				wp_mkdir_p( $zwssgr_log_dir );
			}
		
			// Initialize the WP_Filesystem.
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		
			global $wp_filesystem;
		
			// Format the log entry with UTC timestamp using gmdate().
			$zwssgr_log_entry = sprintf( "[%s] %s\n", gmdate( 'Y-m-d H:i:s' ), $zwssgr_message );
		
			// Write the log entry to the file using WP_Filesystem.
			if ( $wp_filesystem->exists( $zwssgr_log_file ) || $wp_filesystem->put_contents( $zwssgr_log_file, $zwssgr_log_entry, FS_CHMOD_FILE ) ) {
				$wp_filesystem->put_contents( $zwssgr_log_file, $zwssgr_log_entry, FS_CHMOD_FILE );
			}
		}
		
		function zwssgr_action_allow_svg_in_post_content($zwssgr_allowed_tags) {

			$zwssgr_allowed_tags['svg'] = array(
				'xmlns' => true,
				'width' => true,
				'height' => true,
				'fill' => true,
				'viewBox' => true,
				'class' => true,
				'stroke' => true,
				'stroke-width' => true,
			);
			$zwssgr_allowed_tags['path'] = array(
				'd' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
			);

			return $zwssgr_allowed_tags;
			
		}

		// Display all review details in one meta box
		function zwssgr_display_review_details_meta_box($zwssgr_review) 
		{

			$zwssgr_review_published_date = get_the_date( 'F j, Y g:i A', $zwssgr_review->ID );
			$zwssgr_review_comment	  	 = get_post_meta($zwssgr_review->ID, 'zwssgr_review_comment', true);
			$zwssgr_reviewer_name 	  	 = get_post_meta($zwssgr_review->ID, 'zwssgr_reviewer_name', true);
			$zwssgr_review_star_rating 	 = get_post_meta($zwssgr_review->ID, 'zwssgr_review_star_rating', true);
			$zwssgr_reply_comment 	  	 = get_post_meta($zwssgr_review->ID, 'zwssgr_reply_comment', true);
			$zwssgr_reply_update_time  	 = get_post_meta($zwssgr_review->ID, 'zwssgr_reply_update_time', true);

			// Define the SVG for filled and empty stars
			$zwssgr_filled_star = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="orange" class="bi bi-star-fill" viewBox="0 0 16 16">
			<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
			</svg>';

			$zwssgr_empty_star = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="orange" stroke-width="1" class="bi bi-star" viewBox="0 0 16 16">
			<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
			</svg>';

			// Map the rating value (in words) to numerical equivalents
			$zwssgr_rating_map = [
				'FIVE' => 5,
				'FOUR' => 4,
				'THREE' => 3,
				'TWO' => 2,
				'ONE' => 1,
				'ZERO' => 0,
			];

			$zwssgr_rating_value 	  	   = strtoupper($zwssgr_review_star_rating);
			$zwssgr_numeric_rating 		   = isset($zwssgr_rating_map[$zwssgr_rating_value]) ? $zwssgr_rating_map[$zwssgr_rating_value] : 0;
			$zwssgr_filled_star 		   = str_repeat($zwssgr_filled_star, $zwssgr_numeric_rating);
			$zwssgr_empty_star 		  	   = str_repeat($zwssgr_empty_star, 5 - $zwssgr_numeric_rating);
			$zwssgr_review_id 		  	   = get_post_meta($zwssgr_review->ID, 'zwssgr_review_id', true);
			$zwssgr_gmb_reviewer_image_uri = ZWSSGR_UPLOAD_URL . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';

			echo '<table class="form-table zwssgr-gmb-review-data" id="gmb-review-data" zwssgr-review-id="'.esc_attr( $zwssgr_review->ID ).'">
				<tr>
					<th>
						<label for="zwssgr_reviewer_image">' . esc_html('', 'smart-showcase-for-google-reviews') . '</label>
					</th>
					<td>';
						if (!empty($zwssgr_gmb_reviewer_image_uri)) {
							echo '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="zwssgr-fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">';
						} else {
							echo '<img src="' . esc_url( ZWSSGR_URL . '/assets/images/fallback-user-dp.svg' ) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">';						
						}
					echo '</td>
				</tr>
				<tr>
					<th>
						<label for="zwssgr_reviewer_name">' . esc_html('Reviewer', 'smart-showcase-for-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwssgr_reviewer_name) . '" readonly class="zwssgr-regular-text" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwssgr_review_published_date">' . esc_html('Publish Date', 'smart-showcase-for-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwssgr_review_published_date) . '" readonly class="zwssgr-regular-text" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwssgr_review_comment">' . esc_html('Review Content', 'smart-showcase-for-google-reviews') . '</label>
					</th>
					<td>
						<textarea readonly class="zwssgr-regular-text" rows="5">' . esc_textarea($zwssgr_review_comment) . '</textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwssgr_review_star_rating">' . esc_html('Star Ratings', 'smart-showcase-for-google-reviews') . '</label>
					</th>
					<td>
						<div class="zwssgr-star-ratings"> ' . wp_kses_post($zwssgr_filled_star . $zwssgr_empty_star) . ' </div>
					</td>
					<td><div class="separator"></div></td>
				</tr>
				<tr>
					<th class="zwssgr-separator-cell" colspan="2">
						<hr class="separator">
					</th>
				</tr>';
				if (!empty($zwssgr_reply_update_time)) {
					$zwssgr_datetime = new DateTime($zwssgr_reply_update_time);
					$zwssgr_formatted_time = $zwssgr_datetime->format('F j, Y g:i A');
					echo '<tr>
						<th>
							<label for="zwssgr_reply_update_time">' . esc_html('Reply Update Time', 'smart-showcase-for-google-reviews') . '</label>
						</th>
						<td>
							<input type="text" value="' . esc_attr($zwssgr_formatted_time) . '" readonly class="zwssgr-regular-text" />
						</td>
					</tr>';

				}
				echo '<tr>
					<th>
						<label for="zwssgr_reply_comment"> ' . esc_html('Reply Content', 'smart-showcase-for-google-reviews') . ' </label>
					</th>
					<td>
						<div id="json-response-message" class="json-response-message zwssgr-success-message"></div>
						<textarea name="zwssgr_reply_comment" class="zwssgr-regular-text" rows="5">'. esc_textarea($zwssgr_reply_comment) .'</textarea>
						<div class="zwssgr-cta-wrapper">';
							if (!empty($zwssgr_reply_comment)) {
								echo '<button class="button button-primary button-large zwssgr-submit-btn" id="update-reply"> ' . esc_html('Update', 'smart-showcase-for-google-reviews') . ' </button>';
							} else {
								echo '<button class="button button-primary button-large zwssgr-submit-btn" id="add-reply"> ' . esc_html('Add Reply', 'smart-showcase-for-google-reviews') . ' </button>';
							}
							if (!empty($zwssgr_reply_comment)) {
								echo '<button class="button zwssgr-button-danger button-large zwssgr-submit-btn" id="delete-reply">' . esc_html('Delete', 'smart-showcase-for-google-reviews') . '</button>';
							}
						echo '</div>
					</td>
				</tr>
			</table>';
			
		}

		function zwssgr_register_request_data_cpt()
		{
			$zwssgr_labels = array(
				'name'               => __('Request Data', 'smart-showcase-for-google-reviews'),
				'singular_name'      => __('Request Data', 'smart-showcase-for-google-reviews'),
				'menu_name'          => __('Request Data', 'smart-showcase-for-google-reviews'),
				'name_admin_bar'     => __('Request Data', 'smart-showcase-for-google-reviews'),
				'add_new'            => __('Add New', 'smart-showcase-for-google-reviews'),
				'add_new_item'       => __('Add New Request Data', 'smart-showcase-for-google-reviews'),
				'new_item'           => __('New Request Data', 'smart-showcase-for-google-reviews'),
				'edit_item'          => __('Edit Request Data', 'smart-showcase-for-google-reviews'),
				'view_item'          => __('View Request Data', 'smart-showcase-for-google-reviews'),
				'all_items'          => __('All Request Data', 'smart-showcase-for-google-reviews'),
				'search_items'       => __('Search Request Data', 'smart-showcase-for-google-reviews'),
				'not_found'          => __('No Request Data found.', 'smart-showcase-for-google-reviews'),
				'not_found_in_trash' => __('No Request Data found in Trash.', 'smart-showcase-for-google-reviews')
			);

			$zwssgr_args = array(
				'label'               => __('Request Data', 'smart-showcase-for-google-reviews'),
				'labels'              => $zwssgr_labels,
				'description'         => '',
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'delete_with_user'    => true,
				'show_in_rest'        => false,
				'rest_base'           => '',
				'show_in_menu'        => false,  // Prevent from showing in the main menu
				'query_var'           => false,
				'rewrite'             => array(
					'slug' => 'zwssgr_request_data',
					'with_front' => false
				),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'capabilities'        => array(
					'read' => true,
					'create_posts' => 'do_not_allow',
					'publish_posts' => 'do_not_allow',
				),
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array('title')
			);
			register_post_type('zwssgr_request_data', $zwssgr_args);  // Updated post type name to zwssgr_request_data
		}

		function zwssgr_add_custom_meta_filters() 
		{
			global $wpdb;

			// Get current post type
			$zwssgr_current_post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';

			// Check if we are on the correct post type pages
			if (!in_array($zwssgr_current_post_type, [ZWSSGR_POST_REVIEW_TYPE, ZWSSGR_POST_WIDGET_TYPE])) {
				return;
			}

			if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
				return;
			}

			// Get saved email and selected filters from URL parameters
			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_selected_account = isset($_GET['zwssgr_account']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_account'])) : '';
			$zwssgr_selected_location = isset( $_GET['zwssgr_location'] ) ? sanitize_text_field( wp_unslash( $_GET['zwssgr_location'] ) ) : '';

			// Fetch accounts using SQL query, with caching
			$zwssgr_cached_accounts = 'zwssgr_accounts_' . md5($zwssgr_gmb_email);
			$zwssgr_accounts 		= get_transient($zwssgr_cached_accounts);

			if ($zwssgr_accounts === false || empty($zwssgr_accounts)) {

				$zwssgr_accounts = $wpdb->get_results( $wpdb->prepare("
					SELECT pm.meta_value AS account_number, p.ID AS post_id, p.post_title AS account_name
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE pm.meta_key = %s
					AND p.post_type = %s
					AND p.post_status = 'publish'
					AND EXISTS (
						SELECT 1 FROM {$wpdb->postmeta} pm2
						WHERE pm2.post_id = p.ID AND pm2.meta_key = 'zwssgr_gmb_email' AND pm2.meta_value = %s
					)", 'zwssgr_account_number', 'zwssgr_request_data', $zwssgr_gmb_email)
				);

				set_transient($zwssgr_cached_accounts, $zwssgr_accounts, HOUR_IN_SECONDS);

			}

			// Begin the form
			echo '<form method="GET">';
			wp_nonce_field('zwssgr_get_form', 'security-zwssgr-get-form');
			echo '<input type="hidden" name="post_type" value="' . esc_attr($zwssgr_current_post_type) . '">';

			// Account dropdown
			echo '<select id="zwssgr-account-select" name="zwssgr_account">';
			echo '<option value="">' . esc_html__('Select an Account', 'smart-showcase-for-google-reviews') . '</option>';
			foreach ($zwssgr_accounts as $zwssgr_account) {
				$zwssgr_selected = ($zwssgr_account->account_number === $zwssgr_selected_account) ? ' selected' : '';
				echo '<option value="' . esc_attr($zwssgr_account->account_number) . '"' . esc_attr( $zwssgr_selected ). '>' . esc_html($zwssgr_account->account_name) . '</option>';
			}
			echo '</select>';

			// Fetch accounts using SQL query, with caching
			$zwssgr_cached_locations = 'zwssgr_locations_' . md5($zwssgr_selected_account);
			$zwssgr_locations 		 = get_transient($zwssgr_cached_locations);

			if ($zwssgr_locations === false || empty($zwssgr_locations)) {

				$zwssgr_locations = $wpdb->get_results( 
					$wpdb->prepare("
						SELECT pm.meta_value AS location_data, p.ID AS post_id
						FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
						WHERE pm.meta_key = %s
						AND p.post_type = %s
						AND p.post_status = 'publish'
						AND EXISTS (
							SELECT 1 FROM {$wpdb->postmeta} pm2
							WHERE pm2.post_id = p.ID AND pm2.meta_key = 'zwssgr_account_number' AND pm2.meta_value = %s
						)
					", 'zwssgr_account_locations', 'zwssgr_request_data', $zwssgr_selected_account)
				);

				set_transient($zwssgr_cached_locations, $zwssgr_locations, HOUR_IN_SECONDS);
				
			}

			if (!empty($zwssgr_locations)){

				// Location dropdown
				echo '<select id="zwssgr-location-select" name="zwssgr_location">';
				echo '<option value="">' . esc_html__('Select a Location', 'smart-showcase-for-google-reviews') . '</option>';

					// Parse and output location options
					foreach ($zwssgr_locations as $zwssgr_location) {
						$zwssgr_location_data = maybe_unserialize($zwssgr_location->location_data);
						if (is_array($zwssgr_location_data)) {
							foreach ($zwssgr_location_data as $zwssgr_loc) {
								$zwssgr_loc_title = $zwssgr_loc['title'] ?? '';
								$zwssgr_loc_value = ltrim(strrchr($zwssgr_loc['name'], '/'), '/');
								$zwssgr_selected = ($zwssgr_loc_value === $zwssgr_selected_location) ? ' selected' : '';
								echo '<option value="' . esc_attr($zwssgr_loc_value) . '"' . esc_attr($zwssgr_selected) . '>' . esc_html($zwssgr_loc_title) . '</option>';
							}
						}
					}

				echo '</select>';
				echo '</form>';
			}
			
		}

		// Filter posts based on custom meta for ZWSSGR_POST_REVIEW_TYPE
		function zwssgr_filter_posts_by_custom_meta($zwssgr_query)
		{
			global $pagenow;

			if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
				return;
			}

			if (is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && in_array($_GET['post_type'], [ZWSSGR_POST_REVIEW_TYPE, ZWSSGR_POST_WIDGET_TYPE])) {
				
				$zwssgr_meta_query = array();
				
				if (isset($_GET['zwssgr_account']) && !empty($_GET['zwssgr_account'])) {
					$zwssgr_account = isset($_GET['zwssgr_account']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_account'])) : '';
					$zwssgr_meta_query[] = [
						'key'     => 'zwssgr_account_number',
						'value'   => $zwssgr_account,
						'compare' => '='
					];
				}
				

				if (isset($_GET['zwssgr_location']) && !empty($_GET['zwssgr_location'])) {
					$zwssgr_location = isset($_GET['zwssgr_location']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_location'])) : '';
					$zwssgr_meta_query[] = [
						'key'     => 'zwssgr_location_number',
						'value'   => $zwssgr_location,
						'compare' => '='
					];
				}
				

				if (!empty($zwssgr_meta_query)) {
					$zwssgr_query->set('meta_query', array_merge([
						'relation' => 'AND',
					], $zwssgr_meta_query));
				}
			}
		}

		// Add the custom "Shortcode" column
		function zwssgr_add_shortcode_column($zwssgr_columns) 
		{
			$zwssgr_new_columns = array();
			foreach ($zwssgr_columns as $zwssgr_key => $zwssgr_title) {
				if ($zwssgr_key === 'title') {
					$zwssgr_new_columns[$zwssgr_key] = $zwssgr_title; // Keep the Title column
					$zwssgr_new_columns['shortcode'] = __('Shortcode', 'smart-showcase-for-google-reviews'); // Add Shortcode column after Title
				} else {
					$zwssgr_new_columns[$zwssgr_key] = $zwssgr_title; // Add other columns
				}
			}
			return $zwssgr_new_columns;
		}

		function zwssgr_populate_shortcode_column($zwssgr_column, $zwssgr_post_id) 
		{
			if ($zwssgr_column === 'shortcode') {
				// Check if the "tab-selected" metadata exists and meets a specific condition
				$zwssgr_current_tab2 = get_post_meta($zwssgr_post_id, 'tab-selected', true); 
				
				if ($zwssgr_current_tab2) { // Or use any specific condition for `$zwssgr_current_tab2`
					// Generate the shortcode
					$zwssgr_shortcode = sprintf('[zwssgr_widget post-id="%d"]', $zwssgr_post_id);
					
					// Display the shortcode and copy icon
					echo '<div class="zwssgr-shortcode">';
						echo '<input type="text" value="' . esc_attr($zwssgr_shortcode) . '" readonly class="zwssgr-shortcode-text" id="shortcode-' . esc_attr($zwssgr_post_id) . '">';
						echo '<span class="dashicons dashicons-admin-page copy-shortcode-icon" data-target="shortcode-' . esc_attr($zwssgr_post_id) . '" class="zwssgr-dashicons" title="' . esc_attr__('Copy Shortcode', 'smart-showcase-for-google-reviews') . '"></span>';
					echo '</div>';
				} else {
					// Optionally, you can display a message or leave it blank if the condition is not met
					echo '<span>' . esc_html('Please select the appropriate options', 'smart-showcase-for-google-reviews') . '</span>';
				}
			}
		}

		/**
		 * Filter: manage_zwssgr_reviews_posts_columns
		 *
		 * - Used to add new column fields for the "zwssgr_reviews" CPT
		 *
		 * @method filter__zwssgr_manage_zwssgr_reviews_posts_columns
		 *
		 * @param  array $zwssgr_columns
		 *
		 * @return array
		 */
		function zwssgr_filter_manage_data_posts_columns($zwssgr_columns)
		{
			unset($zwssgr_columns['date']);
			unset($zwssgr_columns['title']);
			$zwssgr_columns['title'] = __('Review', 'smart-showcase-for-google-reviews');
			$zwssgr_columns[ZWSSGR_META_PREFIX . 'user_login'] = __('Hide', 'smart-showcase-for-google-reviews');
			$zwssgr_columns['date'] = __('Date', 'smart-showcase-for-google-reviews');
			return $zwssgr_columns;
		}

		/**
		 * Render the visibility column content
		 *
		 * @param string $zwssgr_column
		 * @param int $zwssgr_post_id
		 */
		function zwssgr_render_hide_column_content( $zwssgr_column, $zwssgr_post_id ) 
		{
			if ( $zwssgr_column === ZWSSGR_META_PREFIX . 'user_login' ) {
				$zwssgr_is_hidden = get_post_meta( $zwssgr_post_id, '_is_hidden', true );
				$zwssgr_icon = $zwssgr_is_hidden ? 'hidden' : 'visibility';

				// Display the toggle button with the current state
				echo '<a href="#" class="zwssgr-toggle-visibility" data-post-id="' . esc_attr( $zwssgr_post_id ) . '">';
					echo '<span class="dashicons dashicons-' . esc_attr( $zwssgr_icon ) . '"></span>';
				echo '</a>';
			}
		}

		/**
		 * Toggle visibility (AJAX callback)
		 */
		function zwssgr_toggle_visibility() 
		{
			check_ajax_referer( 'toggle-visibility-nonce', 'nonce' );
			$zwssgr_post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : 0;

			if ( ! current_user_can( 'edit_post', $zwssgr_post_id ) ) {
				wp_send_json_error( array( 'message' => 'Not authorized' ) );
			}

			$zwssgr_is_hidden = get_post_meta( $zwssgr_post_id, '_is_hidden', true );

			if ( $zwssgr_is_hidden ) {
				// If currently hidden, set to visibility and delete meta
				delete_post_meta( $zwssgr_post_id, '_is_hidden' );
				$zwssgr_new_state = 'show';
				$zwssgr_icon = 'visibility';
			} else {
				// If currently visibility, set to hidden
				update_post_meta( $zwssgr_post_id, '_is_hidden', 1 );
				$zwssgr_new_state = 'hidden';
				$zwssgr_icon = 'hidden';
			}
		
			// Send JSON response back to the front-end
			wp_send_json_success( array(
				'icon' => $zwssgr_icon,
				'state' => $zwssgr_new_state // Send the current state (hidden/show)
			));
		}

		/**
		 * Register settings for the plugin
		 * @method zwssgr_register_settings
		 */
		function zwssgr_register_settings()
		{

			// SEO & Notifications Settings
			register_setting('zwssgr_notification_settings', 'zwssgr_admin_notification_enabled', 'sanitize_text_field');

			// Advanced Setting
			register_setting('zwssgr_advanced_account_settings', 'zwssgr_sync_reviews', 'sanitize_text_field');

			// Google setting section & fields
			add_settings_section(
				'zwssgr_google_section',
				__('Google Settings', 'smart-showcase-for-google-reviews'),
				null,
				'zwssgr_google_account_settings'
			);

			// Notification section & fields
			add_settings_section(
				'zwssgr_notification_section',
				__('SEO & Notifications', 'smart-showcase-for-google-reviews'),
				null,
				'zwssgr_notification_settings'
			);

			add_settings_field(
				'zwssgr_admin_notification_enabled',
				__('Enable Admin Notifications', 'smart-showcase-for-google-reviews'),
				array($this, 'zwssgr_admin_notification_enabled_callback'),
				'zwssgr_notification_settings',
				'zwssgr_notification_section'
			);

			// Add settings for the Advanced tab
			add_settings_section(
				'zwssgr_advanced_section',
				__('Advanced Settings', 'smart-showcase-for-google-reviews'),
				null,
				'zwssgr_advanced_account_settings'
			);

			add_settings_field(
				'zwssgr_sync_reviews',
				__('Sync Reviews', 'smart-showcase-for-google-reviews'),
				array($this, 'zwssgr_sync_reviews_callback'),
				'zwssgr_advanced_account_settings',
				'zwssgr_advanced_section'
			);

		}

		// Notifications callback
		function zwssgr_admin_notification_enabled_callback()
		{
			$zwssgr_value = get_option('zwssgr_admin_notification_enabled', '0');
			echo '<label class="switch zwssgr-switch">';
			echo '<input type="checkbox" id="zwssgr_admin_notification_enabled" class="zwssgr-input-check" name="zwssgr_admin_notification_enabled" value="1" ' . checked(0, $zwssgr_value, false) . ' />';
			echo '<span class="slider zwssgr-toggle-slider"></span>';
			echo '</label>';
		}

		function zwssgr_admin_notification_emails_callback()
		{
			$zwssgr_value = get_option('zwssgr_admin_notification_emails', '');
			echo '<div class="zwssgr-notification-field">';
				echo '<div class="zwssgr-th-label">';
					echo '<label class="zwssgr-th">' . esc_html__('Custom Email Addresses', 'smart-showcase-for-google-reviews') . '</label>';
				echo '</div>';
				echo '<div class="zwssgr-field">';
					echo '<input type="text" id="zwssgr_admin_notification_emails" name="zwssgr_admin_notification_emails" class="zwssgr-input-text" rows="5" cols="50" value="' . esc_attr($zwssgr_value) . '" />';
					echo '<p class="zwssgr-description">' . esc_html('Enter email addresses separated by commas.', 'smart-showcase-for-google-reviews') . '</p>';
				echo '</div>';
			echo '</div>';
		}

		function zwssgr_admin_notification_emails_subject_callback()
		{
			$zwssgr_value = get_option('zwssgr_admin_notification_emails_subject', '');
			echo '<div class="zwssgr-notification-field">';
				echo '<div class="zwssgr-th-label">';
					echo '<label class="zwssgr-th">' . esc_html__('Custom Email Subject', 'smart-showcase-for-google-reviews') . '</label>';
				echo '</div>';
				echo '<div class="zwssgr-field">';
					echo '<input type="text" id="zwssgr_admin_notification_emails_subject" class="zwssgr-input-text" name="zwssgr_admin_notification_emails_subject" rows="5" cols="50" value="' . esc_attr(
					$zwssgr_value) . '" />';
				echo '</div>';
			echo '</div>';
		}

		function zwssgr_admin_notofication_email_body_callback() {
			// Get the current value from the database
			$zwssgr_value = get_option('zwssgr_admin_notification_email_body', '');
		
			// Set up the WYSIWYG editor settings
			$settings = array(
				'textarea_name' => 'zwssgr_admin_notification_email_body',
				'editor_class'  => 'zwssgr_email_body_editor', // Add a custom class if needed
				'media_buttons' => false, // Set to true to show media buttons
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,unlink,|,removeformat', 
				),
				'textarea_rows' => 10, // Set the number of rows for the editor
			);
			
			echo '<div class="zwssgr-editor-wrapper">';
				echo '<div class="zwssgr-th-label">';
					echo '<label for="zwssgr_admin_notification_email_body" class="zwssgr-th">' . esc_html('Email Body', 'smart-showcase-for-google-reviews') . '</label>';
				echo '</div>';
				echo '<div class="zwssgr-field">';
					wp_editor($zwssgr_value, 'zwssgr_admin_notification_email_body', $settings);
					echo '<p class="zwssgr-description">' . esc_html('Enter your custom email body content here.', 'smart-showcase-for-google-reviews') . '</p>';
				echo '</div>';
			echo '</div>';
		}

		//Advance Section 
		function zwssgr_sync_reviews_callback()
		{
			$zwssgr_value = get_option('zwssgr_sync_reviews', 'daily');
			echo '<select id="zwssgr_sync_reviews" name="zwssgr_sync_reviews" class="zwssgr-input-text zwssgr-input-select">
					<option value="daily" ' . selected($zwssgr_value, 'daily', false) . '>'. esc_html__('Daily', 'smart-showcase-for-google-reviews').'</option>
					<option value="weekly" ' . selected($zwssgr_value, 'weekly', false) . '>'. esc_html__('Weekly', 'smart-showcase-for-google-reviews').'</option>
					<option value="monthly" ' . selected($zwssgr_value, 'monthly', false) . '>'. esc_html__('Monthly', 'smart-showcase-for-google-reviews').'</option>
				</select>';
		}

		/**
		 * Action: action_admin_menu
		 * Add admin registration settings page for the plugin
		 * @method zwssgr_setting_page
		 */
		function zwssgr_settings_callback()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			// Show error messages for both settings groups
			settings_errors('zwssgr_google_account_settings');
			settings_errors('zwssgr_advanced_account_settings');
			settings_errors('zwssgr_settings&tab=notifications');

			// Handle form submission (send email)
			if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

				if (isset($_POST['zwssgr_notification_nonce_field'])) {
					$zwssgr_nonce = isset($_POST['zwssgr_notification_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_notification_nonce_field'])) : '';
				
					if (wp_verify_nonce($zwssgr_nonce, 'zwssgr_notification_nonce')) {
					// Handle notification emails submission
						if (isset($_POST['zwssgr_admin_notification_emails'])) {
							// Sanitize and save the form values
							$zwssgr_emails = isset($_POST['zwssgr_admin_notification_emails']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_admin_notification_emails'])) : '';
							$zwssgr_subject = isset($_POST['zwssgr_admin_notification_emails_subject']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_admin_notification_emails_subject'])) : '';
							$zwssgr_body = isset($_POST['zwssgr_admin_notification_email_body']) ? wp_kses_post(wp_unslash($_POST['zwssgr_admin_notification_email_body'])) : '';
				
							// Update the options (only update the subject and body; leave email field empty after submission)
							update_option('zwssgr_admin_notification_emails_subject', $zwssgr_subject);
							update_option('zwssgr_admin_notification_email_body', $zwssgr_body);
				
							// Prepare email
							if (!empty($zwssgr_emails)) {
								$zwssgr_from_email = '';
								if (is_array($this->zwssgr_smtp_opt) && isset($this->zwssgr_smtp_opt['zwssgr_from_email'])) {
									$zwssgr_from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];
								}
								$zwssgr_to = explode(',', $zwssgr_emails); // Assume emails are comma-separated
								$zwssgr_message = $zwssgr_body;
								$zwssgr_headers[] = 'Content-type: text/html; charset=utf-8';
								$zwssgr_headers[] = 'From:' . $zwssgr_from_email;
								// Send the email using wp_mail()
								$zwssgr_mail_sent = wp_mail($zwssgr_to, $zwssgr_subject, $zwssgr_message, $zwssgr_headers);
				
								// Check if email was sent successfully
								if ($zwssgr_mail_sent) {
									add_settings_error('zwssgr_notification_settings', 'settings_updated', 'Emails sent successfully.', 'updated');
								} else {
									add_settings_error('zwssgr_notification_settings', 'settings_error', 'Failed to send email.', 'error');
								}
							} else {
								add_settings_error('zwssgr_notification_settings', 'settings_error', 'No email addresses provided.', 'error');
							}

							// Clear only the email field after submission
							update_option('zwssgr_admin_notification_emails', '');
						}
					}
				}

				if (isset($_POST['zwssgr_advanced_nonce_field'])) {
					$zwssgr_nonce = isset($_POST['zwssgr_advanced_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_advanced_nonce_field'])) : '';
					$zwssgr_value = isset($_POST['zwssgr_sync_reviews']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_sync_reviews'])) : 'daily'; // Default to 'daily' if not set
					if (wp_verify_nonce($zwssgr_nonce, 'zwssgr_advanced_nonce')) {
						// Handle advanced settings form submission
						if (isset($_POST['advance_submit_buttons'])) {
							update_option('zwssgr_sync_reviews', $zwssgr_value);
							add_settings_error('zwssgr_advanced_account_settings', 'settings_updated', 'Advanced settings saved successfully!', 'updated');
						}
					}
				}
			}
		
			// Now render the form and tabs
			$zwssgr_current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'google';
			?>
			<div class="wrap">
				<h1 class="zwssgr-page-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
				<div class="zwssgr-section-wrap">
					<h2 class="nav-tab-wrapper zwssgr-nav-tab-wrapper">
						<a href="?page=zwssgr_settings&tab=google" class="nav-tab <?php echo ($zwssgr_current_tab === 'google') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('Google', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=notifications" class="nav-tab <?php echo ($zwssgr_current_tab === 'notifications') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('SEO & Notifications', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=advanced" class="nav-tab <?php echo ($zwssgr_current_tab === 'advanced') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('Advanced', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=smtp-settings" class="nav-tab <?php echo ($zwssgr_current_tab === 'smtp-settings') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('SMTP Settings', 'smart-showcase-for-google-reviews'); ?>
						</a>
					</h2>
					<?php if ($zwssgr_current_tab === 'google'):

					$zwssgr_disconnect_text = 'Disconnect'; // Default value

					// Safely check for 'tab' and 'settings' in the query string
					if (isset($_GET['tab'], $_GET['settings']) && $_GET['tab'] === 'google' && $_GET['settings'] === 'disconnect-auth') {
						$zwssgr_disconnect_text = 'Disconnecting...';
					}

					// Check if the JWT token is present in the database
					$zwssgr_jwt_token = get_option('zwssgr_jwt_token');
					$zwssgr_gmb_email = get_option('zwssgr_gmb_email');

					if (!empty($zwssgr_jwt_token)) { ?>
						<div class="disconnect-wrapper zwssgr-disconnect-wrapper">
							<a href="<?php echo esc_url(admin_url('admin.php?page=zwssgr_settings&tab=google&settings=disconnect-auth')); ?>" 
							class="button zwssgr-submit-btn zwssgr-disconnect-btn">
								<?php echo esc_attr($zwssgr_disconnect_text); ?>
							</a>
							<div class="zwssgr-th-label">
								<label class="zwssgr-th">
									<?php echo esc_html($zwssgr_gmb_email); ?>
								</label>
							</div>
						</div>
					<?php } else { ?>
						<p class="zwssgr-google-tab-text"><?php echo esc_html__('Please connect to Google!', 'smart-showcase-for-google-reviews'); ?></p>
					<?php } ?>

					<?php elseif ($zwssgr_current_tab === 'notifications'): ?>
						<form id="notification-form" action="" method="post" class="zwssgr-setting-form">
							<?php
							wp_nonce_field('zwssgr_notification_nonce', 'zwssgr_notification_nonce_field');
							// Display WordPress admin notices
							settings_errors('zwssgr_notification_settings');
							settings_fields('zwssgr_notification_settings');
							do_settings_sections('zwssgr_notification_settings');
							?>
							<div class="zwssgr-notification-fields">
								<?php $this->zwssgr_admin_notification_emails_callback(); ?>
								<?php $this->zwssgr_admin_notification_emails_subject_callback(); ?>
								<?php $this->zwssgr_admin_notofication_email_body_callback(); ?>
							</div>
							<!-- Error message container -->
							<span id="email-error" class="zwssgr-email-error"></span>
							<!-- Success message container -->
							<span id="email-success" class="zwssgr-email-success"></span>
							<?php 
							submit_button( 'Send Notification Emails', 'zwssgr-submit-btn zwssgr-notification-submit-btn', 'submit_buttons' );
							?>
						</form>
					<?php elseif ($zwssgr_current_tab === 'advanced'): ?>
						<form action="" method="post" class="zwssgr-setting-form">
							<?php
							wp_nonce_field('zwssgr_advanced_nonce', 'zwssgr_advanced_nonce_field');
							settings_errors('zwssgr_advanced_account_settings');
							settings_fields('zwssgr_advanced_account_settings');
							do_settings_sections('zwssgr_advanced_account_settings');
							submit_button( 'Save Advanced Settings', 'zwssgr-submit-btn', 'advance_submit_buttons' );
							?>
						</form>
						<?php elseif ($zwssgr_current_tab === 'smtp-settings'):
							// SMTP settings
							require_once( ZWSSGR_DIR . '/inc/admin/' . ZWSSGR_PREFIX . '.smtp.settings.template.php' );
						endif; ?> 
				</div>
			</div>
			<?php
		}
		function zwssgr_translate_read_more($zwssgr_language) 
		{
			$zwssgr_translations = [
				'en' => 'Read more',
				'es' => 'Leer más',
				'fr' => 'Lire la suite',
				'de' => 'Mehr lesen',
				'it' => 'Leggi di più',
				'pt' => 'Leia mais',
				'ru' => 'Читать дальше',
				'zh' => '阅读更多',
				'ja' => '続きを読む',
				'hi' => 'और पढ़ें',
				'ar' => 'اقرأ أكثر',
				'ko' => '더 읽기',
				'tr' => 'Daha fazla oku',
				'bn' => 'আরও পড়ুন',
				'ms' => 'Baca lagi',
				'nl' => 'Lees verder',
				'pl' => 'Czytaj więcej',
				'sv' => 'Läs mer',
				'th' => 'อ่านเพิ่มเติม',
			];
			return $zwssgr_translations[$zwssgr_language] ?? 'Read more'; // Fallback to English
		}
		function zwssgr_translate_months($zwssgr_language) {
			$zwssgr_month_translations = [
				'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				'es' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
				'fr' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
				'de' => ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
				'it' => ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'],
				'pt' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
				'ru' => ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'],
				'zh' => ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
				'ja' => ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
				'hi' => ['जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'],
				'ar' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
				'ko' => ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
				'tr' => ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
				'bn' => ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'],
				'ms' => ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'],
				'nl' => ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
				'pl' => ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'],
				'sv' => ['januari', 'februari', 'mars', 'april', 'maj', 'juni', 'juli', 'augusti', 'september', 'oktober', 'november', 'december'],
				'th' => ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
			];
		
			return $zwssgr_month_translations[$zwssgr_language] ?? $zwssgr_month_translations['en']; // Fallback to English
		}

		/**
		 * Action: action_admin_menu
		 * Add admin registration Dashboard page for the plugin
		 * @method zwssgr_dashboard_page
		 */
		function zwssgr_dashboard_callback()
		{	

			$zwssgr_allowed_html = wp_kses_allowed_html( 'post' );

			// Merge your custom allowed tags and attributes
			$zwssgr_allowed_html = array_merge( $zwssgr_allowed_html, array(
				'input' => array( 'type' => true, 'name' => true, 'value' => true, 'id' => true, 'class' => true, 'data-type' => true ),
				'select' => array( 'name' => true, 'id' => true, 'class' => true ),
				'option' => array( 'value' => true ),
				'button' => array( 'class' => true, 'data-filter' => true, 'data-type' => true ),
				'ul' => array( 'class' => true ),
				'li' => array( 'class' => true ),
				'h1' => array( 'class' => true )
			) );

			// Use wp_kses instead of wp_kses_post
			echo '<div class="zwssgr-dashboard" id="zwssgr-dashboard">
				<div class="zwssgr-dashboard-header">'
					. wp_kses($this->zwssgr_dashboard->zwssgr_date_range_filter(), $zwssgr_allowed_html) .
				'</div>'
				. wp_kses($this->zwssgr_dashboard->zwssgr_data_render(), $zwssgr_allowed_html) .
			'</div>';
			
		}
		
		/**
		 * Action: action_admin_menu
		 * Add admin registration Dashboard page for the plugin
		 * @method zwssgr_dashboard_page
		 */

		function zwssgr_widget_configurator_callback() 
		{

			if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
				return;
			}

			$zwssgr_post_id = isset($_GET['zwssgr_widget_id']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_widget_id'])) : '';
			$zwssgr_post_objct = get_post($zwssgr_post_id);
			if (!isset($zwssgr_post_id) || !$zwssgr_post_objct ) {
				wp_die( 'Invalid post ID.' ) ;
			}

			// Get stored widget settings
			$zwssgr_display_option = get_post_meta($zwssgr_post_id, 'display_option', true);
			$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
			$zwssgr_selected_elements = get_post_meta($zwssgr_post_id, 'selected_elements', true);
			$zwssgr_keywords = get_post_meta($zwssgr_post_id, 'keywords', true);
			$zwssgr_date_format = get_post_meta($zwssgr_post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$zwssgr_char_limit = get_post_meta($zwssgr_post_id, 'char_limit', true);
			$zwssgr_language = get_post_meta($zwssgr_post_id, 'language', true);
			$zwssgr_sort_by = get_post_meta($zwssgr_post_id, 'sort_by', true);
			$zwssgr_enable_load_more = get_post_meta($zwssgr_post_id, 'enable_load_more', true)?'checked':'';
			$zwssgr_google_review_toggle = get_post_meta($zwssgr_post_id, 'google_review_toggle', true);
			$zwssgr_bg_color = get_post_meta($zwssgr_post_id, 'bg_color', true);
			$zwssgr_text_color = get_post_meta($zwssgr_post_id, 'text_color', true);
			$zwssgr_bg_color_load = get_post_meta($zwssgr_post_id, 'bg_color_load', true);
			$zwssgr_text_color_load = get_post_meta($zwssgr_post_id, 'text_color_load', true);
			$zwssgr_posts_per_page = get_post_meta($zwssgr_post_id, 'posts_per_page', true);
			if (empty($zwssgr_posts_per_page)) {
				$zwssgr_posts_per_page = 12;
			}

			$zwssgr_selected_elements = is_array($zwssgr_selected_elements) ? $zwssgr_selected_elements : [];
			$zwssgr_selected_display_option = !empty($zwssgr_display_option) ? $zwssgr_display_option : 'all'; 
			$zwssgr_selected_layout_option = !empty($zwssgr_layout_option) ? $zwssgr_layout_option : '';
			$zwssgr_custom_css = get_post_meta($zwssgr_post_id, '_zwssgr_custom_css', true);

			$zwssgr_generated_shortcode = $this->generate_shortcode($zwssgr_post_id);
			$zwssgr_current_tab = get_post_meta($zwssgr_post_id, 'tab-options', true); 
			$zwssgr_current_tab2 = get_post_meta($zwssgr_post_id, 'tab-selected', true); 
			$zwssgr_rating_filter = intval(get_post_meta($zwssgr_post_id, 'rating_filter', true)) ?: 0;
			$zwssgr_enable_sort_by = get_post_meta($zwssgr_post_id, 'enable_sort_by', true);
			$zwssgr_location_new_review_uri = get_post_meta($zwssgr_post_id, 'zwssgr_location_new_review_uri', true);
			$zwssgr_location_name = get_post_meta($zwssgr_post_id, 'zwssgr_location_name', true);
			$zwssgr_location_all_review_uri =  get_post_meta($zwssgr_post_id, 'zwssgr_location_all_review_uri', true);
			$zwssgr_location_thumbnail_url = get_post_meta($zwssgr_post_id, 'zwssgr_location_thumbnail_url', true);

			// Define the mapping from numeric values to words.
			$zwssgr_rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word.
			$zwssgr_rating_filter_word = isset($zwssgr_rating_mapping[$zwssgr_rating_filter]) ? $zwssgr_rating_mapping[$zwssgr_rating_filter] : '';

			$zwssgr_ratings_to_include = array();
			if ($zwssgr_rating_filter_word == 'TWO') {
				$zwssgr_ratings_to_include = array('TWO');
			} elseif ($zwssgr_rating_filter_word == 'THREE') {
				$zwssgr_ratings_to_include = array('THREE');
			} elseif ($zwssgr_rating_filter_word == 'FOUR') {
				$zwssgr_ratings_to_include = array('FOUR');
			} elseif ($zwssgr_rating_filter_word == 'FIVE') {
				$zwssgr_ratings_to_include = array('FIVE');
			} elseif ($zwssgr_rating_filter_word == 'ONE') {
				$zwssgr_ratings_to_include = array('ONE');
			}

			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_location_number =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);
			
			$zwssgr_reviews_args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE,
				'posts_per_page' => 6,
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are met
					array(
						'key'     => 'zwssgr_review_star_rating',
						'value'   => $zwssgr_ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					),
					array(
						'key'     => '_is_hidden',
						'compare' => 'NOT EXISTS',  // Ensure only visible posts
					),
					array(
						'key'     => 'zwssgr_gmb_email',
						'value'   => $zwssgr_gmb_email,
						'compare' => '='
					)
				)
			);
			
			// Add the account filter only if it exists
			if (!empty($zwssgr_account_number)) {
				$zwssgr_reviews_args['meta_query'][] = array(
					'key'     => 'zwssgr_account_number',
					'value'   => (string) $zwssgr_account_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			if (!empty($zwssgr_location_number)) {
				$zwssgr_reviews_args['meta_query'][] = array(
					'key'     => 'zwssgr_location_number',
					'value'   => (string) $zwssgr_location_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}			

			// Add sort_by filters
			switch ($zwssgr_sort_by) {
				case 'newest':
					$zwssgr_reviews_args['orderby'] = 'date';
					$zwssgr_reviews_args['order'] = 'DESC';
					break;

				case 'highest':
					// Sort from FIVE to ONE
					add_filter('posts_orderby', function ($zwssgr_orderby, $zwssgr_query) use ($zwssgr_rating_mapping) {
						global $wpdb;
						if ($zwssgr_query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$zwssgr_custom_order = "'" . implode("','", array_reverse($zwssgr_rating_mapping)) . "'";
							$zwssgr_orderby = "FIELD({$wpdb->postmeta}.meta_value, $zwssgr_custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $zwssgr_orderby;
					}, 10, 2);
					break;

				case 'lowest':
					// Sort from ONE to FIVE
					add_filter('posts_orderby', function ($zwssgr_orderby, $zwssgr_query) use ($zwssgr_rating_mapping) {
						global $wpdb;
						if ($zwssgr_query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$zwssgr_custom_order = "'" . implode("','", $zwssgr_rating_mapping) . "'";
							$zwssgr_orderby = "FIELD({$wpdb->postmeta}.meta_value, $zwssgr_custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $zwssgr_orderby;
					}, 10, 2);
					break;

				default:
					$zwssgr_reviews_args['orderby'] = 'date';
					$zwssgr_reviews_args['order'] = 'DESC';
			}

			$latest_zwssgr_reviews = new WP_Query($zwssgr_reviews_args);
			$zwssgr_post_count = $latest_zwssgr_reviews->found_posts;
			$zwssgr_plugin_dir_path = ZWSSGR_URL;
			$zwssgr_image_url = '';
			$zwssgr_image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png';
			if ($latest_zwssgr_reviews->have_posts()) {
				while($latest_zwssgr_reviews->have_posts()) {
					$latest_zwssgr_reviews->the_post();
			
					$zwssgr_reviewer_name   	  	   = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
					$zwssgr_review_star_rating 	   = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
					$zwssgr_review_comment  	  	   = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
					$zwssgr_review_id		  	   = get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
					$zwssgr_gmb_reviewer_image_path = ZWSSGR_UPLOAD_DIR . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_gmb_reviewer_image_uri  = ZWSSGR_UPLOAD_URL . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_published_date  = get_the_date('F j, Y');
					$zwssgr_months = $this->zwssgr_translate_months($zwssgr_language);

					// Determine if content is trimmed based on character limit
					$zwssgr_is_trimmed = $zwssgr_char_limit > 0 && mb_strlen($zwssgr_review_comment) > $zwssgr_char_limit; // Check if the content length exceeds the character limit
					$zwssgr_trimmed_content = $zwssgr_is_trimmed ? mb_substr($zwssgr_review_comment, 0, $zwssgr_char_limit) . '...' : $zwssgr_review_comment; // Trim the content if necessary


					$zwssgr_formatted_date = '';
					$zwssgr_timestamp = strtotime($zwssgr_published_date); // Calculate the timestamp once for better performance

					if ($zwssgr_date_format === 'DD/MM/YYYY') {
						$zwssgr_formatted_date = gmdate('d/m/Y', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'MM-DD-YYYY') {
						$zwssgr_formatted_date = gmdate('m-d-Y', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'YYYY/MM/DD') {
						$zwssgr_formatted_date = gmdate('Y/m/d', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'full') {
						$zwssgr_day = gmdate('j', $zwssgr_timestamp);
						$zwssgr_month = $zwssgr_months[(int)gmdate('n', $zwssgr_timestamp) - 1];
						$zwssgr_year = gmdate('Y', $zwssgr_timestamp);

						// Construct the full date
						$zwssgr_formatted_date = "$zwssgr_month $zwssgr_day, $zwssgr_year";
					} elseif ($zwssgr_date_format === 'hide') {
						$zwssgr_formatted_date = ''; // No display for "hide"
					}


					// Map textual rating to numeric values
					$zwssgr_rating_map = [
						'ONE'   => 1,
						'TWO'   => 2,
						'THREE' => 3,
						'FOUR'  => 4,
						'FIVE'  => 5,
					];

					// Convert the textual rating to numeric
					$zwssgr_numeric_rating = isset($zwssgr_rating_map[$zwssgr_review_star_rating]) ? $zwssgr_rating_map[$zwssgr_review_star_rating] : 0;

					// Generate stars HTML
					$zwssgr_stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$zwssgr_stars_html .= $i < $zwssgr_numeric_rating 
							? '<span class="zwssgr-star filled">★</span>' 
							: '<span class="zwssgr-star">☆</span>';
					}

					// Format the slider item for each review
					$zwssgr_slider_item1 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($zwssgr_published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' .
								( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ). '
							</div>
						</div>';

					$zwssgr_slider_item2 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-rating-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
									' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>': '') . '</p>' : '' ) . '
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_slider_item3 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item4 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item5 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div>
								<div class="zwssgr-profile">
									'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'	
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								<div class="zwssgr-contnt-wrap">
									' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
									' . (!empty($zwssgr_published_date)
									? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item6 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_list_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';
					
					$zwssgr_list_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								<div class="zwssgr-list-content-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
								</div>
							</div>
						</div>';

					$zwssgr_list_item3= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_list_item4= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
									<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									</div>
									' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
							</div>
						</div>';

					$zwssgr_list_item5= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-list-wrap5">
									<div class="zwssgr-prifile-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-data">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
									<div class="zwssgr-content-wrap">
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
											<div class="zwssgr-google-icon">
												<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_grid_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_grid_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										<div class="zwssgr-date-wrap">
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
											' . (!empty($zwssgr_published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_grid_item3 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-review-detail">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($zwssgr_published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										<div class="zwssgr-rating-wrap">
											<div class="zwssgr-google-icon">
												<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
										</div>
									</div>
									' . ( !empty($zwssgr_trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</div></p>' : '' ) . '
								</div>
							</div>
						</div>';

					$zwssgr_grid_item4 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>									
								' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($zwssgr_published_date)
									? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
							</div>
						</div>';
					
					$zwssgr_grid_item5 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_popup_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_popup_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								<div class="zwssgr-list-content-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
								</div>
							</div>
						</div>';


					// Add the slider item to the slider content array
					$zwssgr_slider_content1[] = $zwssgr_slider_item1;
					$zwssgr_slider_content2[] = $zwssgr_slider_item2;
					$zwssgr_slider_content3[] = $zwssgr_slider_item3;
					$zwssgr_slider_content4[] = $zwssgr_slider_item4;
					$zwssgr_slider_content5[] = $zwssgr_slider_item5;
					$zwssgr_slider_content6[] = $zwssgr_slider_item6;

					$zwssgr_list_content1[] = $zwssgr_list_item1;
					$zwssgr_list_content2[] = $zwssgr_list_item2;
					$zwssgr_list_content3[] = $zwssgr_list_item3;
					$zwssgr_list_content4[] = $zwssgr_list_item4;
					$zwssgr_list_content5[] = $zwssgr_list_item5;

					$zwssgr_grid_content1[] = $zwssgr_grid_item1;
					$zwssgr_grid_content2[] = $zwssgr_grid_item2;
					$zwssgr_grid_content3[] = $zwssgr_grid_item3;
					$zwssgr_grid_content4[] = $zwssgr_grid_item4;
					$zwssgr_grid_content5[] = $zwssgr_grid_item5;

					$zwssgr_popup_content1[] = $zwssgr_popup_item1;
					$zwssgr_popup_content2[] = $zwssgr_popup_item2;

				}
				wp_reset_postdata();
			}

			$zwssgr_slider_content1 = isset($zwssgr_slider_content1) && !empty($zwssgr_slider_content1) ? implode('', (array) $zwssgr_slider_content1) : '';
			$zwssgr_slider_content2 = isset($zwssgr_slider_content2) && !empty($zwssgr_slider_content2) ? implode('', (array) $zwssgr_slider_content2) : '';
			$zwssgr_slider_content3 = isset($zwssgr_slider_content3) && !empty($zwssgr_slider_content3) ? implode('', (array) $zwssgr_slider_content3) : '';
			$zwssgr_slider_content4 = isset($zwssgr_slider_content4) && !empty($zwssgr_slider_content4) ? implode('', (array) $zwssgr_slider_content4) : '';
			$zwssgr_slider_content5 = isset($zwssgr_slider_content5) && !empty($zwssgr_slider_content5) ? implode('', (array) $zwssgr_slider_content5) : '';
			$zwssgr_slider_content6 = isset($zwssgr_slider_content6) && !empty($zwssgr_slider_content6) ? implode('', (array) $zwssgr_slider_content6) : '';

			$zwssgr_list_content1 = isset($zwssgr_list_content1) && !empty($zwssgr_list_content1) ? implode('', (array) $zwssgr_list_content1) : '';
			$zwssgr_list_content2 = isset($zwssgr_list_content2) && !empty($zwssgr_list_content2) ? implode('', (array) $zwssgr_list_content2) : '';
			$zwssgr_list_content3 = isset($zwssgr_list_content3) && !empty($zwssgr_list_content3) ? implode('', (array) $zwssgr_list_content3) : '';
			$zwssgr_list_content4 = isset($zwssgr_list_content4) && !empty($zwssgr_list_content4) ? implode('', (array) $zwssgr_list_content4) : '';
			$zwssgr_list_content5 = isset($zwssgr_list_content5) && !empty($zwssgr_list_content5) ? implode('', (array) $zwssgr_list_content5) : '';

			$zwssgr_grid_content1 = isset($zwssgr_grid_content1) && !empty($zwssgr_grid_content1) ? implode('', (array) $zwssgr_grid_content1) : '';
			$zwssgr_grid_content2 = isset($zwssgr_grid_content2) && !empty($zwssgr_grid_content2) ? implode('', (array) $zwssgr_grid_content2) : '';
			$zwssgr_grid_content3 = isset($zwssgr_grid_content3) && !empty($zwssgr_grid_content3) ? implode('', (array) $zwssgr_grid_content3) : '';
			$zwssgr_grid_content4 = isset($zwssgr_grid_content4) && !empty($zwssgr_grid_content4) ? implode('', (array) $zwssgr_grid_content4) : '';
			$zwssgr_grid_content5 = isset($zwssgr_grid_content5) && !empty($zwssgr_grid_content5) ? implode('', (array) $zwssgr_grid_content5) : '';

			$zwssgr_popup_content1 = isset($zwssgr_popup_content1) && !empty($zwssgr_popup_content1) ? implode('', (array) $zwssgr_popup_content1) : '';
			$zwssgr_popup_content2 = isset($zwssgr_popup_content2) && !empty($zwssgr_popup_content2) ? implode('', (array) $zwssgr_popup_content2) : '';

			$zwssgr_gmb_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_gmb_account_location =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);

			$zwssgr_filter_data = [
				'zwssgr_gmb_account_number'   => $zwssgr_gmb_account_number,
				'zwssgr_gmb_account_location' => $zwssgr_gmb_account_location,
				'zwssgr_range_filter_type'    => '',
				'zwssgr_range_filter_data'    => ''
			];

			$zwssgr_data_render_args = $this->zwssgr_dashboard->zwssgr_data_render_query($zwssgr_filter_data);		
			$zwssgr_reviews_ratings = $this->zwssgr_dashboard->zwssgr_get_reviews_ratings($zwssgr_data_render_args);
			$zwssgr_widthPercentage = $zwssgr_reviews_ratings['ratings'] * 20;

			$zwssgr_final_rating = ' <div class="zwssgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwssgr-final-review-fill" style="width: ' . $zwssgr_widthPercentage . '%;">
					<svg width="100" height="20" viewBox="0 0 100 20" className="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M30.0001 15.5616L23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616Z" fill="#f39c12" />
						<path d="M50.0001 15.5616L43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616Z" fill="#f39c12" />
						<path d="M70.0004 15.5619L63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619Z" fill="#f39c12" />
						<path d="M10.0001 15.5616L3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616Z" fill="#f39c12" />
						<path d="M90.0004 15.5619L83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619Z" fill="#f39c12" />
					</svg>
				</div>
			</div>';
			// Define your options and layouts with corresponding HTML content
			$zwssgr_options = [
				'slider' => [
					'<div class="zwssgr-slider zwssgr-slider1" id="zwssgr-slider1"> 
						<div class="swiper zwssgr-slider-1">
							<div class="swiper-wrapper">
								' . (($zwssgr_post_count > 0) ? $zwssgr_slider_content1  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider2" id="zwssgr-slider2">
						<div class="swiper zwssgr-slider-2">
							<div class="swiper-wrapper">
								' . (($zwssgr_post_count > 0) ? $zwssgr_slider_content2  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'' . ( ($zwssgr_post_count > 0) 
					? '
					<div class="zwssgr-slider zwssgr-slider3" id="zwssgr-slider3">
						<div class="zwssgr-slider-badge">
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
								<div class="zwssgr-badge-item" id="zwssgr-badge1">
									<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
									' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
									<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews ', 'smart-showcase-for-google-reviews').' </b></p>
									<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
								</div>
							</a>
						</div>
						<div class="zwssgr-slider-3-wrap">
							<div class="zwssgr-slider-3 swiper">
								<div class="swiper-wrapper">
									'.$zwssgr_slider_content3.'
								</div>
							</div>
							<div class="swiper-button-next zwssgr-swiper-button-next"></div>
							<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
						</div>
					</div>'
					: '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>'
				) . '
				',
					'<div class="zwssgr-slider zwssgr-slider4" id="zwssgr-slider4">
						<div class="zwssgr-slider-4 swiper">
							<div class="swiper-wrapper">
								' . (($zwssgr_post_count > 0) ? $zwssgr_slider_content4  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
						<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider5" id="zwssgr-slider5">
						<div class="zwssgr-slider-5 swiper">
							<div class="swiper-wrapper">
								' . (($zwssgr_post_count > 0) ? $zwssgr_slider_content5  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
						<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider6" id="zwssgr-slider6">
						<div class="zwssgr-slider-6 swiper">
							<div class="swiper-wrapper">
								' . (($zwssgr_post_count > 0) ? $zwssgr_slider_content6  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
						<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
				],
				'grid' => [
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid1" id="zwssgr-grid1">
						' . (($zwssgr_post_count > 0) ? $zwssgr_grid_content1  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid2" id="zwssgr-grid2">
						' . (($zwssgr_post_count > 0) ? $zwssgr_grid_content2  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid3" id="zwssgr-grid3">
						' . (($zwssgr_post_count > 0) ? $zwssgr_grid_content3  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid4" id="zwssgr-grid4">
						' . (($zwssgr_post_count > 0) ? $zwssgr_grid_content4  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid5" id="zwssgr-grid5">
						' . (($zwssgr_post_count > 0) ? $zwssgr_grid_content5  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>'
				],
				'list' => [
					'<div class="zwssgr-slider zwssgr-list zwssgr-list1" id="zwssgr-list1">
						' . (($zwssgr_post_count > 0) ? $zwssgr_list_content1  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list2" id="zwssgr-list2">
						' . (($zwssgr_post_count > 0) ? $zwssgr_list_content2  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list3" id="zwssgr-list3">
						' . (($zwssgr_post_count > 0) ? $zwssgr_list_content3  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list4" id="zwssgr-list4">
						' . (($zwssgr_post_count > 0) ? $zwssgr_list_content4  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list5" id="zwssgr-list5">
						' . (($zwssgr_post_count > 0) ? $zwssgr_list_content5  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
					</div>'
				],
				'badge' => [
					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge1" id="zwssgr-badge1">
							<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews ', 'smart-showcase-for-google-reviews').' </b></p>
							<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge2" id="zwssgr-badge2">
							<div class="zwssgr-badge-image">
								<img src="' . $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
							</div>
							<div class="zwssgr-badge-info">
								<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
								<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews', 'smart-showcase-for-google-reviews').'</b></p>
							</div>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge3" id="zwssgr-badge3">
							<div class="zwssgr-rating-wrap">
								<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							</div>
							<img src="' . $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge4" id="zwssgr-badge4">
							<div class="zwssgr-badge4-rating">
								<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							</div>
							<div class="zwssgr-badge4-info">
								<h3 class="zwssgr-google">'.esc_html__('Google', 'smart-showcase-for-google-reviews').'</h3>
								<p class="zwssgr-avg-note">'.esc_html__('Average Rating', 'smart-showcase-for-google-reviews').'</p>
								<img src="' . $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
							</div>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge5" id="zwssgr-badge5">
							<div class="zwssgr-badge5-rating">
								<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							</div>
							<div class="zwssgr-badge5-info">
								<h3 class="zwssgr-google">'.esc_html__('Google', 'smart-showcase-for-google-reviews').'</h3>
								<p class="zwssgr-avg-note">'.esc_html__('Average Rating', 'smart-showcase-for-google-reviews').'</p>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							</div>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge6" id="zwssgr-badge6">
							<div class="zwssgr-badge6-rating">
								<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							</div>
							<div class="zwssgr-badge6-info">
								<h3 class="zwssgr-google">'.esc_html__('Google', 'smart-showcase-for-google-reviews').'</h3>
								<p class="zwssgr-avg-note">'.esc_html__('Average Rating', 'smart-showcase-for-google-reviews').'</p>
							</div>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge7" id="zwssgr-badge7">
							<img src="' . $zwssgr_plugin_dir_path . 'assets/images/review-us.png" alt="Review Us">
							<div class="zwssgr-badge7-rating">
								<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							</div>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge8" id="zwssgr-badge8">
							<div class="zwssgr-logo-wrap">
								<img src="' . $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
								<p class="zwssgr-avg-note">'.esc_html__('Google Reviews', 'smart-showcase-for-google-reviews').'</p>
							</div>
							<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews', 'smart-showcase-for-google-reviews').'</b></p>
						</div>
					</a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
						<div class="zwssgr-badge-item zwssgr-badge9" id="zwssgr-badge9">
							<div class="zwssgr-badge-image">
								<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
							</div>
							<div class="zwssgr-badge-info">
								<h3 class="zwssgr-average">' . $zwssgr_location_name .'</h3>
								' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
								<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews', 'smart-showcase-for-google-reviews').'</b></p>
							</div>
						</div>
					</a>',
				],
				'popup' => [
					'<div class="zwssgr-popup-item zwssgr-popup1" id="zwssgr-popup1" data-popup="zwssgrpopup1">
						<div class="zwssgr-profile-logo">
							<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
						</div>
						<div class="zwssgr-profile-info">
							<h3>'.$zwssgr_location_name.'</h3>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-total-review"> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google Reviews', 'smart-showcase-for-google-reviews').'</a>
						</div>
					</div>
					<div id="zwssgrpopup1" class="zwssgr-popup-overlay zwssgrpopup1">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google reviews', 'smart-showcase-for-google-reviews').'</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($zwssgr_post_count > 0) ? $zwssgr_popup_content1  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
								</div>
							</div>
						</div>
					</div>',
					'<div class="zwssgr-popup-item zwssgr-popup2" id="zwssgr-popup2"  data-popup="zwssgrpopup2">
						<div class="zwssgr-title-wrap">
							<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
							<h3>'.esc_html__('Reviews', 'smart-showcase-for-google-reviews').'</h3>
						</div>
						<div class="zwssgr-info-wrap">
							<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" 	class="zwssgr-total-review">(  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews', 'smart-showcase-for-google-reviews').' )</a>
						</div>
					</div>
					<div id="zwssgrpopup2" class="zwssgr-popup-overlay zwssgrpopup2">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google reviews', 'smart-showcase-for-google-reviews').'</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($zwssgr_post_count > 0) ? $zwssgr_popup_content2  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
								</div>
							</div>
						</div>
					</div>'
				]
			];
			?>
			
			<div class="zwssgr-dashboard">
				<h1 class="zwssgr-page-title"><?php echo esc_html__('Widget Configuration', 'smart-showcase-for-google-reviews'); ?></h1>

				<!-- Tab Navigation -->
				<ul class="tab-nav zwssgr-custom-tab">
					<li class="tab-item zwssgr-tab-item active done" data-tab="tab-fetch-data">
						<span class="zwssgr-step"><?php echo esc_html__('1.', 'smart-showcase-for-google-reviews'); ?> </span><?php echo esc_html__('Fetch Data', 'smart-showcase-for-google-reviews'); ?>
					</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item  <?php echo ($zwssgr_layout_option) ? 'done' : ''; ?>" data-tab="tab-options">
						<span class="zwssgr-step"><?php echo esc_html__('2.', 'smart-showcase-for-google-reviews'); ?></span><?php echo esc_html__('Select Display Options', 'smart-showcase-for-google-reviews'); ?>
					</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item <?php echo ($zwssgr_current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-selected">
						<span class="zwssgr-step"><?php echo esc_html__('3.', 'smart-showcase-for-google-reviews'); ?></span><?php echo esc_html__('Selected Option', 'smart-showcase-for-google-reviews'); ?>
					</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item <?php echo ($zwssgr_current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-shortcode">
						<span class="zwssgr-step"><?php echo esc_html__('4.', 'smart-showcase-for-google-reviews'); ?></span><?php echo esc_html__('Generated Shortcode', 'smart-showcase-for-google-reviews'); ?>
					</li>
				</ul>

				<!-- Tab Data Fetch Areas -->
				<div class="tab-content zwssgr-tab-content" id="tab-fetch-data">
					<?php zwssgr_Google_My_Business_Connector::get_instance()->zwssgr_fetch_gmb_data_callback(); ?>
				</div>

				<!-- Tab Content Areas -->
				<div class="tab-content zwssgr-tab-content" id="tab-options">
					<!-- Dynamically Render Radio Buttons -->
					<div class="zwssgr-layout-radio"> 
						<label><input type="radio" name="display_option" class="zwssgr-radio" value="all" checked> <span><?php echo esc_html__('All', 'smart-showcase-for-google-reviews'); ?></span></label>
						<?php foreach ($zwssgr_options as $zwssgr_key => $zwssgr_layouts) : ?>
							<label><input type="radio" name="display_option" class="zwssgr-radio" id="<?php echo esc_attr($zwssgr_key); ?>" value="<?php echo esc_attr($zwssgr_key); ?>"><span for="<?php echo esc_attr($zwssgr_key); ?>"> <?php echo esc_html(ucfirst($zwssgr_key)); ?></span></label>
						<?php endforeach; ?>
					</div>

					<!-- Dynamically Render Layout Options Based on Selected Display Option -->
					<div id="layout-options" class="zwssgr-layout-options">
						<?php
						foreach ($zwssgr_options as $option_type => $zwssgr_layouts) {
							$zwssgr_layout_count = 1;
							if( $option_type == "badge") {
								echo '<div class="zwssgr-badge-wrap">';
							}
							if( $option_type == "popup") {
								echo '<div class="zwssgr-popup-wrap zwssgr-popup-option-item">';
							}
							foreach ($zwssgr_layouts as $zwssgr_layout_content) { 
								$zwssgr_element_id = $option_type . '-' . $zwssgr_layout_count;

								// Only show layouts for the selected display option
								$zwssgr_display_style = ($zwssgr_selected_display_option === $option_type || $zwssgr_selected_display_option === 'all') ? 'block' : 'block';
								$zwssgr_selected_class = ($zwssgr_element_id === $zwssgr_layout_option) ? ' selected' : ''; // Check if this layout is selected
								
								echo '<div id="' . esc_attr($zwssgr_element_id) . '" class="zwssgr-option-item' . esc_attr($zwssgr_selected_class) . '" data-type="' . esc_attr($option_type) . '" style="display: ' . esc_attr($zwssgr_display_style) . ';">';
									echo '<div class="zwssgr-layout-title-wrap">';
										echo '<h3 class="zwssgr-layout-title">Layout: '. esc_html($option_type) .' '.esc_html($zwssgr_layout_count).'</h3>';
										echo '<button class="select-btn zwssgr-select-btn zwssgr-btn" data-option="' . esc_attr($zwssgr_element_id) . '"' . ($zwssgr_element_id === $zwssgr_selected_layout_option ? ' selected' : '') . '>'. esc_html__('Select Option', 'smart-showcase-for-google-reviews').'</button>';
									echo '</div>';
									$zwssgr_allowed_html = wp_kses_allowed_html('post');

									// Add SVG support
									$zwssgr_allowed_html['svg'] = [
										'xmlns' => true,
										'width' => true,
										'height' => true,
										'viewBox' => true,
										'fill' => true,
										'stroke' => true,
										'stroke-width' => true,
										'class' => true,
										'id' => true,
										'style' => true,
									];

									$zwssgr_allowed_html['path'] = [
										'd' => true,
										'fill' => true,
										'class' => true,
									];

									$zwssgr_allowed_html['g'] = [
										'fill' => true,
										'stroke' => true,
										'stroke-width' => true,
										'class' => true,
									];

								echo wp_kses($zwssgr_layout_content, $zwssgr_allowed_html);
								echo '</div>';

								$zwssgr_layout_count++;
							}

							if( $option_type == "popup") {
								echo '</div>';
							}
							if( $option_type == "badge") {
								echo '</div>';
							}
						}
						?>
					</div>
				</div>

				<div class="tab-content zwssgr-tab-content zwssgr-tab-content-display" id="tab-selected">
					<h3><?php echo esc_html__('Selected Option', 'smart-showcase-for-google-reviews'); ?></h3>
					<div id="selected-option-display" class="selected-option-display zwssgr-selected-option-display" data-layout-option="<?php echo esc_attr( $zwssgr_layout_option ); ?>"></div>
					<?php if (!in_array($zwssgr_layout_option, ['badge-1', 'badge-2', 'badge-3', 'badge-4', 'badge-5', 'badge-6', 'badge-7', 'badge-8', 'badge-9'], true)) : ?>
						<div class="zwssgr-toogle-display">
							<a href="<?php echo esc_url($zwssgr_location_new_review_uri); ?>" style="background-color:<?php echo esc_attr($zwssgr_bg_color); ?>; color:<?php echo esc_attr($zwssgr_text_color); ?>;" class="zwssgr-google-toggle" target="_blank">
								<?php echo esc_html__('Review Us On G', 'smart-showcase-for-google-reviews'); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if (!in_array($zwssgr_layout_option, ['badge-1', 'badge-2', 'badge-3', 'badge-4', 'badge-5', 'badge-6', 'badge-7', 'badge-8', 'badge-9'])): ?>
						<div class="zwssgr-widget-settings">
							<h2 class="zwssgr-page-title"><?php echo esc_html__('Widget Settings', 'smart-showcase-for-google-reviews'); ?></h2>
							<div class="zwssgr-widget-wrap">
								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Filter Rating', 'smart-showcase-for-google-reviews'); ?></h3>
									<div class="filter-rating">
										<?php
										for ($i = 1; $i <= 5; $i++) {
											$zwssgr_selected = ($i <= $zwssgr_rating_filter) ? 'selected' : '';  // Check if the current star is selected
											$zwssgr_fillColor = ($i <= $zwssgr_rating_filter) ? '#F08C3C' : '#ccc'; // Color for selected and non-selected stars
											?>
											<span class="zwssgr-setting-star star-filter <?php echo esc_attr($zwssgr_selected); ?>" data-rating="<?php echo esc_attr($i); ?>" title="<?php echo esc_attr($i); ?> Star">
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path class="star" d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="<?php echo esc_attr($zwssgr_fillColor); ?>" />
												</svg>
											</span>
											<?php
										}
										?>
									</div>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Hide Element', 'smart-showcase-for-google-reviews'); ?></h3>
									<ul class="zwssgr-widget-eleemt-list">
										<li>
											<input type="checkbox" id="review-title" class="zwssgr-checkbox" name="review-element" value="review-title" 
											<?php echo in_array('review-title', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-title" class="zwssgr-chechbox-label"><?php echo esc_html__('Reviewer Name', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
										<li>
											<input type="checkbox" id="review-rating" class="zwssgr-checkbox" name="review-element" value="review-rating" 
											<?php echo in_array('review-rating', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-rating" class="zwssgr-chechbox-label"><?php echo esc_html__('Rating', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
										<li>
											<input type="checkbox" id="review-days-ago" class="zwssgr-checkbox" name="review-element" value="review-days-ago" 
											<?php echo in_array('review-days-ago', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-days-ago" class="zwssgr-chechbox-label"><?php echo esc_html__('Date', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
										<li>
											<input type="checkbox" id="review-content" class="zwssgr-checkbox" name="review-element" value="review-content" 
											<?php echo in_array('review-content', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-content" class="zwssgr-chechbox-label"><?php echo esc_html__('Review Content', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
										<li>
											<input type="checkbox" id="review-photo" class="zwssgr-checkbox" name="review-element" value="review-photo" 
											<?php echo in_array('review-photo', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-photo" class="zwssgr-chechbox-label"><?php echo esc_html__('Reviewer Photo', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
										<li>
											<input type="checkbox" id="review-g-icon" class="zwssgr-checkbox" name="review-element" value="review-g-icon" 
											<?php echo in_array('review-g-icon', $zwssgr_selected_elements) ? 'checked' : ''; ?>>
											<label for="review-g-icon" class="zwssgr-chechbox-label"><?php echo esc_html__('G Icon', 'smart-showcase-for-google-reviews'); ?></label>
										</li>
									</ul>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Keywords', 'smart-showcase-for-google-reviews'); ?></h3>
									<input type="text" id="keywords-input" name="keywords-input" class="zwssgr-input-text" placeholder="e.g., keyword1, keyword2, keyword3">
									<p class="zwssgr-description"><?php echo esc_html__('Type keywords separated by commas', 'smart-showcase-for-google-reviews'); ?></p>

									<!-- Hidden input field to store comma-separated keywords for submission -->
									<input type="hidden" id="keywords-input-hidden" name="keywords_input_hidden" value="">

									<!-- Display the list of saved keywords -->
									<?php
									$zwssgr_keywords = get_post_meta($zwssgr_post_id, 'keywords', true);
									if (is_array($zwssgr_keywords) && !empty($zwssgr_keywords)) {
										echo '<div id="keywords-list" class="keywords-list zwssgr-keywords-list">';
											foreach ($zwssgr_keywords as $zwssgr_keyword) {
												echo '<div class="keyword-item zwssgr-keyword-item">' . esc_html($zwssgr_keyword) . '<span class="remove-keyword zwssgr-remove-keyword"> ✖</span></div>';
											}
										echo '</div>';
									} else {
										echo '<div id="keywords-list" class="keywords-list zwssgr-keywords-list"></div>';
									}
									?>

									<div id="error-message" class="error-message zwssgr-keyword-error-message">
										<?php echo esc_html__('You can only enter a maximum of 5 keywords.', 'smart-showcase-for-google-reviews'); ?>
									</div> 
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Review us on Google', 'smart-showcase-for-google-reviews'); ?></h3>
									<label class="switch zwssgr-switch">
										<input type="checkbox" id="toggle-google-review" class="zwssgr-input-check" name="google_review_toggle" <?php echo ($zwssgr_google_review_toggle) ? 'checked' : ''; ?>>
										<span class="slider zwssgr-toggle-slider"></span>
									</label>

									<div id="color-picker-options" style="display: <?php echo ($zwssgr_google_review_toggle) ? 'flex' : 'none'; ?>" class="zwssgr-color-options">
										<div class="zwssgr-color-picker">
											<label for="bg-color-picker" class="zwssgr-chechbox-label"><?php echo esc_html__('Background Color:', 'smart-showcase-for-google-reviews'); ?></label>
											<input type="color" id="bg-color-picker" name="bg_color_picker" value="<?php echo esc_attr($zwssgr_bg_color ? $zwssgr_bg_color : '#3780ff'); ?>">
										</div>
										<div class="zwssgr-color-picker">
											<label for="text-color-picker" class="zwssgr-chechbox-label"><?php echo esc_html__('Text Color:', 'smart-showcase-for-google-reviews'); ?></label>
											<input type="color" id="text-color-picker" name="text_color_picker" value="<?php echo esc_attr($zwssgr_text_color ? $zwssgr_text_color : '#ffffff'); ?>">
										</div>
									</div>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Trim long reviews with a "read more" link', 'smart-showcase-for-google-reviews'); ?></h3>
									<input type="number" class="zwssgr-input-text" id="review-char-limit" name="review-char-limit" min="10" placeholder="Enter character limit" value="<?php echo !empty($zwssgr_char_limit) ? esc_attr($zwssgr_char_limit) : ''; ?>">
									<p id="char-limit-error" class="error-message"></p>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Language', 'smart-showcase-for-google-reviews'); ?></h3>
									<select id="language-select" name="language" class="zwssgr-input-text zwssgr-input-select">
										<option value="en" <?php echo ($zwssgr_language === 'en') ? 'selected' : ''; ?>><?php echo esc_html__('English', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="es" <?php echo ($zwssgr_language === 'es') ? 'selected' : ''; ?>><?php echo esc_html__('Spanish', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="fr" <?php echo ($zwssgr_language === 'fr') ? 'selected' : ''; ?>><?php echo esc_html__('French', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="de" <?php echo ($zwssgr_language === 'de') ? 'selected' : ''; ?>><?php echo esc_html__('German', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="it" <?php echo ($zwssgr_language === 'it') ? 'selected' : ''; ?>><?php echo esc_html__('Italian', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="pt" <?php echo ($zwssgr_language === 'pt') ? 'selected' : ''; ?>><?php echo esc_html__('Portuguese', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="ru" <?php echo ($zwssgr_language === 'ru') ? 'selected' : ''; ?>><?php echo esc_html__('Russian', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="zh" <?php echo ($zwssgr_language === 'zh') ? 'selected' : ''; ?>><?php echo esc_html__('Chinese', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="ja" <?php echo ($zwssgr_language === 'ja') ? 'selected' : ''; ?>><?php echo esc_html__('Japanese', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="hi" <?php echo ($zwssgr_language === 'hi') ? 'selected' : ''; ?>><?php echo esc_html__('Hindi', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="ar" <?php echo ($zwssgr_language === 'ar') ? 'selected' : ''; ?>><?php echo esc_html__('Arabic', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="ko" <?php echo ($zwssgr_language === 'ko') ? 'selected' : ''; ?>><?php echo esc_html__('Korean', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="tr" <?php echo ($zwssgr_language === 'tr') ? 'selected' : ''; ?>><?php echo esc_html__('Turkish', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="bn" <?php echo ($zwssgr_language === 'bn') ? 'selected' : ''; ?>><?php echo esc_html__('Bengali', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="ms" <?php echo ($zwssgr_language === 'ms') ? 'selected' : ''; ?>><?php echo esc_html__('Malay', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="nl" <?php echo ($zwssgr_language === 'nl') ? 'selected' : ''; ?>><?php echo esc_html__('Dutch', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="pl" <?php echo ($zwssgr_language === 'pl') ? 'selected' : ''; ?>><?php echo esc_html__('Polish', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="sv" <?php echo ($zwssgr_language === 'sv') ? 'selected' : ''; ?>><?php echo esc_html__('Swedish', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="th" <?php echo ($zwssgr_language === 'th') ? 'selected' : ''; ?>><?php echo esc_html__('Thai', 'smart-showcase-for-google-reviews'); ?></option>
									</select>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Sort By', 'smart-showcase-for-google-reviews'); ?></h3>
									<select id="sort-by-select" name="sort_by" class="zwssgr-input-text zwssgr-input-select">
										<option value="newest" <?php echo ($zwssgr_sort_by === 'newest') ? 'selected' : ''; ?>><?php echo esc_html__('Newest', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="highest" <?php echo ($zwssgr_sort_by === 'highest') ? 'selected' : ''; ?>><?php echo esc_html__('Highest Rating', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="lowest" <?php echo ($zwssgr_sort_by === 'lowest') ? 'selected' : ''; ?>><?php echo esc_html__('Lowest Rating', 'smart-showcase-for-google-reviews'); ?></option>
									</select>
									<div class="zwssgr-sort-by-checkbox">
											<input type="checkbox" class="zwssgr-checkbox" id="enable-sort-by-filter" name="enable_sort_by" <?php echo ($zwssgr_enable_sort_by ? 'checked' : ''); ?> />
											<label for="enable-sort-by-filter" class="zwssgr-chechbox-label"><?php echo esc_html__('Do you want to show "Sort By" filter on front side?', 'smart-showcase-for-google-reviews'); ?></label>
									</div>
								</div>
		
								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label"><?php echo esc_html__('Date Format', 'smart-showcase-for-google-reviews'); ?></h3>
									<select id="date-format-select" name="date-format" class="zwssgr-input-text zwssgr-input-select">
										<option value="DD/MM/YYYY" <?php echo ($zwssgr_date_format === 'DD/MM/YYYY') ? 'selected' : ''; ?>><?php echo esc_html__('DD/MM/YYYY', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="MM-DD-YYYY" <?php echo ($zwssgr_date_format === 'MM-DD-YYYY') ? 'selected' : ''; ?>><?php echo esc_html__('MM-DD-YYYY', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="YYYY/MM/DD" <?php echo ($zwssgr_date_format === 'YYYY/MM/DD') ? 'selected' : ''; ?>><?php echo esc_html__('YYYY/MM/DD', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="full" <?php echo ($zwssgr_date_format === 'full') ? 'selected' : ''; ?>><?php echo esc_html__('Full Date (e.g., January 1, 2024)', 'smart-showcase-for-google-reviews'); ?></option>
										<option value="hide" <?php echo ($zwssgr_date_format === 'hide') ? 'selected' : ''; ?>><?php echo esc_html__('Hide', 'smart-showcase-for-google-reviews'); ?></option>
									</select>
								</div>
								<?php

									if ($zwssgr_current_tab2 == '' && $zwssgr_enable_load_more == '') {
										$zwssgr_is_checked = 'checked';
									} else if ($zwssgr_current_tab2 == 'tab-selected' && $zwssgr_enable_load_more === true) {
										$zwssgr_is_checked = 'checked';
									} else {
										$zwssgr_enable_background_color = '';
										$zwssgr_is_checked = '';
									}

									
								?>
								<div class="zwssgr-widget-setting">
									<?php 
									$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
									// Check if layout option is not in 'slider-1' to 'slider-6'
									$zwssgr_exclude_slider_options = array('slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6');
									if (!in_array($zwssgr_layout_option, $zwssgr_exclude_slider_options)) :
									?>
										<div class="zwssgr-load-more-wrapper">
											<h3 class="zwssgr-label"><?php echo esc_html__('Load More', 'smart-showcase-for-google-reviews'); ?></h3>
											<label class="switch zwssgr-switch">
												<input type="checkbox" class="zwssgr-input-check" id="enable-load-more" name="enable_load_more" <?php echo ($zwssgr_enable_load_more ? 'checked' : ''); echo esc_attr($zwssgr_is_checked);?> />
												<span class="slider zwssgr-toggle-slider"></span>
											</label>
											<div id="zwssgr-load-color-picker-options" style="display: <?php echo ($zwssgr_enable_load_more) ? 'flex' : 'none'; ?>" class="zwssgr-color-options_load">
												<div class="zwssgr-color-picker-load">
													<label for="bg-color-picker_load" class="zwssgr-chechbox-label"><?php echo esc_html__('Background Color:', 'smart-showcase-for-google-reviews'); ?></label>
													<input type="color" id="bg-color-picker_load" name="bg_color_picker_load" value="<?php echo esc_attr($zwssgr_bg_color_load ? $zwssgr_bg_color_load : '#000000'); ?>">
												</div>
												<div class="zwssgr-color-picker-load">
													<label for="text-color-picker_load" class="zwssgr-chechbox-label"><?php echo esc_html__('Text Color:', 'smart-showcase-for-google-reviews'); ?></label>
													<input type="color" id="text-color-picker_load" name="text_color_picker_load" value="<?php echo esc_attr($zwssgr_text_color_load ? $zwssgr_text_color_load : '#ffffff'); ?>">
												</div>
											</div>
										</div>
									<?php endif; ?>

									<div id="load-more-settings">
										<h3 class="zwssgr-label"><?php echo esc_html__('Reviews Per Page for List, Grid, and Popup:', 'smart-showcase-for-google-reviews'); ?></h3>
										<div class="zwssgr-tooltip">
											<input type="number" id="posts-per-page" name="posts_per_page" class="zwssgr-input-text" value="<?php echo esc_attr($zwssgr_posts_per_page); ?>" min="12" max="100" step="1" onchange="this.value = Math.max(12, Math.min(100, this.value));">
											<span class="zwssgr-tooltip-container">
												<div class="zwssgr-wrapper">
													<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 30 30">
													<path d="M15,3C8.373,3,3,8.373,3,15c0,6.627,5.373,12,12,12s12-5.373,12-12C27,8.373,21.627,3,15,3z M16,21h-2v-7h2V21z M15,11.5 c-0.828,0-1.5-0.672-1.5-1.5s0.672-1.5,1.5-1.5s1.5,0.672,1.5,1.5S15.828,11.5,15,11.5z"></path></svg>
													<span class="zwssgr-tooltip-text"><?php echo esc_html__('We recommend a maximum of 100 reviews for the best experience.', 'smart-showcase-for-google-reviews'); ?></span>
												</div>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				
					<div class="zwssgr-widget-settings">
						<h2 class="zwssgr-page-title"><?php echo esc_html__('Custom CSS Support', 'smart-showcase-for-google-reviews'); ?></h2>
						<textarea class="zwssgr-textarea" rows="5" cols="40" placeholder="Enter your custom CSS here"><?php echo esc_textarea($zwssgr_custom_css); ?></textarea>
					</div>
					<button id="save-get-code-btn" class="zwssgr-btn" data-zwssgr-btn='zwssgr-btn'>
						<?php echo !empty($zwssgr_current_tab2) ? esc_html__('Update', 'smart-showcase-for-google-reviews') : esc_html__('Save & Get Code', 'smart-showcase-for-google-reviews');?>
					</button>
				</div>

				<div class="tab-content zwssgr-tab-content zwssgr-tab-content-display" id="tab-shortcode">
					<h3><?php echo esc_html__('Generated Shortcode', 'smart-showcase-for-google-reviews'); ?></h3>
					<div id="generated-shortcode-display" class="generated-shortcode-display zwssgr-generated-shortcode-display">
						<div class="zwssgr-shortcode">
							<input type="text" class="zwssgr-input-text zwssgr-shortcode-input" value="<?php echo esc_attr($zwssgr_generated_shortcode); ?>" readonly id="shortcode-<?php echo esc_attr($zwssgr_post_id); ?>">
							<span class="dashicons dashicons-admin-page zwssgr-copy-shortcode-icon" data-target="shortcode-<?php echo esc_attr($zwssgr_post_id); ?>" title="<?php echo esc_attr__('Copy Shortcode', 'smart-showcase-for-google-reviews'); ?>"></span>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		// Handle AJAX Request to Save Dashboard Data
		function zwssgr_save_widget_data() 
		{
			// Check security nonce
			check_ajax_referer('my_widget_nonce', 'security');

			// Check security nonce
			if (!check_ajax_referer('my_widget_nonce', 'security', false)) {
				$this->zwssgr_debug_function('Nonce verification failed.');
				wp_send_json_error(esc_html__('Nonce verification failed.', 'smart-showcase-for-google-reviews'));
				return;
			}
			
			$this->zwssgr_debug_function('Nonce verified successfully.');

			// Get and sanitize post ID
			$zwssgr_post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			if (!$zwssgr_post_id) {
				$this->zwssgr_debug_function('Invalid post ID');
				wp_send_json_error(esc_html__('Invalid post ID.', 'smart-showcase-for-google-reviews'));
				return;
			}
			$this->zwssgr_debug_function('Post ID: ' . $zwssgr_post_id);

			// Check if the post exists
			if (get_post_status($zwssgr_post_id) === false) {
				$this->zwssgr_debug_function('Post does not exist: ' . $zwssgr_post_id);
				wp_send_json_error(esc_html__('Post does not exist.', 'smart-showcase-for-google-reviews'));
				return;
			}
			$this->zwssgr_debug_function('Post exists, ID: ' . $zwssgr_post_id);

			// Ensure user has permission to edit the post
			if (!current_user_can('edit_post', $zwssgr_post_id)) {
				$this->zwssgr_debug_function('User does not have permission to edit post: ' . $zwssgr_post_id);
				wp_send_json_error(esc_html__('You do not have permission to edit this post.', 'smart-showcase-for-google-reviews'));
				return;
			}
			
			$zwssgr_setting_tb = ( isset( $_POST['settings'] ) && ! empty( $_POST['settings'] ) ) ? sanitize_text_field( wp_unslash( $_POST['settings'] ) ) : '';

			if(  $zwssgr_setting_tb == 'tab-options' ){
			$zwssgr_display_option = isset( $_POST['display_option'] ) ? sanitize_text_field( wp_unslash( $_POST['display_option'] ) ) : get_post_meta( $zwssgr_post_id, 'display_option', true );
			update_post_meta($zwssgr_post_id, 'display_option', $zwssgr_display_option);	

			$zwssgr_layout_option = isset( $_POST['layout_option'] ) ? sanitize_text_field( wp_unslash( $_POST['layout_option'] ) ) : get_post_meta( $zwssgr_post_id, 'layout_option', true );
			update_post_meta($zwssgr_post_id, 'layout_option', $zwssgr_layout_option);
			
			$zwssgr_current_tab = isset( $_POST['current_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['current_tab'] ) ) : '';
			update_post_meta($zwssgr_post_id, 'tab-options', $zwssgr_current_tab); // Save the active tab state

			}

			else if(  $zwssgr_setting_tb == 'tab-selected' ){

				$zwssgr_selected_elements = isset($_POST['selected_elements']) ? json_decode(sanitize_text_field(wp_unslash($_POST['selected_elements'])), true) : [];
				if (!is_array($zwssgr_selected_elements)) {
					$zwssgr_selected_elements = [];
				}
				$zwssgr_selected_elements = array_map('sanitize_text_field', $zwssgr_selected_elements);

				$zwssgr_keywords = isset($_POST['keywords']) ? json_decode(sanitize_text_field(wp_unslash($_POST['keywords'])), true)  : [];
				if (!is_array($zwssgr_keywords)) {
					$zwssgr_keywords = [];
				}

				$zwssgr_keywords = array_map('sanitize_text_field', $zwssgr_keywords);
				$zwssgr_date_format = isset( $_POST['date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['date_format'] ) ) : '';
				$zwssgr_char_limit = isset($_POST['char_limit']) ? intval(wp_unslash($_POST['char_limit'])) : '';
				$zwssgr_language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
				$zwssgr_sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
				$zwssgr_enable_load_more = isset( $_POST['enable_load_more'] ) ? intval( wp_unslash( $_POST['enable_load_more'] ) ) : 0;
				$zwssgr_google_review_toggle = isset( $_POST['google_review_toggle'] ) ? intval( wp_unslash( $_POST['google_review_toggle'] ) ) : 0;
				$zwssgr_bg_color = isset( $_POST['bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['bg_color'] ) ) : '';
				$zwssgr_text_color = isset( $_POST['text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color'] ) ) : '';
				$zwssgr_bg_color_load = isset( $_POST['bg_color_load'] ) ? sanitize_hex_color( wp_unslash( $_POST['bg_color_load'] ) ) : '';
				$zwssgr_text_color_load = isset( $_POST['text_color_load'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color_load'] ) ) : '';
				$zwssgr_posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( wp_unslash( $_POST['posts_per_page'] ) ) : 12; // Default to 12
				$zwssgr_rating_filter = isset( $_POST['rating_filter'] ) ? intval( wp_unslash( $_POST['rating_filter'] ) ) : 0;
				$zwssgr_custom_css = isset( $_POST['custom_css'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_css'] ) ) : '';
				$zwssgr_current_tab2 = sanitize_text_field( wp_unslash( $_POST['settings'] ) ); // The active tab
				$zwssgr_enable_sort_by = isset( $_POST['enable_sort_by'] ) ? intval( wp_unslash( $_POST['enable_sort_by'] ) ) : 0;

				update_post_meta($zwssgr_post_id, 'tab-selected', $zwssgr_current_tab2); // Save the active tab state
				update_post_meta($zwssgr_post_id, 'selected_elements', $zwssgr_selected_elements);
				update_post_meta($zwssgr_post_id, 'rating_filter', $zwssgr_rating_filter);
				update_post_meta($zwssgr_post_id, 'keywords', $zwssgr_keywords);
				update_post_meta($zwssgr_post_id, 'date_format', $zwssgr_date_format);
				update_post_meta($zwssgr_post_id, 'char_limit', $zwssgr_char_limit);
				update_post_meta($zwssgr_post_id, 'language', $zwssgr_language);
				update_post_meta($zwssgr_post_id, 'sort_by', $zwssgr_sort_by);
				update_post_meta($zwssgr_post_id, 'enable_load_more', $zwssgr_enable_load_more);
				update_post_meta($zwssgr_post_id, 'google_review_toggle', $zwssgr_google_review_toggle);
				update_post_meta($zwssgr_post_id, 'bg_color', $zwssgr_bg_color);
				update_post_meta($zwssgr_post_id, 'text_color', $zwssgr_text_color);
				update_post_meta($zwssgr_post_id, 'bg_color_load', $zwssgr_bg_color_load);
				update_post_meta($zwssgr_post_id, 'text_color_load', $zwssgr_text_color_load);
				update_post_meta($zwssgr_post_id, 'posts_per_page', $zwssgr_posts_per_page);
				update_post_meta($zwssgr_post_id, '_zwssgr_custom_css', $zwssgr_custom_css);
				update_post_meta($zwssgr_post_id, 'enable_sort_by', $zwssgr_enable_sort_by);
			}
			// Respond with success message
			wp_send_json_success('Settings updated successfully.' . $zwssgr_setting_tb );
		}

		function generate_shortcode($zwssgr_post_id) 
		{
			// Build the shortcode with attributes
			$zwssgr_shortcode = '[zwssgr_widget post-id="' . esc_attr($zwssgr_post_id) . '"]';
			update_post_meta($zwssgr_post_id, '_generated_shortcode_new', $zwssgr_shortcode);
			return $zwssgr_shortcode;	
		}

		function zwssgr_filter_reviews_ajax_handler() {

			// Get post ID
			$zwssgr_post_id = isset($_POST['zwssgr_widget_id']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_widget_id'])) : '';

			// Validate post
			$zwssgr_post_objct = get_post($zwssgr_post_id);
			if (empty($zwssgr_post_id) || !$zwssgr_post_objct) {
				wp_die('Invalid post ID.');
			}

			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'zwssgr_filter_reviews_nonce')) {
				die(esc_html__('Permission Denied', 'smart-showcase-for-google-reviews'));
			}

			$rating_filter = isset($_POST['rating_filter']) ? json_decode(sanitize_text_field(wp_unslash($_POST['rating_filter'])), true) 	: [];

			// Validate if the decoded value is an array
			if (!is_array($rating_filter)) {
				die('Invalid filter value');
			}
			
			// Ensure all values are integers
			$zwssgr_rating_filter = array_map('intval', $rating_filter);
			$zwssgr_sort_by = isset($_POST['sort_by']) ? sanitize_text_field(wp_unslash($_POST['sort_by'])) : 'newest';
			$zwssgr_date_format = get_post_meta($zwssgr_post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$zwssgr_language = get_post_meta($zwssgr_post_id, 'language', true);
			$zwssgr_char_limit = get_post_meta($zwssgr_post_id, 'char_limit', true); // Retrieve character limit meta value
			$zwssgr_location_all_review_uri =  get_post_meta($zwssgr_post_id, 'zwssgr_location_all_review_uri', true);
			$zwssgr_plugin_dir_path = ZWSSGR_URL;
			$zwssgr_location_thumbnail_url = get_post_meta($zwssgr_post_id, 'zwssgr_location_thumbnail_url', true);
			$zwssgr_image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png';

			$zwssgr_rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Create an array of string values based on the selected numeric ratings
			$zwssgr_rating_strings = array();
			foreach ($zwssgr_rating_filter as $filter) {
				if (isset($zwssgr_rating_mapping[$filter])) {
					$zwssgr_rating_strings[] = $zwssgr_rating_mapping[$filter];
				}
			}
		
			if (empty($zwssgr_rating_strings)) {
				echo 'Invalid rating';
				die();
			}
		
			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_account_location =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);
			$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);


			// Query reviews with the selected string-based filters
			$zwssgr_args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE, // Replace with your custom post type
				'posts_per_page' => 6,
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are met
					array(
						'key'     => 'zwssgr_review_star_rating',
						'value'   => $zwssgr_rating_strings,
						'compare' => 'IN',
						'type'    => 'CHAR'
					),
					array(
						'key'     => '_is_hidden',
						'compare' => 'NOT EXISTS',  // Ensure only visible posts
					),
					array(
						'key'     => 'zwssgr_gmb_email',
						'value'   => $zwssgr_gmb_email,
						'compare' => '='
					)
				),
				'orderby'         => 'date', 
				'order'           => 'DESC'
			);
			
			// Add the account filter only if it exists
			if (!empty($zwssgr_account_number)) {
				$zwssgr_args['meta_query'][] = array(
					'key'     => 'zwssgr_account_number',
					'value'   => (string) $zwssgr_account_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			if (!empty($zwssgr_account_location)) {
				$zwssgr_args['meta_query'][] = array(
					'key'     => 'zwssgr_location_number',
					'value'   => (string) $zwssgr_account_location,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}			
			// Add sorting logic
			switch ($zwssgr_sort_by) {
				case 'newest':
					$zwssgr_args['orderby'] = 'date';
					$zwssgr_args['order'] = 'DESC';
					break;

				case 'highest':
					// Sort from FIVE to ONE
					add_filter('posts_orderby', function ($zwssgr_orderby, $zwssgr_query) use ($zwssgr_rating_mapping) {
						global $wpdb;
						if ($zwssgr_query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$zwssgr_custom_order = "'" . implode("','", array_reverse($zwssgr_rating_mapping)) . "'";
							$zwssgr_orderby = "FIELD({$wpdb->postmeta}.meta_value, $zwssgr_custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $zwssgr_orderby;
					}, 10, 2);
					break;

				case 'lowest':
					// Sort from ONE to FIVE
					add_filter('posts_orderby', function ($zwssgr_orderby, $zwssgr_query) use ($zwssgr_rating_mapping) {
						global $wpdb;
						if ($zwssgr_query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$zwssgr_custom_order = "'" . implode("','", $zwssgr_rating_mapping) . "'";
							$zwssgr_orderby = "FIELD({$wpdb->postmeta}.meta_value, $zwssgr_custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $zwssgr_orderby;
					}, 10, 2);
					break;

				default:
					$zwssgr_args['orderby'] = 'date';
					$zwssgr_args['order'] = 'DESC';
			}
		
			$zwssgr_reviews_query = new WP_Query($zwssgr_args);
			$zwssgr_post_count = $zwssgr_reviews_query->found_posts;
			$zwssgr_reviews_html ='';    
			$zwssgr_location_name = get_post_meta($zwssgr_post_id, 'zwssgr_location_name', true);

			
			if ($zwssgr_reviews_query->have_posts()) {
				while ($zwssgr_reviews_query->have_posts()) {
					$zwssgr_reviews_query->the_post();
					$zwssgr_reviewer_name = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
					$zwssgr_review_content = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
					$zwssgr_review_star_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
					$zwssgr_review_id= get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
					$zwssgr_gmb_reviewer_image_path = ZWSSGR_UPLOAD_DIR . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_gmb_reviewer_image_uri  = ZWSSGR_UPLOAD_URL . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_published_date = get_the_date('F j, Y');
					$zwssgr_months = $this->zwssgr_translate_months($zwssgr_language);
					// Determine if content is trimmed based on character limit
					$zwssgr_is_trimmed = $zwssgr_char_limit > 0 && mb_strlen($zwssgr_review_content) > $zwssgr_char_limit; // Check if the content length exceeds the character limit
					$zwssgr_trimmed_content = $zwssgr_is_trimmed ? mb_substr($zwssgr_review_content, 0, $zwssgr_char_limit) . '...' : $zwssgr_review_content; // Trim the content if necessary

					$zwssgr_formatted_date = '';
					$zwssgr_timestamp = strtotime($zwssgr_published_date); // Calculate the timestamp once for better performance

					if ($zwssgr_date_format === 'DD/MM/YYYY') {
						$zwssgr_formatted_date = gmdate('d/m/Y', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'MM-DD-YYYY') {
						$zwssgr_formatted_date = gmdate('m-d-Y', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'YYYY/MM/DD') {
						$zwssgr_formatted_date = gmdate('Y/m/d', $zwssgr_timestamp);
					} elseif ($zwssgr_date_format === 'full') {
						$zwssgr_day = gmdate('j', $zwssgr_timestamp);
						$zwssgr_month = $zwssgr_months[(int)gmdate('n', $zwssgr_timestamp) - 1];
						$zwssgr_year = gmdate('Y', $zwssgr_timestamp);

						// Construct the full date
						$zwssgr_formatted_date = "$zwssgr_month $zwssgr_day, $zwssgr_year";
					} elseif ($zwssgr_date_format === 'hide') {
						$zwssgr_formatted_date = ''; // No display for "hide"
					}

					// Map textual rating to numeric values
					$zwssgr_rating_map = [
						'ONE'   => 1,
						'TWO'   => 2,
						'THREE' => 3,
						'FOUR'  => 4,
						'FIVE'  => 5,
					];

					// Convert the textual rating to numeric
					$zwssgr_numeric_rating = isset($zwssgr_rating_map[$zwssgr_review_star_rating]) ? $zwssgr_rating_map[$zwssgr_review_star_rating] : 0;

					// Generate stars HTML
					$zwssgr_stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$zwssgr_stars_html .= $i < $zwssgr_numeric_rating 
							? '<span class="zwssgr-star filled">★</span>' 
							: '<span class="zwssgr-star">☆</span>';
					}

					// Format the slider item for each review
					$zwssgr_slider_item1 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_slider_item2 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-rating-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
									' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_slider_item3 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item4 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item5 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div>
								<div class="zwssgr-profile">
									'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'	
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								<div class="zwssgr-contnt-wrap">
									' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
									' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
							</div>
						</div>';

					$zwssgr_slider_item6 = '
						<div class="zwssgr-slide-item swiper-slide">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_list_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';
					
					$zwssgr_list_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								<div class="zwssgr-list-content-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
									' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
								</div>
							</div>
						</div>';

					$zwssgr_list_item3= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
								<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_list_item4= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									</div>
									' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
							</div>
						</div>';

					$zwssgr_list_item5= '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-list-wrap5">
									<div class="zwssgr-prifile-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-data">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
									<div class="zwssgr-content-wrap">
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
											<div class="zwssgr-google-icon">
												<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>': '') . '</p>' : '' ) . '	
									</div>
								</div>
							</div>
						</div>';

					$zwssgr_grid_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_grid_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										<div class="zwssgr-date-wrap">
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
											' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_grid_item3 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-review-detail">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
										<div class="zwssgr-rating-wrap">
											<div class="zwssgr-google-icon">
												<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
											' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
										</div>
									</div>
									' . ( !empty($zwssgr_trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</div></p>' : '' ) . '
								</div>
							</div>
						</div>';

					$zwssgr_grid_item4 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-profile">
									'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>									
								' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($zwssgr_published_date)
									? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
								' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '	
							</div>
						</div>';
					
					$zwssgr_grid_item5 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-grid-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_popup_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
								</div>
								' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
							</div>
						</div>';

					$zwssgr_popup_item2 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
										' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								<div class="zwssgr-list-content-wrap">
									' . (!empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') . '
									' . ( !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
								</div>
							</div>
						</div>';

					// Add the slider item to the slider content array
					$zwssgr_slider_content1[] = $zwssgr_slider_item1;
					$zwssgr_slider_content2[] = $zwssgr_slider_item2;
					$zwssgr_slider_content3[] = $zwssgr_slider_item3;
					$zwssgr_slider_content4[] = $zwssgr_slider_item4;
					$zwssgr_slider_content5[] = $zwssgr_slider_item5;
					$zwssgr_slider_content6[] = $zwssgr_slider_item6;

					$zwssgr_list_content1[] = $zwssgr_list_item1;
					$zwssgr_list_content2[] = $zwssgr_list_item2;
					$zwssgr_list_content3[] = $zwssgr_list_item3;
					$zwssgr_list_content4[] = $zwssgr_list_item4;
					$zwssgr_list_content5[] = $zwssgr_list_item5;

					$zwssgr_grid_content1[] = $zwssgr_grid_item1;
					$zwssgr_grid_content2[] = $zwssgr_grid_item2;
					$zwssgr_grid_content3[] = $zwssgr_grid_item3;
					$zwssgr_grid_content4[] = $zwssgr_grid_item4;
					$zwssgr_grid_content5[] = $zwssgr_grid_item5;

					$zwssgr_popup_content1[] = $zwssgr_popup_item1;
					$zwssgr_popup_content2[] = $zwssgr_popup_item2;

				}
				wp_reset_postdata();
			} 
			else {
				if ($zwssgr_layout_option != "popup-1" && $zwssgr_layout_option != "popup-2") {
					echo '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>';
				}				
			}

			$zwssgr_slider_content1 = isset($zwssgr_slider_content1) && !empty($zwssgr_slider_content1) ? implode('', (array) $zwssgr_slider_content1) : '';
			$zwssgr_slider_content2 = isset($zwssgr_slider_content2) && !empty($zwssgr_slider_content2) ? implode('', (array) $zwssgr_slider_content2) : '';
			$zwssgr_slider_content3 = isset($zwssgr_slider_content3) && !empty($zwssgr_slider_content3) ? implode('', (array) $zwssgr_slider_content3) : '';
			$zwssgr_slider_content4 = isset($zwssgr_slider_content4) && !empty($zwssgr_slider_content4) ? implode('', (array) $zwssgr_slider_content4) : '';
			$zwssgr_slider_content5 = isset($zwssgr_slider_content5) && !empty($zwssgr_slider_content5) ? implode('', (array) $zwssgr_slider_content5) : '';
			$zwssgr_slider_content6 = isset($zwssgr_slider_content6) && !empty($zwssgr_slider_content6) ? implode('', (array) $zwssgr_slider_content6) : '';

			$zwssgr_list_content1 = isset($zwssgr_list_content1) && !empty($zwssgr_list_content1) ? implode('', (array) $zwssgr_list_content1) : '';
			$zwssgr_list_content2 = isset($zwssgr_list_content2) && !empty($zwssgr_list_content2) ? implode('', (array) $zwssgr_list_content2) : '';
			$zwssgr_list_content3 = isset($zwssgr_list_content3) && !empty($zwssgr_list_content3) ? implode('', (array) $zwssgr_list_content3) : '';
			$zwssgr_list_content4 = isset($zwssgr_list_content4) && !empty($zwssgr_list_content4) ? implode('', (array) $zwssgr_list_content4) : '';
			$zwssgr_list_content5 = isset($zwssgr_list_content5) && !empty($zwssgr_list_content5) ? implode('', (array) $zwssgr_list_content5) : '';

			$zwssgr_grid_content1 = isset($zwssgr_grid_content1) && !empty($zwssgr_grid_content1) ? implode('', (array) $zwssgr_grid_content1) : '';
			$zwssgr_grid_content2 = isset($zwssgr_grid_content2) && !empty($zwssgr_grid_content2) ? implode('', (array) $zwssgr_grid_content2) : '';
			$zwssgr_grid_content3 = isset($zwssgr_grid_content3) && !empty($zwssgr_grid_content3) ? implode('', (array) $zwssgr_grid_content3) : '';
			$zwssgr_grid_content4 = isset($zwssgr_grid_content4) && !empty($zwssgr_grid_content4) ? implode('', (array) $zwssgr_grid_content4) : '';
			$zwssgr_grid_content5 = isset($zwssgr_grid_content5) && !empty($zwssgr_grid_content5) ? implode('', (array) $zwssgr_grid_content5) : '';

			$zwssgr_popup_content1 = isset($zwssgr_popup_content1) && !empty($zwssgr_popup_content1) ? implode('', (array) $zwssgr_popup_content1) : '';
			$zwssgr_popup_content2 = isset($zwssgr_popup_content2) && !empty($zwssgr_popup_content2) ? implode('', (array) $zwssgr_popup_content2) : '';


			$zwssgr_gmb_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_gmb_account_location =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);

			$zwssgr_filter_data = [
				'zwssgr_gmb_account_number'   => $zwssgr_gmb_account_number,
				'zwssgr_gmb_account_location' => $zwssgr_gmb_account_location,
				'zwssgr_range_filter_type'    => '',
				'zwssgr_range_filter_data'    => ''
			];

			$zwssgr_data_render_args = $this->zwssgr_dashboard->zwssgr_data_render_query($zwssgr_filter_data);		
			$zwssgr_reviews_ratings = $this->zwssgr_dashboard->zwssgr_get_reviews_ratings($zwssgr_data_render_args);
			$zwssgr_widthPercentage = $zwssgr_reviews_ratings['ratings'] * 20;

			$zwssgr_final_rating = ' <div class="zwssgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwssgr-final-review-fill" style="width: ' . $zwssgr_widthPercentage . '%;">
					<svg width="100" height="20" viewBox="0 0 100 20" className="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M30.0001 15.5616L23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616Z" fill="#f39c12" />
						<path d="M50.0001 15.5616L43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616Z" fill="#f39c12" />
						<path d="M70.0004 15.5619L63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619Z" fill="#f39c12" />
						<path d="M10.0001 15.5616L3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616Z" fill="#f39c12" />
						<path d="M90.0004 15.5619L83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619Z" fill="#f39c12" />
					</svg>
				</div>
			</div>';


			$zwssgr_filter_layout = [
				'slider' => [
					'<div class="zwssgr-slider zwssgr-slider1" id="zwssgr-slider1">
						<div class="zwssgr-slider-1 swiper">
							<div class="swiper-wrapper">
								' . $zwssgr_slider_content1 . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider2" id="zwssgr-slider2">
						<div class="zwssgr-slider-2 swiper">
							<div class="swiper-wrapper">
								' . $zwssgr_slider_content2 . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'' . ( $zwssgr_post_count > 0 ? '
					<div class="zwssgr-slider zwssgr-slider3" id="zwssgr-slider3">
						<div class="zwssgr-slider-badge">
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
								<div class="zwssgr-badge-item" id="zwssgr-badge1">
									<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
									' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
									<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews ', 'smart-showcase-for-google-reviews').' </b></p>
									<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
								</div>
							</a>
						</div>
						<div class="zwssgr-slider-3-wrap">
							<div class="zwssgr-slider-3 swiper">
								<div class="swiper-wrapper">
									' . $zwssgr_slider_content3 . '
								</div>
							</div>
							<div class="swiper-button-next zwssgr-swiper-button-next"></div>
							<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
						</div>
					</div>'
					: '') . '',
					'<div class="zwssgr-slider zwssgr-slider4" id="zwssgr-slider4">
						<div class="zwssgr-slider-4 swiper">
							<div class="swiper-wrapper">
								' . $zwssgr_slider_content4 . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider5" id="zwssgr-slider5">
						<div class="zwssgr-slider-5 swiper">
							<div class="swiper-wrapper">
								' . $zwssgr_slider_content5 . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider6" id="zwssgr-slider6">
						<div class="zwssgr-slider-6 swiper">
							<div class="swiper-wrapper">
								' . $zwssgr_slider_content6 . '
							</div>
						</div>
						<div class="swiper-button-next zwssgr-swiper-button-next"></div>
    					<div class="swiper-button-prev zwssgr-swiper-button-prev"></div>
					</div>',
				],
				'grid' => [
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid1" id="zwssgr-grid1">
						' . $zwssgr_grid_content1 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid2" id="zwssgr-grid2">
						' . $zwssgr_grid_content2 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid3" id="zwssgr-grid3">
						' . $zwssgr_grid_content3 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid4" id="zwssgr-grid4">
						' . $zwssgr_grid_content4 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid5" id="zwssgr-grid5">
						' . $zwssgr_grid_content5 . '
					</div>'
				],
				'list' => [
					'<div class="zwssgr-slider zwssgr-list zwssgr-list1" id="zwssgr-list1">
						' . $zwssgr_list_content1 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list2" id="zwssgr-list2">
						' . $zwssgr_list_content2 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list3" id="zwssgr-list3">
						' . $zwssgr_list_content3 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list4" id="zwssgr-list4">
						' . $zwssgr_list_content4 . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list5" id="zwssgr-list5">
						' . $zwssgr_list_content5 . '
					</div>'
				],
				'popup' => [
					'<div class="zwssgr-popup-item zwssgr-popup1" id="zwssgr-popup1" data-popup="zwssgrpopup1">
						<div class="zwssgr-profile-logo">
							<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
						</div>
						<div class="zwssgr-profile-info">
							<h3>'.$zwssgr_location_name.'</h3>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-total-review"> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google Reviews', 'smart-showcase-for-google-reviews').'</a>
						</div>
					</div>
					<div id="zwssgrpopup1" class="zwssgr-popup-overlay">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google reviews', 'smart-showcase-for-google-reviews').'</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($zwssgr_post_count > 0) ? $zwssgr_popup_content1  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
								</div>
							</div>
						</div>
					</div>',
					'<div class="zwssgr-popup-item zwssgr-popup2" id="zwssgr-popup2"  data-popup="zwssgrpopup2">
						<div class="zwssgr-title-wrap">
							<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
							<h3>'.esc_html__('Reviews', 'smart-showcase-for-google-reviews').'</h3>
						</div>
						<div class="zwssgr-info-wrap">
							<span class="zwssgr-final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" 	class="zwssgr-total-review">(  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews', 'smart-showcase-for-google-reviews').' )</a>
						</div>
					</div>
					<div id="zwssgrpopup2" class="zwssgr-popup-overlay">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($zwssgr_image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b> '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('Google reviews', 'smart-showcase-for-google-reviews').'</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($zwssgr_post_count > 0) ? $zwssgr_popup_content2  : '<p class="zwssgr-no-found-message">'.esc_html__('No reviews found for the selected ratings', 'smart-showcase-for-google-reviews').'</p>') . '
								</div>
							</div>
						</div>
					</div>'
				]
			];

			$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
			$zwssgr_layout_option_divide = explode('-', $zwssgr_layout_option);

			$zwssgr_layout_option_key = $zwssgr_layout_option_divide[0]; 
			$zwssgr_layout_option_value = $zwssgr_layout_option_divide[1];
			$zwssgr_reviews_html = $zwssgr_filter_layout[$zwssgr_layout_option_key][$zwssgr_layout_option_value-1];

			if($zwssgr_post_count > 0){
				echo '<h3 class="zwssgr-layout-title">'.esc_html__('Layout:', 'smart-showcase-for-google-reviews').' ' . esc_html($zwssgr_layout_option_key) . ' ' . esc_html($zwssgr_layout_option_value) . '</h3>';
			}

			// Return the filtered reviews HTML as the response
			echo wp_kses_post($zwssgr_reviews_html);
			die();
		}		
	}
}
