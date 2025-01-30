(function ($) {
	"use strict";
	function initSlickSlider() {
		$('.zwssgr-slider-1').not('.slick-initialized').slick({
			slidesToShow: 3,
			slidesToScroll: 3,
			arrows: true,
			dots: false,
			adaptiveHeight: false,
			responsive: [
				{
					breakpoint: 1200,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
			]
		});
		$('.zwssgr-slider-2').not('.slick-initialized').slick({
			infinite: true,
			slidesToShow: 3,
			slidesToScroll: 3,
			arrows: true,
			dots: false,
			responsive: [
				{
					breakpoint: 1200,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
			]
		});
		$('.zwssgr-slider-3').not('.slick-initialized').slick({
			infinite: true,
			slidesToShow: 2,
			slidesToScroll: 2,
			arrows: true,
			dots: false,
			responsive: [
				{
					breakpoint: 1180,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
			]
		});
		$('.zwssgr-slider-4').not('.slick-initialized').slick({
			infinite: true,
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: true,
			dots: false,
		});
		$('.zwssgr-slider-5').not('.slick-initialized').slick({
			infinite: true,
			slidesToShow: 2,
			slidesToScroll: 2,
			arrows: true,
			dots: false,
			responsive: [
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
			]
		});
		$('.zwssgr-slider-6').not('.slick-initialized').slick({
			infinite: true,
			slidesToShow: 3,
			slidesToScroll: 3,
			arrows: true,
			dots: false,
			responsive: [
				{
					breakpoint: 1200,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
			]
		});
	}

	$(document).ready(function () {
		initSlickSlider();
	});

	// Reinitialize Slick when Elementor updates preview
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			initSlickSlider();
		});
	});
})(jQuery);

