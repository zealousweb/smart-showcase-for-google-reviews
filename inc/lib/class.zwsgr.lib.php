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

			$sort_by = get_post_meta($post_id, 'sort_by', true)?: 'newest';
			$language = get_post_meta($post_id, 'language', true) ?: 'en'; 
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$months = $this->translate_months($language);
			
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
			// Adjust sorting based on the 'sort_by' value
			switch ($sort_by) {
				case 'newest':
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
					break;

				case 'highest':
					// Adjust the "highest" sort based on the selected rating filter
					if (!empty($rating_filter_word)) {
						// Sort by the highest rating within the selected filter group
						$args['meta_query'][0]['value'] = $rating_filter_word; // Limit to the selected rating
						$args['orderby'] = 'meta_value_num';
						$args['order'] = 'DESC';
					} else {
						// Default behavior if no filter is set
						$args['meta_query'][0]['value'] = 'FIVE';
						$args['orderby'] = 'meta_value_num';
						$args['order'] = 'DESC';
					}
					break;

				case 'lowest':
					$args['meta_query'][0]['value'] = 'ONE';
					$args['orderby'] = 'meta_value_num';
					$args['order'] = 'ASC';    
					break;

				default:
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
			
			$query = new WP_Query($args);

			ob_start();  // Start output buffering

			if ($query->have_posts()) {

				// Fetch selected elements from post meta
				$selected_elements = get_post_meta($post_id, 'selected_elements', true);
				$selected_elements = maybe_unserialize($selected_elements);

				echo '<div id="div-container">';

					// Loop through the posts and display them
					while ($query->have_posts()) {
						$query->the_post();
						
						// Retrieve the meta values
						$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
						$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
						$zwsgr_review_content   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
						$published_date  = get_the_date('F j, Y');
						$post_date = get_the_date('U');
						$days_ago = floor((time() - $post_date) / (60 * 60 * 24));
						$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
						$char_limit = (int) $char_limit ; 
						$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));

						// Determine if content is trimmed based on character limit
						$is_trimmed = $char_limit > 0 && mb_strlen($zwsgr_review_content) > $char_limit; // Check if the content length exceeds the character limit
						$trimmed_content = $is_trimmed ? mb_substr($zwsgr_review_content, 0, $char_limit) . '...' : $zwsgr_review_content; // Trim the content if necessary

						// Format the published date based on the selected format
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

						echo '<div class="zwsgr-slide-item">';
							echo '<div class="zwsgr-slide-wrap">';
						
								// Profile image
								if (!in_array('review-photo', $selected_elements)) {
									echo '<div class="zwsgr-profile">';
									echo '<img src="' . esc_url($plugin_dir_path . 'assets/images/testi-pic.png') . '">';
									echo '</div>';
								}
							
								// Reviewer info
								echo '<div class="zwsgr-review-info">';
									if (!in_array('review-title', $selected_elements)) {
										echo '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
									}
									if (!in_array('review-days-ago', $selected_elements)) {
										echo '<p class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '"><strong>Published:</strong> ' . esc_html($formatted_date) . ' (' . esc_html($days_ago) . ' days ago)</p>';
									}
								echo '</div>';
							
								// Google icon
								if (!in_array('review-photo', $selected_elements)) {
									echo '<div class="zwsgr-google-icon">';
									echo '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">';
									echo '</div>';
								}	
							echo '</div>'; // End of zwsgr-slide-wrap
						
							// Rating
							if (!in_array('review-rating', $selected_elements)) {
								echo '<div class="zwsgr-rating">' . esc_html($zwsgr_review_star_rating) . ' Stars</div>';
							}
						
							// Review content
							if (!in_array('review-content', $selected_elements)) {
								echo '<p class="zwsgr-content"><strong>Content:</strong>' . esc_html($trimmed_content);
								if ($is_trimmed) {
									echo ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">'.esc_html($this->translate_read_more($language)).'</a>';
			
								}
								echo '</p>';
							}
						echo '</div>'; // End of zwsgr-slide-item
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

			$sort_by = get_post_meta($post_id, 'sort_by', true)?: 'newest';
			$language = get_post_meta($post_id, 'language', true) ?: 'en'; 
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$months = $this->translate_months($language);

			// Query for posts based on the current page
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Replace with your custom post type slug
				'posts_per_page' => $posts_per_page,  // Use dynamic posts per page value
				'paged'          => $page,
				'meta_query'     => array(
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					)
				),
			);
 
			// Adjust sorting based on the 'sort_by' value
			switch ($sort_by) {
				case 'newest':
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
					break;

				case 'highest':
					// Adjust the "highest" sort based on the selected rating filter
					if (!empty($rating_filter_word)) {
						// Sort by the highest rating within the selected filter group
						$args['meta_query'][0]['value'] = $rating_filter_word; // Limit to the selected rating
					} else {
						// Default behavior if no filter is set
						$args['meta_query'][0]['value'] = 'FIVE';
					}
					break;

				case 'lowest':
					$args['meta_query'][0]['value'] = 'ONE'; 
					break;

				default:
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
			$query = new WP_Query($args);

			if ($query->have_posts()) {
				$output = '';

				// Fetch selected elements from post meta
				$selected_elements = get_post_meta($post_id, 'selected_elements', true);
				$selected_elements = maybe_unserialize($selected_elements);

				$output .= '<div id="div-container">';
					// Loop through the posts and append the HTML content
					while ($query->have_posts()) {
						$query->the_post();

						// Retrieve meta values
						$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
						$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
						$zwsgr_review_content   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
						$published_date  = get_the_date('F j, Y');
						$post_date = get_the_date('U');
						$days_ago = floor((time() - $post_date) / (60 * 60 * 24));
						$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
						$char_limit = (int) $char_limit ;
						$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));

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


						$output .= '<div class="zwsgr-review-post-item">';

							// Profile image
							if (!in_array('review-photo', $selected_elements)) {
								$output .= '<div class="zwsgr-profile">';
								$output .= '<img src="' . esc_url($plugin_dir_path . 'assets/images/testi-pic.png') . '" alt="Reviewer Image">';
								$output .= '</div>';
							}

							// Reviewer info container
							$output .= '<div class="zwsgr-review-info">';
								if (!in_array('review-title', $selected_elements)) {
									$output .= '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
								}
								if (!in_array('review-days-ago', $selected_elements)) {
									$output .= '<p class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '"><strong>Published:</strong> ' . esc_html($formatted_date) . ' (' . esc_html($days_ago) . ' days ago)</p>';
								}
							$output .= '</div>'; // End of reviewer info

							// Google icon
							if (!in_array('review-photo', $selected_elements)) {
								$output .= '<div class="zwsgr-google-icon">';
								$output .= '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">';
								$output .= '</div>';
							}

							// Rating
							if (!in_array('review-rating', $selected_elements)) {
								$output .= '<div class="zwsgr-rating">' . esc_html($zwsgr_review_star_rating) . ' stars</div>';
							}

							// Review content
							if (!in_array('review-content', $selected_elements)) {
								$output .= '<p class="zwsgr-content"><strong>Content:</strong>' . esc_html($trimmed_content);
								if ($is_trimmed) {
									$output .= '<a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">'.esc_html($this->translate_read_more($language)).'</a>';
								}
								$output .= '</p>';
							}

						// End of review post item container
						$output .= '</div>';
					}
				$output .= '</div>';

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

