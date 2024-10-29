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

	// If on the "Generated Shortcode" tab, display the generated shortcode
	if (activeTab === 'tab-shortcode' && selectedOption) {
		var shortcode = '[zwsgr_layout id="' + selectedOption + '"]';
		$('#generated-shortcode-display').text(shortcode);
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

	// Handle the Save & Get Code Button
	$('#save-get-code-btn').on('click', function() {
		var selectedOption = getQueryParam('selectedOption');
		var currentUrl = window.location.href.split('?')[0]; // Get the base URL

		// Redirect to the "Generated Shortcode" tab with selected option in the URL
		window.location.href = currentUrl + '?page=zwsgr_layout&tab=tab-shortcode&selectedOption=' + selectedOption;
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

	// Handle click on visibility toggle icon of REview CPT
	$('.zwsgr-toggle-visibility').on('click', function(e) {
		e.preventDefault();

		var post_id = $(this).data('post-id');
		var $icon = $(this).find('.dashicons');

		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'toggle_visibility',
				post_id: post_id,
				nonce: zwsgr_admin.nonce
			},
			success: function(response) {
				if (response.success) {
					// Update icon based on the response
					$icon.removeClass('dashicons-hidden dashicons-visibility').addClass('dashicons-' + response.data.icon);

					// Optionally display the current state somewhere on the page
					var currentState = response.data.state;
					// console.log("Post visibility is now: " + currentState); 	
				}
			}
		});
	});



	// Function to hide or show elements with a smooth effect
    function toggleElements() {
        $('#review-title').is(':checked') ? $('.selected-option-display .title').fadeOut(600) : $('.selected-option-display .title').fadeIn(600);
        $('#review-rating').is(':checked') ? $('.selected-option-display .rating').fadeOut(600) : $('.selected-option-display .rating').fadeIn(600);
        $('#review-days-ago').is(':checked') ? $('.selected-option-display .days-ago').fadeOut(600) : $('.selected-option-display .days-ago').fadeIn(600);
        $('#review-content').is(':checked') ? $('.selected-option-display .content').fadeOut(600) : $('.selected-option-display .content').fadeIn(600);
        $('#reviewiew-photo').is(':checked') ? $('.selected-option-display .reviewer-photo').fadeOut(600) : $('.selected-option-display .reviewer-photo').fadeIn(600);
    }

    // Attach change event listeners to checkboxes
    $('input[name="review-element"]').on('change', function() {
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
	$('#date-format-select').on('change', function() {
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
	var translations = {
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
		var charLimit = parseInt($('#review-char-limit').val(), 10); // Get character limit
		var fullText = $element.data('full-text'); // Get the stored full text

		if (charLimit && fullText.length > charLimit) {
			var trimmedText = fullText.substring(0, charLimit) + '... ';
			$element.html(trimmedText + `<a href="#" class="read-more-link">${translations[lang]}</a>`);
			
			// Re-apply the "Read more" click event
			$element.find('.read-more-link').on('click', function (e) {
				e.preventDefault();
				$element.text(fullText);
			});
		} else {
			$element.text(fullText); // Show full text if no limit
		}
	}

	// On character limit input change
	$('#review-char-limit').on('input', function () {
		var charLimit = parseInt($(this).val(), 10); // Get character limit from input
		var lang = $('#language-select').val(); // Get selected language

		// Loop through each content block and apply the limit and language
		$('.content').each(function () {
			var $this = $(this);
			var fullText = $this.data('full-text') || $this.text(); // Store the original full text

			// Only store full text once to prevent resetting to trimmed text
			if (!$this.data('full-text')) {
				$this.data('full-text', fullText);
			}

			updateReadMoreLink($this, lang); // Update the Read more link with the correct language
		});
	});

	   // Function to update displayed dates based on selected language and format
	   function updateDisplayedDates() {
		const lang = $('#language-select').val(); // Get selected language
		const format = $('#date-format-select').val(); // Get selected date format
	
		$('.date').each(function () {
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
	$('#language-select').on('change', function () {
		var lang = $(this).val(); // Get selected language
		updateDisplayedDates(); // Re-render dates when language changes

		// Loop through each content block and update the Read more link with the new language
		$('.content').each(function () {
			var $this = $(this);
			updateReadMoreLink($this, lang);
		});
	});

	$('#date-format-select').on('change', updateDisplayedDates); // Ensure dates update on format change

	// Toggle for Google Review link
    $('#toggle-google-review').on('change', function() {
        if ($(this).is(':checked')) {
            $('#google-review-section').fadeIn();
            $('#color-picker-options').fadeIn(); // Show color picker options
        } else {
            $('#google-review-section').fadeOut();
            $('#color-picker-options').fadeOut(); // Hide color picker options
        }
    });

	 // Color picker for background color
	 $('#bg-color-picker').on('input', function() {
        var bgColor = $(this).val();
        $('#google-review-section').css('background-color', bgColor);
    });

    // Color picker for text color
    $('#text-color-picker').on('input', function() {
        var textColor = $(this).val();
        $('#google-review-section').css('color', textColor);
    });

	const updateInputField = () => {
		const keywords = [];
		$('#keywords-list .keyword-item').each(function () {
			keywords.push($(this).text().trim().replace(' ✖', ''));
		});
	};
	
	$('#keywords-input').on('keypress', function (e) {
		if (e.which === 13) { // Check for Enter key
			e.preventDefault(); // Prevent default form submission
	
			// Get the input value and split it into keywords
			const newKeywords = $(this).val().split(',').map(keyword => keyword.trim()).filter(keyword => keyword);
	
			// Get the current number of keywords in the list
			const currentKeywordsCount = $('#keywords-list .keyword-item').length;
	
			// Check if adding new keywords exceeds the limit of 5
			if (currentKeywordsCount + newKeywords.length > 5) {
				$('#error-message').show(); // Show the error message
				return; // Stop further execution
			} else {
				$('#error-message').hide(); // Hide the error message if under limit
			}
	
			$(this).val(''); // Clear input field
	
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
	
					// Update input field
					updateInputField();
	
					// Set up click event to remove keyword
					removeButton.on('click', function () {
						keywordItem.remove(); // Remove keyword from list
						updateInputField(); // Update input field after removal
					});
				}
			});
		}
	});		
	
});