jQuery(document).ready(function($) {
	"use strict";
	
	// Bind click event to open popup
	$(document).on('click', '.zwssgr-popup-item', function (e) {
		let popupId = $(this).data('popup'); // Get the popup ID from the data attribute
		
		if( $( e.target ).hasClass('zwssgr-popup-item') ){
			$('#' + popupId).fadeIn(); // Show the popup
		} else {
			console.log( 'not found');
		}
	});

	// Bind click event to close popup when the close button is clicked
	$(document).on('click', '.zwssgr-close-popup', function () {
		$(this).closest('.zwssgr-popup-overlay').fadeOut(); // Hide the popup
	});

	// Bind click event to close popup when clicking outside the popup content
	$(document).on('click', '.zwssgr-popup-overlay', function (e) {
		if ($(e.target).is('.zwssgr-popup-overlay')) {
			$(this).fadeOut(); // Hide the popup
		}
	});

	// Bind keydown event to close popup when ESC key is pressed
	$(document).on('keydown', function (e) {
		if (e.key === "Escape" || e.keyCode === 27) {
			$('.zwssgr-popup-overlay').fadeOut(); // Hide the popup
		}
	});

    $(document).on('click','.load-more-meta',function() {
		const mainWrapper = $(this).parents('.zwssgr-main-wrapper');
        let button = mainWrapper.find(this);
        let page = button.data('page');  // Get the current page number
        let postId = button.data('post-id');  // Get the post-id from the button data attribute
		let selectedValue = mainWrapper.find('.front-sort-by-select').val();
		let keyword = mainWrapper.find('.zwssgr-front-keywords-list li.selected').data('zwssgr-keyword');
		let popupContentContainer = mainWrapper.find('zwssgr-slider.zwssgr-grid-item.zwssgr-popup-list');
		let isFirstLoad = mainWrapper.data('onload-first'); // Get onload-first value

		// Set `data-onload-first` to false after first interaction
		mainWrapper.data('onload-first', false);

        // Disable the button to prevent multiple clicks
        button.prop('disabled', true).text('Loading...');

        // AJAX request
        $.ajax({
            url: load_more.ajax_url,  // Use the localized ajax_url
            method: 'POST',
            data: {
                action: 'zwssgr_load_more_meta_data',  // Action hook for AJAX
                post_id: postId,  // Pass the post-id from the button
                page: page,  // Pass the current page number
				front_sort_by: selectedValue,
				front_keyword: keyword,
				onload_first: isFirstLoad, // Pass the flag to PHP
                nonce: load_more.nonce  // Include the nonce for security
            },
            success: function(response) {
                // console.log(response, 'response ');
                if (response.success) {
					// Append new content to the popup
					if (popupContentContainer.length >= 1) {
						popupContentContainer.append(response.data.content);  // Append the new content to the popup
					}
					let container = $('#div-container[data-widget-id="' + postId + '"]');

					if (container.find('.zwssgr-list').length >= 1) {
						container.find('.zwssgr-list').append(response.data.content);
					}
					if (container.find('.zwssgr-grid-item').length >= 1) {
						container.find('.zwssgr-grid-item').append(response.data.content);
					}

                    // Update the page number for future requests
                    button.data('page', response.data.new_page);

                    // If no more posts, remove or disable the button
                    if (response.data.disable_button) {
                        button.remove();  // Remove the button if no more posts
                    } else {
                        button.prop('disabled', false).text('Load More');  // Re-enable button and reset text
                    }
                } else {
                    button.prop('disabled', false).text('Error, try again');
                }
            },
            error: function() {
                button.prop('disabled', false).text('Error, try again');
            }
        });
    });

    $(document).on('click', '.toggle-content', function () {
        let $link = $(this);
        let fullText = $link.data('full-text');
        let $parentParagraph = $link.closest('p');
    
        // Replace the trimmed content with the full content
        $parentParagraph.html(fullText);
    });

	$(document).on('click', '.zwssgr-front-keywords-list  li', function () {
		$('.zwssgr-main-wrapper').attr('data-onload-first', 'false');
		
		// Get the closest '.zwssgr-main-wrapper' for the clicked element
		const mainWrapper = $(this).parents('.zwssgr-main-wrapper');
		mainWrapper.find('.zwssgr-front-keywords-list li').removeClass('selected');
		$(this).addClass('selected');
		const postId = mainWrapper.data('widget-id'); // Get the dynamic post ID
	
		if (!postId) {
			console.warn('Post ID not found for the selected element.');
			return;
		}
		const keyword = mainWrapper.find(this).data('zwssgr-keyword'); // Get the clicked keyword
		const selectedValue = mainWrapper.find('.front-sort-by-select').val();
	
		const mainDivWrapper = mainWrapper.find('.zwssgr-front-review-filter-wrap').next();
		const list_to_apnd = mainDivWrapper.find('.zwssgr-slider.zwssgr-list');
		const grid_to_apnd = mainDivWrapper.find('.zwssgr-slider.zwssgr-grid-item');
	
		const ratingFilter = mainDivWrapper.data('rating-filter');
		const layoutType = mainDivWrapper.data('layout-type');
		const bg_color_load = mainDivWrapper.data('bg-color');
		const text_color_load = mainDivWrapper.data('text-color');
		const enable_load_more = mainDivWrapper.data('enable-load-more');
		let isFirstLoad = mainWrapper.data('onload-first'); // Get onload-first value

		// Set `data-onload-first` to false after first interaction
		mainWrapper.data('onload-first', false);
		
		if (enable_load_more === 1){
			window.zwssgrLoadMoreButton = '<button class="load-more-meta zwssgr-load-more-btn" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '" style="background-color: ' + bg_color_load + '; color: ' + text_color_load + ';">Load More</button>';
		}

		$('.zwssgr-slider.zwssgr-list');

		// Hide existing Load More buttons in the current wrapper
		mainDivWrapper.find('.load-more-meta').hide();

		// AJAX request
        $.ajax({
			url: load_more.ajax_url,
			method: 'POST',
			data: {
				action: 'zwssgr_load_more_meta_data',
				front_keyword: keyword,
				post_id: postId,
				front_sort_by: selectedValue,
				onload_first: isFirstLoad, // Pass the flag to PHP
				nonce: load_more.nonce
            },
            success: function(response) {

				// console.log(response.data.content, 'response');

				 // Check if there is content in the response
				if (!response.data.content || response.data.content.trim() === '') {
					// No more posts, show the "No more posts." message
					list_to_apnd.html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					grid_to_apnd.html('<p class="zwssgr-no-found-message" style="width:100%;">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-1').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-2').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('#zwssgr-slider3').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-4').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-5').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-6').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					return;
				}

				// List
				list_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				list_to_apnd.append(response.data.content);

				// Grid
				grid_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				grid_to_apnd.append(response.data.content);

				// Slider
				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-1'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-1').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-1').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-2'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-2').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-2').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('#zwssgr-slider3'));
				}, 100);
				mainDivWrapper.find('#zwssgr-slider3').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('#zwssgr-slider3').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-4'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-4').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-4').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-5'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-5').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-5').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-6'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-6').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-6').append(response.data.content);
				
				
				if  (layoutType === 'list-1' || layoutType === 'list-2' || layoutType === 'list-3' || layoutType === 'list-4' || layoutType === 'list-5' ||
				layoutType === 'grid-1' || layoutType === 'grid-2' || layoutType === 'grid-3' || layoutType === 'grid-4' || layoutType === 'grid-5') {
					if( true != response.data.disable_button ){
						mainDivWrapper.append(window.zwssgrLoadMoreButton);  // Clears previous content and adds the button
					}
				}
				if(layoutType === 'popup-1'|| layoutType === 'popup-2'){
					if( true != response.data.disable_button ){
						$('.scrollable-content').append(window.zwssgrLoadMoreButton);  // Clears previous content and adds the button
					}
				}
				// console.log(response); // Log success response
			}
        });
	});

	$(document).on('change', '.front-sort-by-select', function () {
		$('.zwssgr-main-wrapper').attr('data-onload-first', 'false');
		const mainWrapper = $(this).parents('.zwssgr-main-wrapper'); // Find the closest wrapper for scoping
		const postId = mainWrapper.data('widget-id'); // Dynamically get the post ID from the wrapper
	
		if (!postId) {
			console.error('Post ID not found');
			return;
		}
	
		const selectedValue = mainWrapper.find(this).val();
		const keyword = mainWrapper.find('.zwssgr-front-keywords-list li.selected').data('zwssgr-keyword');
		const mainDivWrapper = mainWrapper.find('.zwssgr-front-review-filter-wrap').next();
	
		const list_to_apnd = mainDivWrapper.find('.zwssgr-slider.zwssgr-list');
		const grid_to_apnd = mainDivWrapper.find('.zwssgr-slider.zwssgr-grid-item');
	
		const ratingFilter = mainDivWrapper.data('rating-filter');
		const layoutType = mainDivWrapper.data('layout-type');
		const bg_color_load = mainDivWrapper.data('bg-color');
		const text_color_load = mainDivWrapper.data('text-color');
		const enable_load_more = mainDivWrapper.data('enable-load-more');
		let isFirstLoad = mainWrapper.data('onload-first'); // Get onload-first value

		// Set `data-onload-first` to false after first interaction
		mainWrapper.data('onload-first', false);
	
		if (enable_load_more === 1){
			window.zwssgrLoadMoreButton = '<button class="load-more-meta zwssgr-load-more-btn" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '" style="background-color: ' + bg_color_load + '; color: ' + text_color_load + ';">Load More</button>';
		}

		mainDivWrapper.find('.load-more-meta').remove();
	
		// Send the selected value via AJAX
		$.ajax({
			url: load_more.ajax_url,
			method: 'POST',
			data: {
				action: 'zwssgr_load_more_meta_data',
				front_sort_by: selectedValue,
				post_id: postId,
				front_keyword: keyword,
				onload_first: isFirstLoad, // Pass the flag to PHP
				nonce: load_more.nonce
			},
			success: function(response) {

				 // Check if there is content in the response
				if (!response.data.content || response.data.content.trim() === '') {
					// No more posts, show the "No more posts." message
					list_to_apnd.html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					grid_to_apnd.html('<p class="zwssgr-no-found-message" style="width:100%;">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-1').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-2').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('#zwssgr-slider3').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-4').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-5').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					mainDivWrapper.find('.zwssgr-slider-6').html('<p class="zwssgr-no-found-message">' + response.data.err_msg + '</p>');
					return;
				}

				// List
				list_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				list_to_apnd.append(response.data.content);

				// Grid
				grid_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				grid_to_apnd.append(response.data.content);

				// Slider
				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-1'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-1').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-1').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-2'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-2').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-2').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('#zwssgr-slider3'));
				}, 100);
				mainDivWrapper.find('#zwssgr-slider3').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('#zwssgr-slider3').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-4'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-4').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-4').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-5'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-5').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-5').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(mainDivWrapper.find('.zwssgr-slider-6'));
				}, 100);
				mainDivWrapper.find('.zwssgr-slider-6').empty('');
				// Append the 'Load More' button before making the AJAX request
				mainDivWrapper.find('.zwssgr-slider-6').append(response.data.content);
				
				if  (layoutType === 'list-1' || layoutType === 'list-2' || layoutType === 'list-3' || layoutType === 'list-4' || layoutType === 'list-5' ||
				layoutType === 'grid-1' || layoutType === 'grid-2' || layoutType === 'grid-3' || layoutType === 'grid-4' || layoutType === 'grid-5') {
					if( true != response.data.disable_button ){
						mainDivWrapper.append(window.zwssgrLoadMoreButton);  // Clears previous content and adds the button
					}
				}
				if(layoutType === 'popup-1'|| layoutType === 'popup-2'){
					if( true != response.data.disable_button ){
						$('.scrollable-content').append(window.zwssgrLoadMoreButton);  // Clears previous content and adds the button
					}
				}
				// console.log(response); // Log success response
			}
		});
	});
	
	function reinitializeSlickSlider(container) {
		// Find and reinitialize Slick sliders
		let slider1 = $(container).find('.zwssgr-slider-1');
		let slider2 = $(container).find('.zwssgr-slider-2');
		let slider3 = $(container).find('.zwssgr-slider-3');
		let slider4 = $(container).find('.zwssgr-slider-4');
		let slider5 = $(container).find('.zwssgr-slider-5');
		let slider6 = $(container).find('.zwssgr-slider-6');

		// Unslick if it's already initialized
		if (slider1.hasClass('slick-initialized')) {
			slider1.slick('unslick');
		}

		if (slider2.hasClass('slick-initialized')) {
			slider2.slick('unslick');
		}

		if (slider4.hasClass('slick-initialized')) {
			slider4.slick('unslick');
		}

		if (slider5.hasClass('slick-initialized')) {
			slider5.slick('unslick');
		}

		if (slider6.hasClass('slick-initialized')) {
			slider6.slick('unslick');
		}


		// Reinitialize the selected slider
		if (slider1.length) {
			slider1.slick({
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		}

		if (slider2.length) {
			slider2.slick({
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		}

		if (slider3.length) {
			slider3.slick({
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 2,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1180,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		}

		if (slider4.length) {
			slider4.slick({
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: true,
				dots: false,
			});
		}

		if (slider5.length) {
			slider5.slick({
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 2,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		}

		if (slider6.length) {
			slider6.slick({
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		}
	}

});
