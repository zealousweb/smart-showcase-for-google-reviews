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
		
		function __construct() 
		{
			add_action('wp_enqueue_scripts', array($this, 'ZWSGR_lib_public_enqueue'));  

			add_shortcode( 'zwsgr_widget', array($this,'shortcode_load_more'));
			add_action('wp_ajax_load_more_meta_data', array($this,'load_more_meta_data'));
			add_action('wp_ajax_nopriv_load_more_meta_data', array($this,'load_more_meta_data'));


		}
		function ZWSGR_lib_public_enqueue() 
		{
			wp_enqueue_script( ZWSGR_PREFIX . '_script_js', ZWSGR_URL . 'assets/js/script.js', array( 'jquery-core' ), ZWSGR_VERSION, true );
			
			wp_localize_script(ZWSGR_PREFIX . '_script_js', 'load_more', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('zwsgr_load_more_nonce')
			));
		}
		
		// Shortcode to render initial posts and Load More button
		function shortcode_load_more($atts) 
		{
			// Extract the attributes passed to the shortcode
			$atts = shortcode_atts(
				array(
					'post-id' => '',  // Default value for the post-id attribute
				),
				$atts,
				'zwsgr_widget'
			);

			// Retrieve the post ID from the shortcode attributes
			$post_id = $atts['post-id'];

			// Check if a post ID is provided and it exists
			if (empty($post_id) || !get_post($post_id)) {
				return esc_html__('Invalid post ID.', 'zw-smart-google-reviews');
			}

			// Retrieve the 'enable_load_more' setting from post meta
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);

			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

			// Determine the number of posts per page based on 'enable_load_more'
			$posts_per_page = $enable_load_more ? ($stored_posts_per_page ? intval($stored_posts_per_page) : 2) : -1; 
			// If 'enable_load_more' is enabled, use the stored 'posts_per_page', default to 2 if not set, otherwise show all posts.

			// Retrieve the 'rating_filter' value from the post meta
			$rating_filter = intval(get_post_meta($post_id, 'rating_filter', true)) ?: 0;

			// Define the mapping from numeric values to words
			$rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word
			$rating_filter_word = isset($rating_mapping[$rating_filter]) ? $rating_mapping[$rating_filter] : '';

			// Define the ratings to include based on the rating filter
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

			// Query for posts
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Your custom post type slug
				'posts_per_page' => $posts_per_page,         // Use dynamic posts per page value
				'paged'          => 1,                      // Initial page number
				'meta_query'     => array(
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					)
				),
			);
			$query = new WP_Query($args);

			ob_start();  // Start output buffering

			if ($query->have_posts()) {
				echo '<div id="div-container">';

				// Loop through the posts and display them
				while ($query->have_posts()) {
					$query->the_post();
					
					// Retrieve the meta values
					$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
					$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
					$zwsgr_review_comment   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
					$published_date  = get_the_date('F j, Y');
					$post_date = get_the_date('U');
					$days_ago = floor((time() - $post_date) / (60 * 60 * 24));
					$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
					$char_limit = (int) $char_limit ; 

					// Check if the comment length exceeds the character limit
					$is_trimmed = mb_strlen($zwsgr_review_comment) > $char_limit;

					// Trim the comment if necessary
					$trimmed_comment = $is_trimmed ? mb_substr($zwsgr_review_comment, 0, $char_limit) . '...' : $zwsgr_review_comment;

					echo '<div class="zwsgr-review-post-item">';
					echo '<h2>' . esc_html(get_the_title()) . '</h2>';

					// Render reviewer name, rating, and comment
					echo '<p><strong>Reviewer:</strong> ' . esc_html($zwsgr_reviewer_name) . '</p>';
					echo '<p><strong>Rating:</strong> ' . esc_html($zwsgr_review_star_rating) . ' Stars</p>';
					echo '<p><strong>Comment:</strong> ' . esc_html($trimmed_comment) ;
					if ($is_trimmed) {
						// Add a "Read more" link
						echo '<a href="javascript:void(0);" class="toggle-comment" data-full-text="' . esc_attr($zwsgr_review_comment) . '">Read more</a>';
					}
					'</p>';
					echo '<p><strong>Published:</strong> ' . esc_html($published_date) . ' (' . esc_html($days_ago) . ' days ago)</p>';
					echo '</div>';
				}

				echo '</div>';

				// Add the Load More button only if 'enable_load_more' is true
				if ($enable_load_more && $query->max_num_pages >=2) {
					echo '<button class="load-more-meta" data-page="2" data-post-id="' . esc_attr($post_id) . '" data-rating-filter="' . esc_attr($rating_filter) . '">' . esc_html__('Load More', 'zw-smart-google-reviews') . '</button>';
				}
			} else {
				echo '<p>' . esc_html__('No posts found.', 'zw-smart-google-reviews') . '</p>';
			}

			// Reset post data
			wp_reset_postdata();

			return ob_get_clean();  // Return the buffered content
		}
		
		function load_more_meta_data() 
		{
			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'zwsgr_load_more_nonce')) {
				wp_send_json_error(esc_html__('Nonce verification failed.', 'zw-smart-google-reviews'));
				return;
			}

			// Retrieve the page number and post_id
			$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			// Ensure the post_id exists
			if (empty($post_id) || !get_post($post_id)) {
				wp_send_json_error(esc_html__('Invalid post ID.', 'zw-smart-google-reviews'));
				return;
			}

			// Retrieve the 'enable_load_more' setting from post meta
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);

			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

			// Determine the number of posts per page based on 'enable_load_more'
			$posts_per_page = $enable_load_more ? ($stored_posts_per_page ? intval($stored_posts_per_page) : 2) : -1;

			// Retrieve the 'rating_filter' value from the post meta
			$rating_filter = intval(get_post_meta($post_id, 'rating_filter', true)) ?: 0;

			// Define the mapping from numeric values to words
			$rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word
			$rating_filter_word = isset($rating_mapping[$rating_filter]) ? $rating_mapping[$rating_filter] : '';

			// Define the ratings to include based on the rating filter
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

			// Query for posts based on the current page
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Replace with your custom post type slug
				'posts_per_page' => $posts_per_page,  // Use dynamic posts per page value
				'paged'          => $page,
				'orderby'        => 'date',
				'meta_query'     => array(
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					)
				),
			);

			$query = new WP_Query($args);

			if ($query->have_posts()) {
				$output = '';

				// Loop through the posts and append the HTML content
				while ($query->have_posts()) {
					$query->the_post();

					// Retrieve meta values
					$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
					$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
					$zwsgr_review_comment   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
					$published_date  = get_the_date('F j, Y');
					$post_date = get_the_date('U');
					$days_ago = floor((time() - $post_date) / (60 * 60 * 24));
					$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
					$char_limit = (int) $char_limit ;

					// Check if the comment length exceeds the character limit
					$is_trimmed = mb_strlen($zwsgr_review_comment) > $char_limit;

					// Trim the comment if necessary
					$trimmed_comment = $is_trimmed ? mb_substr($zwsgr_review_comment, 0, $char_limit) . '...' : $zwsgr_review_comment;

					// Output the content, including the meta values
					$output .= '<div class="zwsgr-review-post-item">';
					$output .= '<h2>' . esc_html(get_the_title()) . '</h2>';  // Post title

					// Display the reviewer name, rating, comment, and days ago
					$output .= '<p><strong>Reviewer Name:</strong> ' . esc_html($zwsgr_reviewer_name) . '</p>';
					$output .= '<p><strong>Rating:</strong> ' . esc_html($zwsgr_review_star_rating) . ' stars</p>';
					$output .= '<p><strong>Comment:</strong> ' . esc_html($trimmed_comment);
					if ($is_trimmed) {
						// Add a "Read more" link
						$output .= '<a href="javascript:void(0);" class="toggle-comment" data-full-text="' . esc_attr($zwsgr_review_comment) . '">Read more</a>';
					}
					$output .= '<p><strong>Published:</strong> ' . esc_html($published_date) . ' (' . esc_html($days_ago) . ' days ago)</p>';
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
				wp_send_json_error(esc_html__('No more posts.', 'zw-smart-google-reviews'));
			}

			wp_die();  // Properly terminate the AJAX request
		}
	}
}

