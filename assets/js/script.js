jQuery(document).ready(function($) {
    $('.load-more-meta').on('click', function() {
        var button = $(this);
        var page = button.data('page');  // Get the current page number
        var post_id = button.data('post-id');  // Get the post-id from the button data attribute

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
                nonce: load_more.nonce  // Include the nonce for security
            },
            success: function(response) {
                console.log(response, 'response ');
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

});
