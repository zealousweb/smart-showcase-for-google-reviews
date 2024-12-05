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

    $('body').on('click','.load-more-meta',function() {
        var button = $(this);
        var page = button.data('page');  // Get the current page number
        var post_id = button.data('post-id');  // Get the post-id from the button data attribute
		var selectedValue = $('#front-sort-by-select').val();
		var keyword = $('#front-keywords-list li.selected').data('zwsgr-keyword');

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

					if($('#div-container .zwsgr-list').length >= 1){
						$('#div-container .zwsgr-list').append(response.data.content);  // This will render the HTML on the screen 
					}
					if($('#div-container .zwsgr-grid-item').length >= 1){
						$('#div-container .zwsgr-grid-item').append(response.data.content);   
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

	$('body').on('click', '#front-keywords-list li', function() {

		$('#front-keywords-list li').removeClass('selected');  // Remove previous selection
		$(this).addClass('selected');  // Add the 'selected' class to the clicked keyword

		// Get the keyword from the clicked element's data attribute
		const keyword = $(this).data('zwsgr-keyword');
		var postId = $('.main-div-wrapper').data('widget-id');
		var ratingFilter = $('.main-div-wrapper').data('rating-filter');
		var layoutType = $('.main-div-wrapper').data('layout-type');
		var selectedValue = $('#front-sort-by-select').val();

		var loadMoreButton = '<button class="load-more-meta" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '">Load More</button>';
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

				// List
				$('.zwsgr-slider.zwsgr-list').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-list').append(response.data.content);

				// Grid
				$('.zwsgr-slider.zwsgr-grid-item').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-grid-item').append(response.data.content);

				// Slider
				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-1'));
				}, 100);
				$('.zwsgr-slider-1').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-1').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-2'));
				}, 100);
				$('.zwsgr-slider-2').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-2').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-4'));
				}, 100);
				$('.zwsgr-slider-4').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-4').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-5'));
				}, 100);
				$('.zwsgr-slider-5').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-5').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-6'));
				}, 100);
				$('.zwsgr-slider-6').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-6').append(response.data.content);

				// Popup
				$('.zwsgr-popup-item').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-popup-item').append(response.data.content);
				
				
				if (layoutType === 'list' || layoutType === 'grid') {
					if( true != response.data.disable_button ){
						$('.main-div-wrapper').append(loadMoreButton);  // Clears previous content and adds the button
					}
				}
            }
        });
	});

	// Listen for the change event on the select dropdown
	$('body').on('change', '#front-sort-by-select',function(){	

		var selectedValue = $('#front-sort-by-select').val();
		var keyword = $('#front-keywords-list li.selected').data('zwsgr-keyword'); 
	
		var postId = $('.main-div-wrapper').data('widget-id');
		var ratingFilter = $('.main-div-wrapper').data('rating-filter');
		var layoutType = $('.main-div-wrapper').data('layout-type');

		var loadMoreButton = '<button class="load-more-meta" data-page="2" data-post-id="' + postId + '" data-rating-filter="' + ratingFilter + '">Load More</button>';
		$('.zwsgr-slider.zwsgr-list');

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

				// List
				$('.zwsgr-slider.zwsgr-list').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-list').append(response.data.content);

				// Grid
				$('.zwsgr-slider.zwsgr-grid-item').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-grid-item').append(response.data.content);

				// Slider
				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-1'));
				}, 100);
				$('.zwsgr-slider-1').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-1').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-2'));
				}, 100);
				$('.zwsgr-slider-2').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-2').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-4'));
				}, 100);
				$('.zwsgr-slider-4').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-4').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-5'));
				}, 100);
				$('.zwsgr-slider-5').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-5').append(response.data.content);

				setTimeout(function() {
					reinitializeSlickSlider($('.zwsgr-slider-6'));
				}, 100);
				$('.zwsgr-slider-6').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider-6').append(response.data.content);

				// Popup
				$('.zwsgr-popup-item').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-popup-item').append(response.data.content);
				
				if (layoutType === 'list' || layoutType === 'grid') {
					if( true != response.data.disable_button ){
						$('.main-div-wrapper').append(loadMoreButton);  // Clears previous content and adds the button
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
