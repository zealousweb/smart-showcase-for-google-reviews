<?php
/**
 * ZWSGR_Front_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSGR_Front_Action' ) ){

	/**
	 *  The ZWSGR_Front_Action Class
	 */
	class ZWSGR_Front_Action {

		function __construct()  {
			add_action('wp_enqueue_scripts',array( $this, 'ZWSGR_public_enqueue'));	
			add_shortcode( 'zwsgr_visible_reviews', array( $this, 'display_visible_reviews' ) );
		}

		function ZWSGR_public_enqueue() {
			wp_enqueue_style( ZWSGR_PREFIX . '-front-css', ZWSGR_URL . 'assets/css/front.min.css', array(), ZWSGR_VERSION );
			wp_enqueue_script( ZWSGR_PREFIX . '_front_js', ZWSGR_URL . 'assets/js/front.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
		}

		/**
		 * Shortcode to display only visible reviews
		 *
		 * @param array $atts Shortcode attributes
		 * @return string HTML content of visible reviews
		 */
		function display_visible_reviews( $atts ) {
			// Set default attributes if needed
			$atts = shortcode_atts( array(
				'posts_per_page' => -1, // Default number of posts to display
			), $atts, 'zwsgr_visible_reviews' );

			// Query only posts that are "visible" (i.e., don't have the '_is_hidden' meta key set)
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE, // Replace with your post type slug
				'posts_per_page' => intval( $atts['posts_per_page'] ),
				'meta_query'     => array(
					array(
						'key'     => '_is_hidden',
						'compare' => 'NOT EXISTS', // This ensures we only get posts where _is_hidden is not set
					),
				),
			);

			$visible_reviews = new WP_Query( $args );

			// Start output buffer to capture HTML output
			ob_start();

			if ( $visible_reviews->have_posts() ) {
				echo '<div class="zwsgr-visible-reviews">';
				while ( $visible_reviews->have_posts() ) {
					$visible_reviews->the_post();

					// Customize how you want to display the post data
					echo '<div class="zwsgr-review">';
					echo '<h3>' . get_the_title() . '</h3>';
					echo '<div class="zwsgr-review-content">' . get_the_content() . '</div>';
					echo '</div>';
				}
				echo '</div>';

				wp_reset_postdata(); // Reset the global $post object
			} else {
				echo '<p>No reviews available at the moment.</p>';
			}

			// Return the output buffer content
			return ob_get_clean();
		}
	}
}
