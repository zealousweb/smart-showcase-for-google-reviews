document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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
});