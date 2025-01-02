jQuery(document).ready(function($) {

	// Bind click event to open popup
	$(document).on('click', '.zwsgr-popup-item', function (e) {
		var popupId = $(this).data('popup'); // Get the popup ID from the data attribute
		
		if( $( e.target ).hasClass('zwsgr-popup-item') ){
			$('#' + popupId).fadeIn(); // Show the popup
		} else {
			console.log( 'not found');
		}
	});

	// Bind click event to close popup when the close button is clicked
	$(document).on('click', '.zwsgr-close-popup', function () {
		$(this).closest('.zwsgr-popup-overlay').fadeOut(); // Hide the popup
	});

	// Bind click event to close popup when clicking outside the popup content
	$(document).on('click', '.zwsgr-popup-overlay', function (e) {
		if ($(e.target).is('.zwsgr-popup-overlay')) {
			$(this).fadeOut(); // Hide the popup
		}
	});

	// Bind keydown event to close popup when ESC key is pressed
	$(document).on('keydown', function (e) {
		if (e.key === "Escape" || e.keyCode === 27) {
			$('.zwsgr-popup-overlay').fadeOut(); // Hide the popup
		}
	});

    $('body').on('click','.load-more-meta',function() {
        var button = $(this);
        var page = button.data('page');  // Get the current page number
        var post_id = button.data('post-id');  // Get the post-id from the button data attribute
		var selectedValue = $('#front-sort-by-select').val();
		var keyword = $('#zwsgr-front-keywords-list li.selected').data('zwsgr-keyword');

        // Disable the button to prevent multiple clicks
        button.prop('disabled', true).text('Loading...');

        // AJAX request
        $.ajax({
            url: load_more.ajax_url,  // Use the localized ajax_url
            method: 'POST',
            data: {
                action: 'load_more_meta_data',  // Action hook for AJAX
                post_id: post_id,  // Pass the post-id from the button
                page: page,  // Pass the current page number
				front_sort_by: selectedValue,
				front_keyword: keyword,
                nonce: load_more.nonce  // Include the nonce for security
            },
            success: function(response) {
                // console.log(response, 'response ');
                if (response.success) {
                    // Append new post content to the #div-container
					
					var popupContentContainer = $('.zwsgr-slider.zwsgr-grid-item.zwsgr-popup-list');

					// Append new content to the popup
					if (popupContentContainer.length >= 1) {
						popupContentContainer.append(response.data.content);  // Append the new content to the popup
					}
					var container = $('#div-container[data-widget-id="' + post_id + '"]');

					if (container.find('.zwsgr-list').length >= 1) {
						container.find('.zwsgr-list').append(response.data.content);
					}
					if (container.find('.zwsgr-grid-item').length >= 1) {
						container.find('.zwsgr-grid-item').append(response.data.content);
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
        var $link = $(this);
        var fullText = $link.data('full-text');
        var $parentParagraph = $link.closest('p');
    
        // Replace the trimmed content with the full content
        $parentParagraph.html(fullText);
    });


    $('.zwsgr-slider-1').slick({
		infinite: true,
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

    $('.zwsgr-slider-2').slick({
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

	$('.zwsgr-slider-3').slick({
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

	$('.zwsgr-slider-4').slick({
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		dots: false,
	});	

	$('.zwsgr-slider-5').slick({
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

	$('.zwsgr-slider-6').slick({
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

	$('body').on('click', '#zwsgr-front-keywords-list li', function() {

		$('#zwsgr-front-keywords-list li').removeClass('selected');  // Remove previous selection
		$(this).addClass('selected');  // Add the 'selected' class to the clicked keyword

		// Get the keyword from the clicked element's data attribute
		const keyword = $(this).data('zwsgr-keyword');
		const mainDivWrapper = $(this).parents('.zwsgr-front-review-filter-wrap').next();

		var postId = mainDivWrapper.data('widget-id');
		const list_to_apnd = mainDivWrapper.find('.zwsgr-slider.zwsgr-list');
		const grid_to_apnd = mainDivWrapper.find('.zwsgr-slider.zwsgr-grid-item');
		const slider1_to_apnd = mainDivWrapper.find('.zwsgr-slider-1');
		const slider2_to_apnd = mainDivWrapper.find('.zwsgr-slider-2');
		const slider3_to_apnd = mainDivWrapper.find('.zwsgr-slider-3');
		const slider4_to_apnd = mainDivWrapper.find('.zwsgr-slider-4');
		const slider5_to_apnd = mainDivWrapper.find('.zwsgr-slider-5');
		const slider6_to_apnd = mainDivWrapper.find('.zwsgr-slider-6');
		
		var ratingFilter = $('.main-div-wrapper').data('rating-filter');
		var layoutType = $('.main-div-wrapper').data('layout-type');
		var selectedValue = $('#front-sort-by-select').val();
		var bg_color_load =$('.main-div-wrapper').data('bg-color');
		var text_color_load =$('.main-div-wrapper').data('text-color');
		var enable_load_more = $('.main-div-wrapper').data('enable-load-more');

		if (enable_load_more === 1){
			var loadMoreButton = '<button class="load-more-meta zwsgr-load-more-btn" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '" style="background-color: ' + bg_color_load + '; color: ' + text_color_load + ';">Load More</button>';
		}

		$('.zwsgr-slider.zwsgr-list');

		$('.load-more-meta').hide();

		// AJAX request
        $.ajax({
			url: load_more.ajax_url,
			method: 'POST',
			data: {
				action: 'load_more_meta_data',
				front_keyword: keyword,
				post_id: postId,
				front_sort_by: selectedValue,
				nonce: load_more.nonce
            },
            success: function(response) {

				  // Check if there is content in the response
				if (!response.data.content || response.data.content.trim() === '') {
					// No more posts, show the "No more posts." message
					list_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					grid_to_apnd.html('<p class="zwsgr-no-found-message" style="width:100%;">' + response.data.err_msg + '</p>');
					slider1_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider2_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider3_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider4_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider5_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider6_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
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
					reinitializeSlickSlider(slider1_to_apnd);
				}, 100);
				slider1_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider1_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider2_to_apnd);
				}, 100);
				slider2_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider2_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider3_to_apnd);
				}, 100);
				slider3_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider3_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider4_to_apnd);
				}, 100);
				slider4_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider4_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider5_to_apnd);
				}, 100);
				slider5_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider5_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider6_to_apnd);
				}, 100);
				slider6_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider6_to_apnd.append(response.data.content);
				
				if  (layoutType === 'list-1' || layoutType === 'list-2' || layoutType === 'list-3' || layoutType === 'list-4' || layoutType === 'list-5' ||
					layoutType === 'grid-1' || layoutType === 'grid-2' || layoutType === 'grid-3' || layoutType === 'grid-4' || layoutType === 'grid-5') {
					if( true != response.data.disable_button ){
						postId.append(loadMoreButton);  // Clears previous content and adds the button
					}
				}

				if(layoutType === 'popup-1'|| layoutType === 'popup-2'){
					if( true != response.data.disable_button ){
						$('.scrollable-content').append(loadMoreButton);  // Clears previous content and adds the button
					}
				}
            }
        });
	});

	// Listen for the change event on the select dropdown
	$('body').on('change', '#front-sort-by-select',function(){	

		var selectedValue = $('#front-sort-by-select').val();
		var keyword = $('#zwsgr-front-keywords-list li.selected').data('zwsgr-keyword'); 
		const mainDivWrapper = $(this).parents('.zwsgr-front-review-filter-wrap').next();

		var postId = mainDivWrapper.data('widget-id');
		const list_to_apnd = mainDivWrapper.find('.zwsgr-slider.zwsgr-list');
		const grid_to_apnd = mainDivWrapper.find('.zwsgr-slider.zwsgr-grid-item');
		const slider1_to_apnd = mainDivWrapper.find('.zwsgr-slider-1');
		const slider2_to_apnd = mainDivWrapper.find('.zwsgr-slider-2');
		const slider3_to_apnd = mainDivWrapper.find('.zwsgr-slider-3');
		const slider4_to_apnd = mainDivWrapper.find('.zwsgr-slider-4');
		const slider5_to_apnd = mainDivWrapper.find('.zwsgr-slider-5');
		const slider6_to_apnd = mainDivWrapper.find('.zwsgr-slider-6');
		
	
		// var postId = $('.main-div-wrapper').data('widget-id');
		var ratingFilter = $('.main-div-wrapper').data('rating-filter');
		var layoutType = $('.main-div-wrapper').data('layout-type');
		var bg_color_load =$('.main-div-wrapper').data('bg-color');
		var text_color_load =$('.main-div-wrapper').data('text-color');
		var enable_load_more = $('.main-div-wrapper').data('enable-load-more');

		if (enable_load_more === 1){
			var loadMoreButton = '<button class="load-more-meta zwsgr-load-more-btn" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '" style="background-color: ' + bg_color_load + '; color: ' + text_color_load + ';">Load More</button>';
		}

		$('.load-more-meta').remove();

		 
		// Send the selected value via AJAX
		$.ajax({
			url: load_more.ajax_url,
			method: 'POST',
			data: {
				action: 'load_more_meta_data',
				front_sort_by: selectedValue,
				post_id: postId,
				front_keyword: keyword,
				nonce: load_more.nonce
			},
			success: function(response) {

				// console.log(response.data.content, 'response');

				 // Check if there is content in the response
				if (!response.data.content || response.data.content.trim() === '') {
					// No more posts, show the "No more posts." message
					list_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					grid_to_apnd.html('<p class="zwsgr-no-found-message" style="width:100%;">' + response.data.err_msg + '</p>');
					slider1_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider2_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider3_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider4_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider5_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
					slider6_to_apnd.html('<p class="zwsgr-no-found-message">' + response.data.err_msg + '</p>');
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
					reinitializeSlickSlider(slider1_to_apnd);
				}, 100);
				slider1_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider1_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider2_to_apnd);
				}, 100);
				slider2_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider2_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider3_to_apnd);
				}, 100);
				slider3_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider3_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider4_to_apnd);
				}, 100);
				slider4_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider4_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider5_to_apnd);
				}, 100);
				slider5_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider5_to_apnd.append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider(slider6_to_apnd);
				}, 100);
				slider6_to_apnd.empty('');
				// Append the 'Load More' button before making the AJAX request
				slider6_to_apnd.append(response.data.content);
				
				if  (layoutType === 'list-1' || layoutType === 'list-2' || layoutType === 'list-3' || layoutType === 'list-4' || layoutType === 'list-5' ||
				layoutType === 'grid-1' || layoutType === 'grid-2' || layoutType === 'grid-3' || layoutType === 'grid-4' || layoutType === 'grid-5') {
					if( true != response.data.disable_button ){
						mainDivWrapper.append(loadMoreButton);  // Clears previous content and adds the button
					}
				}
				if(layoutType === 'popup-1'|| layoutType === 'popup-2'){
					if( true != response.data.disable_button ){
						$('.scrollable-content').append(loadMoreButton);  // Clears previous content and adds the button
					}
				}
				// console.log(response); // Log success response
			}
		});

	});
	function reinitializeSlickSlider(container) {
		// Find and reinitialize Slick sliders
		var slider1 = $(container).find('.zwsgr-slider-1');
		var slider2 = $(container).find('.zwsgr-slider-2');
		var slider3 = $(container).find('.zwsgr-slider-3');
		var slider4 = $(container).find('.zwsgr-slider-4');
		var slider5 = $(container).find('.zwsgr-slider-5');
		var slider6 = $(container).find('.zwsgr-slider-6');

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
