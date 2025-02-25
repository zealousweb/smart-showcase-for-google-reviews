document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // Ensure the input element exists before adding the event listener
    const zwssgrInput = document.querySelector(".zwssgr-shortcode-input");
    
    if (!zwssgrInput) {
        // console.error("Element with class 'zwssgr-shortcode-input' not found.");
        return; // Exit script if element is not found
    }

    zwssgrInput.addEventListener("input", function () {
        const zwssgrinputValue = this.value.trim(); // Get input value
        const zwssgrsuccessDiv = document.getElementById("zwssgr-success-response");
        const zwssgrerrorDiv = document.getElementById("zwssgr-error-response");

        // Regular expressions to extract values
        const zwssgrpostIdMatch = zwssgrinputValue.match(/post-id=["'](\d+)["']/);
        const zwssgrlayoutMatch = zwssgrinputValue.match(/layout=["']([\w-]+)["']/);
        const zwssgrlayoutOptionMatch = zwssgrinputValue.match(/layout-option=["']([\w-]+)["']/);

        // Extract values or set defaults
        const zwssgrpostId = zwssgrpostIdMatch ? zwssgrpostIdMatch[1] : '';
        const zwssgrlayout = zwssgrlayoutMatch ? zwssgrlayoutMatch[1] : '';
        const zwssgrlayoutOption = zwssgrlayoutOptionMatch ? zwssgrlayoutOptionMatch[1] : '';

        // Define allowed layout-option pairs
        const zwssgrvalidLayouts = {
            "slider": ["slider-1", "slider-2", "slider-3", "slider-4", "slider-5", "slider-6", "slider-7", "slider-8"],
            "grid": ["grid-1", "grid-2", "grid-3", "grid-4", "grid-5", "grid-6", "grid-7"],
            "list": ["list-1", "list-2", "list-3", "list-4", "list-5", "list-6", "list-7"],
            "badge": ["badge-1", "badge-2", "badge-3", "badge-4", "badge-5", "badge-6", "badge-7", "badge-8", "badge-9", "badge-10", "badge-11"],
            "popup": ["popup-1", "popup-2"]
        };

        // Validate layout and layout-option
        if (!zwssgrvalidLayouts[zwssgrlayout] || !zwssgrvalidLayouts[zwssgrlayout].includes(zwssgrlayoutOption)) {
            if (zwssgrerrorDiv) {
                zwssgrsuccessDiv.style.display = "none";
                zwssgrerrorDiv.innerText = "layout or layout-option do not match!";
                zwssgrerrorDiv.style.display = "block";

                // Hide error message after 10 seconds
                setTimeout(function () {
                    zwssgrerrorDiv.style.display = "none";
                    zwssgrerrorDiv.innerText = "";
                }, 30000); // 10000ms = 10 seconds
            }
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", ajaxurl, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const zwssgrresponse = JSON.parse(xhr.responseText);
                    if (zwssgrresponse.success) {
                        if (zwssgrerrorDiv) zwssgrerrorDiv.style.display = "none"; // Hide error message
                        if (zwssgrsuccessDiv) {
                            zwssgrsuccessDiv.innerText = zwssgrresponse.data.message;
                            zwssgrsuccessDiv.style.display = "block";

                            setTimeout(function () {
                                zwssgrsuccessDiv.style.display = "none";
                                zwssgrsuccessDiv.innerText = "";
                            }, 3000);
                        }

                        // **Redirect after successful response**
                        if (zwssgrresponse.data.redirect_url) {
                            setTimeout(function () {
                                window.location.href = zwssgrresponse.data.redirect_url;
                            }, 3000);
                        }
                    } else {
                        if (zwssgrsuccessDiv) zwssgrsuccessDiv.style.display = "none"; // Hide success message
                        if (zwssgrerrorDiv) {
                            zwssgrerrorDiv.innerText = zwssgrresponse.data.message;
                            zwssgrerrorDiv.style.display = "block";

                            setTimeout(function () {
                                zwssgrerrorDiv.style.display = "none";
                                zwssgrerrorDiv.innerText = "";
                            }, 3000);
                        }
                    }
                } catch (e) {
                    console.error("Invalid JSON response", e);
                }
            }
        };

        // Send data via AJAX (include post-id)
        xhr.send("action=zwssgr_update_shortcode&post_id=" + encodeURIComponent(zwssgrpostId) +
                 "&layout=" + encodeURIComponent(zwssgrlayout) + 
                 "&layout_option=" + encodeURIComponent(zwssgrlayoutOption));
    });
});
