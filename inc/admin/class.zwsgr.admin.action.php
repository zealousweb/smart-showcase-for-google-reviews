<?php
/**
 * ZWSGR_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Admin_Action' ) ){

	/**
	 *  The ZWSGR_Admin_Action Class
	 */
	class ZWSGR_Admin_Action {

		private $client;

		private $zwsgr_gmbc;

		private $zwsgr_dashboard;

		function __construct()  
		{

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );
			add_action('admin_menu', array($this, 'zwsgr_admin_menu_registration'));
			add_action('admin_init', array($this, 'zwsgr_register_settings'));
			add_action('init', array($this, 'zwsgr_register_widget_cpt'));
			add_action('load-post-new.php', array($this, 'action__custom_widget_url_on_add_new'));
			add_action('init', array($this, 'zwsgr_register_review_cpt'));

			add_action('add_meta_boxes', array($this, 'zwsgr_add_review_meta_box'));
			add_action('init', array($this, 'zwsgr_register_request_data_cpt'));
			add_action('add_meta_boxes', array($this, 'zwsgr_add_account_number_meta_box'));

			add_filter('manage_' . ZWSGR_POST_REVIEW_TYPE . '_posts_columns', array($this, 'filter__zwsgr_manage_data_posts_columns'), 10, 3);
			add_action('manage_' . ZWSGR_POST_REVIEW_TYPE . '_posts_custom_column', array($this, 'render_hide_column_content'), 10, 2);
			add_action('wp_ajax_toggle_visibility', array($this, 'zwsgr_toggle_visibility'));

			add_action('wp_ajax_save_widget_data',array($this, 'save_widget_data'));
			add_action('wp_ajax_nopriv_save_widget_data', array($this, 'save_widget_data'));

			add_action('wp_ajax_filter_reviews', array($this,'filter_reviews_ajax_handler'));
			add_action('wp_ajax_nopriv_filter_reviews', array($this,'filter_reviews_ajax_handler'));

			add_filter('manage_' . ZWSGR_POST_WIDGET_TYPE . '_posts_columns', array($this,'zwsgr_add_shortcode_column'));
			add_action('manage_' . ZWSGR_POST_WIDGET_TYPE . '_posts_custom_column', array($this,'zwsgr_populate_shortcode_column'), 10, 2);

			add_action('restrict_manage_posts', array( $this,'zwsgr_add_custom_meta_filters'));
			add_action('pre_get_posts', array( $this,'zwsgr_filter_posts_by_custom_meta'));

			// Initialize dashboard class
			$this->zwsgr_dashboard = ZWSGR_Dashboard::get_instance();
		
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
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-min-js', ZWSGR_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-js', ZWSGR_URL . 'assets/js/admin.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// Enqueue Moment.js (required by daterangepicker)

			wp_enqueue_script( ZWSGR_PREFIX . '-moment-min-js', ZWSGR_URL . 'assets/js/moment.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// Enqueue Daterangepicker JS
			wp_enqueue_script( ZWSGR_PREFIX . '-daterangepicker-min-js', ZWSGR_URL . 'assets/js/daterangepicker.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// admin css
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-min-css', ZWSGR_URL . 'assets/css/admin.min.css', array(), ZWSGR_VERSION );
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-css', ZWSGR_URL . 'assets/css/admin.css', array(), ZWSGR_VERSION );	
			
			// style css
			wp_enqueue_style( ZWSGR_PREFIX . '-style-css', ZWSGR_URL . 'assets/css/style.css', array(), ZWSGR_VERSION );

			// Enqueue Daterangepicker CSS

			wp_enqueue_style( ZWSGR_PREFIX . '-daterangepicker-css', ZWSGR_URL . 'assets/css/daterangepicker.css', array(), ZWSGR_VERSION );
		
			// Slick js
			wp_enqueue_script( ZWSGR_PREFIX . '-slick-min-js', ZWSGR_URL . 'assets/js/slick.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
			
			// Slick css
			wp_enqueue_style( ZWSGR_PREFIX . '-slick-css', ZWSGR_URL . 'assets/css/slick.css', array(), ZWSGR_VERSION );

			//Toggle Ajax
			wp_localize_script(ZWSGR_PREFIX . '-admin-js', 'zwsgr_admin', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('toggle-visibility-nonce')
			));

			//Toggle Ajax
			wp_localize_script(ZWSGR_PREFIX . '-admin-js', 'zwsgr_admin', array(
				'ajax_url' 					    => admin_url('admin-ajax.php'),
				'nonce' 					    => wp_create_nonce('toggle-visibility-nonce'),
				'zwsgr_queue_manager_nounce'    => wp_create_nonce('zwsgr_queue_manager_nounce'),
				'zwsgr_delete_oauth_connection' => wp_create_nonce('zwsgr_delete_oauth_connection'),
				'zwsgr_add_update_reply_nonce'  => wp_create_nonce('zwsgr_add_update_reply_nonce'),
				'zwsgr_delete_review_reply'	    => wp_create_nonce('zwsgr_delete_review_reply'),
				'zwsgr_gmb_dashboard_filter'	=> wp_create_nonce('zwsgr_gmb_dashboard_filter'),
				'zwsgr_data_render'				=> wp_create_nonce('zwsgr_data_render'),
				'zwsgr_wp_review_id' 		    => ( is_admin() && isset( $_GET['post'] ) ) ? $_GET['post'] : 0,
			));

			//Save Widget Ajax
			wp_localize_script(ZWSGR_PREFIX . '-admin-js', 'my_widget', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('my_widget_nonce'),
            ));

			wp_localize_script(ZWSGR_PREFIX . '-admin-js', 'filter_reviews', array(
				'ajax_url' => admin_url('admin-ajax.php'), // The AJAX handler URL
				'nonce' => wp_create_nonce('filter_reviews_nonce') // Security nonce
			));
		
		}

		/**
		 * Action: admin_menu
		 * Used for Add Menu Page
		 * @method action_admin_menu
		 */
		function zwsgr_admin_menu_registration()
		{

			add_menu_page(
				__('Smart Google Reviews', 'zw-smart-google-reviews'),
				__('Smart Google Reviews', 'zw-smart-google-reviews'),
				'manage_options',
				'zwsgr_dashboard',
				array($this, 'zwsgr_dashboard_callback'),
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
				'zwsgr_dashboard',
				__('Widget', 'zw-smart-google-reviews'),
				__('Widgets', 'zw-smart-google-reviews'),
				'manage_options',
				'edit.php?post_type=' . ZWSGR_POST_WIDGET_TYPE,
				null
			);

			add_submenu_page(
				'zwsgr_dashboard',
				__('Review', 'zw-smart-google-reviews'),
				__('Reviews', 'zw-smart-google-reviews'),
				'manage_options',
				'edit.php?post_type=' . ZWSGR_POST_REVIEW_TYPE,
				null
			);

			add_submenu_page(
                null,
                'Widget Configurator',             
                'Widget Configurator',
                'manage_options',
                'zwsgr_widget_configurator',
                [$this, 'zwsgr_widget_configurator_callback']
            );

			add_submenu_page(
				'zwsgr_dashboard',
				__('Settings', 'zw-smart-google-reviews'),
				__('Settings', 'zw-smart-google-reviews'),
				'manage_options',
				'zwsgr_settings',
				array($this, 'zwsgr_settings_callback')
			);
		}

		// Register Custom Post Type: Widget
		function zwsgr_register_widget_cpt()
		{
			$labels = array(
				'name' => _x('Widgets', null, 'zw-smart-google-reviews'),
				'singular_name' => _x('Widget', null, 'zw-smart-google-reviews'),
				'menu_name' => _x('Widgets', 'admin menu', 'zw-smart-google-reviews'),
				'name_admin_bar' => _x('Widget', 'add new on admin bar', 'zw-smart-google-reviews'),
				'add_new' => _x('Add New', 'widget', 'zw-smart-google-reviews'),
				'add_new_item' => __('Add New Widget', 'zw-smart-google-reviews'),
				'new_item' => __('New Widget', 'zw-smart-google-reviews'),
				'edit_item' => __('Edit Widget', 'zw-smart-google-reviews'),
				'view_item' => __('View Widget', 'zw-smart-google-reviews'),
				'all_items' => __('All Widgets', 'zw-smart-google-reviews'),
				'search_items' => __('Search Widgets', 'zw-smart-google-reviews'),
				'not_found' => __('No widgets found.', 'zw-smart-google-reviews'),
				'not_found_in_trash' => __('No widgets found in Trash.', 'zw-smart-google-reviews')
			);

			$args = array(
				'label' => __('Widgets', 'zw-smart-google-reviews'),
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

			register_post_type(ZWSGR_POST_WIDGET_TYPE, $args);
		}

		function action__custom_widget_url_on_add_new() {

			$zwsgr_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
		
			if ($zwsgr_post_type === 'zwsgr_data_widget') {
		
				$zwsgr_widget_id = wp_insert_post([
					'post_type'   => 'zwsgr_data_widget',
					'post_status' => 'publish',
					'post_title'  => 'Auto-Created Widget ' . wp_generate_uuid4(),
				]);
			
				if (!$zwsgr_widget_id || is_wp_error($zwsgr_widget_id)) {
					error_log('Failed to create a new widget');
					return;
				}
		
				$zwsgr_redirect_url = admin_url('admin.php?page=zwsgr_widget_configurator&tab=tab-fetch-data&zwsgr_widget_id=' . $zwsgr_widget_id);
				wp_redirect($zwsgr_redirect_url);
				exit;
		
			}
		
		}

		function zwsgr_register_review_cpt()
		{
			$labels = array(
				'name' => _x('Reviews', 'zw-smart-google-reviews'),
				'singular_name' => _x('Review', 'zw-smart-google-reviews'),
				'menu_name' => _x('Reviews', 'admin menu', 'zw-smart-google-reviews'),
				'name_admin_bar' => _x('Review', 'add new on admin bar', 'zw-smart-google-reviews'),
				'add_new' => _x('Add New', 'review', 'zw-smart-google-reviews'),
				'add_new_item' => __('Add New Review', 'zw-smart-google-reviews'),
				'new_item' => __('New Review', 'zw-smart-google-reviews'),
				'edit_item' => __('Edit Review', 'zw-smart-google-reviews'),
				'view_item' => __('View Review', 'zw-smart-google-reviews'),
				'all_items' => __('All Reviews', 'zw-smart-google-reviews'),
				'search_items' => __('Search Reviews', 'zw-smart-google-reviews'),
				'not_found' => __('No Reviews found.', 'zw-smart-google-reviews'),
				'not_found_in_trash' => __('No Reviews found in Trash.', 'zw-smart-google-reviews')
			);

			$args = array(
				'label' => __('Reviews', 'zw-smart-google-reviews'),
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

			register_post_type(ZWSGR_POST_REVIEW_TYPE, $args);

		}

		// Register a single meta box to display all review details
		function zwsgr_add_review_meta_box() 
		{
			remove_meta_box(
				'submitdiv', 
				'zwsgr_reviews', 
				'side'
			);
			add_meta_box(
				'zwsgr_review_details_meta_box',
				__('Review Details', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_display_review_details_meta_box'),
				'zwsgr_reviews',
				'normal',
				'high'
			);
		}

		// Display all review details in one meta box
		function zwsgr_display_review_details_meta_box($zwsgr_review) 
		{

			$zwsgr_review_published_date = get_the_date( 'F j, Y g:i A', $zwsgr_review->ID );
			$zwsgr_review_comment	  	 = get_post_meta($zwsgr_review->ID, 'zwsgr_review_comment', true);
			$zwsgr_reviewer_name 	  	 = get_post_meta($zwsgr_review->ID, 'zwsgr_reviewer_name', true);
			$zwsgr_review_star_rating 	 = get_post_meta($zwsgr_review->ID, 'zwsgr_review_star_rating', true);
			$zwsgr_reply_comment 	  	 = get_post_meta($zwsgr_review->ID, 'zwsgr_reply_comment', true);
			$zwsgr_reply_update_time  	 = get_post_meta($zwsgr_review->ID, 'zwsgr_reply_update_time', true);

			// Define the SVG for filled and empty stars
			$zwsgr_filled_star = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="orange" class="bi bi-star-fill" viewBox="0 0 16 16">
			<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
			</svg>';

			$zwsgr_empty_star = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="orange" stroke-width="1" class="bi bi-star" viewBox="0 0 16 16">
			<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
			</svg>';

			// Map the rating value (in words) to numerical equivalents
			$zwsgr_rating_map = [
				'FIVE' => 5,
				'FOUR' => 4,
				'THREE' => 3,
				'TWO' => 2,
				'ONE' => 1,
				'ZERO' => 0,
			];

			$zwsgr_rating_value 	  	  = strtoupper($zwsgr_review_star_rating);
			$numeric_rating 		  	  = isset($zwsgr_rating_map[$zwsgr_rating_value]) ? $zwsgr_rating_map[$zwsgr_rating_value] : 0;
			$zwsgr_filled_star 		  	  = str_repeat($zwsgr_filled_star, $numeric_rating);
			$zwsgr_empty_star 		  	  = str_repeat($zwsgr_empty_star, 5 - $numeric_rating);
			$zwsgr_review_id 		  	  = get_post_meta($zwsgr_review->ID, 'zwsgr_review_id', true);
			$zwsgr_gmb_reviewer_image_uri = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';

			echo '<table class="form-table test" id="gmb-review-data" zwsgr-review-id="'.$zwsgr_review->ID.'">
				<tr>
					<th>
						<label for="zwsgr_reviewer_image">' . __('', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>';
						if (!empty($zwsgr_gmb_reviewer_image_uri)) {
							echo '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">';
						} else {
							echo '<img src="' . ZWSGR_URL . '/assets/images/fallback-user-dp.svg" class="fallback-user-dp">';
						}
					echo '</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_reviewer_name">' . __('Reviewer', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_reviewer_name) . '" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
								<tr>
					<th>
						<label for="zwsgr_review_published_date">' . __('Publish Date', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_review_published_date) . '" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_comment">' . __('Review Content', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<textarea readonly class="regular-text" rows="5" style="width:100%;">' . esc_textarea($zwsgr_review_comment) . '</textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_star_rating">' . __('Star Ratings', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<div class="zwsgr-star-ratings"> ' . $zwsgr_filled_star . $zwsgr_empty_star . ' </div>
					</td>
					<td><div class="separator"></div></td>
				</tr>
				<tr>
					<th class="separator-cell" colspan="2">
						<hr class="separator">
					</th>
				</tr>';
				if (!empty($zwsgr_reply_update_time)) {
					$zwsgr_datetime = new DateTime($zwsgr_reply_update_time);
					$zwsgr_formatted_time = $zwsgr_datetime->format('F j, Y g:i A');
					echo '<tr>
						<th>
							<label for="zwsgr_reply_update_time">' . __('Reply Update Time', 'zw-smart-google-reviews') . '</label>
						</th>
						<td>
							<input type="text" value="' . esc_attr($zwsgr_formatted_time) . '" readonly class="regular-text" style="width:100%;" />
						</td>
					</tr>';

				}
				echo '<tr>
					<th>
						<label for="zwsgr_reply_comment"> ' . __('Reply Content', 'zw-smart-google-reviews') . ' </label>
					</th>
					<td>
						<div id="json-response-message" style="margin-bottom: 10px; color: green;"></div>
						<textarea name="zwsgr_reply_comment" class="regular-text" rows="5" style="width: 100%;">'. esc_textarea($zwsgr_reply_comment) .'</textarea>
						<div class="cta-wrapper">';
							if (!empty($zwsgr_reply_comment)) {
								echo '<button class="button button-primary button-large zwsgr-submit-btn" id="update-reply"> ' . __('Update', 'zw-smart-google-reviews') . ' </button>';
							} else {
								echo '<button class="button button-primary button-large zwsgr-submit-btn" id="add-reply"> ' . __('Add Reply', 'zw-smart-google-reviews') . ' </button>';
							}
							if (!empty($zwsgr_reply_comment)) {
								echo '<button class="button button-danger button-large zwsgr-submit-btn" id="delete-reply">' . __('Delete', 'zw-smart-google-reviews') . '</button>';
							}
						echo '</div>
					</td>
				</tr>
			</table>';
			
		}

		function zwsgr_register_request_data_cpt()
		{
			$labels = array(
				'name'               => __('Request Data', 'zw-smart-google-reviews'),
				'singular_name'      => __('Request Data', 'zw-smart-google-reviews'),
				'menu_name'          => __('Request Data', 'zw-smart-google-reviews'),
				'name_admin_bar'     => __('Request Data', 'zw-smart-google-reviews'),
				'add_new'            => __('Add New', 'zw-smart-google-reviews'),
				'add_new_item'       => __('Add New Request Data', 'zw-smart-google-reviews'),
				'new_item'           => __('New Request Data', 'zw-smart-google-reviews'),
				'edit_item'          => __('Edit Request Data', 'zw-smart-google-reviews'),
				'view_item'          => __('View Request Data', 'zw-smart-google-reviews'),
				'all_items'          => __('All Request Data', 'zw-smart-google-reviews'),
				'search_items'       => __('Search Request Data', 'zw-smart-google-reviews'),
				'not_found'          => __('No Request Data found.', 'zw-smart-google-reviews'),
				'not_found_in_trash' => __('No Request Data found in Trash.', 'zw-smart-google-reviews')
			);

			$args = array(
				'label'               => __('Request Data', 'zw-smart-google-reviews'),
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
					'slug' => 'zwsgr_request_data',
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

			register_post_type('zwsgr_request_data', $args);  // Updated post type name to zwsgr_request_data
		}

		function zwsgr_add_custom_meta_filters() 
		{
			global $wpdb;

			// Get current post type
			$current_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';

			// Check if we are on the correct post type pages
			if (!in_array($current_post_type, [ZWSGR_POST_REVIEW_TYPE, ZWSGR_POST_WIDGET_TYPE])) {
				return;
			}

			// Get saved email and selected filters from URL parameters
			$zwsgr_gmb_email = get_option('zwsgr_gmb_email');
			$selected_account = isset($_GET['zwsgr_account']) ? sanitize_text_field($_GET['zwsgr_account']) : '';
			$selected_location = isset($_GET['zwsgr_location']) ? sanitize_text_field($_GET['zwsgr_location']) : '';

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
					WHERE pm2.post_id = p.ID AND pm2.meta_key = 'zwsgr_gmb_email' AND pm2.meta_value = %s
				)
			", 'zwsgr_account_number', 'zwsgr_request_data', $zwsgr_gmb_email);

			$accounts = $wpdb->get_results($accounts_query);

			// Begin the form
			echo '<form method="GET">';
			echo '<input type="hidden" name="post_type" value="' . esc_attr($current_post_type) . '">';

			// Account dropdown
			echo '<select id="zwsgr-account-select" name="zwsgr_account" style="margin-right: 10px;">';
			echo '<option value="">Select an Account</option>';
			foreach ($accounts as $account) {
				$selected = ($account->account_number === $selected_account) ? ' selected' : '';
				echo '<option value="' . esc_attr($account->account_number) . '"' . $selected . '>' . esc_html($account->account_name) . '</option>';
			}
			echo '</select>';

			// Location dropdown
			echo '<select id="zwsgr-location-select" name="zwsgr_location">';
			echo '<option value="">Select a Location</option>';

			if ($selected_account) {
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
						WHERE pm2.post_id = p.ID AND pm2.meta_key = 'zwsgr_account_number' AND pm2.meta_value = %s
					)
				", 'zwsgr_account_locations', 'zwsgr_request_data', $selected_account);

				$locations = $wpdb->get_results($locations_query);

				// Parse and output location options
				foreach ($locations as $location) {
					$location_data = maybe_unserialize($location->location_data);
					if (is_array($location_data)) {
						foreach ($location_data as $loc) {
							$loc_title = $loc['title'] ?? '';
							$loc_value = ltrim(strrchr($loc['name'], '/'), '/');
							$selected = ($loc_value === $selected_location) ? ' selected' : '';
							echo '<option value="' . esc_attr($loc_value) . '"' . $selected . '>' . esc_html($loc_title) . '</option>';
						}
					}
				}
			}
			echo '</select>';
			echo '</form>';
		}
		

		// Filter posts based on custom meta for ZWSGR_POST_REVIEW_TYPE
		function zwsgr_filter_posts_by_custom_meta($query)
		{
			global $pagenow;

			if (is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && in_array($_GET['post_type'], [ZWSGR_POST_REVIEW_TYPE, ZWSGR_POST_WIDGET_TYPE])) {
				$meta_query = array();

				if (isset($_GET['zwsgr_account']) && !empty($_GET['zwsgr_account'])) {
					$meta_query[] = [
						'key'     => 'zwsgr_account_number',
						'value'   => (string) $_GET['zwsgr_account'],
						'compare' => '='
					];
				}

				if (isset($_GET['zwsgr_location']) && !empty($_GET['zwsgr_location'])) {
					$meta_query[] = [
						'key'     => 'zwsgr_location_number',
						'value'   => (string) $_GET['zwsgr_location'],
						'compare' => '='
					];
				}

				if (!empty($meta_query)) {
					$query->set('meta_query', [
						'relation' => 'AND',
						...$meta_query
					]);
				}
			}
		}

		// Register the meta box
		function zwsgr_add_account_number_meta_box() 
		{
			add_meta_box(
				'zwsgr_account_number_meta_box', // Meta box ID
				__('Account Number', 'zw-smart-google-reviews'), // Title
				array($this, 'zwsgr_display_account_number_meta_box'), // Callback function to display the meta box content
				'zwsgr_request_data', // Post type
				'normal', // Context (where to display: 'normal', 'side', 'advanced')
				'high' // Priority (high, default, low)
			);
		}

		// Add the custom "Shortcode" column
		function zwsgr_add_shortcode_column($columns) 
		{
			$new_columns = array();
			foreach ($columns as $key => $title) {
				if ($key === 'title') {
					$new_columns[$key] = $title; // Keep the Title column
					$new_columns['shortcode'] = __('Shortcode', 'zw-smart-google-reviews'); // Add Shortcode column after Title
				} else {
					$new_columns[$key] = $title; // Add other columns
				}
			}
			return $new_columns;
		}


		function zwsgr_populate_shortcode_column($column, $post_id) 
		{
			if ($column === 'shortcode') {
				// Check if the "tab-selected" metadata exists and meets a specific condition
				$current_tab2 = get_post_meta($post_id, 'tab-selected', true); 
				
				if ($current_tab2) { // Or use any specific condition for `$current_tab2`
					// Generate the shortcode
					$shortcode = sprintf('[zwsgr_widget post-id="%d"]', $post_id);
					
					// Display the shortcode and copy icon
					echo '<div style="display: flex; align-items: center;">';
					echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly style="margin-right: 10px; width: auto;" id="shortcode-' . $post_id . '">';
					echo '<span class="dashicons dashicons-admin-page copy-shortcode-icon" data-target="shortcode-' . $post_id . '" style="cursor: pointer;" title="' . __('Copy Shortcode', 'zw-smart-google-reviews') . '"></span>';
					echo '</div>';
				} else {
					// Optionally, you can display a message or leave it blank if the condition is not met
					echo '<span>' . __('Please select the appropriate options', 'zw-smart-google-reviews') . '</span>';
				}
			}
		}

		/**
		 * Filter: manage_zwsgr_reviews_posts_columns
		 *
		 * - Used to add new column fields for the "zwsgr_reviews" CPT
		 *
		 * @method filter__zwsgr_manage_zwsgr_reviews_posts_columns
		 *
		 * @param  array $columns
		 *
		 * @return array
		 */
		function filter__zwsgr_manage_data_posts_columns($columns)
		{
			unset($columns['date']);
			unset($columns['title']);
			$columns['title'] = __('Review', 'zw-smart-google-reviews');
			$columns[ZWSGR_META_PREFIX . 'user_login'] = __('Hide', 'zw-smart-google-reviews');
			$columns['date'] = __('Date', 'zw-smart-google-reviews');
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
			if ( $column === ZWSGR_META_PREFIX . 'user_login' ) {
				$is_hidden = get_post_meta( $post_id, '_is_hidden', true );
				$icon = $is_hidden ? 'hidden' : 'visibility';

				// Display the toggle button with the current state
				echo '<a href="#" class="zwsgr-toggle-visibility" data-post-id="' . esc_attr( $post_id ) . '">';
				echo '<span class="dashicons dashicons-' . esc_attr( $icon ) . '"></span>';
				echo '</a>';
			}
		}

		/**
		 * Toggle visibility (AJAX callback)
		 */
		function zwsgr_toggle_visibility() 
		{
			check_ajax_referer( 'toggle-visibility-nonce', 'nonce' );
		
			$post_id = intval( $_POST['post_id'] );
		
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
		 * @method zwsgr_register_settings
		 */
		function zwsgr_register_settings()
		{

			//Google account settings
			register_setting('zwsgr_google_account_settings', 'zwsgr_google_business_api_key');
			register_setting('zwsgr_google_account_settings', 'zwsgr_google_business_place_id');

			// SEO & Notifications Settings
			register_setting('zwsgr_notification_settings', 'zwsgr_admin_notification_enabled');
			register_setting('zwsgr_notification_settings', 'zwsgr_google_analytics_tracking_id');

			//Advance Setting
			register_setting('zwsgr_advanced_account_settings', 'zwsgr_sync_reviews');
			register_setting('zwsgr_advanced_account_settings', 'zwsgr_google_analytics_tracking_id');

			// Google setting section & fields
			add_settings_section(
				'zwsgr_google_section',
				__('Google Settings', 'zw-smart-google-reviews'),
				null,
				'zwsgr_google_account_settings'
			);

			add_settings_field(
				'zwsgr_google_api_key',
				__('Google Business API Key', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_google_api_key_callback'),
				'zwsgr_google_account_settings',
				'zwsgr_google_section'
			);

			add_settings_field(
				'zwsgr_google_place_id',
				__('Google Place ID', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_google_place_id_callback'),
				'zwsgr_google_account_settings',
				'zwsgr_google_section'
			);

			// Notification section & fields
			add_settings_section(
				'zwsgr_notification_section',
				__('SEO & Notifications', 'zw-smart-google-reviews'),
				null,
				'zwsgr_notification_settings'
			);

			add_settings_field(
				'zwsgr_admin_notification_enabled',
				__('Enable Admin Notifications', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_admin_notification_enabled_callback'),
				'zwsgr_notification_settings',
				'zwsgr_notification_section'
			);

			// Add settings for the Advanced tab
			add_settings_section(
				'zwsgr_advanced_section',
				__('Advanced Settings', 'zw-smart-google-reviews'),
				null,
				'zwsgr_advanced_account_settings'
			);

			add_settings_field(
				'zwsgr_sync_reviews',
				__('Sync Reviews', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_sync_reviews_callback'),
				'zwsgr_advanced_account_settings',
				'zwsgr_advanced_section'
			);

			add_settings_field(
				'zwsgr_google_analytics_tracking_id',
				__('Google Analytics Tracking ID', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_google_analytics_tracking_id_callback'),
				'zwsgr_advanced_account_settings',
				'zwsgr_advanced_section'
			);

		}

	

		// Google API Key callback
		function zwsgr_google_api_key_callback()
		{
			$value = get_option('zwsgr_google_business_api_key', '');
			echo '<input type="text" id="zwsgr_google_business_api_key" name="zwsgr_google_business_api_key" class="zwsgr-input-text" value="' . esc_attr($value) . '" />';
		}

		function zwsgr_google_place_id_callback()
		{
			$value = get_option('zwsgr_google_business_place_id', '');
			echo '<input type="text" id="zwsgr_google_business_place_id" name="zwsgr_google_business_place_id" class="zwsgr-input-text" value="' . esc_attr($value) . '" />';
		}

		// Notifications callback
		function zwsgr_admin_notification_enabled_callback()
		{
			$value = get_option('zwsgr_admin_notification_enabled', '0');
			echo '<label class="switch">';
			echo '<input type="checkbox" id="zwsgr_admin_notification_enabled" name="zwsgr_admin_notification_enabled" value="1" ' . checked(0, $value, false) . ' />';
			echo '<span class="slider"></span>';
			echo '</label>';
		}

		function zwsgr_admin_notification_emails_callback()
		{
			$value = get_option('zwsgr_admin_notification_emails', '');
			echo '<div class="zwsgr-notification-field">';
			echo '<div class="zwsgr-th-label">';
			echo '<label class="zwsgr-th">Custom Email Addresses</label>';
			echo '</div>';
			echo '<div class="zwsgr-field">';
			echo '<input type="text" id="zwsgr_admin_notification_emails" name="zwsgr_admin_notification_emails" class="zwsgr-input-text" rows="5" cols="50" value="' . esc_attr($value) . '" />';
			echo '<p class="zwsgr-description">' . __('Enter email addresses separated by commas.', 'zw-smart-google-reviews') . '</p>';
			echo '</div>';
			echo '</div>';
		}

		function zwsgr_admin_notification_emails_subject_callback()
		{
			$value = get_option('zwsgr_admin_notification_emails_subject', '');
			echo '<div class="zwsgr-notification-field">';
			echo '<div class="zwsgr-th-label">';
			echo '<label class="zwsgr-th">Custom Email Subject</label>';
			echo '</div>';
			echo '<div class="zwsgr-field">';
			echo '<input type="text" id="zwsgr_admin_notification_emails_subject" class="zwsgr-input-text" name="zwsgr_admin_notification_emails_subject" rows="5" cols="50" value="' . esc_attr(
				$value) . '" />';
			echo '</div>';
			echo '</div>';
		}

		function zwsgr_admin_notofication_email_body_callback() {
			// Get the current value from the database
			$value = get_option('zwsgr_admin_notification_email_body', '');
		
			// Set up the WYSIWYG editor settings
			$settings = array(
				'textarea_name' => 'zwsgr_admin_notification_email_body',
				'editor_class'  => 'zwsgr_email_body_editor', // Add a custom class if needed
				'media_buttons' => false, // Set to true to show media buttons
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,unlink,|,removeformat', // Customize toolbar buttons
				),
				'textarea_rows' => 10, // Set the number of rows for the editor
			);
			
			echo '<div class="zwsgr-editor-wrapper">';
				echo '<div class="zwsgr-th-label">';
				echo '<label for="zwsgr_admin_notification_email_body" class="zwsgr-th">' . __('Email Body', 'zw-smart-google-reviews') . '</label>';
				echo '</div>';
				echo '<div class="zwsgr-field">';
				wp_editor($value, 'zwsgr_admin_notification_email_body', $settings);
				echo '<p class="zwsgr-description">' . __('Enter your custom email body content here.', 'zw-smart-google-reviews') . '</p>';
				echo '</div>';
			echo '</div>';
		}

		//Advance Section 
		function zwsgr_sync_reviews_callback()
		{
			$value = get_option('zwsgr_sync_reviews', 'daily');
			echo '<select id="zwsgr_sync_reviews" name="zwsgr_sync_reviews" class="zwsgr-input-text">
					<option value="daily" ' . selected($value, 'daily', false) . '>Daily</option>
					<option value="weekly" ' . selected($value, 'weekly', false) . '>Weekly</option>
					<option value="monthly" ' . selected($value, 'monthly', false) . '>Monthly</option>
				</select>';
		}

		function zwsgr_google_analytics_tracking_id_callback()
		{
			$value = get_option('zwsgr_google_analytics_tracking_id', '');
			echo '<input type="text" id="zwsgr_google_analytics_tracking_id" class="zwsgr-input-text" name="zwsgr_google_analytics_tracking_id" value="' . esc_attr($value) . '" />';
			echo '<p class="zwsgr-description">' . __('Enter your Google Analytics Tracking ID (e.g., UA-XXXXXXXXX-Y).', 'zw-smart-google-reviews') . '</p>';
		}

		/**
		 * Action: action_admin_menu
		 * Add admin registration settings page for the plugin
		 * @method zwsgr_setting_page
		 */
		function zwsgr_settings_callback()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			// Show error messages for both settings groups
			settings_errors('zwsgr_google_account_settings');
			settings_errors('zwsgr_advanced_account_settings');
			settings_errors('zwsgr_settings&tab=notifications');

		
			// Handle form submission (send email)
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if (isset($_POST['zwsgr_admin_notification_emails'])) {
					// Sanitize and save the form values
					$emails = sanitize_text_field($_POST['zwsgr_admin_notification_emails']);
					$subject = sanitize_text_field($_POST['zwsgr_admin_notification_emails_subject']);
					$body = wp_kses_post($_POST['zwsgr_admin_notification_email_body']); // Use wp_kses_post for rich text
		
					// Update the options (only update the subject and body; leave email field empty after submission)
					update_option('zwsgr_admin_notification_emails_subject', $subject);
					update_option('zwsgr_admin_notification_email_body', $body);
		
					// Prepare email
					$to = explode(',', $emails); // Assume emails are comma-separated
					$message = $body;
					$headers = array('Content-Type: text/html; charset=UTF-8');
		
					// Send the email using wp_mail()
					if (!empty($emails)) {
						$mail_sent = wp_mail($to, $subject, $message, $headers);
		
						// Check if email was sent successfully
						if ($mail_sent) {
							add_settings_error('zwsgr_notification_settings', 'settings_updated', 'Emails sent successfully.', 'updated');
						} else {
							add_settings_error('zwsgr_notification_settings', 'settings_error', 'Failed to send email.', 'error');
						}
					} else {
						add_settings_error('zwsgr_notification_settings', 'settings_error', 'No email addresses provided.', 'error');
					}
		
					// Clear only the email field after submission
					update_option('zwsgr_admin_notification_emails', '');
				}
				if (isset($_POST['advance_submit_buttons'])){
					add_settings_error('zwsgr_advanced_account_settings', 'settings_updated', 'Advanced settings saved successfully!', 'updated');
				}
				
			}
			
			// Now render the form and tabs
			$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'google';
			?>
			<div class="wrap">
				<h1 class="zwsgr-page-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
				<div class="zwsgr-section-wrap">
					<h2 class="nav-tab-wrapper zwsgr-nav-tab-wrapper">
						<a href="?page=zwsgr_settings&tab=google" class="nav-tab <?php echo ($current_tab === 'google') ? 'nav-tab-active' : ''; ?>">
							<?php _e('Google', 'zw-smart-google-reviews'); ?>
						</a>
						<a href="?page=zwsgr_settings&tab=notifications" class="nav-tab <?php echo ($current_tab === 'notifications') ? 'nav-tab-active' : ''; ?>">
							<?php _e('SEO & Notifications', 'zw-smart-google-reviews'); ?>
						</a>
						<a href="?page=zwsgr_settings&tab=advanced" class="nav-tab <?php echo ($current_tab === 'advanced') ? 'nav-tab-active' : ''; ?>">
							<?php _e('Advanced', 'zw-smart-google-reviews'); ?>
						</a>
					</h2>
					<?php if ($current_tab === 'google'): ?>
						<?php /* <form action="" method="post" class="zwsgr-setting-form">
							<?php
							settings_fields('zwsgr_google_account_settings');
							do_settings_sections('zwsgr_google_account_settings');
							submit_button( 'Save Google Settings', 'zwsgr-submit-btn', 'submit_buttons' );
							
							?>
						</form> */?>
					<?php elseif ($current_tab === 'notifications'): ?>
						<form id="notification-form" action="" method="post" class="zwsgr-setting-form">
							<?php
							// Display WordPress admin notices
    						settings_errors('zwsgr_notification_settings');
							settings_fields('zwsgr_notification_settings');
							do_settings_sections('zwsgr_notification_settings');
							?>
							<div class="zwsgr-notification-fields">
								<?php $this->zwsgr_admin_notification_emails_callback(); ?>
								<?php $this->zwsgr_admin_notification_emails_subject_callback(); ?>
								<?php $this->zwsgr_admin_notofication_email_body_callback(); ?>
							</div>
							<!-- Error message container -->
							<span id="email-error" style="color: red; display: none;"></span>
							<!-- Success message container -->
							<span id="email-success" style="color: green; display: none;"></span>
							<?php 
							submit_button( 'Send Notification Emails', 'zwsgr-submit-btn', 'submit_buttons' );

							?>
						</form>
					<?php elseif ($current_tab === 'advanced'): ?>
						<form action="" method="post" class="zwsgr-setting-form">
							<?php
							settings_errors('zwsgr_advanced_account_settings');
							settings_fields('zwsgr_advanced_account_settings');
							do_settings_sections('zwsgr_advanced_account_settings');
							submit_button( 'Save Advanced Settings', 'zwsgr-submit-btn', 'advance_submit_buttons' );

							?>
						</form>
					<?php endif; ?>
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
		 * @method zwsgr_dashboard_page
		 */
		function zwsgr_dashboard_callback()
		{	
			echo '<div class="zwgr-dashboard">
				<div class="zwgr-dashboard-header">'
					. $this->zwsgr_dashboard->zwsgr_date_range_filter() .
				'</div>'
				. $this->zwsgr_dashboard->zwsgr_data_render() .
			'</div>';
		}
		
		/**
		 * Action: action_admin_menu
		 * Add admin registration Dashboard page for the plugin
		 * @method zwsgr_dashboard_page
		 */

		function zwsgr_widget_configurator_callback() 
		{

			$post_id = $_GET['zwsgr_widget_id'];
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
			$selected_display_option = !empty($display_option) ? $display_option : 'all'; // Default to 'all'
			$selected_layout_option = !empty($layout_option) ? $layout_option : '';
			$custom_css = get_post_meta($post_id, '_zwsgr_custom_css', true);
	
			// Generate the shortcode by calling the new function
			$generated_shortcode = $this->generate_shortcode($post_id);
			$current_tab = get_post_meta($post_id, 'tab-options', true); 
			$current_tab2 = get_post_meta($post_id, 'tab-selected', true); 
			$rating_filter = intval(get_post_meta($post_id, 'rating_filter', true)) ?: 0;
			$enable_sort_by = get_post_meta($post_id, 'enable_sort_by', true);
			$zwsgr_location_new_review_uri = get_post_meta($post_id, 'zwsgr_location_new_review_uri', true);
			$zwsgr_location_name = get_post_meta($post_id, 'zwsgr_location_name', true);


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
				$ratings_to_include = array('ONE', 'TWO');
			} elseif ($rating_filter_word == 'THREE') {
				$ratings_to_include = array('ONE', 'TWO', 'THREE');
			} elseif ($rating_filter_word == 'FOUR') {
				$ratings_to_include = array('ONE', 'TWO', 'THREE', 'FOUR');
			} elseif ($rating_filter_word == 'FIVE') {
				$ratings_to_include = array('ONE', 'TWO', 'THREE', 'FOUR', 'FIVE');
			} elseif ($rating_filter_word == 'ONE') {
				$ratings_to_include = array('ONE');
			}

			$zwsgr_reviews_args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,
				'posts_per_page' => 5,
				'meta_query'     => array(
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					)
				),
			);

			// Add sort_by filters
			switch ($sort_by) {
				case 'newest':
					$zwsgr_reviews_args['orderby'] = 'date';
					$zwsgr_reviews_args['order'] = 'DESC';
					break;

				case 'highest':
					// Adjust the "highest" sort based on the selected rating filter
					if (!empty($rating_filter_word)) {
						// Sort by the highest rating within the selected filter group
						$zwsgr_reviews_args['meta_query'][0]['value'] = $rating_filter_word; // Limit to the selected rating
						$zwsgr_reviews_args['orderby'] = 'meta_value_num';
						$zwsgr_reviews_args['order'] = 'DESC';
					} else {
						// Default behavior if no filter is set
						// $zwsgr_reviews_args['meta_query'][0]['value'] = 'FIVE';
						$zwsgr_reviews_args['orderby'] = 'meta_value';
						$zwsgr_reviews_args['order'] = 'ASC';
						$azwsgr_reviews_argsrgs['meta_key'] = 'zwsgr_review_star_rating';
					}
					break;

				case 'lowest':
					// $zwsgr_reviews_args['meta_query'][0]['value'] = 'ONE';
					$zwsgr_reviews_args['orderby'] = 'meta_value';
						$zwsgr_reviews_args['order'] = 'DESC';
						$azwsgr_reviews_argsrgs['meta_key'] = 'zwsgr_review_star_rating';
					break;

				default:
					$zwsgr_reviews_args['orderby'] = 'date';
					$zwsgr_reviews_args['order'] = 'DESC';
			}

			$latest_zwsgr_reviews = new WP_Query($zwsgr_reviews_args);
			$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));

			if ($latest_zwsgr_reviews->have_posts()) {
				while($latest_zwsgr_reviews->have_posts()) {
					$latest_zwsgr_reviews->the_post();
			
					$zwsgr_reviewer_name   	  	   = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
					$zwsgr_review_star_rating 	   = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
					$zwsgr_review_comment  	  	   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
					$zwsgr_review_id		  	   = get_post_meta(get_the_ID(), 'zwsgr_review_id', true);
					$zwsgr_gmb_reviewer_image_path = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
					$zwsgr_gmb_reviewer_image_uri  = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
					$published_date  = get_the_date('F j, Y');
					$months = $this->translate_months($language);

					// Determine if content is trimmed based on character limit
					$is_trimmed = $char_limit > 0 && mb_strlen($zwsgr_review_comment) > $char_limit; // Check if the content length exceeds the character limit
					$trimmed_content = $is_trimmed ? mb_substr($zwsgr_review_comment, 0, $char_limit) . '...' : $zwsgr_review_comment; // Trim the content if necessary


					$formatted_date = '';
						if ($date_format === 'DD/MM/YYYY') {
							$formatted_date = date('d/m/Y', strtotime($published_date));
						} elseif ($date_format === 'MM-DD-YYYY') {
							$formatted_date = date('m-d-Y', strtotime($published_date));
						} elseif ($date_format === 'YYYY/MM/DD') {
							$formatted_date = date('Y/m/d', strtotime($published_date));
						} elseif ($date_format === 'full') {
							$day = date('j', strtotime($published_date));
							$month = $months[(int)date('n', strtotime($published_date)) - 1];
							$year = date('Y', strtotime($published_date));
						
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
					$numeric_rating = isset($rating_map[$zwsgr_review_star_rating]) ? $rating_map[$zwsgr_review_star_rating] : 0;

					// Generate stars HTML
					$stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$stars_html .= $i < $numeric_rating 
							? '<span class="zwsgr-star filled">★</span>' 
							: '<span class="zwsgr-star">☆</span>';
					}

					// Format the slider item for each review
					$zwsgr_slider_item1 = '
						<div class="zwsgr-slide-item">
							<div class="zwsgr-list-inner">
								<div class="zwsgr-slide-wrap">
									<div class="zwsgr-profile">
										'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
									</div>
									<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									<div class="zwsgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								' .
									( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' )
							 . '
							</div>
						</div>';

						$zwsgr_slider_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-rating-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_slider_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								<div class="zwsgr-slide-wrap4">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								<div class="zwsgr-slide-wrap4">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-review-info">
										  	' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                						? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item5 = '
							<div class="zwsgr-slide-item">
								<div class="">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'	
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
								' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								<div class="zwsgr-contnt-wrap">
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									' . (!empty($published_date)
									? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item6 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								<div class="zwsgr-slide-wrap">
									<div class="zwsgr-profile">
										'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
									</div>
									<div class="zwsgr-review-info">
										' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									<div class="zwsgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_list_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
						
						$zwsgr_list_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwsgr_list_item3= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap3">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											<div class="zwsgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
											</div>
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_list_item4= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap4">
										<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											<div class="zwsgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
											</div>
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										</div>
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';

						$zwsgr_list_item5= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-list-wrap5">
										<div class="zwsgr-prifile-wrap">
											<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											</div>
											<div class="zwsgr-data">
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
                								? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											</div>
										</div>
										<div class="zwsgr-content-wrap">
											<div class="zwsgr-review-info">
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
												<div class="zwsgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
												</div>
											</div>
											' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_grid_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_grid_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											<div class="zwsgr-date-wrap">
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
												' . (!empty($published_date)
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											</div>
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_grid_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-review-detail">
											<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											</div>
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											<div class="zwsgr-rating-wrap">
												<div class="zwsgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
												</div>
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
											</div>
										</div>
										' . ( !empty($trimmed_content) ? '<div class="zwsgr-content-wrap"><p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwsgr_grid_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>									
									' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';
						
						$zwsgr_grid_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_popup_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_popup_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_comment) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';



					// Add the slider item to the slider content array
					$zwsgr_slider_content1[] = $zwsgr_slider_item1;
					$zwsgr_slider_content2[] = $zwsgr_slider_item2;
					$zwsgr_slider_content3[] = $zwsgr_slider_item3;
					$zwsgr_slider_content4[] = $zwsgr_slider_item4;
					$zwsgr_slider_content5[] = $zwsgr_slider_item5;
					$zwsgr_slider_content6[] = $zwsgr_slider_item6;

					$zwsgr_list_content1[] = $zwsgr_list_item1;
					$zwsgr_list_content2[] = $zwsgr_list_item2;
					$zwsgr_list_content3[] = $zwsgr_list_item3;
					$zwsgr_list_content4[] = $zwsgr_list_item4;
					$zwsgr_list_content5[] = $zwsgr_list_item5;

					$zwsgr_grid_content1[] = $zwsgr_grid_item1;
					$zwsgr_grid_content2[] = $zwsgr_grid_item2;
					$zwsgr_grid_content3[] = $zwsgr_grid_item3;
					$zwsgr_grid_content4[] = $zwsgr_grid_item4;
					$zwsgr_grid_content5[] = $zwsgr_grid_item5;

					$zwsgr_popup_content1[] = $zwsgr_popup_item1;
					$zwsgr_popup_content2[] = $zwsgr_popup_item2;

			
				}
				wp_reset_postdata();
			}

			$zwsgr_slider_content1 = isset($zwsgr_slider_content1) && !empty($zwsgr_slider_content1) ? implode('', (array) $zwsgr_slider_content1) : '';
			$zwsgr_slider_content2 = isset($zwsgr_slider_content2) && !empty($zwsgr_slider_content2) ? implode('', (array) $zwsgr_slider_content2) : '';
			$zwsgr_slider_content3 = isset($zwsgr_slider_content3) && !empty($zwsgr_slider_content3) ? implode('', (array) $zwsgr_slider_content3) : '';
			$zwsgr_slider_content4 = isset($zwsgr_slider_content4) && !empty($zwsgr_slider_content4) ? implode('', (array) $zwsgr_slider_content4) : '';
			$zwsgr_slider_content5 = isset($zwsgr_slider_content5) && !empty($zwsgr_slider_content5) ? implode('', (array) $zwsgr_slider_content5) : '';
			$zwsgr_slider_content6 = isset($zwsgr_slider_content6) && !empty($zwsgr_slider_content6) ? implode('', (array) $zwsgr_slider_content6) : '';

			$zwsgr_list_content1 = isset($zwsgr_list_content1) && !empty($zwsgr_list_content1) ? implode('', (array) $zwsgr_list_content1) : '';
			$zwsgr_list_content2 = isset($zwsgr_list_content2) && !empty($zwsgr_list_content2) ? implode('', (array) $zwsgr_list_content2) : '';
			$zwsgr_list_content3 = isset($zwsgr_list_content3) && !empty($zwsgr_list_content3) ? implode('', (array) $zwsgr_list_content3) : '';
			$zwsgr_list_content4 = isset($zwsgr_list_content4) && !empty($zwsgr_list_content4) ? implode('', (array) $zwsgr_list_content4) : '';
			$zwsgr_list_content5 = isset($zwsgr_list_content5) && !empty($zwsgr_list_content5) ? implode('', (array) $zwsgr_list_content5) : '';

			$zwsgr_grid_content1 = isset($zwsgr_grid_content1) && !empty($zwsgr_grid_content1) ? implode('', (array) $zwsgr_grid_content1) : '';
			$zwsgr_grid_content2 = isset($zwsgr_grid_content2) && !empty($zwsgr_grid_content2) ? implode('', (array) $zwsgr_grid_content2) : '';
			$zwsgr_grid_content3 = isset($zwsgr_grid_content3) && !empty($zwsgr_grid_content3) ? implode('', (array) $zwsgr_grid_content3) : '';
			$zwsgr_grid_content4 = isset($zwsgr_grid_content4) && !empty($zwsgr_grid_content4) ? implode('', (array) $zwsgr_grid_content4) : '';
			$zwsgr_grid_content5 = isset($zwsgr_grid_content5) && !empty($zwsgr_grid_content5) ? implode('', (array) $zwsgr_grid_content5) : '';

			$zwsgr_popup_content1 = isset($zwsgr_popup_content1) && !empty($zwsgr_popup_content1) ? implode('', (array) $zwsgr_popup_content1) : '';
			$zwsgr_popup_content2 = isset($zwsgr_popup_content2) && !empty($zwsgr_popup_content2) ? implode('', (array) $zwsgr_popup_content2) : '';


			$zwsgr_gmb_account_number = get_post_meta($post_id, 'zwsgr_account_number', true);
			$zwsgr_gmb_account_location =get_post_meta($post_id, 'zwsgr_location_number', true);

			$zwsgr_filter_data = [
				'zwsgr_gmb_account_number'   => $zwsgr_gmb_account_number,
				'zwsgr_gmb_account_location' => $zwsgr_gmb_account_location,
				'zwsgr_range_filter_type'    => '',
				'zwsgr_range_filter_data'    => ''
			];

			$zwsgr_data_render_args = $this->zwsgr_dashboard->zwsgr_data_render_query($zwsgr_filter_data);		
			$zwsgr_reviews_ratings = $this->zwsgr_dashboard->zwsgr_get_reviews_ratings($zwsgr_data_render_args);
			$widthPercentage = $zwsgr_reviews_ratings['ratings'] * 20;

			$final_rating = ' <div class="zwsgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwsgr-final-review-fill" style="width: ' . $widthPercentage . '%;">
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
					'<div class="zwsgr-slider" id="zwsgr-slider1">
						<div class="zwsgr-slider-1">
							' . $zwsgr_slider_content1 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider2">
						<div class="zwsgr-slider-2">
							' . $zwsgr_slider_content2 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider3">
						<div class="zwsgr-slider-badge">
							<div class="zwsgr-badge-item" id="zwsgr-badge1">
								<h3 class="zwsgr-average">Good</h3>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
								<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
								<img src="' . $plugin_dir_path . 'assets/images/google.png">
							</div>
						</div>
						<div class="zwsgr-slider-3">
							' . $zwsgr_slider_content3 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider4">
						<div class="zwsgr-slider-4">
							' . $zwsgr_slider_content4 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider5">
						<div class="zwsgr-slider-5">
							' . $zwsgr_slider_content5 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider6">
						<div class="zwsgr-slider-6">
							' . $zwsgr_slider_content6 . '
						</div>
					</div>'
				],
				'grid' => [
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid1">
						' . $zwsgr_grid_content1 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid2">
						' . $zwsgr_grid_content2 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid3">
						' . $zwsgr_grid_content3 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid4">
						' . $zwsgr_grid_content4 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid5">
						' . $zwsgr_grid_content5 . '
					</div>'
				],
				'list' => [
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list1">
						' . $zwsgr_list_content1 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list2">
						' . $zwsgr_list_content2 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list3">
						' . $zwsgr_list_content3 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list4">
						' . $zwsgr_list_content4 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list5">
						' . $zwsgr_list_content5 . '
					</div>'
				],
				'badge' => [
					'<div class="zwsgr-badge-item" id="zwsgr-badge1">
						<h3 class="zwsgr-average">Good</h3>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
						<img src="' . $plugin_dir_path . 'assets/images/google.png">
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge2">
						<div class="zwsgr-badge-image">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
						</div>
						<div class="zwsgr-badge-info">
							<h3 class="zwsgr-average">Good</h3>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge3">
						<div class="zwsgr-rating-wrap">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge4">
						<div class="zwsgr-badge4-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwsgr-badge4-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge5">
						<div class="zwsgr-badge5-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
						</div>
						<div class="zwsgr-badge5-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge6">
						<div class="zwsgr-badge6-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwsgr-badge6-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge7">
						<img src="' . $plugin_dir_path . 'assets/images/review-us.png">
						<div class="zwsgr-badge7-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge8">
						<div class="zwsgr-logo-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
							<p class="zwsgr-avg-note">Google Reviews</p>
						</div>
						<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
					</div>'
				],
				'popup' => [
					'<div class="zwsgr-popup-item" id="zwsgr-popup1" data-popup="zwsgrpopup1">
						<div class="zwsgr-profile-logo">
							<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
						</div>
						<div class="zwsgr-profile-info">
							<h3>'.$zwsgr_location_name.'</h3>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="#" target="_blank" class="zwsgr-total-review"> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</a>
						</div>
					</div>
					<div id="zwsgrpopup1" class="zwsgr-popup-overlay">
						<div class="zwsgr-popup-content">
							<div class="scrollable-content">
								<span class="zwsgr-close-popup">&times;</span>
								<div class="zwsgr-popup-wrap">
									<div class="zwsgr-profile-logo">
										<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
									</div>
									<div class="zwsgr-profile-info">
										<h3>'.$zwsgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
									' . $zwsgr_popup_content1 . '
								</div>
							</div>
						</div>
					</div>',
					'<div class="zwsgr-popup-item" id="zwsgr-popup2"  data-popup="zwsgrpopup2">
						<div class="zwsgr-title-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/google.png">
							<h3>Reviews</h3>
						</div>
						<div class="zwsgr-info-wrap">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<a href="#" target="_blank" 	class="zwsgr-total-review">(  '.$zwsgr_reviews_ratings['reviews'].' reviews )</a>
						</div>
					</div>
					<div id="zwsgrpopup2" class="zwsgr-popup-overlay">
						<div class="zwsgr-popup-content">
							<div class="scrollable-content">
								<span class="zwsgr-close-popup">&times;</span>
								<div class="zwsgr-popup-wrap">
									<div class="zwsgr-profile-logo">
										<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
									</div>
									<div class="zwsgr-profile-info">
										<h3>'.$zwsgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
									' . $zwsgr_popup_content2 . '
								</div>
							</div>
						</div>
					</div>'
				]
			];
			?>
			
			<div class="zwsgr-dashboard">

			<h1 class="zwsgr-page-title">Widget Configuration</h1>


				<!-- Tab Navigation -->
				<ul class="tab-nav zwsgr-custom-tab">
					<li class="tab-item zwsgr-tab-item active done" data-tab="tab-fetch-data"><span class="zwsgr-step">1. </span>Fetch Data</li>
					<span class="zwsgr-step-arrow"></span>
					<li class="tab-item zwsgr-tab-item  <?php echo ($layout_option) ? 'done' : ''; ?>" data-tab="tab-options"><span class="zwsgr-step">2. </span>Select Display Options</li>
					<span class="zwsgr-step-arrow"></span>
					<li class="tab-item zwsgr-tab-item <?php echo ($current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-selected"><span class="zwsgr-step">3. </span>Selected Option</li>
					<span class="zwsgr-step-arrow"></span>
					<li class="tab-item zwsgr-tab-item <?php echo ($current_tab2 === 'tab-selected') ? 'done' : 'disable'; ?>" data-tab="tab-shortcode"><span class="zwsgr-step">4. </span>Generated Shortcode</li>
				</ul>

				<!-- Tab Data Fetch Areas -->
				<div class="tab-content" id="tab-fetch-data">
					<?php Zwsgr_Google_My_Business_Connector::get_instance()->zwsgr_fetch_gmb_data_callback(); ?>
				</div>

				<!-- Tab Content Areas -->
				<div class="tab-content" id="tab-options">
					<!-- Dynamically Render Radio Buttons -->
					<div class="zwsgr-layout-radio"> 
						<label><input type="radio" name="display_option" class="zwsgr-radio" value="all" checked> <span>All</span></label>
						<?php foreach ($options as $key => $layouts) : ?>
							<label><input type="radio" name="display_option" class="zwsgr-radio" value="<?php echo esc_attr($key); ?>"><span> <?php echo ucfirst($key); ?></span></label>
						<?php endforeach; ?>
					</div>

					<!-- Dynamically Render Layout Options Based on Selected Display Option -->
					<div id="layout-options">
						<?php
						foreach ($options as $option_type => $layouts) {
							$layout_count = 1;
							if( $option_type == "badge") {
								echo '<div class="zwsgr-badge-wrap">';
							}
							if( $option_type == "popup") {
								echo '<div class="zwsgr-popup-wrap">';
							}
							foreach ($layouts as $layout_content) { 
								$element_id = $option_type . '-' . $layout_count;

								// Only show layouts for the selected display option
								$display_style = ($selected_display_option === $option_type || $selected_display_option === 'all') ? 'block' : 'block';
								$selected_class = ($element_id === $layout_option) ? ' selected' : ''; // Check if this layout is selected
								
								echo '<div id="' . esc_attr($element_id) . '" class="zwsgr-option-item' . $selected_class . '" data-type="' . esc_attr($option_type) . '" style="display: ' . $display_style . ';">';
									echo '<div class="zwsgr-layout-title-wrap">';
										echo '<h3 class="zwsgr-layout-title">Layout: '. $option_type .' '.$layout_count.'</h3>';
										echo '<button class="select-btn zwsgr-btn" data-option="' . esc_attr($element_id) . '"' . ($element_id === $selected_layout_option ? ' selected' : '') . '>Select Option</button>';
									echo '</div>';
									echo $layout_content;
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

				<div class="tab-content" id="tab-selected" style="display:none;">
					<h3>Selected Option</h3>
					<div id="selected-option-display" class="selected-option-display"></div>
					<div class="zwsgr-toogle-display">
							<a href="<?php echo esc_attr($zwsgr_location_new_review_uri); ?>" style="background-color:<?php echo esc_attr($bg_color); ?>; color:<?php echo esc_attr($text_color); ?>;" class="zwsgr-google-toggle">Review Us On G</a>
					</div>
					<?php if ($display_option !== 'badge') : ?>
						<div class="zwsgr-widget-settings">
							<h2 class="zwsgr-page-title">Widget Settings</h2>
							<div class="zwsgr-widget-wrap">
								<div class="zwsgr-widget-setting">
										<h3 class="zwsgr-label">Filter Rating</h3>
										<div class="filter-rating">
											<?php
											for ($i = 1; $i <= 5; $i++) {
												$selected = ($i <= $rating_filter) ? 'selected' : '';  // Check if the current star is selected
												$fillColor = ($i <= $rating_filter) ? '#FFD700' : '#ccc'; // Color for selected and non-selected stars
												?>
												<span class="star-filter <?php echo $selected; ?>" data-rating="<?php echo $i; ?>" title="<?php echo $i; ?> Star">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path class="star" d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="<?php echo $fillColor; ?>" />
													</svg>
												</span>
												<?php
											}
											?>
										</div>
								</div>


								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Hide Element</h3>
									<ul class="zwsgr-widget-eleemt-list">
										<li>
											<input type="checkbox" id="review-title" class="zwsgr-checkbox" name="review-element" value="review-title" 
											<?php echo in_array('review-title', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-title" class="zwsgr-chechbox-label">Reviewer Name</label>
										</li>
										<li>
											<input type="checkbox" id="review-rating" class="zwsgr-checkbox" name="review-element" value="review-rating" 
											<?php echo in_array('review-rating', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-rating" class="zwsgr-chechbox-label">Rating</label>
										</li>
										<li>
											<input type="checkbox" id="review-days-ago" class="zwsgr-checkbox" name="review-element" value="review-days-ago" 
											<?php echo in_array('review-days-ago', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-days-ago" class="zwsgr-chechbox-label">Date</label>
										</li>
										<li>
											<input type="checkbox" id="review-content" class="zwsgr-checkbox" name="review-element" value="review-content" 
											<?php echo in_array('review-content', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-content" class="zwsgr-chechbox-label">Review Content</label>
										</li>
										<li>
											<input type="checkbox" id="review-photo" class="zwsgr-checkbox" name="review-element" value="review-photo" 
											<?php echo in_array('review-photo', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-photo" class="zwsgr-chechbox-label">Reviewer Photo</label>
										</li>
										<li>
											<input type="checkbox" id="review-g-icon" class="zwsgr-checkbox" name="review-element" value="review-g-icon" 
											<?php echo in_array('review-g-icon', $selected_elements) ? 'checked' : ''; ?>>
											<label for="review-g-icon" class="zwsgr-chechbox-label">G Icon</label>
										</li>
										<!-- Add more elements as needed -->
									</ul>
								</div>

								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Keywords</h3>
									<input type="text" id="keywords-input" name="keywords-input" class="zwsgr-input-text" placeholder="e.g., keyword1, keyword2, keyword3">
									<p class="zwsgr-description">Type keywords separated by commas</p>

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

									<div id="error-message" class="error-message" style="display: none; color: red;">
										You can only enter a maximum of 5 keywords.
									</div> 
								</div>

								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Review us on Google</h3>
									<label class="switch">
										<input type="checkbox" id="toggle-google-review" name="google_review_toggle" <?php echo ($google_review_toggle) ? 'checked' : ''; ?>>
										<span class="slider"></span>
									</label>

									<div id="color-picker-options" style="display: <?php echo ($google_review_toggle) ? 'flex' : 'none'; ?>" class="zwsgr-color-options">
										<div class="zwsgr-color-picker">
											<label for="bg-color-picker" class="zwsgr-chechbox-label">Background Color:</label>
											<input type="color" id="bg-color-picker" name="bg_color_picker" value="<?php echo esc_attr($bg_color ? $bg_color : '#3780ff'); ?>">
										</div>
										<div class="zwsgr-color-picker">
											<label for="text-color-picker" class="zwsgr-chechbox-label">Text Color:</label>
											<input type="color" id="text-color-picker" name="text_color_picker" value="<?php echo esc_attr($text_color ? $text_color : '#ffffff'); ?>">
										</div>
									</div>
								</div>

								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Trim long reviews with a "read more" link</h3>
									<input type="number" class="zwsgr-input-text" id="review-char-limit" name="review-char-limit" min="10" placeholder="Enter character limit" value="<?php echo !empty($char_limit) ? esc_attr($char_limit) : ''; ?>" oninput="this.value = Math.max(this.value, 0)">
								</div>

								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Language</h3>
									<select id="language-select" name="language" class="zwsgr-input-text">
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

								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Sort By</h3>
									<select id="sort-by-select" name="sort_by" class="zwsgr-input-text">
										<option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest</option>
										<option value="highest" <?php echo ($sort_by === 'highest') ? 'selected' : ''; ?>>Highest Rating</option>
										<option value="lowest" <?php echo ($sort_by === 'lowest') ? 'selected' : ''; ?>>Lowest Rating</option>
									</select>
									<div class="zwsgr-sort-by-checkbox">
											<input type="checkbox" class="zwsgr-checkbox" id="enable-sort-by-filter" name="enable_sort_by" <?php echo ($enable_sort_by ? 'checked' : ''); ?> />
											<label class="zwsgr-chechbox-label">Do you want to show "Sort By" filter on front side?</label>
									</div>
								</div>
		
								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Date Format</h3>
									<select id="date-format-select" name="date-format" class="zwsgr-input-text">
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
								<div class="zwsgr-widget-setting">
									<h3 class="zwsgr-label">Load More</h3>
									<label class="switch">
										<input type="checkbox" id="enable-load-more" name="enable_load_more" <?php echo ($enable_load_more ? 'checked' : ''); echo $is_checked;?> />
										<span class="slider"></span>
									</label>
								<div id="zwsgr-load-color-picker-options" style="display: <?php echo ($enable_load_more) ? 'flex' : 'none'; ?>" class="zwsgr-color-options_load">
										<div class="zwsgr-color-picker-load">
											<label for="bg-color-picker_load" class="zwsgr-chechbox-label">Background Color:</label>
											<input type="color" id="bg-color-picker_load" name="bg_color_picker_load" value="<?php echo esc_attr($bg_color_load ? $bg_color_load : '#000000'); ?>">
										</div>
										<div class="zwsgr-color-picker-load">
											<label for="text-color-picker_load" class="zwsgr-chechbox-label">Text Color:</label>
											<input type="color" id="text-color-picker_load" name="text_color_picker_load" value="<?php echo esc_attr($text_color_load ? $text_color_load : '#ffffff'); ?>">
										</div>
									</div>

									<div id="load-more-settings" style="display:'block';">
									<h3 class="zwsgr-label">Number Of Review:</h3>
									<select id="posts-per-page" name="posts_per_page" class="zwsgr-input-text">
										<option value="1" <?php echo ($posts_per_page == 1) ? 'selected' : ''; ?>>1</option>
										<option value="2" <?php echo ($posts_per_page == 2) ? 'selected' : ''; ?>>2</option>
										<option value="3" <?php echo ($posts_per_page == 3) ? 'selected' : ''; ?>>3</option>
										<option value="4" <?php echo ($posts_per_page == 4) ? 'selected' : ''; ?>>4</option>
										<option value="5" <?php echo ($posts_per_page == 5) ? 'selected' : ''; ?>>5</option>
										<option value="6" <?php echo ($posts_per_page == 6) ? 'selected' : ''; ?>>6</option>
										<option value="7" <?php echo ($posts_per_page == 7) ? 'selected' : ''; ?>>7</option>
										<option value="8" <?php echo ($posts_per_page == 8) ? 'selected' : ''; ?>>8</option>
										<option value="9" <?php echo ($posts_per_page == 9) ? 'selected' : ''; ?>>9</option>
										<option value="10" <?php echo ($posts_per_page == 10) ? 'selected' : ''; ?>>10</option>
									</select>
								</div>
							</div>
						</div>
					
						</div>
				<?php endif; ?>
				
				<div class="zwsgr-widget-settings">
					<h2 class="zwsgr-page-title">Custom CSS Support</h2>
					<textarea class="zwsgr-textarea" rows="5" cols="40" placeholder="Enter your custom CSS here"><?php echo esc_textarea($custom_css); ?></textarea>
				</div>
					<button id="save-get-code-btn" class="zwsgr-btn" data-zwsgr-btn='zwsgr-btn'>Save & Get Code</button>
				</div>

				<div class="tab-content" id="tab-shortcode" style="display:none;">
					<h3>Generated Shortcode</h3>
					<div id="generated-shortcode-display" class="generated-shortcode-display">
						<?php echo esc_html($generated_shortcode); ?>
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
				error_log('Nonce verification failed.');
				wp_send_json_error(esc_html__('Nonce verification failed.', 'zw-smart-google-reviews'));
				return;
			}
			error_log('Nonce verified successfully.');

			// Get and sanitize post ID
			$post_id = intval($_POST['post_id']);
			if (!$post_id) {
				error_log('Invalid post ID');
				wp_send_json_error(esc_html__('Invalid post ID.', 'zw-smart-google-reviews'));
				return;
			}
			error_log('Post ID: ' . $post_id);

			// Check if the post exists
			if (get_post_status($post_id) === false) {
				error_log('Post does not exist: ' . $post_id);
				wp_send_json_error(esc_html__('Post does not exist.', 'zw-smart-google-reviews'));
				return;
			}
			error_log('Post exists, ID: ' . $post_id);

			// Ensure user has permission to edit the post
			if (!current_user_can('edit_post', $post_id)) {
				error_log('User does not have permission to edit post: ' . $post_id);
				wp_send_json_error(esc_html__('You do not have permission to edit this post.', 'zw-smart-google-reviews'));
				return;
			}
			
			$setting_tb = ( isset( $_POST['settings'] ) && ! empty( $_POST['settings'] ) ) ? sanitize_text_field( $_POST['settings'] ) : '';

			if(  $setting_tb == 'tab-options' ){
			$display_option = isset($_POST['display_option']) ? sanitize_text_field($_POST['display_option']) : get_post_meta($post_id, 'display_option', true);
			update_post_meta($post_id, 'display_option', $display_option);	

			$layout_option = isset($_POST['layout_option']) ? sanitize_text_field($_POST['layout_option']) : get_post_meta($post_id, 'layout_option', true);
			update_post_meta($post_id, 'layout_option', $layout_option);
			
			$current_tab = sanitize_text_field($_POST['current_tab']); // The active tab
			update_post_meta($post_id, 'tab-options', $current_tab); // Save the active tab state

			}

			else if(  $setting_tb == 'tab-selected' ){
				$selected_elements = isset($_POST['selected_elements']) ? array_map('sanitize_text_field', $_POST['selected_elements']) : array();
				// $rating_filter = isset($_POST['rating_filter']) ? sanitize_text_field($_POST['rating_filter']) : '';
				$keywords = isset($_POST['keywords']) ? array_map('sanitize_text_field', $_POST['keywords']) : [];
				$date_format = isset($_POST['date_format']) ? sanitize_text_field($_POST['date_format']) : '';
				$char_limit = isset($_POST['char_limit']) ? intval($_POST['char_limit']) : 0;
				$language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
				$sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : '';
				$enable_load_more = isset($_POST['enable_load_more']) ? intval($_POST['enable_load_more']) : 0;
				$google_review_toggle = isset($_POST['google_review_toggle']) ? intval($_POST['google_review_toggle']) : 0;
				$bg_color = isset($_POST['bg_color']) ? sanitize_hex_color($_POST['bg_color']) : '';
				$text_color = isset($_POST['text_color']) ? sanitize_hex_color($_POST['text_color']) : '';
				$bg_color_load = isset($_POST['bg_color_load']) ? sanitize_hex_color($_POST['bg_color_load']) : '';
				$text_color_load = isset($_POST['text_color_load']) ? sanitize_hex_color($_POST['text_color_load']) : '';
				$posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 10; // Default to 10
				$rating_filter = isset($_POST['rating_filter']) ? intval($_POST['rating_filter']) : 0;
				$custom_css = sanitize_textarea_field($_POST['custom_css']);
				$current_tab2 = sanitize_text_field( $_POST['settings'] ); // The active tab
				$enable_sort_by = isset($_POST['enable_sort_by']) ? intval($_POST['enable_sort_by']) : 0;

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
				update_post_meta($post_id, '_zwsgr_custom_css', $custom_css);
				update_post_meta($post_id, 'enable_sort_by', $enable_sort_by);
			}
		
			// Respond with success message
			wp_send_json_success('Settings updated successfully.' . $setting_tb );
		}

		function generate_shortcode($post_id) 
		{
			// Build the shortcode with attributes
			$shortcode = '[zwsgr_widget post-id="' . esc_attr($post_id) . '"]';
			update_post_meta($post_id, '_generated_shortcode_new', $shortcode);
			return $shortcode;	
		}

		function filter_reviews_ajax_handler() {
			
			$post_id = $_POST['zwsgr_widget_id'];

			$post_objct = get_post($post_id);
			if (!isset($post_id) || !$post_objct ) {
				wp_die( 'Invalid post ID.' ) ;
			}

			// Verify nonce for security
			if( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'filter_reviews_nonce') ) {
				die('Permission Denied');
			}
		
			// Check if rating_filter is set and is an array
			if (!isset($_POST['rating_filter']) || !is_array($_POST['rating_filter'])) {
				die('Invalid filter value');
			}
		
			$rating_filter = array_map('intval', $_POST['rating_filter']); // Ensure all values are integers
			$sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'newest';
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$language = get_post_meta($post_id, 'language', true);
			$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
		
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
		
			// Query reviews with the selected string-based filters
			$args = array(
				'post_type' => ZWSGR_POST_REVIEW_TYPE, // Replace with your custom post type
				'posts_per_page' => 5,
				'meta_query' => array(
					array(
						'key' => 'zwsgr_review_star_rating',
						'value' => $rating_strings,
						'compare' => 'IN',
						'type' => 'CHAR'
					)
				),
				'orderby' => 'date', // Default order by date
				'order' => 'DESC'    // Default order
			);

			// Add sorting logic
			switch ($sort_by) {
				case 'newest':
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
					break;

				case 'highest':
					// Sort by highest rating first
					$args['orderby'] = 'meta_value';
					$args['order'] = 'ASC';
					$args['meta_key'] = 'zwsgr_review_star_rating';
					break;

				case 'lowest':
					// Sort by lowest rating first
					$args['orderby'] = 'meta_value';
					$args['order'] = 'DESC';
					$args['meta_key'] = 'zwsgr_review_star_rating';
					break;

				default:
					// Default sorting (e.g., relevance)
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
		
			$reviews_query = new WP_Query($args);

			$reviews_html ='';    
			$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
			if ($reviews_query->have_posts()) {
				while ($reviews_query->have_posts()) {
					$reviews_query->the_post();
					$zwsgr_reviewer_name = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
					$zwsgr_review_content = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
					$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
					$zwsgr_review_id= get_post_meta(get_the_ID(), 'zwsgr_review_id', true);
					$zwsgr_gmb_reviewer_image_path = wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
					$zwsgr_gmb_reviewer_image_uri  = wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
					$zwsgr_location_name = get_post_meta($post_id, 'zwsgr_location_name', true);
					$published_date = get_the_date('F j, Y');
					$months = $this->translate_months($language);

					// Determine if content is trimmed based on character limit
					$is_trimmed = $char_limit > 0 && mb_strlen($zwsgr_review_content) > $char_limit; // Check if the content length exceeds the character limit
					$trimmed_content = $is_trimmed ? mb_substr($zwsgr_review_content, 0, $char_limit) . '...' : $zwsgr_review_content; // Trim the content if necessary

					$formatted_date = '';
						if ($date_format === 'DD/MM/YYYY') {
							$formatted_date = date('d/m/Y', strtotime($published_date));
						} elseif ($date_format === 'MM-DD-YYYY') {
							$formatted_date = date('m-d-Y', strtotime($published_date));
						} elseif ($date_format === 'YYYY/MM/DD') {
							$formatted_date = date('Y/m/d', strtotime($published_date));
						} elseif ($date_format === 'full') {
							$day = date('j', strtotime($published_date));
							$month = $months[(int)date('n', strtotime($published_date)) - 1];
							$year = date('Y', strtotime($published_date));
						
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
					$numeric_rating = isset($rating_map[$zwsgr_review_star_rating]) ? $rating_map[$zwsgr_review_star_rating] : 0;

					// Generate stars HTML
					$stars_html = '';
					for ($i = 0; $i < 5; $i++) {
						$stars_html .= $i < $numeric_rating 
							? '<span class="zwsgr-star filled">★</span>' 
							: '<span class="zwsgr-star">☆</span>';
					}
				
			

					// Format the slider item for each review
					$zwsgr_slider_item1 = '
						<div class="zwsgr-slide-item">
							<div class="zwsgr-list-inner">
								<div class="zwsgr-slide-wrap">
									<div class="zwsgr-profile">
										'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
									</div>
									<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									<div class="zwsgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
							</div>
						</div>';

						$zwsgr_slider_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-rating-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_slider_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								<div class="zwsgr-slide-wrap4">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								<div class="zwsgr-slide-wrap4">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-review-info">
										  	' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                						? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item5 = '
							<div class="zwsgr-slide-item">
								<div class="">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'	
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
								' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								<div class="zwsgr-contnt-wrap">
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									' . (!empty($published_date)
									? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
								</div>
								</div>
							</div>';

						$zwsgr_slider_item6 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
								<div class="zwsgr-slide-wrap">
									<div class="zwsgr-profile">
										'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
									</div>
									<div class="zwsgr-review-info">
										' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									<div class="zwsgr-google-icon">
										<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
									</div>
								</div>
								' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
								' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_list_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
						
						$zwsgr_list_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwsgr_list_item3= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap3">
										<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											<div class="zwsgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
											</div>
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_list_item4= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap4">
										<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											<div class="zwsgr-google-icon">
												<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
											</div>
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
										</div>
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';

						$zwsgr_list_item5= '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-list-wrap5">
										<div class="zwsgr-prifile-wrap">
											<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											</div>
											<div class="zwsgr-data">
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
                								? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											</div>
										</div>
										<div class="zwsgr-content-wrap">
											<div class="zwsgr-review-info">
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
												<div class="zwsgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
												</div>
											</div>
											' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
										</div>
									</div>
								</div>
							</div>';

						$zwsgr_grid_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_grid_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											<div class="zwsgr-date-wrap">
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
												' . (!empty($published_date)
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											</div>
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_grid_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-review-detail">
											<div class="zwsgr-profile">
												'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
											</div>
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
											<div class="zwsgr-rating-wrap">
												<div class="zwsgr-google-icon">
													<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
												</div>
												' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
											</div>
										</div>
										' . ( !empty($trimmed_content) ? '<div class="zwsgr-content-wrap"><p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwsgr_grid_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>									
									' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
									' . (!empty($published_date)
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
									' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '	
								</div>
							</div>';
						
						$zwsgr_grid_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
												' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
												' . (!empty($published_date)
											? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_popup_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
									</div>
									' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwsgr_popup_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-profile">
											'.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'
										</div>
										<div class="zwsgr-review-info">
											' . (!empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '') . '
											' . (!empty($published_date)
                							? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '') . '
										</div>
										<div class="zwsgr-google-icon">
											<img src="' . $plugin_dir_path . 'assets/images/google-icon.png">
										</div>
									</div>
									<div class="zwsgr-list-content-wrap">
										' . (!empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '') . '
										' . ( !empty($trimmed_content) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>
							</div>';

					// Add the slider item to the slider content array
					$zwsgr_slider_content1[] = $zwsgr_slider_item1;
					$zwsgr_slider_content2[] = $zwsgr_slider_item2;
					$zwsgr_slider_content3[] = $zwsgr_slider_item3;
					$zwsgr_slider_content4[] = $zwsgr_slider_item4;
					$zwsgr_slider_content5[] = $zwsgr_slider_item5;
					$zwsgr_slider_content6[] = $zwsgr_slider_item6;

					$zwsgr_list_content1[] = $zwsgr_list_item1;
					$zwsgr_list_content2[] = $zwsgr_list_item2;
					$zwsgr_list_content3[] = $zwsgr_list_item3;
					$zwsgr_list_content4[] = $zwsgr_list_item4;
					$zwsgr_list_content5[] = $zwsgr_list_item5;

					$zwsgr_grid_content1[] = $zwsgr_grid_item1;
					$zwsgr_grid_content2[] = $zwsgr_grid_item2;
					$zwsgr_grid_content3[] = $zwsgr_grid_item3;
					$zwsgr_grid_content4[] = $zwsgr_grid_item4;
					$zwsgr_grid_content5[] = $zwsgr_grid_item5;

					$zwsgr_popup_content1[] = $zwsgr_popup_item1;
					$zwsgr_popup_content2[] = $zwsgr_popup_item2;

				}
				wp_reset_postdata();
			} 
			else {
				echo 'No reviews found for the selected ratings';
			}
			$zwsgr_slider_content1 = implode('', (array) $zwsgr_slider_content1);
			$zwsgr_slider_content2 = implode('', (array) $zwsgr_slider_content2);
			$zwsgr_slider_content3 = implode('', (array) $zwsgr_slider_content3);
			$zwsgr_slider_content4 = implode('', (array) $zwsgr_slider_content4);
			$zwsgr_slider_content5 = implode('', (array) $zwsgr_slider_content5);
			$zwsgr_slider_content6 = implode('', (array) $zwsgr_slider_content6);

			$zwsgr_list_content1 = implode('', (array) $zwsgr_list_content1);
			$zwsgr_list_content2 = implode('', (array) $zwsgr_list_content2);
			$zwsgr_list_content3 = implode('', (array) $zwsgr_list_content3);
			$zwsgr_list_content4 = implode('', (array) $zwsgr_list_content4);
			$zwsgr_list_content5 = implode('', (array) $zwsgr_list_content5);

			$zwsgr_grid_content1 = implode('', (array) $zwsgr_grid_content1);
			$zwsgr_grid_content2 = implode('', (array) $zwsgr_grid_content2);
			$zwsgr_grid_content3 = implode('', (array) $zwsgr_grid_content3);
			$zwsgr_grid_content4 = implode('', (array) $zwsgr_grid_content4);
			$zwsgr_grid_content5 = implode('', (array) $zwsgr_grid_content5);

			$zwsgr_popup_content1 = implode('', (array) $zwsgr_popup_content1);
			$zwsgr_popup_content2 = implode('', (array) $zwsgr_popup_content2);

			$zwsgr_gmb_account_number = get_post_meta($post_id, 'zwsgr_account_number', true);
			$zwsgr_gmb_account_location =get_post_meta($post_id, 'zwsgr_account_locations', true);

			$zwsgr_filter_data = [
				'zwsgr_gmb_account_number'   => $zwsgr_gmb_account_number,
				'zwsgr_gmb_account_location' => $zwsgr_gmb_account_location,
				'zwsgr_range_filter_type'    => 'rangeofdays',
				'zwsgr_range_filter_data'    => 'monthly'
			];

			$zwsgr_reviews_ratings = $this->zwsgr_dashboard->zwsgr_get_reviews_ratings($zwsgr_filter_data);

			
			$widthPercentage = $zwsgr_reviews_ratings['ratings'] * 20;

			$final_rating = ' <div class="zwsgr-final-review-wrap">
				<svg width="100" height="20" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M30.0001 14.4156L34.7771 17.0896L33.7102 11.72L37.7293 8.00321L32.293 7.35866L30.0001 2.38752L27.7071 7.35866L22.2707 8.00321L26.2899 11.72L25.223 17.0896L30.0001 14.4156ZM23.8197 19.0211L25.2 12.0742L20 7.26542L27.0335 6.43152L30.0001 0L32.9666 6.43152L40 7.26542L34.8001 12.0742L36.1804 19.0211L30.0001 15.5616L23.8197 19.0211Z" fill="#ccc" />
					<path d="M50.0001 14.4156L54.7771 17.0896L53.7102 11.72L57.7293 8.0032L52.293 7.35866L50.0001 2.38752L47.7071 7.35866L42.2707 8.0032L46.2899 11.72L45.223 17.0896L50.0001 14.4156ZM43.8197 19.0211L45.2 12.0742L40 7.26541L47.0335 6.43152L50.0001 0L52.9666 6.43152L60 7.26541L54.8001 12.0742L56.1804 19.0211L50.0001 15.5616L43.8197 19.0211Z" fill="#ccc" />
					<path d="M70.0005 14.4159L74.7773 17.0899L73.7106 11.7203L77.7295 8.00334L72.2928 7.35876L70.0003 2.3876L67.7071 7.35877L62.2705 8.00334L66.2895 11.7203L65.2227 17.09L70.0005 14.4159ZM63.8195 19.0214L65.1996 12.0744L60 7.26554L67.0335 6.43163L70.0004 0L72.9665 6.43163L80 7.26554L74.8004 12.0744L76.1805 19.0214L70.0004 15.5619L63.8195 19.0214Z" fill="#ccc" />
					<path d="M10.0001 14.4156L14.7771 17.0896L13.7102 11.7201L17.7293 8.00322L12.293 7.35867L10.0001 2.38751L7.70704 7.35867L2.27068 8.00322L6.28991 11.7201L5.223 17.0896L10.0001 14.4156ZM3.81966 19.0211L5.19999 12.0742L0 7.26543L7.03344 6.43153L10.0001 0L12.9666 6.43153L20 7.26543L14.8001 12.0742L16.1804 19.0211L10.0001 15.5616L3.81966 19.0211Z" fill="#ccc" />
					<path d="M90.0005 14.4159L94.7773 17.0899L93.7106 11.7203L97.7295 8.00335L92.2928 7.35877L90.0003 2.38761L87.7071 7.35878L82.2705 8.00335L86.2895 11.7203L85.2227 17.09L90.0005 14.4159ZM83.8195 19.0214L85.1996 12.0744L80 7.26554L87.0335 6.43163L90.0004 0L92.9665 6.43163L100 7.26554L94.8004 12.0744L96.1805 19.0214L90.0004 15.5619L83.8195 19.0214Z" fill="#ccc" />
				</svg>
				<div class="zwsgr-final-review-fill" style="width: ' . $widthPercentage . '%;">
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
					'<div class="zwsgr-slider" id="zwsgr-slider1">
						<div class="zwsgr-slider-1">
							' . $zwsgr_slider_content1 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider2">
						<div class="zwsgr-slider-2">
							' . $zwsgr_slider_content2 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider3">
						<div class="zwsgr-slider-badge">
							<div class="zwsgr-badge-item" id="zwsgr-badge1">
								<h3 class="zwsgr-average">Good</h3>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
								<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
								<img src="' . $plugin_dir_path . 'assets/images/google.png">
							</div>
						</div>
						<div class="zwsgr-slider-3">
							' . $zwsgr_slider_content3 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider4">
						<div class="zwsgr-slider-4">
							' . $zwsgr_slider_content4 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider5">
						<div class="zwsgr-slider-5">
							' . $zwsgr_slider_content5 . '
						</div>
					</div>',
					'<div class="zwsgr-slider" id="zwsgr-slider6">
						<div class="zwsgr-slider-6">
							' . $zwsgr_slider_content6 . '
						</div>
					</div>'
				],
				'grid' => [
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid1">
						' . $zwsgr_grid_content1 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid2">
						' . $zwsgr_grid_content2 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid3">
						' . $zwsgr_grid_content3 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid4">
						' . $zwsgr_grid_content4 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-grid-item" id="zwsgr-grid5">
						' . $zwsgr_grid_content5 . '
					</div>'
				],
				'list' => [
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list1">
						' . $zwsgr_list_content1 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list2">
						' . $zwsgr_list_content2 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list3">
						' . $zwsgr_list_content3 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list4">
						' . $zwsgr_list_content4 . '
					</div>',
					'<div class="zwsgr-slider zwsgr-list" id="zwsgr-list5">
						' . $zwsgr_list_content5 . '
					</div>'
				],
				'badge' => [
					'<div class="zwsgr-badge-item" id="zwsgr-badge1">
						<h3 class="zwsgr-average">Good</h3>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
						<img src="' . $plugin_dir_path . 'assets/images/google.png">
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge2">
						<div class="zwsgr-badge-image">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
						</div>
						<div class="zwsgr-badge-info">
							<h3 class="zwsgr-average">Good</h3>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge3">
						<div class="zwsgr-rating-wrap">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge4">
						<div class="zwsgr-badge4-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwsgr-badge4-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge5">
						<div class="zwsgr-badge5-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
						</div>
						<div class="zwsgr-badge5-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge6">
						<div class="zwsgr-badge6-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
						<div class="zwsgr-badge6-info">
							<h3 class="zwsgr-google">Google</h3>
							<p class="zwsgr-avg-note">Average Rating</p>
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge7">
						<img src="' . $plugin_dir_path . 'assets/images/review-us.png">
						<div class="zwsgr-badge7-rating">
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						</div>
					</div>',

					'<div class="zwsgr-badge-item" id="zwsgr-badge8">
						<div class="zwsgr-logo-wrap">
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
							<p class="zwsgr-avg-note">Google Reviews</p>
						</div>
						<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
					</div>'
				],
				'popup' => [
					'<div class="zwsgr-popup-item" id="zwsgr-popup1" data-popup="zwsgrpopup1">
					<div class="zwsgr-profile-logo">
						<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
					</div>
					<div class="zwsgr-profile-info">
						<h3>'.$zwsgr_location_name.'</h3>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<a href="#" target="_blank" class="zwsgr-total-review"> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</a>
					</div>
				</div>
				<div id="zwsgrpopup1" class="zwsgr-popup-overlay">
					<div class="zwsgr-popup-content">
						<div class="scrollable-content">
							<span class="zwsgr-close-popup">&times;</span>
							<div class="zwsgr-popup-wrap">
								<div class="zwsgr-profile-logo">
									<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
								</div>
								<div class="zwsgr-profile-info">
									<h3>'.$zwsgr_location_name.'</h3>
									' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
									<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</b></p>
								</div>
							</div>
							<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
								' . $zwsgr_popup_content1 . '
							</div>
						</div>
					</div>
				</div>',
				'<div class="zwsgr-popup-item" id="zwsgr-popup2"  data-popup="zwsgrpopup2">
					<div class="zwsgr-title-wrap">
						<img src="' . $plugin_dir_path . 'assets/images/google.png">
						<h3>Reviews</h3>
					</div>
					<div class="zwsgr-info-wrap">
						<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
						' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
						<a href="#" target="_blank" 	class="zwsgr-total-review">(  '.$zwsgr_reviews_ratings['reviews'].' reviews )</a>
					</div>
				</div>
				<div id="zwsgrpopup2" class="zwsgr-popup-overlay">
					<div class="zwsgr-popup-content">
						<div class="scrollable-content">
							<span class="zwsgr-close-popup">&times;</span>
							<div class="zwsgr-popup-wrap">
								<div class="zwsgr-profile-logo">
									<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
								</div>
								<div class="zwsgr-profile-info">
									<h3>'.$zwsgr_location_name.'</h3>
									' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
									<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</b></p>
								</div>
							</div>
							<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
								' . $zwsgr_popup_content2 . '
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
			
			// if (isset($filter_layout[$layout_option_key][$layout_option_value])) {
			// 	$reviews_html = $filter_layout[$layout_option_key][$layout_option_value-1];
			// }
		
			// Return the filtered reviews HTML as the response
			echo $reviews_html;
			die();
		}		
	}
}
