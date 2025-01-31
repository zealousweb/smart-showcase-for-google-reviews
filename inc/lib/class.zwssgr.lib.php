<?php
/**
 * ZWSSGR_Lib Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Smart Showcase for Google Reviews
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZWSSGR_Lib' ) ) {

	/**
	 * The main ZWSSGR class
	 */
	class ZWSSGR_Lib {

		private $zwssgr_dashboard;
		
		function __construct() 
		{
			add_action('wp_enqueue_scripts', array($this, 'ZWSSGR_lib_public_enqueue'));  
			add_shortcode( 'zwssgr_widget', array($this,'zwssgr_shortcode_load_more'));
			add_action('wp_ajax_zwssgr_load_more_meta_data', array($this,'zwssgr_load_more_meta_data'));
			add_action('wp_ajax_nopriv_zwssgr_load_more_meta_data', array($this,'zwssgr_load_more_meta_data'));
			
			// Initialize dashboard class
			$this->zwssgr_dashboard = ZWSSGR_Dashboard::get_instance();

		}
		function ZWSSGR_lib_public_enqueue() 
		{
			// script js
			wp_register_script( ZWSSGR_PREFIX . '_script_js', ZWSSGR_URL .'assets/js/script.js', array('jquery-core'), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '_script_js' );

			// style css
			wp_register_style( ZWSSGR_PREFIX . '-style-css', ZWSSGR_URL . 'assets/css/style.css', false, ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . '-style-css' );

			// Slick js
			wp_register_script( ZWSSGR_PREFIX . '-slick-min-js', ZWSSGR_URL .'assets/js/slick.min.js', array('jquery-core'), ZWSSGR_VERSION, true );
			wp_enqueue_script( ZWSSGR_PREFIX . '-slick-min-js' );
			
			// Slick css
			wp_register_style( ZWSSGR_PREFIX . '-slick-css', ZWSSGR_URL . 'assets/css/slick.css', false, ZWSSGR_VERSION );
			wp_enqueue_style( ZWSSGR_PREFIX . '-slick-css' );

			// Custom css
			$zwssgr_wd_posts_args = array(
				'post_type'			=> 'zwssgr_data_widget',
				'posts_per_page'	=> -1,
				'status'			=> 'publish',
				'fields'			=> 'ids',
				'meta_query' => array(
					array(
						'key'     => '_zwssgr_custom_css',
						'value'   => '', 
						'compare' => '!=',
					),
				),
			);
			$zwssgr_wd_posts = get_posts( $zwssgr_wd_posts_args );
			
			$zwssgr_wd_dy_style = '';
	
			if( ! empty( $zwssgr_wd_posts ) ){
				foreach( $zwssgr_wd_posts as $zwssgr_wd_posts_single_id ){
					$zwssgr_wd_dy_style .= get_post_meta($zwssgr_wd_posts_single_id, '_zwssgr_custom_css', true);
					wp_add_inline_style(ZWSSGR_PREFIX . '-style-css', $zwssgr_wd_dy_style);
				}
			}

			wp_localize_script(ZWSSGR_PREFIX . '_script_js', 'load_more', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('zwssgr_load_more_nonce')
			));
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

		function zwssgr_frontend_sortby($zwssgr_post_id){

			$zwssgr_sort_by = get_post_meta($zwssgr_post_id, 'sort_by', true);
			$zwssgr_rating_filter = intval(get_post_meta($zwssgr_post_id, 'rating_filter', true)) ?: 0;
			if ($zwssgr_rating_filter === 0) {
				?>
				<div class="zwssgr-widget-setting-font">
					<h3 class="zwssgr-label-font"><?php echo esc_html__('Sort By', 'smart-showcase-for-google-reviews'); ?></h3>
					<select id="front-sort-by-select-<?php echo esc_attr( $zwssgr_post_id ); ?>" name="front_sort_by" class="zwssgr-input-text front-sort-by-select">
						<option value="newest" <?php echo ($zwssgr_sort_by === 'newest') ? 'selected' : ''; ?>><?php echo esc_html__('Newest', 'smart-showcase-for-google-reviews'); ?></option>
						<option value="highest" <?php echo ($zwssgr_sort_by === 'highest') ? 'selected' : ''; ?>><?php echo esc_html__('Highest Rating', 'smart-showcase-for-google-reviews'); ?></option>
						<option value="lowest" <?php echo ($zwssgr_sort_by === 'lowest') ? 'selected' : ''; ?>><?php echo esc_html__('Lowest Rating', 'smart-showcase-for-google-reviews'); ?></option>
					</select>
				</div>
				<?php
			}
		}

		function zwssgr_keyword_search($zwssgr_post_id) {
			// Get the keywords meta data for the given post ID
			$zwssgr_keywords = get_post_meta($zwssgr_post_id, 'keywords', true);
		
			// Check if keywords are available and is an array
			if (is_array($zwssgr_keywords) && !empty($zwssgr_keywords)) {
				echo "<div id='zwssgr-front-keywords-list-" . esc_attr( $zwssgr_post_id ) . "' class='zwssgr-front-keywords-list zwssgr-front-keyword-list'>";
					echo '<h3 class="zwssgr-label-font">'.esc_html__('Keywords', 'smart-showcase-for-google-reviews').'</h3>';
					echo '<ul>';  // Start an unordered list
			
						// Add "All" as the first keyword
						echo '<li class="zwssgr-keyword-item zwssgr-all-keyword selected" data-zwssgr-keyword="all">'.esc_html__('All', 'smart-showcase-for-google-reviews').'</li>';
				
						foreach ($zwssgr_keywords as $zwssgr_keyword) {
							// Display each keyword as a list item
							echo '<li class="zwssgr-keyword-item" data-zwssgr-keyword="' . esc_attr($zwssgr_keyword) . '">' . esc_html($zwssgr_keyword) . '</li>';
						}
					echo '</ul>';
				echo '</div>';
			}
		}


		// Shortcode to render initial posts and Load More button
		function zwssgr_shortcode_load_more($zwssgr_atts) 
		{
			// Extract the attributes passed to the shortcode
			$zwssgr_atts = shortcode_atts(
				array(
					'post-id'         => '',  // Default value for post-id
					'display-option'  => '',  // Default value for display-option
					'layout-option'   => '',  // Default value for layout-option
				),
				$zwssgr_atts,
				'zwssgr_widget'
			);
		
			// Retrieve attributes
			$zwssgr_post_id       = $zwssgr_atts['post-id'];
			$zwssgr_display_option = $zwssgr_atts['display-option'];
			$zwssgr_layout_option  = $zwssgr_atts['layout-option'];
		
			// Validate the post ID
			if (empty($zwssgr_post_id) || !is_numeric($zwssgr_post_id) || !get_post($zwssgr_post_id)) {
				return esc_html__('Invalid post ID.', 'smart-showcase-for-google-reviews');
			}
		
			// Get the post object
			$zwssgr_post = get_post($zwssgr_post_id);
		
			// Ensure the post exists, is of the correct post type, and is published
			if (!$zwssgr_post || $zwssgr_post->post_type !== 'zwssgr_data_widget' || $zwssgr_post->post_status !== 'publish') {
				return ''; // Return nothing if conditions are not met
			}
		
			// Retrieve expected values from post meta
			$zwssgr_expected_display_option = get_post_meta($zwssgr_post_id, 'display_option', true);
			$zwssgr_expected_layout_option  = get_post_meta($zwssgr_post_id, 'layout_option', true);
		
			// Validate that the shortcode attributes match the stored post meta values
			if (
				empty($zwssgr_display_option) || empty($zwssgr_layout_option) || 
				$zwssgr_display_option !== $zwssgr_expected_display_option || 
				$zwssgr_layout_option !== $zwssgr_expected_layout_option
			) {
				return esc_html__('Invalid or missing shortcode parameters.', 'smart-showcase-for-google-reviews');
			}
		
			// If all checks pass, return the correct shortcode
			// Validate that the shortcode attributes match the stored post meta values
			if (
				empty($zwssgr_display_option) || empty($zwssgr_layout_option) || 
				$zwssgr_display_option !== $zwssgr_expected_display_option || 
				$zwssgr_layout_option !== $zwssgr_expected_layout_option
			) {
				return ''; // Stop shortcode from functioning
			}

			// Retrieve the 'enable_load_more' setting from post meta
			$zwssgr_enable_load_more = get_post_meta($zwssgr_post_id, 'enable_load_more', true);
			$zwssgr_google_review_toggle = get_post_meta($zwssgr_post_id, 'google_review_toggle', true);
			$zwssgr_bg_color = get_post_meta($zwssgr_post_id, 'bg_color', true);
			$zwssgr_text_color = get_post_meta($zwssgr_post_id, 'text_color', true);
			$zwssgr_bg_color_load = get_post_meta($zwssgr_post_id, 'bg_color_load', true);
			$zwssgr_text_color_load = get_post_meta($zwssgr_post_id, 'text_color_load', true);

			// Retrieve dynamic 'posts_per_page' from post meta
			$zwssgr_stored_posts_per_page = get_post_meta($zwssgr_post_id, 'posts_per_page', true);

			// Retrieve the 'rating_filter' value from the post meta
			$zwssgr_rating_filter = intval(get_post_meta($zwssgr_post_id, 'rating_filter', true)) ?: 0;

			// Define the mapping from numeric values to words
			$zwssgr_rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word
			$zwssgr_rating_filter_word = isset($zwssgr_rating_mapping[$zwssgr_rating_filter]) ? $zwssgr_rating_mapping[$zwssgr_rating_filter] : '';

			// Define the ratings to include based on the rating filter
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

			$zwssgr_sort_by	 = get_post_meta($zwssgr_post_id, 'sort_by', true)?: 'newest';
			$zwssgr_language = get_post_meta($zwssgr_post_id, 'language', true) ?: 'en'; 
			$zwssgr_date_format = get_post_meta($zwssgr_post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$zwssgr_months = $this->zwssgr_translate_months($zwssgr_language);
			$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
			$zwssgr_enable_sort_by = get_post_meta($zwssgr_post_id, 'enable_sort_by', true);
			$zwssgr_location_new_review_uri = get_post_meta($zwssgr_post_id, 'zwssgr_location_new_review_uri', true);

			$zwssgr_badge_layout_option = preg_match('/^badge-\d+$/', $zwssgr_layout_option) ? substr($zwssgr_layout_option, 0, -2) : $zwssgr_layout_option;
			
			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_location_number =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);

			// Query for posts
			$zwssgr_args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE,  // Custom post type slug
				'posts_per_page' => $zwssgr_stored_posts_per_page,  // Dynamic posts per page value
				'paged'          => 1,                      // Initial page number
				'meta_query'     => array(
					'relation' => 'AND',  // Ensure all conditions are matched
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
				),
				'orderby' => 'date',
				'order'   => 'DESC'
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

			if (!empty($zwssgr_location_number)) {
				$zwssgr_args['meta_query'][] = array(
					'key'     => 'zwssgr_location_number',
					'value'   => (string) $zwssgr_location_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}		
			
			// Adjust sorting based on the 'sort_by' value
			switch ($zwssgr_sort_by) {
				case 'newest':
					$zwssgr_args['orderby'] = 'date';
					$zwssgr_args['order'] = 'DESC';
		
					// Check if we need to prioritize pinned posts (i.e., zwssgr_is_pinned === 1)
					$zwssgr_pinned_query = get_posts(array(
						'post_type' => ZWSSGR_POST_REVIEW_TYPE,
						'meta_key'  => 'zwssgr_is_pinned',
						'meta_value' => 1,  // Only get pinned posts
						'fields' => 'ids',  // Return only post IDs
					));

					if (!empty($zwssgr_pinned_query)) {
						// Add custom ordering if there are pinned posts
						$zwssgr_args['orderby'] = array(
							'meta_value_num' => 'DESC',  // First, show pinned posts (1) at the top
							'date' => 'DESC',  // Then, sort by date for both pinned and unpinned
						);
						$zwssgr_args['meta_key'] = 'zwssgr_is_pinned';  // Sort by the zwssgr_is_pinned meta key
					}
	
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
			
			$zwssgr_query = new WP_Query($zwssgr_args);
			ob_start();  // Start output buffering

			echo '<div class="zwssgr-main-wrapper" data-widget-id="' . esc_attr( $zwssgr_post_id ) . '" data-onload-first="true">';

				echo '<div class="zwssgr-front-review-filter-wrap" data-widget-id="' . esc_attr( $zwssgr_post_id ) . '">';
					if ($zwssgr_badge_layout_option === 'badge') {
					}else{
						if ($zwssgr_enable_sort_by) { // Check if "Sort By" is not enabled
							$this->zwssgr_frontend_sortby($zwssgr_post_id);
						}
						$this->zwssgr_keyword_search($zwssgr_post_id);
					}
				echo '</div>';

				echo '<div class="main-div-wrapper zwssgr-main-div-wrapper" data-widget-id="' . esc_attr( $zwssgr_post_id ) . '" data-rating-filter="' . esc_attr( $zwssgr_rating_filter ) . '" data-layout-type="' . esc_attr( $zwssgr_layout_option ) . '" data-bg-color="' . esc_attr( $zwssgr_bg_color_load ) . '" data-text-color="' . esc_attr( $zwssgr_text_color_load ) . '" data-enable-load-more="' . esc_attr( $zwssgr_enable_load_more ) . '">';
				if ($zwssgr_query->have_posts()) {

					// Fetch selected elements from post meta
					$zwssgr_reviews_html = '';
					$zwssgr_selected_elements = get_post_meta($zwssgr_post_id, 'selected_elements', true);
					$zwssgr_selected_elements = maybe_unserialize($zwssgr_selected_elements);

					$zwssgr_reviews_html .= '<div id="div-container" class="zwssgr-front-container" data-widget-id="' . esc_attr( $zwssgr_post_id ) . '">';
						// Loop through the posts and display them
						while ($zwssgr_query->have_posts()) {
							$zwssgr_query->the_post();
							
							// Retrieve the meta values
							$zwssgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
							$zwssgr_review_star_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
							$zwssgr_review_content   = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
							$zwssgr_review_id= get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
							$zwssgr_gmb_reviewer_image_path=ZWSSGR_UPLOAD_DIR . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
							$zwssgr_gmb_reviewer_image_uri =ZWSSGR_UPLOAD_URL . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
							$zwssgr_location_name = get_post_meta($zwssgr_post_id, 'zwssgr_location_name', true);
							$zwssgr_published_date  = get_the_date('F j, Y');
							$zwssgr_char_limit = get_post_meta($zwssgr_post_id, 'char_limit', true); // Retrieve character limit meta value
							$zwssgr_location_all_review_uri =  get_post_meta($zwssgr_post_id, 'zwssgr_location_all_review_uri', true);
							$zwssgr_char_limit = (int) $zwssgr_char_limit ; 
							$zwssgr_plugin_dir_path = ZWSSGR_URL;
							$zwssgr_location_thumbnail_url = get_post_meta($zwssgr_post_id, 'zwssgr_location_thumbnail_url', true);
							$zwssgr_image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png';

							// Determine if content is trimmed based on character limit
							$zwssgr_is_trimmed = $zwssgr_char_limit > 0 && mb_strlen($zwssgr_review_content) > $zwssgr_char_limit; // Check if the content length exceeds the character limit
							$zwssgr_trimmed_content = $zwssgr_is_trimmed ? mb_substr($zwssgr_review_content, 0, $zwssgr_char_limit) . '...' : $zwssgr_review_content; // Trim the content if necessary

							// Format the published date based on the selected format
							$zwssgr_formatted_date = '';
							if ($zwssgr_date_format === 'DD/MM/YYYY') {
								$zwssgr_formatted_date = gmdate('d/m/Y', strtotime($zwssgr_published_date));
							} elseif ($zwssgr_date_format === 'MM-DD-YYYY') {
								$zwssgr_formatted_date = gmdate('m-d-Y', strtotime($zwssgr_published_date));
							} elseif ($zwssgr_date_format === 'YYYY/MM/DD') {
								$zwssgr_formatted_date = gmdate('Y/m/d', strtotime($zwssgr_published_date));
							} elseif ($zwssgr_date_format === 'full') {
								$zwssgr_day = gmdate('j', strtotime($zwssgr_published_date));
								$zwssgr_month = $zwssgr_months[(int)gmdate('n', strtotime($zwssgr_published_date)) - 1];
								$zwssgr_year = gmdate('Y', strtotime($zwssgr_published_date));
								
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

							// Slider 
							$zwssgr_slider_item1 = '
								<div class="zwssgr-slide-item">' .
									'<div class="zwssgr-list-inner">' .
										'<div class="zwssgr-slide-wrap">' .
											(!in_array('review-photo', $zwssgr_selected_elements) ? '<div class="zwssgr-profile">' .
											''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').'' .
											'</div>' : '') .
											(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)?
											'<div class="zwssgr-review-info">' .
												(!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) 
													? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' 
													: '') .
												(!in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . '</h3>' : '') .
											'</div>':'') .
											(!in_array('review-g-icon', $zwssgr_selected_elements)?
											'<div class="zwssgr-google-icon">' .
												''.( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'' .
											'</div>':'') .
										'</div>' .

										(!in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) 
										? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '') .

										(!in_array('review-content', $zwssgr_selected_elements) ? (!empty($zwssgr_trimmed_content) 
										? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) .
										($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') .'</p>' : '') : '') .
									'</div>' .
								'</div>';

							$zwssgr_slider_item2 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">'.
										(!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)?'<div class="zwssgr-rating-wrap">
										' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . 
										
										( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) .
										'</div>':'').'
										
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</p>' : '' ) . 
										
										'<div class="zwssgr-slide-wrap">'.	
											( !in_array('review-photo', $zwssgr_selected_elements)?
											'<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) .
											'</div>':'').'
										
											'. (!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '
											<div class="zwssgr-review-info">
												' . 
													( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) .
											'</div>' :'').'
											
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?
											'<div class="zwssgr-google-icon">
												'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'' ).'
										</div>
									</div>
								</div>';


							$zwssgr_slider_item3 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										
										<div class="zwssgr-slide-wrap4">
											'.( (!in_array('review-photo', $zwssgr_selected_elements))?'
											<div class="zwssgr-profile">
												' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
												'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
													'.
													( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
												
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)) ?'
											<div class="zwssgr-review-info">
												' . 
													( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
										
											' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										</div>
									</div>
								</div>';

							$zwssgr_slider_item4 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</p>' : '' ) . '
										
										<div class="zwssgr-slide-wrap4">
											'.( (!in_array('review-photo', $zwssgr_selected_elements))?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
												'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
													'.
													( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
												
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)) ?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										</div>
									</div>
								</div>';
							

							$zwssgr_slider_item5 = '
								<div class="zwssgr-slide-item">' .
									'<div>' .
										(!in_array('review-photo', $zwssgr_selected_elements) ? '
										<div class="zwssgr-profile">' .
												''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'' .
											'</div>':'').'	
										</div>' : '') .
										(!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) 
											? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' 
											: '') .
										(!in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) 
											? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' 
											: '') .
										((!in_array('review-content', $zwssgr_selected_elements)&& $zwssgr_review_content) || !in_array('review-days-ago', $zwssgr_selected_elements) ? '
										<div class="zwssgr-contnt-wrap">' .
											(!in_array('review-content', $zwssgr_selected_elements) && !empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) .($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') .
											'</p>' : '') .
											(!in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . '</h3>' : '') .
										'</div>' : '') .
									'</div>' .
								'</div>';

							$zwssgr_slider_item6 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
										</div>
										
										' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';
								
							// List
							$zwssgr_list_item1 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap">';
											if (!in_array('review-photo', $zwssgr_selected_elements)) {
												$zwssgr_list_item1 .= '
												<div class="zwssgr-profile">
													'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
												</div>';
											}
											if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
												$zwssgr_list_item1 .= '<div class="zwssgr-review-info">';
												if (!in_array('review-title', $zwssgr_selected_elements)) {
													$zwssgr_list_item1 .= '
														<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
												}
												if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
													$zwssgr_list_item1 .= '
														<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
															' . esc_html($zwssgr_formatted_date) . ' 
														</h3>';
												}
												$zwssgr_list_item1 .= '</div>';
											}
											
											if (!in_array('review-photo', $zwssgr_selected_elements)) {
												$zwssgr_list_item1 .= '
												<div class="zwssgr-google-icon">
													'.( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ).'
												</div>';
											}

										$zwssgr_list_item1 .= '</div>';
										if (!in_array('review-rating', $zwssgr_selected_elements)) {
											$zwssgr_list_item1 .= '
											<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
										}
										if (!in_array('review-content', $zwssgr_selected_elements)) {
											$zwssgr_list_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);

											if ($zwssgr_is_trimmed) {
												$zwssgr_list_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
											}

											$zwssgr_list_item1 .= '</p>';
										}
								$zwssgr_list_item1 .= '
									</div>
								</div>';
							
							$zwssgr_list_item2 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											</div>':'').'
											'.( (!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
										</div>
										'.((!in_array('review-rating', $zwssgr_selected_elements) ||!in_array('review-content', $zwssgr_selected_elements))?'
										<div class="zwssgr-list-content-wrap">
											' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										
											' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</p>' : '' ) . '
										</div>':'').'
									</div>
								</div>';

							$zwssgr_list_item3 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
										<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
											'.((!in_array('review-photo', $zwssgr_selected_elements))?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
													'.
													( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
										</div>
									</div>
								</div>';
							
							$zwssgr_list_item4 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
											'.((!in_array('review-photo', $zwssgr_selected_elements))?'
											<div class="zwssgr-profile">
												' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
													'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
													<div class="zwssgr-google-icon">
													'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
													
											</div>':'').'
											'.( (!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											</div>':'').'
											
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>
										' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';
							
							$zwssgr_list_item5 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-list-wrap5">
											<div class="zwssgr-prifile-wrap">
												'.( !in_array('review-photo', $zwssgr_selected_elements)?'
												<div class="zwssgr-profile">
													' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
												</div>':'').'
												'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)) ?'
												<div class="zwssgr-data">
													' . 
													( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
													
													' . 
													( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
												</div>':'') .'
											</div>

											<div class="zwssgr-content-wrap">
												'.((!in_array('review-rating', $zwssgr_selected_elements) ||!in_array('review-g-icon', $zwssgr_selected_elements))?'
												<div class="zwssgr-review-info">
													' . 
														( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
													'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
													<div class="zwssgr-google-icon">
														'.
													( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
													</div>':'').'
												</div>':'').'
												' . 
												( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '
											</div>
										</div>
									</div>
								</div>';

							// Gird
							$zwssgr_grid_item1 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-grid-inner">
										<div class="zwssgr-slide-wrap">';

											if (!in_array('review-photo', $zwssgr_selected_elements)) {
												$zwssgr_grid_item1 .= '
												<div class="zwssgr-profile">
												'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
												</div>';
											}

											if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
												$zwssgr_grid_item1 .= '<div class="zwssgr-review-info">';
												if (!in_array('review-title', $zwssgr_selected_elements)) {
													$zwssgr_grid_item1 .= '
														<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
												}
												if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
													$zwssgr_grid_item1 .= '
														<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
															' . esc_html($zwssgr_formatted_date) . ' 
														</h3>';
												}
												$zwssgr_grid_item1 .= '</div>'; 
											}
											

											if (!in_array('review-photo', $zwssgr_selected_elements)) {
												$zwssgr_grid_item1 .= '
												<div class="zwssgr-google-icon">
													'.( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ).'
												</div>';
											}

										$zwssgr_grid_item1 .= '</div>'; 

										if (!in_array('review-rating', $zwssgr_selected_elements)) {
											$zwssgr_grid_item1 .= '
											<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
										}

										if (!in_array('review-content', $zwssgr_selected_elements)) {
											$zwssgr_grid_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);
	
											if ($zwssgr_is_trimmed) {
												$zwssgr_grid_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
											}
	
											$zwssgr_grid_item1 .= '</p>';
										}

								$zwssgr_grid_item1 .= '
										</div>
									</div>';
							
							$zwssgr_grid_item2 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-grid-inner">
										<div class="zwssgr-slide-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements))||( !in_array('review-rating', $zwssgr_selected_elements))||(!in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
													( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												'.( (!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
												<div class="zwssgr-date-wrap">
													' . 
													( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
													' . 
													( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
												</div>':'').'
												
											</div>':'').'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'	
										</div>
										' . 
											
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';

							$zwssgr_grid_item3 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-grid-inner">
										<div class="zwssgr-slide-wrap">
											<div class="zwssgr-review-detail">
												' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? '<div class="zwssgr-profile">
													'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
												</div>' : '' ) . '
												
												' . 
													( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
													( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
														? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
												'.((!in_array('review-g-icon', $zwssgr_selected_elements) ||!in_array('review-rating', $zwssgr_selected_elements))?'
												<div class="zwssgr-rating-wrap">
													'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
													<div class="zwssgr-google-icon">
														'.
														( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
													</div>':'').'
													
													' . 
													( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
												</div>':'').'	
											</div>
											' . 
												( !in_array('review-content', $zwssgr_selected_elements) && !empty($zwssgr_trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</div></p>' : '' ) . '
											
										</div>
									</div>
								</div>';
						
							$zwssgr_grid_item4 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-grid-inner">
										'.((!in_array('review-photo', $zwssgr_selected_elements))?'
											<div class="zwssgr-profile">
												' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
													'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
													<div class="zwssgr-google-icon">
													'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
													
											</div>':'').'
										' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
										' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
												? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
												? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
												: '') . '</p>' : '' ) . '  
									</div>
								</div>';

							$zwssgr_grid_item5 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-grid-inner">
										<div class="zwssgr-slide-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											</div>':'').'
											'.(( !in_array('review-title', $zwssgr_selected_elements)||!in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											
											' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										</div>
										' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>
								</div>';

							// Popup
							$zwssgr_popup_item1 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap">';

											if (!in_array('review-photo', $zwssgr_selected_elements)) {
												$zwssgr_popup_item1 .= '
												<div class="zwssgr-profile">
													'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
												</div>';
											}
											if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
												$zwssgr_popup_item1 .= '<div class="zwssgr-review-info">';
												if (!in_array('review-title', $zwssgr_selected_elements)) {
													$zwssgr_popup_item1 .= '
														<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
												}
												if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
													$zwssgr_popup_item1 .= '
														<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
															' . esc_html($zwssgr_formatted_date) . ' 
														</h3>';
												}
												$zwssgr_popup_item1 .= '</div>'; 
											}
										

											if (!in_array('review-rating', $zwssgr_selected_elements)) {
												$zwssgr_popup_item1 .= '
												<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
											}
										$zwssgr_popup_item1 .= '</div>'; 

										if (!in_array('review-content', $zwssgr_selected_elements)) {
											$zwssgr_popup_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);
	
											if ($zwssgr_is_trimmed) {
												$zwssgr_popup_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
											}
											$zwssgr_popup_item1 .= '</p>';
										}
								$zwssgr_popup_item1 .= '
									</div>
								</div>';
						
							$zwssgr_popup_item2 = '
								<div class="zwssgr-slide-item">
									<div class="zwssgr-list-inner">
										<div class="zwssgr-slide-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
												<div class="zwssgr-profile">
													' . 
													( !in_array('review-photo', $zwssgr_selected_elements) ? ''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').'': '' ) . '
												</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements) ?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
										</div>
										'.((!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-content', $zwssgr_selected_elements))?'
										<div class="zwssgr-list-content-wrap">
											' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
											' .
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</p>' : '' ). '
										</div>':'') .'
									</div>
								</div>';
								
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
											<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
											' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
											<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews ', 'smart-showcase-for-google-reviews').' </b></p>
											<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
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
										<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
									</div>
									<img src="' . $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png" alt="G Icon">
								</div>
							</a>',

							'<a href="'.$zwssgr_location_all_review_uri.'" target="_blank" class="zwssgr-badge-link">
								<div class="zwssgr-badge-item zwssgr-badge4" id="zwssgr-badge4">
									<div class="zwssgr-badge4-rating">
										<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
										<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
										<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
										<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
									<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
												<h3>' . $zwssgr_location_name . '</h3>
												' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
												<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>' . $zwssgr_reviews_ratings['reviews'] . ' '.esc_html__('Google reviews', 'smart-showcase-for-google-reviews').'</b></p>
											</div>
										</div>
										<div class="zwssgr-slider zwssgr-grid-item zwssgr-popup-list">
											' . $zwssgr_popup_content1 . '
										</div>' .
										($zwssgr_enable_load_more && $zwssgr_query->max_num_pages >= 2 && in_array($zwssgr_layout_option, ['popup-1', 'popup-2']) ?
										'<button class="load-more-meta zwssgr-load-more-btn" style="background-color:' . esc_attr($zwssgr_bg_color_load) . '; color:' . esc_attr($zwssgr_text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($zwssgr_post_id) . '" data-rating-filter="' . esc_attr($zwssgr_rating_filter) . '">' . esc_html__('Load More', 'smart-showcase-for-google-reviews') . '</button>'
										: '') .
									'</div>
								</div>
							</div>',

							'<div class="zwssgr-popup-item zwssgr-popup2" id="zwssgr-popup2"  data-popup="zwssgrpopup2">
								<div class="zwssgr-title-wrap">
									<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
									<h3>'.esc_html__('Reviews', 'smart-showcase-for-google-reviews').'</h3>
								</div>
								<div class="zwssgr-info-wrap">
									<span class="final-rating">'.$zwssgr_reviews_ratings['ratings'].'</span>
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
											' . $zwssgr_popup_content2 . '
										</div>' .
										($zwssgr_enable_load_more && $zwssgr_query->max_num_pages >= 2 && in_array($zwssgr_layout_option, ['popup-1', 'popup-2']) ?
										'<button class="load-more-meta zwssgr-load-more-btn" style="background-color:' . esc_attr($zwssgr_bg_color_load) . '; color:' . esc_attr($zwssgr_text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($zwssgr_post_id) . '" data-rating-filter="' . esc_attr($zwssgr_rating_filter) . '">' . esc_html__('Load More', 'smart-showcase-for-google-reviews') . '</button>': '') .'
									</div>
								</div>
							</div>'
						]
					];
					$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
					$zwssgr_layout_option_divide = explode('-', $zwssgr_layout_option);
					
					$zwssgr_layout_option_key = $zwssgr_layout_option_divide[0]; 
					$zwssgr_layout_option_value = $zwssgr_layout_option_divide[1];
					$zwssgr_reviews_html .= $zwssgr_options[$zwssgr_layout_option_key][$zwssgr_layout_option_value-1];
					$zwssgr_reviews_html .= '</div>';
					
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

					echo wp_kses($zwssgr_reviews_html, $zwssgr_allowed_html);				
					
					// Add the Load More button only if 'enable_load_more' is true
					if ($zwssgr_enable_load_more && $zwssgr_query->max_num_pages >= 2 && in_array($zwssgr_layout_option, ['list-1','list-2','','list-3','list-4','list-5','grid-1','grid-2','grid-3','grid-4','grid-5'])) {
						echo '<button class="load-more-meta zwssgr-load-more-btn" style="background-color:' . esc_attr($zwssgr_bg_color_load) . '; color:' . esc_attr($zwssgr_text_color_load) . ';" data-page="2" data-post-id="' . esc_attr($zwssgr_post_id) . '" data-rating-filter="' . esc_attr($zwssgr_rating_filter) . '">' . esc_html__('Load More', 'smart-showcase-for-google-reviews') . '</button>';
					}
					
				echo '</div>';
					if($zwssgr_google_review_toggle){
						echo '<div class="zwssgr-toogle-display zwssgr-toogle-display-front">';
							echo '<a href="'.esc_url($zwssgr_location_new_review_uri).'" style="background-color:' . esc_attr($zwssgr_bg_color) . '; color:' . esc_attr($zwssgr_text_color) . ';" class="zwssgr-google-toggle" target="_blank">'.esc_html__('Review Us On G', 'smart-showcase-for-google-reviews').'</a>';
						echo '</div>';
					}
				} else {
					echo '<p class="zwssgr-no-found-message">' . esc_html__('No reviews found for the selected ratings.', 'smart-showcase-for-google-reviews') . '</p>';
				}

			echo '</div>';

			return ob_get_clean();  // Return the buffered content
		}
		
		function zwssgr_load_more_meta_data() 
		{
			// Verify nonce for security
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'zwssgr_load_more_nonce' ) ) {
				wp_send_json_error( esc_html__( 'Nonce verification failed.', 'smart-showcase-for-google-reviews' ) );
				return;
			}

			// Retrieve the page number and post_id
			$zwssgr_page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$zwssgr_post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

			// Ensure the post_id exists
			if (empty($zwssgr_post_id) || !get_post($zwssgr_post_id)) {
				wp_send_json_error(esc_html__('Invalid post ID.', 'smart-showcase-for-google-reviews'));
				return;
			}

			// Retrieve dynamic 'posts_per_page' from post meta
			$zwssgr_stored_posts_per_page = get_post_meta($zwssgr_post_id, 'posts_per_page', true);

			// Retrieve the 'rating_filter' value from the post meta
			$zwssgr_rating_filter = intval(get_post_meta($zwssgr_post_id, 'rating_filter', true)) ?: 0;

			// Define the mapping from numeric values to words
			$zwssgr_rating_mapping = array(
				1 => 'ONE',
				2 => 'TWO',
				3 => 'THREE',
				4 => 'FOUR',
				5 => 'FIVE'
			);

			// Convert the rating filter to a word
			$zwssgr_rating_filter_word = isset($zwssgr_rating_mapping[$zwssgr_rating_filter]) ? $zwssgr_rating_mapping[$zwssgr_rating_filter] : '';

			// Define the ratings to include based on the rating filter
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
			}else{
				$zwssgr_ratings_to_include = array('ONE', 'TWO', 'THREE', 'FOUR', 'FIVE');
			}

			$zwssgr_sort_by = isset($_POST['front_sort_by']) ? sanitize_text_field(wp_unslash($_POST['front_sort_by'])) : (get_post_meta($zwssgr_post_id, 'sort_by', true) ?: 'newest');
			$zwssgr_front_keyword = isset($_POST['front_keyword']) ? sanitize_text_field(wp_unslash($_POST['front_keyword'])) : '';

			
			$zwssgr_language = get_post_meta($zwssgr_post_id, 'language', true) ?: 'en'; 
			$zwssgr_date_format = get_post_meta($zwssgr_post_id, 'date_format', true) ?: 'DD/MM/YYYY';
			$zwssgr_months = $this->zwssgr_translate_months($zwssgr_language);

			$zwssgr_gmb_email = get_option('zwssgr_gmb_email');
			$zwssgr_account_number = get_post_meta($zwssgr_post_id, 'zwssgr_account_number', true);
			$zwssgr_location_number =get_post_meta($zwssgr_post_id, 'zwssgr_location_number', true);
			$zwssgr_location_all_review_uri =  get_post_meta($zwssgr_post_id, 'zwssgr_location_all_review_uri', true);
			$zwssgr_onload_first = isset($_POST['onload_first']) && $_POST['onload_first'] === "true";


			// Query for posts based on the current page
			$zwssgr_args = array(
				'post_type'      => ZWSSGR_POST_REVIEW_TYPE,  // Replace with your custom post type slug
				'posts_per_page' => $zwssgr_stored_posts_per_page,  // Use dynamic posts per page value
				'paged'          => $zwssgr_page,
				'meta_query'     => array(
					'relation' => 'AND',
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
				),
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

			if (!empty($zwssgr_location_number)) {
				$zwssgr_args['meta_query'][] = array(
					'key'     => 'zwssgr_location_number',
					'value'   => (string) $zwssgr_location_number,
					'compare' => '=',
					'type'    => 'CHAR'
				);
			}

			// Add the LIKE condition if front_keyword is present
			if ($zwssgr_front_keyword && $zwssgr_front_keyword !== 'all') {
				$zwssgr_args['meta_query'][]= array(
					'key'     => 'zwssgr_review_comment', // Replace with the actual meta key for the keyword
					'value'   => stripslashes($zwssgr_front_keyword),
					'compare' => 'LIKE',
				);
			}else{
				$zwssgr_args['meta_query'][]=array(
					'key'     => 'zwssgr_review_star_rating',
					'value'   => $zwssgr_ratings_to_include,  // Apply the word-based rating filter
					'compare' => 'IN',
					'type'    => 'CHAR'
				);
			}

			// Adjust sorting based on the 'sort_by' value
			switch ($zwssgr_sort_by) {
				case 'newest':
					$zwssgr_args['orderby'] = 'date';
					$zwssgr_args['order'] = 'DESC';
					if ($zwssgr_onload_first) {
						// Check if we need to prioritize pinned posts (i.e., zwssgr_is_pinned === 1)
						$zwssgr_pinned_query = get_posts(array(
							'post_type' => ZWSSGR_POST_REVIEW_TYPE,
							'meta_key'  => 'zwssgr_is_pinned',
							'meta_value' => 1,  // Only get pinned posts
							'fields' => 'ids',  // Return only post IDs
						));

						if (!empty($zwssgr_pinned_query)) {
							// Add custom ordering if there are pinned posts
							$zwssgr_args['orderby'] = array(
								'meta_value_num' => 'DESC',  // First, show pinned posts (1) at the top
								'date' => 'DESC',  // Then, sort by date for both pinned and unpinned
							);
							$zwssgr_args['meta_key'] = 'zwssgr_is_pinned';  // Sort by the zwssgr_is_pinned meta key
						}
					}
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
			$zwssgr_query = new WP_Query($zwssgr_args);

			if ($zwssgr_query->have_posts()) {
				$zwssgr_output = '';

				// Fetch selected elements from post meta
				$zwssgr_selected_elements = get_post_meta($zwssgr_post_id, 'selected_elements', true);
				$zwssgr_selected_elements = maybe_unserialize($zwssgr_selected_elements);

					// Loop through the posts and append the HTML content
					while ($zwssgr_query->have_posts()) {
						$zwssgr_query->the_post();

						// Retrieve meta values
						$zwssgr_reviewer_name    = get_post_meta(get_the_ID(), 'zwssgr_reviewer_name', true);
						$zwssgr_review_star_rating = get_post_meta(get_the_ID(), 'zwssgr_review_star_rating', true);
						$zwssgr_review_content   = get_post_meta(get_the_ID(), 'zwssgr_review_comment', true);
						$zwssgr_review_id= get_post_meta(get_the_ID(), 'zwssgr_review_id', true);
						$zwssgr_gmb_reviewer_image_path=ZWSSGR_UPLOAD_DIR . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
						$zwssgr_gmb_reviewer_image_uri =ZWSSGR_UPLOAD_URL . '/smart-showcase-for-google-reviews/gmb-reviewers/gmb-reviewer-'.$zwssgr_review_id.'.png';
						$zwssgr_location_name = get_post_meta($zwssgr_post_id, 'zwssgr_location_name', true);
						$zwssgr_published_date  = get_the_date('F j, Y');
						$zwssgr_char_limit = get_post_meta($zwssgr_post_id, 'char_limit', true); // Retrieve character limit meta value
						$zwssgr_char_limit = (int) $zwssgr_char_limit ;
						$zwssgr_plugin_dir_path = ZWSSGR_URL;
						$zwssgr_location_thumbnail_url = get_post_meta($zwssgr_post_id, 'zwssgr_location_thumbnail_url', true);
						$zwssgr_image_url = $zwssgr_location_thumbnail_url ? $zwssgr_location_thumbnail_url : $zwssgr_plugin_dir_path . 'assets/images/Google_G_Logo.png';

						$zwssgr_is_trimmed = $zwssgr_char_limit > 0 && mb_strlen($zwssgr_review_content) > $zwssgr_char_limit; // Check if the content length exceeds the character limit
						$zwssgr_trimmed_content = $zwssgr_is_trimmed ? mb_substr($zwssgr_review_content, 0, $zwssgr_char_limit) . '...' : $zwssgr_review_content; // Trim the content if necessary

						$zwssgr_formatted_date = '';
						if ($zwssgr_date_format === 'DD/MM/YYYY') {
							$zwssgr_formatted_date = gmdate('d/m/Y', strtotime($zwssgr_published_date));
						} elseif ($zwssgr_date_format === 'MM-DD-YYYY') {
							$zwssgr_formatted_date = gmdate('m-d-Y', strtotime($zwssgr_published_date));
						} elseif ($zwssgr_date_format === 'YYYY/MM/DD') {
							$zwssgr_formatted_date = gmdate('Y/m/d', strtotime($zwssgr_published_date));
						} elseif ($zwssgr_date_format === 'full') {
							$zwssgr_day = gmdate('j', strtotime($zwssgr_published_date));
							$zwssgr_month = $zwssgr_months[(int)gmdate('n', strtotime($zwssgr_published_date)) - 1];
							$zwssgr_year = gmdate('Y', strtotime($zwssgr_published_date));
							
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

						// Slider 
						$zwssgr_slider_item1 = '
							<div class="zwssgr-slide-item">' .
								'<div class="zwssgr-list-inner">' .
									'<div class="zwssgr-slide-wrap">' .
										(!in_array('review-photo', $zwssgr_selected_elements) ? '<div class="zwssgr-profile">' .
										''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').'' .'</div>' : '') .

										(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements) ?
										'<div class="zwssgr-review-info">' .
											(!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) 
												? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' 
												: '') .
											(!in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . '</h3>' : '') .
										'</div>':'') .
										(!in_array('review-g-icon', $zwssgr_selected_elements)?
										'<div class="zwssgr-google-icon">' .
											''.
										( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'' .
										'</div>':'') .
									'</div>' .

									(!in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) 
										? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' 
										: '') .

									(!in_array('review-content', $zwssgr_selected_elements) ? (!empty($zwssgr_trimmed_content) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) .
									($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') .'</p>' : '') : '') .
								'</div>' .
							'</div>';

						$zwssgr_slider_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">'.
									(!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements) ?'
									<div class="zwssgr-rating-wrap">
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . 
										
										( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) .
									'</div>':'').'
									
									' . 
									( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ) . 
									
									'<div class="zwssgr-slide-wrap">'.	
										( !in_array('review-photo', $zwssgr_selected_elements)?
										'<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) .
										'</div>':'').'
									
										'. (!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) .
										'</div>' :'').'
										
										'.( !in_array('review-g-icon', $zwssgr_selected_elements)?
										'<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
										</div>':'' ).'
									</div>
								</div>
							</div>';


						$zwssgr_slider_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwssgr-slide-wrap4">
										'.( (!in_array('review-photo', $zwssgr_selected_elements))?'
										<div class="zwssgr-profile">
											' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)) ?'
											<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
									
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';

						$zwssgr_slider_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									
									<div class="zwssgr-slide-wrap4">
										'.( (!in_array('review-photo', $zwssgr_selected_elements))?'
										<div class="zwssgr-profile">
											' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
											
										</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)) ?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									</div>
								</div>
							</div>';
						

						$zwssgr_slider_item5 = '
							<div class="zwssgr-slide-item">' .
								'<div>' .
									(!in_array('review-photo', $zwssgr_selected_elements) 
										? '<div class="zwssgr-profile">' .
												''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
											'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'' .
											'</div>':'').'	
										</div>' 
										: '') .
									(!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) 
										? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' 
										: '') .
									(!in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) 
										? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' 
										: '') .
									((!in_array('review-content', $zwssgr_selected_elements)&& $zwssgr_review_content) || !in_array('review-days-ago', $zwssgr_selected_elements) ? '
									<div class="zwssgr-contnt-wrap">' .
										(!in_array('review-content', $zwssgr_selected_elements) && !empty($zwssgr_trimmed_content) 
											? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) .
												($zwssgr_is_trimmed 
													? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
													: '') .
											'</p>' 
											: '') .
										(!in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
										? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . '</h3>' 
										: '') .
									'</div>' : '') .
								'</div>' .
							'</div>';

						$zwssgr_slider_item6 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										'.( !in_array('review-photo', $zwssgr_selected_elements)?'
										<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
										</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
										'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
										<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
										</div>':'').'
									</div>
									
									' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';
							
						// List
						$zwssgr_list_item1 = '
						<div class="zwssgr-slide-item">
							<div class="zwssgr-list-inner">
								<div class="zwssgr-slide-wrap">';
									if (!in_array('review-photo', $zwssgr_selected_elements)) {
										$zwssgr_list_item1 .= '
										<div class="zwssgr-profile">
											'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
										</div>';
									}
									if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
										$zwssgr_list_item1 .= '<div class="zwssgr-review-info">';
										if (!in_array('review-title', $zwssgr_selected_elements)) {
											$zwssgr_list_item1 .= '
												<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
										}
										if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
											$zwssgr_list_item1 .= '
												<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
													' . esc_html($zwssgr_formatted_date) . ' 
												</h3>';
										}
										$zwssgr_list_item1 .= '</div>';
									}
									
									if (!in_array('review-photo', $zwssgr_selected_elements)) {
										$zwssgr_list_item1 .= '
										<div class="zwssgr-google-icon">
											'.( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ).'
										</div>';
									}

								$zwssgr_list_item1 .= '</div>';
								if (!in_array('review-rating', $zwssgr_selected_elements)) {
									$zwssgr_list_item1 .= '
									<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
								}
								if (!in_array('review-content', $zwssgr_selected_elements)) {
									$zwssgr_list_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);

									if ($zwssgr_is_trimmed) {
										$zwssgr_list_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
									}

									$zwssgr_list_item1 .= '</p>';
								}

						$zwssgr_list_item1 .= '
							</div>
						</div>';
						
						$zwssgr_list_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										'.( !in_array('review-photo', $zwssgr_selected_elements)?'
										<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
										</div>':'').'
										'.( (!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
										'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
										<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
										</div>':'').'
									</div>
									'.((!in_array('review-rating', $zwssgr_selected_elements) ||!in_array('review-content', $zwssgr_selected_elements))?'<div class="zwssgr-list-content-wrap">
										' . 
											( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									</div>':'').'
								</div>
							</div>';

						$zwssgr_list_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									' . 
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ) . '
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap3">
										'.((!in_array('review-photo', $zwssgr_selected_elements))?'
										<div class="zwssgr-profile">
											' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
												'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
												'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
												
										</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
									</div>
								</div>
							</div>';
						
						$zwssgr_list_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap4 zwssgr-list-wrap4">
										'.((!in_array('review-photo', $zwssgr_selected_elements))?'
										<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
										</div>':'').'
										'.( (!in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name)) ?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
										</div>':'').'
										
										' . 
										( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
									</div>
									' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									' . 
									( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';
						
						$zwssgr_list_item5 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-list-wrap5">
										<div class="zwssgr-prifile-wrap">
											'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											</div>':'').'
											'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-data">
												' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
												
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'') .'
										</div>

										<div class="zwssgr-content-wrap">
											'.((!in_array('review-rating', $zwssgr_selected_elements) ||!in_array('review-g-icon', $zwssgr_selected_elements))?'
											<div class="zwssgr-review-info">
												' . 
													( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
												'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
													'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
											</div>':'').'
											' . 
												( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
											? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
											: '') . '</p>' : '' ) . '
										</div>
									</div>
								</div>
							</div>';

						// Gird
						$zwssgr_grid_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">';

										if (!in_array('review-photo', $zwssgr_selected_elements)) {
											$zwssgr_grid_item1 .= '
											<div class="zwssgr-profile">
											'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
											</div>';
										}

										if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
											$zwssgr_grid_item1 .= '<div class="zwssgr-review-info">';
											if (!in_array('review-title', $zwssgr_selected_elements)) {
												$zwssgr_grid_item1 .= '
													<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
											}
											if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
												$zwssgr_grid_item1 .= '
													<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
														' . esc_html($zwssgr_formatted_date) . ' 
													</h3>';
											}
											$zwssgr_grid_item1 .= '</div>'; 
										}
										

										if (!in_array('review-photo', $zwssgr_selected_elements)) {
											$zwssgr_grid_item1 .= '
											<div class="zwssgr-google-icon">
												'.( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ).'
											</div>';
										}

									$zwssgr_grid_item1 .= '</div>'; 

									if (!in_array('review-rating', $zwssgr_selected_elements)) {
										$zwssgr_grid_item1 .= '
										<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
									}

									if (!in_array('review-content', $zwssgr_selected_elements)) {
										$zwssgr_grid_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);

										if ($zwssgr_is_trimmed) {
											$zwssgr_grid_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
										}

										$zwssgr_grid_item1 .= '</p>';
									}

							$zwssgr_grid_item1 .= '
								</div>
							</div>';
						
						$zwssgr_grid_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										'.( !in_array('review-photo', $zwssgr_selected_elements)?'
										<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
										</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements))||( !in_array('review-rating', $zwssgr_selected_elements))||(!in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											'.( (!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
											<div class="zwssgr-date-wrap">
												' . 
												( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
												' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											</div>':'').'
											
										</div>':'').'
										'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
										<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
										</div>':'').'	
									</div>
									' . 
										
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';

						$zwssgr_grid_item3 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										<div class="zwssgr-review-detail">
											' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? '<div class="zwssgr-profile">
												'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
												</div>' : '' ) . '
											
											' . 
												( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
												( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
													? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
											'.((!in_array('review-g-icon', $zwssgr_selected_elements) ||!in_array('review-rating', $zwssgr_selected_elements))?'
											<div class="zwssgr-rating-wrap">
												'.( !in_array('review-g-icon', $zwssgr_selected_elements)?'
												<div class="zwssgr-google-icon">
													'.
													( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
												</div>':'').'
												
												' . 
												( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
											</div>':'').'	
										</div>
										' . 
											( !in_array('review-content', $zwssgr_selected_elements) && !empty($zwssgr_trimmed_content) ? '<div class="zwssgr-content-wrap"><p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</div></p>' : '' ) . '
										
									</div>
								</div>
							</div>';
						
						$zwssgr_grid_item4 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									'.((!in_array('review-photo', $zwssgr_selected_elements))?'
									<div class="zwssgr-profile">
										' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
											'.(!in_array('review-g-icon', $zwssgr_selected_elements)?'
											<div class="zwssgr-google-icon">
												'.
												( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
											</div>':'').'
											
									</div>':'').'
									' . 
										( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
									' . 
										( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) 
											? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
									' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									' . 
									( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed ? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' : '') . '</p>' : '' ) . '  
								</div>
							</div>';

						$zwssgr_grid_item5 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-grid-inner">
									<div class="zwssgr-slide-wrap">
										'.( !in_array('review-photo', $zwssgr_selected_elements)?'
										<div class="zwssgr-profile">
											' . 
											( !in_array('review-photo', $zwssgr_selected_elements) ? '	'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'' : '' ) . '
										</div>':'').'
										'.(( !in_array('review-title', $zwssgr_selected_elements)||!in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
										
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
									</div>
									' . 
									( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
									? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
									: '') . '</p>' : '' ) . '
								</div>
							</div>';

						// Popup
						$zwssgr_popup_item1 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">';

									if (!in_array('review-photo', $zwssgr_selected_elements)) {
										$zwssgr_popup_item1 .= '
										<div class="zwssgr-profile">
											'.	''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').''.'
										</div>';
									}
									if(!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements)){
										$zwssgr_popup_item1 .= '<div class="zwssgr-review-info">';
										if (!in_array('review-title', $zwssgr_selected_elements)) {
											$zwssgr_popup_item1 .= '
												<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>';
										}
										if (!in_array('review-days-ago', $zwssgr_selected_elements)) {
											$zwssgr_popup_item1 .= '
												<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">
													' . esc_html($zwssgr_formatted_date) . ' 
												</h3>';
										}
										$zwssgr_popup_item1 .= '</div>'; 
									}
								

									if (!in_array('review-rating', $zwssgr_selected_elements)) {
										$zwssgr_popup_item1 .= '
										<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>';
									}
									$zwssgr_popup_item1 .= '</div>'; 

									if (!in_array('review-content', $zwssgr_selected_elements)) {
										$zwssgr_popup_item1 .= '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content);

										if ($zwssgr_is_trimmed) {
											$zwssgr_popup_item1 .= ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>';
										}
										$zwssgr_popup_item1 .= '</p>';
									}
							$zwssgr_popup_item1 .= '
								</div>
							</div>';
					
						$zwssgr_popup_item2 = '
							<div class="zwssgr-slide-item">
								<div class="zwssgr-list-inner">
									<div class="zwssgr-slide-wrap">
										'.( !in_array('review-photo', $zwssgr_selected_elements)?'
											<div class="zwssgr-profile">
												' . 
												( !in_array('review-photo', $zwssgr_selected_elements) ? ''.(file_exists($zwssgr_gmb_reviewer_image_path) ? '<img src="' . esc_url($zwssgr_gmb_reviewer_image_uri) . '" class="fallback-user-dp" alt="'. esc_html($zwssgr_reviewer_name) .'">' : '<img src="' . $zwssgr_plugin_dir_path . 'assets/images/fallback-user-dp.png" alt="'. esc_html($zwssgr_reviewer_name) .'">').'': '' ) . '
											</div>':'').'
										'.((!in_array('review-title', $zwssgr_selected_elements) || !in_array('review-days-ago', $zwssgr_selected_elements))?'
										<div class="zwssgr-review-info">
											' . 
											( !in_array('review-title', $zwssgr_selected_elements) && !empty($zwssgr_reviewer_name) ? '<h2 class="zwssgr-title">' . esc_html($zwssgr_reviewer_name) . '</h2>' : '' ) . '
											' . 
											( !in_array('review-days-ago', $zwssgr_selected_elements) && !empty($zwssgr_published_date) ? '<h3 class="zwssgr-days-ago zwssgr-date" data-original-date="' . esc_attr($zwssgr_published_date) . '">' . esc_html($zwssgr_formatted_date) . ' </h3>' : '' ) . '
										</div>':'').'
										'.( !in_array('review-g-icon', $zwssgr_selected_elements) ?'
										<div class="zwssgr-google-icon">
											'.
											( !in_array('review-g-icon', $zwssgr_selected_elements) ? '<img src="' . esc_url($zwssgr_plugin_dir_path . 'assets/images/google-icon.png') . '" alt="Google Icon">' : '' ) .'
										</div>':'').'
										
									</div>
									'.((!in_array('review-rating', $zwssgr_selected_elements) || !in_array('review-content', $zwssgr_selected_elements))?'
									<div class="zwssgr-list-content-wrap">
										' . 
										( !in_array('review-rating', $zwssgr_selected_elements) && !empty($zwssgr_stars_html) ? '<div class="zwssgr-rating">' . $zwssgr_stars_html . '</div>' : '' ) . '
										' .
										( !in_array('review-content', $zwssgr_selected_elements) ? '<p class="zwssgr-content">' . esc_html($zwssgr_trimmed_content) . ($zwssgr_is_trimmed 
										? ' <a class="toggle-content" data-full-text="' . esc_attr($zwssgr_review_content) . '">' . esc_html($this->zwssgr_translate_read_more($zwssgr_language)) . '</a>' 
										: '') . '</p>' : '' ). '
									</div>':'') .'
								</div>
							</div>';

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
										<h3 class="zwssgr-average">'.esc_html__('Good', 'smart-showcase-for-google-reviews').'</h3>
										' . (!empty($zwssgr_final_rating) ? '<div class="zwssgr-rating">' . $zwssgr_final_rating . '</div>' : '') . '
										<p class="zwssgr-based-on">'.esc_html__('Based on', 'smart-showcase-for-google-reviews').' <b>  '.$zwssgr_reviews_ratings['reviews'].' '.esc_html__('reviews ', 'smart-showcase-for-google-reviews').' </b></p>
										<img src="' . $zwssgr_plugin_dir_path . 'assets/images/google.png" alt="Google">
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
					'list' => [
						$zwssgr_list_content1,
						$zwssgr_list_content2,
						$zwssgr_list_content3,
						$zwssgr_list_content4,
						$zwssgr_list_content5
					],
					'grid' => [
						$zwssgr_grid_content1,
						$zwssgr_grid_content2,
						$zwssgr_grid_content3,
						$zwssgr_grid_content4,
						$zwssgr_grid_content5
					],
					'popup' => [
						$zwssgr_popup_content1, 
						$zwssgr_popup_content2 
					]
				];

				$zwssgr_layout_option = get_post_meta($zwssgr_post_id, 'layout_option', true);
				$zwssgr_layout_option_divide = explode('-', $zwssgr_layout_option);
				
				$zwssgr_layout_option_key = $zwssgr_layout_option_divide[0]; 
				$zwssgr_layout_option_value = $zwssgr_layout_option_divide[1];
				$zwssgr_output .= $zwssgr_filter_layout[$zwssgr_layout_option_key][$zwssgr_layout_option_value-1];

				// Prepare the response with the loaded content and the new page number
				$zwssgr_response = array(
					'content'   => $zwssgr_output,  // Send HTML content in the response
					'new_page'  => $zwssgr_page + 1,
				);
				wp_reset_postdata();
			} else {
				// No more posts available
				$zwssgr_response['err_msg'] = esc_html__('No Review Founds.', 'smart-showcase-for-google-reviews');
			}
			$zwssgr_response['disable_button'] = ( $zwssgr_query->max_num_pages <= $zwssgr_page ) ? true : false;
			wp_send_json_success($zwssgr_response);

			wp_die();  // Properly terminate the AJAX request
		}
	}
}