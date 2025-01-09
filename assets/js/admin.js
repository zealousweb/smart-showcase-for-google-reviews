"use strict";
jQuery(document).ready(function($) {

	// Bind click event to open popup
	$(document).on('click', '.zwssgr-popup-item', function (e) {
		if ($(e.target).hasClass('zwssgr-total-review')) {
			return;
		}
		let popupId = $(this).data('popup'); // Get the popup ID from the data attribute
		$('#' + popupId).stop(true, true).fadeIn(); // Show the popup
	});

	// Bind click event to close popup when the close button is clicked
	$(document).on('click', '.zwssgr-close-popup', function () {
		$(this).closest('.zwssgr-popup-overlay').fadeOut(); // Hide the popup
	});

	// Bind click event to close popup when clicking outside the popup content
	$(document).on('click', '.zwssgr-popup-overlay', function (e) {
		if ($(e.target).is('.zwssgr-popup-overlay')) {
			$(this).stop(true, true).fadeOut(); // Hide the popup
		}
	});

	// Bind keydown event to close popup when ESC key is pressed
	$(document).on('keydown', function (e) {
		if (e.key === "Escape" || e.keyCode === 27) {
			$('.zwssgr-popup-overlay').stop(true, true).fadeOut(); // Hide the popup
		}
	});

	$(document).on('click', '.copy-shortcode-icon, .zwssgr-copy-shortcode-icon', function () {
		let targetId = $(this).data('target');
		let $input = $('#' + targetId);
	
		if ($input.length) {
			// Copy the input field text using Clipboard API
			navigator.clipboard.writeText($input.val()).then(() => {
				$(this).addClass('dashicons-yes'); // Change icon to a checkmark
				setTimeout(() => {
					$(this).removeClass('dashicons-yes').addClass('dashicons-admin-page'); // Reset icon after 2 seconds
				}, 2000);
			}).catch(err => {
				console.error('Failed to copy text: ', err);
			});
		}
	});
	
	var zwssgrWidgetPostType = 'zwssgr_data_widget';

    // Check if we're on the edit, new post, or the custom layout page for the widget post type
    if ($('.post-type-' + zwssgrWidgetPostType).length || 
        $('.post-php.post-type-' + zwssgrWidgetPostType).length || 
        $('.post-new-php.post-type-' + zwssgrWidgetPostType).length || 
        window.location.href.indexOf('admin.php?page=zwssgr_widget_configurator') !== -1) {

        // Ensure the parent menu (dashboard) is highlighted as active
        $('.toplevel_page_zwssgr_dashboard')
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-current-submenu wp-menu-open');

        // Ensure the specific submenu item for zwssgr_data_widget is active
        $('ul.wp-submenu li a[href="edit.php?post_type=' + zwssgrWidgetPostType + '"]')
            .parent('li')
            .addClass('current');
    }

	var zwssgrReviewPostType = 'zwssgr_reviews';

	// Check if we're on the edit, new post, or the custom layout page for the review post type
	if ($('.post-type-' + zwssgrReviewPostType).length || 
	$('.post-php.post-type-' + zwssgrReviewPostType).length || 
	$('.post-new-php.post-type-' + zwssgrReviewPostType).length || 
	window.location.href.indexOf('admin.php?page=zwssgr_review_configurator') !== -1) {

	// Ensure the parent menu (dashboard) is highlighted as active
	$('.toplevel_page_zwssgr_dashboard')
		.removeClass('wp-not-current-submenu')
		.addClass('wp-has-current-submenu wp-menu-open');

	// Ensure the specific submenu item for zwssgr_reviews is active
	$('ul.wp-submenu li a[href="edit.php?post_type=' + zwssgrReviewPostType + '"]')
		.parent('li')
		.addClass('current');
	}

	//SEO and Notification Email Toggle 
	var zwssgrToggle = $('#zwssgr_admin_notification_enabled');
	var zwssgrNotificationFields = $('.zwssgr-notification-fields');
	var zwssgrSubmitButton = $('.zwssgr-notification-submit-btn'); 
	if (zwssgrToggle.is(':checked')) {
		zwssgrNotificationFields.show();
		zwssgrSubmitButton.removeClass('zwssgr-disable');
	} else {
		zwssgrNotificationFields.hide();
		zwssgrSubmitButton.addClass('zwssgr-disable');
	}
	zwssgrToggle.on('change', function () {
		if ($(this).is(':checked')) {
			zwssgrNotificationFields.show();
			zwssgrSubmitButton.removeClass('zwssgr-disable');
		} else {
			zwssgrNotificationFields.hide();
			zwssgrSubmitButton.addClass('zwssgr-disable');
		}
	});


	// SEO and Notification email vaildation
	function validateEmail(email) {
		let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
		return emailPattern.test(email);
	}

	// Function to validate emails and show messages
	function validateEmails() {
		let emails = $('#zwssgr_admin_notification_emails').val().split(',');
		let invalidEmails = [];
		emails.forEach(function(email) {
			email = email.trim(); // Clean the email address
			if (!validateEmail(email)) {
				invalidEmails.push(email);
			}
		});

		// Show error message if any email is invalid
		if (invalidEmails.length > 0) {
			$('#email-error').text('Invalid email(s): ' + invalidEmails.join(', ')).show();
			$('#email-success').hide(); // Hide success message
		} else {
			$('#email-error').hide(); // Hide error message if all emails are valid
		}
	}

	// On keypress in the email field
	$(document).on('keypress', '#zwssgr_admin_notification_emails', function() {
		validateEmails();
	});

	// On blur (when the user leaves the email field)
	$(document).on('blur', '#zwssgr_admin_notification_emails', function() {
		validateEmails();
	});

	// On form submission, check if all emails are valid
	$(document).on('submit', '#notification-form', function(e) {
		let emails = $('#zwssgr_admin_notification_emails').val().split(',');
		let invalidEmails = [];
		emails.forEach(function(email) {
			email = email.trim();
			if (!validateEmail(email)) {
				invalidEmails.push(email);
			}
		});

		// If there are invalid emails, prevent form submission and show error message
		if (invalidEmails.length > 0) {
			e.preventDefault();
			$('#email-error').text('Cannot send emails. Invalid email(s): ' + invalidEmails.join(', ')).show();
			$('#email-success').hide(); // Hide success message
		} else {
			// If all emails are valid, show success message and allow form submission
			$('#email-error').hide(); // Hide error message
			$('#email-success').text('Success! Emails are valid and form submitted.').show(); // Show success message
		}
	});

	// Select Layout option functionality
	const radioButtons = $('input[name="display_option"]');
	let currentDisplayOption = 'all';

	// Add event listeners to radio buttons for dynamic filtering
	radioButtons.change(function () {
		currentDisplayOption = $(this).val();
		updateOptions(currentDisplayOption);
		saveSelectedOption(currentDisplayOption); // Save the selected display option
	});

	// Function to save the selected display option and layout option via AJAX
	function saveSelectedOption(option) {
		let postId = getQueryParam('zwssgr_widget_id');
		let settings = $('.tab-item.active').attr('data-tab');
		let selectedLayout = $('.zwssgr-option-item:visible .select-btn.selected').data('option'); // Get selected layout option

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			async: false,  // Make the request synchronous
			data: {
				action: 'save_widget_data',
				security: my_widget.nonce,
				display_option: option,
				layout_option: selectedLayout, // Send selected layout
				post_id: postId,
				settings: settings
			},
			success: function(response) {
				// console.log('Display and layout option saved:', response);
			},
			error: function(error) {
				console.error('Error saving options:', error);
			}
		});
	}

	// Function to show/hide options based on the selected radio button
	function updateOptions(value) {
		$('.zwssgr-option-item').each(function () {
			if (value === 'all' || $(this).data('type') === value) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
				
	// Function to get query parameter by name
	function getQueryParam(param) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(param);
	}

	// Get the active tab and selected option from the URL
	var zwssgrActiveTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
	var zwssgrSelectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL

	// Initially show the active tab content
	$('.tab-content').hide(); // Hide all tab content
	$('#' + zwssgrActiveTab).show(); // Show the active tab content

	$('.tab-item').removeClass('active');
	$('.tab-item[data-tab="' + zwssgrActiveTab + '"]').addClass('active');

	// If there's a selected option in the URL, display it in the "Selected Option" tab
	if (zwssgrSelectedOption && zwssgrActiveTab === 'tab-selected') {
		let selectedOptionElement = $('#' + zwssgrSelectedOption);
		$('#selected-option-display').html(selectedOptionElement);
		$('#selected-option-display').find('.select-btn').remove();

		// Reinitialize Slick slider after the DOM has been updated
		setTimeout(function() {
			reinitializeSlickSlider($('#selected-option-display'));
		}, 100);
	}
	
	// Handle click events for the tab navigation items
	$(document).on('click', '.tab-item', function() {
		let tabId = $(this).data('tab');
		let currentUrl = window.location.href.split('?')[0]; // Get the base URL

		// Get existing query parameters
		let selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists
		let postId = getQueryParam('zwssgr_widget_id'); // Get the post_id from the URL if it exists

		// Start building the new URL with page and tab parameters
		let newUrl = currentUrl + '?page=zwssgr_widget_configurator&tab=' + tabId;

		// Add selectedOption to the URL if it exists
		if (selectedOption) {
			newUrl += '&selectedOption=' + selectedOption;
		}

		// Add post_id to the URL if it exists
		if (postId) {
			newUrl += '&zwssgr_widget_id=' + postId;
		}

		// Redirect to the new URL
		window.location.href = newUrl;
	});

	// Function to show custom notifications
	function showNotification(message, type) {
		// Define the notification types: success, error, warning, info
		let notificationClass = 'zwssgr-notice-' + type; // Example: zwssgr-notice-success, zwssgr-notice-error

		// Create the notification HTML
		let notification = `
			<div class="zwssgr-notice ${notificationClass} zwssgr-is-dismissible">
				<p>${message}</p>
			</div>
		`;

		// Append the notification to the target area
		$('.zwssgr-dashboard').prepend(notification);

		// Add click event for the dismiss button
		$('.zwssgr-notice.zwssgr-is-dismissible').on('click', '.zwssgr-notice-dismiss', function () {
			$(this).closest('.zwssgr-notice').fadeOut(function () {
				$(this).remove();
			});
		});
	}

	// Handle click events for "Select Option" buttons
    $(document).on('click', '.select-btn', function() {
        let optionId = $(this).data('option');
        let postId = getQueryParam('zwssgr_widget_id');
        let currentUrl = window.location.href.split('?')[0];


        if (!postId) {
			showNotification('Post ID not found!', 'error'); // Custom error notification
			return;
		}

		// Fetch the HTML for the selected option using the correct optionId
		let selectedOptionElement = $('#' + optionId); // Clone the selected option's element
		$('#selected-option-display').html(selectedOptionElement); // Update the display area
		$('#selected-option-display').find('.select-btn').remove(); // Remove the select button from the cloned HTML

		// Get the current display option (assuming you have a variable for this)
		let displayOption = $('input[name="display_option"]:checked').val(); // Or adjust according to your setup
		let settings = $('.tab-item.active').attr('data-tab');
		let currentTab = $('.tab-item.active').data('tab'); // Get the current active tab

		$.ajax({
			url: ajaxurl,  // This is the WordPress AJAX URL
			type: 'POST',
			async: false,  // Make the request synchronous
			data: {
				action: 'save_widget_data',
				security: my_widget.nonce,
				layout_option: optionId,
				display_option: displayOption, // The selected display option
				post_id: postId   ,// The post ID
				settings: settings,
				current_tab: currentTab // Include current tab status
			},
			success: function(response) {
				if (response.success) {
					showNotification('Layout option saved successfully!', 'success'); // Show success message
				} else {
					showNotification('Failed to save layout option.', 'error'); // Show error message
				}
			},
			error: function() {
				showNotification('An error occurred.', 'error'); // Show error message
			}
		});

        // Append post_id and selected option to the URL
        window.location.href = currentUrl + '?page=zwssgr_widget_configurator&tab=tab-selected&selectedOption=' + optionId + '&zwssgr_widget_id=' + postId;
    });

    // Handle the Save & Get Code Button
    $(document).on('click', '#save-get-code-btn', function() {
        let selectedOption = getQueryParam('selectedOption');
        let postId = getQueryParam('zwssgr_widget_id');
        let currentUrl = window.location.href.split('?')[0];


        if (!postId) {
			showNotification('Post ID not found!', 'error'); // Custom error notification
			return;
		}

        // Redirect to the "Generated Shortcode" tab with selected option and post_id
        window.location.href = currentUrl + '?page=zwssgr_widget_configurator&tab=tab-shortcode&selectedOption=' + selectedOption + '&zwssgr_widget_id=' + postId;
    });

	// Function to reinitialize the selected Slick Slider
	function reinitializeSlickSlider(container) {
		// Find all sliders within the container
		let sliders = $(container).find('[class^="zwssgr-slider-"]');
	
		// Slider configurations based on slider types
		let sliderConfigs = {
			'zwssgr-slider-1': {
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: { slidesToShow: 2, slidesToScroll: 2 }
					},
					{
						breakpoint: 480,
						settings: { slidesToShow: 1, slidesToScroll: 1 }
					}
				]
			},
			'zwssgr-slider-2': {
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: { slidesToShow: 2, slidesToScroll: 2 }
					},
					{
						breakpoint: 480,
						settings: { slidesToShow: 1, slidesToScroll: 1 }
					}
				]
			},
			'zwssgr-slider-3': {
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 2,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1180,
						settings: { slidesToShow: 1, slidesToScroll: 1 }
					}
				]
			},
			'zwssgr-slider-4': {
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: true,
				dots: false
			},
			'zwssgr-slider-5': {
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 2,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 480,
						settings: { slidesToShow: 1, slidesToScroll: 1 }
					}
				]
			},
			'zwssgr-slider-6': {
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 3,
				arrows: true,
				dots: false,
				responsive: [
					{
						breakpoint: 1200,
						settings: { slidesToShow: 2, slidesToScroll: 2 }
					},
					{
						breakpoint: 480,
						settings: { slidesToShow: 1, slidesToScroll: 1 }
					}
				]
			}
		};
	
		// Iterate through each slider and reinitialize
		sliders.each(function () {
			let slider = $(this);
	
			// Unslick if already initialized
			if (slider.hasClass('slick-initialized')) {
				slider.slick('unslick');
			}
	
			// Get slider-specific settings
			let sliderClass = slider.attr('class').split(' ').find(cls => cls.startsWith('zwssgr-slider-'));
			let config = sliderConfigs[sliderClass];
	
			// Initialize Slick with the configuration
			if (config) {
				slider.slick(config);
			}
		});
	}
	

	// Slick sliders
	$('.zwssgr-slider-1').slick({
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
	
	$('.zwssgr-slider-2').slick({
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

	$('.zwssgr-slider-3').slick({
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

	$('.zwssgr-slider-4').slick({
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		dots: false,
	});	

	$('.zwssgr-slider-5').slick({
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

	$('.zwssgr-slider-6').slick({
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

	// Handle click on visibility toggle icon of Review CPT
	$(document).on('click', '.zwssgr-toggle-visibility', function(e) {
		e.preventDefault();

		let postId = $(this).data('post-id');
		let $icon = $(this).find('.dashicons');

		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: 'POST',
			async: false,  // Make the request synchronous
			dataType: 'json',
			data: {
				action: 'toggle_visibility',
				post_id: postId,
				nonce: zwssgr_admin.nonce
			},
			success: function(response) {
				if (response.success) {
					// Update icon based on the response
					$icon.removeClass('dashicons-hidden dashicons-visibility').addClass('dashicons-' + response.data.icon);

					// Optionally display the current state somewhere on the page
					let currentState = response.data.state;
					// console.log("Post visibility is now: " + currentState); 	
				}
			}
		});
	});

	$(document).on('change', '#toggle-google-review', function() {
        // Update button colors based on the color pickers
        let bgColor = $('#bg-color-picker').val();
        let textColor = $('#text-color-picker').val();

        $('.zwssgr-google-toggle').css({
            'background-color': bgColor,
            'color': textColor
        });
    });

    // When the background color picker changes
	$(document).on('input', '#bg-color-picker', function() {
        let bgColor = $(this).val();
        $('.zwssgr-google-toggle').css('background-color', bgColor);
    });

    // When the text color picker changes
	$(document).on('input', '#text-color-picker', function() {
        let textColor = $(this).val();
        $('.zwssgr-google-toggle').css('color', textColor);
    });

	function toggleButtonVisibility() {
		if ($('#toggle-google-review').is(':checked')) {
			$('.zwssgr-google-toggle').show(); // Show the button
		} else {
			$('.zwssgr-google-toggle').hide(); // Hide the button
		}
	}

	// Run the function when the page loads
	toggleButtonVisibility();

	// Run the function whenever the checkbox state changes
	$(document).on('change', '#toggle-google-review', toggleButtonVisibility);

	// Function to hide or show elements with a smooth effect
	function toggleElements() {
        $('#review-title').is(':checked') ? $('.selected-option-display .zwssgr-title').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-title').stop(true, true).fadeIn(600);
        $('#review-rating').is(':checked') ? $('.selected-option-display .zwssgr-rating').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-rating').stop(true, true).fadeIn(600);
        $('#review-days-ago').is(':checked') ? $('.selected-option-display .zwssgr-days-ago').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-days-ago').stop(true, true).fadeIn(600);
        $('#review-content').is(':checked') ? $('.selected-option-display .zwssgr-content').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-content').stop(true, true).fadeIn(600);
		$('#review-photo').is(':checked') ? $('.selected-option-display .zwssgr-profile').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-profile').stop(true, true).fadeIn(600);
		$('#review-g-icon').is(':checked') ? $('.selected-option-display .zwssgr-google-icon').stop(true, true).fadeOut(600) : $('.selected-option-display .zwssgr-google-icon').stop(true, true).fadeIn(600);
    }

	

    // Attach change event listeners to checkboxes
	$(document).on('change', 'input[name="review-element"]', function() {
        toggleElements(); // Call function to toggle elements with fade effect
    });

    // Call toggleElements on page load to apply any initial settings with fade effect
    toggleElements();

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
	// Event listener for date format dropdown
	$(document).on('change', '#date-format-select', function() {
		const selectedFormat = $(this).val();
		updateDisplayedDates(); // Updated to ensure it re-renders based on new format
	});

	// Function to update date display based on format
	function updateDisplayedDates() {
		const lang = $('#language-select').val(); // Get selected language
		const format = $('#date-format-select').val(); // Get selected date format
	
		$('.date').each(function() {
			const originalDate = $(this).data('original-date'); // Get the original date
			if (format === 'hide') {
				$(this).text(''); // Hide the date
			} else {
				const formattedDate = formatDate(originalDate, format, lang); // Pass lang to formatDate
				$(this).text(formattedDate); // Update the text with the formatted date
			}
		});
	}

	// Translations for "Read more" in different languages
	var zwssgrTranslations = {
		en: 'Read more',
		es: 'Leer más',
		fr: 'Lire la suite',
		de: 'Mehr lesen',
		it: 'Leggi di più',
		pt: 'Leia mais',
		ru: 'Читать далее',
		zh: '阅读更多',
		ja: '続きを読む',
		hi: 'और पढ़ें',
		ar: 'اقرأ أكثر',
		ko: '더 읽기',
		tr: 'Daha fazla oku',
		bn: 'আরও পড়ুন',
		ms: 'Baca lagi',
		nl: 'Lees meer',
		pl: 'Czytaj więcej',
		sv: 'Läs mer',
		th: 'อ่านเพิ่มเติม',
	};

	// Function to update Read more link based on language
	function updateReadMoreLink($element, lang) {
		let charLimit = parseInt($('#review-char-limit').val(), 10); // Get character limit
		let fullText = $element.data('full-text'); // Get the stored full text

		if (charLimit && fullText.length > charLimit) {
			let trimmedText = fullText.substring(0, charLimit) + '... ';
			$element.html(trimmedText + `<a href="javascript:void(0);" class="read-more-link">${zwssgrTranslations[lang]}</a>`);
			
			// Re-apply the "Read more" click event using event delegation
			$(document).on('click', '.read-more-link', function (e) {
				e.preventDefault();
				$(this).parent().text(fullText); // Update parent with full text
			});
		} else {
			$element.text(fullText); // Show full text if no limit
		}
	}

	// On character limit input change
	$(document).on('input', '#review-char-limit', function () {
		let charLimit = parseInt($(this).val(), 10); // Get the entered value
		let lang = $('#language-select').val(); // Get current language

		// Reference to the error message container
		let $errorContainer = $('#char-limit-error');

		// Remove previous error message if any
		$errorContainer.text('');

		// Validation: Ensure the value is 1 or greater
		if (charLimit < 1 || isNaN(charLimit)) {
			if ($(this).val().trim() === '') {
				// If input is blank, reset all content to full text
				$('.zwssgr-content').each(function () {
					let $this = $(this);
					let fullText = $this.data('full-text') || $this.text(); // Get stored full text or fallback to current text
					$this.text(fullText); // Show the full text
				});
			} else {
				$errorContainer.text('Character limit must be 1 or greater.'); // Show the error message
				$(this).val(''); // Reset the input to an empty value
			}
			return; // Exit the function early if the validation fails
		}

		// If valid, apply the new character limit dynamically
		$('.zwssgr-content').each(function () {
			let $this = $(this);
			let fullText = $this.data('full-text') || $this.text(); // Get full text or fallback to current text

			// Store original full text if not already stored
			if (!$this.data('full-text')) {
				$this.data('full-text', fullText);
			}

			// Update the content with the new character limit
			updateReadMoreLink($this, lang); // Update the "Read more" link based on the new limit
		});
	});

	   // Function to update displayed dates based on selected language and format
	function updateDisplayedDates() {
		const lang = $('#language-select').val(); // Get selected language
		const format = $('#date-format-select').val(); // Get selected date format
	
		$('.zwssgr-date').each(function () {
			const originalDate = $(this).data('original-date'); // Get the original date
			if (format === 'hide') {
				$(this).text(''); // Hide the date
			} else {
				const formattedDate = formatDate(originalDate, format, lang);
				$(this).text(formattedDate); // Update the text with the formatted date
			}
		});
	}
	

	// On language select change
	$(document).on('change', '#language-select', function () {
		let lang = $(this).val(); // Get selected language
		updateDisplayedDates(); // Re-render dates when language changes

		// Loop through each content block and update the Read more link with the new language
		$('.zwssgr-content').each(function () {
			let $this = $(this);
			updateReadMoreLink($this, lang);
		});
	});

	$(document).on('change', '#date-format-select', updateDisplayedDates);

	// Toggle for Google Review link
	$(document).on('change', '#toggle-google-review', function() {
        if ($(this).is(':checked')) {
			$('#color-picker-options').stop(true, true).fadeIn();
        } else {
			$('#color-picker-options').stop(true, true).fadeOut();
        }
    });

	// Toggle for Lode More
	$(document).on('change', '#enable-load-more', function () {
        if ($(this).is(':checked')) {
            // If checkbox is checked, fade in the color picker options
			$('#zwssgr-load-color-picker-options').stop(true, true).fadeIn();
        } else {
            // If checkbox is unchecked, fade out the color picker options
			$('#zwssgr-load-color-picker-options').stop(true, true).fadeOut();
        }
    });

	if ($('#enable-load-more').is(':checked')) {
        $('#zwssgr-load-color-picker-options').show();
    } else {
        $('#zwssgr-load-color-picker-options').hide();
    }

	// Function to update the hidden input field with the keywords in a comma-separated format
	const updateInputField = () => {
		const keywords = [];
		$('#keywords-list .keyword-item').each(function () {
			keywords.push($(this).text().trim().replace(' ✖', ''));
		});
		$('#keywords-input-hidden').val(keywords.join(', ')); // Store the keywords in a hidden input
	};

	// Initialize the hidden input field based on the existing keywords
	updateInputField();

	// Function to handle adding new keywords
	const handleAddKeywords = (inputValue) => {
		// Get the input value and split it into keywords
		const newKeywords = inputValue.split(',').map(keyword => keyword.trim()).filter(keyword => keyword);

		// Get the current number of keywords in the list
		const currentKeywordsCount = $('#keywords-list .keyword-item').length;

		// Check if adding new keywords exceeds the limit of 5
		if (currentKeywordsCount + newKeywords.length > 5) {
			$('#error-message').show(); // Show the error message
			return; // Stop further execution
		} else {
			$('#error-message').hide(); // Hide the error message if under limit
		}

		$('#keywords-input').val(''); // Clear input field

		newKeywords.forEach(function (keyword) {
			// Check if the keyword is already in the list
			if ($('#keywords-list .keyword-item').filter(function () {
				return $(this).text().trim() === keyword;
			}).length === 0) {
				// Create a new keyword item
				const keywordItem = $('<div class="keyword-item"></div>').text(keyword);
				const removeButton = $('<span class="remove-keyword"> ✖</span>'); // Cross sign

				// Append remove button to the keyword item
				keywordItem.append(removeButton);

				// Append the keyword item to the keywords list
				$('#keywords-list').append(keywordItem);

				// Update hidden input field
				updateInputField();

				// Set up click event to remove keyword
				removeButton.on('click', function () {
					keywordItem.remove(); // Remove keyword from list
					updateInputField(); // Update input field after removal
				});
			}
		});
	};

	// Handle the Enter key press to add keywords
	$(document).on('keypress', '#keywords-input', function (e) {
		if (e.which === 13) { // Check for Enter key
			e.preventDefault(); // Prevent default form submission
			handleAddKeywords($(this).val());
		}
	});

	// Handle the blur event to add keywords
	$(document).on('blur', '#keywords-input', function () {
		handleAddKeywords($(this).val());
	});


    // Set up click event to remove existing keywords (on page load)
	$(document).on('click', '#keywords-list .remove-keyword', function () {
        $(this).parent('.keyword-item').remove(); // Remove the clicked keyword
        updateInputField(); // Update the hidden input after removal
    });
	
	// Save the all Widget and Generate the shortcode
	$(document).on('click', '#save-get-code-btn', function(e) {
		e.preventDefault();
	
		let postId = getQueryParam('zwssgr_widget_id'); // Get post_id from the URL
		let displayOption = $('input[name="display_option"]:checked').val();
		let selectedElements = $('input[name="review-element"]:checked').map(function() {
			return $(this).val();
		}).get();
		let keywords = $('#keywords-list .keyword-item').map(function() {
			return $(this).text().trim().replace(' ✖', '');
		}).get();
		let dateFormat = $('#date-format-select').val();
		let charLimit = $('#review-char-limit').val();
		let language = $('#language-select').val();
		let sortBy = $('#sort-by-select').val();
		let enableLoadMore = $('#enable-load-more').is(':checked') ? 1 : 0;
		let googleReviewToggle = $('#toggle-google-review').is(':checked') ? 1 : 0;
		let bgColor = $('#bg-color-picker').val();
		let textColor = $('#text-color-picker').val();
		let bgColorLoad = $('#bg-color-picker_load').val();
		let textColorLoad = $('#text-color-picker_load').val();
		let settings = $('.tab-item.active').attr('data-tab');
		let postsPerPage = $('#posts-per-page').val();
		let selectedRating = $('.star-filter.selected').last().data('rating') || 0; // Fetch the rating, or default to 0
		let currentTab2 = $('.tab-item.active').data('tab'); // Get the current active tab
		let customCSS = $('.zwssgr-textarea').val();
		let enableSortBy = $('#enable-sort-by-filter').is(':checked') ? 1 : 0; 

		// Send AJAX request to store the widget data and shortcode
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			async: false,  // Make the request synchronous
			data: {
				action: 'save_widget_data',
				security: my_widget.nonce,
				post_id: postId,
				display_option: displayOption,
				selected_elements: selectedElements,
				rating_filter: selectedRating,
				keywords: keywords,
				date_format: dateFormat,
				char_limit: charLimit,
				language: language,
				sort_by: sortBy,
				enable_load_more: enableLoadMore,
				google_review_toggle: googleReviewToggle,
				bg_color: bgColor,
				text_color: textColor,
				bg_color_load: bgColorLoad,
				text_color_load: textColorLoad,
				settings: settings,
				posts_per_page: postsPerPage,
				current_tab2: currentTab2,
				enable_sort_by: enableSortBy,
				custom_css: customCSS  // Add the custom CSS value here
			},
			success: function(response) {
				if (response.success) {
					showNotification('Settings and shortcode saved successfully.', 'success'); // Custom success notification
				} else {
					showNotification('Error: ' + response.data, 'error'); // Custom error notification
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log('AJAX Error: ', textStatus, errorThrown);
				showNotification('An error occurred while saving data. Details: ' + textStatus + ': ' + errorThrown, 'error'); // Custom error notification
			}
		});
	});

	$("#fetch-gmb-data #fetch-gmd-accounts").on("click", function (e) {
		e.preventDefault();
		const zwssgr_button = $(this);
		const zwssgr_gmb_data_type = zwssgr_button.data("fetch-type");

		$('#fetch-gmb-data .progress-bar').css('display', 'block');
	
		zwssgr_button.prop("disabled", true);
		zwssgr_button.html('<span class="spinner is-active"></span> Fetching...');
	
		processBatch(zwssgr_gmb_data_type);
	});
	
	$("#fetch-gmb-auth-url").on("click", function (e) {
		
		e.preventDefault();

		$("#fetch-gmb-auth-url").prop('disabled', true);
		$("#fetch-gmb-auth-url").html("Connecting...");

		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: "zwssgr_fetch_oauth_url"
			},
			success: function (response) {

				if (response.success) {
					
					$("#fetch-gmb-auth-url").prop('disabled', false);
					$("#fetch-gmb-auth-url").html("Redirecting...");

					// Redirect to the OAuth URL
					window.location.href = response.data.zwssgr_oauth_url;

				} else {

					$("#fetch-gmb-auth-url-response").html("<p class='error response''>Error generating OAuth URL: " + (response.data?.message || "Unknown error") + "</p>");

				}
			},
			error: function (xhr, status, error) {

				$("#fetch-gmb-auth-url").prop('disabled', false);
				$("#fetch-gmb-auth-url").html("Connect with Google");

				// Log and alert errors
				$("#fetch-gmb-auth-url-response").html("<p class='error response''> An unexpected error occurred: " + error + "</p>");
				
			}
		});
		
	});

	$("#disconnect-gmb-auth #disconnect-auth").on("click", function (e) {
		
		e.preventDefault();

		$("#disconnect-gmb-auth #disconnect-auth").prop('disabled', true);
		$("#disconnect-gmb-auth #disconnect-auth").html("Disconnecting...");

		// Get the checkbox value
		var zwssgr_delete_plugin_data = $('#disconnect-gmb-auth #delete-all-data').prop('checked') ? '1' : '0';

		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: "zwssgr_delete_oauth_connection",
				zwssgr_delete_plugin_data: zwssgr_delete_plugin_data,
				security: zwssgr_admin.zwssgr_delete_oauth_connection,
			},
			success: function (response) {

				if (response.success) {
					
					$("#disconnect-gmb-auth #disconnect-auth").prop('disabled', false);
					$("#disconnect-gmb-auth #disconnect-auth").html("Disconnected");

					$("#disconnect-gmb-auth-response").html("<p class='success response''> OAuth Disconnected: " + (response.data?.message || "Unknown error") + "</p>");

					$("#disconnect-gmb-auth .zwssgr-th-label").html("");

					$("#disconnect-gmb-auth .zwssgr-caution-div").fadeOut();
					$("#disconnect-gmb-auth .danger-note").fadeOut();

					setTimeout(function() {
						window.location.href = zwssgr_admin.zwssgr_redirect;
					}, 1500);

				} else {

					$("#disconnect-gmb-auth-response").html("<p class='error response''>Error disconnecting OAuth connection: " + (response.data?.message || "Unknown error") + "</p>");

				}

			},
			error: function (xhr, status, error) {

				$("#disconnect-gmb-auth #disconnect-auth").prop('disabled', false);
				$("#disconnect-gmb-auth #disconnect-auth").html("Disconnect");

				// Log and alert errors
				$("#disconnect-gmb-auth-response").html("<p class='error response''> An unexpected error occurred: " + error + "</p>");
				
			}
		});
		
	});

	function zwssgr_validate_email(email) {
		var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailPattern.test(email);
	}
	
	$("#fetch-gmb-data #fetch-gmd-reviews").on("click", function (e) {
		e.preventDefault();
		const zwssgr_button = $(this);
		const zwssgr_gmb_data_type = zwssgr_button.data("fetch-type");
	
		// Get selected account and location from the dropdowns
		const zwssgr_account_number = $("#fetch-gmb-data #zwssgr-account-select").val();
		$("#fetch-gmb-data #zwssgr-account-select").addClass('disabled');

		const zwssgr_account_name = $(
			"#fetch-gmb-data #zwssgr-account-select option:selected"
		).text();

		const zwssgr_location_number = $("#fetch-gmb-data #zwssgr-location-select").val();

		const zwssgr_location_name = $(
			"#fetch-gmb-data #zwssgr-location-select option:selected"
		).text();

		const zwssgr_location_new_review_uri = $(
			"#fetch-gmb-data #zwssgr-location-select option:selected"
		).attr("data-new-review-url");

		const zwssgr_location_all_review_uri = $(
			"#fetch-gmb-data #zwssgr-location-select option:selected"
		).attr("data-all-reviews-url");
		
		$("#fetch-gmb-data #zwssgr-location-select").addClass('disabled');

		const zwssgr_widget_id = zwssgr_getUrlParameter("zwssgr_widget_id");

		zwssgr_button.addClass("disabled");
		zwssgr_button.html('<span class="spinner is-active"></span> Fetching...');

		if (!zwssgr_account_number && !zwssgr_location_number) {
			$('#fetch-gmb-data .response').html('<p class="error">Both account and location are required.</p>');
			setTimeout(function() {
				location.reload();
			}, 1500);
			return;
		}

		if (!zwssgr_account_number) {
			$('#fetch-gmb-data .response').html('<p class="error"> Account is required. </p>');
			setTimeout(function() {
				location.reload();
			}, 1500);
			return;
		}

		if (!zwssgr_location_number) {
			$('#fetch-gmb-data .response').html('<p class="error"> Location is required. </p>');
			setTimeout(function() {
				location.reload();
			}, 1500);
			return;
		}

		if (!zwssgr_widget_id) {
			alert("Please select an approprite location.");
			$('#fetch-gmb-data .response').html('<p class="error"> No valid widget ID found. </p>');
			setTimeout(function() {
				location.reload();
			}, 1500);
			return;
		}

		$('#fetch-gmb-data .response').html('');

		$('#fetch-gmb-data .progress-bar').css('display', 'block');
	
		processBatch(
			zwssgr_gmb_data_type,
			zwssgr_account_number,
			zwssgr_location_number,
			zwssgr_widget_id,
			zwssgr_location_name,
			zwssgr_location_new_review_uri,
			zwssgr_account_name,
			zwssgr_location_all_review_uri
		);
	});
	
	  // Function to get URL parameter by name
	function zwssgr_getUrlParameter(name) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(name);
	}
	
	  // Listen for changes in the account dropdown and process batch if changed
	$("#fetch-gmb-data #zwssgr-account-select").on("change", function () {
		const zwssgr_account_number = $(this).val();
		const zwssgr_account_name = $(this).find("option:selected").text();
		$("#fetch-gmb-data #zwssgr-location-select").remove();
		$("body #fetch-gmb-data .zwssgr-submit-btn").remove();
		
		if (zwssgr_account_number) {

		  // Add loading spinner and disable the dropdown to prevent multiple selections
			$(this).prop("disabled", true);
			$("#fetch-gmb-data #zwssgr-location-select").remove();
			$("#fetch-gmb-data #fetch-gmd-reviews").remove();
		
			const zwssgr_widget_id = zwssgr_getUrlParameter("zwssgr_widget_id");
			
			$('#fetch-gmb-data .response').html('');
			$('#fetch-gmb-data .progress-bar').css('display', 'block');
		
			// Assuming 'zwssgr_gmb_locations' as the data type for fetching locations on account change
			processBatch(
			"zwssgr_gmb_locations",
			zwssgr_account_number,
			null,
			zwssgr_widget_id,
			null,
			null,
			zwssgr_account_name
			);
		}		

	});
	
	function processBatch(
	zwssgr_gmb_data_type,
	zwssgr_account_number,
	zwssgr_location_number,
	zwssgr_widget_id,
	zwssgr_location_name,
	zwssgr_location_new_review_uri,
	zwssgr_account_name,
	zwssgr_location_all_review_uri
	) {
		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
			action: "zwssgr_fetch_gmb_data",
			security: zwssgr_admin.zwssgr_queue_manager_nounce,
			zwssgr_gmb_data_type: zwssgr_gmb_data_type,
			zwssgr_account_number: zwssgr_account_number,
			zwssgr_location_number: zwssgr_location_number,
			zwssgr_widget_id: zwssgr_widget_id,
			zwssgr_location_name: zwssgr_location_name,
			zwssgr_location_new_review_uri: zwssgr_location_new_review_uri,
			zwssgr_account_name: zwssgr_account_name,
			zwssgr_location_all_review_uri: zwssgr_location_all_review_uri
			},
			success: function (response) {

			if (response.success) {
				$('#fetch-gmb-data .progress-bar').stop(true, true).fadeIn();
			} else {
				$('#fetch-gmb-data .response').html('<p class="error">' + response.data.message + '</p>');
				// Reload the page after a 1-second delay
				setTimeout(function() {
					location.reload();
				}, 1500);
			}

			},
			error: function (xhr, status, error) {
			
			// Catch errors sent using wp_send_json_error
			let response = xhr.responseJSON;

			console.log(response, 'response');

			if (response && !response.success) {
				$('#fetch-gmb-data .response').html('<p class="error">' + response.data.message + '</p>');
			}

			// Reload the page after a 1-second delay
			setTimeout(function() {
				location.reload();
			}, 1500);

			},
		});

		// batchInterval = setInterval(function() {
		// 	checkBatchStatus();
		// }, 1000);

	}

	  // Check if we're on the specific page URL that contains zwssgr_widget_id dynamically
	if (window.location.href.indexOf('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data&zwssgr_widget_id=') !== -1) {
        // Call the function to check batch status
		batchInterval = setInterval(function() {
			checkBatchStatus();
		}, 2500);

	}
	
	function checkBatchStatus() {

		// Function to get URL parameters
		function getUrlParameter(name) {
			const urlParams = new URLSearchParams(window.location.search);
			return urlParams.get(name);
		}

		// Capture 'zwssgr_widget_id' from the URL
		const zwssgr_widget_id = getUrlParameter('zwssgr_widget_id');

		$.ajax({
			url: zwssgr_admin.ajax_url,
			method: "POST",
			data: {
				action: "zwssgr_get_batch_processing_status",
				security: zwssgr_admin.zwssgr_queue_manager_nounce,
				zwssgr_widget_id: zwssgr_widget_id
			},
			success: function (response) {
				if (response.success && response.data.zwgr_data_processing_init == 'false' && response.data.zwgr_data_sync_once == 'true') {
					
					$('#fetch-gmb-data .progress-bar #progress').val(100);
					$('#fetch-gmb-data .progress-bar #progress-percentage').text(Math.round(100) + '%');
					$('#fetch-gmb-data .progress-bar #progress-percentage').text('Processed');

					if (response.data.zwssgr_gmb_data_type == 'zwssgr_gmb_locations') {

						$('#fetch-gmb-data .response').html('<p class="success">Locations processed successfully</p>');

					} else if (response.data.zwssgr_gmb_data_type == 'zwssgr_gmb_reviews') {

						$('#fetch-gmb-data .response').html('<p class="success">Reviews processed successfully</p>');
						$('#fetch-gmb-data #fetch-gmd-reviews').html('Fetched');

					}
					
					setTimeout(function () {
						$('#fetch-gmb-data .progress-bar').fadeOut();
						if (response.data.zwssgr_gmb_data_type === 'zwssgr_gmb_reviews') {
							redirectToOptionsTab();
						} else {
							location.reload();
						}
					}, 2000);

				} else {
					// Use the batch progress directly from the response
					var zwssgr_batch_progress = response.data.zwssgr_batch_progress;

					// Check if zwssgr_batch_progress is a valid number
					if (!isNaN(zwssgr_batch_progress) && zwssgr_batch_progress >= 0 && zwssgr_batch_progress <= 100) {
						
						// Update the progress bar with the batch progress
						$('#fetch-gmb-data .progress-bar #progress').val(zwssgr_batch_progress);
						$('#fetch-gmb-data .progress-bar #progress-percentage').text(Math.round(zwssgr_batch_progress) + '%');

					} else {

						console.error('Invalid batch progress:', zwssgr_batch_progress);
						
					}
				}
			},
			error: function (xhr, status, error) {
				//console.error("Error:", error);
				//console.error("Status:", status);
				//console.error("Response:", xhr.responseText);
			},
		});
	}

	function redirectToOptionsTab() {
		// Get the current URL
		let currentUrl = window.location.href;
		
		// Replace or add the 'tab' parameter
		if (currentUrl.includes('tab=')) {
			currentUrl = currentUrl.replace(/tab=[^&]+/, 'tab=tab-options'); // Replace existing 'tab' value
		} else {
			currentUrl += (currentUrl.includes('?') ? '&' : '?') + 'tab=tab-options'; // Add 'tab' if it doesn't exist
		}
		
		// Redirect to the modified URL
		window.location.href = currentUrl;
	}
	
	$("#gmb-review-data #add-reply, #gmb-review-data #update-reply").on("click", function (event) {
	
		event.preventDefault();

		// Get the value of the 'Reply Comment' from textarea
		var zwssgr_reply_comment = $("#gmb-review-data textarea[name='zwssgr_reply_comment']").val();

		if (zwssgr_reply_comment.trim() === "") {
            $("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p>' + 'Please enter a valid reply.' + '</p></div>');
            return;
        }
		
		if (zwssgr_reply_comment.trim().length > 4086) {
			$("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p>' + 'Reply cannot exceed 4086 characters.' + '</p></div>');
			return;
		}

		var loader = $('<span class="loader is-active" style="margin-left: 10px;"></span>');
		var buttons = $("#gmb-review-data #add-reply, #gmb-review-data #update-reply, #gmb-review-data #delete-reply");

		const urlParams = new URLSearchParams(window.location.search);
		const zwssgr_wp_review_id = urlParams.get('post');

		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwssgr_add_update_review_reply',
				zwssgr_wp_review_id: zwssgr_wp_review_id,
				zwssgr_reply_comment: zwssgr_reply_comment,
				security: zwssgr_admin.zwssgr_add_update_reply_nonce
			},
			beforeSend: function() {
				buttons.addClass('disabled');
				$("#gmb-review-data textarea[name='zwssgr_reply_comment']").prop('readonly', true);
				$("#gmb-review-data #add-reply, #gmb-review-data #delete-reply").after(loader.clone());
			},
			success: function(response) {
				if (response.success) {
					$("#gmb-review-data #json-response-message").html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						location.reload();
					}, 2000);
				}
			},
			complete: function() {
				$("#gmb-review-data .loader.is-active").remove();
			},
			error: function(xhr, status, error) {
				$("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p> Error:' + error + '</p></div>');
			}
		});
	
	});
	
	$("#gmb-review-data #delete-reply").on("click", function (event) {
		
		event.preventDefault();

		var loader = $('<span class="loader is-active" style="margin-left: 10px;"></span>');
		var buttons = $("#gmb-review-data #update-reply, #gmb-review-data #delete-reply");

		const urlParams = new URLSearchParams(window.location.search);
		const zwssgr_wp_review_id = urlParams.get('post');
	
		// Send AJAX request to handle the reply update
		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwssgr_delete_review_reply',
				zwssgr_wp_review_id: zwssgr_wp_review_id,
				security: zwssgr_admin.zwssgr_delete_review_reply
			},
			beforeSend: function() {
				buttons.addClass('disabled');
				$("#gmb-review-data textarea[name='zwssgr_reply_comment']").prop('readonly', true);
				$("#gmb-review-data #delete-reply").after(loader);
			},
			success: function(response) {
				if (response.success) {
					$("#gmb-review-data #json-response-message").html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						location.reload();
					}, 2000);
				}
			},
			complete: function() {
				$("#gmb-review-data .loader.is-active").remove();
			},
			error: function(xhr, status, error) {
				$("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p> Error:' + error + '</p></div>');
			}
		});
	
	});

	$("#gmb-data-filter #zwssgr-account-select").on("change", function (e) {

		e.preventDefault();

		// Get the selected value
		var zwssgr_account_number = $(this).val();
		$(this).addClass('disabled');
	
		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwssgr_gmb_dashboard_data_filter',
				zwssgr_account_number: zwssgr_account_number,
				security: zwssgr_admin.zwssgr_gmb_dashboard_filter
			},
			success: function(response) {
				$('#gmb-data-filter #zwssgr-location-select').remove();
				if (response.success) {
					$('#gmb-data-filter').append(response.data);
					//$("#gmb-data-filter #zwssgr-location-select").trigger('change');
				}
			},
			complete: function() {
				$("#gmb-data-filter #zwssgr-account-select").removeClass('disabled');
			},
		});
	
	});

	$(".zwgr-dashboard").on("change", "#zwssgr-account-select, #zwssgr-location-select", function (e) {

		// Declare variables globally or in the appropriate scope
		let zwssgr_range_filter_data = null;
		let zwssgr_range_filter_type = null;

		// Disable select inputs while processing
		$('#zwssgr-account-select, #zwssgr-location-select').addClass('disabled').prop('disabled', true);

		if ($('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button.active').length > 0) {
			zwssgr_range_filter_type = 'rangeofdays';
			zwssgr_range_filter_data = $('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button.active').text().trim().toLowerCase();
		} else if ($('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"].active').length > 0) {
			zwssgr_range_filter_type = 'rangeofdate';
			zwssgr_range_filter_data = $('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').val().trim();
		}

		zwssgr_render_data_callback(e, zwssgr_range_filter_data, zwssgr_range_filter_type);

	});

	$(".zwgr-dashboard").on("click", ".zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button", function (e) {
		var zwssgr_range_filter_data = $(this).text().trim().toLowerCase();
		zwssgr_render_data_callback(e, zwssgr_range_filter_data, 'rangeofdays');
	});

	$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
		
		$('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button').removeClass('active');
		$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').addClass('active');
		
		var zwssgr_start_date = picker.startDate ? picker.startDate.format('DD-MM-YYYY') : null;
		var zwssgr_end_date = picker.endDate ? picker.endDate.format('DD-MM-YYYY') : null;

		var zwssgr_range_filter_data = zwssgr_start_date && zwssgr_end_date ? zwssgr_start_date + ' - ' + zwssgr_end_date : null;

		zwssgr_render_data_callback(ev, zwssgr_range_filter_data, 'rangeofdate');
	});

	var zwssgr_chart;
	var zwssgr_data;
	var zwssgr_options;
	var zwssgr_chart_data = zwssgr_admin.zwssgr_dynamic_chart_data;

	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(() => zwssgr_draw_chart(zwssgr_chart_data));
	
	function zwssgr_draw_chart(zwssgr_chart_data) {

		// Check if zwssgr_chart_data is a valid array
		if (!Array.isArray(zwssgr_chart_data)) {
			document.getElementById('zwssgr_chart_wrapper').innerHTML = 
				'<div style="text-align: center; font-size: 16px; color: #888; padding: 50px;">No enough data available</div>';
			return;
		}

		// Check if all second elements in rows are zero
		var zwssgr_all_zero = zwssgr_chart_data.every(function(row) {
			return Array.isArray(row) && row[1] === 0;
		});

		if (zwssgr_all_zero) {
			document.getElementById('zwssgr_chart_wrapper').innerHTML = 
				'<div style="text-align: center; font-size: 16px; color: #888; padding: 50px;">No enough data available</div>';
			return;
		}

		zwssgr_chart_data.unshift(['Rating', 'Number of Reviews']);

		// console.log(zwssgr_chart_data, 'zwssgr_chart_data');

		var zwssgr_data = google.visualization.arrayToDataTable(zwssgr_chart_data);

		var zwssgr_options = {
			pieHole: 0.4,
			width: 276,
			height: 276,
			pieSliceText: 'percentage',
			pieSliceTextStyle: {
				color: '#000000',
				fontSize: 16
			},
			legend: 'none',
			chartArea: {
				width: '90%',
				height: '90%'
			},
			colors: ['#F08C3C', '#3CAAB4', '#A9C6CC', '#285064', '#F44336'],
			backgroundColor: 'transparent'
		};

		var zwssgr_chart = new google.visualization.PieChart(document.getElementById('zwssgr_chart_wrapper'));
		zwssgr_chart.draw(zwssgr_data, zwssgr_options);

	}
	
	// Redraw chart on window resize
	window.addEventListener('resize', function() {
		if (zwssgr_chart) {
			zwssgr_chart.draw(zwssgr_data, zwssgr_options);
		}
	});

	function zwssgr_render_data_callback(e, zwssgr_range_filter_data, zwssgr_range_filter_type) {

		e.preventDefault();

		$zwssgr_chart_wrapper = $('#zwssgr_chart_wrapper').outerHeight(true);

		var zwssgr_gmb_account_div = $("#gmb-data-filter #zwssgr-account-select");
		var zwssgr_gmb_location_div = $("#gmb-data-filter #zwssgr-location-select");

		if ($(e.target).is("#gmb-data-filter #zwssgr-location-select")) {
			zwssgr_gmb_account_div.addClass('disabled');
			zwssgr_gmb_location_div.addClass('disabled');
		}

		var zwssgr_gmb_account_number   = zwssgr_gmb_account_div.val();
		var zwssgr_gmb_account_location = zwssgr_gmb_location_div.val();

		var zwssgr_filter_data = {
			zwssgr_gmb_account_number: zwssgr_gmb_account_number,
			zwssgr_gmb_account_location: zwssgr_gmb_account_location,
			zwssgr_range_filter_type: zwssgr_range_filter_type,
			zwssgr_range_filter_data: zwssgr_range_filter_data
		};
	
		$.ajax({
			url: zwssgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwssgr_data_render',
				zwssgr_filter_data: zwssgr_filter_data,
				security: zwssgr_admin.zwssgr_data_render
			},
			beforeSend: function() {
				var minHeight = $('.zwgr-dashboard #render-dynamic').outerHeight(true) || 200;
				$('.zwgr-dashboard #render-dynamic').remove();
				$('.zwgr-dashboard').append('<div class="loader-outer-wrapper" style="height:' + minHeight + 'px;"><div class="loader"></div></div>');	
			},
			success: function(response) {
				if (response.success) {
					$('.zwgr-dashboard').append(response.data.html);
					$('.zwgr-dashboard').children(':last').hide().stop(true, true).fadeIn(300);
					if (response.data.zwssgr_chart_data) {
						setTimeout(function() {
							google.charts.setOnLoadCallback(zwssgr_draw_chart(response.data.zwssgr_chart_data));
						}, 500);
					}

				} else {
					$('.zwgr-dashboard ').html('<p>Error loading data.</p>');
				}
			},
			complete: function() {
				$('.zwgr-dashboard .loader-outer-wrapper').remove();
				zwssgr_gmb_account_div.removeClass('disabled');
				zwssgr_gmb_location_div.removeClass('disabled');
				// Disable select inputs while processing
				$('#zwssgr-account-select, #zwssgr-location-select').removeClass('disabled').prop('disabled', false);
			},
			error: function() {
				$('#render-dynamic').html('<p>An error occurred while fetching data.</p>');
			}
		});
	}

	$(document).on('click', '.star-filter', function () {
		let rating = $(this).data('rating'); // Get the rating of the clicked star
	
		// Check if the clicked star is already selected and is the lowest rating
		if ($(this).hasClass('selected') && rating === 1) {
			// Unselect all stars
			$('.star-filter').removeClass('selected');
			$('.star-filter .star').css('fill', '#ccc'); // Reset color to unselected
			return;
		}
	
		// Toggle the 'selected' state of stars
		$('.star-filter').each(function () {
			let currentRating = $(this).data('rating');
			if (currentRating <= rating) {
				// Select this star
				$(this).addClass('selected');
				$(this).find('.star').css('fill', '#f39c12'); // Set color to gold
			} else {
				// Deselect this star
				$(this).removeClass('selected');
				$(this).find('.star').css('fill', '#ccc'); // Reset color to unselected
			}
		});
	
		// Handle the new rating value
		let ratingFilterValue = rating;
	});
	

	// Event listener for clicking on a star filter
	$(document).on('click', '#sort-by-select,.filter-rating .star-filter' , function() {
		let nonce = filter_reviews.nonce;
		let postId = getQueryParam('zwssgr_widget_id');
		let sortBy = $('#sort-by-select').val(); // Get the selected sort by value
		let selectedOption = getQueryParam('selectedOption');
		// Prepare an array of selected ratings
		let selectedRatings = [];
		$('.filter-rating .star-filter.selected').each(function() {
			selectedRatings.push($(this).data('rating')); // Push each selected rating into the array
		});

		// If nothing is selected, default to all ratings (1-5 stars)
		if(selectedRatings.length === 1){
			selectedRatings =[1]
		}else if(selectedRatings.length === 2){
			selectedRatings =[2]
		}else if(selectedRatings.length === 3){
			selectedRatings =[3]
		}else if(selectedRatings.length === 4){
			selectedRatings =[4]
		}else if(selectedRatings.length === 5){
			selectedRatings =[5]
		}else{
			selectedRatings = [1, 2, 3, 4, 5];
		}

		// Make the AJAX request to filter reviews based on selected ratings
		$.ajax({
			type: 'POST',
			url: filter_reviews.ajax_url,
			data: {
				action: 'filter_reviews', // The action for the PHP handler
				zwssgr_widget_id: postId,
				rating_filter: selectedRatings, // Pass the selected ratings array
				sort_by: sortBy, // Pass sort by parameter
				nonce: nonce
			},
			success: function(response) {

				// Ensure the response is HTML or clean content
				if (typeof response === "string" || response instanceof String) {
					// Insert response as HTML into the display
					$('#selected-option-display').empty();
					$('#selected-option-display').html(response);
				} else {
					console.error("Expected HTML content, but received:", response);
				}

				// Only reinitialize Slick slider if selectedOption is one of the slider options
				if (selectedOption === 'slider-1' || selectedOption === 'slider-2' || selectedOption === 'slider-3' || selectedOption === 'slider-4' || selectedOption === 'slider-5' || selectedOption === 'slider-6') {
					setTimeout(function() {
						reinitializeSlickSlider1($('#selected-option-display'));
					}, 100);
				}
				// Handle list layout reinitialization (if needed)
				if (selectedOption === 'list-1' || selectedOption === 'list-2' || selectedOption === 'list-3' || selectedOption === 'list-4' || selectedOption === 'list-5') {
					 // Optionally, you can apply list-specific reinitialization logic here
					//  alert('List layout filtered');
				}				
			},
			error: function(xhr, status, error) {
				console.error("AJAX Error: ", status, error);
			}
		});
	});

	// Function to reinitialize the selected Slick Slider
	function reinitializeSlickSlider1(container) {
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

		if (slider3.hasClass('slick-initialized')) {
			slider3.slick('unslick');
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


	// Handle filter button clicks
    $('.zwgr-dashboard .zwssgr-filters-wrapper .zwssgr-filter-button').on('click', function () {
        // Remove active class from all buttons and add it to the clicked button
		$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').removeClass('active');
		$('.zwgr-dashboard .zwssgr-filters-wrapper .zwssgr-filter-button').removeClass('active');
        $(this).addClass('active');		
    });
	
	$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').daterangepicker({
		opens: 'center',
		locale: {
			format: 'DD-MM-YYYY'
		},
		autoUpdateInput: false,
		minDate: moment('1995-01-01', 'YYYY-MM-DD'),
		maxDate: moment(),
	}, function(start, end, label) {
		$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').val(
			start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY')
		);
	});
	
	// Set custom placeholder for gmb-dashboard-filter
	$('.zwgr-dashboard .zwssgr-filters-wrapper input[name="dates"]').attr('placeholder', 'Custom');

	$(document).on('click', '.toggle-content', function () {
        var $link = $(this);
        var fullText = $link.data('full-text');
        var $parentParagraph = $link.closest('p');
    
        // Replace the trimmed content with the full content
        $parentParagraph.html(fullText);
    });

	// Start code SMTP
	function zwssgr_update_Smtp_Port() {
        let encryptionType = $('input[name="zwssgr_smtp_ency_type"]:checked').val();
        switch(encryptionType) {
            case 'none':
                $('#zwssgr-smtp-port').val('25'); // Set port to 25 for 'None'
                break;
            case 'ssl':
                $('#zwssgr-smtp-port').val('465'); // Set port to 465 for 'SSL'
                break;
            case 'tls':
                $('#zwssgr-smtp-port').val('587'); // Set port to 587 for 'TLS'
                break;
            default:
                $('#zwssgr-smtp-port').val('25'); // Default port
        }
    }
	$(document).on('change', 'input[name="zwssgr_smtp_ency_type"]', function() {
        zwssgr_update_Smtp_Port(); // Update the port when the encryption type is changed
    });
	if ($('#zwssgr_smtp_auth_1').is(':checked')) {
        $('.zwssgr-smtp-auth-enable').hide(); // Hide if 'No' is selected
		$('input[name="zwssgr_smtp_username"]').removeAttr('required');
		$('input[name="zwssgr_smtp_password"]').removeAttr('required');
	} else {
        $('.zwssgr-smtp-auth-enable').show(); // Show if 'Yes' is selected
		$('input[name="zwssgr_smtp_username"]').attr('required', 'required');
		$('input[name="zwssgr_smtp_password"]').attr('required', 'required');
    }
    $(document).on('change', 'input[name="zwssgr_smtp_auth"]', function() {
        if ($(this).val() === 'no') {
            $('.zwssgr-smtp-auth-enable').hide(); // Hide if 'No' is selected
			$('input[name="zwssgr_smtp_username"]').removeAttr('required');
			$('input[name="zwssgr_smtp_password"]').removeAttr('required');
        } else {
            $('.zwssgr-smtp-auth-enable').show(); // Show if 'Yes' is selected
			$('input[name="zwssgr_smtp_username"]').attr('required', 'required');
			$('input[name="zwssgr_smtp_password"]').attr('required', 'required');
        }
    }); 
	var zwssgrSmtpSubmitButton = $('.zwssgr-smtp-submit-btn'); // Select the submit button
	$(document).on('change', 'input[name="zwssgr_admin_smtp_enabled"]', function() {
        if ($(this).is(':checked')) {
			$('input[name="zwssgr_smtp_username"]').attr('required', 'required');
			$('input[name="zwssgr_smtp_password"]').attr('required', 'required');
			$('input[name="zwssgr_from_email"]').attr('required', 'required');
			$('input[name="zwssgr_smtp_host"]').attr('required', 'required');
			$('.zwssgr-admin-enable-smtp').show(); // Example of showing an element
			zwssgrSmtpSubmitButton.removeClass('zwssgr-disable'); 
        } else {
			$('.zwssgr-admin-enable-smtp').hide(); // Example of hiding an element
			$('input[name="zwssgr_from_email"]').removeAttr('required');
			$('input[name="zwssgr_smtp_host"]').removeAttr('required');
			$('input[name="zwssgr_smtp_username"]').removeAttr('required');
			$('input[name="zwssgr_smtp_password"]').removeAttr('required');
			zwssgrSmtpSubmitButton.addClass('zwssgr-disable'); 
        }
    });
	if ($('input[name="zwssgr_admin_smtp_enabled"]').is(':checked')) {
		$('.zwssgr-admin-enable-smtp').show();
		$('input[name="zwssgr_smtp_username"]').attr('required', 'required');
		$('input[name="zwssgr_smtp_password"]').attr('required', 'required');
		$('input[name="zwssgr_from_email"]').attr('required', 'required');
        $('input[name="zwssgr_smtp_host"]').attr('required', 'required'); 
		zwssgrSmtpSubmitButton.removeClass('zwssgr-disable'); 
	} else {
		$('.zwssgr-admin-enable-smtp').hide(); 
		$('input[name="zwssgr_from_email"]').removeAttr('required');
		$('input[name="zwssgr_smtp_host"]').removeAttr('required');
		$('input[name="zwssgr_smtp_username"]').removeAttr('required');
		$('input[name="zwssgr_smtp_password"]').removeAttr('required');
		zwssgrSmtpSubmitButton.addClass('zwssgr-disable'); 
	}
	// End code SMTP
	

	$(document).on('change', '#zwssgr-account-select', function () {
		$(this).closest('form').submit();
	});
});