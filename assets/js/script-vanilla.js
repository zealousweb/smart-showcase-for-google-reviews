(function () {
	"use strict";
	
	function initSwiperSlider() {
		const sliderConfigs = {
			".zwssgr-slider-1": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
			".zwssgr-slider-2": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
			".zwssgr-slider-3": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
			".zwssgr-slider-4": { slidesPerView: 1, slidesPerGroup: 1 },
			".zwssgr-slider-5": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
			".zwssgr-slider-6": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
		};
	
		Object.keys(sliderConfigs).forEach(selector => {
			const sliderElements = document.querySelectorAll(selector);
		
			if (sliderElements.length > 0) {
				const parentElement = sliderElements[0].parentElement;    
				sliderElements.forEach(sliderElement => {
					const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
					const config = sliderConfigs[selector];
					const minSlidesRequired = (config.slidesPerView || 1) + 1;
					const enableLoop = slideCount >= minSlidesRequired;
		
					new Swiper(sliderElement, {
						slidesPerView: config.slidesPerView,
						slidesPerGroup: config.slidesPerGroup,
						spaceBetween: 20,
						loop: enableLoop,
						navigation: {
							nextEl: parentElement.querySelector(".swiper-button-next"),
							prevEl: parentElement.querySelector(".swiper-button-prev"),
						},
						breakpoints: config.breakpoints || {},
					});
				});
			}
		});
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

	const sliderConfigs = {
        ".zwssgr-slider-1": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-2": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-3": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-4": { slidesPerView: 1, slidesPerGroup: 1 },
        ".zwssgr-slider-5": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-6": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
    };
	
	// Store Swiper instances in an object
	let swiperInstances = {};

	function reinitializeAllSwipers(container) {
		// Ensure container is a valid HTML element
		if (!(container instanceof HTMLElement)) {
			console.error(`Invalid container element!`, container);
			return;
		}
	
		// Loop through all configured Swiper sliders
		Object.keys(sliderConfigs).forEach(selector => {
			const sliderElements = container.querySelectorAll(selector); // Get all sliders within the container
	
			sliderElements.forEach(sliderElement => {
				const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
				const config = sliderConfigs[selector];
				const minSlidesRequired = (config.slidesPerView || 1) + 1;
				const enableLoop = slideCount >= minSlidesRequired;
	
				// Destroy existing Swiper instance if it exists
				if (swiperInstances[selector]) {
					swiperInstances[selector].destroy(true, true);
				}
	
				// Initialize new Swiper instance
				swiperInstances[selector] = new Swiper(sliderElement, {
					slidesPerView: config.slidesPerView,
					slidesPerGroup: config.slidesPerGroup,
					spaceBetween: 20,
					loop: enableLoop,
					navigation: {
						nextEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-next"),
						prevEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-prev"),
					},
					breakpoints: config.breakpoints || {},
				});
			});
		});
	}

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


	document.addEventListener('click', function (event) {
		if (event.target.closest('.load-more-meta')) {
			const button = event.target.closest('.load-more-meta');
			const mainWrapper = button.closest('.zwssgr-main-wrapper');
	
			if (!mainWrapper) return;
	
			let page = button.getAttribute('data-page'); // Get the current page number
			let postId = button.getAttribute('data-post-id'); // Get the post-id from the button data attribute
			let selectedValue = mainWrapper.querySelector('.front-sort-by-select')?.value || '';
			let selectedKeywordElement = mainWrapper.querySelector('.zwssgr-front-keywords-list li.selected');
			let keyword = selectedKeywordElement ? selectedKeywordElement.getAttribute('data-zwssgr-keyword') : '';
			let popupContentContainer = mainWrapper.querySelector('zwssgr-slider.zwssgr-grid-item.zwssgr-popup-list');
	
			// Disable the button to prevent multiple clicks
			button.setAttribute('disabled', true);
			button.textContent = 'Loading...';
	
			// AJAX request using Fetch API
			const xhr = new XMLHttpRequest();
			xhr.open('POST', load_more.ajax_url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4) {
					if (xhr.status === 200) {
						const response = JSON.parse(xhr.responseText);
	
						if (response.success) {
							// Append new content to the popup if applicable
							if (popupContentContainer) {
								popupContentContainer.insertAdjacentHTML('beforeend', response.data.content);
							}
	
							let container = document.querySelector(`#div-container[data-widget-id="${postId}"]`);
	
							if (container) {
								let listContainer = container.querySelector('.zwssgr-list');
								let gridContainer = container.querySelector('.zwssgr-grid-item');
	
								if (listContainer) {
									listContainer.insertAdjacentHTML('beforeend', response.data.content);
								}
								if (gridContainer) {
									gridContainer.insertAdjacentHTML('beforeend', response.data.content);
								}
							}
	
							// Update the page number for future requests
							button.setAttribute('data-page', response.data.new_page);
	
							// If no more posts, remove or disable the button
							if (response.data.disable_button) {
								button.remove();
							} else {
								button.removeAttribute('disabled');
								button.textContent = 'Load More';
							}
						} else {
							button.removeAttribute('disabled');
							button.textContent = 'Error, try again';
						}
					} else {
						button.removeAttribute('disabled');
						button.textContent = 'Error, try again';
					}
				}
			};
	
			const params = new URLSearchParams();
			params.append('action', 'zwssgr_load_more_meta_data');
			params.append('post_id', postId);
			params.append('page', page);
			params.append('front_sort_by', selectedValue);
			params.append('front_keyword', keyword);
			params.append('nonce', load_more.nonce);
	
			xhr.send(params);
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

	document.addEventListener('click', function (event) {
    
		if (event.target.closest('.zwssgr-front-keywords-list li')) {
			
			const clickedElement = event.target.closest('.zwssgr-front-keywords-list li');
			const mainWrapper = clickedElement.closest('.zwssgr-main-wrapper');
	
			mainWrapper.querySelectorAll('.zwssgr-front-keywords-list li').forEach(li => li.classList.remove('selected'));
			clickedElement.classList.add('selected');
	
			const postId = mainWrapper.getAttribute('data-widget-id');
	
			if (!postId) {
				console.warn('Post ID not found for the selected element.');
				return;
			}
	
			const keyword = clickedElement.getAttribute('data-zwssgr-keyword');
			const selectedValue = mainWrapper.querySelector('.front-sort-by-select')?.value || '';
	
			const mainDivWrapper = mainWrapper.querySelector('.zwssgr-front-review-filter-wrap')?.nextElementSibling;
			if (!mainDivWrapper) return;
	
			const list_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-list');
			const grid_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-grid-item');
	
			const ratingFilter = mainDivWrapper.getAttribute('data-rating-filter');
			const layoutType = mainDivWrapper.getAttribute('data-layout-type');
			const bg_color_load = mainDivWrapper.getAttribute('data-bg-color');
			const text_color_load = mainDivWrapper.getAttribute('data-text-color');
			const enable_load_more = mainDivWrapper.getAttribute('data-enable-load-more');
	
			if (enable_load_more === "1") {
				window.zwssgrLoadMoreButton = `<button class="load-more-meta zwssgr-load-more-btn" data-page="2" data-post-id="${postId}" data-rating-filter="${ratingFilter}" style="background-color: ${bg_color_load}; color: ${text_color_load};">Load More</button>`;
			}
	
			mainDivWrapper.querySelectorAll('.load-more-meta').forEach(button => button.style.display = 'none');
	
			const xhr = new XMLHttpRequest();
			xhr.open('POST', load_more.ajax_url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
					const response = JSON.parse(xhr.responseText);
	
					if (!response.data.content || response.data.content.trim() === '') {
						const errMsg = `<p class="zwssgr-no-found-message">${response.data.err_msg}</p>`;
	
						if (list_to_apnd) list_to_apnd.innerHTML = errMsg;
						if (grid_to_apnd) grid_to_apnd.innerHTML = errMsg;
						
						['.zwssgr-slider-1', '.zwssgr-slider-2', '#zwssgr-slider3', '.zwssgr-slider-4', '.zwssgr-slider-5', '.zwssgr-slider-6'].forEach(selector => {
							const element = mainDivWrapper.querySelector(selector);
							if (element) element.innerHTML = errMsg;
						});
	
						return;
					}
	
					if (list_to_apnd) {
						list_to_apnd.innerHTML = ''; 
						list_to_apnd.insertAdjacentHTML('beforeend', response.data.content);
					}
	
					if (grid_to_apnd) {
						grid_to_apnd.innerHTML = ''; 
						grid_to_apnd.insertAdjacentHTML('beforeend', response.data.content);
					}
	
					['.zwssgr-slider-1', '.zwssgr-slider-2', '#zwssgr-slider3', '.zwssgr-slider-4', '.zwssgr-slider-5', '.zwssgr-slider-6'].forEach(selector => {
						const element = mainDivWrapper.querySelector(selector);
						if (element) {
							setTimeout(() => reinitializeAllSwipers(element), 100);
							element.innerHTML = ''; 
							element.insertAdjacentHTML('beforeend', response.data.content);
						}
					});
	
					if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 
						'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5'].includes(layoutType)) {
						if (response.data.disable_button !== true) {
							mainDivWrapper.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
						}
					}
	
					if (['popup-1', 'popup-2'].includes(layoutType)) {
						if (response.data.disable_button !== true) {
							document.querySelector('.scrollable-content')?.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
						}
					}
				}
			};
	
			const params = new URLSearchParams();
			params.append('action', 'zwssgr_load_more_meta_data');
			params.append('front_keyword', keyword);
			params.append('post_id', postId);
			params.append('front_sort_by', selectedValue);
			params.append('nonce', load_more.nonce);
	
			xhr.send(params);
		}
	});

	document.addEventListener('change', function (event) {
		if (event.target.matches('.front-sort-by-select')) {
			const mainWrapper = event.target.closest('.zwssgr-main-wrapper');
	
			if (!mainWrapper) return;
	
			const postId = mainWrapper.getAttribute('data-widget-id');
	
			if (!postId) {
				console.error('Post ID not found');
				return;
			}
	
			const selectedValue = event.target.value;
			const selectedKeywordElement = mainWrapper.querySelector('.zwssgr-front-keywords-list li.selected');
			const keyword = selectedKeywordElement ? selectedKeywordElement.getAttribute('data-zwssgr-keyword') : '';
	
			const mainDivWrapper = mainWrapper.querySelector('.zwssgr-front-review-filter-wrap')?.nextElementSibling;
			if (!mainDivWrapper) return;
	
			const list_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-list');
			const grid_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-grid-item');
	
			const ratingFilter = mainDivWrapper.getAttribute('data-rating-filter');
			const layoutType = mainDivWrapper.getAttribute('data-layout-type');
			const bg_color_load = mainDivWrapper.getAttribute('data-bg-color');
			const text_color_load = mainDivWrapper.getAttribute('data-text-color');
			const enable_load_more = mainDivWrapper.getAttribute('data-enable-load-more');
	
			if (enable_load_more === "1") {
				window.zwssgrLoadMoreButton = `<button class="load-more-meta zwssgr-load-more-btn" data-page="2" data-post-id="${postId}" data-rating-filter="${ratingFilter}" style="background-color: ${bg_color_load}; color: ${text_color_load};">Load More</button>`;
			}
	
			mainDivWrapper.querySelectorAll('.load-more-meta').forEach(button => button.remove());
	
			const xhr = new XMLHttpRequest();
			xhr.open('POST', load_more.ajax_url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
					const response = JSON.parse(xhr.responseText);
	
					if (!response.data.content || response.data.content.trim() === '') {
						const errMsg = `<p class="zwssgr-no-found-message">${response.data.err_msg}</p>`;
	
						if (list_to_apnd) list_to_apnd.innerHTML = errMsg;
						if (grid_to_apnd) grid_to_apnd.innerHTML = errMsg;
	
						['.zwssgr-slider-1', '.zwssgr-slider-2', '#zwssgr-slider3', '.zwssgr-slider-4', '.zwssgr-slider-5', '.zwssgr-slider-6'].forEach(selector => {
							const element = mainDivWrapper.querySelector(selector);
							if (element) element.innerHTML = errMsg;
						});
	
						return;
					}
	
					if (list_to_apnd) {
						list_to_apnd.innerHTML = '';
						list_to_apnd.insertAdjacentHTML('beforeend', response.data.content);
					}
	
					if (grid_to_apnd) {
						grid_to_apnd.innerHTML = '';
						grid_to_apnd.insertAdjacentHTML('beforeend', response.data.content);
					}
	
					['.zwssgr-slider-1', '.zwssgr-slider-2', '#zwssgr-slider3', '.zwssgr-slider-4', '.zwssgr-slider-5', '.zwssgr-slider-6'].forEach(selector => {
						const element = mainDivWrapper.querySelector(selector);
						if (element) {
							setTimeout(() => reinitializeAllSwipers(element), 100);
							element.innerHTML = '';
							element.insertAdjacentHTML('beforeend', response.data.content);
						}
					});
	
					if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5',
						 'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5'].includes(layoutType)) {
						if (response.data.disable_button !== true) {
							mainDivWrapper.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
						}
					}
	
					if (['popup-1', 'popup-2'].includes(layoutType)) {
						if (response.data.disable_button !== true) {
							document.querySelector('.scrollable-content')?.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
						}
					}
				}
			};
	
			const params = new URLSearchParams();
			params.append('action', 'zwssgr_load_more_meta_data');
			params.append('front_sort_by', selectedValue);
			params.append('post_id', postId);
			params.append('front_keyword', keyword);
			params.append('nonce', load_more.nonce);
	
			xhr.send(params);
		}
	});
	
	
});
