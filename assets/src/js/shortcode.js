document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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

});