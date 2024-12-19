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

		private $zwsgr_dashboard;
		
		function __construct() 
		{
			add_action('wp_enqueue_scripts', array($this, 'ZWSGR_lib_public_enqueue'));  

			add_shortcode( 'zwsgr_widget', array($this,'shortcode_load_more'));
			add_action('wp_ajax_load_more_meta_data', array($this,'load_more_meta_data'));
			add_action('wp_ajax_nopriv_load_more_meta_data', array($this,'load_more_meta_data'));

			// Initialize dashboard class
			$this->zwsgr_dashboard = ZWSGR_Dashboard::get_instance();

		}
		function ZWSGR_lib_public_enqueue() 
		{
			wp_enqueue_script( ZWSGR_PREFIX . '_script_js', ZWSGR_URL . 'assets/js/script.js', array( 'jquery-core' ), ZWSGR_VERSION, true );

			// style css
			wp_enqueue_style( ZWSGR_PREFIX . '-style-css', ZWSGR_URL . 'assets/css/style.css', array(), ZWSGR_VERSION );

			// Slick js
			wp_enqueue_script( ZWSGR_PREFIX . '-slick-min-js', ZWSGR_URL . 'assets/js/slick.min.js', array( 'jquery-core' ), ZWSGR_VERSION );
			
			// Slick css
			wp_enqueue_style( ZWSGR_PREFIX . '-slick-css', ZWSGR_URL . 'assets/css/slick.css', array(), ZWSGR_VERSION );
			
			wp_localize_script(ZWSGR_PREFIX . '_script_js', 'load_more', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('zwsgr_load_more_nonce')
			));
		}

		function enqueue_custom_plugin_styles($post_id) {
			$custom_css = get_post_meta($post_id, '_zwsgr_custom_css', true);
			// Check if there's any custom CSS to render
			if (!empty($custom_css)) {
				echo '<style type="text/css">' . esc_html($custom_css) . '</style>';
			}
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

		function frontend_sortby($post_id){

			$sort_by = get_post_meta($post_id, 'sort_by', true);
			?>
			<div class="zwsgr-widget-setting-font">
				<h3 class="zwsgr-label-font">Sort By</h3>
				<select id="front-sort-by-select" name="front_sort_by" class="zwsgr-input-text">
					<option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest</option>
					<option value="highest" <?php echo ($sort_by === 'highest') ? 'selected' : ''; ?>>Highest Rating</option>
					<option value="lowest" <?php echo ($sort_by === 'lowest') ? 'selected' : ''; ?>>Lowest Rating</option>
				</select>
			</div>
			<?php
		}

		function keyword_search($post_id) {
			// Get the keywords meta data for the given post ID
			$keywords = get_post_meta($post_id, 'keywords', true);
		
			// Check if keywords are available and is an array
			if (is_array($keywords) && !empty($keywords)) {
				echo '<div id="zwsgr-front-keywords-list" class="zwsgr-front-keywords-list zwsgr-front-keyword-list">';
				echo '<h3 class="zwsgr-label-font">Keywords</h3>';
				echo '<ul>';  // Start an unordered list
		
				// Add "All" as the first keyword
				echo '<li class="zwsgr-keyword-item zwsgr-all-keyword" data-zwsgr-keyword="all">All</li>';
		
				foreach ($keywords as $keyword) {
					// Display each keyword as a list item
					echo '<li class="zwsgr-keyword-item" data-zwsgr-keyword="' . esc_attr($keyword) . '">' . esc_html($keyword) . '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}
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
				return esc_html__('Invalid post ID.', 'smart-google-reviews');
			}

			// Retrieve the 'enable_load_more' setting from post meta
			$enable_load_more = get_post_meta($post_id, 'enable_load_more', true);
			$google_review_toggle = get_post_meta($post_id, 'google_review_toggle', true);
			$bg_color = get_post_meta($post_id, 'bg_color', true);
			$text_color = get_post_meta($post_id, 'text_color', true);
			$bg_color_load = get_post_meta($post_id, 'bg_color_load', true);
			$text_color_load = get_post_meta($post_id, 'text_color_load', true);

			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

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
			$display_option = get_post_meta($post_id, 'display_option', true);
			$layout_option = get_post_meta($post_id, 'layout_option', true);
			$enable_sort_by = get_post_meta($post_id, 'enable_sort_by', true);
			$zwsgr_location_new_review_uri = get_post_meta($post_id, 'zwsgr_location_new_review_uri', true);

			$badge_layout_option = preg_match('/^badge-\d+$/', $layout_option) ? substr($layout_option, 0, -2) : $layout_option;
			
			$zwsgr_gmb_email = get_option('zwsgr_gmb_email');
			$zwsgr_account_number = get_post_meta($post_id, 'zwsgr_account_number', true);
			$zwsgr_location_number =get_post_meta($post_id, 'zwsgr_location_number', true);

			// Query for posts
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Custom post type slug
				'posts_per_page' => $stored_posts_per_page,  // Dynamic posts per page value
				'paged'          => 1,                      // Initial page number
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are matched
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					),
					array(
						'key'     => '_is_hidden',
						'compare' => 'NOT EXISTS',  // Ensure only visible posts
					),
					array(
						'key'     => 'zwsgr_gmb_email',
						'value'   => $zwsgr_gmb_email,
						'compare' => '='
					)
				),
				'orderby' => 'date',
				'order'   => 'DESC'
			);
			
			// Add the account filter only if it exists
			if (!empty($zwsgr_account_number)) {
				$args['meta_query'][] = array(
					'key'     => 'zwsgr_account_number',
					'value'   => (string) $zwsgr_account_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			if (!empty($zwsgr_location_number)) {
				$args['meta_query'][] = array(
					'key'     => 'zwsgr_location_number',
					'value'   => (string) $zwsgr_location_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}		
			
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
						$args['orderby'] = 'meta_value';
						$args['order'] = 'ASC';
						$args['meta_key'] = 'zwsgr_review_star_rating';
					}
					break;

				case 'lowest':
					$args['orderby'] = 'meta_value';
					$args['order'] = 'DESC';
					$args['meta_key'] = 'zwsgr_review_star_rating';
					break;

				default:
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
			
			$query = new WP_Query($args);

			ob_start();  // Start output buffering
			$this->enqueue_custom_plugin_styles($post_id);
			echo '<div class="zwsgr-front-review-filter-wrap">';
				if ($badge_layout_option === 'badge') {
				}else{
					if ($enable_sort_by) { // Check if "Sort By" is not enabled
						$this->frontend_sortby($post_id);
					}
					$this->keyword_search($post_id);
				}
			echo '</div>';

			echo '<div class="main-div-wrapper" style="max-width: 100%;" data-widget-id="' . esc_attr( $post_id ) . '" data-rating-filter="' . esc_attr( $rating_filter ) . '" data-layout-type="' . esc_attr( $layout_option ) . '" data-bg-color="' . esc_attr( $bg_color_load ) . '" data-text-color="' . esc_attr( $text_color_load ) . '">';
			if ($query->have_posts()) {

				// Fetch selected elements from post meta
				$reviews_html = '';
				$selected_elements = get_post_meta($post_id, 'selected_elements', true);
				$selected_elements = maybe_unserialize($selected_elements);

				$reviews_html .= '<div id="div-container">';
					// Loop through the posts and display them
					while ($query->have_posts()) {
						$query->the_post();
						
						// Retrieve the meta values
						$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
						$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
						$zwsgr_review_content   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
						$zwsgr_review_id= get_post_meta(get_the_ID(), 'zwsgr_review_id', true);
						$zwsgr_gmb_reviewer_image_path=wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
						$zwsgr_gmb_reviewer_image_uri =wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
						$zwsgr_location_name = get_post_meta($post_id, 'zwsgr_location_name', true);
						$published_date  = get_the_date('F j, Y');
						$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
						$zwsgr_location_all_review_uri =  get_post_meta($post_id, 'zwsgr_location_all_review_uri', true);
						$char_limit = (int) $char_limit ; 
						$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
						$zwsgr_location_thumbnail_url = get_post_meta($post_id, 'zwsgr_location_thumbnail_url', true);
						$image_url = $zwsgr_location_thumbnail_url ? $zwsgr_location_thumbnail_url : $plugin_dir_path . 'assets/images/Google_G_Logo.png';

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

						// Slider 
						$zwsgr_slider_item1 = '
							<div class="zwsgr-slide-item">' .
								'<div class="zwsgr-list-inner">' .
									'<div class="zwsgr-slide-wrap">' .
										(!in_array('review-photo', $selected_elements) 
											? '<div class="zwsgr-profile">' .
													''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'' .
											'</div>' 
											: '') .
										(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)?
										'<div class="zwsgr-review-info">' .
											(!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) 
												? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' 
												: '') .
											(!in_array('review-days-ago', $selected_elements) && !empty($published_date) 
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . '</h5>' 
												: '') .
										'</div>':'') .
										(!in_array('review-g-icon', $selected_elements)?
										'<div class="zwsgr-google-icon">' .
											''.
										( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'' .
										'</div>':'') .
									'</div>' .

									(!in_array('review-rating', $selected_elements) && !empty($stars_html) 
										? '<div class="zwsgr-rating">' . $stars_html . '</div>' 
										: '') .

									(!in_array('review-content', $selected_elements) 
										? (!empty($trimmed_content) 
											? '<p class="zwsgr-content">' . esc_html($trimmed_content) .
												($is_trimmed 
													? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
													: '') .
											'</p>' 
											: '') 
										: '') .
								'</div>' .
							'</div>';

						$zwsgr_slider_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">'.
									(!in_array('review-rating', $selected_elements) || !in_array('review-days-ago', $selected_elements)?'<div class="zwsgr-rating-wrap">
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . 
									
									( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) .
									'</div>':'').'
									
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . 
									
									'<div class="zwsgr-slide-wrap">'.	
										( !in_array('review-photo', $selected_elements)?
										'<div class="zwsgr-profile">
											' . 
											( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) .
										'</div>':'').'
									
										'. (!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) .
										'</div>' :'').'
										
										'.( !in_array('review-g-icon', $selected_elements)?
										'<div class="zwsgr-google-icon">
											'.
										( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
										</div>':'' ).'
									</div>
								</div>
							</div>';


							$zwsgr_slider_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwsgr-slide-wrap4">
										'.( (!in_array('review-photo', $selected_elements) ||  !in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)) ?'
											<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
									
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';

							$zwsgr_slider_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwsgr-slide-wrap4">
										'.( (!in_array('review-photo', $selected_elements) ||  !in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)) ?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';
							

							$zwsgr_slider_item5 = '
								<div class="zwsgr-slide-item">' .
									'<div>' .
										(!in_array('review-photo', $selected_elements) 
											? '<div class="zwsgr-profile">' .
													''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
												'.( !in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">'.
													( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'' .
												'</div>':'').'	
											</div>' 
											: '') .
										(!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) 
											? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' 
											: '') .
										(!in_array('review-rating', $selected_elements) && !empty($stars_html) 
											? '<div class="zwsgr-rating">' . $stars_html . '</div>' 
											: '') .
										((!in_array('review-content', $selected_elements)&& $zwsgr_review_content) || !in_array('review-days-ago', $selected_elements)
											? '<div class="zwsgr-contnt-wrap">' .
												(!in_array('review-content', $selected_elements) && !empty($trimmed_content) 
													? '<p class="zwsgr-content">' . esc_html($trimmed_content) .
														($is_trimmed 
															? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
															: '') .
													'</p>' 
													: '') .
												(!in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . '</h5>' 
													: '') .
											'</div>' 
											: '') .
									'</div>' .
								'</div>';

							$zwsgr_slider_item6 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
										</div>
										
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
										
										' . 
											( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';
							
							// List
							$zwsgr_list_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">';
									if (!in_array('review-photo', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-profile">
											'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
										</div>';
									}
									if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
										$zwsgr_list_item1 .= '<div class="zwsgr-review-info">';
										if (!in_array('review-title', $selected_elements)) {
											$zwsgr_list_item1 .= '
												<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
										}
										if (!in_array('review-days-ago', $selected_elements)) {
											$zwsgr_list_item1 .= '
												<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
													' . esc_html($formatted_date) . ' 
												</h5>';
										}
										$zwsgr_list_item1 .= '</div>';
									}
									
									if (!in_array('review-photo', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-google-icon">
											'.( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ).'
										</div>';
									}

									$zwsgr_list_item1 .= '</div>';
									if (!in_array('review-rating', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-rating">' . $stars_html . '</div>';
									}
									if (!in_array('review-content', $selected_elements)) {
										$zwsgr_list_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);

										if ($is_trimmed) {
											$zwsgr_list_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
										}

										$zwsgr_list_item1 .= '</p>';
									}

							$zwsgr_list_item1 .= '
								</div>
							</div>';
							
							$zwsgr_list_item2 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
													( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.( (!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
										</div>
										'.((!in_array('review-rating', $selected_elements) ||!in_array('review-content', $selected_elements))?'<div class="zwsgr-list-content-wrap">
											' . 
												( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											
											' . 
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										</div>':'').'
									</div>
								</div>';

							$zwsgr_list_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap3">
										'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
									</div>
								</div>
							</div>';
							
							$zwsgr_list_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap4">
										'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
										'.( (!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
										</div>':'').'
										
										' . 
											( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
									</div>
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
							
							$zwsgr_list_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-list-wrap5">
										<div class="zwsgr-prifile-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
													( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-data">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'') .'
										</div>

										<div class="zwsgr-content-wrap">
											'.((!in_array('review-rating', $selected_elements) ||!in_array('review-g-icon', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
													'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
												</div>':'').'
											</div>':'').'
											' . 
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										</div>
									</div>
								</div>
							</div>';

							// Gird
							$zwsgr_grid_item1 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-grid-inner">
										<div class="zwsgr-slide-wrap">';

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-profile">
											'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>';
										}

										if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
											$zwsgr_grid_item1 .= '<div class="zwsgr-review-info">';
											if (!in_array('review-title', $selected_elements)) {
												$zwsgr_grid_item1 .= '
													<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
											}
											if (!in_array('review-days-ago', $selected_elements)) {
												$zwsgr_grid_item1 .= '
													<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
														' . esc_html($formatted_date) . ' 
													</h5>';
											}
											$zwsgr_grid_item1 .= '</div>'; 
										}
										

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-google-icon">
												'.( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ).'
											</div>';
										}

										$zwsgr_grid_item1 .= '</div>'; 

										if (!in_array('review-rating', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-rating">' . $stars_html . '</div>';
										}

										if (!in_array('review-content', $selected_elements)) {
											$zwsgr_grid_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);
	
											if ($is_trimmed) {
												$zwsgr_grid_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
											}
	
											$zwsgr_grid_item1 .= '</p>';
										}

								$zwsgr_grid_item1 .= '
										</div>
									</div>';
							
							$zwsgr_grid_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
									'.( !in_array('review-photo', $selected_elements)?'
									<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
										</div>':'').'
										'.((!in_array('review-title', $selected_elements))||( !in_array('review-rating', $selected_elements))||(!in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											'.( (!in_array('review-rating', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-date-wrap">
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											
										</div>':'').'
										'.( !in_array('review-g-icon', $selected_elements)?'
										<div class="zwsgr-google-icon">
											'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
										</div>':'').'	
									</div>
									' . 
										
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
									? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';

							$zwsgr_grid_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-review-detail">
											' . 
												( !in_array('review-photo', $selected_elements) ? '<div class="zwsgr-profile">
												'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>' : '' ) . '
											
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											'.((!in_array('review-g-icon', $selected_elements) ||!in_array('review-rating', $selected_elements))?'
											<div class="zwsgr-rating-wrap">
												'.( !in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
													'.
													( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
												</div>':'').'
												
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											</div>':'').'	
										</div>
										' . 
											( !in_array('review-content', $selected_elements) && !empty($trimmed_content) ? '<div class="zwsgr-content-wrap"><p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
										
									</div>
								</div>
							</div>';
						
						$zwsgr_grid_item4 = '
						<div class="zwsgr-slide-item">
							<div class="zwsgr-grid-inner">
								'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
								' . 
									( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
								' . 
									( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
								' . 
									( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
								' . 
									( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '  
							</div>
						</div>';

						$zwsgr_grid_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										'.( !in_array('review-photo', $selected_elements)?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
										</div>':'').'
										'.(( !in_array('review-title', $selected_elements)||!in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
										
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

							// Popup
							$zwsgr_popup_item1 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">';

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_popup_item1 .= '
											<div class="zwsgr-profile">
												'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>';
										}
										if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
											$zwsgr_popup_item1 .= '<div class="zwsgr-review-info">';
											if (!in_array('review-title', $selected_elements)) {
												$zwsgr_popup_item1 .= '
													<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
											}
											if (!in_array('review-days-ago', $selected_elements)) {
												$zwsgr_popup_item1 .= '
													<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
														' . esc_html($formatted_date) . ' 
													</h5>';
											}
											$zwsgr_popup_item1 .= '</div>'; 
										}
									

										if (!in_array('review-rating', $selected_elements)) {
											$zwsgr_popup_item1 .= '
											<div class="zwsgr-rating">' . $stars_html . '</div>';
										}
										$zwsgr_popup_item1 .= '</div>'; 

										if (!in_array('review-content', $selected_elements)) {
											$zwsgr_popup_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);
	
											if ($is_trimmed) {
												$zwsgr_popup_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
											}
											$zwsgr_popup_item1 .= '</p>';
										}
								$zwsgr_popup_item1 .= '
									</div>
								</div>';
						
								$zwsgr_popup_item2 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
												<div class="zwsgr-profile">
													' . 
														( !in_array('review-photo', $selected_elements) ? ''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'': '' ) . '
												</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements) ?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>
										'.((!in_array('review-rating', $selected_elements) || !in_array('review-content', $selected_elements))?'
										<div class="zwsgr-list-content-wrap">
											' . 
												( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											' .
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
												? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
												: '') . '</p>' : '' ). '
										</div>':'') .'
									</div>
								</div>';
							
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
								<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link">
									<div class="zwsgr-badge-item" id="zwsgr-badge1">
										<h3 class="zwsgr-average">Good</h3>
										' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
										<img src="' . $plugin_dir_path . 'assets/images/google.png">
									</div>
								</a>
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
					'badge' => [
						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge1">
							<h3 class="zwsgr-average">Good</h3>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
							<img src="' . $plugin_dir_path . 'assets/images/google.png">
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge2">
							<div class="zwsgr-badge-image">
								<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
							</div>
							<div class="zwsgr-badge-info">
								<h3 class="zwsgr-average">Good</h3>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
								<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
							</div>
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge3">
							<div class="zwsgr-rating-wrap">
								<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							</div>
							<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge4">
							<div class="zwsgr-badge4-rating">
								<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							</div>
							<div class="zwsgr-badge4-info">
								<h3 class="zwsgr-google">Google</h3>
								<p class="zwsgr-avg-note">Average Rating</p>
								<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
							</div>
						</div></a>',
						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge5">
							<div class="zwsgr-badge5-rating">
								<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							</div>
							<div class="zwsgr-badge5-info">
								<h3 class="zwsgr-google">Google</h3>
								<p class="zwsgr-avg-note">Average Rating</p>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							</div>
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge6">
							<div class="zwsgr-badge6-rating">
								<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							</div>
							<div class="zwsgr-badge6-info">
								<h3 class="zwsgr-google">Google</h3>
								<p class="zwsgr-avg-note">Average Rating</p>
							</div>
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge7">
							<img src="' . $plugin_dir_path . 'assets/images/review-us.png">
							<div class="zwsgr-badge7-rating">
								<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							</div>
						</div></a>',

						'<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link"><div class="zwsgr-badge-item" id="zwsgr-badge8">
							<div class="zwsgr-logo-wrap">
								<img src="' . $plugin_dir_path . 'assets/images/Google_G_Logo.png">
								<p class="zwsgr-avg-note">Google Reviews</p>
							</div>
							<span class="final-rating">'.$zwsgr_reviews_ratings['ratings'].'</span>
							' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
							<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' reviews</b></p>
						</div></a>'
					],
					'popup' => [
						'<div class="zwsgr-popup-item" id="zwsgr-popup1" data-popup="zwsgrpopup1">
							<div class="zwsgr-profile-logo">
								<img src="' . $plugin_dir_path . 'assets/images/profile-logo.png">
							</div>
							<div class="zwsgr-profile-info">
								<h3>'.$zwsgr_location_name.'</h3>
								' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
								<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-total-review"> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</a>
							</div>
						</div>
						<div id="zwsgrpopup1" class="zwsgr-popup-overlay">
							<div class="zwsgr-popup-content">
								<div class="scrollable-content">
									<span class="zwsgr-close-popup">&times;</span>
									<div class="zwsgr-popup-wrap">
										<div class="zwsgr-profile-logo">
											<img src="' . $image_url . '">
										</div>
										<div class="zwsgr-profile-info">
											<h3>' . $zwsgr_location_name . '</h3>
											' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
											<p class="zwsgr-based-on">Based on <b>' . $zwsgr_reviews_ratings['reviews'] . ' Google reviews</b></p>
										</div>
									</div>
									<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
										' . $zwsgr_popup_content1 . '
									</div>' .
									($enable_load_more && $query->max_num_pages >= 2 && in_array($layout_option, ['popup-1', 'popup-2']) ?
										'<button class="load-more-meta zwsgr-load-more-btn" style="background-color:' . esc_attr($bg_color_load) . '; color:' . esc_attr($text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($post_id) . '" data-rating-filter="' . esc_attr($rating_filter) . '">' . esc_html__('Load More', 'smart-google-reviews') . '</button>'
										: '') .
								'</div>
							</div>
						</div>',
						'<div class="zwsgr-popup-item" id="zwsgr-popup2"  data-popup="zwsgrpopup2">
						<div class="zwsgr-title-wrap">
							<img src="' . $image_url . '">
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
										<img src="' . $image_url . '">
									</div>
									<div class="zwsgr-profile-info">
										<h3>'.$zwsgr_location_name.'</h3>
										' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwsgr-based-on">Based on <b> '.$zwsgr_reviews_ratings['reviews'].' Google reviews</b></p>
									</div>
								</div>
								<div class="zwsgr-slider zwsgr-grid-item zwsgr-popup-list">
									' . $zwsgr_popup_content2 . '
								</div>' .
									($enable_load_more && $query->max_num_pages >= 2 && in_array($layout_option, ['popup-1', 'popup-2']) ?
										'<button class="load-more-meta zwsgr-load-more-btn" style="background-color:' . esc_attr($bg_color_load) . '; color:' . esc_attr($text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($post_id) . '" data-rating-filter="' . esc_attr($rating_filter) . '">' . esc_html__('Load More', 'smart-google-reviews') . '</button>'
										: '') .'
							</div>
						</div>
					</div>'
					]
				];
				$layout_option = get_post_meta($post_id, 'layout_option', true);
				$layout_option_divide = explode('-', $layout_option);
				
				$layout_option_key = $layout_option_divide[0]; 
				$layout_option_value = $layout_option_divide[1];
				$reviews_html .= $options[$layout_option_key][$layout_option_value-1];
				$reviews_html .= '</div>';
				
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

				echo wp_kses($reviews_html, $allowed_html);
				// echo wp_kses_post($reviews_html);
				
				
				// Add the Load More button only if 'enable_load_more' is true
				if ($enable_load_more && $query->max_num_pages >= 2 && in_array($layout_option, ['list-1','list-2','','list-3','list-4','list-5','grid-1','grid-2','grid-3','grid-4','grid-5'])) {
					echo '<button class="load-more-meta zwsgr-load-more-btn" style="background-color:' . esc_attr($bg_color_load) . '; color:' . esc_attr($text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($post_id) . '" data-rating-filter="' . esc_attr($rating_filter) . '">' . esc_html__('Load More', 'smart-google-reviews') . '</button>';
				}
				
			echo '</div>';
				if($google_review_toggle){
					echo '<div class="zwsgr-toogle-display zwsgr-toogle-display-front">';
						echo '<a href="'.esc_attr($zwsgr_location_new_review_uri).'" style="background-color:' . esc_attr($bg_color) . '; color:' . esc_attr($text_color) . ';" class="zwsgr-google-toggle">Review Us On G</a>';
					echo '</div>';
				}
			} else {
				echo '<p>' . esc_html__('No posts found.', 'smart-google-reviews') . '</p>';
			}

			return ob_get_clean();  // Return the buffered content
		}
		
		function load_more_meta_data() 
		{
			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'zwsgr_load_more_nonce')) {
				wp_send_json_error(esc_html__('Nonce verification failed.', 'smart-google-reviews'));
				return;
			}

			// Retrieve the page number and post_id
			$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			// Ensure the post_id exists
			if (empty($post_id) || !get_post($post_id)) {
				wp_send_json_error(esc_html__('Invalid post ID.', 'smart-google-reviews'));
				return;
			}

			// Retrieve dynamic 'posts_per_page' from post meta
			$stored_posts_per_page = get_post_meta($post_id, 'posts_per_page', true);

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

			// $sort_by = get_post_meta($post_id, 'sort_by', true)?: 'newest';

			if ($_POST['front_sort_by']) {
				$sort_by = $_POST['front_sort_by'];
			} else {
				$sort_by = get_post_meta($post_id, 'sort_by', true)?: 'newest';
  			}            
			
			$front_keyword =$_POST['front_keyword'];
			
			$language = get_post_meta($post_id, 'language', true) ?: 'en'; 
			$date_format = get_post_meta($post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$months = $this->translate_months($language);

			$zwsgr_gmb_email = get_option('zwsgr_gmb_email');
			$zwsgr_account_number = get_post_meta($post_id, 'zwsgr_account_number', true);
			$zwsgr_location_number =get_post_meta($post_id, 'zwsgr_location_number', true);
			$zwsgr_location_all_review_uri =  get_post_meta($post_id, 'zwsgr_location_all_review_uri', true);

			// Query for posts based on the current page
			$args = array(
				'post_type'      => ZWSGR_POST_REVIEW_TYPE,  // Replace with your custom post type slug
				'posts_per_page' => $stored_posts_per_page,  // Use dynamic posts per page value
				'paged'          => $page,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'zwsgr_review_star_rating',
						'value'   => $ratings_to_include,  // Apply the word-based rating filter
						'compare' => 'IN',
						'type'    => 'CHAR'
					),
					array(
						'key'     => 'zwsgr_gmb_email',
						'value'   => $zwsgr_gmb_email,
						'compare' => '='
					)
				),
			);

			// Add the account filter only if it exists
			if (!empty($zwsgr_account_number)) {
				$args['meta_query'][] = array(
					'key'     => 'zwsgr_account_number',
					'value'   => (string) $zwsgr_account_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			if (!empty($zwsgr_location_number)) {
				$args['meta_query'][] = array(
					'key'     => 'zwsgr_location_number',
					'value'   => (string) $zwsgr_location_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			// Add the LIKE condition if front_keyword is present
			if ($front_keyword && $front_keyword !== 'all') {
				$args['meta_query'][]= array(
					'key'     => 'zwsgr_review_comment', // Replace with the actual meta key for the keyword
					'value'   => $front_keyword,
					'compare' => 'LIKE',
				);
			}else{
				$args['meta_query'][]=array(
					'key'     => 'zwsgr_review_star_rating',
					'value'   => $ratings_to_include,  // Apply the word-based rating filter
					'compare' => 'IN',
					'type'    => 'CHAR'
				);
			}
 
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
						$args['orderby'] = 'meta_value';
						$args['order'] = 'ASC';
						$args['meta_key'] = 'zwsgr_review_star_rating';
					}
					break;

				case 'lowest':
					$args['orderby'] = 'meta_value';
					$args['order'] = 'DESC';
					$args['meta_key'] = 'zwsgr_review_star_rating';
					break;

				default:
					$args['orderby'] = 'date';
					$args['order'] = 'DESC';
			}
			$query = new WP_Query($args);

			if ($query->have_posts()) {
				$output = '';
				// `$output = "<pre>" . print_r( $args, true ) . "</pre>"; `

				// Fetch selected elements from post meta
				$selected_elements = get_post_meta($post_id, 'selected_elements', true);
				$selected_elements = maybe_unserialize($selected_elements);

				// $output .= '<div id="div-container" style="max-width: 100%;">';
					// Loop through the posts and append the HTML content
					while ($query->have_posts()) {
						$query->the_post();

						// Retrieve meta values
						$zwsgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwsgr_reviewer_name', true);
						$zwsgr_review_star_rating = get_post_meta(get_the_ID(), 'zwsgr_review_star_rating', true);
						$zwsgr_review_content   = get_post_meta(get_the_ID(), 'zwsgr_review_comment', true);
						$zwsgr_review_id= get_post_meta(get_the_ID(), 'zwsgr_review_id', true);
						$zwsgr_gmb_reviewer_image_path=wp_upload_dir()['basedir'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
						$zwsgr_gmb_reviewer_image_uri =wp_upload_dir()['baseurl'] . '/gmb-reviewers/gmb-reviewer-'.$zwsgr_review_id.'.png';
						$zwsgr_location_name = get_post_meta($post_id, 'zwsgr_location_name', true);
						$published_date  = get_the_date('F j, Y');
						$char_limit = get_post_meta($post_id, 'char_limit', true); // Retrieve character limit meta value
						$char_limit = (int) $char_limit ;
						$plugin_dir_path = plugin_dir_url(dirname(__FILE__, 2));
						$zwsgr_location_thumbnail_url = get_post_meta($post_id, 'zwsgr_location_thumbnail_url', true);
						$image_url = $zwsgr_location_thumbnail_url ? $zwsgr_location_thumbnail_url : $plugin_dir_path . 'assets/images/Google_G_Logo.png';

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

						// Slider 
						$zwsgr_slider_item1 = '
							<div class="zwsgr-slide-item">' .
								'<div class="zwsgr-list-inner">' .
									'<div class="zwsgr-slide-wrap">' .
										(!in_array('review-photo', $selected_elements) 
											? '<div class="zwsgr-profile">' .
													''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'' .
											'</div>' 
											: '') .
										(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)?
										'<div class="zwsgr-review-info">' .
											(!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) 
												? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' 
												: '') .
											(!in_array('review-days-ago', $selected_elements) && !empty($published_date) 
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . '</h5>' 
												: '') .
										'</div>':'') .
										(!in_array('review-g-icon', $selected_elements)?
										'<div class="zwsgr-google-icon">' .
											''.
										( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'' .
										'</div>':'') .
									'</div>' .

									(!in_array('review-rating', $selected_elements) && !empty($stars_html) 
										? '<div class="zwsgr-rating">' . $stars_html . '</div>' 
										: '') .

									(!in_array('review-content', $selected_elements) 
										? (!empty($trimmed_content) 
											? '<p class="zwsgr-content">' . esc_html($trimmed_content) .
												($is_trimmed 
													? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
													: '') .
											'</p>' 
											: '') 
										: '') .
								'</div>' .
							'</div>';

						$zwsgr_slider_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">'.
									(!in_array('review-rating', $selected_elements) || !in_array('review-days-ago', $selected_elements)?'<div class="zwsgr-rating-wrap">
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . 
									
									( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) .
									'</div>':'').'
									
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . 
									
									'<div class="zwsgr-slide-wrap">'.	
										( !in_array('review-photo', $selected_elements)?
										'<div class="zwsgr-profile">
											' . 
											( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) .
										'</div>':'').'
									
										'. (!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) .
										'</div>' :'').'
										
										'.( !in_array('review-g-icon', $selected_elements)?
										'<div class="zwsgr-google-icon">
											'.
										( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
										</div>':'' ).'
									</div>
								</div>
							</div>';


							$zwsgr_slider_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwsgr-slide-wrap4">
										'.( (!in_array('review-photo', $selected_elements) ||  !in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)) ?'
											<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
									
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';

							$zwsgr_slider_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwsgr-slide-wrap4">
										'.( (!in_array('review-photo', $selected_elements) ||  !in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)) ?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';
							

							$zwsgr_slider_item5 = '
								<div class="zwsgr-slide-item">' .
									'<div>' .
										(!in_array('review-photo', $selected_elements) 
											? '<div class="zwsgr-profile">' .
													''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
												'.( !in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">'.
													( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'' .
												'</div>':'').'	
											</div>' 
											: '') .
										(!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) 
											? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' 
											: '') .
										(!in_array('review-rating', $selected_elements) && !empty($stars_html) 
											? '<div class="zwsgr-rating">' . $stars_html . '</div>' 
											: '') .
										((!in_array('review-content', $selected_elements)&& $zwsgr_review_content) || !in_array('review-days-ago', $selected_elements)
											? '<div class="zwsgr-contnt-wrap">' .
												(!in_array('review-content', $selected_elements) && !empty($trimmed_content) 
													? '<p class="zwsgr-content">' . esc_html($trimmed_content) .
														($is_trimmed 
															? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
															: '') .
													'</p>' 
													: '') .
												(!in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . '</h5>' 
													: '') .
											'</div>' 
											: '') .
									'</div>' .
								'</div>';

							$zwsgr_slider_item6 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
										</div>
										
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
										
										' . 
											( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';
							
							// List
							$zwsgr_list_item1 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap">';
									if (!in_array('review-photo', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-profile">
											'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
										</div>';
									}
									if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
										$zwsgr_list_item1 .= '<div class="zwsgr-review-info">';
										if (!in_array('review-title', $selected_elements)) {
											$zwsgr_list_item1 .= '
												<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
										}
										if (!in_array('review-days-ago', $selected_elements)) {
											$zwsgr_list_item1 .= '
												<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
													' . esc_html($formatted_date) . ' 
												</h5>';
										}
										$zwsgr_list_item1 .= '</div>';
									}
									
									if (!in_array('review-photo', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-google-icon">
											'.( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ).'
										</div>';
									}

									$zwsgr_list_item1 .= '</div>';
									if (!in_array('review-rating', $selected_elements)) {
										$zwsgr_list_item1 .= '
										<div class="zwsgr-rating">' . $stars_html . '</div>';
									}
									if (!in_array('review-content', $selected_elements)) {
										$zwsgr_list_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);

										if ($is_trimmed) {
											$zwsgr_list_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
										}

										$zwsgr_list_item1 .= '</p>';
									}

							$zwsgr_list_item1 .= '
								</div>
							</div>';
							
							$zwsgr_list_item2 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
													( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.( (!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements)?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
										</div>
										'.((!in_array('review-rating', $selected_elements) ||!in_array('review-content', $selected_elements))?'<div class="zwsgr-list-content-wrap">
											' . 
												( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											
											' . 
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										</div>':'').'
									</div>
								</div>';

							$zwsgr_list_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap3">
										'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
										'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
									</div>
								</div>
							</div>';
							
							$zwsgr_list_item4 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-slide-wrap4 zwsgr-list-wrap4">
										'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
										'.( (!in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
										</div>':'').'
										
										' . 
											( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
												? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
									</div>
									' . 
										( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';
							
							$zwsgr_list_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-list-inner">
									<div class="zwsgr-list-wrap5">
										<div class="zwsgr-prifile-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
											<div class="zwsgr-profile">
												' . 
													( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-data">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'') .'
										</div>

										<div class="zwsgr-content-wrap">
											'.((!in_array('review-rating', $selected_elements) ||!in_array('review-g-icon', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
													'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
												</div>':'').'
											</div>':'').'
											' . 
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										</div>
									</div>
								</div>
							</div>';

							// Gird
							$zwsgr_grid_item1 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-grid-inner">
										<div class="zwsgr-slide-wrap">';

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-profile">
											'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>';
										}

										if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
											$zwsgr_grid_item1 .= '<div class="zwsgr-review-info">';
											if (!in_array('review-title', $selected_elements)) {
												$zwsgr_grid_item1 .= '
													<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
											}
											if (!in_array('review-days-ago', $selected_elements)) {
												$zwsgr_grid_item1 .= '
													<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
														' . esc_html($formatted_date) . ' 
													</h5>';
											}
											$zwsgr_grid_item1 .= '</div>'; 
										}
										

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-google-icon">
												'.( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ).'
											</div>';
										}

										$zwsgr_grid_item1 .= '</div>'; 

										if (!in_array('review-rating', $selected_elements)) {
											$zwsgr_grid_item1 .= '
											<div class="zwsgr-rating">' . $stars_html . '</div>';
										}

										if (!in_array('review-content', $selected_elements)) {
											$zwsgr_grid_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);
	
											if ($is_trimmed) {
												$zwsgr_grid_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
											}
	
											$zwsgr_grid_item1 .= '</p>';
										}

								$zwsgr_grid_item1 .= '
										</div>
									</div>';
							
							$zwsgr_grid_item2 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
									'.( !in_array('review-photo', $selected_elements)?'
									<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
										</div>':'').'
										'.((!in_array('review-title', $selected_elements))||( !in_array('review-rating', $selected_elements))||(!in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											'.( (!in_array('review-rating', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-date-wrap">
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											
										</div>':'').'
										'.( !in_array('review-g-icon', $selected_elements)?'
										<div class="zwsgr-google-icon">
											'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
										</div>':'').'	
									</div>
									' . 
										
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
									? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';

							$zwsgr_grid_item3 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										<div class="zwsgr-review-detail">
											' . 
												( !in_array('review-photo', $selected_elements) ? '<div class="zwsgr-profile">
												'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>' : '' ) . '
											
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											'.((!in_array('review-g-icon', $selected_elements) ||!in_array('review-rating', $selected_elements))?'
											<div class="zwsgr-rating-wrap">
												'.( !in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
													'.
													( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
												</div>':'').'
												
												' . 
													( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											</div>':'').'	
										</div>
										' . 
											( !in_array('review-content', $selected_elements) && !empty($trimmed_content) ? '<div class="zwsgr-content-wrap"><p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
										
									</div>
								</div>
							</div>';
						
						$zwsgr_grid_item4 = '
						<div class="zwsgr-slide-item">
							<div class="zwsgr-grid-inner">
								'.((!in_array('review-photo', $selected_elements)||!in_array('review-g-icon', $selected_elements))?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $selected_elements)?'
												<div class="zwsgr-google-icon">
												'.
											( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
								' . 
									( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
								' . 
									( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
										? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
								' . 
									( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
								' . 
									( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '  
							</div>
						</div>';

						$zwsgr_grid_item5 = '
							<div class="zwsgr-slide-item">
								<div class="zwsgr-grid-inner">
									<div class="zwsgr-slide-wrap">
										'.( !in_array('review-photo', $selected_elements)?'
										<div class="zwsgr-profile">
											' . 
												( !in_array('review-photo', $selected_elements) ? '	'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'' : '' ) . '
										</div>':'').'
										'.(( !in_array('review-title', $selected_elements)||!in_array('review-days-ago', $selected_elements))?'
										<div class="zwsgr-review-info">
											' . 
												( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
													? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
										</div>':'').'
										
										' . 
											( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
									</div>
									' . 
										( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
										? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
										: '') . '</p>' : '' ) . '
								</div>
							</div>';

							// Popup
							$zwsgr_popup_item1 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">';

										if (!in_array('review-photo', $selected_elements)) {
											$zwsgr_popup_item1 .= '
											<div class="zwsgr-profile">
												'.	''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').''.'
											</div>';
										}
										if(!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements)){
											$zwsgr_popup_item1 .= '<div class="zwsgr-review-info">';
											if (!in_array('review-title', $selected_elements)) {
												$zwsgr_popup_item1 .= '
													<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>';
											}
											if (!in_array('review-days-ago', $selected_elements)) {
												$zwsgr_popup_item1 .= '
													<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">
														' . esc_html($formatted_date) . ' 
													</h5>';
											}
											$zwsgr_popup_item1 .= '</div>'; 
										}
									

										if (!in_array('review-rating', $selected_elements)) {
											$zwsgr_popup_item1 .= '
											<div class="zwsgr-rating">' . $stars_html . '</div>';
										}
										$zwsgr_popup_item1 .= '</div>'; 

										if (!in_array('review-content', $selected_elements)) {
											$zwsgr_popup_item1 .= '<p class="zwsgr-content">' . esc_html($trimmed_content);
	
											if ($is_trimmed) {
												$zwsgr_popup_item1 .= ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>';
											}
											$zwsgr_popup_item1 .= '</p>';
										}
								$zwsgr_popup_item1 .= '
									</div>
								</div>';
						
								$zwsgr_popup_item2 = '
								<div class="zwsgr-slide-item">
									<div class="zwsgr-list-inner">
										<div class="zwsgr-slide-wrap">
											'.( !in_array('review-photo', $selected_elements)?'
												<div class="zwsgr-profile">
													' . 
														( !in_array('review-photo', $selected_elements) ? ''.(file_exists($zwsgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwsgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" style="max-width:50px; height:auto;">' : '<img src="' . $plugin_dir_path . 'assets/images/fallback-user-dp.png">').'': '' ) . '
												</div>':'').'
											'.((!in_array('review-title', $selected_elements) || !in_array('review-days-ago', $selected_elements))?'
											<div class="zwsgr-review-info">
												' . 
													( !in_array('review-title', $selected_elements) && !empty($zwsgr_reviewer_name) ? '<h2 class="zwsgr-title">' . esc_html($zwsgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
													( !in_array('review-days-ago', $selected_elements) && !empty($published_date) 
														? '<h5 class="zwsgr-days-ago zwsgr-date" data-original-date="' . esc_attr($published_date) . '">' . esc_html($formatted_date) . ' </h5>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $selected_elements) ?'
											<div class="zwsgr-google-icon">
												'.
												( !in_array('review-g-icon', $selected_elements) ? '<img src="' . esc_url($plugin_dir_path . 'assets/images/google-icon.png') . '">' : '' ) .'
											</div>':'').'
											
										</div>
										'.((!in_array('review-rating', $selected_elements) || !in_array('review-content', $selected_elements))?'
										<div class="zwsgr-list-content-wrap">
											' . 
												( !in_array('review-rating', $selected_elements) && !empty($stars_html) ? '<div class="zwsgr-rating">' . $stars_html . '</div>' : '' ) . '
											' .
												( !in_array('review-content', $selected_elements) ? '<p class="zwsgr-content">' . esc_html($trimmed_content) . ($is_trimmed 
												? ' <a href="javascript:void(0);" class="toggle-content" data-full-text="' . esc_attr($zwsgr_review_content) . '">' . esc_html($this->translate_read_more($language)) . '</a>' 
												: '') . '</p>' : '' ). '
										</div>':'') .'
									</div>
								</div>';

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
				// $output .= '</div>';
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
								<a href="'.$zwsgr_location_all_review_uri.'" target="_blank" class="zwsgr-badge-link">
									<div class="zwsgr-badge-item" id="zwsgr-badge1">
										<h3 class="zwsgr-average">Good</h3>
										' . (!empty($final_rating) ? '<div class="zwsgr-rating">' . $final_rating . '</div>' : '') . '
										<p class="zwsgr-based-on">Based on <b>  '.$zwsgr_reviews_ratings['reviews'].' reviews </b></p>
										<img src="' . $plugin_dir_path . 'assets/images/google.png">
									</div>
								</a>
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
					'list' => [
						$zwsgr_list_content1,
						$zwsgr_list_content2,
						$zwsgr_list_content3,
						$zwsgr_list_content4,
						$zwsgr_list_content5
					],
					'grid' => [
						$zwsgr_grid_content1,
						$zwsgr_grid_content2,
						$zwsgr_grid_content3,
						$zwsgr_grid_content4,
						$zwsgr_grid_content5
					],
					'popup' => [
						$zwsgr_popup_content1, 
						$zwsgr_popup_content2 
					]
				];

				$layout_option = get_post_meta($post_id, 'layout_option', true);
				$layout_option_divide = explode('-', $layout_option);
				
				$layout_option_key = $layout_option_divide[0]; 
				$layout_option_value = $layout_option_divide[1];
				$output .= $filter_layout[$layout_option_key][$layout_option_value-1];

				// Prepare the response with the loaded content and the new page number
				$response = array(
					'content'   => $output,  // Send HTML content in the response
					'new_page'  => $page + 1,
				);
				wp_reset_postdata();
			} else {
				// No more posts available
				$response['err_msg'] = esc_html__('No Review Founds.', 'smart-google-reviews');
			}
			$response['disable_button'] = ( $query->max_num_pages <= $page ) ? true : false;
			wp_send_json_success($response);

			wp_die();  // Properly terminate the AJAX request
		}
	}
}

