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

		function __construct()  {

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );
			add_action('admin_menu', array($this, 'zwsgr_admin_menu_registration'));
			add_action('admin_init', array($this, 'zwsgr_register_settings')); // Register settings when admin initializes
			add_action('init', array($this, 'zwsgr_register_widget_cpt'));  // Register Widget Custom Post Type
			add_action('init', array($this, 'zwsgr_register_review_cpt'));  // Register Review Custom Post Type
			
            		
		}
		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__admin_init() {

			// admin js
			wp_enqueue_script( ZWSGR_PREFIX . '-admin-js', ZWSGR_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), ZWSGR_VERSION );

			// admin css
			wp_enqueue_style( ZWSGR_PREFIX . '-admin-css', ZWSGR_URL . 'assets/css/admin.min.css', array(), ZWSGR_VERSION );
			
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
				'zwsgr_dashboard',
				__('Layout', 'zw-smart-google-reviews'),
				__('Layout', 'zw-smart-google-reviews'),
				'manage_options',
				'zwsgr_layout',
				array($this, 'zwsgr_layout_callback')
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
			register_setting('zwsgr_notification_settings', 'zwsgr_admin_notification_emails');
			register_setting('zwsgr_notification_settings', 'zwsgr_admin_notification_emails_subject');
			register_setting('zwsgr_notification_settings', 'zwsgr_admin_notification_email_body');
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

			add_settings_field(
				'zwsgr_admin_notification_emails',
				__('Custom Email Addresses', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_admin_notification_emails_callback'),
				'zwsgr_notification_settings',
				'zwsgr_notification_section'
			);

			add_settings_field(
				'zwsgr_admin_notification_emails_subject',
				__('Custom Email Subject', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_admin_notification_emails_subject_callback'),
				'zwsgr_notification_settings',
				'zwsgr_notification_section'
			);

			add_settings_field(
				'zwsgr_admin_notofication_email_body',
				__('Custom Email body', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_admin_notofication_email_body_callback'),
				'zwsgr_notification_settings',
				'zwsgr_notification_section'
			);

			add_settings_field(
				'zwsgr_google_analytics_tracking_id',
				__('Google Analytics Tracking ID', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_google_analytics_tracking_id_callback'),
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

			// Advanced settings
			add_settings_field(
				'zwsgr_sync_reviews',
				__('Sync Reviews', 'zw-smart-google-reviews'),
				array($this, 'zwsgr_sync_reviews_callback'),
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
			echo '<input type="checkbox" id="zwsgr_admin_notification_enabled" name="zwsgr_admin_notification_enabled" value="1" ' . checked(1, $value, false) . ' />';
			echo '<label for="zwsgr_admin_notification_enabled">' . __('Enable Admin Notifications', 'zw-smart-google-reviews') . '</label>';
		}

		function zwsgr_admin_notification_emails_callback()
		{
			$value = get_option('zwsgr_admin_notification_emails', '');
			echo '<input type="text" id="zwsgr_admin_notification_emails" name="zwsgr_admin_notification_emails" rows="5" cols="50" value="' . esc_attr($value) . '" />';
			echo '<p class="description">' . __('Enter email addresses separated by commas.', 'zw-smart-google-reviews') . '</p>';
		}

		function zwsgr_admin_notification_emails_subject_callback()
		{
			$value = get_option('zwsgr_admin_notification_emails_subject', '');
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
		
			// Output the WYSIWYG editor
			wp_editor($value, 'zwsgr_admin_notification_email_body', $settings);
			
			echo '<p class="description">' . __('Enter your custom email body content here.', 'zw-smart-google-reviews') . '</p>';
		}
		

		function zwsgr_google_analytics_tracking_id_callback()
		{
			$value = get_option('zwsgr_google_analytics_tracking_id', '');
			echo '<input type="text" id="zwsgr_google_analytics_tracking_id" name="zwsgr_google_analytics_tracking_id" value="' . esc_attr($value) . '" />';
			echo '<p class="description">' . __('Enter your Google Analytics Tracking ID (e.g., UA-XXXXXXXXX-Y).', 'zw-smart-google-reviews') . '</p>';
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

			?>
			<div class="wrap">
				<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
				<h2 class="nav-tab-wrapper">
					<a href="?page=zwsgr_settings&tab=google"
						class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'google') ? 'nav-tab-active' : ''; ?>"><?php _e('Google', 'zw-smart-google-reviews'); ?></a>
					<a href="?page=zwsgr_settings&tab=notifications"
						class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'notifications') ? 'nav-tab-active' : ''; ?>"><?php _e('SEO & Notifications', 'zw-smart-google-reviews'); ?></a>
					<a href="?page=zwsgr_settings&tab=advanced"
						class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'advanced') ? 'nav-tab-active' : ''; ?>"><?php _e('Advanced', 'zw-smart-google-reviews'); ?></a>
				</h2>

				<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'google'): ?>
					<form action="options.php" method="post">
						<?php
						settings_fields('zwsgr_google_account_settings');
						do_settings_sections('zwsgr_google_account_settings');
						submit_button('Save Google Settings');
						?>
					</form>
				<?php elseif ($_GET['tab'] === 'notifications'): ?>
					<form action="options.php" method="post">
						<?php
						settings_fields('zwsgr_notification_settings');
						do_settings_sections('zwsgr_notification_settings');
						submit_button('Save Notification Settings');
						?>
					</form>
				<?php elseif ($_GET['tab'] === 'advanced'): ?>
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

		 function zwsgr_layout_callback() {
			// Define your options and layouts with corresponding HTML content
			$options = [
				'slider' => [
					'<div class="slider-item" id="slider1">
						Slider 1 Content
						<ul>
							<li>1</li>
							<li>2</li>
							<li>3</li>
						</ul>
					</div>',
					'<div class="slider-item" id="slider2">Slider 2 Content</div>',
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
		
				<!-- Dynamically Render Radio Buttons -->
				<label><input type="radio" name="display_option" value="all" checked> All</label><br>
				<?php foreach ($options as $key => $layouts) : ?>
					<label><input type="radio" name="display_option" value="<?php echo $key; ?>"> <?php echo ucfirst($key); ?></label><br>
				<?php endforeach; ?>
		
				<!-- Dynamically Render Layout Options -->
				<?php
				$layout_count = 1;
				foreach ($options as $option_type => $layouts) {
					foreach ($layouts as $layout_content) {
						echo '<div id="layout-' . $layout_count . '" class="option-item" data-type="' . $option_type . '">';
						echo $layout_content; // Render the HTML content
						echo '<button class="select-btn" data-option="layout-' . $layout_count . '">Select Option</button>';
						echo '</div>';
						$layout_count++;
					}
				}
				?>
		
				<!-- Render selected option here -->
				<h3>Selected Option</h3>
				<div id="selected-option-display" class="selected-option-display"></div>
		
				<!-- Render generated shortcode here -->
				<h3>Generated Shortcode</h3>
				<div id="generated-shortcode-display" class="generated-shortcode-display"></div>
			</div>
		
			<!-- JavaScript for Dynamic Functionality -->
			<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', function () {
				const radioButtons = document.querySelectorAll('input[name="display_option"]');
				const selectButtons = document.querySelectorAll('.select-btn');
				const selectedOptionDisplay = document.getElementById('selected-option-display');
				const generatedShortcodeDisplay = document.getElementById('generated-shortcode-display');

				let currentDisplayOption = 'all';

				// Add event listeners to radio buttons for dynamic filtering
				radioButtons.forEach(radio => {
					radio.addEventListener('change', function () {
						currentDisplayOption = this.value;
						updateOptions(currentDisplayOption);
					});
				});

				// Function to show/hide options based on the selected radio button
				function updateOptions(value) {
					document.querySelectorAll('.option-item').forEach(item => {
						if (value === 'all' || item.getAttribute('data-type') === value) {
							item.style.display = 'block';
						} else {
							item.style.display = 'none';
						}
					});
				}

				// Set default view (show all options)
				updateOptions('all');

				// Function to render the selected option and generate the shortcode
				function renderSelectedOption(optionElement) {
					const layoutElement = optionElement.parentNode; // Get the parent element of the button
					const layoutHTML = layoutElement.innerHTML.replace(optionElement.outerHTML, ''); // Remove the button HTML

					// Extract the ID from the layout HTML
					const tempDiv = document.createElement('div');
					tempDiv.innerHTML = layoutHTML; // Load the HTML into a temporary div to extract the ID
					const idElement = tempDiv.querySelector('[id]'); // Get the first element with an ID
					const sectionID = idElement ? idElement.id : 'No ID Found'; // Get the ID or a fallback message

					selectedOptionDisplay.innerHTML = `
						<div class="selected-id">
							<strong>You selected:</strong> <p>${sectionID}</p>
						</div>
						<div class="selected-layout">
							${layoutHTML}
						</div>`; //  Separate divs for section ID and layout HTML		
								
					// Dynamically generate the shortcode based on the selected option and type
					const selectedType = layoutElement.getAttribute('data-type'); // Get the type of the layout
					generatedShortcodeDisplay.innerHTML = '[smart-google-reviews type="' + selectedType + '"]'; // Show the generated shortcode
				}

				// Add event listeners to select buttons for selecting an option
				selectButtons.forEach(button => {
					button.addEventListener('click', function () {
						renderSelectedOption(this); // Pass the selected button element
					});
				});
			});

			</script>
		
			<style>
				.option-item {
					display: none;
					margin: 5px 0;
					padding: 5px;
					border: 1px solid #ddd;
					background-color: #f9f9f9;
				}
		
				.selected-option-display,
				.generated-shortcode-display {
					margin-top: 20px;
					padding: 10px;
					background-color: #e0e0e0;
					border: 1px solid #ccc;
					font-size: 18px;
				}
			</style>
			<?php
		}
	}
}
