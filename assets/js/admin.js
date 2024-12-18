jQuery(document).ready(function($) {


	// Bind click event to open popup
	$(document).on('click', '.zwsgr-popup-item', function () {
		var popupId = $(this).data('popup'); // Get the popup ID from the data attribute
		$('#' + popupId).fadeIn(); // Show the popup
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

	// Bind keydown event to close popup when ESC key is pressed
	$(document).on('keydown', function (e) {
		if (e.key === "Escape" || e.keyCode === 27) {
			$('.zwsgr-popup-overlay').fadeOut(); // Hide the popup
		}
	});

	$('.copy-shortcode-icon').on('click', function () {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);

        if ($input.length) {
            $input.select(); // Select the input field text
            document.execCommand('copy'); // Copy the selected text
            $(this).addClass('dashicons-yes'); // Change icon to a checkmark
            setTimeout(() => {
                $(this).removeClass('dashicons-yes').addClass('dashicons-admin-page'); // Reset icon after 2 seconds
            }, 2000);
        }
    });
	

	var widget_post_type = 'zwsgr_data_widget';

    // Check if we're on the edit, new post, or the custom layout page for the widget post type
    if ($('body.post-type-' + widget_post_type).length || 
        $('body.post-php.post-type-' + widget_post_type).length || 
        $('body.post-new-php.post-type-' + widget_post_type).length || 
        window.location.href.indexOf('admin.php?page=zwsgr_widget_configurator') !== -1) {

        // Ensure the parent menu (dashboard) is highlighted as active
        $('.toplevel_page_zwsgr_dashboard')
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-current-submenu wp-menu-open');

        // Ensure the specific submenu item for zwsgr_data_widget is active
        $('ul.wp-submenu li a[href="edit.php?post_type=' + widget_post_type + '"]')
            .parent('li')
            .addClass('current');
    }

	//SEO and Notification Email Toggle 
	var toggle = $('#zwsgr_admin_notification_enabled');
	var notificationFields = $('.zwsgr-notification-fields');
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
		saveSelectedOption(currentDisplayOption); // Save the selected display option
	});

	// Function to save the selected display option and layout option via AJAX
	function saveSelectedOption(option) {
		var postId = getQueryParam('zwsgr_widget_id');
		var settings = $('.tab-item.active').attr('data-tab');
		var selectedLayout = $('.zwsgr-option-item:visible .select-btn.selected').data('option'); // Get selected layout option

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
				console.log('Display and layout option saved:', response);
			},
			error: function(error) {
				console.error('Error saving options:', error);
			}
		});
	}

	// Function to show/hide options based on the selected radio button
	function updateOptions(value) {
		$('.zwsgr-option-item').each(function () {
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
	var activeTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
	var selectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL

	// Initially show the active tab content
	$('.tab-content').hide(); // Hide all tab content
	$('#' + activeTab).show(); // Show the active tab content

	$('.tab-item').removeClass('active');
	$('.tab-item[data-tab="' + activeTab + '"]').addClass('active');

	// If there's a selected option in the URL, display it in the "Selected Option" tab
	if (selectedOption && activeTab === 'tab-selected') {
		var selectedOptionElement = $('#' + selectedOption);
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

		// Get existing query parameters
		var selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists
		var postId = getQueryParam('zwsgr_widget_id'); // Get the post_id from the URL if it exists

		// Start building the new URL with page and tab parameters
		var newUrl = currentUrl + '?page=zwsgr_widget_configurator&tab=' + tabId;

		// Add selectedOption to the URL if it exists
		if (selectedOption) {
			newUrl += '&selectedOption=' + selectedOption;
		}

		// Add post_id to the URL if it exists
		if (postId) {
			newUrl += '&zwsgr_widget_id=' + postId;
		}

		// Redirect to the new URL
		window.location.href = newUrl;
	});

	// Handle click events for "Select Option" buttons
    $('.select-btn').on('click', function() {
        var optionId = $(this).data('option');
        var postId = getQueryParam('zwsgr_widget_id');
        var currentUrl = window.location.href.split('?')[0];

        if (!postId) {
            alert('Post ID not found!');
            return;
        }

		// Fetch the HTML for the selected option using the correct optionId
		var selectedOptionElement = $('#' + optionId); // Clone the selected option's element
		$('#selected-option-display').html(selectedOptionElement); // Update the display area
		$('#selected-option-display').find('.select-btn').remove(); // Remove the select button from the cloned HTML
	
		// Get the outer HTML of the selected element
		var selectedHtml = selectedOptionElement.prop('outerHTML');

		// Get the current display option (assuming you have a variable for this)
		var displayOption = $('input[name="display_option"]:checked').val(); // Or adjust according to your setup
		var settings = $('.tab-item.active').attr('data-tab');
		var currentTab = $('.tab-item.active').data('tab'); // Get the current active tab

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
					alert('Layout option saved successfully!');
				} else {
					alert('Failed to save layout option.');
				}
			},
			error: function() {
				alert('An error occurred.');
			}
		});

        // Append post_id and selected option to the URL
        window.location.href = currentUrl + '?page=zwsgr_widget_configurator&tab=tab-selected&selectedOption=' + optionId + '&zwsgr_widget_id=' + postId;
    });

    // Handle the Save & Get Code Button
    $('#save-get-code-btn').on('click', function() {
        var selectedOption = getQueryParam('selectedOption');
        var postId = getQueryParam('zwsgr_widget_id');
        var currentUrl = window.location.href.split('?')[0];

        if (!postId) {
            alert('Post ID not found!');
            return;
        }

        // Redirect to the "Generated Shortcode" tab with selected option and post_id
        window.location.href = currentUrl + '?page=zwsgr_widget_configurator&tab=tab-shortcode&selectedOption=' + selectedOption + '&zwsgr_widget_id=' + postId;
    });

	// Function to reinitialize the selected Slick Slider
	function reinitializeSlickSlider(container) {
		// Find and reinitialize Slick sliders
		var slider1 = $(container).find('.zwsgr-slider-1');
		var slider2 = $(container).find('.zwsgr-slider-2');
		var slider3 = $(container).find('.zwsgr-slider-3');
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

	// Slick sliders
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

	$('.zwsgr-slider-3').slick({
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

	// Handle click on visibility toggle icon of REview CPT
	$('.zwsgr-toggle-visibility').on('click', function(e) {
		e.preventDefault();

		var post_id = $(this).data('post-id');
		var $icon = $(this).find('.dashicons');

		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			async: false,  // Make the request synchronous
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

	$('#toggle-google-review').on('change', function() {
        // Update button colors based on the color pickers
        var bgColor = $('#bg-color-picker').val();
        var textColor = $('#text-color-picker').val();

        $('.zwsgr-google-toggle').css({
            'background-color': bgColor,
            'color': textColor
        });
    });

    // When the background color picker changes
    $('#bg-color-picker').on('input', function() {
        var bgColor = $(this).val();
        $('.zwsgr-google-toggle').css('background-color', bgColor);
    });

    // When the text color picker changes
    $('#text-color-picker').on('input', function() {
        var textColor = $(this).val();
        $('.zwsgr-google-toggle').css('color', textColor);
    });

	function toggleButtonVisibility() {
		if ($('#toggle-google-review').is(':checked')) {
			$('.zwsgr-google-toggle').show(); // Show the button
		} else {
			$('.zwsgr-google-toggle').hide(); // Hide the button
		}
	}

	// Run the function when the page loads
	toggleButtonVisibility();

	// Run the function whenever the checkbox state changes
	$('#toggle-google-review').on('change', toggleButtonVisibility);

	// Function to hide or show elements with a smooth effect
    function toggleElements() {
        $('#review-title').is(':checked') ? $('.selected-option-display .zwsgr-title').fadeOut(600) : $('.selected-option-display .zwsgr-title').fadeIn(600);
        $('#review-rating').is(':checked') ? $('.selected-option-display .zwsgr-rating').fadeOut(600) : $('.selected-option-display .zwsgr-rating').fadeIn(600);
        $('#review-days-ago').is(':checked') ? $('.selected-option-display .zwsgr-days-ago').fadeOut(600) : $('.selected-option-display .zwsgr-days-ago').fadeIn(600);
        $('#review-content').is(':checked') ? $('.selected-option-display .zwsgr-content').fadeOut(600) : $('.selected-option-display .zwsgr-content').fadeIn(600);
		$('#review-photo').is(':checked') ? $('.selected-option-display .zwsgr-profile').fadeOut(600) : $('.selected-option-display .zwsgr-profile').fadeIn(600);
		$('#review-g-icon').is(':checked') ? $('.selected-option-display .zwsgr-google-icon').fadeOut(600) : $('.selected-option-display .zwsgr-google-icon').fadeIn(600);
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
		$('.zwsgr-content').each(function () {
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
	
		$('.zwsgr-date').each(function () {
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
		$('.zwsgr-content').each(function () {
			var $this = $(this);
			updateReadMoreLink($this, lang);
		});
	});

	$('#date-format-select').on('change', updateDisplayedDates); // Ensure dates update on format change

	// Toggle for Google Review link
    $('#toggle-google-review').on('change', function() {
        if ($(this).is(':checked')) {

            $('#color-picker-options').fadeIn(); // Show color picker options
        } else {
            $('#color-picker-options').fadeOut(); // Hide color picker options
        }
    });

	// Toggle for Lode More
	$('#enable-load-more').on('change', function () {
        if ($(this).is(':checked')) {
            // If checkbox is checked, fade in the color picker options
            $('#zwsgr-load-color-picker-options').fadeIn();
        } else {
            // If checkbox is unchecked, fade out the color picker options
            $('#zwsgr-load-color-picker-options').fadeOut();
        }
    });

	if ($('#enable-load-more').is(':checked')) {
        $('#zwsgr-load-color-picker-options').show();
    } else {
        $('#zwsgr-load-color-picker-options').hide();
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

    // Handle the enter key press to add keywords
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

                    // Update hidden input field
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

    // Set up click event to remove existing keywords (on page load)
    $('#keywords-list').on('click', '.remove-keyword', function () {
        $(this).parent('.keyword-item').remove(); // Remove the clicked keyword
        updateInputField(); // Update the hidden input after removal
    });
	
	// Save the all Widget and Generate the shortcode
	$('#save-get-code-btn').on('click', function(e) {
		e.preventDefault();
	
		var postId = getQueryParam('zwsgr_widget_id'); // Get post_id from the URL
		var displayOption = $('input[name="display_option"]:checked').val();
		var selectedElements = $('input[name="review-element"]:checked').map(function() {
			return $(this).val();
		}).get();
		// var ratingFilter = $('.star-filter.selected').data('rating') || '';
		var keywords = $('#keywords-list .keyword-item').map(function() {
			return $(this).text().trim().replace(' ✖', '');
		}).get();
		var dateFormat = $('#date-format-select').val();
		var charLimit = $('#review-char-limit').val();
		var language = $('#language-select').val();
		var sortBy = $('#sort-by-select').val();
		var enableLoadMore = $('#enable-load-more').is(':checked') ? 1 : 0;
		var googleReviewToggle = $('#toggle-google-review').is(':checked') ? 1 : 0;
		var bgColor = $('#bg-color-picker').val();
		var textColor = $('#text-color-picker').val();
		var bgColorLoad = $('#bg-color-picker_load').val();
		var textColorLoad = $('#text-color-picker_load').val();
		var settings = $('.tab-item.active').attr('data-tab');
		var postsPerPage = $('#posts-per-page').val();
		// Fetch the selected star rating from the star filter
		var selectedRating = $('.star-filter.selected').last().data('rating') || 0; // Fetch the rating, or default to 0
		var currentTab2 = $('.tab-item.active').data('tab'); // Get the current active tab
		var customCSS = $('.zwsgr-textarea').val();
		var enableSortBy = $('#enable-sort-by-filter').is(':checked') ? 1 : 0; 

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
					alert('Settings and shortcode saved successfully.');
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log('AJAX Error: ', textStatus, errorThrown);
				alert('An error occurred while saving data. Details: ' + textStatus + ': ' + errorThrown);
			}
		});
	});

	$("#fetch-gmb-data #fetch-gmd-accounts").on("click", function (e) {
		e.preventDefault();
		const zwsgr_button = $(this);
		const zwsgr_gmb_data_type = zwsgr_button.data("fetch-type");

		$('#fetch-gmb-data .progress-bar').css('display', 'block');
	
		zwsgr_button.prop("disabled", true);
		zwsgr_button.html('<span class="spinner is-active"></span> Fetching...');
	
		processBatch(zwsgr_gmb_data_type);
	});
	
	$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").on("click", function (e) {
		
		e.preventDefault();

		$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").prop('disabled', true);
		$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").html("Connecting...");

		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: "zwsgr_fetch_oauth_url"
			},
			success: function (response) {

				if (response.success) {
					
					$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").prop('disabled', false);
					$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").html("Redirecting...");

					// Redirect to the OAuth URL
					window.location.href = response.data.zwsgr_oauth_url;

				} else {

					$("#fetch-gmb-auth-url-response").html("<p class='error response''>Error generating OAuth URL: " + (response.data?.message || "Unknown error") + "</p>");

				}
			},
			error: function (xhr, status, error) {

				$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").prop('disabled', false);
				$("#fetch-gmb-auth-url-wrapper #fetch-gmb-auth-url").html("Connect with Google");

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
		var zwsgr_delete_plugin_data = $('#disconnect-gmb-auth #delete-all-data').prop('checked') ? '1' : '0';

		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: "zwsgr_delete_oauth_connection",
				zwsgr_delete_plugin_data: zwsgr_delete_plugin_data,
				security: zwsgr_admin.zwsgr_delete_oauth_connection,
			},
			success: function (response) {

				if (response.success) {
					
					$("#disconnect-gmb-auth #disconnect-auth").prop('disabled', false);
					$("#disconnect-gmb-auth #disconnect-auth").html("Disconnected");

					$("#disconnect-gmb-auth-response").html("<p class='success response''> OAuth Disconnected: " + (response.data?.message || "Unknown error") + "</p>");

					$("#disconnect-gmb-auth .zwsgr-caution-div").fadeOut();
					$("#disconnect-gmb-auth .danger-note").fadeOut();

					// Reload the page after a 1-second delay
					setTimeout(function() {
						location.reload();
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

	function zwsgr_validate_email(email) {
		var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailPattern.test(email);
	}
	
	  $("#fetch-gmb-data #fetch-gmd-reviews").on("click", function (e) {
		e.preventDefault();
		const zwsgr_button = $(this);
		const zwsgr_gmb_data_type = zwsgr_button.data("fetch-type");
	
		// Get selected account and location from the dropdowns
		const zwsgr_account_number = $(
		  "#fetch-gmb-data #zwsgr-account-select"
		).val();
		$("#fetch-gmb-data #zwsgr-account-select").addClass('disabled');

		const zwsgr_account_name = $(
			"#fetch-gmb-data #zwsgr-account-select option:selected"
		).text();

		const zwsgr_location_number = $(
		  "#fetch-gmb-data #zwsgr-location-select"
		).val();

		const zwsgr_location_name = $(
			"#fetch-gmb-data #zwsgr-location-select option:selected"
		).text();

		const zwsgr_location_new_review_uri = $(
			"#fetch-gmb-data #zwsgr-location-select option:selected"
		).attr("data-new-review-url");
		
		$("#fetch-gmb-data #zwsgr-location-select").addClass('disabled');

		const zwsgr_widget_id = zwsgr_getUrlParameter("zwsgr_widget_id");

		if (!zwsgr_account_number) {
			$('#fetch-gmb-data .response').html('<p class="error">Account is required</p>');
			return;
		}

		if (!zwsgr_location_number) {
			$('#fetch-gmb-data .response').html('<p class="error">Location is required</p>');
			return;
		}

		if (!zwsgr_widget_id) {
			alert("Please select an approprite location.");
			$('#fetch-gmb-data .response').html('<p class="error">No valid widget ID found</p>');
			return;
		}

		$('#fetch-gmb-data .response').html('');

		$('#fetch-gmb-data .progress-bar').css('display', 'block');
	
		zwsgr_button.addClass("disabled");
		zwsgr_button.html('<span class="spinner is-active"></span> Fetching...');
	
		processBatch(
		  zwsgr_gmb_data_type,
		  zwsgr_account_number,
		  zwsgr_location_number,
		  zwsgr_widget_id,
		  zwsgr_location_name,
		  zwsgr_location_new_review_uri,
		  zwsgr_account_name
		);
	  });
	
	  // Function to get URL parameter by name
	  function zwsgr_getUrlParameter(name) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(name);
	  }
	
	  // Listen for changes in the account dropdown and process batch if changed
	  $("#fetch-gmb-data #zwsgr-account-select").on("change", function () {
		const zwsgr_account_number = $(this).val();
		const zwsgr_account_name = $(this).find("option:selected").text();
		
		if (zwsgr_account_number) {

		  // Add loading spinner and disable the dropdown to prevent multiple selections
		  $(this).prop("disabled", true);
		  $("#fetch-gmb-data #zwsgr-location-select").remove();
		  $("#fetch-gmb-data #fetch-gmd-reviews").remove();
	
		  const zwsgr_widget_id = zwsgr_getUrlParameter("zwsgr_widget_id");
		  
		  $('#fetch-gmb-data .response').html('');
		  $('#fetch-gmb-data .progress-bar').css('display', 'block');
	
		  // Assuming 'zwsgr_gmb_locations' as the data type for fetching locations on account change
		  processBatch(
			"zwsgr_gmb_locations",
			zwsgr_account_number,
			null,
			zwsgr_widget_id,
			null,
			null,
			zwsgr_account_name
		  );

		}		

	  });
	
	  function processBatch(
		zwsgr_gmb_data_type,
		zwsgr_account_number,
		zwsgr_location_number,
		zwsgr_widget_id,
		zwsgr_location_name,
		zwsgr_location_new_review_uri,
		zwsgr_account_name
	  ) {
		$.ajax({
		  url: zwsgr_admin.ajax_url,
		  type: "POST",
		  dataType: "json",
		  data: {
			action: "zwsgr_fetch_gmb_data",
			security: zwsgr_admin.zwsgr_queue_manager_nounce,
			zwsgr_gmb_data_type: zwsgr_gmb_data_type,
			zwsgr_account_number: zwsgr_account_number,
			zwsgr_location_number: zwsgr_location_number,
			zwsgr_widget_id: zwsgr_widget_id,
			zwsgr_location_name: zwsgr_location_name,
			zwsgr_location_new_review_uri: zwsgr_location_new_review_uri,
			zwsgr_account_name: zwsgr_account_name
		  },
		  success: function (response) {

			if (response.success) {
				$('#fetch-gmb-data .progress-bar').fadeIn();
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

	  // Check if we're on the specific page URL that contains zwsgr_widget_id dynamically
	  if (window.location.href.indexOf('admin.php?page=zwsgr_widget_configurator&tab=tab-fetch-data&zwsgr_widget_id=') !== -1) {
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

		// Capture 'zwsgr_widget_id' from the URL
		const zwsgr_widget_id = getUrlParameter('zwsgr_widget_id');

		$.ajax({
		  url: zwsgr_admin.ajax_url,
		  method: "POST",
		  data: {
			action: "zwsgr_get_batch_processing_status",
			security: zwsgr_admin.zwsgr_queue_manager_nounce,
			zwsgr_widget_id: zwsgr_widget_id
		  },
		  success: function (response) {

			if (response.success && response.data.zwgr_data_processing_init == 'false' && response.data.zwgr_data_sync_once == 'true') {
				
				$('#fetch-gmb-data .progress-bar #progress').val(100);
				$('#fetch-gmb-data .progress-bar #progress-percentage').text(Math.round(100) + '%');
				$('#fetch-gmb-data .progress-bar #progress-percentage').text('Processed');

				if (response.data.zwsgr_gmb_data_type == 'zwsgr_gmb_locations') {

					$('#fetch-gmb-data .response').html('<p class="success">Locations processed successfully</p>');

				} else if (response.data.zwsgr_gmb_data_type == 'zwsgr_gmb_reviews') {

					$('#fetch-gmb-data .response').html('<p class="success">Reviews processed successfully</p>');
					$('#fetch-gmb-data #fetch-gmd-reviews').html('Fetched');

				}
				
				setTimeout(function () {
					$('#fetch-gmb-data .progress-bar').fadeOut();
					if (response.data.zwsgr_gmb_data_type === 'zwsgr_gmb_reviews') {
						redirectToOptionsTab();
					} else {
						location.reload();
					}
				}, 2000);

			} else {
				 
				// Use the batch progress directly from the response
				var zwsgr_batch_progress = response.data.zwsgr_batch_progress;

				// Check if zwsgr_batch_progress is a valid number
				if (!isNaN(zwsgr_batch_progress) && zwsgr_batch_progress >= 0 && zwsgr_batch_progress <= 100) {
					
					// Update the progress bar with the batch progress
					$('#fetch-gmb-data .progress-bar #progress').val(zwsgr_batch_progress);
					$('#fetch-gmb-data .progress-bar #progress-percentage').text(Math.round(zwsgr_batch_progress) + '%');

				} else {

					console.error('Invalid batch progress:', zwsgr_batch_progress);
					
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
		var zwsgr_reply_comment = $("#gmb-review-data textarea[name='zwsgr_reply_comment']").val();

		if (zwsgr_reply_comment.trim() === "") {
            $("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p>' + 'Please enter a valid reply.' + '</p></div>');
            return;
        }
		
		if (zwsgr_reply_comment.trim().length > 4086) {
			$("#gmb-review-data #json-response-message").html('<div class="notice notice-error"><p>' + 'Reply cannot exceed 4086 characters.' + '</p></div>');
			return;
		}

		var loader = $('<span class="loader is-active" style="margin-left: 10px;"></span>');
		var buttons = $("#gmb-review-data #add-reply, #gmb-review-data #update-reply, #gmb-review-data #delete-reply");
	
		// Send AJAX request to handle the add / update reply request
		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwsgr_add_update_review_reply',
				zwsgr_wp_review_id: zwsgr_admin.zwsgr_wp_review_id,
				zwsgr_reply_comment: zwsgr_reply_comment,
				security: zwsgr_admin.zwsgr_add_update_reply_nonce
			},
			beforeSend: function() {
				buttons.addClass('disabled');
				$("#gmb-review-data textarea[name='zwsgr_reply_comment']").prop('readonly', true);
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
	
		// Send AJAX request to handle the reply update
		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwsgr_delete_review_reply',
				zwsgr_wp_review_id: zwsgr_admin.zwsgr_wp_review_id,
				security: zwsgr_admin.zwsgr_delete_review_reply
			},
			beforeSend: function() {
				buttons.addClass('disabled');
				$("#gmb-review-data textarea[name='zwsgr_reply_comment']").prop('readonly', true);
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

	$("#gmb-data-filter #zwsgr-account-select").on("change", function (e) {

		e.preventDefault();

		// Get the selected value
		var zwsgr_account_number = $(this).val();
		$(this).addClass('disabled');
	
		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwsgr_gmb_dashboard_data_filter',
				zwsgr_account_number: zwsgr_account_number,
				security: zwsgr_admin.zwsgr_gmb_dashboard_filter
			},
			success: function(response) {
				$('#gmb-data-filter #zwsgr-location-select').remove();
				if (response.success) {
					$('#gmb-data-filter').append(response.data);
					//$("#gmb-data-filter #zwsgr-location-select").trigger('change');
				}
			},
			complete: function() {
				$("#gmb-data-filter #zwsgr-account-select").removeClass('disabled');
			},
		});
	
	});

	$(".zwgr-dashboard").on("change", "#zwsgr-account-select, #zwsgr-location-select", function (e) {

		// Declare variables globally or in the appropriate scope
		let zwsgr_range_filter_data = null;
		let zwsgr_range_filter_type = null;

		if ($('.zwsgr-filters-wrapper .zwsgr-filter-item .zwsgr-filter-button.active').length > 0) {
			zwsgr_range_filter_type = 'rangeofdays';
			zwsgr_range_filter_data = $('.zwsgr-filters-wrapper .zwsgr-filter-item .zwsgr-filter-button.active').text().trim().toLowerCase();
		} else if ($('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"].active').length > 0) {
			zwsgr_range_filter_type = 'rangeofdate';
			zwsgr_range_filter_data = $('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').val().trim();
		}

		zwsgr_render_data_callback(e, zwsgr_range_filter_data, zwsgr_range_filter_type);

	});

	$(".zwgr-dashboard").on("click", ".zwsgr-filters-wrapper .zwsgr-filter-item .zwsgr-filter-button", function (e) {
		var zwsgr_range_filter_data = $(this).text().trim().toLowerCase();
		zwsgr_render_data_callback(e, zwsgr_range_filter_data, 'rangeofdays');
	});

	$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
		
		$('.zwsgr-filters-wrapper .zwsgr-filter-item .zwsgr-filter-button').removeClass('active');
		$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').addClass('active');
		
		var zwsgr_start_date = picker.startDate ? picker.startDate.format('DD-MM-YYYY') : null;
		var zwsgr_end_date = picker.endDate ? picker.endDate.format('DD-MM-YYYY') : null;

		var zwsgr_range_filter_data = zwsgr_start_date && zwsgr_end_date ? zwsgr_start_date + ' - ' + zwsgr_end_date : null;

		zwsgr_render_data_callback(ev, zwsgr_range_filter_data, 'rangeofdate');
	});

	var zwsgr_chart;
	var zwsgr_data;
	var zwsgr_options;

	google.charts.load('current', {'packages':['corechart']});

	google.charts.setOnLoadCallback(zwsgr_draw_chart);
	
	function zwsgr_draw_chart(zwsgr_chart_data) {

		var zwsgr_all_zero = zwsgr_chart_data.every(function(row) {
			return row[1] === 0;
		});
	
		if (zwsgr_all_zero) {
			document.getElementById('zwsgr_chart_wrapper').innerHTML = 
				'<div style="text-align: center; font-size: 16px; color: #888; padding: 50px;">No enough data available</div>';
			return;
		}

		zwsgr_chart_data.unshift(['Rating', 'Number of Reviews']);

		var zwsgr_data = google.visualization.arrayToDataTable(zwsgr_chart_data);

		var zwsgr_options = {
			pieHole: 0.4,
			width: 276,
			height: 276,
			legend: 'none',
			chartArea: {
				width: '90%',
				height: '90%'
			},
			colors: ['#F08C3C', '#3CAAB4', '#A9C6CC', '#285064', '#F44336'],
			backgroundColor: 'transparent'
		};

		var zwsgr_chart = new google.visualization.PieChart(document.getElementById('zwsgr_chart_wrapper'));
		zwsgr_chart.draw(zwsgr_data, zwsgr_options);

	}
	
	// Redraw chart on window resize
	window.addEventListener('resize', function() {
		if (zwsgr_chart) {
			zwsgr_chart.draw(zwsgr_data, zwsgr_options);
		}
	});

	function zwsgr_render_data_callback(e, zwsgr_range_filter_data, zwsgr_range_filter_type) {

		e.preventDefault();

		$zwsgr_chart_wrapper = $('#zwsgr_chart_wrapper').outerHeight(true);

		var zwsgr_gmb_account_div = $("#gmb-data-filter #zwsgr-account-select");
		var zwsgr_gmb_location_div = $("#gmb-data-filter #zwsgr-location-select");

		if ($(e.target).is("#gmb-data-filter #zwsgr-location-select")) {
			zwsgr_gmb_account_div.addClass('disabled');
			zwsgr_gmb_location_div.addClass('disabled');
		}

		var zwsgr_gmb_account_number   = zwsgr_gmb_account_div.val();
		var zwsgr_gmb_account_location = zwsgr_gmb_location_div.val();

		var zwsgr_filter_data = {
			zwsgr_gmb_account_number: zwsgr_gmb_account_number,
			zwsgr_gmb_account_location: zwsgr_gmb_account_location,
			zwsgr_range_filter_type: zwsgr_range_filter_type,
			zwsgr_range_filter_data: zwsgr_range_filter_data
		};
	
		$.ajax({
			url: zwsgr_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'zwsgr_data_render',
				zwsgr_filter_data: zwsgr_filter_data,
				security: zwsgr_admin.zwsgr_data_render
			},
			beforeSend: function() {
				var minHeight = $('.zwgr-dashboard #render-dynamic').outerHeight(true) || 200;
				
				// Fade out existing content smoothly before appending the loader
				$('.zwgr-dashboard #render-dynamic').fadeOut(300, function() {
					$(this).remove();
					// Fade in the loader smoothly
					$('.zwgr-dashboard').append('<div class="loader-outer-wrapper" style="height:' + minHeight + 'px;"><div class="loader"></div></div>');	
				});

			},
			success: function(response) {
				if (response.success) {
					
					
					$('.zwgr-dashboard').append(response.data.html);

					// Fade in the newly appended content smoothly
					$('.zwgr-dashboard').children(':last').hide().fadeIn(300);

					if (response.data.zwsgr_chart_data) {
						setTimeout(function() {
							google.charts.setOnLoadCallback(zwsgr_draw_chart(response.data.zwsgr_chart_data));
						}, 500);
					} else {
					}

				} else {
					$('.zwgr-dashboard ').html('<p>Error loading data.</p>');
				}
			},
			complete: function() {

				// Fade out existing content smoothly before appending the loader
				$('.zwgr-dashboard .loader-outer-wrapper').fadeOut(300, function() {
					$('.zwgr-dashboard .loader-outer-wrapper').remove();
				});

				zwsgr_gmb_account_div.removeClass('disabled');
				zwsgr_gmb_location_div.removeClass('disabled');
			},
			error: function() {
				$('#render-dynamic').html('<p>An error occurred while fetching data.</p>');
			}
		});
	}

	$('.star-filter').on('click', function () {
		var rating = $(this).data('rating'); // Get the rating of the clicked star
	
		// Check if the clicked star is already selected and is the lowest rating
		if ($(this).hasClass('selected') && rating === 1) {
			// Unselect all stars
			$('.star-filter').removeClass('selected');
			$('.star-filter .star').css('fill', '#ccc'); // Reset color to unselected
			return;
		}
	
		// Toggle the 'selected' state of stars
		$('.star-filter').each(function () {
			var currentRating = $(this).data('rating');
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
		var ratingFilterValue = rating;
		console.log("Selected rating:", ratingFilterValue);
	});
	

	// Event listener for clicking on a star filter
	$(document).on('click', '#sort-by-select,.filter-rating .star-filter' , function() {
		var selectedRating = $(this).data('rating'); // Get the selected rating
		var nonce = filter_reviews.nonce;
		var postId = getQueryParam('zwsgr_widget_id');
		var sortBy = $('#sort-by-select').val(); // Get the selected sort by value
		// Prepare an array of selected ratings
		var selectedRatings = [];
		$('.filter-rating .star-filter.selected').each(function() {
			selectedRatings.push($(this).data('rating')); // Push each selected rating into the array
		});

		// If nothing is selected, default to all ratings (1-5 stars)
		if (selectedRatings.length === 0) {
			selectedRatings = [1, 2, 3, 4, 5];
		}

		// Make the AJAX request to filter reviews based on selected ratings
		$.ajax({
			type: 'POST',
			url: filter_reviews.ajax_url,
			data: {
				action: 'filter_reviews', // The action for the PHP handler
				zwsgr_widget_id: postId,
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

				 // Use getQueryParam to extract the 'selectedOption' parameter from the URL
				 var selectedOption = getQueryParam('selectedOption');

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
		var slider1 = $(container).find('.zwsgr-slider-1');
		var slider2 = $(container).find('.zwsgr-slider-2');
		var slider3 = $(container).find('.zwsgr-slider-3');
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
    $('.zwgr-dashboard .zwsgr-filters-wrapper .zwsgr-filter-button').on('click', function () {
        // Remove active class from all buttons and add it to the clicked button
		$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').removeClass('active');
		$('.zwgr-dashboard .zwsgr-filters-wrapper .zwsgr-filter-button').removeClass('active');
        $(this).addClass('active');		
    });
	
	$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').daterangepicker({
		opens: 'center',
		locale: {
			format: 'DD-MM-YYYY'
		},
		autoUpdateInput: false,
		minDate: moment('1995-01-01', 'YYYY-MM-DD'),
		maxDate: moment(),
	}, function(start, end, label) {
		$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').val(
			start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY')
		);
	});
	
	// Set custom placeholder for gmb-dashboard-filter
	$('.zwgr-dashboard .zwsgr-filters-wrapper input[name="dates"]').attr('placeholder', 'Custom');

	$(document).on('click', '.toggle-content', function () {
        var $link = $(this);
        var fullText = $link.data('full-text');
		console.log(fullText);
        var $parentParagraph = $link.closest('p');
    
        // Replace the trimmed content with the full content
        $parentParagraph.html(fullText);
    });

	// Start code SMTP
	function zwsgr_update_Smtp_Port() {
        var encryptionType = $('input[name="zwsgr_smtp_ency_type"]:checked').val();
        switch(encryptionType) {
            case 'none':
                $('#zwsgr-smtp-port').val('25'); // Set port to 25 for 'None'
                break;
            case 'ssl':
                $('#zwsgr-smtp-port').val('465'); // Set port to 465 for 'SSL'
                break;
            case 'tls':
                $('#zwsgr-smtp-port').val('587'); // Set port to 587 for 'TLS'
                break;
            default:
                $('#zwsgr-smtp-port').val('25'); // Default port
        }
    }
	$('input[name="zwsgr_smtp_ency_type"]').change(function() {
        zwsgr_update_Smtp_Port(); // Update the port when the encryption type is changed
    });
	if ($('#zwsgr_smtp_auth_1').is(':checked')) {
        $('.zwsgr-smtp-auth-enable').hide(); // Hide if 'No' is selected
		$('input[name="zwsgr_smtp_username"]').removeAttr('required');
		$('input[name="zwsgr_smtp_password"]').removeAttr('required');
	} else {
        $('.zwsgr-smtp-auth-enable').show(); // Show if 'Yes' is selected
		$('input[name="zwsgr_smtp_username"]').attr('required', 'required');
		$('input[name="zwsgr_smtp_password"]').attr('required', 'required');
    }
    $('input[name="zwsgr_smtp_auth"]').change(function() {
        if ($(this).val() == 'no') {
            $('.zwsgr-smtp-auth-enable').hide(); // Hide if 'No' is selected
			$('input[name="zwsgr_smtp_username"]').removeAttr('required');
			$('input[name="zwsgr_smtp_password"]').removeAttr('required');
        } else {
            $('.zwsgr-smtp-auth-enable').show(); // Show if 'Yes' is selected
			$('input[name="zwsgr_smtp_username"]').attr('required', 'required');
			$('input[name="zwsgr_smtp_password"]').attr('required', 'required');
        }
    }); 
	// End code SMTP
	

	$('#zwsgr-account-select').on('change', function () {
		$(this).closest('form').submit();
	});


	
});