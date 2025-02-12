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
