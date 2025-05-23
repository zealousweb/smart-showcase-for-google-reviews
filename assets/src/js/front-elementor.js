import { initializeSwipers } from './swiper.js';

(function () {
	"use strict";
	
	document.addEventListener('DOMContentLoaded', function () {
		initializeSwipers();
	});

	// Reinitialize Swiper when Elementor updates preview
	window.addEventListener('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			initializeSwipers();
		});
	});
})();
