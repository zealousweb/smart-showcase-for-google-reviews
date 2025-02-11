document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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

});