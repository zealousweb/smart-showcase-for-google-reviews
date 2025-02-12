document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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

});