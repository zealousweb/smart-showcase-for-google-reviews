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
                    $('#div-container').append(response.data.content);  // This will render the HTML on the screen

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
});
