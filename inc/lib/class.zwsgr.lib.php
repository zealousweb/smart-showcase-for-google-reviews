<?php
/**
 * ZWSGR_Lib Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Lib' ) ) {

	/**
	 * The main ZWSGR class
	 */
	class ZWSGR_Lib {
		
		function __construct() {
			add_action('wp_enqueue_scripts', array($this, 'ZWSGR_lib_public_enqueue'));  

			add_shortcode( 'zwsgr_widget_configurator', array($this,'shortcode_load_more'));
			add_action('wp_ajax_load_more_meta_data', array($this,'load_more_meta_data'));
			add_action('wp_ajax_nopriv_load_more_meta_data', array($this,'load_more_meta_data'));


		}
		function ZWSGR_lib_public_enqueue() {
			wp_enqueue_script( ZWSGR_PREFIX . '_script_js', ZWSGR_URL . 'assets/js/script.js', array( 'jquery-core' ), ZWSGR_VERSION, true );
			
			wp_localize_script(ZWSGR_PREFIX . '_script_js', 'load_more', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('zwsgr_load_more_nonce')
			));
		}
		
		// Shortcode to render initial posts and Load More button
		function shortcode_load_more($atts) {
			// Extract the attributes passed to the shortcode
			$atts = shortcode_atts(
				array(
					'post-id' => '',  // Default value for the post-id attribute
				),
				$atts,
				'zwsgr_widget_configurator'
			);
		
			// Retrieve the post ID from the shortcode attributes
			$post_id = $atts['post-id'];
		
			// Check if a post ID is provided and it exists
			if (empty($post_id) || !get_post($post_id)) {
				return 'Invalid post ID.';
			}
		
			// Retrieve the 'enable_load_more' setting from post meta
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);
		
			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);
		
			// Determine the number of posts per page based on 'enable_load_more'
			$posts_per_page = $enable_load_more ? ($stored_posts_per_page ? intval($stored_posts_per_page) : 2) : -1; 
			// If 'enable_load_more' is enabled, use the stored 'posts_per_page', default to 2 if not set, otherwise show all posts.
		
			// Query for posts
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Your custom post type slug
				'posts_per_page' => $posts_per_page,         // Use dynamic posts per page value
				'paged'          => 1,                      // Initial page number
			);
			$query = new WP_Query($args);
		
			ob_start();  // Start output buffering
		
			if ($query->have_posts()) {
				echo '<div id="div-container">';
		
				// Loop through the posts and display them
				while ($query->have_posts()) {
					$query->the_post();
					echo '<div>';
					echo '<h2>' . get_the_title() . '</h2>';
					echo '</div>';
				}
		
				echo '</div>';
		
				// Add the Load More button only if 'enable_load_more' is true
				if ($enable_load_more) {
					echo '<button class="load-more-meta" data-page="2" data-post-id="' . esc_attr($post_id) . '">Load More</button>';
				}
			} else {
				echo '<p>No posts found.</p>';
			}
		
			// Reset post data
			wp_reset_postdata();
		
			return ob_get_clean();  // Return the buffered content
		}
		
		// AJAX handler to load more posts
		function load_more_meta_data() {
			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'zwsgr_load_more_nonce')) {
				wp_send_json_error('Nonce verification failed.');
				return;
			}

			// Retrieve the page number and post_id
			$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			// Ensure the post_id exists
			if (empty($post_id) || !get_post($post_id)) {
				wp_send_json_error('Invalid post ID.');
				return;
			}

			// Retrieve the 'enable_load_more' setting from post meta
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);

			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

			// Determine the number of posts per page based on 'enable_load_more'
			$posts_per_page = $enable_load_more ? ($stored_posts_per_page ? intval($stored_posts_per_page) : 2) : -1;

			// Query for posts based on the current page
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Replace with your custom post type slug
				'posts_per_page' => $posts_per_page,  // Use dynamic posts per page value
				'paged'          => $page,
				'orderby'        => 'date',
			);

			$query = new WP_Query($args);

			if ($query->have_posts()) {
				$output = '';

				// Loop through the posts and append the HTML content
				while ($query->have_posts()) {
					$query->the_post();
					$output .= '<div class="post-item">';
					$output .= '<h2>' . get_the_title() . '</h2>';  // Customize the HTML as needed
					$output .= '</div>';
				}

				// Prepare the response with the loaded content and the new page number
				$response = array(
					'content'   => $output,  // Send HTML content in the response
					'new_page'  => $page + 1,
				);

				// Check if there are no more pages to load and disable the button
				if ($query->max_num_pages <= $page) {
					$response['disable_button'] = true;
				}

				wp_reset_postdata();

				// Send success response with content and page information
				wp_send_json_success($response);
			} else {
				// No more posts available
				wp_send_json_error('No more posts.');
			}

			wp_die();  // Properly terminate the AJAX request
		}

	}
}

