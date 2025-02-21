document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    // Function to show custom notifications
    function showNotification(message, type) {
        // Define the notification types: success, error, warning, info
        let notificationClass = 'zwssgr-notice-' + type; // Example: zwssgr-notice-success, zwssgr-notice-error

        // Create the notification HTML
        let notification = document.createElement('div');
        notification.className = `zwssgr-notice ${notificationClass} zwssgr-is-dismissible`;
        notification.innerHTML = `<p>${message}</p>`;

        // Append the notification to the target area
        let dashboard = document.querySelector('.zwssgr-dashboard');
        if (dashboard) {
            dashboard.prepend(notification);
        }

        // Add click event for dismissing the notification
        notification.addEventListener('click', function (event) {
            if (event.target.classList.contains('zwssgr-notice-dismiss')) {
                notification.style.transition = "opacity 0.3s";
                notification.style.opacity = "0";
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        });
    }

    // Function to get query parameters
    function getQueryParam(name) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // Handle click events for "Select Option" buttons
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('select-btn')) {
            let optionId = event.target.getAttribute('data-option');
            let postId = getQueryParam('zwssgr_widget_id');
            let currentUrl = window.location.href.split('?')[0];

            if (!postId) {
                showNotification('Post ID not found!', 'error'); // Custom error notification
                return;
            }

            // Fetch the HTML for the selected option using the correct optionId
            let selectedOptionElement = document.getElementById(optionId);
            let displayArea = document.getElementById('selected-option-display');
            
            if (selectedOptionElement && displayArea) {
                displayArea.innerHTML = selectedOptionElement.outerHTML; // Clone the selected option's element
                let clonedElement = displayArea.firstElementChild;
                
                // Remove the select button from the cloned HTML
                let selectButton = clonedElement.querySelector('.select-btn');
                if (selectButton) {
                    selectButton.remove();
                }
            }

            // Get the current display option
            let displayOption = document.querySelector('input[name="display_option"]:checked')?.value;
            let activeTab = document.querySelector('.tab-item.active');
            let settings = activeTab ? activeTab.getAttribute('data-tab') : '';
            let currentTab = activeTab ? activeTab.dataset.tab : '';

            // Perform AJAX request using Fetch API
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'zwssgr_save_widget_data',
                    security: my_widget.nonce,
                    layout_option: optionId,
                    display_option: displayOption,
                    post_id: postId,
                    settings: settings,
                    current_tab: currentTab
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Layout option saved successfully!', 'success'); // Show success message

                    // Append post_id and selected option to the URL
                    window.location.href = `${currentUrl}?page=zwssgr_widget_configurator&tab=tab-selected&selectedOption=${optionId}&zwssgr_widget_id=${postId}`;
                    
                } else {
                    showNotification('Failed to save layout option.', 'error'); // Show error message
                }
            })
            .catch(() => {
                showNotification('An error occurred.', 'error'); // Show error message
            });

        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'save-get-code-btn') {
            e.preventDefault();
    
            function getQueryParam(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }
            
            let selectedOption = getQueryParam('selectedOption');
            let currentUrl = window.location.href.split('?')[0];
    
            let postId = getQueryParam('zwssgr_widget_id');
            let displayOption = document.querySelector('input[name="display_option"]:checked')?.value || '';
            let selectedElements = Array.from(document.querySelectorAll('input[name="review-element"]:checked')).map(el => el.value);
            let keywords = Array.from(document.querySelectorAll('#keywords-list .keyword-item')).map(el => el.textContent.trim().replace(' ✖', ''));
            let dateFormat = document.getElementById('date-format-select')?.value || '';
            let charLimit = document.getElementById('review-char-limit')?.value || '';
            let language = document.getElementById('language-select')?.value || '';
            let sortBy = document.getElementById('sort-by-select')?.value || '';
            let enableLoadMore = document.getElementById('enable-load-more')?.checked ? 1 : 0;
            let googleReviewToggle = document.getElementById('toggle-google-review')?.checked ? 1 : 0;
            let bgColor = document.getElementById('bg-color-picker')?.value || '';
            let textColor = document.getElementById('text-color-picker')?.value || '';
            let bgColorLoad = document.getElementById('bg-color-picker_load')?.value || '';
            let textColorLoad = document.getElementById('text-color-picker_load')?.value || '';
            let activeTab = document.querySelector('.tab-item.active')?.getAttribute('data-tab') || '';
            let postsPerPage = document.getElementById('posts-per-page')?.value || '';
            let selectedRating = [...document.querySelectorAll('.star-filter.selected')].pop()?.dataset.rating || 0;
            let currentTab2 = document.querySelector('.tab-item.active')?.dataset.tab || '';
            let customCSS = document.querySelector('.zwssgr-textarea')?.value || '';
            let enableSortBy = document.getElementById('enable-sort-by-filter')?.checked ? 1 : 0;
    
            let formData = new FormData();
            formData.append('action', 'zwssgr_save_widget_data');
            formData.append('security', my_widget.nonce);
            formData.append('post_id', postId);
            formData.append('display_option', displayOption);
            formData.append('selected_elements', JSON.stringify(selectedElements)); // Ensure it's a JSON string
            formData.append('rating_filter', selectedRating);
            formData.append('keywords', JSON.stringify(keywords)); // Ensure it's a JSON string
            formData.append('date_format', dateFormat);
            formData.append('char_limit', charLimit);
            formData.append('language', language);
            formData.append('sort_by', sortBy);
            formData.append('enable_load_more', enableLoadMore);
            formData.append('google_review_toggle', googleReviewToggle);
            formData.append('bg_color', bgColor);
            formData.append('text_color', textColor);
            formData.append('bg_color_load', bgColorLoad);
            formData.append('text_color_load', textColorLoad);
            formData.append('settings', activeTab);
            formData.append('posts_per_page', postsPerPage);
            formData.append('current_tab2', currentTab2);
            formData.append('enable_sort_by', enableSortBy);
            formData.append('custom_css', customCSS);

            console.log(formData);
    
            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Settings and shortcode saved successfully.', 'success');
                    window.location.href = `${currentUrl}?page=zwssgr_widget_configurator&tab=tab-shortcode&selectedOption=${selectedOption}&zwssgr_widget_id=${postId}`;
                } else {
                    showNotification('Error: ' + data.data, 'error');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                showNotification('An error occurred while saving data. Details: ' + error, 'error');
            });
            
        }
    });
    
});