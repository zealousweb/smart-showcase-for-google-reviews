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
	
});
