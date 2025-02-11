document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    
    // Bind click event to open popup
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("zwssgr-total-review")) {
        return;
      }
      let popupItem = e.target.closest(".zwssgr-popup-item");
      if (popupItem) {
        let popupId = popupItem.dataset.popup; // Get the popup ID from the data attribute
        let popup = document.getElementById(popupId);
        if (popup) {
          popup.style.display = "block"; // Show the popup
        }
      }
    });
  
    // Bind click event to close popup when the close button is clicked
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("zwssgr-close-popup")) {
        let popupOverlay = e.target.closest(".zwssgr-popup-overlay");
        if (popupOverlay) {
          popupOverlay.style.display = "none"; // Hide the popup
        }
      }
    });
  
    // Bind click event to close popup when clicking outside the popup content
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("zwssgr-popup-overlay")) {
        e.target.style.display = "none"; // Hide the popup
      }
    });
  
    // Bind keydown event to close popup when ESC key is pressed
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" || e.keyCode === 27) {
        document.querySelectorAll(".zwssgr-popup-overlay").forEach(function (popup) {
          popup.style.display = "none"; // Hide all popups
        });
      }
    });
});