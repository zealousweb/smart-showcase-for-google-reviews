document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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

});