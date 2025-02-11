document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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