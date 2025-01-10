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

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );
			add_action('admin_menu', array($this, 'zwssgr_admin_menu_registration'));
			add_action('admin_init', array($this, 'zwssgr_register_settings'));
			add_action('init', array($this, 'zwssgr_register_widget_cpt'));
			add_action('load-post-new.php', array($this, 'action__custom_widget_url_on_add_new'));
			add_action('init', array($this, 'zwssgr_register_review_cpt'));

			add_action('add_meta_boxes', array($this, 'zwssgr_add_review_meta_box'));
			add_action('init', array($this, 'zwssgr_register_request_data_cpt'));
			add_action('add_meta_boxes', array($this, 'zwssgr_add_account_number_meta_box'));

			add_filter('manage_' . ZWSSGR_POST_REVIEW_TYPE . '_posts_columns', array($this, 'filter__zwssgr_manage_data_posts_columns'), 10, 3);
			add_action('manage_' . ZWSSGR_POST_REVIEW_TYPE . '_posts_custom_column', array($this, 'render_hide_column_content'), 10, 2);
			add_action('wp_ajax_toggle_visibility', array($this, 'zwssgr_toggle_visibility'));

			add_filter('wp_kses_allowed_html', array($this, 'zwssgr_action_allow_svg_in_post_content'));;

			add_action('wp_ajax_save_widget_data',array($this, 'save_widget_data'));
			add_action('wp_ajax_nopriv_save_widget_data', array($this, 'save_widget_data'));

			add_action('wp_ajax_filter_reviews', array($this,'filter_reviews_ajax_handler'));
			add_action('wp_ajax_nopriv_filter_reviews', array($this,'filter_reviews_ajax_handler'));

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
				add_action( 'phpmailer_init', array( $this, 'action__init_smtp_mailer' ), 9999 );
			}
		}

		/**
		 * Action: phpmailer_init
		 *
		 * Set SMTP parameters if SMTP mailer.
		 *
		 * @method action__init_smtp_mailer
		 *
		 * @param  mailer object  $phpmailer
		 *
		 */
		function action__init_smtp_mailer(  $phpmailer ) {

			$phpmailer->IsSMTP();

			$from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];
			$from_name = $this->zwssgr_smtp_opt['zwssgr_from_name'];

			$phpmailer->From     = $from_email;
			$phpmailer->FromName = $from_name;
			$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );

			/* Set the SMTP Secure value */
			if ( 'none' !== $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'] ) {
				$phpmailer->SMTPSecure = $this->zwssgr_smtp_opt['zwssgr_smtp_ency_type'];
			}

			/* Set the other options */
			$phpmailer->Host = $this->zwssgr_smtp_opt['zwssgr_smtp_host'];
			$phpmailer->Port = $this->zwssgr_smtp_opt['zwssgr_smtp_port'];

			/* If we're using smtp auth, set the username & password */
			if ( 'yes' == $this->zwssgr_smtp_opt['zwssgr_smtp_auth'] ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $this->zwssgr_smtp_opt['zwssgr_smtp_username'];
				$phpmailer->Password = $this->zwssgr_smtp_opt['zwssgr_smtp_password'];
			}
			//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$phpmailer->SMTPAutoTLS = false;

			//set reasonable timeout
			$phpmailer->Timeout = 10;
			$phpmailer->CharSet  = "utf-8";
		}
		
		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__admin_init() 
		{

			// admin js
			wp_enqueue_script( ZWSSGR_PREFIX . '-admin-min-js', ZWSSGR_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '-admin-js', ZWSSGR_URL . 'assets/js/admin.js', array( 'jquery-core' ), ZWSSGR_VERSION ,true);

			// Google chart JS
			wp_enqueue_script( ZWSSGR_PREFIX . '-google-chart-js', ZWSSGR_URL . 'assets/js/google-chart.js', array( 'jquery-core' ), ZWSSGR_VERSION ,true);

			// Enqueue Daterangepicker JS
			wp_enqueue_script( ZWSSGR_PREFIX . '-daterangepicker-min-js', ZWSSGR_URL . 'assets/js/daterangepicker.min.js', array( 'jquery-core', 'moment' ), ZWSSGR_VERSION ,true);

			// admin css
			wp_enqueue_style( ZWSSGR_PREFIX . '-admin-min-css', ZWSSGR_URL . 'assets/css/admin.min.css', array(), ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . '-admin-css', ZWSSGR_URL . 'assets/css/admin.css', array(), ZWSSGR_VERSION );	
			
			// style css
			wp_enqueue_style( ZWSSGR_PREFIX . '-style-css', ZWSSGR_URL . 'assets/css/style.css', array(), ZWSSGR_VERSION );

			// Enqueue Daterangepicker CSS

			wp_enqueue_style( ZWSSGR_PREFIX . '-daterangepicker-css', ZWSSGR_URL . 'assets/css/daterangepicker.css', array(), ZWSSGR_VERSION );
		
			// Slick js
			wp_enqueue_script( ZWSSGR_PREFIX . '-slick-min-js', ZWSSGR_URL . 'assets/js/slick.min.js', array( 'jquery-core' ), ZWSSGR_VERSION ,true);
			
			// Slick css
			wp_enqueue_style( ZWSSGR_PREFIX . '-slick-css', ZWSSGR_URL . 'assets/css/slick.css', array(), ZWSSGR_VERSION );

			//Toggle Ajax
			wp_localize_script(ZWSSGR_PREFIX . '-admin-js', 'zwssgr_admin', array(
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
			wp_localize_script(ZWSSGR_PREFIX . '-admin-js', 'zwssgr_admin', array(
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
			wp_localize_script(ZWSSGR_PREFIX . '-admin-js', 'my_widget', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('my_widget_nonce'),
            ));

			wp_localize_script(ZWSSGR_PREFIX . '-admin-js', 'filter_reviews', array(
				'ajax_url' => admin_url('admin-ajax.php'), // The AJAX handler URL
				'nonce' => wp_create_nonce('filter_reviews_nonce') // Security nonce
			));
		
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
				<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				viewBox="0 0 1024 1024" style="enable-background:new 0 0 1024 1024;" xml:space="preserve">
					<style type="text/css">
						.st0{fill:#A7AAAD;}
						.st1{fill-rule:evenodd;clip-rule:evenodd;fill:#A7AAAD;}
					</style>
					<g>
						<path class="st0" d="M762.9,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C796.9,834.9,781.7,819.7,762.9,819.7z"/>
						<path class="st0" d="M660.4,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C694.3,834.9,679.1,819.7,660.4,819.7z"/>
						<path class="st0" d="M557.8,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C591.7,834.9,576.5,819.7,557.8,819.7z"/>
						<path class="st1" d="M263.4,443.9c-53.1,63.5-80,128.5-77.8,187.8v0c-0.1,1.8-0.3,3.5-0.3,5.2c-1-3.1-2.1-6.9-3.4-11.3
							c-3-10.4-4.7-20.4-5.6-25.5c-0.1-0.7-0.2-1.2-0.3-1.7c-0.5-2.8-2.1-15.7-3.9-29.3c-1-8-2.1-16.2-3-22.7c-2.3-17.5-6.8-36.3-10.1-49
							c-3.3-12.7-8.9-26.1-14.4-38.5c-5.5-12.4-13.9-27.4-20.3-38.3c-6.4-10.8-15.2-21.2-15.2-21.2c-26.6-30.9-49.7-32.8-49.7-32.8
							c-22.6-5.5-42-1.4-42-1.4c3.7-22.6,10.7-35.9,17.1-47.9c0.3-0.7,0.7-1.3,1-1.9c6.7-12.5,17.9-24,24.2-29.3c0.2-0.2,0.5-0.4,0.8-0.7
							c7.9-6.7,39.6-33.6,95.2-33.1c57.8,0.5,85.6,23.6,100.5,38.4c14.9,14.7,32.7,40.1,37.3,46.8c4.6,6.6,13.3,20.2,13.3,20.2
							s8.1,13,15.8,25.2C300.2,403.1,280.4,423.4,263.4,443.9z M118,315.4c0,6.1-4.8,11-10.7,11s-10.7-4.9-10.7-11c0-6.1,4.8-11,10.7-11
							C113.2,304.4,118,309.3,118,315.4z"/>
						<path class="st0" d="M722.1,164.1c-53.8,24.3-102.2,48-146.1,71.3c-179.1,95.2-279.1,185.4-329,263c11.9-13.7,25.5-27.1,40.7-40
							C373,386.1,505.3,330.7,681,293.6l10.7-2.2c19.7-59.2,32.3-105.3,36.9-123C729.7,164.8,725.7,162.7,722.1,164.1z"/>
						<path class="st0" d="M652.7,400c5.2-12.5,11-29.4,17.1-45.5c0.9-2.7,1.9-5.7,3.1-8.1c4.7-12.7,8.8-24.5,11.9-33.6
							c-294.7,62.1-420.8,167.6-462.9,258C290.4,481.7,385.6,420.9,652.7,400z"/>
						<path class="st0" d="M643.4,417.6c-151.8,14.7-267.6,47-344.5,96.2c-39.2,25.1-63.5,51.5-78.3,76.4c-28.4,47.7-20.7,109.4,21,146
							c8.1,7.1,16.5,12.4,24.4,16.1c22.1,10.2,44.3,15.3,66,15.3c61.1,0,123.1-40.6,184.2-120.7C560.1,589.2,602.9,512.2,643.4,417.6z"/>
						<path class="st0" d="M720.2,413.3c-19.5,0.5-38.3,1.3-56.4,2.3l-12.3,27.7C610,537.3,566,614.3,520.8,672
							c-17.9,22.8-35.9,42.5-54.1,59.2c116.5-77.3,232.6-223.3,301-318.7C751.7,412.5,735.6,412.8,720.2,413.3L720.2,413.3z"/>
						<path class="st0" d="M782.5,394.5c24.1-37.4,48.7-80.4,61.1-101.1c2.4-4-0.3-9.4-4.4-8.7c-50.1,7.7-116,20.4-135.3,24.2
							c-3.3,10-7.2,21.4-11.5,33.7l-0.4,1c-0.8,1.8-1.7,4.2-2.4,6.6l-0.7,2c-3.2,8.8-12.9,32.3-18.3,46.3
							C692.3,396.7,732.6,394.3,782.5,394.5L782.5,394.5z"/>
						<path class="st0" d="M220.2,702c-0.3-0.3,0.1,0.6,1.3,2.3C221.1,703.5,220.6,702.8,220.2,702z"/>
						<path class="st0" d="M594.3,644.1c24.4-16.6,49.7-35.1,75.9-55.5c80.8-62.9,150-128.2,189.1-166.7c2.1-2,0.7-5.7-2.2-5.7
							c-26.4-0.5-54.7-0.7-71.3-0.9C739.7,480,670.7,569.4,594.3,644.1z"/>
						<path class="st0" d="M335.3,776.8c-1.8,0.1-3.5,0.1-5.2,0.1c1,0,2,0,3,0C333.9,776.9,334.6,776.8,335.3,776.8L335.3,776.8z"/>
						<path class="st0" d="M325.6,776.7c1,0,2.1,0.1,3.2,0.1c0.1,0,0.2,0,0.4,0C327.9,776.9,326.7,776.8,325.6,776.7L325.6,776.7z"/>
						<path class="st0" d="M307.3,775.2h-0.5c11.3,4.9,23.1,8.8,35,11.7c27.9,7.8,103.9,29.4,124.2,37.5c0,0-43.9-23-55.3-31.3h-0.2
							c7.6-0.5,22.4-1.9,30-3.2c30.2,6.4,31.7,9.5,49.8,16.7c0,0-29.3-21.8-29.8-21.8c35.5-10,69.4-28.4,97.7-55.1
							c1.4-1.5,2.8-2.7,4.3-4.2c27.2-25.7,53.1-39.7,63.2-44.8c17.6-9.1,71.3-36,153.5-43.1c-47.2-2.2-88.8-6.1-122.3-10.8
							C519.8,733.5,413.4,786.5,307.3,775.2L307.3,775.2z"/>
						<path class="st0" d="M935.6,665.2c-82.8-35.5-217.4-24.2-311,41c9.3-1.9,19.4-3,30.2-3c9.1,0,18.6,0.8,28.5,2.7
							c27.4,5.2,55.4,21.9,85,39.5c33.7,20,68.6,40.8,109.1,50.1l0.3,0.1c4.1,1.1,7.9,2.1,11.6,2.9c38.6,7.5,83.3-3.8,104-26.2
							c7.9-8.5,11.4-17.9,10.6-27.8C1002.2,721,988.9,688,935.6,665.2z"/>
					</g>
			</svg>'),
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
			$labels = array(
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

			$args = array(
				'label' => __('Widgets', 'smart-showcase-for-google-reviews'),
				'labels' => $labels,
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

			register_post_type(ZWSSGR_POST_WIDGET_TYPE, $args);
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
			$labels = array(
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

			$args = array(
				'label' => __('Reviews', 'smart-showcase-for-google-reviews'),
				'labels' => $labels,
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

			register_post_type(ZWSSGR_POST_REVIEW_TYPE, $args);

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
		function zwssgr_debug_function( $message ) {
			// Define the custom log directory path.
			$log_dir = WP_CONTENT_DIR . '/plugins/smart-showcase-for-google-reviews'; // wp-content/plugins/smart-showcase-for-google-reviews
		
			// Define the log file path.
			$log_file = $log_dir . '/smart-showcase-for-google-reviews-debug.log';
		
			// Check if the directory exists, if not create it.
			if ( ! file_exists( $log_dir ) ) {
				wp_mkdir_p( $log_dir );
			}
		
			// Initialize the WP_Filesystem.
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		
			global $wp_filesystem;
		
			// Format the log entry with UTC timestamp using gmdate().
			$log_entry = sprintf( "[%s] %s\n", gmdate( 'Y-m-d H:i:s' ), $message );
		
			// Write the log entry to the file using WP_Filesystem.
			if ( $wp_filesystem->exists( $log_file ) || $wp_filesystem->put_contents( $log_file, $log_entry, FS_CHMOD_FILE ) ) {
				$wp_filesystem->put_contents( $log_file, $log_entry, FS_CHMOD_FILE );
			}
		}
		
		function zwssgr_action_allow_svg_in_post_content($allowed_tags) {

			$allowed_tags['svg'] = array(
				'xmlns' => true,
				'width' => true,
				'height' => true,
				'fill' => true,
				'viewBox' => true,
				'class' => true,
				'stroke' => true,
				'stroke-width' => true,
			);
			$allowed_tags['path'] = array(
				'd' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
			);

			return $allowed_tags;
			
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

			$zwssgr_rating_value 	  	  = strtoupper($zwssgr_review_star_rating);
			$numeric_rating 		  	  = isset($zwssgr_rating_map[$zwssgr_rating_value]) ? $zwssgr_rating_map[$zwssgr_rating_value] : 0;
			$zwssgr_filled_star 		  	  = str_repeat($zwssgr_filled_star, $numeric_rating);
			$zwssgr_empty_star 		  	  = str_repeat($zwssgr_empty_star, 5 - $numeric_rating);
			$zwssgr_review_id 		  	  = get_post_meta($zwssgr_review->ID, 'zwssgr_review_id', true);
			$zwssgr_gmb_reviewer_image_uri = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';

			echo '<table class="form-table test" id="gmb-review-data" zwssgr-review-id="'.esc_attr( $zwssgr_review->ID ).'">
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
					<th class="separator-cell" colspan="2">
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
						<div id="json-response-message zwssgr-success-message"></div>
						<textarea name="zwssgr_reply_comment" class="zwssgr-regular-text" rows="5">'. esc_textarea($zwssgr_reply_comment) .'</textarea>
						<div class="cta-wrapper">';
							if (!empty($zwssgr_reply_comment)) {
								echo '<button class="button button-primary button-large zwssgr-submit-btn" id="update-reply"> ' . esc_html('Update', 'smart-showcase-for-google-reviews') . ' </button>';
							} else {
								echo '<button class="button button-primary button-large zwssgr-submit-btn" id="add-reply"> ' . esc_html('Add Reply', 'smart-showcase-for-google-reviews') . ' </button>';
							}
							if (!empty($zwssgr_reply_comment)) {
								echo '<button class="button button-danger button-large zwssgr-submit-btn" id="delete-reply">' . esc_html('Delete', 'smart-showcase-for-google-reviews') . '</button>';
							}
						echo '</div>
					</td>
				</tr>
			</table>';
			
		}

		function zwssgr_register_request_data_cpt()
		{
			$labels = array(
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

			$args = array(
				'label'               => __('Request Data', 'smart-showcase-for-google-reviews'),
				'labels'              => $labels,
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

			register_post_type('zwssgr_request_data', $args);  // Updated post type name to zwssgr_request_data
		}

		function zwssgr_add_custom_meta_filters() 
		{
			global $wpdb;

			// Get current post type
			$current_post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';

			// Check if we are on the correct post type pages
			if (!in_array($current_post_type, [ZWSSGR_POST_REVIEW_TYPE, ZWSSGR_POST_WIDGET_TYPE])) {
				return;
			}

			if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
				return;
			}

			// Get saved email and selected filters from URL parameters
			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$selected_account = isset($_GET['zwssgr_account']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_account'])) : '';
			$selected_location = isset( $_GET['zwssgr_location'] ) ? sanitize_text_field( wp_unslash( $_GET['zwssgr_location'] ) ) : '';

			// Fetch accounts using SQL query
			$accounts_query = $wpdb->prepare("
				SELECT pm.meta_value AS account_number, p.ID AS post_id, p.post_title AS account_name
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
				AND p.post_type = %s
				AND p.post_status = 'publish'
				AND EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm2
					WHERE pm2.post_id = p.ID AND pm2.meta_key = 'zwssgr_gmb_email' AND pm2.meta_value = %s
				)
			", 'zwssgr_account_number', 'zwssgr_request_data', $zwssgr_gmb_email);

			$accounts = $wpdb->get_results($accounts_query);

			// Begin the form
			echo '<form method="GET">';
			wp_nonce_field('zwssgr_get_form', 'security-zwssgr-get-form');
			echo '<input type="hidden" name="post_type" value="' . esc_attr($current_post_type) . '">';

			// Account dropdown
			echo '<select id="zwssgr-account-select" name="zwssgr_account">';
			echo '<option value="">Select an Account</option>';
			foreach ($accounts as $account) {
				$selected = ($account->account_number === $selected_account) ? ' selected' : '';
				echo '<option value="' . esc_attr($account->account_number) . '"' . esc_attr( $selected ). '>' . esc_html($account->account_name) . '</option>';
			}
			echo '</select>';

			
			// Fetch locations using SQL query
			$locations_query = $wpdb->prepare("
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
			", 'zwssgr_account_locations', 'zwssgr_request_data', $selected_account);

			$locations = $wpdb->get_results($locations_query);

			if (!empty($locations)){

				// Location dropdown
				echo '<select id="zwssgr-location-select" name="zwssgr_location">';
				echo '<option value="">Select a Location</option>';

					// Parse and output location options
					foreach ($locations as $location) {
						$location_data = maybe_unserialize($location->location_data);
						if (is_array($location_data)) {
							foreach ($location_data as $loc) {
								$loc_title = $loc['title'] ?? '';
								$loc_value = ltrim(strrchr($loc['name'], '/'), '/');
								$selected = ($loc_value === $selected_location) ? ' selected' : '';
								echo '<option value="' . esc_attr($loc_value) . '"' . esc_attr($selected) . '>' . esc_html($loc_title) . '</option>';
							}
						}
					}

				echo '</select>';
				echo '</form>';
			}
			
		}
		

		// Filter posts based on custom meta for ZWSSGR_POST_REVIEW_TYPE
		function zwssgr_filter_posts_by_custom_meta($query)
		{
			global $pagenow;

			if(isset($_POST['security-zwssgr-get-form']) && wp_verify_nonce(sanitize_file_name(wp_unslash($_POST['security-zwssgr-get-form'])), 'zwssgr_get_form')){
				return;
			}

			if (is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && in_array($_GET['post_type'], [ZWSSGR_POST_REVIEW_TYPE, ZWSSGR_POST_WIDGET_TYPE])) {
				$meta_query = array();
				
				if (isset($_GET['zwssgr_account']) && !empty($_GET['zwssgr_account'])) {
					$zwssgr_account = isset($_GET['zwssgr_account']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_account'])) : '';
					$meta_query[] = [
						'key'     => 'zwssgr_account_number',
						'value'   => $zwssgr_account,
						'compare' => '='
					];
				}
				

				if (isset($_GET['zwssgr_location']) && !empty($_GET['zwssgr_location'])) {
					$zwssgr_location = isset($_GET['zwssgr_location']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_location'])) : '';
					$meta_query[] = [
						'key'     => 'zwssgr_location_number',
						'value'   => $zwssgr_location,
						'compare' => '='
					];
				}
				

				if (!empty($meta_query)) {
					$query->set('meta_query', array_merge([
						'relation' => 'AND',
					], $meta_query));
				}
			}
		}

		// Register the meta box
		function zwssgr_add_account_number_meta_box() 
		{
			add_meta_box(
				'zwssgr_account_number_meta_box', // Meta box ID
				__('Account Number', 'smart-showcase-for-google-reviews'), // Title
				array($this, 'zwssgr_display_account_number_meta_box'), // Callback function to display the meta box content
				'zwssgr_request_data', // Post type
				'normal', 
				'high' 
			);
		}

		// Add the custom "Shortcode" column
		function zwssgr_add_shortcode_column($columns) 
		{
			$new_columns = array();
			foreach ($columns as $key => $title) {
				if ($key === 'title') {
					$new_columns[$key] = $title; // Keep the Title column
					$new_columns['shortcode'] = __('Shortcode', 'smart-showcase-for-google-reviews'); // Add Shortcode column after Title
				} else {
					$new_columns[$key] = $title; // Add other columns
				}
			}
			return $new_columns;
		}


		function zwssgr_populate_shortcode_column($column, $post_id) 
		{
			if ($column === 'shortcode') {
				// Check if the "tab-selected" metadata exists and meets a specific condition
				$current_tab2 = get_post_meta($post_id, 'tab-selected', true); 
				
				if ($current_tab2) { // Or use any specific condition for `$current_tab2`
					// Generate the shortcode
					$shortcode = sprintf('[zwssgr_widget post-id="%d"]', $post_id);
					
					// Display the shortcode and copy icon
					echo '<div class="zwssgr-shortcode">';
					echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly class="zwssgr-shortcode-text" id="shortcode-' . esc_attr($post_id) . '">';
					echo '<span class="dashicons dashicons-admin-page copy-shortcode-icon" data-target="shortcode-' . esc_attr($post_id) . '" class="zwssgr-dashicons" title="' . esc_attr__('Copy Shortcode', 'smart-showcase-for-google-reviews') . '"></span>';
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
		 * @param  array $columns
		 *
		 * @return array
		 */
		function filter__zwssgr_manage_data_posts_columns($columns)
		{
			unset($columns['date']);
			unset($columns['title']);
			$columns['title'] = __('Review', 'smart-showcase-for-google-reviews');
			$columns[ZWSSGR_META_PREFIX . 'user_login'] = __('Hide', 'smart-showcase-for-google-reviews');
			$columns['date'] = __('Date', 'smart-showcase-for-google-reviews');
			return $columns;
		}

		/**
		 * Render the visibility column content
		 *
		 * @param string $column
		 * @param int $post_id
		 */
		function render_hide_column_content( $column, $post_id ) 
		{
			if ( $column === ZWSSGR_META_PREFIX . 'user_login' ) {
				$is_hidden = get_post_meta( $post_id, '_is_hidden', true );
				$icon = $is_hidden ? 'hidden' : 'visibility';

				// Display the toggle button with the current state
				echo '<a href="#" class="zwssgr-toggle-visibility" data-post-id="' . esc_attr( $post_id ) . '">';
				echo '<span class="dashicons dashicons-' . esc_attr( $icon ) . '"></span>';
				echo '</a>';
			}
		}

		/**
		 * Toggle visibility (AJAX callback)
		 */
		function zwssgr_toggle_visibility() 
		{
			check_ajax_referer( 'toggle-visibility-nonce', 'nonce' );
		
			$post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : 0;

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( array( 'message' => 'Not authorized' ) );
			}
		
			$is_hidden = get_post_meta( $post_id, '_is_hidden', true );
		
			if ( $is_hidden ) {
				// If currently hidden, set to visibility and delete meta
				delete_post_meta( $post_id, '_is_hidden' );
				$new_state = 'show';
				$icon = 'visibility';
			} else {
				// If currently visibility, set to hidden
				update_post_meta( $post_id, '_is_hidden', 1 );
				$new_state = 'hidden';
				$icon = 'hidden';
			}
		
			// Send JSON response back to the front-end
			wp_send_json_success( array(
				'icon' => $icon,
				'state' => $new_state // Send the current state (hidden/show)
			));
		}

		/**
		 * Register settings for the plugin
		 * @method zwssgr_register_settings
		 */
		function zwssgr_register_settings()
		{

			// SEO & Notifications Settings
			register_setting('zwssgr_notification_settings', 'zwssgr_admin_notification_enabled');

			//Advance Setting
			register_setting('zwssgr_advanced_account_settings', 'zwssgr_sync_reviews');

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
			$value = get_option('zwssgr_admin_notification_enabled', '0');
			echo '<label class="switch zwssgr-switch">';
			echo '<input type="checkbox" id="zwssgr_admin_notification_enabled" name="zwssgr_admin_notification_enabled" value="1" ' . checked(0, $value, false) . ' />';
			echo '<span class="slider zwssgr-toggle-slider"></span>';
			echo '</label>';
		}

		function zwssgr_admin_notification_emails_callback()
		{
			$value = get_option('zwssgr_admin_notification_emails', '');
			echo '<div class="zwssgr-notification-field">';
			echo '<div class="zwssgr-th-label">';
			echo '<label class="zwssgr-th">Custom Email Addresses</label>';
			echo '</div>';
			echo '<div class="zwssgr-field">';
			echo '<input type="text" id="zwssgr_admin_notification_emails" name="zwssgr_admin_notification_emails" class="zwssgr-input-text" rows="5" cols="50" value="' . esc_attr($value) . '" />';
			echo '<p class="zwssgr-description">' . esc_html('Enter email addresses separated by commas.', 'smart-showcase-for-google-reviews') . '</p>';
			echo '</div>';
			echo '</div>';
		}

		function zwssgr_admin_notification_emails_subject_callback()
		{
			$value = get_option('zwssgr_admin_notification_emails_subject', '');
			echo '<div class="zwssgr-notification-field">';
			echo '<div class="zwssgr-th-label">';
			echo '<label class="zwssgr-th">Custom Email Subject</label>';
			echo '</div>';
			echo '<div class="zwssgr-field">';
			echo '<input type="text" id="zwssgr_admin_notification_emails_subject" class="zwssgr-input-text" name="zwssgr_admin_notification_emails_subject" rows="5" cols="50" value="' . esc_attr(
				$value) . '" />';
			echo '</div>';
			echo '</div>';
		}

		function zwssgr_admin_notofication_email_body_callback() {
			// Get the current value from the database
			$value = get_option('zwssgr_admin_notification_email_body', '');
		
			// Set up the WYSIWYG editor settings
			$settings = array(
				'textarea_name' => 'zwssgr_admin_notification_email_body',
				'editor_class'  => 'zwssgr_email_body_editor', // Add a custom class if needed
				'media_buttons' => false, // Set to true to show media buttons
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,unlink,|,removeformat', // Customize toolbar buttons
				),
				'textarea_rows' => 10, // Set the number of rows for the editor
			);
			
			echo '<div class="zwssgr-editor-wrapper">';
				echo '<div class="zwssgr-th-label">';
				echo '<label for="zwssgr_admin_notification_email_body" class="zwssgr-th">' . esc_html('Email Body', 'smart-showcase-for-google-reviews') . '</label>';
				echo '</div>';
				echo '<div class="zwssgr-field">';
				wp_editor($value, 'zwssgr_admin_notification_email_body', $settings);
				echo '<p class="zwssgr-description">' . esc_html('Enter your custom email body content here.', 'smart-showcase-for-google-reviews') . '</p>';
				echo '</div>';
			echo '</div>';
		}

		//Advance Section 
		function zwssgr_sync_reviews_callback()
		{
			$value = get_option('zwssgr_sync_reviews', 'daily');
			echo '<select id="zwssgr_sync_reviews" name="zwssgr_sync_reviews" class="zwssgr-input-text">
					<option value="daily" ' . selected($value, 'daily', false) . '>Daily</option>
					<option value="weekly" ' . selected($value, 'weekly', false) . '>Weekly</option>
					<option value="monthly" ' . selected($value, 'monthly', false) . '>Monthly</option>
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
					$nonce = isset($_POST['zwssgr_notification_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_notification_nonce_field'])) : '';
				
					if (wp_verify_nonce($nonce, 'zwssgr_notification_nonce')) {
					// Handle notification emails submission
						if (isset($_POST['zwssgr_admin_notification_emails'])) {
							// Sanitize and save the form values
							$emails = isset($_POST['zwssgr_admin_notification_emails']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_admin_notification_emails'])) : '';
							$subject = isset($_POST['zwssgr_admin_notification_emails_subject']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_admin_notification_emails_subject'])) : '';
							$body = isset($_POST['zwssgr_admin_notification_email_body']) ? wp_kses_post(wp_unslash($_POST['zwssgr_admin_notification_email_body'])) : '';
				
							// Update the options (only update the subject and body; leave email field empty after submission)
							update_option('zwssgr_admin_notification_emails_subject', $subject);
							update_option('zwssgr_admin_notification_email_body', $body);
				
							// Prepare email
							if (!empty($emails)) {
								$from_email = '';
								if (is_array($this->zwssgr_smtp_opt) && isset($this->zwssgr_smtp_opt['zwssgr_from_email'])) {
									$from_email = $this->zwssgr_smtp_opt['zwssgr_from_email'];
								}
								$to = explode(',', $emails); // Assume emails are comma-separated
								$message = $body;
								$headers[] = 'Content-type: text/html; charset=utf-8';
								$headers[] = 'From:' . $from_email;
								// Send the email using wp_mail()
								$mail_sent = wp_mail($to, $subject, $message, $headers);
				
								// Check if email was sent successfully
								if ($mail_sent) {
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
					$nonce = isset($_POST['zwssgr_advanced_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_advanced_nonce_field'])) : '';
					$value = isset($_POST['zwssgr_sync_reviews']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_sync_reviews'])) : 'daily'; // Default to 'daily' if not set
					if (wp_verify_nonce($nonce, 'zwssgr_advanced_nonce')) {
						// Handle advanced settings form submission
						if (isset($_POST['advance_submit_buttons'])) {
							update_option('zwssgr_sync_reviews', $value);
							add_settings_error('zwssgr_advanced_account_settings', 'settings_updated', 'Advanced settings saved successfully!', 'updated');
						}
					}
				}
			}
		
			// Now render the form and tabs
			$current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'google';
			?>
			<div class="wrap">
				<h1 class="zwssgr-page-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
				<div class="zwssgr-section-wrap">
					<h2 class="nav-tab-wrapper zwssgr-nav-tab-wrapper">
						<a href="?page=zwssgr_settings&tab=google" class="nav-tab <?php echo ($current_tab === 'google') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('Google', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=notifications" class="nav-tab <?php echo ($current_tab === 'notifications') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('SEO & Notifications', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=advanced" class="nav-tab <?php echo ($current_tab === 'advanced') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('Advanced', 'smart-showcase-for-google-reviews'); ?>
						</a>
						<a href="?page=zwssgr_settings&tab=smtp-settings" class="nav-tab <?php echo ($current_tab === 'smtp-settings') ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e('SMTP Settings', 'smart-showcase-for-google-reviews'); ?>
						</a>
					</h2>
					<?php if ($current_tab === 'google'):

					$zwssgr_disconnect_text = 'Disconnect'; // Default value

					// Safely check for 'tab' and 'settings' in the query string
					if (isset($_GET['tab'], $_GET['settings']) && $_GET['tab'] === 'google' && $_GET['settings'] === 'disconnect-auth') {
						$zwssgr_disconnect_text = 'Disconnecting...';
					}

					// Check if the JWT token is present in the database
					$zwssgr_jwt_token = get_option('zwssgr_jwt_token');
					$zwssgr_gmb_email = get_option('zwssgr_gmb_email');

					if (!empty($zwssgr_jwt_token)) { ?>
						<div class="disconnect-wrapper">
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
						<p class="zwssgr-google-tab-text">Please connect to Google!</p>
					<?php } ?>

					<?php elseif ($current_tab === 'notifications'): ?>
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
					<?php elseif ($current_tab === 'advanced'): ?>
						<form action="" method="post" class="zwssgr-setting-form">
							<?php
							wp_nonce_field('zwssgr_advanced_nonce', 'zwssgr_advanced_nonce_field');
							settings_errors('zwssgr_advanced_account_settings');
							settings_fields('zwssgr_advanced_account_settings');
							do_settings_sections('zwssgr_advanced_account_settings');
							submit_button( 'Save Advanced Settings', 'zwssgr-submit-btn', 'advance_submit_buttons' );
							?>
						</form>
						<?php elseif ($current_tab === 'smtp-settings'):
							// SMTP settings
							require_once( ZWSSGR_DIR . '/inc/admin/' . ZWSSGR_PREFIX . '.smtp.settings.template.php' );
						endif; ?> 
				</div>
			</div>
			<?php
		}
		function translate_read_more($language) 
		{
			$translations = [
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
			return $translations[$language] ?? 'Read more'; // Fallback to English
		}
		function translate_months($language) {
			$month_translations = [
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
		
			return $month_translations[$language] ?? $month_translations['en']; // Fallback to English
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
			echo '<div class="zwgr-dashboard">
				<div class="zwgr-dashboard-header">'
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

			$post_id = isset($_GET['zwssgr_widget_id']) ? sanitize_text_field(wp_unslash($_GET['zwssgr_widget_id'])) : '';
			$post_objct = get_post($post_id);
			if (!isset($post_id) || !$post_objct ) {
				wp_die( 'Invalid post ID.' ) ;
			}

			// Get stored widget settings
			$display_option = get_post_meta($post_id, 'display_option', true);
			$layout_option = get_post_meta($post_id, 'layout_option', true);
			$selected_elements = get_post_meta($post_id, 'selected_elements', true);
			$keywords = get_post_meta($post_id, 'keywords', true);
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$char_limit = get_post_meta($post_id, 'char_limit', true);
			$language = get_post_meta($post_id, 'language', true);
			$sort_by = get_post_meta($post_id, 'sort_by', true);
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true)?'checked':'';
			$google_review_toggle = get_post_meta($post_id, 'google_review_toggle', true);
			$bg_color = get_post_meta($post_id, 'bg_color', true);
			$text_color = get_post_meta($post_id, 'text_color', true);
			$bg_color_load = get_post_meta($post_id, 'bg_color_load', true);
			$text_color_load = get_post_meta($post_id, 'text_color_load', true);
			$posts_per_page = get_post_meta($post_id, 'posts_per_page', true);
			// Set default value to 10 if no value is found
			if (empty($posts_per_page)) {
				$posts_per_page = 10;
			}
			$selected_elements = is_array($selected_elements) ? $selected_elements : [];
			$selected_display_option = !empty($display_option) ? $display_option : 'all'; 
			$selected_layout_option = !empty($layout_option) ? $layout_option : '';
			$custom_css = get_post_meta($post_id, '_zwssgr_custom_css', true);
	
			// Generate the shortcode by calling the new function
			$generated_shortcode = $this->generate_shortcode($post_id);
			$current_tab = get_post_meta($post_id, 'tab-options', true); 
			$current_tab2 = get_post_meta($post_id, 'tab-selected', true); 
			$rating_filter = intval(get_post_meta($post_id, 'rating_filter', true)) ?: 0;
			$enable_sort_by = get_post_meta($post_id, 'enable_sort_by', true);
			$zwssgr_location_new_review_uri = get_post_meta($post_id, 'zwssgr_location_new_review_uri', true);
			$zwssgr_location_name = get_post_meta($post_id, 'zwssgr_location_name', true);
			$zwssgr_location_all_review_uri =  get_post_meta($post_id, 'zwssgr_location_all_review_uri', true);
			$zwssgr_location_thumbnail_url = get_post_meta($post_id, 'zwssgr_location_thumbnail_url', true);


			// Define the mapping from numeric values to words.
			$rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word.
			$rating_filter_word = isset($rating_mapping[$rating_filter]) ? $rating_mapping[$rating_filter] : '';

			$ratings_to_include = array();
			if ($rating_filter_word == 'TWO') {
				$ratings_to_include = array('TWO');
			} elseif ($rating_filter_word == 'THREE') {
				$ratings_to_include = array('THREE');
			} elseif ($rating_filter_word == 'FOUR') {
				$ratings_to_include = array('FOUR');
			} elseif ($rating_filter_word == 'FIVE') {
				$ratings_to_include = array('FIVE');
			} elseif ($rating_filter_word == 'ONE') {
				$ratings_to_include = array('ONE');
			}

			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($post_id, 'zwssgr_account_number', true);
			$zwssgr_location_number =get_post_meta($post_id, 'zwssgr_location_number', true);
			
			$zwssgr_reviews_args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE,
				'posts_per_page' => 5,
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are met
					array(
						'key'     => 'zwssgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
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
			switch ($sort_by) {
				case 'newest':
					$zwssgr_reviews_args['orderby'] = 'date';
					$zwssgr_reviews_args['order'] = 'DESC';
					break;

				case 'highest':
					// Sort from FIVE to ONE
					add_filter('posts_orderby', function ($orderby, $query) use ($rating_mapping) {
						global $wpdb;
						if ($query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$custom_order = "'" . implode("','", array_reverse($rating_mapping)) . "'";
							$orderby = "FIELD({$wpdb->postmeta}.meta_value, $custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $orderby;
					}, 10, 2);
					break;

				case 'lowest':
					// Sort from ONE to FIVE
					add_filter('posts_orderby', function ($orderby, $query) use ($rating_mapping) {
						global $wpdb;
						if ($query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$custom_order = "'" . implode("','", $rating_mapping) . "'";
							$orderby = "FIELD({$wpdb->postmeta}.meta_value, $custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $orderby;
					}, 10, 2);
					break;

				default:
					$zwssgr_reviews_args['orderby'] = 'date';
					$zwssgr_reviews_args['order'] = 'DESC';
			}

			$latest_zwssgr_reviews = new WP_Query($zwssgr_reviews_args);
			$post_count = $latest_zwssgr_reviews->found_posts;
			$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
			$image_url = '';
			$image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $plugin_dir_path . 'assets/images/Google_G_Logo.png';
			if ($latest_zwssgr_reviews->have_posts()) {
				while($latest_zwssgr_reviews->have_posts()) {
					$latest_zwssgr_reviews->the_post();
			
					$zwssgr_reviewer_name   	  	   = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
					$zwssgr_review_star_rating 	   = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
					$zwssgr_review_comment  	  	   = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
					$zwssgr_review_id		  	   = get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
					$zwssgr_gmb_reviewer_image_path = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_gmb_reviewer_image_uri  = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$published_date  = get_the_date('F j, Y');
					$months = $this->translate_months($language);

					// Determine if content is trimmed based on character limit
					$is_trimmed = $char_limit > 0 && mb_strlen($zwssgr_review_comment) > $char_limit; // Check if the content length exceeds the character limit
					$trimmed_content = $is_trimmed ? mb_substr($zwssgr_review_comment, 0, $char_limit) . '...' : $zwssgr_review_comment; // Trim the content if necessary


					$formatted_date = '';
					$timestamp = strtotime($published_date); // Calculate the timestamp once for better performance

					if ($date_format === 'DD/MM/YYYY') {
						$formatted_date = gmdate('d/m/Y', $timestamp);
					} elseif ($date_format === 'MM-DD-YYYY') {
						$formatted_date = gmdate('m-d-Y', $timestamp);
					} elseif ($date_format === 'YYYY/MM/DD') {
						$formatted_date = gmdate('Y/m/d', $timestamp);
					} elseif ($date_format === 'full') {
						$day = gmdate('j', $timestamp);
						$month = $months[(int)gmdate('n', $timestamp) - 1];
						$year = gmdate('Y', $timestamp);

						// Construct the full date
						$formatted_date = "$month $day, $year";
					} elseif ($date_format === 'hide') {
						$formatted_date = ''; // No display for "hide"
					}


					// Map textual rating to numeric values
					$rating_map = [
						'ONE'   => 1,
						'TWO'   => 2,
						'THREE' => 3,
						'FOUR'  => 4,
						'FIVE'  => 5,
					];

					// Convert the textual rating to numeric
					$numeric_rating = isset($rating_map[$zwssgr_review_star_rating]) ? $rating_map[$zwssgr_review_star_rating] : 0;

					// Generate stars HTML
					$stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$stars_html .= $i < $numeric_rating 
							? '<span class="zwssgr-star filled">★</span>' 
							: '<span class="zwssgr-star">☆</span>';
					}

					// Format the slider item for each review
					$zwssgr_slider_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
								' .
									( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ). '
							</div>
						</div>';

						$zwssgr_slider_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-rating-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_slider_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item5 = '
							<div class="zwssgr-slide-item">
								<div>
									<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'	
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									<div class="zwssgr-contnt-wrap">
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '	
										' . (!empty($published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item6 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_list_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
						
						$zwssgr_list_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwssgr_list_item3= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_list_item4= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										</div>
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';

						$zwssgr_list_item5= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-list-wrap5">
										<div class="zwssgr-prifile-wrap">
											<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											</div>
											<div class="zwssgr-data">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											</div>
										</div>
										<div class="zwssgr-content-wrap">
											<div class="zwssgr-review-info">
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
												<div class="zwssgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
												</div>
											</div>
											' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_grid_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_grid_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											<div class="zwssgr-date-wrap">
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
												' . (!empty($published_date)
												? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											</div>
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_grid_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-review-detail">
											<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											</div>
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
												? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											<div class="zwssgr-rating-wrap">
												<div class="zwssgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
												</div>
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
											</div>
										</div>
										' . ( !empty($trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwssgr_grid_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>									
									' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';
						
						$zwssgr_grid_item5 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_popup_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_popup_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
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


			$zwssgr_gmb_account_number = get_post_meta($post_id, 'zwssgr_account_number', true);
			$zwssgr_gmb_account_location =get_post_meta($post_id, 'zwssgr_location_number', true);

			$zwssgr_filter_data = [
				'zwssgr_gmb_account_number'   => $zwssgr_gmb_account_number,
				'zwssgr_gmb_account_location' => $zwssgr_gmb_account_location,
				'zwssgr_range_filter_type'    => '',
				'zwssgr_range_filter_data'    => ''
			];

			$zwssgr_data_render_args = $this->zwssgr_dashboard->zwssgr_data_render_query($zwssgr_filter_data);		
			$zwssgr_reviews_ratings = $this->zwssgr_dashboard->zwssgr_get_reviews_ratings($zwssgr_data_render_args);
			$widthPercentage = $zwssgr_reviews_ratings['ratings'] * 20;


			$final_rating = ' <div class="zwssgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwssgr-final-review-fill" style="width: ' . $widthPercentage . '%;">
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
			$options = [
				'slider' => [
					'<div class="zwssgr-slider zwssgr-slider1" id="zwssgr-slider1"> 
						<div class="zwssgr-slider-1">
							' . (($post_count > 0) ? $zwssgr_slider_content1  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider2" id="zwssgr-slider2">
						<div class="zwssgr-slider-2">
							' . (($post_count > 0) ? $zwssgr_slider_content2  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider3" id="zwssgr-slider3">
						<div class="zwssgr-slider-badge">
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
								<div class="zwssgr-badge-item" id="zwssgr-badge1">
									<h3 class="zwssgr-average">Good</h3>
									' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
									<p class="zwssgr-based-on">Based on <b>  '.$zwssgr_reviews_ratings['reviews'].' reviews </b></p>
									<img src="' . $plugin_dir_path . 'assets/images/google.png" alt="Google">
								</div>
							</a>
						</div>
						<div class="zwssgr-slider-3">
							' . (($post_count > 0) ? $zwssgr_slider_content3  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider4" id="zwssgr-slider4">
						<div class="zwssgr-slider-4">
							' . (($post_count > 0) ? $zwssgr_slider_content4  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider5" id="zwssgr-slider5">
						<div class="zwssgr-slider-5">
							' . (($post_count > 0) ? $zwssgr_slider_content5  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider6" id="zwssgr-slider6">
						<div class="zwssgr-slider-6">
							' . (($post_count > 0) ? $zwssgr_slider_content6  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
						</div>
					</div>'
				],
				'grid' => [
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid1" id="zwssgr-grid1">
						' . (($post_count > 0) ? $zwssgr_grid_content1  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid2" id="zwssgr-grid2">
						' . (($post_count > 0) ? $zwssgr_grid_content2  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid3" id="zwssgr-grid3">
						' . (($post_count > 0) ? $zwssgr_grid_content3  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid4" id="zwssgr-grid4">
						' . (($post_count > 0) ? $zwssgr_grid_content4  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-grid-item zwssgr-grid5" id="zwssgr-grid5">
						' . (($post_count > 0) ? $zwssgr_grid_content5  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>'
				],
				'list' => [
					'<div class="zwssgr-slider zwssgr-list zwssgr-list1" id="zwssgr-list1">
						' . (($post_count > 0) ? $zwssgr_list_content1  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list2" id="zwssgr-list2">
						' . (($post_count > 0) ? $zwssgr_list_content2  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list3" id="zwssgr-list3">
						' . (($post_count > 0) ? $zwssgr_list_content3  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list4" id="zwssgr-list4">
						' . (($post_count > 0) ? $zwssgr_list_content4  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>',
					'<div class="zwssgr-slider zwssgr-list zwssgr-list5" id="zwssgr-list5">
						' . (($post_count > 0) ? $zwssgr_list_content5  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
					</div>'
				],
				'badge' => [
					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge1" id="zwssgr-badge1">
						<h3 class="zwssgr-average">Good</h3>
						' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwssgr-based-on">Based on <b>  '.$zwssgr_reviews_ratings['reviews'].' reviews </b></p>
						<img src="' . $plugin_dir_path . 'assets/images/google.png" alt="Google">
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge2" id="zwssgr-badge2">
						<div class="zwssgr-badge-image">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
						</div>
						<div class="zwssgr-badge-info">
							<h3 class="zwssgr-average">Good</h3>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' reviews</b></p>
						</div>
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge3" id="zwssgr-badge3">
						<div class="zwssgr-rating-wrap">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge4" id="zwssgr-badge4">
						<div class="zwssgr-badge4-rating">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwssgr-badge4-info">
							<h3 class="zwssgr-google">Google</h3>
							<p class="zwssgr-avg-note">Average Rating</p>
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
						</div>
					</div></a>',
					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge5" id="zwssgr-badge5">
						<div class="zwssgr-badge5-rating">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
						</div>
						<div class="zwssgr-badge5-info">
							<h3 class="zwssgr-google">Google</h3>
							<p class="zwssgr-avg-note">Average Rating</p>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge6" id="zwssgr-badge6">
						<div class="zwssgr-badge6-rating">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwssgr-badge6-info">
							<h3 class="zwssgr-google">Google</h3>
							<p class="zwssgr-avg-note">Average Rating</p>
						</div>
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge7" id="zwssgr-badge7">
						<img src="' . $plugin_dir_path . 'assets/images/review-us.png" alt="Review Us">
						<div class="zwssgr-badge7-rating">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge8" id="zwssgr-badge8">
						<div class="zwssgr-logo-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
							<p class="zwssgr-avg-note">Google Reviews</p>
						</div>
						<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
						' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' reviews</b></p>
					</div></a>',

					'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link"><div class="zwssgr-badge-item zwssgr-badge9" id="zwssgr-badge9">
						<div class="zwssgr-badge-image">
							<img src="' . esc_url($image_url) . '" alt="Profile Logo">
						</div>
						<div class="zwssgr-badge-info">
							<h3 class="zwssgr-average">' . $zwssgr_location_name .'</h3>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' reviews</b></p>
						</div>
					</div></a>',
				],
				'popup' => [
					'<div class="zwssgr-popup-item zwssgr-popup1" id="zwssgr-popup1" data-popup="zwssgrpopup1">
						<div class="zwssgr-profile-logo">
							<img src="' . esc_url($image_url) . '" alt="Profile Logo">
						</div>
						<div class="zwssgr-profile-info">
							<h3>'.$zwssgr_location_name.'</h3>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-total-review"> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</a>
						</div>
					</div>
					<div id="zwssgrpopup1" class="zwssgr-popup-overlay zwssgrpopup1">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($post_count > 0) ? $zwssgr_popup_content1  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
								</div>
							</div>
						</div>
					</div>',
					'<div class="zwssgr-popup-item zwssgr-popup2" id="zwssgr-popup2"  data-popup="zwssgrpopup2">
						<div class="zwssgr-title-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/google.png" alt="Google">
							<h3>Reviews</h3>
						</div>
						<div class="zwssgr-info-wrap">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" 	class="zwssgr-total-review">(  '.$zwssgr_reviews_ratings['reviews'].' reviews )</a>
						</div>
					</div>
					<div id="zwssgrpopup2" class="zwssgr-popup-overlay zwssgrpopup2">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($post_count > 0) ? $zwssgr_popup_content2  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
								</div>
							</div>
						</div>
					</div>'
				]
			];
			?>
			
			<div class="zwssgr-dashboard">

				<h1 class="zwssgr-page-title">Widget Configuration</h1>


				<!-- Tab Navigation -->
				<ul class="tab-nav zwssgr-custom-tab">
					<li class="tab-item zwssgr-tab-item active done" data-tab="tab-fetch-data"><span class="zwssgr-step">1. </span>Fetch Data</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item  <?php echo ($layout_option) ? 'done' : ''; ?>" data-tab="tab-options"><span class="zwssgr-step">2. </span>Select Display Options</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item <?php echo ($current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-selected"><span class="zwssgr-step">3. </span>Selected Option</li>
					<span class="zwssgr-step-arrow"></span>
					<li class="tab-item zwssgr-tab-item <?php echo ($current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-shortcode"><span class="zwssgr-step">4. </span>Generated Shortcode</li>
				</ul>

				<!-- Tab Data Fetch Areas -->
				<div class="tab-content" id="tab-fetch-data">
					<?php zwssgr_Google_My_Business_Connector::get_instance()->zwssgr_fetch_gmb_data_callback(); ?>
				</div>

				<!-- Tab Content Areas -->
				<div class="tab-content" id="tab-options">
					<!-- Dynamically Render Radio Buttons -->
					<div class="zwssgr-layout-radio"> 
						<label><input type="radio" name="display_option" class="zwssgr-radio" value="all" checked> <span>All</span></label>
						<?php foreach ($options as $key => $layouts) : ?>
							<label><input type="radio" name="display_option" class="zwssgr-radio" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($key); ?>"><span for="<?php echo esc_attr($key); ?>"> <?php echo esc_html(ucfirst($key)); ?></span></label>
						<?php endforeach; ?>
					</div>

					<!-- Dynamically Render Layout Options Based on Selected Display Option -->
					<div id="layout-options">
						<?php
						foreach ($options as $option_type => $layouts) {
							$layout_count = 1;
							if( $option_type == "badge") {
								echo '<div class="zwssgr-badge-wrap">';
							}
							if( $option_type == "popup") {
								echo '<div class="zwssgr-popup-wrap">';
							}
							foreach ($layouts as $layout_content) { 
								$element_id = $option_type . '-' . $layout_count;

								// Only show layouts for the selected display option
								$display_style = ($selected_display_option === $option_type || $selected_display_option === 'all') ? 'block' : 'block';
								$selected_class = ($element_id === $layout_option) ? ' selected' : ''; // Check if this layout is selected
								
								echo '<div id="' . esc_attr($element_id) . '" class="zwssgr-option-item' . esc_attr($selected_class) . '" data-type="' . esc_attr($option_type) . '" style="display: ' . esc_attr($display_style) . ';">';
									echo '<div class="zwssgr-layout-title-wrap">';
										echo '<h3 class="zwssgr-layout-title">Layout: '. esc_html($option_type) .' '.esc_html($layout_count).'</h3>';
										echo '<button class="select-btn zwssgr-btn" data-option="' . esc_attr($element_id) . '"' . ($element_id === $selected_layout_option ? ' selected' : '') . '>Select Option</button>';
									echo '</div>';
									$allowed_html = wp_kses_allowed_html('post');

									// Add SVG support
									$allowed_html['svg'] = [
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

									$allowed_html['path'] = [
										'd' => true,
										'fill' => true,
										'class' => true,
									];

									$allowed_html['g'] = [
										'fill' => true,
										'stroke' => true,
										'stroke-width' => true,
										'class' => true,
									];

									echo wp_kses($layout_content, $allowed_html);
								echo '</div>';
								

								$layout_count++;
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

				<div class="tab-content zwssgr-tab-content-display" id="tab-selected">
					<h3>Selected Option</h3>
					<div id="selected-option-display" class="selected-option-display"></div>
					<div class="zwssgr-toogle-display">
							<a href="<?php echo esc_url($zwssgr_location_new_review_uri); ?>" style="background-color:<?php echo esc_attr($bg_color); ?>; color:<?php echo esc_attr($text_color); ?>;" class="zwssgr-google-toggle" target="_blank">Review Us On G</a>
					</div>
					<?php if (!in_array($layout_option, ['badge-1', 'badge-2', 'badge-3', 'badge-4', 'badge-5', 'badge-6', 'badge-7', 'badge-8', 'badge-9'])): ?>
						<div class="zwssgr-widget-settings">
							<h2 class="zwssgr-page-title">Widget Settings</h2>
							<div class="zwssgr-widget-wrap">
								<div class="zwssgr-widget-setting">
										<h3 class="zwssgr-label">Filter Rating</h3>
										<div class="filter-rating">
											<?php
											for ($i = 1; $i <= 5; $i++) {
												$selected = ($i <= $rating_filter) ? 'selected' : '';  // Check if the current star is selected
												$fillColor = ($i <= $rating_filter) ? '#FFD700' : '#ccc'; // Color for selected and non-selected stars
												?>
												<span class="star-filter <?php echo esc_attr($selected); ?>" data-rating="<?php echo esc_attr($i); ?>" title="<?php echo esc_attr($i); ?> Star">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path class="star" d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="<?php echo esc_attr($fillColor); ?>" />
													</svg>
												</span>
												<?php
											}
											?>
										</div>
								</div>


								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Hide Element</h3>
									<ul class="zwssgr-widget-eleemt-list">
										<li>
											<input type="checkbox" id="review-title" class="zwssgr-checkbox" name="review-element" value="review-title" 
											<?php echo in_array('review-title', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-title" class="zwssgr-chechbox-label">Reviewer Name</label>
										</li>
										<li>
											<input type="checkbox" id="review-rating" class="zwssgr-checkbox" name="review-element" value="review-rating" 
											<?php echo in_array('review-rating', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-rating" class="zwssgr-chechbox-label">Rating</label>
										</li>
										<li>
											<input type="checkbox" id="review-days-ago" class="zwssgr-checkbox" name="review-element" value="review-days-ago" 
											<?php echo in_array('review-days-ago', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-days-ago" class="zwssgr-chechbox-label">Date</label>
										</li>
										<li>
											<input type="checkbox" id="review-content" class="zwssgr-checkbox" name="review-element" value="review-content" 
											<?php echo in_array('review-content', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-content" class="zwssgr-chechbox-label">Review Content</label>
										</li>
										<li>
											<input type="checkbox" id="review-photo" class="zwssgr-checkbox" name="review-element" value="review-photo" 
											<?php echo in_array('review-photo', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-photo" class="zwssgr-chechbox-label">Reviewer Photo</label>
										</li>
										<li>
											<input type="checkbox" id="review-g-icon" class="zwssgr-checkbox" name="review-element" value="review-g-icon" 
											<?php echo in_array('review-g-icon', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-g-icon" class="zwssgr-chechbox-label">G Icon</label>
										</li>
										<!-- Add more elements as needed -->
									</ul>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Keywords</h3>
									<input type="text" id="keywords-input" name="keywords-input" class="zwssgr-input-text" placeholder="e.g., keyword1, keyword2, keyword3">
									<p class="zwssgr-description">Type keywords separated by commas</p>

									<!-- Hidden input field to store comma-separated keywords for submission -->
									<input type="hidden" id="keywords-input-hidden" name="keywords_input_hidden" value="">

									<!-- Display the list of saved keywords -->
									<?php
									$keywords = get_post_meta($post_id, 'keywords', true); // Retrieves the array of keywords
									if (is_array($keywords) && !empty($keywords)) {
										echo '<div id="keywords-list" class="keywords-list">';
										foreach ($keywords as $keyword) {
											echo '<div class="keyword-item">' . esc_html($keyword) . '<span class="remove-keyword"> ✖</span></div>';
										}
										echo '</div>';
									} else {
										echo '<div id="keywords-list" class="keywords-list"></div>';
									}
									?>

									<div id="error-message" class="error-message zwssgr-keyword-error-message">
										You can only enter a maximum of 5 keywords.
									</div> 
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Review us on Google</h3>
									<label class="switch zwssgr-switch">
										<input type="checkbox" id="toggle-google-review" name="google_review_toggle" <?php echo ($google_review_toggle) ? 'checked' : ''; ?>>
										<span class="slider zwssgr-toggle-slider"></span>
									</label>

									<div id="color-picker-options" style="display: <?php echo ($google_review_toggle) ? 'flex' : 'none'; ?>" class="zwssgr-color-options">
										<div class="zwssgr-color-picker">
											<label for="bg-color-picker" class="zwssgr-chechbox-label">Background Color:</label>
											<input type="color" id="bg-color-picker" name="bg_color_picker" value="<?php echo esc_attr($bg_color ? $bg_color : '#3780ff'); ?>">
										</div>
										<div class="zwssgr-color-picker">
											<label for="text-color-picker" class="zwssgr-chechbox-label">Text Color:</label>
											<input type="color" id="text-color-picker" name="text_color_picker" value="<?php echo esc_attr($text_color ? $text_color : '#ffffff'); ?>">
										</div>
									</div>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Trim long reviews with a "read more" link</h3>
									<input type="number" class="zwssgr-input-text" id="review-char-limit" name="review-char-limit" min="10" placeholder="Enter character limit" value="<?php echo !empty($char_limit) ? esc_attr($char_limit) : ''; ?>">
									<p id="char-limit-error" class="error-message"></p>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Language</h3>
									<select id="language-select" name="language" class="zwssgr-input-text">
										<option value="en" <?php echo ($language === 'en') ? 'selected' : ''; ?>>English</option>
										<option value="es" <?php echo ($language === 'es') ? 'selected' : ''; ?>>Spanish</option>
										<option value="fr" <?php echo ($language === 'fr') ? 'selected' : ''; ?>>French</option>
										<option value="de" <?php echo ($language === 'de') ? 'selected' : ''; ?>>German</option>
										<option value="it" <?php echo ($language === 'it') ? 'selected' : ''; ?>>Italian</option>
										<option value="pt" <?php echo ($language === 'pt') ? 'selected' : ''; ?>>Portuguese</option>
										<option value="ru" <?php echo ($language === 'ru') ? 'selected' : ''; ?>>Russian</option>
										<option value="zh" <?php echo ($language === 'zh') ? 'selected' : ''; ?>>Chinese</option>
										<option value="ja" <?php echo ($language === 'ja') ? 'selected' : ''; ?>>Japanese</option>
										<option value="hi" <?php echo ($language === 'hi') ? 'selected' : ''; ?>>Hindi</option>
										<option value="ar" <?php echo ($language === 'ar') ? 'selected' : ''; ?>>Arabic</option>
										<option value="ko" <?php echo ($language === 'ko') ? 'selected' : ''; ?>>Korean</option>
										<option value="tr" <?php echo ($language === 'tr') ? 'selected' : ''; ?>>Turkish</option>
										<option value="bn" <?php echo ($language === 'bn') ? 'selected' : ''; ?>>Bengali</option>
										<option value="ms" <?php echo ($language === 'ms') ? 'selected' : ''; ?>>Malay</option>
										<option value="nl" <?php echo ($language === 'nl') ? 'selected' : ''; ?>>Dutch</option>
										<option value="pl" <?php echo ($language === 'pl') ? 'selected' : ''; ?>>Polish</option>
										<option value="sv" <?php echo ($language === 'sv') ? 'selected' : ''; ?>>Swedish</option>
										<option value="th" <?php echo ($language === 'th') ? 'selected' : ''; ?>>Thai</option>
										<!-- Add more languages as needed -->
									</select>
								</div>

								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Sort By</h3>
									<select id="sort-by-select" name="sort_by" class="zwssgr-input-text">
										<option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest</option>
										<option value="highest" <?php echo ($sort_by === 'highest') ? 'selected' : ''; ?>>Highest Rating</option>
										<option value="lowest" <?php echo ($sort_by === 'lowest') ? 'selected' : ''; ?>>Lowest Rating</option>
									</select>
									<div class="zwssgr-sort-by-checkbox">
											<input type="checkbox" class="zwssgr-checkbox" id="enable-sort-by-filter" name="enable_sort_by" <?php echo ($enable_sort_by ? 'checked' : ''); ?> />
											<label for="enable-sort-by-filter" class="zwssgr-chechbox-label">Do you want to show "Sort By" filter on front side?</label>
									</div>
								</div>
		
								<div class="zwssgr-widget-setting">
									<h3 class="zwssgr-label">Date Format</h3>
									<select id="date-format-select" name="date-format" class="zwssgr-input-text">
										<option value="DD/MM/YYYY" <?php echo ($date_format === 'DD/MM/YYYY') ? 'selected' : ''; ?>>DD/MM/YYYY</option>
										<option value="MM-DD-YYYY" <?php echo ($date_format === 'MM-DD-YYYY') ? 'selected' : ''; ?>>MM-DD-YYYY</option>
										<option value="YYYY/MM/DD" <?php echo ($date_format === 'YYYY/MM/DD') ? 'selected' : ''; ?>>YYYY/MM/DD</option>
										<option value="full" <?php echo ($date_format === 'full') ? 'selected' : ''; ?>>Full Date (e.g., January 1, 2024)</option>
										<option value="hide" <?php echo ($date_format === 'hide') ? 'selected' : ''; ?>>Hide</option>
									</select>
								</div>
								<?php

									if ($current_tab2 == '' && $enable_load_more == '') {
										$is_checked = 'checked';
									} else if ($current_tab2 == 'tab-selected' && $enable_load_more === true) {
										$is_checked = 'checked';
									} else {
										$enable_background_color = '';
										$is_checked = '';
									}

									
								?>
								<div class="zwssgr-widget-setting">
									<?php 
									$layout_option = get_post_meta($post_id, 'layout_option', true);
									// Check if layout option is not in 'slider-1' to 'slider-6'
									$exclude_slider_options = array('slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6');
									if (!in_array($layout_option, $exclude_slider_options)) :
									?>
										<div class="zwssgr-load-more-wrapper">
											<h3 class="zwssgr-label">Load More</h3>
											<label class="switch zwssgr-switch">
												<input type="checkbox" id="enable-load-more" name="enable_load_more" <?php echo ($enable_load_more ? 'checked' : ''); echo esc_attr($is_checked);?> />
												<span class="slider zwssgr-toggle-slider"></span>
											</label>
											<div id="zwssgr-load-color-picker-options" style="display: <?php echo ($enable_load_more) ? 'flex' : 'none'; ?>" class="zwssgr-color-options_load">
												<div class="zwssgr-color-picker-load">
													<label for="bg-color-picker_load" class="zwssgr-chechbox-label">Background Color:</label>
													<input type="color" id="bg-color-picker_load" name="bg_color_picker_load" value="<?php echo esc_attr($bg_color_load ? $bg_color_load : '#000000'); ?>">
												</div>
												<div class="zwssgr-color-picker-load">
													<label for="text-color-picker_load" class="zwssgr-chechbox-label">Text Color:</label>
													<input type="color" id="text-color-picker_load" name="text_color_picker_load" value="<?php echo esc_attr($text_color_load ? $text_color_load : '#ffffff'); ?>">
												</div>
											</div>
										</div>
									
									<?php endif; ?>

									<div id="load-more-settings">
										<h3 class="zwssgr-label">Reviews Per Page for List, Grid, and Popup:</h3>
										<div class="zwssgr-tooltip">
											<input type="number" id="posts-per-page" name="posts_per_page" class="zwssgr-input-text" value="<?php echo esc_attr($posts_per_page); ?>" min="10" max="100" step="1" onchange="this.value = Math.max(10, Math.min(100, this.value));">
											<span class="zwssgr-tooltip-container">
												<div class="zwssgr-wrapper">
													<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 30 30">
													<path d="M15,3C8.373,3,3,8.373,3,15c0,6.627,5.373,12,12,12s12-5.373,12-12C27,8.373,21.627,3,15,3z M16,21h-2v-7h2V21z M15,11.5 c-0.828,0-1.5-0.672-1.5-1.5s0.672-1.5,1.5-1.5s1.5,0.672,1.5,1.5S15.828,11.5,15,11.5z"></path></svg>
													<span class="zwssgr-tooltip-text">We recommend a maximum of 100 reviews for the best experience.</span>
												</div>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				
					<div class="zwssgr-widget-settings">
						<h2 class="zwssgr-page-title">Custom CSS Support</h2>
						<textarea class="zwssgr-textarea" rows="5" cols="40" placeholder="Enter your custom CSS here"><?php echo esc_textarea($custom_css); ?></textarea>
					</div>
					<button id="save-get-code-btn" class="zwssgr-btn" data-zwssgr-btn='zwssgr-btn'>
						<?php echo !empty($current_tab2) ? 'Update' : 'Save & Get Code'; ?>
					</button>
				</div>

				<div class="tab-content zwssgr-tab-content-display" id="tab-shortcode">
					<h3>Generated Shortcode</h3>
					<div id="generated-shortcode-display" class="generated-shortcode-display">
						<div class="zwssgr-shortcode">
							<input type="text" class="zwssgr-input-text zwssgr-shortcode-input" value="<?php echo esc_attr($generated_shortcode); ?>" readonly id="shortcode-<?php echo esc_attr($post_id); ?>">
							<span class="dashicons dashicons-admin-page zwssgr-copy-shortcode-icon" data-target="shortcode-<?php echo esc_attr($post_id); ?>" title="<?php echo esc_attr__('Copy Shortcode', 'smart-showcase-for-google-reviews'); ?>"></span>
						</div>
					</div>
				</div>
			</div>

			<?php
		}

		// Handle AJAX Request to Save Dashboard Data
		function save_widget_data() 
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
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			if (!$post_id) {
				$this->zwssgr_debug_function('Invalid post ID');
				wp_send_json_error(esc_html__('Invalid post ID.', 'smart-showcase-for-google-reviews'));
				return;
			}
			$this->zwssgr_debug_function('Post ID: ' . $post_id);

			// Check if the post exists
			if (get_post_status($post_id) === false) {
				$this->zwssgr_debug_function('Post does not exist: ' . $post_id);
				wp_send_json_error(esc_html__('Post does not exist.', 'smart-showcase-for-google-reviews'));
				return;
			}
			$this->zwssgr_debug_function('Post exists, ID: ' . $post_id);

			// Ensure user has permission to edit the post
			if (!current_user_can('edit_post', $post_id)) {
				$this->zwssgr_debug_function('User does not have permission to edit post: ' . $post_id);
				wp_send_json_error(esc_html__('You do not have permission to edit this post.', 'smart-showcase-for-google-reviews'));
				return;
			}
			
			$setting_tb = ( isset( $_POST['settings'] ) && ! empty( $_POST['settings'] ) ) ? sanitize_text_field( wp_unslash( $_POST['settings'] ) ) : '';

			if(  $setting_tb == 'tab-options' ){
			$display_option = isset( $_POST['display_option'] ) ? sanitize_text_field( wp_unslash( $_POST['display_option'] ) ) : get_post_meta( $post_id, 'display_option', true );
			update_post_meta($post_id, 'display_option', $display_option);	

			$layout_option = isset( $_POST['layout_option'] ) ? sanitize_text_field( wp_unslash( $_POST['layout_option'] ) ) : get_post_meta( $post_id, 'layout_option', true );
			update_post_meta($post_id, 'layout_option', $layout_option);
			
			$current_tab = isset( $_POST['current_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['current_tab'] ) ) : '';
			update_post_meta($post_id, 'tab-options', $current_tab); // Save the active tab state

			}

			else if(  $setting_tb == 'tab-selected' ){
				$selected_elements = isset( $_POST['selected_elements'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['selected_elements'] ) ) : array();
				$keywords = isset( $_POST['keywords'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['keywords'] ) ) : [];
				$date_format = isset( $_POST['date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['date_format'] ) ) : '';
				$char_limit = isset($_POST['char_limit']) ? intval(wp_unslash($_POST['char_limit'])) : '';
				$language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
				$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
				$enable_load_more = isset( $_POST['enable_load_more'] ) ? intval( wp_unslash( $_POST['enable_load_more'] ) ) : 0;
				$google_review_toggle = isset( $_POST['google_review_toggle'] ) ? intval( wp_unslash( $_POST['google_review_toggle'] ) ) : 0;
				$bg_color = isset( $_POST['bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['bg_color'] ) ) : '';
				$text_color = isset( $_POST['text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color'] ) ) : '';
				$bg_color_load = isset( $_POST['bg_color_load'] ) ? sanitize_hex_color( wp_unslash( $_POST['bg_color_load'] ) ) : '';
				$text_color_load = isset( $_POST['text_color_load'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color_load'] ) ) : '';
				$posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( wp_unslash( $_POST['posts_per_page'] ) ) : 10; // Default to 10
				$rating_filter = isset( $_POST['rating_filter'] ) ? intval( wp_unslash( $_POST['rating_filter'] ) ) : 0;
				$custom_css = isset( $_POST['custom_css'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_css'] ) ) : '';
				$current_tab2 = sanitize_text_field( wp_unslash( $_POST['settings'] ) ); // The active tab
				$enable_sort_by = isset( $_POST['enable_sort_by'] ) ? intval( wp_unslash( $_POST['enable_sort_by'] ) ) : 0;

				update_post_meta($post_id, 'tab-selected', $current_tab2); // Save the active tab state
				update_post_meta($post_id, 'selected_elements', $selected_elements);
				update_post_meta($post_id, 'rating_filter', $rating_filter);
				update_post_meta($post_id, 'keywords', $keywords);
				update_post_meta($post_id, 'date_format', $date_format);
				update_post_meta($post_id, 'char_limit', $char_limit);
				update_post_meta($post_id, 'language', $language);
				update_post_meta($post_id, 'sort_by', $sort_by);
				update_post_meta($post_id, 'enable_load_more', $enable_load_more);
				update_post_meta($post_id, 'google_review_toggle', $google_review_toggle);
				update_post_meta($post_id, 'bg_color', $bg_color);
				update_post_meta($post_id, 'text_color', $text_color);
				update_post_meta($post_id, 'bg_color_load', $bg_color_load);
				update_post_meta($post_id, 'text_color_load', $text_color_load);
				update_post_meta($post_id, 'posts_per_page', $posts_per_page);
				update_post_meta($post_id, '_zwssgr_custom_css', $custom_css);
				update_post_meta($post_id, 'enable_sort_by', $enable_sort_by);
			}
		
			// Respond with success message
			wp_send_json_success('Settings updated successfully.' . $setting_tb );
		}

		function generate_shortcode($post_id) 
		{
			// Build the shortcode with attributes
			$shortcode = '[zwssgr_widget post-id="' . esc_attr($post_id) . '"]';
			update_post_meta($post_id, '_generated_shortcode_new', $shortcode);
			return $shortcode;	
		}

		function filter_reviews_ajax_handler() {
			
			$post_id = isset($_POST['zwssgr_widget_id']) ? sanitize_text_field(wp_unslash($_POST['zwssgr_widget_id'])) : '';

			$post_objct = get_post($post_id);
			if (!isset($post_id) || !$post_objct ) {
				wp_die( 'Invalid post ID.' ) ;
			}

			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'filter_reviews_nonce')) {
				die(esc_html__('Permission Denied', 'smart-showcase-for-google-reviews'));
			}
			
		
			// Check if rating_filter is set and is an array
			if (!isset($_POST['rating_filter']) || !is_array($_POST['rating_filter'])) {
				die('Invalid filter value');
			}
		
			$rating_filter = array_map('intval', $_POST['rating_filter']); // Ensure all values are integers
			$sort_by = isset($_POST['sort_by']) ? sanitize_text_field(wp_unslash($_POST['sort_by'])) : 'newest';
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$language = get_post_meta($post_id, 'language', true);
			$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
			$zwssgr_location_all_review_uri =  get_post_meta($post_id, 'zwssgr_location_all_review_uri', true);
			$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
			$zwssgr_location_thumbnail_url = get_post_meta($post_id, 'zwssgr_location_thumbnail_url', true);
			$image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $plugin_dir_path . 'assets/images/Google_G_Logo.png';
			$rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Create an array of string values based on the selected numeric ratings
			$rating_strings = array();
			foreach ($rating_filter as $filter) {
				if (isset($rating_mapping[$filter])) {
					$rating_strings[] = $rating_mapping[$filter];
				}
			}
		
			if (empty($rating_strings)) {
				echo 'Invalid rating';
				die();
			}
		
			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($post_id, 'zwssgr_account_number', true);
			$zwssgr_account_location =get_post_meta($post_id, 'zwssgr_location_number', true);
			$layout_option = get_post_meta($post_id, 'layout_option', true);


			// Query reviews with the selected string-based filters
			$args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE, // Replace with your custom post type
				'posts_per_page' => 5,
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are met
					array(
						'key'     => 'zwssgr_review_star_rating',
						'value'   => $rating_strings,
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
				$args['meta_query'][] = array(
					'key'     => 'zwssgr_account_number',
					'value'   => (string) $zwssgr_account_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			if (!empty($zwssgr_account_location)) {
				$args['meta_query'][] = array(
					'key'     => 'zwssgr_location_number',
					'value'   => (string) $zwssgr_account_location,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}			
			// Add sorting logic
			switch ($sort_by) {
				case 'newest':
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
					break;

				case 'highest':
					// Sort from FIVE to ONE
					add_filter('posts_orderby', function ($orderby, $query) use ($rating_mapping) {
						global $wpdb;
						if ($query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$custom_order = "'" . implode("','", array_reverse($rating_mapping)) . "'";
							$orderby = "FIELD({$wpdb->postmeta}.meta_value, $custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $orderby;
					}, 10, 2);
					break;

				case 'lowest':
					// Sort from ONE to FIVE
					add_filter('posts_orderby', function ($orderby, $query) use ($rating_mapping) {
						global $wpdb;
						if ($query->get('post_type') === ZWSSGR_POST_REVIEW_TYPE) {
							$custom_order = "'" . implode("','", $rating_mapping) . "'";
							$orderby = "FIELD({$wpdb->postmeta}.meta_value, $custom_order) ASC, {$wpdb->posts}.post_date DESC";
						}
						return $orderby;
					}, 10, 2);
					break;

				default:
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
		
			$reviews_query = new WP_Query($args);
			$post_count = $reviews_query->found_posts;
			$reviews_html ='';    
			$zwssgr_location_name = get_post_meta($post_id, 'zwssgr_location_name', true);

			
			if ($reviews_query->have_posts()) {
				while ($reviews_query->have_posts()) {
					$reviews_query->the_post();
					$zwssgr_reviewer_name = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
					$zwssgr_review_content = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
					$zwssgr_review_star_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
					$zwssgr_review_id= get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
					$zwssgr_gmb_reviewer_image_path = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$zwssgr_gmb_reviewer_image_uri  = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
					$published_date = get_the_date('F j, Y');
					$months = $this->translate_months($language);
					// Determine if content is trimmed based on character limit
					$is_trimmed = $char_limit > 0 && mb_strlen($zwssgr_review_content) > $char_limit; // Check if the content length exceeds the character limit
					$trimmed_content = $is_trimmed ? mb_substr($zwssgr_review_content, 0, $char_limit) . '...' : $zwssgr_review_content; // Trim the content if necessary

					$formatted_date = '';
					$timestamp = strtotime($published_date); // Calculate the timestamp once for better performance

					if ($date_format === 'DD/MM/YYYY') {
						$formatted_date = gmdate('d/m/Y', $timestamp);
					} elseif ($date_format === 'MM-DD-YYYY') {
						$formatted_date = gmdate('m-d-Y', $timestamp);
					} elseif ($date_format === 'YYYY/MM/DD') {
						$formatted_date = gmdate('Y/m/d', $timestamp);
					} elseif ($date_format === 'full') {
						$day = gmdate('j', $timestamp);
						$month = $months[(int)gmdate('n', $timestamp) - 1];
						$year = gmdate('Y', $timestamp);

						// Construct the full date
						$formatted_date = "$month $day, $year";
					} elseif ($date_format === 'hide') {
						$formatted_date = ''; // No display for "hide"
					}

		
					// Map textual rating to numeric values
					$rating_map = [
						'ONE'   => 1,
						'TWO'   => 2,
						'THREE' => 3,
						'FOUR'  => 4,
						'FIVE'  => 5,
					];

					// Convert the textual rating to numeric
					$numeric_rating = isset($rating_map[$zwssgr_review_star_rating]) ? $rating_map[$zwssgr_review_star_rating] : 0;

					// Generate stars HTML
					$stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$stars_html .= $i < $numeric_rating 
							? '<span class="zwssgr-star filled">★</span>' 
							: '<span class="zwssgr-star">☆</span>';
					}
				
			

					// Format the slider item for each review
					$zwssgr_slider_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">
									<div class="zwssgr-profile">
										'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
									</div>
									<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									<div class="zwssgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
								' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
							</div>
						</div>';

						$zwssgr_slider_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-rating-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_slider_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item5 = '
							<div class="zwssgr-slide-item">
								<div class="">
									<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'	
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									<div class="zwssgr-contnt-wrap">
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '	
										' . (!empty($published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item6 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_list_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
						
						$zwssgr_list_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwssgr_list_item3= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_list_item4= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											<div class="zwssgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
											</div>
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
										</div>
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';

						$zwssgr_list_item5= '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-list-wrap5">
										<div class="zwssgr-prifile-wrap">
											<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											</div>
											<div class="zwssgr-data">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											</div>
										</div>
										<div class="zwssgr-content-wrap">
											<div class="zwssgr-review-info">
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
												<div class="zwssgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
												</div>
											</div>
											' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
											: '') . '</p>' : '' ) . '	
										</div>
									</div>
								</div>
							</div>';

						$zwssgr_grid_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_grid_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											<div class="zwssgr-date-wrap">
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
												' . (!empty($published_date)
												? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											</div>
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_grid_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-review-detail">
											<div class="zwssgr-profile">
												'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
											</div>
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
												? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
											<div class="zwssgr-rating-wrap">
												<div class="zwssgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
												</div>
												' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
											</div>
										</div>
										' . ( !empty($trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwssgr_grid_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>									
									' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($published_date)
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';
						
						$zwssgr_grid_item5 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
												' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_popup_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_popup_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-profile">
											'.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name).'">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name).'">').'
										</div>
										<div class="zwssgr-review-info">
											' . (!empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h3>' : '') . '
										</div>
										<div class="zwssgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png" alt="Google Icon">
										</div>
									</div>
									<div class="zwssgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwssgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwssgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
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
				if ($layout_option != "popup-1" && $layout_option != "popup-2") {
					echo '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>';
				}				
				
			}

			
			$zwssgr_slider_content1 = implode('', (array) $zwssgr_slider_content1);
			$zwssgr_slider_content2 = implode('', (array) $zwssgr_slider_content2);
			$zwssgr_slider_content3 = implode('', (array) $zwssgr_slider_content3);
			$zwssgr_slider_content4 = implode('', (array) $zwssgr_slider_content4);
			$zwssgr_slider_content5 = implode('', (array) $zwssgr_slider_content5);
			$zwssgr_slider_content6 = implode('', (array) $zwssgr_slider_content6);

			$zwssgr_list_content1 = implode('', (array) $zwssgr_list_content1);
			$zwssgr_list_content2 = implode('', (array) $zwssgr_list_content2);
			$zwssgr_list_content3 = implode('', (array) $zwssgr_list_content3);
			$zwssgr_list_content4 = implode('', (array) $zwssgr_list_content4);
			$zwssgr_list_content5 = implode('', (array) $zwssgr_list_content5);

			$zwssgr_grid_content1 = implode('', (array) $zwssgr_grid_content1);
			$zwssgr_grid_content2 = implode('', (array) $zwssgr_grid_content2);
			$zwssgr_grid_content3 = implode('', (array) $zwssgr_grid_content3);
			$zwssgr_grid_content4 = implode('', (array) $zwssgr_grid_content4);
			$zwssgr_grid_content5 = implode('', (array) $zwssgr_grid_content5);

			$zwssgr_popup_content1 = implode('', (array) $zwssgr_popup_content1);
			$zwssgr_popup_content2 = implode('', (array) $zwssgr_popup_content2);

			$zwssgr_gmb_account_number = get_post_meta($post_id, 'zwssgr_account_number', true);
			$zwssgr_gmb_account_location =get_post_meta($post_id, 'zwssgr_location_number', true);

			$zwssgr_filter_data = [
				'zwssgr_gmb_account_number'   => $zwssgr_gmb_account_number,
				'zwssgr_gmb_account_location' => $zwssgr_gmb_account_location,
				'zwssgr_range_filter_type'    => '',
				'zwssgr_range_filter_data'    => ''
			];

			$zwssgr_data_render_args = $this->zwssgr_dashboard->zwssgr_data_render_query($zwssgr_filter_data);		
			$zwssgr_reviews_ratings = $this->zwssgr_dashboard->zwssgr_get_reviews_ratings($zwssgr_data_render_args);
			$widthPercentage = $zwssgr_reviews_ratings['ratings'] * 20;

			$final_rating = ' <div class="zwssgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwssgr-final-review-fill" style="width: ' . $widthPercentage . '%;">
					<svg width="100" height="20" viewBox="0 0 100 20" className="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M30.0001 15.5616L23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616Z" fill="#f39c12" />
						<path d="M50.0001 15.5616L43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616Z" fill="#f39c12" />
						<path d="M70.0004 15.5619L63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619Z" fill="#f39c12" />
						<path d="M10.0001 15.5616L3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616Z" fill="#f39c12" />
						<path d="M90.0004 15.5619L83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619Z" fill="#f39c12" />
					</svg>
				</div>
			</div>';


			$filter_layout = [
				'slider' => [
					'<div class="zwssgr-slider zwssgr-slider1" id="zwssgr-slider1">
						<div class="zwssgr-slider-1">
							' . $zwssgr_slider_content1 . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider2" id="zwssgr-slider2">
						<div class="zwssgr-slider-2">
							' . $zwssgr_slider_content2 . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider3" id="zwssgr-slider3">
						<div class="zwssgr-slider-badge">
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
								<div class="zwssgr-badge-item" id="zwssgr-badge1">
									<h3 class="zwssgr-average">Good</h3>
									' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
									<p class="zwssgr-based-on">Based on <b>  '.$zwssgr_reviews_ratings['reviews'].' reviews </b></p>
									<img src="' . $plugin_dir_path . 'assets/images/google.png" alt="Google">
								</div>
							</a>
						</div>
						<div class="zwssgr-slider-3">
							' . $zwssgr_slider_content3 . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider4" id="zwssgr-slider4">
						<div class="zwssgr-slider-4">
							' . $zwssgr_slider_content4 . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider5" id="zwssgr-slider5">
						<div class="zwssgr-slider-5">
							' . $zwssgr_slider_content5 . '
						</div>
					</div>',
					'<div class="zwssgr-slider zwssgr-slider6" id="zwssgr-slider6">
						<div class="zwssgr-slider-6">
							' . $zwssgr_slider_content6 . '
						</div>
					</div>'
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
							<img src="' . esc_url($image_url) . '" alt="Profile Logo">
						</div>
						<div class="zwssgr-profile-info">
							<h3>'.$zwssgr_location_name.'</h3>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-total-review"> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</a>
						</div>
					</div>
					<div id="zwssgrpopup1" class="zwssgr-popup-overlay">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($post_count > 0) ? $zwssgr_popup_content1  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
								</div>
							</div>
						</div>
					</div>',
					'<div class="zwssgr-popup-item zwssgr-popup2" id="zwssgr-popup2"  data-popup="zwssgrpopup2">
						<div class="zwssgr-title-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/google.png" alt="Google">
							<h3>Reviews</h3>
						</div>
						<div class="zwssgr-info-wrap">
							<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" 	class="zwssgr-total-review">(  '.$zwssgr_reviews_ratings['reviews'].' reviews )</a>
						</div>
					</div>
					<div id="zwssgrpopup2" class="zwssgr-popup-overlay">
						<div class="zwssgr-popup-content">
							<div class="scrollable-content">
								<span class="zwssgr-close-popup">&times;</span>
								<div class="zwssgr-popup-wrap">
									<div class="zwssgr-profile-logo">
										<img src="' . esc_url($image_url) . '" alt="Profile Logo">
									</div>
									<div class="zwssgr-profile-info">
										<h3>'.$zwssgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwssgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">Based on <b> '.$zwssgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
									' . (($post_count > 0) ? $zwssgr_popup_content2  : '<p class="zwssgr-no-found-message">No reviews found for the selected ratings</p>') . '
								</div>
							</div>
						</div>
					</div>'
				]
			];
			
			$layout_option = get_post_meta($post_id, 'layout_option', true);
			$layout_option_divide = explode('-', $layout_option);
			
			$layout_option_key = $layout_option_divide[0]; 
			$layout_option_value = $layout_option_divide[1];
			$reviews_html = $filter_layout[$layout_option_key][$layout_option_value-1];

			if($post_count > 0){
				echo '<h3 class="zwssgr-layout-title">Layout: ' . esc_html($layout_option_key) . ' ' . esc_html($layout_option_value) . '</h3>';
			}

			// Return the filtered reviews HTML as the response
			echo wp_kses_post($reviews_html);
			die();
		}		
	}
}
