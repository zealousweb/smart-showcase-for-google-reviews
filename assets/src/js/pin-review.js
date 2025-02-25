document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    
    // Pin Review Functionality
    document.addEventListener('click', function (e) {
        if (e.target.closest('.zwssgr-toggle-pin')) {
            e.preventDefault();

            const button = e.target.closest('.zwssgr-toggle-pin');
            const postId = button.getAttribute('data-post-id');
            const icon = button.querySelector('img');

            // Prepare the data to send
            const formData = new FormData();
            formData.append('action', 'toggle_visibility');
            formData.append('action_type', 'pin_toggle');
            formData.append('post_id', postId);
            formData.append('nonce', zwssgr_admin.nonce);

            // Send the AJAX request using Fetch API
            fetch(zwssgr_admin.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        icon.setAttribute('src', data.data.icon);
                        // console.log('Post is now: ' + data.data.state);
                    } else {
                        alert(data.data.message);
                    }
                })
                .catch(() => {
                    alert('An error occurred while toggling the pin state.');
                });
        }
    });
});
