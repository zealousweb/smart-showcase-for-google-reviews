jQuery(document).ready(function($) {

	//widget should active 
    var widget_post_type = 'zwsgr_data_widget';
    if ($('body.post-type-' + widget_post_type).length || $('body.post-php.post-type-' + widget_post_type).length ) {
		$('.toplevel_page_zwsgr_dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
		$('ul.wp-submenu li a[href="edit.php?post_type=zwsgr_data_widget"]').parent('li').addClass('current');
	}

	if ($('body.post-new-php.post-type-' + widget_post_type).length) {
		$('.toplevel_page_zwsgr_dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
		$('ul.wp-submenu li a[href="edit.php?post_type=zwsgr_data_widget"]').parent('li').addClass('current');
	}

	//SEO and Notification Email Toggle 
	var toggle = $('#zwsgr_admin_notification_enabled');
	var notificationFields = $('.notification-fields');
	if (toggle.is(':checked')) {
		notificationFields.show();
	} else {
		notificationFields.hide();
	}
	toggle.on('change', function () {
		if ($(this).is(':checked')) {
			notificationFields.show();
		} else {
			notificationFields.hide();
		}
	});


	// SEO and Notification email vaildation
	function validateEmail(email) {
		var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
		return emailPattern.test(email);
	}

	// Function to validate emails and show messages
	function validateEmails() {
		var emails = $('#zwsgr_admin_notification_emails').val().split(',');
		var invalidEmails = [];
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
	$('#zwsgr_admin_notification_emails').on('keypress', function() {
		validateEmails();
	});

	// On blur (when the user leaves the email field)
	$('#zwsgr_admin_notification_emails').on('blur', function() {
		validateEmails();
	});

	// On form submission, check if all emails are valid
	$('#notification-form').on('submit', function(e) {
		var emails = $('#zwsgr_admin_notification_emails').val().split(',');
		var invalidEmails = [];
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
	const selectButtons = $('.select-btn');
	const selectedOptionDisplay = $('#selected-option-display');
	const generatedShortcodeDisplay = $('#generated-shortcode-display');

	let currentDisplayOption = 'all';

	// Add event listeners to radio buttons for dynamic filtering
	radioButtons.change(function () {
		currentDisplayOption = $(this).val();
		updateOptions(currentDisplayOption);
	});

	// Function to show/hide options based on the selected radio button
	function updateOptions(value) {
		$('.option-item').each(function () {
			if (value === 'all' || $(this).data('type') === value) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}

	// Set default view (show all options)
	updateOptions('all');
				
	// Function to get query parameter by name
	function getQueryParam(param) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(param);
	}
	
	// Get the active tab and selected option from the URL
	var activeTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
	var selectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL

	// Initially show the active tab content
	$('.tab-content').hide(); // Hide all tab content
	$('#' + activeTab).show(); // Show the active tab content

	$('.tab-item').removeClass('active');
	$('.tab-item[data-tab="' + activeTab + '"]').addClass('active');

	// If there's a selected option in the URL, display it in the "Selected Option" tab
	if (selectedOption && activeTab === 'tab-selected') {
		var selectedOptionElement = $('#' + selectedOption).clone();
		$('#selected-option-display').html(selectedOptionElement);
		$('#selected-option-display').find('.select-btn').remove();

		// Reinitialize Slick slider after the DOM has been updated
		setTimeout(function() {
			reinitializeSlickSlider($('#selected-option-display'));
		}, 100);
	}
		
	// Handle click events for the tab navigation items
	$('.tab-item').on('click', function() {
		var tabId = $(this).data('tab');
		var currentUrl = window.location.href.split('?')[0]; // Get the base URL
		var selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists

		// Preserve the page parameter and append tab, and keep selected option in the URL
		var newUrl = currentUrl + '?page=zwsgr_layout&tab=' + tabId;
		if (selectedOption) {
			newUrl += '&selectedOption=' + selectedOption;
		}

		// Redirect to the new URL
		window.location.href = newUrl;
	});
		
	// Handle click events for "Select Option" buttons
	$('.select-btn').on('click', function() {
		var optionId = $(this).data('option');
		var currentUrl = window.location.href.split('?')[0]; // Get the base URL

		// Preserve the page parameter and append tab and selected option
		window.location.href = currentUrl + '?page=zwsgr_layout&tab=tab-selected&selectedOption=' + optionId;
	});
		
	// Function to reinitialize the selected Slick Slider
	function reinitializeSlickSlider(container) {
		// Find and reinitialize Slick sliders
		var slider1 = $(container).find('.slider-1');
		var slider2 = $(container).find('.slider-2');

		// Unslick if it's already initialized
		if (slider1.hasClass('slick-initialized')) {
			slider1.slick('unslick');
		}

		if (slider2.hasClass('slick-initialized')) {
			slider2.slick('unslick');
		}

		// Reinitialize the selected slider
		if (slider1.length) {
			slider1.slick({
				dots: false,
				arrows: false,
				infinite: true,
				slidesToShow: 3,
				slidesToScroll: 1
			});
		}

		if (slider2.length) {
			slider2.slick({
				dots: false,
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 1
			});
		}
	}
	
		
	// Slick sliders
	$('.slider-1').slick({
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 3,
		arrows: false,
		dots: false,
	});
	
	$('.slider-2').slick({
		infinite: true,
		slidesToShow: 2,
		slidesToScroll: 2,
		arrows: false,
		dots: false,
	});	 
	
});
