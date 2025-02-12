document.addEventListener('DOMContentLoaded', function () {
    "use strict";

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
	
						['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(selector => {
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
	
					['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(selector => {
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