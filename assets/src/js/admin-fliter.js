import { toggleElements } from './hide-element.js';
import { reinitializeAllSwipers } from './swiper.js';
import { updateDisplayedDates, updateReadMoreLink } from './review-filter.js';
document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    } 

    document.querySelectorAll('.star-filter').forEach(star => {
        star.addEventListener('click', function () {
            let rating = parseInt(this.dataset.rating, 10);
            let allStars = document.querySelectorAll('.star-filter');

            if (this.classList.contains('selected') && rating === 1) {
                allStars.forEach(star => {
                    star.classList.remove('selected');
                    star.querySelector('.star').style.fill = '#ccc';
                });
                return;
            }

            allStars.forEach(star => {
                let currentRating = parseInt(star.dataset.rating, 10);
                if (currentRating <= rating) {
                    star.classList.add('selected');
                    star.querySelector('.star').style.fill = '#f39c12';
                } else {
                    star.classList.remove('selected');
                    star.querySelector('.star').style.fill = '#ccc';
                }
            });
        });
    });


    document.addEventListener('click', function (event) {
        // Check if the clicked element is #sort-by-select or has the .star-filter class inside .filter-rating
        if (event.target.matches('#sort-by-select') || event.target.closest('.filter-rating .star-filter')) {
    
            let nonce = zwssgr_filter_reviews.nonce;
            let postId = getQueryParam('zwssgr_widget_id');
            let sortByElement = document.querySelector('#sort-by-select');
            let sortBy = sortByElement ? sortByElement.value : ''; // Get selected sort by value
            let selectedOption = getQueryParam('selectedOption');
    
            let selectedRatings = [];
            document.querySelectorAll('.filter-rating .star-filter.selected').forEach(function(star) {
                selectedRatings.push(star.getAttribute('data-rating')); // Push each selected rating into the array
            });

            // Convert ratings to numbers
            selectedRatings = selectedRatings.map(Number);

            // If nothing is selected, default to all ratings (1-5 stars)
            if (selectedRatings.length === 1) {
                selectedRatings = [1];
            } else if (selectedRatings.length === 2) {
                selectedRatings = [2];
            } else if (selectedRatings.length === 3) {
                selectedRatings = [3];
            } else if (selectedRatings.length === 4) {
                selectedRatings = [4];
            } else if (selectedRatings.length === 5) {
                selectedRatings = [5];
            } else {
                selectedRatings = [1, 2, 3, 4, 5];
            }
    
            // Create form data
            let formData = new FormData();
            formData.append('action', 'zwssgr_filter_reviews'); // The action for the PHP handler
            formData.append('zwssgr_widget_id', postId);
            formData.append('rating_filter', JSON.stringify(selectedRatings)); // Pass the selected ratings array as JSON
            formData.append('sort_by', sortBy); // Pass sort by parameter
            formData.append('nonce', nonce);
    
            // Make the AJAX request to filter reviews based on selected ratings
            let xhr = new XMLHttpRequest();
            xhr.open('POST', zwssgr_filter_reviews.ajax_url, true);
    
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 400) {
                    let response = xhr.responseText;
    
                    // Ensure the response is HTML or clean content
                    if (typeof response === "string" || response instanceof String) {
                        let displayElement = document.querySelector('#selected-option-display');
                        if (displayElement) {
                            // console.log("Updating #selected-option-display");
                            displayElement.innerHTML = response;
                        } else {
                            console.error("#selected-option-display element not found!");
                        }
                    } else {
                        console.error("Expected HTML content, but received:", response);
                    }
    
                    // Only reinitialize Slick slider if selectedOption is one of the slider options
                    if (['slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6'].includes(selectedOption)) {
                        setTimeout(function () {
                            reinitializeAllSwipers(document.querySelector('#selected-option-display'));
                        }, 100);
                    }
                    toggleElements();
            
                    updateDisplayedDates(); // Ensure dates are updated after new content is loaded

                    let lang = document.getElementById('language-select').value;
                    document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                        let fullText = element.getAttribute('data-full-text') || element.textContent;
                        if (!element.getAttribute('data-full-text')) {
                            element.setAttribute('data-full-text', fullText);
                        }
                        updateReadMoreLink(element, lang);
                    });
            
                    // Handle list layout reinitialization (if needed)
                    if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5'].includes(selectedOption)) {
                        // console.log("List layout filtered");
                    }

                } else {
                    console.error("AJAX Error: ", xhr.statusText, "Status Code:", xhr.status);
                }
            };
    
            xhr.onerror = function () {
                console.error("AJAX request failed");
            };
    
            xhr.send(formData);
        }
    });
});
