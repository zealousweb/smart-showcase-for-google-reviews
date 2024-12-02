jQuery(document).ready(function($) {
    $('body').on('click','.load-more-meta',function() {
        var button = $(this);
        var page = button.data('page');  // Get the current page number
        var post_id = button.data('post-id');  // Get the post-id from the button data attribute
		var selectedValue = $('#front-sort-by-select').val();

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

	// Listen for the change event on the select dropdown
	$('body').on('change', '#front-sort-by-select',function(){	

		var selectedValue = $('#front-sort-by-select').val();
		console.log(selectedValue); 
	
		var postId = $('.main-div-wrapper').data('widget-id');
		var ratingFilter = $('.main-div-wrapper').data('rating-filter');
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
				nonce: load_more.nonce
			},
			success: function(response) {

				// console.log(response.data.content, 'response');

				$('.zwsgr-slider.zwsgr-list').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-list').append(response.data.content);

				$('.zwsgr-slider.zwsgr-grid-item').empty('');
				// Append the 'Load More' button before making the AJAX request
				$('.zwsgr-slider.zwsgr-grid-item').append(response.data.content);
				 

				$('.main-div-wrapper').append(loadMoreButton);  // Clears previous content and adds button
	   

				// console.log(response); // Log success response
			}
		});

	});

});
