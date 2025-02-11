document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get("page");
    const tab = urlParams.get("tab");

    function toggleElements() {
        const elements = [
            { checkbox: "review-title", target: ".zwssgr-title" },
            { checkbox: "review-rating", target: ".zwssgr-rating" },
            { checkbox: "review-days-ago", target: ".zwssgr-days-ago" },
            { checkbox: "review-content", target: ".zwssgr-content" },
            { checkbox: "review-photo", target: ".zwssgr-profile" },
            { checkbox: "review-g-icon", target: ".zwssgr-google-icon" }
        ];
    
        elements.forEach(({ checkbox, target }) => {
            const checkboxElement = document.getElementById(checkbox);
            const targetElements = document.querySelectorAll(target);
    
            if (checkboxElement && targetElements.length) {
                const shouldShow = !checkboxElement.checked;
                targetElements.forEach(el => el.style.display = shouldShow ? 'block' : 'none');
            }
        });
    }

    // Attach change event listeners to checkboxes
    document.querySelectorAll('input[name="review-element"]').forEach(checkbox => {
        checkbox.addEventListener('change', toggleElements);
    });

    // Call toggleElements on page load to apply any initial settings with fade effect
    if (page === "zwssgr_widget_configurator" && tab === "tab-selected") {
        // console.log("Condition met, calling toggleElements...");
        toggleElements();
    }

});