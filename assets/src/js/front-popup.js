document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    // Bind click event to open popup
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('zwssgr-popup-item')) {
            let popupId = e.target.getAttribute('data-popup'); // Get the popup ID from the data attribute
            
            if (popupId) {
                document.getElementById(popupId).style.display = 'block'; // Show the popup
            } else {
                console.log('not found');
            }
        }
    });

    // Bind click event to close popup when the close button is clicked
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('zwssgr-close-popup')) {
            let popupOverlay = e.target.closest('.zwssgr-popup-overlay');
            if (popupOverlay) {
                popupOverlay.style.display = 'none'; // Hide the popup
            }
        }
    });

    // Bind click event to close popup when clicking outside the popup content
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('zwssgr-popup-overlay')) {
            e.target.style.display = 'none'; // Hide the popup
        }
    });

    // Bind keydown event to close popup when ESC key is pressed
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            document.querySelectorAll('.zwssgr-popup-overlay').forEach(function (popup) {
                popup.style.display = 'none'; // Hide the popup
            });
        }
    });

});