(function () {
	"use strict";
	
	function initSwiperSlider() {
		// Initialize Swiper for Slider 1
		const slider1 = document.querySelector('.zwssgr-slider-1');
		if (slider1 && !slider1.classList.contains('swiper-initialized')) {
			new Swiper('.zwssgr-slider-1', {
				slidesPerView: 1,
				slidesPerGroup: 1,
				loop: true,
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',
				},
				breakpoints: {
					1200: {
						slidesPerView: 3,
						slidesPerGroup: 3,
					},
                    768: {
						slidesPerView: 2,
						slidesPerGroup: 2,
					},
					480: {
						slidesPerView: 1,
						slidesPerGroup: 1,
					}
				}
			});
		}

		// // Initialize Swiper for Slider 2
		// const slider2 = document.querySelector('.zwssgr-slider-2');
		// if (slider2 && !slider2.classList.contains('swiper-initialized')) {
		// 	new Swiper('.zwssgr-slider-2', {
		// 		slidesPerView: 3,
		// 		slidesPerGroup: 3,
		// 		loop: true,
		// 		navigation: {
		// 			nextEl: '.swiper-button-next',
		// 			prevEl: '.swiper-button-prev',
		// 		},
		// 		breakpoints: {
		// 			1200: {
		// 				slidesPerView: 2,
		// 				slidesPerGroup: 2,
		// 			},
		// 			480: {
		// 				slidesPerView: 1,
		// 				slidesPerGroup: 1,
		// 			}
		// 		}
		// 	});
		// }

		// // Initialize Swiper for Slider 3
		// const slider3 = document.querySelector('.zwssgr-slider-3');
		// if (slider3 && !slider3.classList.contains('swiper-initialized')) {
		// 	new Swiper('.zwssgr-slider-3', {
		// 		slidesPerView: 2,
		// 		slidesPerGroup: 2,
		// 		loop: true,
		// 		navigation: {
		// 			nextEl: '.swiper-button-next',
		// 			prevEl: '.swiper-button-prev',
		// 		},
		// 		breakpoints: {
		// 			1180: {
		// 				slidesPerView: 1,
		// 				slidesPerGroup: 1,
		// 			}
		// 		}
		// 	});
		// }

		// // Initialize Swiper for Slider 4
		// const slider4 = document.querySelector('.zwssgr-slider-4');
		// if (slider4 && !slider4.classList.contains('swiper-initialized')) {
		// 	new Swiper('.zwssgr-slider-4', {
		// 		slidesPerView: 1,
		// 		slidesPerGroup: 1,
		// 		loop: true,
		// 		navigation: {
		// 			nextEl: '.swiper-button-next',
		// 			prevEl: '.swiper-button-prev',
		// 		},
		// 	});
		// }

		// // Initialize Swiper for Slider 5
		// const slider5 = document.querySelector('.zwssgr-slider-5');
		// if (slider5 && !slider5.classList.contains('swiper-initialized')) {
		// 	new Swiper('.zwssgr-slider-5', {
		// 		slidesPerView: 2,
		// 		slidesPerGroup: 2,
		// 		loop: true,
		// 		navigation: {
		// 			nextEl: '.swiper-button-next',
		// 			prevEl: '.swiper-button-prev',
		// 		},
		// 		breakpoints: {
		// 			480: {
		// 				slidesPerView: 1,
		// 				slidesPerGroup: 1,
		// 			}
		// 		}
		// 	});
		// }

		// // Initialize Swiper for Slider 6
		// const slider6 = document.querySelector('.zwssgr-slider-6');
		// if (slider6 && !slider6.classList.contains('swiper-initialized')) {
		// 	new Swiper('.zwssgr-slider-6', {
		// 		slidesPerView: 3,
		// 		slidesPerGroup: 3,
		// 		loop: true,
		// 		navigation: {
		// 			nextEl: '.swiper-button-next',
		// 			prevEl: '.swiper-button-prev',
		// 		},
		// 		breakpoints: {
		// 			1200: {
		// 				slidesPerView: 2,
		// 				slidesPerGroup: 2,
		// 			},
		// 			480: {
		// 				slidesPerView: 1,
		// 				slidesPerGroup: 1,
		// 			}
		// 		}
		// 	});
		// }
	}

	document.addEventListener('DOMContentLoaded', function () {
		initSwiperSlider();
	});

	// Reinitialize Swiper when Elementor updates preview
	window.addEventListener('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			initSwiperSlider();
		});
	});
})();


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


    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('load-more-meta')) {
            const mainWrapper = e.target.closest('.zwssgr-main-wrapper');
            let button = e.target;
            let page = button.getAttribute('data-page');  // Get the current page number
            let postId = button.getAttribute('data-post-id');  // Get the post-id from the button data attribute
            let selectedValue = mainWrapper.querySelector('.front-sort-by-select')?.value;
            let keywordElement = mainWrapper.querySelector('.zwssgr-front-keywords-list li.selected');
            let keyword = keywordElement ? keywordElement.getAttribute('data-zwssgr-keyword') : null;
            let popupContentContainer = mainWrapper.querySelector('zwssgr-slider.zwssgr-grid-item.zwssgr-popup-list');
    
            // Disable the button to prevent multiple clicks
            button.disabled = true;
            button.textContent = 'Loading...';
    
            // AJAX request
            fetch(load_more.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'zwssgr_load_more_meta_data',  // Action hook for AJAX
                    post_id: postId,  // Pass the post-id from the button
                    page: page,  // Pass the current page number
                    front_sort_by: selectedValue,
                    front_keyword: keyword,
                    nonce: load_more.nonce  // Include the nonce for security
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new content to the popup
                    if (popupContentContainer) {
                        popupContentContainer.insertAdjacentHTML('beforeend', data.data.content);
                    }
                    let container = document.querySelector('#div-container[data-widget-id="' + postId + '"]');
    
                    if (container) {
                        let list = container.querySelector('.zwssgr-list');
                        if (list) {
                            list.insertAdjacentHTML('beforeend', data.data.content);
                        }
                        let gridItem = container.querySelector('.zwssgr-grid-item');
                        if (gridItem) {
                            gridItem.insertAdjacentHTML('beforeend', data.data.content);
                        }
                    }
    
                    // Update the page number for future requests
                    button.setAttribute('data-page', data.data.new_page);
    
                    // If no more posts, remove or disable the button
                    if (data.data.disable_button) {
                        button.remove();  // Remove the button if no more posts
                    } else {
                        button.disabled = false;
                        button.textContent = 'Load More';
                    }
                } else {
                    button.disabled = false;
                    button.textContent = 'Error, try again';
                }
            })
            .catch(() => {
                button.disabled = false;
                button.textContent = 'Error, try again';
            });
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('toggle-content')) {
            let link = e.target;
            let fullText = link.getAttribute('data-full-text');
            let parentParagraph = link.closest('p');
    
            // Replace the trimmed content with the full content
            if (parentParagraph) {
                parentParagraph.innerHTML = fullText;
            }
        }
    });
});
