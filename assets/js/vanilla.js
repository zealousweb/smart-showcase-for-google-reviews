document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    
    const sliderConfigs = {
        ".zwssgr-slider-1": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-2": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-3": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-4": { slidesPerView: 1, slidesPerGroup: 1 },
        ".zwssgr-slider-5": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-6": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
    };

    Object.keys(sliderConfigs).forEach(selector => {
        const sliderElements = document.querySelectorAll(selector);
    
        if (sliderElements.length > 0) {
            const parentElement = sliderElements[0].parentElement;    
            sliderElements.forEach(sliderElement => {
                const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                const config = sliderConfigs[selector];
                const minSlidesRequired = (config.slidesPerView || 1) + 1;
                const enableLoop = slideCount >= minSlidesRequired;
    
                new Swiper(sliderElement, {
                    slidesPerView: config.slidesPerView,
                    slidesPerGroup: config.slidesPerGroup,
                    spaceBetween: 20,
                    loop: enableLoop,
                    navigation: {
                        nextEl: parentElement.querySelector(".swiper-button-next"),
                        prevEl: parentElement.querySelector(".swiper-button-prev"),
                    },
                    breakpoints: config.breakpoints || {},
                });
            });
        }
    });

    // Store Swiper instances in an object
    let swiperInstances = {};

    function reinitializeAllSwipers(container) {
        // Ensure container is a valid HTML element
        if (!(container instanceof HTMLElement)) {
            console.error(`Invalid container element!`, container);
            return;
        }
    
        // Loop through all configured Swiper sliders
        Object.keys(sliderConfigs).forEach(selector => {
            const sliderElements = container.querySelectorAll(selector); // Get all sliders within the container
    
            sliderElements.forEach(sliderElement => {
                const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                const config = sliderConfigs[selector];
                const minSlidesRequired = (config.slidesPerView || 1) + 1;
                const enableLoop = slideCount >= minSlidesRequired;
    
                // Destroy existing Swiper instance if it exists
                if (swiperInstances[selector]) {
                    swiperInstances[selector].destroy(true, true);
                }
    
                // Initialize new Swiper instance
                swiperInstances[selector] = new Swiper(sliderElement, {
                    slidesPerView: config.slidesPerView,
                    slidesPerGroup: config.slidesPerGroup,
                    spaceBetween: 20,
                    loop: enableLoop,
                    navigation: {
                        nextEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-next"),
                        prevEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-prev"),
                    },
                    breakpoints: config.breakpoints || {},
                });
            });
        });
    }
    

    // Bind click event to open popup
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("zwssgr-total-review")) {
            return;
        }
        let popupItem = e.target.closest(".zwssgr-popup-item");
        if (popupItem) {
            let popupId = popupItem.dataset.popup; // Get the popup ID from the data attribute
            let popup = document.getElementById(popupId);
            if (popup) {
                popup.style.display = "block"; // Show the popup
            }
        }
    });

    // Bind click event to close popup when the close button is clicked
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("zwssgr-close-popup")) {
            let popupOverlay = e.target.closest(".zwssgr-popup-overlay");
            if (popupOverlay) {
                popupOverlay.style.display = "none"; // Hide the popup
            }
        }
    });
    

    // Bind click event to close popup when clicking outside the popup content
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("zwssgr-popup-overlay")) {
            e.target.style.display = "none"; // Hide the popup
        }
    });
 
    // Bind keydown event to close popup when ESC key is pressed
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" || e.keyCode === 27) {
            document.querySelectorAll(".zwssgr-popup-overlay").forEach(function (popup) {
                popup.style.display = "none"; // Hide all popups
            });
        }
    });

    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("copy-shortcode-icon") || e.target.classList.contains("zwssgr-copy-shortcode-icon")) {
            let targetId = e.target.dataset.target;
            let inputElement = document.getElementById(targetId);
    
            if (inputElement) {
                // Copy the input field text using Clipboard API
                navigator.clipboard.writeText(inputElement.value).then(() => {
                    e.target.classList.add("dashicons-yes"); // Change icon to a checkmark
                    setTimeout(() => {
                        e.target.classList.remove("dashicons-yes");
                        e.target.classList.add("dashicons-admin-page"); // Reset icon after 2 seconds
                    }, 2000);
                }).catch(err => {
                    console.error("Failed to copy text: ", err);
                });
            }
        }
    });

    window.zwssgrWidgetPostType = "zwssgr_data_widget";

    // Function to check if an element exists
    function elementExists(selector) {
        return document.querySelector(selector) !== null;
    }

    // Check if we're on the edit, new post, or custom layout page for the widget post type
    if (
        elementExists(".post-type-" + window.zwssgrWidgetPostType) ||
        elementExists(".post-php.post-type-" + window.zwssgrWidgetPostType) ||
        elementExists(".post-new-php.post-type-" + window.zwssgrWidgetPostType) ||
        window.location.href.includes("admin.php?page=zwssgr_widget_configurator")
    ) {
        // Ensure the parent menu (dashboard) is highlighted as active
        let dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
        if (dashboardMenu) {
            dashboardMenu.classList.remove("wp-not-current-submenu");
            dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
        }

        // Ensure the specific submenu item for zwssgr_data_widget is active
        let widgetMenuItem = document.querySelector(
            'ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrWidgetPostType + '"]'
        );
        if (widgetMenuItem) {
            widgetMenuItem.closest("li").classList.add("current");
        }
    }

    window.zwssgrReviewPostType = "zwssgr_reviews";

    // Check if we're on the edit, new post, or custom layout page for the review post type
    if (
        elementExists(".post-type-" + window.zwssgrReviewPostType) ||
        elementExists(".post-php.post-type-" + window.zwssgrReviewPostType) ||
        elementExists(".post-new-php.post-type-" + window.zwssgrReviewPostType) ||
        window.location.href.includes("admin.php?page=zwssgr_review_configurator")
    ) {
        // Ensure the parent menu (dashboard) is highlighted as active
        let dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
        if (dashboardMenu) {
            dashboardMenu.classList.remove("wp-not-current-submenu");
            dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
        }

        // Ensure the specific submenu item for zwssgr_reviews is active
        let reviewMenuItem = document.querySelector(
            'ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrReviewPostType + '"]'
        );
        if (reviewMenuItem) {
            reviewMenuItem.closest("li").classList.add("current");
        }
    }

    // SEO and Notification Email Toggle
    window.zwssgrToggle = document.getElementById('zwssgr_admin_notification_enabled');
    window.zwssgrNotificationFields = document.querySelector('.zwssgr-notification-fields');
    window.zwssgrSubmitButton = document.querySelector('.zwssgr-notification-submit-btn');

    function toggleNotificationFields() {
        if (window.zwssgrToggle.checked) {
            window.zwssgrNotificationFields.style.display = 'block';
            window.zwssgrSubmitButton.classList.remove('zwssgr-disable');
        } else {
            window.zwssgrNotificationFields.style.display = 'none';
            window.zwssgrSubmitButton.classList.add('zwssgr-disable');
        }
    }

    // Initialize the state
    if (window.zwssgrToggle) {
        toggleNotificationFields();
        window.zwssgrToggle.addEventListener('change', toggleNotificationFields);
    }

    // SEO and Notification email validation
    function validateEmail(email) {
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }

    function validateEmails() {
        const emailInput = document.getElementById('zwssgr_admin_notification_emails');
        const emailError = document.getElementById('email-error');
        const emailSuccess = document.getElementById('email-success');
        if (!emailInput) return;

        const emails = emailInput.value.split(',').map(email => email.trim());
        const invalidEmails = emails.filter(email => !validateEmail(email));

        if (invalidEmails.length > 0) {
            emailError.textContent = 'Invalid email(s): ' + invalidEmails.join(', ');
            emailError.style.display = 'block';
            emailSuccess.style.display = 'none';
        } else {
            emailError.style.display = 'none';
        }
    }

    // Add event listeners for email validation
    const emailInput = document.getElementById('zwssgr_admin_notification_emails');
    if (emailInput) {
        emailInput.addEventListener('keypress', validateEmails);
        emailInput.addEventListener('blur', validateEmails);
    }

    // Form submission validation
    const notificationForm = document.getElementById('notification-form');
    if (notificationForm) {
        notificationForm.addEventListener('submit', function (e) {
            const emails = emailInput.value.split(',').map(email => email.trim());
            const invalidEmails = emails.filter(email => !validateEmail(email));
            const emailError = document.getElementById('email-error');
            const emailSuccess = document.getElementById('email-success');

            if (invalidEmails.length > 0) {
                e.preventDefault();
                emailError.textContent = 'Cannot send emails. Invalid email(s): ' + invalidEmails.join(', ');
                emailError.style.display = 'block';
                emailSuccess.style.display = 'none';
            } else {
                emailError.style.display = 'none';
                emailSuccess.textContent = 'Success! Emails are valid and form submitted.';
                emailSuccess.style.display = 'block';
            }
        });
    }

    // Select Layout option functionality
    const radioButtons = document.querySelectorAll('input[name="display_option"]');
    let currentDisplayOption = 'all';

    // Add event listeners to radio buttons for dynamic filtering
    radioButtons.forEach(button => {
        button.addEventListener('change', function () {
            currentDisplayOption = this.value;
            updateOptions(currentDisplayOption);
            saveSelectedOption(currentDisplayOption); // Save the selected display option
        });
    });

    // Function to save the selected display option and layout option via AJAX
    function saveSelectedOption(option) {
        let postId = getQueryParam('zwssgr_widget_id');
        let settings = document.querySelector('.tab-item.active')?.getAttribute('data-tab');
        // let selectedLayout = document.querySelector('.zwssgr-option-item:visible .select-btn.selected')?.dataset.option; // Get selected layout option


        // Select all elements with class 'zwssgr-option-item'
        const optionItems = document.querySelectorAll('.zwssgr-option-item');

        let selectedLayout = null; // Default value

        optionItems.forEach(optionItem => {
            // Check if the element is visible
            if (optionItem.offsetParent !== null) { // offsetParent is null for hidden elements
                const selectedButton = optionItem.querySelector('.select-btn.selected');
                if (selectedButton) {
                    selectedLayout = selectedButton.dataset.option;
                }
            }
        });



        let xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, false); // Make the request synchronous
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        let params = new URLSearchParams({
            action: 'zwssgr_save_widget_data',
            security: my_widget.nonce,
            display_option: option,
            layout_option: selectedLayout,
            post_id: postId,
            settings: settings
        });
        
        xhr.onload = function () {
            if (xhr.status === 200) {
                // console.log('Display and layout option saved:', xhr.responseText);
            } else {
                console.error('Error saving options:', xhr.statusText);
            }
        };
        
        xhr.onerror = function () {
            console.error('Request failed');
        };
        
        xhr.send(params.toString());
    }

    // Function to show/hide options based on the selected radio button
    function updateOptions(value) {
        document.querySelectorAll('.zwssgr-option-item').forEach(item => {
            if (value === 'all' || item.dataset.type === value) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Function to get query parameter by name
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }  

    // Set active tab and selected option from the URL
    window.zwssgrActiveTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
    window.zwssgrSelectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL


    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(function (tab) {
        tab.style.display = 'none';
    });

    // Show the active tab content
    let activeTabElement = document.getElementById(window.zwssgrActiveTab);
    if (activeTabElement) {
        activeTabElement.style.display = 'block';
    }

    // Remove 'active' class from all tab items
    document.querySelectorAll('.tab-item').forEach(function (tabItem) {
        tabItem.classList.remove('active');
    });

    // Add 'active' class to the selected tab item
    let activeTabItem = document.querySelector('.tab-item[data-tab="' + window.zwssgrActiveTab + '"]');
    if (activeTabItem) {
        activeTabItem.classList.add('active');
    }

    // If there's a selected option in the URL and the active tab is 'tab-selected'
    if (window.zwssgrSelectedOption && window.zwssgrActiveTab === 'tab-selected') {
        let selectedOptionElement = document.getElementById(window.zwssgrSelectedOption);
        let selectedOptionDisplay = document.getElementById('selected-option-display');

        if (selectedOptionElement && selectedOptionDisplay) {
            selectedOptionDisplay.innerHTML = ''; // Clear previous content
            selectedOptionDisplay.appendChild(selectedOptionElement); // Move the selected option
            
            // Remove the select button from the moved element
            let selectBtn = selectedOptionDisplay.querySelector('.select-btn');
            if (selectBtn) {
                selectBtn.remove();
            }
        }
    }


    // Handle click events for the tab navigation items
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('tab-item')) {
            let tabId = event.target.getAttribute('data-tab');
            let currentUrl = window.location.href.split('?')[0]; // Get the base URL

            // Get existing query parameters
            let selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists
            let postId = getQueryParam('zwssgr_widget_id'); // Get the post_id from the URL if it exists

            // Start building the new URL with page and tab parameters
            let newUrl = `${currentUrl}?page=zwssgr_widget_configurator&tab=${tabId}`;

            // Add selectedOption to the URL if it exists
            if (selectedOption) {
                newUrl += `&selectedOption=${selectedOption}`;
            }

            // Add post_id to the URL if it exists
            if (postId) {
                newUrl += `&zwssgr_widget_id=${postId}`;
            }

            // Redirect to the new URL
            window.location.href = newUrl;
        }
    });

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
                } else {
                    showNotification('Failed to save layout option.', 'error'); // Show error message
                }
            })
            .catch(() => {
                showNotification('An error occurred.', 'error'); // Show error message
            });

            // Append post_id and selected option to the URL
            window.location.href = `${currentUrl}?page=zwssgr_widget_configurator&tab=tab-selected&selectedOption=${optionId}&zwssgr_widget_id=${postId}`;
        }
    });

    // Handle the Save & Get Code Button
    document.addEventListener('click', function (event) {
        if (event.target.id === 'save-get-code-btn') {
            let selectedOption = getQueryParam('selectedOption');
            let postId = getQueryParam('zwssgr_widget_id');
            let currentUrl = window.location.href.split('?')[0];

            if (!postId) {
                showNotification('Post ID not found!', 'error'); // Custom error notification
                return;
            }

            // Redirect to the "Generated Shortcode" tab with selected option and post_id
            window.location.href = `${currentUrl}?page=zwssgr_widget_configurator&tab=tab-shortcode&selectedOption=${selectedOption}&zwssgr_widget_id=${postId}`;
        }
    });

    // Handle click on visibility toggle icon of Review CPT
    document.addEventListener("click", function (e) {
        let toggleButton = e.target.closest(".zwssgr-toggle-visibility");
        if (!toggleButton) return;

        e.preventDefault();

        let postId = toggleButton.getAttribute("data-post-id");
        let icon = toggleButton.querySelector(".dashicons");

        let formData = new FormData();
        formData.append("action", "toggle_visibility");
        formData.append("post_id", postId);
        formData.append("nonce", zwssgr_admin.nonce);

        let xhr = new XMLHttpRequest();
        xhr.open("POST", zwssgr_admin.ajax_url, true); // Make it asynchronous (true)
        
        xhr.onload = function () {
            if (xhr.status === 200) {
                let response = JSON.parse(xhr.responseText); // Parse JSON manually
                
                if (response.success) {
                    icon.classList.remove("dashicons-hidden", "dashicons-visibility");
                    icon.classList.add("dashicons-" + response.data.icon);

                    // Optionally display the current state
                    let currentState = response.data.state;
                    // console.log("Post visibility is now: " + currentState);
                }
            }
        };

        xhr.send(formData);
    });

    // Listen for changes on the checkbox
    document.addEventListener('change', function (event) {
        if (event.target.id === 'toggle-google-review') {
            // Update button colors based on the color pickers
            let bgColor = document.getElementById('bg-color-picker').value;
            let textColor = document.getElementById('text-color-picker').value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.backgroundColor = bgColor;
                button.style.color = textColor;
            });

            // Toggle button visibility
            toggleButtonVisibility();
        }
    });

    // When the background color picker changes
    document.addEventListener('input', function (event) {
        if (event.target.id === 'bg-color-picker') {
            let bgColor = event.target.value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.backgroundColor = bgColor;
            });
        }
    });

    // When the text color picker changes
    document.addEventListener('input', function (event) {
        if (event.target.id === 'text-color-picker') {
            let textColor = event.target.value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.color = textColor;
            });
        }
    });

    function toggleButtonVisibility() {
        let toggleCheckbox = document.getElementById('toggle-google-review');
        let buttons = document.querySelectorAll('.zwssgr-google-toggle');

        buttons.forEach(button => {
            button.style.display = toggleCheckbox.checked ? 'inline-block' : 'none';
        });
    }

    // Run the function when the page loads
    toggleButtonVisibility();

    // Run the function whenever the checkbox state changes
    let toggleCheckbox = document.getElementById('toggle-google-review');
    if (toggleCheckbox) {
        toggleCheckbox.addEventListener('change', toggleButtonVisibility);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get("page");
    const tab = urlParams.get("tab");
    
    function toggleElements() {
        const elements = [
            { checkbox: "review-title", target: ".zwssgr-title" },
            { checkbox: "review-rating", target: ".zwssgr-rating" },
            { checkbox: "review-days-ago", target: ".zwssgr-days-ago" },
            { checkbox: "review-content", target: ".zwssgr-content" },
            { checkbox: "review-photo", target: ".zwssgr-profile" },
            { checkbox: "review-g-icon", target: ".zwssgr-google-icon" }
        ];
    
        elements.forEach(({ checkbox, target }) => {
            const checkboxElement = document.getElementById(checkbox);
            const targetElements = document.querySelectorAll(target);
    
            if (checkboxElement && targetElements.length) {
                const shouldShow = !checkboxElement.checked;
                targetElements.forEach(el => el.style.display = shouldShow ? 'block' : 'none');
            }
        });
    }

    // Attach change event listeners to checkboxes
    document.querySelectorAll('input[name="review-element"]').forEach(checkbox => {
        checkbox.addEventListener('change', toggleElements);
    });

    // Call toggleElements on page load to apply any initial settings with fade effect
    if (page === "zwssgr_widget_configurator" && tab === "tab-selected") {
        // console.log("Condition met, calling toggleElements...");
        toggleElements();
    }
    
    

    function formatDate(dateString, format, lang) {
		let dateParts;
		let date;
	
		// Check for various formats and parse accordingly
		if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
			dateParts = dateString.split('/');
			date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]); // DD/MM/YYYY
		} else if (/^\d{2}-\d{2}-\d{4}$/.test(dateString)) {
			dateParts = dateString.split('-');
			date = new Date(dateParts[2], dateParts[0] - 1, dateParts[1]); // MM-DD-YYYY
		} else if (/^\d{4}\/\d{2}\/\d{2}$/.test(dateString)) {
			dateParts = dateString.split('/');
			date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // YYYY/MM/DD
		} else {
			date = new Date(dateString); // ISO or fallback
		}
	
		// Return original if date is invalid
		if (isNaN(date.getTime())) return dateString;
	
		// Format date based on selected format and language
		const options = { year: 'numeric', month: 'long', day: 'numeric' };
		switch (format) {
			case 'DD/MM/YYYY':
				return date.toLocaleDateString('en-GB'); // e.g., 01/01/2024
			case 'MM-DD-YYYY':
				return date.toLocaleDateString('en-US').replace(/\//g, '-'); // e.g., 01-01-2024
			case 'YYYY/MM/DD':
				return date.toISOString().split('T')[0].replace(/-/g, '/'); // e.g., 2024/01/01
			case 'full':
				return date.toLocaleDateString(lang, options); // January 1, 2024 in selected language
			default:
				return dateString;
		}
	}

    function updateDisplayedDates() {
        const lang = document.getElementById("language-select").value; // Get selected language
        const format = document.getElementById("date-format-select").value; // Get selected date format

        document.querySelectorAll(".date").forEach(function (element) {
            const originalDate = element.getAttribute("data-original-date"); // Get the original date
            if (format === "hide") {
                element.textContent = ""; // Hide the date
            } else {
                const formattedDate = formatDate(originalDate, format, lang); // Pass lang to formatDate
                element.textContent = formattedDate; // Update the text with the formatted date
            }
        });
    }

    document.body.addEventListener("change", function (event) {
        if (event.target && event.target.id === "date-format-select") {
            updateDisplayedDates();
        }
    });
    
    
    window.zwssgrTranslations = {
        en: 'Read more',
        es: 'Leer más',
        fr: 'Lire la suite',
        de: 'Mehr lesen',
        it: 'Leggi di più',
        pt: 'Leia mais',
        ru: 'Читать дальше',
        zh: '阅读更多',
        ja: '続きを読む',
        hi: 'और पढ़ें',
        ar: 'اقرأ أكثر',
        ko: '더 읽기',
        tr: 'Daha fazla oku',
        bn: 'আরও পড়ুন',
        ms: 'Baca lagi',
        nl: 'Lees verder',
        pl: 'Czytaj więcej',
        sv: 'Läs mer',
        th: 'อ่านเพิ่มเติม'
    };
    
    // Function to update Read more link based on language
    function updateReadMoreLink(element, lang) {
        let charLimit = parseInt(document.getElementById('review-char-limit').value, 10); // Get character limit
        let fullText = element.getAttribute('data-full-text'); // Get the stored full text
    
        if (charLimit && fullText.length > charLimit) {
            let trimmedText = fullText.substring(0, charLimit) + '... ';
            element.innerHTML = trimmedText + `<a href="javascript:void(0);" class="read-more-link">${window.zwssgrTranslations[lang]}</a>`;
        } else {
            element.textContent = fullText; // Show full text if no limit
        }
    }
    
    // Event delegation for "Read more" click
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('read-more-link')) {
            event.preventDefault();
            let parent = event.target.parentElement;
            if (parent) {
                let fullText = parent.getAttribute('data-full-text');
                if (fullText) {
                    parent.textContent = fullText;
                }
            }
        }
    });
    
    // On character limit input change
    document.body.addEventListener('input', function (event) {
        if (event.target && event.target.id === 'review-char-limit') {
            let charLimit = parseInt(event.target.value, 10);
            let lang = document.getElementById('language-select').value;
            let errorContainer = document.getElementById('char-limit-error');
            errorContainer.textContent = '';
    
            if (charLimit < 1 || isNaN(charLimit)) {
                if (event.target.value.trim() === '') {
                    document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                        let fullText = element.getAttribute('data-full-text') || element.textContent;
                        element.textContent = fullText;
                    });
                } else {
                    errorContainer.textContent = 'Character limit must be 1 or greater.';
                    event.target.value = '';
                }
                return;
            }
    
            document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                let fullText = element.getAttribute('data-full-text') || element.textContent;
                if (!element.getAttribute('data-full-text')) {
                    element.setAttribute('data-full-text', fullText);
                }
                updateReadMoreLink(element, lang);
            });
        }
    });
    
    
    // Function to update displayed dates based on selected language and format
    function updateDisplayedDates() {
        const lang = document.getElementById('language-select').value;
        const format = document.getElementById('date-format-select').value;
    
        document.querySelectorAll('.zwssgr-date').forEach(function (element) {
            const originalDate = element.getAttribute('data-original-date');
            if (format === 'hide') {
                element.textContent = '';
            } else {
                const formattedDate = formatDate(originalDate, format, lang);
                element.textContent = formattedDate;
            }
        });
    }

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'language-select') {
            let lang = event.target.value;
            updateDisplayedDates();
        
            document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                let fullText = element.getAttribute('data-full-text') || element.textContent;
                if (!element.getAttribute('data-full-text')) {
                    element.setAttribute('data-full-text', fullText);
                }
                updateReadMoreLink(element, lang);
            });
        }
    });
    
    
    // On date format select change
    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'date-format-select') {
            updateDisplayedDates();
        }
    });    

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'toggle-google-review') {
            const colorPickerOptions = document.getElementById('color-picker-options');
            if (event.target.checked) {
                colorPickerOptions.style.display = 'flex';
                fadeIn(colorPickerOptions);
            } else {
                fadeOut(colorPickerOptions);
            }
        }
    });

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'enable-load-more') {
            const loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');
            if (event.target.checked) {
                loadMoreOptions.style.display = 'flex';
                fadeIn(loadMoreOptions);
            } else {
                fadeOut(loadMoreOptions);
            }
        }
    });
    
   
    const enableLoadMore = document.getElementById('enable-load-more');
    const loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');

    if (enableLoadMore && loadMoreOptions) {  // Ensure elements exist
        if (enableLoadMore.checked) {
            loadMoreOptions.style.display = 'flex';
        } else {
            loadMoreOptions.style.display = 'none';
        }
    }
    

    // Fade In function
    function fadeIn(element) {
        element.style.opacity = 0;
        element.style.display = 'flex';
        let opacity = 0;
        const interval = setInterval(function () {
            if (opacity < 1) {
                opacity += 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(interval);
            }
        }, 30);
    }

    // Fade Out function
    function fadeOut(element) {
        let opacity = 1;
        const interval = setInterval(function () {
            if (opacity > 0) {
                opacity -= 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(interval);
                element.style.display = 'none';
            }
        }, 30);
    }

    const updateInputField = () => {
        const keywords = [];
        document.querySelectorAll('#keywords-list .keyword-item').forEach(item => {
            keywords.push(item.textContent.trim().replace(' ✖', ''));
        });

        const hiddenInput = document.getElementById('keywords-input-hidden');
        if (hiddenInput) { // Ensure the element exists before modifying it
            hiddenInput.value = keywords.join(', ');
        }
    };

    updateInputField(); // Call the function after DOM is ready
    
    // Function to handle adding new keywords
    const handleAddKeywords = (inputValue) => {
        // Get the input value and split it into keywords
        const newKeywords = inputValue.split(',').map(keyword => keyword.trim()).filter(keyword => keyword);
    
        // Get the current number of keywords in the list
        const currentKeywordsCount = document.querySelectorAll('#keywords-list .keyword-item').length;
    
        // Check if adding new keywords exceeds the limit of 5
        if (currentKeywordsCount + newKeywords.length > 5) {
            document.getElementById('error-message').style.display = 'block'; // Show the error message
            return; // Stop further execution
        } else {
            document.getElementById('error-message').style.display = 'none'; // Hide the error message if under limit
        }
    
        document.getElementById('keywords-input').value = ''; // Clear input field
    
        newKeywords.forEach(keyword => {
            // Check if the keyword is already in the list
            const existingKeywords = Array.from(document.querySelectorAll('#keywords-list .keyword-item')).map(item => item.textContent.trim().replace(' ✖', ''));
            if (!existingKeywords.includes(keyword)) {
                // Create a new keyword item
                const keywordItem = document.createElement('div');
                keywordItem.classList.add('keyword-item');
                keywordItem.textContent = keyword;
    
                // Create remove button
                const removeButton = document.createElement('span');
                removeButton.classList.add('remove-keyword');
                removeButton.textContent = ' ✖';
    
                // Append remove button to the keyword item
                keywordItem.appendChild(removeButton);
    
                // Append the keyword item to the keywords list
                document.getElementById('keywords-list').appendChild(keywordItem);
    
                // Update hidden input field
                updateInputField();
    
                // Set up click event to remove keyword
                removeButton.addEventListener('click', function () {
                    keywordItem.remove(); // Remove keyword from list
                    updateInputField(); // Update input field after removal
                });
            }
        });
    };

    document.body.addEventListener('keypress', function (event) {
        if (event.target && event.target.id === 'keywords-input') {
            if (event.key === 'Enter') { // Check for Enter key
                event.preventDefault(); // Prevent default form submission
                handleAddKeywords(event.target.value);
            }
        }
    });

    document.body.addEventListener('blur', function (event) {
        if (event.target && event.target.id === 'keywords-input') {
            handleAddKeywords(event.target.value);
        }
    }, true); // Use the "true" parameter to capture the event during the capturing phase

    
    // Function to set up click event to remove existing keywords (on page load and dynamically)
    const keywordsList = document.getElementById('keywords-list');
    if (keywordsList) {
        keywordsList.addEventListener('click', function (e) {
            document.getElementById('keywords-list').addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.remove-keyword'); // Find the closest remove button
                if (removeBtn) {
                    removeBtn.parentElement.remove(); // Remove the keyword item
                    updateInputField(); // Update the hidden input after removal
                }
            });
        });
    }

    document.body.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'save-get-code-btn') {
            e.preventDefault();
    
            function getQueryParam(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }
    
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
    
            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Settings and shortcode saved successfully.', 'success');
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


    document.querySelectorAll('.star-filter').forEach(star => {
        star.addEventListener('click', function () {
            let rating = parseInt(this.dataset.rating, 10);
            let allStars = document.querySelectorAll('.star-filter');

            if (this.classList.contains('selected') && rating === 1) {
                allStars.forEach(star => {
                    star.classList.remove('selected');
                    star.querySelector('.star').style.fill = '#ccc';
                });
                return;
            }

            allStars.forEach(star => {
                let currentRating = parseInt(star.dataset.rating, 10);
                if (currentRating <= rating) {
                    star.classList.add('selected');
                    star.querySelector('.star').style.fill = '#f39c12';
                } else {
                    star.classList.remove('selected');
                    star.querySelector('.star').style.fill = '#ccc';
                }
            });
        });
    });


    document.addEventListener('click', function (event) {
        // Check if the clicked element is #sort-by-select or has the .star-filter class inside .filter-rating
        if (event.target.matches('#sort-by-select') || event.target.closest('.filter-rating .star-filter')) {
    
            let nonce = zwssgr_filter_reviews.nonce;
            let postId = getQueryParam('zwssgr_widget_id');
            let sortByElement = document.querySelector('#sort-by-select');
            let sortBy = sortByElement ? sortByElement.value : ''; // Get selected sort by value
            let selectedOption = getQueryParam('selectedOption');
    
            let selectedRatings = [];
            document.querySelectorAll('.filter-rating .star-filter.selected').forEach(function(star) {
                selectedRatings.push(star.getAttribute('data-rating')); // Push each selected rating into the array
            });

            // Convert ratings to numbers
            selectedRatings = selectedRatings.map(Number);

            // If nothing is selected, default to all ratings (1-5 stars)
            if (selectedRatings.length === 1) {
                selectedRatings = [1];
            } else if (selectedRatings.length === 2) {
                selectedRatings = [2];
            } else if (selectedRatings.length === 3) {
                selectedRatings = [3];
            } else if (selectedRatings.length === 4) {
                selectedRatings = [4];
            } else if (selectedRatings.length === 5) {
                selectedRatings = [5];
            } else {
                selectedRatings = [1, 2, 3, 4, 5];
            }
    
            // Create form data
            let formData = new FormData();
            formData.append('action', 'zwssgr_filter_reviews'); // The action for the PHP handler
            formData.append('zwssgr_widget_id', postId);
            formData.append('rating_filter', JSON.stringify(selectedRatings)); // Pass the selected ratings array as JSON
            formData.append('sort_by', sortBy); // Pass sort by parameter
            formData.append('nonce', nonce);
    
            // Make the AJAX request to filter reviews based on selected ratings
            let xhr = new XMLHttpRequest();
            xhr.open('POST', zwssgr_filter_reviews.ajax_url, true);
    
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 400) {
                    let response = xhr.responseText;
    
                    // Ensure the response is HTML or clean content
                    if (typeof response === "string" || response instanceof String) {
                        let displayElement = document.querySelector('#selected-option-display');
                        if (displayElement) {
                            // console.log("Updating #selected-option-display");
                            displayElement.innerHTML = response;
                        } else {
                            console.error("#selected-option-display element not found!");
                        }
                    } else {
                        console.error("Expected HTML content, but received:", response);
                    }
    
                    // Only reinitialize Slick slider if selectedOption is one of the slider options
                    if (['slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6'].includes(selectedOption)) {
                        setTimeout(function () {
                            reinitializeAllSwipers(document.querySelector('#selected-option-display'));
                        }, 100);
                    }
                    toggleElements();
    
                    // Handle list layout reinitialization (if needed)
                    if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5'].includes(selectedOption)) {
                        // console.log("List layout filtered");
                    }
                } else {
                    console.error("AJAX Error: ", xhr.statusText, "Status Code:", xhr.status);
                }
            };
    
            xhr.onerror = function () {
                console.error("AJAX request failed");
            };
    
            xhr.send(formData);
        }
    });
    
    // Start code SMTP
    function zwssgr_update_Smtp_Port() {
        let encryptionType = document.querySelector('input[name="zwssgr_smtp_ency_type"]:checked')?.value;
        const portInput = document.getElementById('zwssgr-smtp-port');

        if (!portInput) return; // Prevent error if element doesn't exist

        switch (encryptionType) {
            case 'none':
                portInput.value = '25'; // Set port to 25 for 'None'
                break;
            case 'ssl':
                portInput.value = '465'; // Set port to 465 for 'SSL'
                break;
            case 'tls':
                portInput.value = '587'; // Set port to 587 for 'TLS'
                break;
            default:
                portInput.value = '25'; // Default port
        }
    }

    // Attach event listeners for SMTP encryption type
    document.querySelectorAll('input[name="zwssgr_smtp_ency_type"]').forEach(input => {
        input.addEventListener('change', zwssgr_update_Smtp_Port);
    });

    // Function to toggle SMTP authentication fields
    function toggleSmtpAuth() {
        const smtpAuth = document.querySelector('input[name="zwssgr_smtp_auth"]:checked');
        const zwssgrSmtprows = document.querySelectorAll('tr.zwssgr-smtp-auth-enable-main');
        const usernameField = document.querySelector('input[name="zwssgr_smtp_username"]');
        const passwordField = document.querySelector('input[name="zwssgr_smtp_password"]');

        if (!smtpAuth || !usernameField || !passwordField) return; // Prevent error if elements don't exist

        if (smtpAuth.value === 'no') {
            zwssgrSmtprows.forEach(row => row.style.display = 'none');
            usernameField.removeAttribute('required');
            passwordField.removeAttribute('required');
        } else {
            zwssgrSmtprows.forEach(row => row.style.display = 'table-row');
            usernameField.setAttribute('required', 'required');
            passwordField.setAttribute('required', 'required');
        }
    }

    // Attach event listeners for SMTP authentication
    document.querySelectorAll('input[name="zwssgr_smtp_auth"]').forEach(input => {
        input.addEventListener('change', toggleSmtpAuth);
    });

    // Function to toggle Admin SMTP settings
    function toggleAdminSmtp() {
        const adminSmtpEnabled = document.querySelector('input[name="zwssgr_admin_smtp_enabled"]');
        const adminSmtpFields = document.querySelectorAll('.zwssgr-admin-enable-smtp');
        const requiredFields = [
            'zwssgr_smtp_username',
            'zwssgr_smtp_password',
            'zwssgr_from_email',
            'zwssgr_smtp_host'
        ];

        if (!adminSmtpEnabled) return; // Prevent error if element doesn't exist

        if (adminSmtpEnabled.checked) {
            adminSmtpFields.forEach(el => el.style.display = 'contents');
            requiredFields.forEach(field => {
                const input = document.querySelector(`input[name="${field}"]`);
                if (input) input.setAttribute('required', 'required'); // Check if input exists
            });
        } else {
            adminSmtpFields.forEach(el => el.style.display = 'none');
            requiredFields.forEach(field => {
                const input = document.querySelector(`input[name="${field}"]`);
                if (input) input.removeAttribute('required'); // Check if input exists
            });
        }
    }

    // Attach event listener for Admin SMTP toggle
    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'zwssgr_admin_smtp_enabled') {
            toggleAdminSmtp();
        }
    });

    if (document.querySelector('input[name="zwssgr_admin_smtp_enabled"]')) {
        toggleAdminSmtp();
    }
    if (document.querySelector('input[name="zwssgr_smtp_auth"]')) {
        toggleSmtpAuth();
    }
    // End code SMTP


    // Deactivation Popup 
    document.addEventListener('click', function (event) {
        const target = event.target.closest('a[href*="deactivate"][href*="smart-showcase-for-google-reviews"]');
        
        if (target) {
            event.preventDefault(); // Prevent default action
            const deactivateUrl = target.getAttribute('href'); // Get the deactivation URL from the link
            
            // Show the deactivation confirmation popup
            document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'block';
    
            // Cancel Deactivation
            document.getElementById('zwssgr-plugin-cancel-deactivate').addEventListener('click', function () {
                document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
            }, { once: true });
    
            // Confirm Deactivation
            document.getElementById('zwssgr-plugin-confirm-deactivate').addEventListener('click', function () {
                const deletePluginDataCheckbox = document.getElementById('zwssgr-delete-plugin-data');
                const zwssgrDeletePluginData = deletePluginDataCheckbox.checked ? 1 : 0;
                if (zwssgrDeletePluginData) {
                    if (!zwssgr_admin.ajax_url || !zwssgr_admin.zwssgr_delete_oauth_connection) {
                        console.error("AJAX URL or security nonce is missing!");
                        return;
                    }
        
                    const formData = new FormData();
                    formData.append("action", "zwssgr_delete_oauth_connection");
                    formData.append("zwssgr_delete_plugin_data", zwssgrDeletePluginData);
                    formData.append("security", zwssgr_admin.zwssgr_delete_oauth_connection);
        
                    fetch(zwssgr_admin.ajax_url, {
                        method: "POST",
                        body: formData
                    }).finally(() => {
                        // Proceed to deactivate the plugin after AJAX completes
                        document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
                        window.location.href = deactivateUrl;
                    });
                }else{
                    // If checkbox is not checked, directly deactivate the plugin
                    document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
                    window.location.href = deactivateUrl;
                }
            }, { once: true });
        }
    });

});
