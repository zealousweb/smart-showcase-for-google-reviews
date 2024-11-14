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

		function __construct()  {

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );
			add_action('admin_menu', array($this, 'zwsgr_admin_menu_registration'));
			add_action('admin_init', array($this, 'zwsgr_register_settings')); // Register settings when admin initializes
			add_action('init', array($this, 'zwsgr_register_widget_cpt'));  // Register Widget Custom Post Type
			add_action('init', array($this, 'zwsgr_register_review_cpt'));  // Register Review Custom Post Type

			add_action('add_meta_boxes', array($this, 'zwsgr_add_review_meta_box'));
			add_action('init', array($this, 'zwsgr_register_request_data_cpt'));
			add_action('add_meta_boxes', array($this, 'zwsgr_add_account_number_meta_box'));

			add_filter('manage_' . ZWSGR_POST_REVIEW_TYPE . '_posts_columns', array($this, 'filter__zwsgr_manage_data_posts_columns'), 10, 3);
			add_action('manage_' . ZWSGR_POST_REVIEW_TYPE . '_posts_custom_column', array($this, 'render_hide_column_content'), 10, 2);
			add_action('wp_ajax_toggle_visibility', array($this, 'zwsgr_toggle_visibility'));

			add_action('wp_ajax_save_widget_data',array($this, 'save_widget_data'));
			add_action('wp_ajax_nopriv_save_widget_data', array($this, 'save_widget_data'));
		
		}
		
		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__admin_init() {

			// admin js
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-min-js', ZWSGR_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-js', ZWSGR_URL . 'assets/js/admin.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// admin css
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-min-css', ZWSGR_URL . 'assets/css/admin.min.css', array(), ZWSGR_VERSION );
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-css', ZWSGR_URL . 'assets/css/admin.css', array(), ZWSGR_VERSION );			
		
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
				'ajax_url' 					   => admin_url('admin-ajax.php'),
				'nonce' 					   => wp_create_nonce('toggle-visibility-nonce'),
				'zwsgr_queue_manager_nounce'   => wp_create_nonce('zwsgr_queue_manager_nounce'),
				'zwsgr_add_update_reply_nonce' => wp_create_nonce('zwsgr_add_update_reply_nonce'),
				'zwsgr_delete_review_reply'	   => wp_create_nonce('zwsgr_delete_review_reply'),
				'zwsgr_wp_review_id' 		   => ( is_admin() && isset( $_GET['post'] ) ) ? $_GET['post'] : 0,
			));

			//Save Widget Ajax
			wp_localize_script(ZWSGR_PREFIX . '-admin-js', 'my_widget', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('my_widget_nonce')
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
				'supports' => array('title', 'editor')
			);

			register_post_type(ZWSGR_POST_WIDGET_TYPE, $args);
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
				'supports' => array('title', 'editor')
			);

			register_post_type(ZWSGR_POST_REVIEW_TYPE, $args);
		}

		// Register a single meta box to display all review details
		function zwsgr_add_review_meta_box() {
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
		function zwsgr_display_review_details_meta_box($zwsgr_review) {
			// Retrieve review fields
			$zwsgr_account_number 	  = get_post_meta($zwsgr_review->ID, 'zwsgr_account_number', true);
			$zwsgr_location_code 	  = get_post_meta($zwsgr_review->ID, 'zwsgr_location_code', true);
			$zwsgr_review_id 		  = get_post_meta($zwsgr_review->ID, 'zwsgr_review_id', true);
			$zwsgr_review_comment	  = get_post_meta($zwsgr_review->ID, 'zwsgr_review_comment', true);
			$zwsgr_reviewer_name 	  = get_post_meta($zwsgr_review->ID, 'zwsgr_reviewer_name', true);
			$zwsgr_review_star_rating = get_post_meta($zwsgr_review->ID, 'zwsgr_review_star_rating', true);
			$zwsgr_reply_comment 	  = get_post_meta($zwsgr_review->ID, 'zwsgr_reply_comment', true);
			$zwsgr_reply_update_time  = get_post_meta($zwsgr_review->ID, 'zwsgr_reply_update_time', true);

			echo '<table class="form-table" id="gmb-review-data">
				<tr>
					<th>
						<label for="zwsgr_review_id">' . __('Account ID', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_account_number) . '" name="zwsgr_account_number" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_id">' . __('Location', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_location_code) . '" name="zwsgr_location_code" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_id">' . __('Review ID', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_review_id) . '" name="zwsgr_review_id" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_reviewer_name">' . __('Reviewer Name', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_reviewer_name) . '" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_comment">' . __('Reviewer Comment', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<textarea readonly class="regular-text" rows="5" style="width:100%;">' . esc_textarea($zwsgr_review_comment) . '</textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_review_star_rating">' . __('Star Rating', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_review_star_rating) . '" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_reply_update_time">' . __('Reply Update Time', 'zw-smart-google-reviews') . '</label>
					</th>
					<td>
						<input type="text" value="' . esc_attr($zwsgr_reply_update_time) . '" readonly class="regular-text" style="width:100%;" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="zwsgr_reply_comment"> ' . __('Reply Comment', 'zw-smart-google-reviews') . ' </label>
					</th>
					<td>
						<div id="json-response-message" style="margin-bottom: 10px; color: green;"></div>
						
						<textarea name="zwsgr_reply_comment" class="regular-text" rows="5" style="width:100%;">
							' . esc_textarea($zwsgr_reply_comment) . '
						</textarea>';

						if (!empty($zwsgr_reply_comment)) {
							echo '<button class="button button-primary button-large" id="update-replay"> ' . __('Update', 'zw-smart-google-reviews') . ' </button>';
						} else {
							echo '<button class="button button-primary button-large" id="add-replay"> ' . __('Add Replay', 'zw-smart-google-reviews') . ' </button>';
						}
						if (!empty($zwsgr_reply_comment)) {
							echo '<button class="button button-primary button-large" id="delete-replay">' . __('Delete', 'zw-smart-google-reviews') . '</button>';
						}
					echo '</td>
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

		// Register the meta box
		function zwsgr_add_account_number_meta_box() {
			add_meta_box(
				'zwsgr_account_number_meta_box', // Meta box ID
				__('Account Number', 'zw-smart-google-reviews'), // Title
				array($this, 'zwsgr_display_account_number_meta_box'), // Callback function to display the meta box content
				'zwsgr_request_data', // Post type
				'normal', // Context (where to display: 'normal', 'side', 'advanced')
				'high' // Priority (high, default, low)
			);
		}

		/**
		 * Filter: manage_zuserreg_data_posts_columns
		 *
		 * - Used to add new column fields for the "zuserreg_data" CPT
		 *
		 * @method filter__zwsgr_manage_zuserreg_data_posts_columns
		 *
		 * @param  array $columns
		 *
		 * @return array
		 */
		function filter__zwsgr_manage_data_posts_columns($columns)
		{
			unset($columns['date']);
			unset($columns['title']);
			$columns['title'] = __('Review', 'zw-google-reviews');
			$columns[ZWSGR_META_PREFIX . 'user_login'] = __('Hide', 'zw-google-reviews');
			$columns['date'] = __('Date', 'zw-google-reviews');
			return $columns;
		}

		/**
		 * Render the visibility column content
		 *
		 * @param string $column
		 * @param int $post_id
		 */
		function render_hide_column_content( $column, $post_id ) {
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
		function zwsgr_toggle_visibility() {
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
			register_setting('zwsgr_advanced_account_settings', 'zwsgr_multilingual_support');
			register_setting('zwsgr_advanced_account_settings', 'zwsgr_spam_keywords');
			register_setting('zwsgr_advanced_account_settings', 'zwsgr_spam_detection_enabled');

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
			echo '<input type="text" id="zwsgr_google_business_api_key" name="zwsgr_google_business_api_key" value="' . esc_attr($value) . '" />';
		}

		function zwsgr_google_place_id_callback()
		{
			$value = get_option('zwsgr_google_business_place_id', '');
			echo '<input type="text" id="zwsgr_google_business_place_id" name="zwsgr_google_business_place_id" value="' . esc_attr($value) . '" />';
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
			echo '<label>Custom Email Addresses</label>';
			echo '<input type="text" id="zwsgr_admin_notification_emails" name="zwsgr_admin_notification_emails" rows="5" cols="50" value="' . esc_attr($value) . '" />';
			echo '<p class="description">' . __('Enter email addresses separated by commas.', 'zw-smart-google-reviews') . '</p>';
		}

		function zwsgr_admin_notification_emails_subject_callback()
		{
			$value = get_option('zwsgr_admin_notification_emails_subject', '');
			echo '<label>Custom Email Subject</label>';
			echo '<input type="text" id="zwsgr_admin_notification_emails_subject" name="zwsgr_admin_notification_emails_subject" rows="5" cols="50" value="' . esc_attr(
				$value) . '" />';
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
				echo '<label for="zwsgr_admin_notification_email_body">' . __('Email Body', 'zw-smart-google-reviews') . '</label>';
				wp_editor($value, 'zwsgr_admin_notification_email_body', $settings);
				echo '<p class="description">' . __('Enter your custom email body content here.', 'zw-smart-google-reviews') . '</p>';
			echo '</div>';
		}
		

		//Advance Section 
		function zwsgr_sync_reviews_callback()
		{
			$value = get_option('zwsgr_sync_reviews', 'daily');
			echo '<select id="zwsgr_sync_reviews" name="zwsgr_sync_reviews">
					<option value="daily" ' . selected($value, 'daily', false) . '>Daily</option>
					<option value="weekly" ' . selected($value, 'weekly', false) . '>Weekly</option>
					<option value="monthly" ' . selected($value, 'monthly', false) . '>Monthly</option>
				</select>';
		}

		function zwsgr_google_analytics_tracking_id_callback()
		{
			$value = get_option('zwsgr_google_analytics_tracking_id', '');
			echo '<input type="text" id="zwsgr_google_analytics_tracking_id" name="zwsgr_google_analytics_tracking_id" value="' . esc_attr($value) . '" />';
			echo '<p class="description">' . __('Enter your Google Analytics Tracking ID (e.g., UA-XXXXXXXXX-Y).', 'zw-smart-google-reviews') . '</p>';
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
			}
			
			// Now render the form and tabs
			$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'google';
			?>
			<div class="wrap">
				<h1 class="zwsgr-page-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
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
					<form action="options.php" method="post">
						<?php
						settings_fields('zwsgr_google_account_settings');
						do_settings_sections('zwsgr_google_account_settings');
						submit_button('Save Google Settings');
						?>
					</form>
				<?php elseif ($current_tab === 'notifications'): ?>
					<form id="notification-form" action="" method="post">
						<?php
						settings_fields('zwsgr_notification_settings');
						do_settings_sections('zwsgr_notification_settings');
						?>
						<div class="notification-fields">
							<?php $this->zwsgr_admin_notification_emails_callback(); ?>
							<?php $this->zwsgr_admin_notification_emails_subject_callback(); ?>
							<?php $this->zwsgr_admin_notofication_email_body_callback(); ?>
						</div>
						<!-- Error message container -->
						<span id="email-error" style="color: red; display: none;"></span>
						<!-- Success message container -->
						<span id="email-success" style="color: green; display: none;"></span>
						<?php submit_button('Send Notification Emails'); ?>
					</form>
				<?php elseif ($current_tab === 'advanced'): ?>
					<form action="options.php" method="post">
						<?php
						settings_fields('zwsgr_advanced_account_settings');
						do_settings_sections('zwsgr_advanced_account_settings');
						submit_button('Save Advanced Settings');
						?>
					</form>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Action: action_admin_menu
		 * Add admin registration Dashboard page for the plugin
		 * @method zwsgr_dashboard_page
		 */
		function zwsgr_dashboard_callback()
		{	
			echo '<h1>Dashboard</h1>';
			// Dashboard content can be added here
		}

		/**
		 * Action: action_admin_menu
		 * Add admin registration Dashboard page for the plugin
		 * @method zwsgr_dashboard_page
		 */

		function zwsgr_widget_configurator_callback() 
		{

			$post_id = $_GET['post_id'];
			$post_objct = get_post($post_id);
			if (!isset($post_id) || !$post_objct ) {
				wp_die( 'Invalid post ID.' ) ;
			}

			// Get stored widget settings
			$display_option = get_post_meta($post_id, 'display_option', true);
			$layout_option = get_post_meta($post_id, 'layout_option', true);
			$selected_elements = get_post_meta($post_id, 'selected_elements', true);
			$rating_filter = get_post_meta($post_id, 'rating_filter', true);
			$keywords = get_post_meta($post_id, 'keywords', true);
			$date_format = get_post_meta($post_id, 'date_format', true);
			$char_limit = get_post_meta($post_id, 'char_limit', true);
			$language = get_post_meta($post_id, 'language', true);
			$sort_by = get_post_meta($post_id, 'sort_by', true);
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);
			$google_review_toggle = get_post_meta($post_id, 'google_review_toggle', true);
			$bg_color = get_post_meta($post_id, 'bg_color', true);
			$text_color = get_post_meta($post_id, 'text_color', true);
			$posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

			$selected_elements = is_array($selected_elements) ? $selected_elements : [];
			$selected_display_option = !empty($display_option) ? $display_option : 'all'; // Default to 'all'
			$selected_layout_option = !empty($layout_option) ? $layout_option : '';
			// $generated_shortcode = '[zwsgr_widget_configurator id="' . esc_attr($display_option) . '" layout_option="' . esc_attr($layout_option) . '"]';
			// Generate the shortcode by calling the new function
			$generated_shortcode = $this->generate_shortcode($post_id);

			// Define your options and layouts with corresponding HTML content
			$options = [
				'slider' => [
					'<div class="slider-item" id="slider1">
						<div class="slider-1">
							<div class="slide-item">
								<h2 class="title">Title1</h2>
								<h5 class="date" data-original-date="25/10/2024">25/10/2024</h5>
								<h6 class="rating">5 Star</h6>
								<p class="content"> Tech support was super helpful in getting a payment gateway set up for a client. They were professional, friendly, patient, responsive, and knowledgeable, and the client is thrilled with the result! </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<h5 class="date" data-original-date="24/10/2024">24/10/2024</h5>
								<h6 class="rating">4 Star</h6>
								<p class="content"> Tech support was super helpful in getting a payment gateway set up for a client. They were professional, friendly, patient, responsive, and knowledgeable, and the client is thrilled with the result! </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title3</h2>
								<h5 class="date" data-original-date="23/10/2024">23/10/2024</h5>
								<h6 class="rating">3 Star</h6>
								<p class="content"> Tech support was super helpful in getting a payment gateway set up for a client. They were professional, friendly, patient, responsive, and knowledgeable, and the client is thrilled with the result! </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title4</h2>
								<h5 class="date" data-original-date="22/10/2024">22/10/2024</h5>
								<h6 class="rating">2 Star</h6>
								<p class="content"> Tech support was super helpful in getting a payment gateway set up for a client. They were professional, friendly, patient, responsive, and knowledgeable, and the client is thrilled with the result! </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title5</h2>
								<h5 class="date" data-original-date="21/10/2024">21/10/2024</h5>
								<h6 class="rating">1 Star</h6>
								<p class="content"> Tech support was super helpful in getting a payment gateway set up for a client. They were professional, friendly, patient, responsive, and knowledgeable, and the client is thrilled with the result! </p>
							</div>
						</div>
					</div>',
					'<div class="slider-item" id="slider2">
						<div class="slider-2">
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<p class="content"> Lorem2 </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<p class="content"> Lorem2 </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<p class="content"> Lorem2 </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<p class="content"> Lorem2 </p>
							</div>
							<div class="slide-item">
								<h2 class="title">Title2</h2>
								<p class="content"> Lorem2 </p>
							</div>
						</div>
					</div>',
					'<div class="slider-item" id="slider3">Slider 3 Content</div>',
					'<div class="slider-item" id="slider4">Slider 4 Content</div>',
					'<div class="slider-item" id="slider5">Slider 5 Content</div>'
				],
				'grid' => [
					'<div class="grid-item" id="grid1">Grid 1 Content</div>',
					'<div class="grid-item" id="grid2">Grid 2 Content</div>',
					'<div class="grid-item" id="grid3">Grid 3 Content</div>',
					'<div class="grid-item" id="grid4">Grid 4 Content</div>',
					'<div class="grid-item" id="grid5">Grid 5 Content</div>'
				],
				'list' => [
					'<div class="list-item" id="list1">List 1 Content</div>',
					'<div class="list-item" id="list2">List 2 Content</div>',
					'<div class="list-item" id="list3">List 3 Content</div>',
					'<div class="list-item" id="list4">List 4 Content</div>',
					'<div class="list-item" id="list5">List 5 Content</div>'
				],
				'badge' => [
					'<div class="badge-item" id="badge1">Badge 1 Content</div>',
					'<div class="badge-item" id="badge2">Badge 2 Content</div>',
					'<div class="badge-item" id="badge3">Badge 3 Content</div>',
					'<div class="badge-item" id="badge4">Badge 4 Content</div>',
					'<div class="badge-item" id="badge5">Badge 5 Content</div>'
				],
				'popup' => [
					'<div class="popup-item" id="popup1">Popup 1 Content</div>',
					'<div class="popup-item" id="popup2">Popup 2 Content</div>',
					'<div class="popup-item" id="popup3">Popup 3 Content</div>',
					'<div class="popup-item" id="popup4">Popup 4 Content</div>',
					'<div class="popup-item" id="popup5">Popup 5 Content</div>'
				]
			];
			?>
			
			<div class="zwsgr-dashboard">
    			<h3>Select Display Options</h3>

				<!-- Tab Navigation -->
				<ul class="tab-nav">
					<li class="tab-item active" data-tab="tab-options">Select Display Options</li>
					<li class="tab-item" data-tab="tab-selected">Selected Option</li>
					<li class="tab-item" data-tab="tab-shortcode">Generated Shortcode</li>
				</ul>

				<!-- Tab Content Areas -->
				<div class="tab-content" id="tab-options">
					<!-- Dynamically Render Radio Buttons -->
					<label><input type="radio" name="display_option" value="all" <?php echo $selected_display_option === 'all' ? 'checked' : ''; ?>> All</label><br>
					<?php foreach ($options as $key => $layouts) : ?>
						<label><input type="radio" name="display_option" value="<?php echo esc_attr($key); ?>" <?php echo $selected_display_option === $key ? 'checked' : ''; ?>> <?php echo ucfirst($key); ?></label><br>
					<?php endforeach; ?>

					<!-- Dynamically Render Layout Options Based on Selected Display Option -->
					<div id="layout-options">
						<?php
						foreach ($options as $option_type => $layouts) {
							$layout_count = 1;
							foreach ($layouts as $layout_content) {
								$element_id = $option_type . '-' . $layout_count;

								// Only show layouts for the selected display option
								$display_style = ($selected_display_option === $option_type || $selected_display_option === 'all') ? 'block' : 'none';
								$selected_class = ($element_id === $layout_option) ? ' selected' : ''; // Check if this layout is selected

								echo '<div id="' . esc_attr($element_id) . '" class="option-item' . $selected_class . '" data-type="' . esc_attr($option_type) . '" style="display: ' . $display_style . ';">';
								echo $layout_content;
								echo '<button class="select-btn" data-option="' . esc_attr($element_id) . '"' . ($element_id === $selected_layout_option ? ' selected' : '') . '>Select Option</button>';
								echo '</div>';

								$layout_count++;
							}
						}
						?>
					</div>
				</div>

				<div class="tab-content" id="tab-selected" style="display:none;">
					<h3>Selected Option</h3>
					<div id="selected-option-display" class="selected-option-display"><?php //echo wp_kses_post($selected_html); ?></div>
					<div class="widget-settings">
						<h2>Widget Settings</h2>
						<div>
							<h3>Hide Element</h3>
							<ul>
								<li>
									<input type="checkbox" id="review-title" name="review-element" value="review-title" 
									<?php echo in_array('review-title', $selected_elements) ? 'checked' : ''; ?>>
									<label for="review-title">Title</label>
								</li>
								<li>
									<input type="checkbox" id="review-rating" name="review-element" value="review-rating" 
									<?php echo in_array('review-rating', $selected_elements) ? 'checked' : ''; ?>>
									<label for="review-rating">Rating</label>
								</li>
								<li>
									<input type="checkbox" id="review-days-ago" name="review-element" value="review-days-ago" 
									<?php echo in_array('review-days-ago', $selected_elements) ? 'checked' : ''; ?>>
									<label for="review-days-ago">Days Ago</label>
								</li>
								<li>
									<input type="checkbox" id="review-content" name="review-element" value="review-content" 
									<?php echo in_array('review-content', $selected_elements) ? 'checked' : ''; ?>>
									<label for="review-content">Review Content</label>
								</li>
								<li>
									<input type="checkbox" id="review-photo" name="review-element" value="review-photo" 
									<?php echo in_array('review-photo', $selected_elements) ? 'checked' : ''; ?>>
									<label for="review-photo">Reviewer Photo</label>
								</li>
								<!-- Add more elements as needed -->
							</ul>
						</div>
						<div>
							<h3>Filter Rating</h3>
							<div class="filter-rating">
								<span class="star-filter" data-rating="5" title="5 Stars">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="#ccc" />
									</svg>
								</span>
								<span class="star-filter" data-rating="4" title="4 Stars">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="#ccc" />
									</svg>
								</span>
								<span class="star-filter" data-rating="3" title="3 Stars">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="#ccc" />
									</svg>
								</span>
								<span class="star-filter" data-rating="2" title="2 Stars">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="#ccc" />
									</svg>
								</span>
								<span class="star-filter" data-rating="1" title="1 Star">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27Z" fill="#ccc" />
									</svg>
								</span>
							</div>
						</div>

						<div>
							<h3>Keywords</h3>
							<label for="keywords-input">Enter Keywords (separate by commas):</label>
							<input type="text" id="keywords-input" name="keywords-input" placeholder="e.g., keyword1, keyword2, keyword3">
							<small>Type keywords separated by commas</small>

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
  
						<div>
							<h3>Date Format</h3>
							<select id="date-format-select" name="date-format">
								<option value="DD/MM/YYYY" <?php echo ($date_format === 'DD/MM/YYYY') ? 'selected' : ''; ?>>DD/MM/YYYY</option>
								<option value="MM-DD-YYYY" <?php echo ($date_format === 'MM-DD-YYYY') ? 'selected' : ''; ?>>MM-DD-YYYY</option>
								<option value="YYYY/MM/DD" <?php echo ($date_format === 'YYYY/MM/DD') ? 'selected' : ''; ?>>YYYY/MM/DD</option>
								<option value="full" <?php echo ($date_format === 'full') ? 'selected' : ''; ?>>Full Date (e.g., January 1, 2024)</option>
								<option value="hide" <?php echo ($date_format === 'hide') ? 'selected' : ''; ?>>Hide</option>
							</select>
						</div>

						<div>
							<h3>Trim long reviews with a "read more" link</h3>
							<input type="number" id="review-char-limit" name="review-char-limit" min="10" placeholder="Enter character limit" value="<?php echo !empty($char_limit) ? esc_attr($char_limit) : ''; ?>">
						</div>

						<div>
							<h3>Language</h3>
							<select id="language-select" name="language">
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

						<div>
							<h3>Sort By</h3>
							<select id="sort-by-select" name="sort_by">
								<option value="relevant" <?php echo ($sort_by === 'relevant') ? 'selected' : ''; ?>>Most Relevant</option>
								<option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest</option>
								<option value="highest" <?php echo ($sort_by === 'highest') ? 'selected' : ''; ?>>Highest Rating</option>
								<option value="lowest" <?php echo ($sort_by === 'lowest') ? 'selected' : ''; ?>>Lowest Rating</option>
							</select>
						</div>

						<div>
							<h3>Load More</h3>
							<label class="switch">
								<input type="checkbox" id="enable-load-more" name="enable_load_more" <?php echo ($enable_load_more) ? 'checked' : ''; ?>>
								<span class="slider"></span>
							</label>
						</div>

						<div id="load-more-settings" style="display: <?php echo ($enable_load_more) ? 'block' : 'none'; ?>">
							<label for="posts-per-page">Posts per page:</label>
							<select id="posts-per-page" name="posts_per_page">
								<option value="1" <?php echo ($posts_per_page == 1) ? 'selected' : ''; ?>>1</option>
								<option value="2" <?php echo ($posts_per_page == 2) ? 'selected' : ''; ?>>2</option>
								<option value="3" <?php echo ($posts_per_page == 3) ? 'selected' : ''; ?>>3</option>
								<option value="4" <?php echo ($posts_per_page == 4) ? 'selected' : ''; ?>>4</option>
								<option value="5" <?php echo ($posts_per_page == 5) ? 'selected' : ''; ?>>5</option>
							</select>
						</div>

						<div>
							<h3>Review us on Google</h3>
							<label class="switch">
								<input type="checkbox" id="toggle-google-review" name="google_review_toggle" <?php echo ($google_review_toggle) ? 'checked' : ''; ?>>
								<span class="slider"></span>
							</label>
						</div>

						<div id="color-picker-options" style="display: <?php echo ($google_review_toggle) ? 'block' : 'none'; ?>">
							<label for="bg-color-picker">Background Color:</label>
							<input type="color" id="bg-color-picker" name="bg_color_picker" value="<?php echo esc_attr($bg_color); ?>">

							<label for="text-color-picker">Text Color:</label>
							<input type="color" id="text-color-picker" name="text_color_picker" value="<?php echo esc_attr($text_color); ?>">
						</div>
					</div>
					<button id="save-get-code-btn">Save & Get Code</button>
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
		function save_widget_data() {
			// Check security nonce
			check_ajax_referer('my_widget_nonce', 'security');

			// Check security nonce
			if (!check_ajax_referer('my_widget_nonce', 'security', false)) {
				error_log('Nonce verification failed.');
				wp_send_json_error('Nonce verification failed.');
				return;
			}
			error_log('Nonce verified successfully.');

			// Get and sanitize post ID
			$post_id = intval($_POST['post_id']);
			if (!$post_id) {
				error_log('Invalid post ID');
				wp_send_json_error('Invalid post ID.');
				return;
			}
			error_log('Post ID: ' . $post_id);

			// Check if post exists
			if (get_post_status($post_id) === false) {
				error_log('Post does not exist: ' . $post_id);
				wp_send_json_error('Post does not exist.');
				return;
			}
			error_log('Post exists, ID: ' . $post_id);

			$post_id = intval($_POST['post_id']);
			if (!$post_id) {
				error_log('Invalid post ID');
				wp_send_json_error('Invalid post ID.');
				return;
			}

			// Check if the post exists
			if (get_post_status($post_id) === false) {
				error_log('Post does not exist: ' . $post_id);
				wp_send_json_error('Post does not exist.');
				return;
			}

			// Ensure user has permission to edit the post
			if (!current_user_can('edit_post', $post_id)) {
				error_log('User does not have permission to edit post: ' . $post_id);
				wp_send_json_error('You do not have permission to edit this post.');
				return;
			}
			
			$setting_tb = ( isset( $_POST['settings'] ) && ! empty( $_POST['settings'] ) ) ? sanitize_text_field( $_POST['settings'] ) : '';

			if(  $setting_tb == 'tab-options' ){
				$display_option = isset($_POST['display_option']) ? sanitize_text_field($_POST['display_option']) : '';
				update_post_meta($post_id, 'display_option', $display_option);

				$layout_option =  isset($_POST['layout_option']) ? sanitize_text_field($_POST['layout_option']) : '';
				update_post_meta($post_id, 'layout_option', $layout_option);

			}

			$selected_elements = isset($_POST['selected_elements']) ? array_map('sanitize_text_field', $_POST['selected_elements']) : array();
			$rating_filter = isset($_POST['rating_filter']) ? sanitize_text_field($_POST['rating_filter']) : '';
			$keywords = isset($_POST['keywords']) ? array_map('sanitize_text_field', $_POST['keywords']) : [];
			$date_format = isset($_POST['date_format']) ? sanitize_text_field($_POST['date_format']) : '';
			$char_limit = isset($_POST['char_limit']) ? intval($_POST['char_limit']) : 0;
			$language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
			$sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : '';
			$enable_load_more = isset($_POST['enable_load_more']) ? intval($_POST['enable_load_more']) : 0;
			$google_review_toggle = isset($_POST['google_review_toggle']) ? intval($_POST['google_review_toggle']) : 0;
			$bg_color = isset($_POST['bg_color']) ? sanitize_hex_color($_POST['bg_color']) : '';
			$text_color = isset($_POST['text_color']) ? sanitize_hex_color($_POST['text_color']) : '';
			$posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 5; // default to 5

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
			update_post_meta($post_id, 'posts_per_page', $posts_per_page);
		
			// Respond with success message
			wp_send_json_success('Settings updated successfully.' . $setting_tb );
		}

		function generate_shortcode($post_id) {
			// Build the shortcode with attributes
			$shortcode = '[zwsgr_widget_configurator post-id="' . esc_attr($post_id) . '"]';
			update_post_meta($post_id, '_generated_shortcode_new', $shortcode);
			return $shortcode;
		}		
	}
}
