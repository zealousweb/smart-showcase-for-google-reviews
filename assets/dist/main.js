/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/js sync \\.js$":
/*!************************************************!*\
  !*** ./assets/src/js/ sync nonrecursive \.js$ ***!
  \************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var map = {
	"./admin-fliter.js": "./assets/src/js/admin-fliter.js",
	"./color-picker.js": "./assets/src/js/color-picker.js",
	"./deactivation-popup.js": "./assets/src/js/deactivation-popup.js",
	"./front-elementor.js": "./assets/src/js/front-elementor.js",
	"./front-keyword-filter.js": "./assets/src/js/front-keyword-filter.js",
	"./front-popup.js": "./assets/src/js/front-popup.js",
	"./front-sortby-filter.js": "./assets/src/js/front-sortby-filter.js",
	"./get-url-parameter.js": "./assets/src/js/get-url-parameter.js",
	"./hide-element.js": "./assets/src/js/hide-element.js",
	"./hide-show-review.js": "./assets/src/js/hide-show-review.js",
	"./index.js": "./assets/src/js/index.js",
	"./keyword-filter.js": "./assets/src/js/keyword-filter.js",
	"./load-more.js": "./assets/src/js/load-more.js",
	"./pin-review.js": "./assets/src/js/pin-review.js",
	"./plugin-menu.js": "./assets/src/js/plugin-menu.js",
	"./popup.js": "./assets/src/js/popup.js",
	"./process-batches.js": "./assets/src/js/process-batches.js",
	"./read-more.js": "./assets/src/js/read-more.js",
	"./redirect-to-options-tab.js": "./assets/src/js/redirect-to-options-tab.js",
	"./review-filter.js": "./assets/src/js/review-filter.js",
	"./seo-notification.js": "./assets/src/js/seo-notification.js",
	"./shortcode.js": "./assets/src/js/shortcode.js",
	"./smtp.js": "./assets/src/js/smtp.js",
	"./success-message.js": "./assets/src/js/success-message.js",
	"./swiper-bundle.js": "./assets/src/js/swiper-bundle.js",
	"./swiper.js": "./assets/src/js/swiper.js",
	"./tabbing.js": "./assets/src/js/tabbing.js",
	"./toogle-btn.js": "./assets/src/js/toogle-btn.js",
	"./update-shortcode.js": "./assets/src/js/update-shortcode.js"
};


function webpackContext(req) {
	var id = webpackContextResolve(req);
	return __webpack_require__(id);
}
function webpackContextResolve(req) {
	if(!__webpack_require__.o(map, req)) {
		var e = new Error("Cannot find module '" + req + "'");
		e.code = 'MODULE_NOT_FOUND';
		throw e;
	}
	return map[req];
}
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = "./assets/src/js sync \\.js$";

/***/ }),

/***/ "./assets/src/js/admin-fliter.js":
/*!***************************************!*\
  !*** ./assets/src/js/admin-fliter.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _hide_element_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./hide-element.js */ "./assets/src/js/hide-element.js");
/* harmony import */ var _swiper_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./swiper.js */ "./assets/src/js/swiper.js");
/* harmony import */ var _review_filter_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./review-filter.js */ "./assets/src/js/review-filter.js");



document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  function getQueryParam(param) {
    var urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }
  document.querySelectorAll('.star-filter').forEach(function (star) {
    star.addEventListener('click', function () {
      var rating = parseInt(this.dataset.rating, 10);
      var allStars = document.querySelectorAll('.star-filter');
      if (this.classList.contains('selected') && rating === 1) {
        allStars.forEach(function (star) {
          star.classList.remove('selected');
          star.querySelector('.star').style.fill = '#ccc';
        });
        return;
      }
      allStars.forEach(function (star) {
        var currentRating = parseInt(star.dataset.rating, 10);
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
      var nonce = zwssgr_filter_reviews.nonce;
      var postId = getQueryParam('zwssgr_widget_id');
      var sortByElement = document.querySelector('#sort-by-select');
      var sortBy = sortByElement ? sortByElement.value : ''; // Get selected sort by value
      var selectedOption = getQueryParam('selectedOption');
      var selectedRatings = [];
      document.querySelectorAll('.filter-rating .star-filter.selected').forEach(function (star) {
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
      var formData = new FormData();
      formData.append('action', 'zwssgr_filter_reviews'); // The action for the PHP handler
      formData.append('zwssgr_widget_id', postId);
      formData.append('rating_filter', JSON.stringify(selectedRatings)); // Pass the selected ratings array as JSON
      formData.append('sort_by', sortBy); // Pass sort by parameter
      formData.append('nonce', nonce);

      // Make the AJAX request to filter reviews based on selected ratings
      var xhr = new XMLHttpRequest();
      xhr.open('POST', zwssgr_filter_reviews.ajax_url, true);
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 400) {
          var response = xhr.responseText;

          // Ensure the response is HTML or clean content
          if (typeof response === "string" || response instanceof String) {
            var displayElement = document.querySelector('#selected-option-display');
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
          if (['slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6', 'slider-7', 'slider-8'].includes(selectedOption)) {
            setTimeout(function () {
              (0,_swiper_js__WEBPACK_IMPORTED_MODULE_1__.reinitializeAllSwipers)(document.querySelector('#selected-option-display'));
            }, 100);
          }
          (0,_hide_element_js__WEBPACK_IMPORTED_MODULE_0__.toggleElements)();
          (0,_review_filter_js__WEBPACK_IMPORTED_MODULE_2__.updateDisplayedDates)(); // Ensure dates are updated after new content is loaded

          var lang = document.getElementById('language-select').value;
          document.querySelectorAll('.zwssgr-content').forEach(function (element) {
            var fullText = element.getAttribute('data-full-text') || element.textContent;
            if (!element.getAttribute('data-full-text')) {
              element.setAttribute('data-full-text', fullText);
            }
            (0,_review_filter_js__WEBPACK_IMPORTED_MODULE_2__.updateReadMoreLink)(element, lang);
          });

          // Handle list layout reinitialization (if needed)
          // if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 'list-6', 'list-7'].includes(selectedOption)) {
          //     console.log("List layout filtered");
          // }
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

/***/ }),

/***/ "./assets/src/js/color-picker.js":
/*!***************************************!*\
  !*** ./assets/src/js/color-picker.js ***!
  \***************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Listen for changes on the checkbox
  document.addEventListener('change', function (event) {
    if (event.target.id === 'toggle-google-review') {
      // Update button colors based on the color pickers
      var bgColor = document.getElementById('bg-color-picker').value;
      var textColor = document.getElementById('text-color-picker').value;
      var buttons = document.querySelectorAll('.zwssgr-google-toggle');
      buttons.forEach(function (button) {
        button.style.backgroundColor = bgColor;
        button.style.color = textColor;
      });

      // Toggle button visibility
      toggleButtonVisibility();
    }
  });

  // When the background color picker changes
  document.addEventListener('input', function (event) {
    if (event.target.id === 'bg-color-picker') {
      var bgColor = event.target.value;
      var buttons = document.querySelectorAll('.zwssgr-google-toggle');
      buttons.forEach(function (button) {
        button.style.backgroundColor = bgColor;
      });
    }
  });

  // When the text color picker changes
  document.addEventListener('input', function (event) {
    if (event.target.id === 'text-color-picker') {
      var textColor = event.target.value;
      var buttons = document.querySelectorAll('.zwssgr-google-toggle');
      buttons.forEach(function (button) {
        button.style.color = textColor;
      });
    }
  });
  function toggleButtonVisibility() {
    var toggleCheckbox = document.getElementById('toggle-google-review');
    var buttons = document.querySelectorAll('.zwssgr-google-toggle');

    // Check if the checkbox element exists
    if (!toggleCheckbox) {
      // console.error("Element with ID 'toggle-google-review' not found.");
      return;
    }
    buttons.forEach(function (button) {
      button.style.display = toggleCheckbox.checked ? 'inline-block' : 'none';
    });
  }

  // Run the function when the page loads
  toggleButtonVisibility();

  // Run the function whenever the checkbox state changes
  var toggleCheckbox = document.getElementById('toggle-google-review');
  if (toggleCheckbox) {
    toggleCheckbox.addEventListener('change', toggleButtonVisibility);
  }

  // Fade In function
  function fadeIn(element) {
    element.style.opacity = 0;
    element.style.display = 'flex';
    var opacity = 0;
    var interval = setInterval(function () {
      if (opacity < 1) {
        opacity += 0.1;
        element.style.opacity = opacity;
      } else {
        clearInterval(interval);
      }
    }, 30);
  }

  // Fade Out function
  function fadeOut(element) {
    var opacity = 1;
    var interval = setInterval(function () {
      if (opacity > 0) {
        opacity -= 0.1;
        element.style.opacity = opacity;
      } else {
        clearInterval(interval);
        element.style.display = 'none';
      }
    }, 30);
  }
  document.body.addEventListener('change', function (event) {
    if (event.target && event.target.id === 'toggle-google-review') {
      var colorPickerOptions = document.getElementById('color-picker-options');
      if (event.target.checked) {
        colorPickerOptions.style.display = 'flex';
        fadeIn(colorPickerOptions);
      } else {
        fadeOut(colorPickerOptions);
      }
    }
  });
  document.body.addEventListener('change', function (event) {
    if (event.target && event.target.id === 'enable-load-more') {
      var loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');
      if (event.target.checked) {
        loadMoreOptions.style.display = 'flex';
        fadeIn(loadMoreOptions);
      } else {
        fadeOut(loadMoreOptions);
      }
    }
  });
});

/***/ }),

/***/ "./assets/src/js/deactivation-popup.js":
/*!*********************************************!*\
  !*** ./assets/src/js/deactivation-popup.js ***!
  \*********************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Deactivation Popup 
  document.addEventListener('click', function (event) {
    var target = event.target.closest('a[href*="deactivate"][href*="smart-showcase-for-google-reviews"]');
    if (target) {
      event.preventDefault(); // Prevent default action
      var deactivateUrl = target.getAttribute('href'); // Get the deactivation URL from the link

      // Show the deactivation confirmation popup
      document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'block';

      // Cancel Deactivation
      document.getElementById('zwssgr-plugin-cancel-deactivate').addEventListener('click', function () {
        document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
      }, {
        once: true
      });

      // Confirm Deactivation
      document.getElementById('zwssgr-plugin-confirm-deactivate').addEventListener('click', function () {
        var deletePluginDataCheckbox = document.getElementById('zwssgr-delete-plugin-data');
        var zwssgrDeletePluginData = deletePluginDataCheckbox.checked ? 1 : 0;
        if (zwssgrDeletePluginData) {
          if (!zwssgr_admin.ajax_url || !zwssgr_admin.zwssgr_delete_oauth_connection) {
            console.error("AJAX URL or security nonce is missing!");
            return;
          }
          var formData = new FormData();
          formData.append("action", "zwssgr_delete_oauth_connection");
          formData.append("zwssgr_delete_plugin_data", zwssgrDeletePluginData);
          formData.append("security", zwssgr_admin.zwssgr_delete_oauth_connection);
          fetch(zwssgr_admin.ajax_url, {
            method: "POST",
            body: formData
          })["finally"](function () {
            // Proceed to deactivate the plugin after AJAX completes
            document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
            window.location.href = deactivateUrl;
          });
        } else {
          // If checkbox is not checked, directly deactivate the plugin
          document.getElementById('zwssgr-plugin-deactivation-popup').style.display = 'none';
          window.location.href = deactivateUrl;
        }
      }, {
        once: true
      });
    }
  });
});

/***/ }),

/***/ "./assets/src/js/front-elementor.js":
/*!******************************************!*\
  !*** ./assets/src/js/front-elementor.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _swiper_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./swiper.js */ "./assets/src/js/swiper.js");

(function () {
  "use strict";

  document.addEventListener('DOMContentLoaded', function () {
    (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.initializeSwipers)();
  });

  // Reinitialize Swiper when Elementor updates preview
  window.addEventListener('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
      (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.initializeSwipers)();
    });
  });
})();

/***/ }),

/***/ "./assets/src/js/front-keyword-filter.js":
/*!***********************************************!*\
  !*** ./assets/src/js/front-keyword-filter.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _swiper_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./swiper.js */ "./assets/src/js/swiper.js");

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.addEventListener('click', function (event) {
    if (event.target.closest('.zwssgr-front-keywords-list li')) {
      var _mainWrapper$querySel, _mainWrapper$querySel2;
      document.querySelectorAll('.zwssgr-main-wrapper').forEach(function (element) {
        element.setAttribute('data-onload-first', 'false');
      });
      var clickedElement = event.target.closest('.zwssgr-front-keywords-list li');
      var mainWrapper = clickedElement.closest('.zwssgr-main-wrapper');
      mainWrapper.querySelectorAll('.zwssgr-front-keywords-list li').forEach(function (li) {
        return li.classList.remove('selected');
      });
      clickedElement.classList.add('selected');
      var postId = mainWrapper.getAttribute('data-widget-id');
      if (!postId) {
        console.warn('Post ID not found for the selected element.');
        return;
      }
      var keyword = clickedElement.getAttribute('data-zwssgr-keyword');
      var selectedValue = ((_mainWrapper$querySel = mainWrapper.querySelector('.front-sort-by-select')) === null || _mainWrapper$querySel === void 0 ? void 0 : _mainWrapper$querySel.value) || '';
      var mainDivWrapper = (_mainWrapper$querySel2 = mainWrapper.querySelector('.zwssgr-front-review-filter-wrap')) === null || _mainWrapper$querySel2 === void 0 ? void 0 : _mainWrapper$querySel2.nextElementSibling;
      if (!mainDivWrapper) return;
      var list_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-list');
      var grid_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-grid-item');
      var ratingFilter = mainDivWrapper.getAttribute('data-rating-filter');
      var layoutType = mainDivWrapper.getAttribute('data-layout-type');
      var bg_color_load = mainDivWrapper.getAttribute('data-bg-color');
      var text_color_load = mainDivWrapper.getAttribute('data-text-color');
      var enable_load_more = mainDivWrapper.getAttribute('data-enable-load-more');
      var isFirstLoad = mainWrapper.getAttribute('data-onload-first');

      // Set `data-onload-first` to false after first interaction
      mainWrapper.setAttribute('data-onload-first', 'false');
      if (enable_load_more === "1") {
        window.zwssgrLoadMoreButton = "<button class=\"load-more-meta zwssgr-load-more-btn\" data-page=\"2\" data-post-id=\"".concat(postId, "\" data-rating-filter=\"").concat(ratingFilter, "\" style=\"background-color: ").concat(bg_color_load, "; color: ").concat(text_color_load, ";\">Load More</button>");
      }
      mainDivWrapper.querySelectorAll('.load-more-meta').forEach(function (button) {
        return button.style.display = 'none';
      });
      var xhr = new XMLHttpRequest();
      xhr.open('POST', load_more.ajax_url, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (!response.data.content || response.data.content.trim() === '') {
            var errMsg = "<p class=\"zwssgr-no-found-message\">".concat(response.data.err_msg, "</p>");
            if (list_to_apnd) list_to_apnd.innerHTML = errMsg;
            if (grid_to_apnd) grid_to_apnd.innerHTML = errMsg;
            ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6', '#zwssgr-slider7', '#zwssgr-slider8'].forEach(function (selector) {
              var element = mainDivWrapper.querySelector(selector);
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
          ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6', '#zwssgr-slider7', '#zwssgr-slider8'].forEach(function (selector) {
            var element = mainDivWrapper.querySelector(selector);
            if (element) {
              setTimeout(function () {
                return (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.reinitializeAllSwipers)(element);
              }, 100);
              element.innerHTML = '';
              element.insertAdjacentHTML('beforeend', response.data.content);
            }
          });
          if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 'list-6', 'list-7', 'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5', 'grid-6', 'grid-7'].includes(layoutType)) {
            if (response.data.disable_button !== true) {
              mainDivWrapper.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
            }
          }
          if (['popup-1', 'popup-2'].includes(layoutType)) {
            if (response.data.disable_button !== true) {
              var _document$querySelect;
              (_document$querySelect = document.querySelector('.scrollable-content')) === null || _document$querySelect === void 0 || _document$querySelect.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
            }
          }
        }
      };
      var params = new URLSearchParams();
      params.append('action', 'zwssgr_load_more_meta_data');
      params.append('front_keyword', keyword);
      params.append('post_id', postId);
      params.append('front_sort_by', selectedValue);
      params.append('onload_first', isFirstLoad);
      params.append('nonce', load_more.nonce);
      xhr.send(params);
    }
  });
});

/***/ }),

/***/ "./assets/src/js/front-popup.js":
/*!**************************************!*\
  !*** ./assets/src/js/front-popup.js ***!
  \**************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Bind click event to open popup
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('zwssgr-popup-item')) {
      var popupId = e.target.getAttribute('data-popup'); // Get the popup ID from the data attribute

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
      var popupOverlay = e.target.closest('.zwssgr-popup-overlay');
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

/***/ }),

/***/ "./assets/src/js/front-sortby-filter.js":
/*!**********************************************!*\
  !*** ./assets/src/js/front-sortby-filter.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _swiper_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./swiper.js */ "./assets/src/js/swiper.js");

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.addEventListener('change', function (event) {
    if (event.target.matches('.front-sort-by-select')) {
      var _mainWrapper$querySel;
      document.querySelectorAll('.zwssgr-main-wrapper').forEach(function (element) {
        element.setAttribute('data-onload-first', 'false');
      });
      var mainWrapper = event.target.closest('.zwssgr-main-wrapper');
      if (!mainWrapper) return;
      var postId = mainWrapper.getAttribute('data-widget-id');
      if (!postId) {
        console.error('Post ID not found');
        return;
      }
      var selectedValue = event.target.value;
      var selectedKeywordElement = mainWrapper.querySelector('.zwssgr-front-keywords-list li.selected');
      var keyword = selectedKeywordElement ? selectedKeywordElement.getAttribute('data-zwssgr-keyword') : '';
      var mainDivWrapper = (_mainWrapper$querySel = mainWrapper.querySelector('.zwssgr-front-review-filter-wrap')) === null || _mainWrapper$querySel === void 0 ? void 0 : _mainWrapper$querySel.nextElementSibling;
      if (!mainDivWrapper) return;
      var list_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-list');
      var grid_to_apnd = mainDivWrapper.querySelector('.zwssgr-slider.zwssgr-grid-item');
      var ratingFilter = mainDivWrapper.getAttribute('data-rating-filter');
      var layoutType = mainDivWrapper.getAttribute('data-layout-type');
      var bg_color_load = mainDivWrapper.getAttribute('data-bg-color');
      var text_color_load = mainDivWrapper.getAttribute('data-text-color');
      var enable_load_more = mainDivWrapper.getAttribute('data-enable-load-more');
      var isFirstLoad = mainWrapper.getAttribute('data-onload-first');

      // Set `data-onload-first` to false after first interaction
      mainWrapper.setAttribute('data-onload-first', 'false');
      if (enable_load_more === "1") {
        window.zwssgrLoadMoreButton = "<button class=\"load-more-meta zwssgr-load-more-btn\" data-page=\"2\" data-post-id=\"".concat(postId, "\" data-rating-filter=\"").concat(ratingFilter, "\" style=\"background-color: ").concat(bg_color_load, "; color: ").concat(text_color_load, ";\">Load More</button>");
      }
      mainDivWrapper.querySelectorAll('.load-more-meta').forEach(function (button) {
        return button.remove();
      });
      var xhr = new XMLHttpRequest();
      xhr.open('POST', load_more.ajax_url, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (!response.data.content || response.data.content.trim() === '') {
            var errMsg = "<p class=\"zwssgr-no-found-message\">".concat(response.data.err_msg, "</p>");
            if (list_to_apnd) list_to_apnd.innerHTML = errMsg;
            if (grid_to_apnd) grid_to_apnd.innerHTML = errMsg;
            ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6', '#zwssgr-slider7', '#zwssgr-slider8'].forEach(function (selector) {
              var element = mainDivWrapper.querySelector(selector);
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
          ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6', '#zwssgr-slider7', '#zwssgr-slider8'].forEach(function (selector) {
            var element = mainDivWrapper.querySelector(selector);
            if (element) {
              setTimeout(function () {
                return (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.reinitializeAllSwipers)(element);
              }, 100);
              element.innerHTML = '';
              element.insertAdjacentHTML('beforeend', response.data.content);
            }
          });
          if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 'list-6', 'list-7', 'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5', 'grid-6', 'grid-7'].includes(layoutType)) {
            if (response.data.disable_button !== true) {
              mainDivWrapper.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
            }
          }
          if (['popup-1', 'popup-2'].includes(layoutType)) {
            if (response.data.disable_button !== true) {
              var _document$querySelect;
              (_document$querySelect = document.querySelector('.scrollable-content')) === null || _document$querySelect === void 0 || _document$querySelect.insertAdjacentHTML('beforeend', window.zwssgrLoadMoreButton);
            }
          }
        }
      };
      var params = new URLSearchParams();
      params.append('action', 'zwssgr_load_more_meta_data');
      params.append('front_sort_by', selectedValue);
      params.append('post_id', postId);
      params.append('front_keyword', keyword);
      params.append('onload_first', isFirstLoad);
      params.append('nonce', load_more.nonce);
      xhr.send(params);
    }
  });
});

/***/ }),

/***/ "./assets/src/js/get-url-parameter.js":
/*!********************************************!*\
  !*** ./assets/src/js/get-url-parameter.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgrGetUrlParameter: () => (/* binding */ zwssgrGetUrlParameter)
/* harmony export */ });
function zwssgrGetUrlParameter(zwssgrName) {
  var zwssgrUrlParams = new URLSearchParams(window.location.search);
  return zwssgrUrlParams.get(zwssgrName);
}

/***/ }),

/***/ "./assets/src/js/hide-element.js":
/*!***************************************!*\
  !*** ./assets/src/js/hide-element.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   toggleElements: () => (/* binding */ toggleElements)
/* harmony export */ });


function toggleElements() {
  var elements = [{
    checkbox: "review-title",
    target: ".zwssgr-title"
  }, {
    checkbox: "review-rating",
    target: ".zwssgr-rating"
  }, {
    checkbox: "review-days-ago",
    target: ".zwssgr-days-ago"
  }, {
    checkbox: "review-content",
    target: ".zwssgr-content"
  }, {
    checkbox: "review-photo",
    target: ".zwssgr-profile"
  }, {
    checkbox: "review-g-icon",
    target: ".zwssgr-google-icon"
  }, {
    checkbox: "review-rating",
    target: ".zwssgr-profile-info .zwssgr-rating",
    alwaysShow: true
  }, {
    checkbox: "review-rating",
    target: ".zwssgr-info-wrap .zwssgr-rating",
    alwaysShow: true
  }, {
    checkbox: "review-rating",
    target: ".zwssgr-slider-badge .zwssgr-rating",
    alwaysShow: true
  }];
  elements.forEach(function (_ref) {
    var checkbox = _ref.checkbox,
      target = _ref.target,
      alwaysShow = _ref.alwaysShow;
    var checkboxElement = document.getElementById(checkbox);
    var targetElements = document.querySelectorAll(target);
    if (checkboxElement && targetElements.length) {
      var shouldShow = !checkboxElement.checked || alwaysShow;
      targetElements.forEach(function (el) {
        return el.style.display = shouldShow ? 'block' : 'none';
      });
    }
  });
}
document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var urlParams = new URLSearchParams(window.location.search);
  var page = urlParams.get("page");
  var tab = urlParams.get("tab");

  // Attach change event listeners to checkboxes
  document.querySelectorAll('input[name="review-element"]').forEach(function (checkbox) {
    checkbox.addEventListener('change', toggleElements);
  });

  // Call toggleElements on page load to apply any initial settings with fade effect
  if (page === "zwssgr_widget_configurator" && tab === "tab-selected") {
    // console.log("Condition met, calling toggleElements...");
    toggleElements();
  }
});

/***/ }),

/***/ "./assets/src/js/hide-show-review.js":
/*!*******************************************!*\
  !*** ./assets/src/js/hide-show-review.js ***!
  \*******************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Handle click on visibility toggle icon of Review CPT
  document.addEventListener("click", function (e) {
    var toggleButton = e.target.closest(".zwssgr-toggle-visibility");
    if (!toggleButton) return;
    e.preventDefault();
    var postId = toggleButton.getAttribute("data-post-id");
    var icon = toggleButton.querySelector(".dashicons");
    var formData = new FormData();
    formData.append("action", "toggle_visibility");
    formData.append("post_id", postId);
    formData.append("nonce", zwssgr_admin.nonce);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", zwssgr_admin.ajax_url, true); // Make it asynchronous (true)

    xhr.onload = function () {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText); // Parse JSON manually

        if (response.success) {
          icon.classList.remove("dashicons-hidden", "dashicons-visibility");
          icon.classList.add("dashicons-" + response.data.icon);

          // Optionally display the current state
          var currentState = response.data.state;
          // console.log("Post visibility is now: " + currentState);
        }
      }
    };
    xhr.send(formData);
  });
});

/***/ }),

/***/ "./assets/src/js/index.js":
/*!********************************!*\
  !*** ./assets/src/js/index.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var requireAll = __webpack_require__("./assets/src/js sync \\.js$");
requireAll.keys().forEach(requireAll);

/***/ }),

/***/ "./assets/src/js/keyword-filter.js":
/*!*****************************************!*\
  !*** ./assets/src/js/keyword-filter.js ***!
  \*****************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var updateInputField = function updateInputField() {
    var keywords = [];
    document.querySelectorAll('#keywords-list .keyword-item').forEach(function (item) {
      keywords.push(item.textContent.trim().replace(' ✖', ''));
    });
    var hiddenInput = document.getElementById('keywords-input-hidden');
    if (hiddenInput) {
      // Ensure the element exists before modifying it
      hiddenInput.value = keywords.join(', ');
    }
  };
  updateInputField(); // Call the function after DOM is ready

  // Function to handle adding new keywords
  var handleAddKeywords = function handleAddKeywords(inputValue) {
    // Get the input value and split it into keywords
    var newKeywords = inputValue.split(',').map(function (keyword) {
      return keyword.trim();
    }).filter(function (keyword) {
      return keyword;
    });

    // Get the current number of keywords in the list
    var currentKeywordsCount = document.querySelectorAll('#keywords-list .keyword-item').length;

    // Check if adding new keywords exceeds the limit of 5
    if (currentKeywordsCount + newKeywords.length > 5) {
      document.getElementById('error-message').style.display = 'block'; // Show the error message
      return; // Stop further execution
    } else {
      document.getElementById('error-message').style.display = 'none'; // Hide the error message if under limit
    }
    document.getElementById('keywords-input').value = ''; // Clear input field

    newKeywords.forEach(function (keyword) {
      // Check if the keyword is already in the list
      var existingKeywords = Array.from(document.querySelectorAll('#keywords-list .keyword-item')).map(function (item) {
        return item.textContent.trim().replace(' ✖', '');
      });
      if (!existingKeywords.includes(keyword)) {
        // Create a new keyword item
        var keywordItem = document.createElement('div');
        keywordItem.classList.add('keyword-item');
        keywordItem.textContent = keyword;

        // Create remove button
        var removeButton = document.createElement('span');
        removeButton.classList.add('remove-keyword');
        removeButton.textContent = ' ✖';

        // Append remove button to the keyword item
        keywordItem.appendChild(removeButton);

        // Append the keyword item to the keywords list
        document.getElementById('keywords-list').appendChild(keywordItem);

        // Update hidden input field
        updateInputField();

        // Set up click event to remove keyword
        removeButton.addEventListener('click', function () {
          keywordItem.remove(); // Remove keyword from list
          updateInputField(); // Update input field after removal
        });
      }
    });
  };
  document.body.addEventListener('keypress', function (event) {
    if (event.target && event.target.id === 'keywords-input') {
      if (event.key === 'Enter') {
        // Check for Enter key
        event.preventDefault(); // Prevent default form submission
        handleAddKeywords(event.target.value);
      }
    }
  });
  document.body.addEventListener('blur', function (event) {
    if (event.target && event.target.id === 'keywords-input') {
      handleAddKeywords(event.target.value);
    }
  }, true); // Use the "true" parameter to capture the event during the capturing phase

  // Function to set up click event to remove existing keywords (on page load and dynamically)
  var keywordsList = document.getElementById('keywords-list');
  if (keywordsList) {
    keywordsList.addEventListener('click', function (e) {
      document.getElementById('keywords-list').addEventListener('click', function (e) {
        var removeBtn = e.target.closest('.remove-keyword'); // Find the closest remove button
        if (removeBtn) {
          removeBtn.parentElement.remove(); // Remove the keyword item
          updateInputField(); // Update the hidden input after removal
        }
      });
    });
  }
});

/***/ }),

/***/ "./assets/src/js/load-more.js":
/*!************************************!*\
  !*** ./assets/src/js/load-more.js ***!
  \************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.addEventListener('click', function (event) {
    if (event.target.closest('.load-more-meta')) {
      var _mainWrapper$querySel;
      var button = event.target.closest('.load-more-meta');
      var mainWrapper = button.closest('.zwssgr-main-wrapper');
      if (!mainWrapper) return;
      var page = button.getAttribute('data-page'); // Get the current page number
      var postId = button.getAttribute('data-post-id'); // Get the post-id from the button data attribute
      var selectedValue = ((_mainWrapper$querySel = mainWrapper.querySelector('.front-sort-by-select')) === null || _mainWrapper$querySel === void 0 ? void 0 : _mainWrapper$querySel.value) || '';
      var selectedKeywordElement = mainWrapper.querySelector('.zwssgr-front-keywords-list li.selected');
      var keyword = selectedKeywordElement ? selectedKeywordElement.getAttribute('data-zwssgr-keyword') : '';
      var popupContentContainer = mainWrapper.querySelector('zwssgr-slider.zwssgr-grid-item.zwssgr-popup-list');
      var isFirstLoad = mainWrapper.getAttribute('data-onload-first');

      // Disable the button to prevent multiple clicks
      button.setAttribute('disabled', true);
      button.textContent = 'Loading...';

      // AJAX request using Fetch API
      var xhr = new XMLHttpRequest();
      xhr.open('POST', load_more.ajax_url, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
              // Append new content to the popup if applicable
              if (popupContentContainer) {
                popupContentContainer.insertAdjacentHTML('beforeend', response.data.content);
              }
              var container = document.querySelector("#div-container[data-widget-id=\"".concat(postId, "\"]"));
              if (container) {
                var listContainer = container.querySelector('.zwssgr-list');
                var gridContainer = container.querySelector('.zwssgr-grid-item');
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
      var params = new URLSearchParams();
      params.append('action', 'zwssgr_load_more_meta_data');
      params.append('post_id', postId);
      params.append('page', page);
      params.append('front_sort_by', selectedValue);
      params.append('front_keyword', keyword);
      params.append('onload_first', isFirstLoad);
      params.append('nonce', load_more.nonce);
      xhr.send(params);
    }
  });
});

/***/ }),

/***/ "./assets/src/js/pin-review.js":
/*!*************************************!*\
  !*** ./assets/src/js/pin-review.js ***!
  \*************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Pin Review Functionality
  document.addEventListener('click', function (e) {
    if (e.target.closest('.zwssgr-toggle-pin')) {
      e.preventDefault();
      var button = e.target.closest('.zwssgr-toggle-pin');
      var postId = button.getAttribute('data-post-id');
      var icon = button.querySelector('img');

      // Prepare the data to send
      var formData = new FormData();
      formData.append('action', 'toggle_visibility');
      formData.append('action_type', 'pin_toggle');
      formData.append('post_id', postId);
      formData.append('nonce', zwssgr_admin.nonce);

      // Send the AJAX request using Fetch API
      fetch(zwssgr_admin.ajax_url, {
        method: 'POST',
        body: formData
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.success) {
          icon.setAttribute('src', data.data.icon);
          // console.log('Post is now: ' + data.data.state);
        } else {
          alert(data.data.message);
        }
      })["catch"](function () {
        alert('An error occurred while toggling the pin state.');
      });
    }
  });
});

/***/ }),

/***/ "./assets/src/js/plugin-menu.js":
/*!**************************************!*\
  !*** ./assets/src/js/plugin-menu.js ***!
  \**************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  window.zwssgrWidgetPostType = "zwssgr_data_widget";

  // Function to check if an element exists
  function elementExists(selector) {
    return document.querySelector(selector) !== null;
  }

  // Check if we're on the edit, new post, or custom layout page for the widget post type
  if (elementExists(".post-type-" + window.zwssgrWidgetPostType) || elementExists(".post-php.post-type-" + window.zwssgrWidgetPostType) || elementExists(".post-new-php.post-type-" + window.zwssgrWidgetPostType) || window.location.href.includes("admin.php?page=zwssgr_widget_configurator")) {
    // Ensure the parent menu (dashboard) is highlighted as active
    var dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
    if (dashboardMenu) {
      dashboardMenu.classList.remove("wp-not-current-submenu");
      dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
    }

    // Ensure the specific submenu item for zwssgr_data_widget is active
    var widgetMenuItem = document.querySelector('ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrWidgetPostType + '"]');
    if (widgetMenuItem) {
      widgetMenuItem.closest("li").classList.add("current");
    }
  }
  window.zwssgrReviewPostType = "zwssgr_reviews";

  // Check if we're on the edit, new post, or custom layout page for the review post type
  if (elementExists(".post-type-" + window.zwssgrReviewPostType) || elementExists(".post-php.post-type-" + window.zwssgrReviewPostType) || elementExists(".post-new-php.post-type-" + window.zwssgrReviewPostType) || window.location.href.includes("admin.php?page=zwssgr_review_configurator")) {
    // Ensure the parent menu (dashboard) is highlighted as active
    var _dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
    if (_dashboardMenu) {
      _dashboardMenu.classList.remove("wp-not-current-submenu");
      _dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
    }

    // Ensure the specific submenu item for zwssgr_reviews is active
    var reviewMenuItem = document.querySelector('ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrReviewPostType + '"]');
    if (reviewMenuItem) {
      reviewMenuItem.closest("li").classList.add("current");
    }
  }
});

/***/ }),

/***/ "./assets/src/js/popup.js":
/*!********************************!*\
  !*** ./assets/src/js/popup.js ***!
  \********************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Bind click event to open popup
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("zwssgr-total-review")) {
      return;
    }
    var popupItem = e.target.closest(".zwssgr-popup-item");
    if (popupItem) {
      var popupId = popupItem.dataset.popup; // Get the popup ID from the data attribute
      var popup = document.getElementById(popupId);
      if (popup) {
        popup.style.display = "block"; // Show the popup
      }
    }
  });

  // Bind click event to close popup when the close button is clicked
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("zwssgr-close-popup")) {
      var popupOverlay = e.target.closest(".zwssgr-popup-overlay");
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

/***/ }),

/***/ "./assets/src/js/process-batches.js":
/*!******************************************!*\
  !*** ./assets/src/js/process-batches.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgrProcessBatch: () => (/* binding */ zwssgrProcessBatch)
/* harmony export */ });
function zwssgrProcessBatch(zwssgrGmbDataType, zwssgrAccountNumber, zwssgrLocationNumber, zwssgrWidgetId, zwssgrLocationName, zwssgrLocationNewReviewUri, zwssgrAccountName, zwssgrLocationAllReviewUri) {
  var _zwssgr_admin, _zwssgr_admin2;
  function zwssgrReloadWithDelay() {
    var zwssgrDelay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1500;
    setTimeout(function () {
      return location.reload();
    }, zwssgrDelay);
  }
  fetch((_zwssgr_admin = zwssgr_admin) === null || _zwssgr_admin === void 0 ? void 0 : _zwssgr_admin.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({
      action: "zwssgr_fetch_gmb_data",
      security: (_zwssgr_admin2 = zwssgr_admin) === null || _zwssgr_admin2 === void 0 ? void 0 : _zwssgr_admin2.zwssgr_queue_manager_nounce,
      zwssgr_gmb_data_type: zwssgrGmbDataType,
      zwssgr_account_number: zwssgrAccountNumber,
      zwssgr_location_number: zwssgrLocationNumber,
      zwssgr_widget_id: zwssgrWidgetId,
      zwssgr_location_name: zwssgrLocationName,
      zwssgr_location_new_review_uri: zwssgrLocationNewReviewUri,
      zwssgr_account_name: zwssgrAccountName,
      zwssgr_location_all_review_uri: zwssgrLocationAllReviewUri
    })
  }).then(function (zwssgrResponse) {
    return zwssgrResponse.json();
  }).then(function (zwssgrResponse) {
    var zwssgrProgressBar = document.querySelector('#fetch-gmb-data .progress-bar');
    var zwssgrResponseContainer = document.querySelector('#fetch-gmb-data .response');
    if (zwssgrResponse.success) {
      zwssgrProgressBar.style.display = 'block';
    } else {
      var _zwssgrResponse$data;
      zwssgrResponseContainer.innerHTML = "<p class=\"error\">".concat(((_zwssgrResponse$data = zwssgrResponse.data) === null || _zwssgrResponse$data === void 0 ? void 0 : _zwssgrResponse$data.message) || 'An error occurred.', "</p>");
      zwssgrReloadWithDelay();
    }
  })["catch"](function (zwssgrError) {
    var zwssgrResponseContainer = document.querySelector('#fetch-gmb-data .response');
    zwssgrResponseContainer.innerHTML = "<p class=\"error\">An unexpected error occurred.</p>";
    zwssgrReloadWithDelay();
  });
}

/***/ }),

/***/ "./assets/src/js/read-more.js":
/*!************************************!*\
  !*** ./assets/src/js/read-more.js ***!
  \************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('toggle-content')) {
      var link = e.target;
      var fullText = link.getAttribute('data-full-text');
      var parentParagraph = link.closest('p');

      // Replace the trimmed content with the full content
      if (parentParagraph) {
        parentParagraph.innerHTML = fullText;
      }
    }
  });
});

/***/ }),

/***/ "./assets/src/js/redirect-to-options-tab.js":
/*!**************************************************!*\
  !*** ./assets/src/js/redirect-to-options-tab.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgrRedirectToOptionsTab: () => (/* binding */ zwssgrRedirectToOptionsTab)
/* harmony export */ });
function zwssgrRedirectToOptionsTab() {
  var zwssgrCurrentUrl = window.location.href;
  if (zwssgrCurrentUrl.includes('tab=')) {
    zwssgrCurrentUrl = zwssgrCurrentUrl.replace(/tab=[^&]+/, 'tab=tab-options');
  } else {
    zwssgrCurrentUrl += (zwssgrCurrentUrl.includes('?') ? '&' : '?') + 'tab=tab-options';
  }
  window.location.href = zwssgrCurrentUrl;
}

/***/ }),

/***/ "./assets/src/js/review-filter.js":
/*!****************************************!*\
  !*** ./assets/src/js/review-filter.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   formatDate: () => (/* binding */ formatDate),
/* harmony export */   updateDisplayedDates: () => (/* binding */ updateDisplayedDates),
/* harmony export */   updateReadMoreLink: () => (/* binding */ updateReadMoreLink)
/* harmony export */ });


// Helper function: Formats date based on user selection
function formatDate(dateString, format, lang) {
  var dateParts;
  var date;
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
    dateParts = dateString.split('/');
    date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
  } else if (/^\d{2}-\d{2}-\d{4}$/.test(dateString)) {
    dateParts = dateString.split('-');
    date = new Date(dateParts[2], dateParts[0] - 1, dateParts[1]);
  } else if (/^\d{4}\/\d{2}\/\d{2}$/.test(dateString)) {
    dateParts = dateString.split('/');
    date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
  } else {
    date = new Date(dateString);
  }
  if (isNaN(date.getTime())) return dateString;
  var options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  };
  switch (format) {
    case 'DD/MM/YYYY':
      return date.toLocaleDateString('en-GB');
    case 'MM-DD-YYYY':
      return date.toLocaleDateString('en-US').replace(/\//g, '-');
    case 'YYYY/MM/DD':
      return date.toISOString().split('T')[0].replace(/-/g, '/');
    case 'full':
      return date.toLocaleDateString(lang, options);
    default:
      return dateString;
  }
}

// Exported function: Updates date display based on language/format
function updateDisplayedDates() {
  var _document$getElementB, _document$getElementB2;
  var lang = (_document$getElementB = document.getElementById('language-select')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value;
  var format = (_document$getElementB2 = document.getElementById('date-format-select')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.value;
  document.querySelectorAll('.zwssgr-date').forEach(function (element) {
    var originalDate = element.getAttribute('data-original-date');
    if (format === 'hide') {
      element.textContent = '';
    } else {
      var formattedDate = formatDate(originalDate, format, lang);
      element.textContent = formattedDate;
    }
  });
}
window.zwssgrTranslations = {
  en: 'Read more',
  es: 'Leer más',
  fr: 'Lire la suite',
  de: 'Mehr lesen',
  it: 'Leggi di più',
  pt: 'Leia mais',
  ru: 'Читать дальше',
  zh: '阅读更多',
  ja: '続きを読む',
  hi: 'और पढ़ें',
  ar: 'اقرأ أكثر',
  ko: '더 읽기',
  tr: 'Daha fazla oku',
  bn: 'আরও পড়ুন',
  ms: 'Baca lagi',
  nl: 'Lees verder',
  pl: 'Czytaj więcej',
  sv: 'Läs mer',
  th: 'อ่านเพิ่มเติม'
};

// Updates "Read more" link based on language and char limit
function updateReadMoreLink(element, lang) {
  var _document$getElementB3;
  var charLimit = parseInt((_document$getElementB3 = document.getElementById('review-char-limit')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.value, 10);
  var fullText = element.getAttribute('data-full-text');
  if (charLimit && fullText.length > charLimit) {
    var trimmedText = fullText.substring(0, charLimit) + '... ';
    element.innerHTML = trimmedText + "<a href=\"javascript:void(0);\" class=\"read-more-link\">".concat(window.zwssgrTranslations[lang], "</a>");
  } else {
    element.textContent = fullText;
  }
}

// Event Listeners (Triggers on load & user interactions)

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.body.addEventListener('change', function (event) {
    if (event.target && event.target.id === 'date-format-select') {
      updateDisplayedDates();
    }
    if (event.target && event.target.id === 'language-select') {
      var lang = event.target.value;
      updateDisplayedDates();
      document.querySelectorAll('.zwssgr-content').forEach(function (element) {
        var fullText = element.getAttribute('data-full-text') || element.textContent;
        if (!element.getAttribute('data-full-text')) {
          element.setAttribute('data-full-text', fullText);
        }
        updateReadMoreLink(element, lang);
      });
    }
  });
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('read-more-link')) {
      event.preventDefault();
      var parent = event.target.parentElement;
      var fullText = parent.getAttribute('data-full-text');
      if (fullText) {
        parent.textContent = fullText;
      }
    }
  });
  document.body.addEventListener('input', function (event) {
    if (event.target && event.target.id === 'review-char-limit') {
      var charLimit = parseInt(event.target.value, 10);
      var lang = document.getElementById('language-select').value;
      var errorContainer = document.getElementById('char-limit-error');
      errorContainer.textContent = '';
      if (charLimit < 1 || isNaN(charLimit)) {
        if (event.target.value.trim() === '') {
          document.querySelectorAll('.zwssgr-content').forEach(function (element) {
            var fullText = element.getAttribute('data-full-text') || element.textContent;
            element.textContent = fullText;
          });
        } else {
          errorContainer.textContent = 'Character limit must be 1 or greater.';
          event.target.value = '';
        }
        return;
      }
      document.querySelectorAll('.zwssgr-content').forEach(function (element) {
        var fullText = element.getAttribute('data-full-text') || element.textContent;
        if (!element.getAttribute('data-full-text')) {
          element.setAttribute('data-full-text', fullText);
        }
        updateReadMoreLink(element, lang);
      });
    }
  });
});

/***/ }),

/***/ "./assets/src/js/seo-notification.js":
/*!*******************************************!*\
  !*** ./assets/src/js/seo-notification.js ***!
  \*******************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // SEO and Notification Email Toggle
  window.zwssgrToggle = document.getElementById('zwssgr_admin_notification_enabled');
  window.zwssgrNotificationFields = document.querySelector('.zwssgr-notification-fields');
  window.zwssgrSubmitButton = document.querySelector('.zwssgr-notification-submit-btn');
  function toggleNotificationFields() {
    if (window.zwssgrToggle.checked) {
      window.zwssgrNotificationFields.style.display = 'block';
      window.zwssgrSubmitButton.classList.remove('zwssgr-disable');
    } else {
      window.zwssgrNotificationFields.style.display = 'none';
      window.zwssgrSubmitButton.classList.add('zwssgr-disable');
    }
  }

  // Initialize the state
  if (window.zwssgrToggle) {
    toggleNotificationFields();
    window.zwssgrToggle.addEventListener('change', toggleNotificationFields);
  }

  // SEO and Notification email validation
  function validateEmail(email) {
    var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailPattern.test(email);
  }
  function validateEmails() {
    var emailInput = document.getElementById('zwssgr_admin_notification_emails');
    var emailError = document.getElementById('email-error');
    var emailSuccess = document.getElementById('email-success');
    if (!emailInput) return;
    var emails = emailInput.value.split(',').map(function (email) {
      return email.trim();
    });
    var invalidEmails = emails.filter(function (email) {
      return !validateEmail(email);
    });
    if (invalidEmails.length > 0) {
      emailError.textContent = 'Invalid email(s): ' + invalidEmails.join(', ');
      emailError.style.display = 'block';
      emailSuccess.style.display = 'none';
    } else {
      emailError.style.display = 'none';
    }
  }

  // Add event listeners for email validation
  var emailInput = document.getElementById('zwssgr_admin_notification_emails');
  if (emailInput) {
    emailInput.addEventListener('keypress', validateEmails);
    emailInput.addEventListener('blur', validateEmails);
  }

  // Form submission validation
  var notificationForm = document.getElementById('notification-form');
  if (notificationForm) {
    notificationForm.addEventListener('submit', function (e) {
      var emails = emailInput.value.split(',').map(function (email) {
        return email.trim();
      });
      var invalidEmails = emails.filter(function (email) {
        return !validateEmail(email);
      });
      var emailError = document.getElementById('email-error');
      var emailSuccess = document.getElementById('email-success');
      if (invalidEmails.length > 0) {
        e.preventDefault();
        emailError.textContent = 'Cannot send emails. Invalid email(s): ' + invalidEmails.join(', ');
        emailError.style.display = 'block';
        emailSuccess.style.display = 'none';
      } else {
        emailError.style.display = 'none';
        emailSuccess.textContent = 'Success! Emails are valid and form submitted.';
        emailSuccess.style.display = 'block';
      }
    });
  }
});

/***/ }),

/***/ "./assets/src/js/shortcode.js":
/*!************************************!*\
  !*** ./assets/src/js/shortcode.js ***!
  \************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("copy-shortcode-icon") || e.target.classList.contains("zwssgr-copy-shortcode-icon")) {
      var targetId = e.target.dataset.target;
      var inputElement = document.getElementById(targetId);
      if (inputElement) {
        // Copy the input field text using Clipboard API
        navigator.clipboard.writeText(inputElement.value).then(function () {
          e.target.classList.add("dashicons-yes"); // Change icon to a checkmark
          setTimeout(function () {
            e.target.classList.remove("dashicons-yes");
            e.target.classList.add("dashicons-admin-page"); // Reset icon after 2 seconds
          }, 2000);
        })["catch"](function (err) {
          console.error("Failed to copy text: ", err);
        });
      }
    }
  });
});

/***/ }),

/***/ "./assets/src/js/smtp.js":
/*!*******************************!*\
  !*** ./assets/src/js/smtp.js ***!
  \*******************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  function zwssgr_update_Smtp_Port() {
    var _document$querySelect;
    var encryptionType = (_document$querySelect = document.querySelector('input[name="zwssgr_smtp_ency_type"]:checked')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value;
    var portInput = document.getElementById('zwssgr-smtp-port');
    if (!portInput) return; // Prevent error if element doesn't exist

    switch (encryptionType) {
      case 'none':
        portInput.value = '25'; // Set port to 25 for 'None'
        break;
      case 'ssl':
        portInput.value = '465'; // Set port to 465 for 'SSL'
        break;
      case 'tls':
        portInput.value = '587'; // Set port to 587 for 'TLS'
        break;
      default:
        portInput.value = '25';
      // Default port
    }
  }

  // Attach event listeners for SMTP encryption type
  document.querySelectorAll('input[name="zwssgr_smtp_ency_type"]').forEach(function (input) {
    input.addEventListener('change', zwssgr_update_Smtp_Port);
  });

  // Function to toggle SMTP authentication fields
  function toggleSmtpAuth() {
    var smtpAuth = document.querySelector('input[name="zwssgr_smtp_auth"]:checked');
    var zwssgrSmtprows = document.querySelectorAll('tr.zwssgr-smtp-auth-enable-main');
    var usernameField = document.querySelector('input[name="zwssgr_smtp_username"]');
    var passwordField = document.querySelector('input[name="zwssgr_smtp_password"]');
    if (!smtpAuth || !usernameField || !passwordField) return; // Prevent error if elements don't exist

    if (smtpAuth.value === 'no') {
      zwssgrSmtprows.forEach(function (row) {
        return row.style.display = 'none';
      });
      usernameField.removeAttribute('required');
      passwordField.removeAttribute('required');
    } else {
      zwssgrSmtprows.forEach(function (row) {
        return row.style.display = 'table-row';
      });
      usernameField.setAttribute('required', 'required');
      passwordField.setAttribute('required', 'required');
    }
  }

  // Attach event listeners for SMTP authentication
  document.querySelectorAll('input[name="zwssgr_smtp_auth"]').forEach(function (input) {
    input.addEventListener('change', toggleSmtpAuth);
  });

  // Function to toggle Admin SMTP settings
  function toggleAdminSmtp() {
    var adminSmtpEnabled = document.querySelector('input[name="zwssgr_admin_smtp_enabled"]');
    var adminSmtpFields = document.querySelectorAll('.zwssgr-admin-enable-smtp');
    var requiredFields = ['zwssgr_smtp_username', 'zwssgr_smtp_password', 'zwssgr_from_email', 'zwssgr_smtp_host'];
    if (!adminSmtpEnabled) return; // Prevent error if element doesn't exist

    if (adminSmtpEnabled.checked) {
      adminSmtpFields.forEach(function (el) {
        return el.style.display = 'contents';
      });
      requiredFields.forEach(function (field) {
        var input = document.querySelector("input[name=\"".concat(field, "\"]"));
        if (input) input.setAttribute('required', 'required'); // Check if input exists
      });
    } else {
      adminSmtpFields.forEach(function (el) {
        return el.style.display = 'none';
      });
      requiredFields.forEach(function (field) {
        var input = document.querySelector("input[name=\"".concat(field, "\"]"));
        if (input) input.removeAttribute('required'); // Check if input exists
      });
    }
  }

  // Attach event listener for Admin SMTP toggle
  document.body.addEventListener('change', function (event) {
    if (event.target && event.target.name === 'zwssgr_admin_smtp_enabled') {
      toggleAdminSmtp();
    }
  });
  if (document.querySelector('input[name="zwssgr_admin_smtp_enabled"]')) {
    toggleAdminSmtp();
  }
  if (document.querySelector('input[name="zwssgr_smtp_auth"]')) {
    toggleSmtpAuth();
  }
});

/***/ }),

/***/ "./assets/src/js/success-message.js":
/*!******************************************!*\
  !*** ./assets/src/js/success-message.js ***!
  \******************************************/
/***/ (() => {

function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Function to show custom notifications
  function showNotification(message, type) {
    // Define the notification types: success, error, warning, info
    var notificationClass = 'zwssgr-notice-' + type; // Example: zwssgr-notice-success, zwssgr-notice-error

    // Create the notification HTML
    var notification = document.createElement('div');
    notification.className = "zwssgr-notice ".concat(notificationClass, " zwssgr-is-dismissible");
    notification.innerHTML = "<p>".concat(message, "</p>");

    // Append the notification to the target area
    var dashboard = document.querySelector('.zwssgr-dashboard');
    if (dashboard) {
      dashboard.prepend(notification);
    }

    // Add click event for dismissing the notification
    notification.addEventListener('click', function (event) {
      if (event.target.classList.contains('zwssgr-notice-dismiss')) {
        notification.style.transition = "opacity 0.3s";
        notification.style.opacity = "0";
        setTimeout(function () {
          notification.remove();
        }, 300);
      }
    });
  }

  // Function to get query parameters
  function getQueryParam(name) {
    var urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  // Handle click events for "Select Option" buttons
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('select-btn')) {
      var _document$querySelect;
      var optionId = event.target.getAttribute('data-option');
      var postId = getQueryParam('zwssgr_widget_id');
      var currentUrl = window.location.href.split('?')[0];
      if (!postId) {
        showNotification('Post ID not found!', 'error'); // Custom error notification
        return;
      }

      // Fetch the HTML for the selected option using the correct optionId
      var selectedOptionElement = document.getElementById(optionId);
      var displayArea = document.getElementById('selected-option-display');
      if (selectedOptionElement && displayArea) {
        displayArea.innerHTML = selectedOptionElement.outerHTML; // Clone the selected option's element
        var clonedElement = displayArea.firstElementChild;

        // Remove the select button from the cloned HTML
        var selectButton = clonedElement.querySelector('.select-btn');
        if (selectButton) {
          selectButton.remove();
        }
      }

      // Get the current display option
      var displayOption = (_document$querySelect = document.querySelector('input[name="display_option"]:checked')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value;
      var activeTab = document.querySelector('.tab-item.active');
      var settings = activeTab ? activeTab.getAttribute('data-tab') : '';
      var currentTab = activeTab ? activeTab.dataset.tab : '';

      // Perform AJAX request using Fetch API
      fetch(ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'zwssgr_save_widget_data',
          security: my_widget.nonce,
          layout_option: optionId,
          display_option: displayOption,
          post_id: postId,
          settings: settings,
          current_tab: currentTab
        })
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.success) {
          showNotification('Layout option saved successfully!', 'success'); // Show success message

          // Append post_id and selected option to the URL
          window.location.href = "".concat(currentUrl, "?page=zwssgr_widget_configurator&tab=tab-selected&selectedOption=").concat(optionId, "&zwssgr_widget_id=").concat(postId);
        } else {
          showNotification('Failed to save layout option.', 'error'); // Show error message
        }
      })["catch"](function () {
        showNotification('An error occurred.', 'error'); // Show error message
      });
    }
  });
  document.body.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'save-get-code-btn') {
      var _document$querySelect2, _document$getElementB, _document$getElementB2, _document$getElementB3, _document$getElementB4, _document$getElementB5, _document$getElementB6, _document$getElementB7, _document$getElementB8, _document$getElementB9, _document$getElementB10, _document$querySelect3, _document$getElementB11, _pop, _document$querySelect4, _document$querySelect5, _document$getElementB12;
      var _getQueryParam = function _getQueryParam(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
      };
      e.preventDefault();
      var selectedOption = _getQueryParam('selectedOption');
      var currentUrl = window.location.href.split('?')[0];
      var postId = _getQueryParam('zwssgr_widget_id');
      var displayOption = ((_document$querySelect2 = document.querySelector('input[name="display_option"]:checked')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.value) || '';
      var selectedElements = Array.from(document.querySelectorAll('input[name="review-element"]:checked')).map(function (el) {
        return el.value;
      });
      var keywords = Array.from(document.querySelectorAll('#keywords-list .keyword-item')).map(function (el) {
        return el.textContent.trim().replace(' ✖', '');
      });
      var dateFormat = ((_document$getElementB = document.getElementById('date-format-select')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value) || '';
      var charLimit = ((_document$getElementB2 = document.getElementById('review-char-limit')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.value) || '';
      var language = ((_document$getElementB3 = document.getElementById('language-select')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.value) || '';
      var sortBy = ((_document$getElementB4 = document.getElementById('sort-by-select')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.value) || '';
      var enableLoadMore = (_document$getElementB5 = document.getElementById('enable-load-more')) !== null && _document$getElementB5 !== void 0 && _document$getElementB5.checked ? 1 : 0;
      var googleReviewToggle = (_document$getElementB6 = document.getElementById('toggle-google-review')) !== null && _document$getElementB6 !== void 0 && _document$getElementB6.checked ? 1 : 0;
      var bgColor = ((_document$getElementB7 = document.getElementById('bg-color-picker')) === null || _document$getElementB7 === void 0 ? void 0 : _document$getElementB7.value) || '';
      var textColor = ((_document$getElementB8 = document.getElementById('text-color-picker')) === null || _document$getElementB8 === void 0 ? void 0 : _document$getElementB8.value) || '';
      var bgColorLoad = ((_document$getElementB9 = document.getElementById('bg-color-picker_load')) === null || _document$getElementB9 === void 0 ? void 0 : _document$getElementB9.value) || '';
      var textColorLoad = ((_document$getElementB10 = document.getElementById('text-color-picker_load')) === null || _document$getElementB10 === void 0 ? void 0 : _document$getElementB10.value) || '';
      var activeTab = ((_document$querySelect3 = document.querySelector('.tab-item.active')) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.getAttribute('data-tab')) || '';
      var postsPerPage = ((_document$getElementB11 = document.getElementById('posts-per-page')) === null || _document$getElementB11 === void 0 ? void 0 : _document$getElementB11.value) || '';
      var selectedRating = ((_pop = _toConsumableArray(document.querySelectorAll('.star-filter.selected')).pop()) === null || _pop === void 0 ? void 0 : _pop.dataset.rating) || 0;
      var currentTab2 = ((_document$querySelect4 = document.querySelector('.tab-item.active')) === null || _document$querySelect4 === void 0 ? void 0 : _document$querySelect4.dataset.tab) || '';
      var customCSS = ((_document$querySelect5 = document.querySelector('.zwssgr-textarea')) === null || _document$querySelect5 === void 0 ? void 0 : _document$querySelect5.value) || '';
      var enableSortBy = (_document$getElementB12 = document.getElementById('enable-sort-by-filter')) !== null && _document$getElementB12 !== void 0 && _document$getElementB12.checked ? 1 : 0;
      var formData = new FormData();
      formData.append('action', 'zwssgr_save_widget_data');
      formData.append('security', my_widget.nonce);
      formData.append('post_id', postId);
      formData.append('display_option', displayOption);
      formData.append('selected_elements', JSON.stringify(selectedElements)); // Ensure it's a JSON string
      formData.append('rating_filter', selectedRating);
      formData.append('keywords', JSON.stringify(keywords)); // Ensure it's a JSON string
      formData.append('date_format', dateFormat);
      formData.append('char_limit', charLimit);
      formData.append('language', language);
      formData.append('sort_by', sortBy);
      formData.append('enable_load_more', enableLoadMore);
      formData.append('google_review_toggle', googleReviewToggle);
      formData.append('bg_color', bgColor);
      formData.append('text_color', textColor);
      formData.append('bg_color_load', bgColorLoad);
      formData.append('text_color_load', textColorLoad);
      formData.append('settings', activeTab);
      formData.append('posts_per_page', postsPerPage);
      formData.append('current_tab2', currentTab2);
      formData.append('enable_sort_by', enableSortBy);
      formData.append('custom_css', customCSS);
      console.log(formData);
      fetch(ajaxurl, {
        method: 'POST',
        body: formData
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.success) {
          showNotification('Settings and shortcode saved successfully.', 'success');
          window.location.href = "".concat(currentUrl, "?page=zwssgr_widget_configurator&tab=tab-shortcode&selectedOption=").concat(selectedOption, "&zwssgr_widget_id=").concat(postId);
        } else {
          showNotification('Error: ' + data.data, 'error');
        }
      })["catch"](function (error) {
        console.error('AJAX Error:', error);
        showNotification('An error occurred while saving data. Details: ' + error, 'error');
      });
    }
  });
});

/***/ }),

/***/ "./assets/src/js/swiper-bundle.js":
/*!****************************************!*\
  !*** ./assets/src/js/swiper-bundle.js ***!
  \****************************************/
/***/ (() => {

function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/**
 * Swiper 11.2.4
 * Most modern mobile touch slider and framework with hardware accelerated transitions
 * https://swiperjs.com
 *
 * Copyright 2014-2025 Vladimir Kharlampidi
 *
 * Released under the MIT License
 *
 * Released on: February 14, 2025
 */

var Swiper = function () {
  "use strict";

  function e(e) {
    return null !== e && "object" == _typeof(e) && "constructor" in e && e.constructor === Object;
  }
  function t(s, a) {
    void 0 === s && (s = {}), void 0 === a && (a = {});
    var i = ["__proto__", "constructor", "prototype"];
    Object.keys(a).filter(function (e) {
      return i.indexOf(e) < 0;
    }).forEach(function (i) {
      void 0 === s[i] ? s[i] = a[i] : e(a[i]) && e(s[i]) && Object.keys(a[i]).length > 0 && t(s[i], a[i]);
    });
  }
  var s = {
    body: {},
    addEventListener: function addEventListener() {},
    removeEventListener: function removeEventListener() {},
    activeElement: {
      blur: function blur() {},
      nodeName: ""
    },
    querySelector: function querySelector() {
      return null;
    },
    querySelectorAll: function querySelectorAll() {
      return [];
    },
    getElementById: function getElementById() {
      return null;
    },
    createEvent: function createEvent() {
      return {
        initEvent: function initEvent() {}
      };
    },
    createElement: function createElement() {
      return {
        children: [],
        childNodes: [],
        style: {},
        setAttribute: function setAttribute() {},
        getElementsByTagName: function getElementsByTagName() {
          return [];
        }
      };
    },
    createElementNS: function createElementNS() {
      return {};
    },
    importNode: function importNode() {
      return null;
    },
    location: {
      hash: "",
      host: "",
      hostname: "",
      href: "",
      origin: "",
      pathname: "",
      protocol: "",
      search: ""
    }
  };
  function a() {
    var e = "undefined" != typeof document ? document : {};
    return t(e, s), e;
  }
  var i = {
    document: s,
    navigator: {
      userAgent: ""
    },
    location: {
      hash: "",
      host: "",
      hostname: "",
      href: "",
      origin: "",
      pathname: "",
      protocol: "",
      search: ""
    },
    history: {
      replaceState: function replaceState() {},
      pushState: function pushState() {},
      go: function go() {},
      back: function back() {}
    },
    CustomEvent: function CustomEvent() {
      return this;
    },
    addEventListener: function addEventListener() {},
    removeEventListener: function removeEventListener() {},
    getComputedStyle: function getComputedStyle() {
      return {
        getPropertyValue: function getPropertyValue() {
          return "";
        }
      };
    },
    Image: function Image() {},
    Date: function Date() {},
    screen: {},
    setTimeout: function setTimeout() {},
    clearTimeout: function clearTimeout() {},
    matchMedia: function matchMedia() {
      return {};
    },
    requestAnimationFrame: function requestAnimationFrame(e) {
      return "undefined" == typeof setTimeout ? (e(), null) : setTimeout(e, 0);
    },
    cancelAnimationFrame: function cancelAnimationFrame(e) {
      "undefined" != typeof setTimeout && clearTimeout(e);
    }
  };
  function r() {
    var e = "undefined" != typeof window ? window : {};
    return t(e, i), e;
  }
  function n(e) {
    return void 0 === e && (e = ""), e.trim().split(" ").filter(function (e) {
      return !!e.trim();
    });
  }
  function l(e, t) {
    return void 0 === t && (t = 0), setTimeout(e, t);
  }
  function o() {
    return Date.now();
  }
  function d(e, t) {
    void 0 === t && (t = "x");
    var s = r();
    var a, i, n;
    var l = function (e) {
      var t = r();
      var s;
      return t.getComputedStyle && (s = t.getComputedStyle(e, null)), !s && e.currentStyle && (s = e.currentStyle), s || (s = e.style), s;
    }(e);
    return s.WebKitCSSMatrix ? (i = l.transform || l.webkitTransform, i.split(",").length > 6 && (i = i.split(", ").map(function (e) {
      return e.replace(",", ".");
    }).join(", ")), n = new s.WebKitCSSMatrix("none" === i ? "" : i)) : (n = l.MozTransform || l.OTransform || l.MsTransform || l.msTransform || l.transform || l.getPropertyValue("transform").replace("translate(", "matrix(1, 0, 0, 1,"), a = n.toString().split(",")), "x" === t && (i = s.WebKitCSSMatrix ? n.m41 : 16 === a.length ? parseFloat(a[12]) : parseFloat(a[4])), "y" === t && (i = s.WebKitCSSMatrix ? n.m42 : 16 === a.length ? parseFloat(a[13]) : parseFloat(a[5])), i || 0;
  }
  function c(e) {
    return "object" == _typeof(e) && null !== e && e.constructor && "Object" === Object.prototype.toString.call(e).slice(8, -1);
  }
  function p() {
    var e = Object(arguments.length <= 0 ? void 0 : arguments[0]),
      t = ["__proto__", "constructor", "prototype"];
    for (var _a = 1; _a < arguments.length; _a += 1) {
      var _i = _a < 0 || arguments.length <= _a ? void 0 : arguments[_a];
      if (null != _i && (s = _i, !("undefined" != typeof window && void 0 !== window.HTMLElement ? s instanceof HTMLElement : s && (1 === s.nodeType || 11 === s.nodeType)))) {
        var _s = Object.keys(Object(_i)).filter(function (e) {
          return t.indexOf(e) < 0;
        });
        for (var _t = 0, _a2 = _s.length; _t < _a2; _t += 1) {
          var _a3 = _s[_t],
            _r = Object.getOwnPropertyDescriptor(_i, _a3);
          void 0 !== _r && _r.enumerable && (c(e[_a3]) && c(_i[_a3]) ? _i[_a3].__swiper__ ? e[_a3] = _i[_a3] : p(e[_a3], _i[_a3]) : !c(e[_a3]) && c(_i[_a3]) ? (e[_a3] = {}, _i[_a3].__swiper__ ? e[_a3] = _i[_a3] : p(e[_a3], _i[_a3])) : e[_a3] = _i[_a3]);
        }
      }
    }
    var s;
    return e;
  }
  function u(e, t, s) {
    e.style.setProperty(t, s);
  }
  function m(e) {
    var t = e.swiper,
      s = e.targetPosition,
      a = e.side;
    var i = r(),
      n = -t.translate;
    var l,
      o = null;
    var d = t.params.speed;
    t.wrapperEl.style.scrollSnapType = "none", i.cancelAnimationFrame(t.cssModeFrameID);
    var c = s > n ? "next" : "prev",
      p = function p(e, t) {
        return "next" === c && e >= t || "prev" === c && e <= t;
      },
      _u = function u() {
        l = new Date().getTime(), null === o && (o = l);
        var e = Math.max(Math.min((l - o) / d, 1), 0),
          r = .5 - Math.cos(e * Math.PI) / 2;
        var c = n + r * (s - n);
        if (p(c, s) && (c = s), t.wrapperEl.scrollTo(_defineProperty({}, a, c)), p(c, s)) return t.wrapperEl.style.overflow = "hidden", t.wrapperEl.style.scrollSnapType = "", setTimeout(function () {
          t.wrapperEl.style.overflow = "", t.wrapperEl.scrollTo(_defineProperty({}, a, c));
        }), void i.cancelAnimationFrame(t.cssModeFrameID);
        t.cssModeFrameID = i.requestAnimationFrame(_u);
      };
    _u();
  }
  function h(e) {
    return e.querySelector(".swiper-slide-transform") || e.shadowRoot && e.shadowRoot.querySelector(".swiper-slide-transform") || e;
  }
  function f(e, t) {
    void 0 === t && (t = "");
    var s = r(),
      a = _toConsumableArray(e.children);
    return s.HTMLSlotElement && e instanceof HTMLSlotElement && a.push.apply(a, _toConsumableArray(e.assignedElements())), t ? a.filter(function (e) {
      return e.matches(t);
    }) : a;
  }
  function g(e) {
    try {
      return void console.warn(e);
    } catch (e) {}
  }
  function v(e, t) {
    var _s$classList;
    void 0 === t && (t = []);
    var s = document.createElement(e);
    return (_s$classList = s.classList).add.apply(_s$classList, _toConsumableArray(Array.isArray(t) ? t : n(t))), s;
  }
  function w(e) {
    var t = r(),
      s = a(),
      i = e.getBoundingClientRect(),
      n = s.body,
      l = e.clientTop || n.clientTop || 0,
      o = e.clientLeft || n.clientLeft || 0,
      d = e === t ? t.scrollY : e.scrollTop,
      c = e === t ? t.scrollX : e.scrollLeft;
    return {
      top: i.top + d - l,
      left: i.left + c - o
    };
  }
  function b(e, t) {
    return r().getComputedStyle(e, null).getPropertyValue(t);
  }
  function y(e) {
    var t,
      s = e;
    if (s) {
      for (t = 0; null !== (s = s.previousSibling);) 1 === s.nodeType && (t += 1);
      return t;
    }
  }
  function E(e, t) {
    var s = [];
    var a = e.parentElement;
    for (; a;) t ? a.matches(t) && s.push(a) : s.push(a), a = a.parentElement;
    return s;
  }
  function x(e, t) {
    t && e.addEventListener("transitionend", function s(a) {
      a.target === e && (t.call(e, a), e.removeEventListener("transitionend", s));
    });
  }
  function S(e, t, s) {
    var a = r();
    return s ? e["width" === t ? "offsetWidth" : "offsetHeight"] + parseFloat(a.getComputedStyle(e, null).getPropertyValue("width" === t ? "margin-right" : "margin-top")) + parseFloat(a.getComputedStyle(e, null).getPropertyValue("width" === t ? "margin-left" : "margin-bottom")) : e.offsetWidth;
  }
  function T(e) {
    return (Array.isArray(e) ? e : [e]).filter(function (e) {
      return !!e;
    });
  }
  function M(e) {
    return function (t) {
      return Math.abs(t) > 0 && e.browser && e.browser.need3dFix && Math.abs(t) % 90 == 0 ? t + .001 : t;
    };
  }
  var C, P, L;
  function I() {
    return C || (C = function () {
      var e = r(),
        t = a();
      return {
        smoothScroll: t.documentElement && t.documentElement.style && "scrollBehavior" in t.documentElement.style,
        touch: !!("ontouchstart" in e || e.DocumentTouch && t instanceof e.DocumentTouch)
      };
    }()), C;
  }
  function z(e) {
    return void 0 === e && (e = {}), P || (P = function (e) {
      var _ref = void 0 === e ? {} : e,
        t = _ref.userAgent;
      var s = I(),
        a = r(),
        i = a.navigator.platform,
        n = t || a.navigator.userAgent,
        l = {
          ios: !1,
          android: !1
        },
        o = a.screen.width,
        d = a.screen.height,
        c = n.match(/(Android);?[\s\/]+([\d.]+)?/);
      var p = n.match(/(iPad).*OS\s([\d_]+)/);
      var u = n.match(/(iPod)(.*OS\s([\d_]+))?/),
        m = !p && n.match(/(iPhone\sOS|iOS)\s([\d_]+)/),
        h = "Win32" === i;
      var f = "MacIntel" === i;
      return !p && f && s.touch && ["1024x1366", "1366x1024", "834x1194", "1194x834", "834x1112", "1112x834", "768x1024", "1024x768", "820x1180", "1180x820", "810x1080", "1080x810"].indexOf("".concat(o, "x").concat(d)) >= 0 && (p = n.match(/(Version)\/([\d.]+)/), p || (p = [0, 1, "13_0_0"]), f = !1), c && !h && (l.os = "android", l.android = !0), (p || m || u) && (l.os = "ios", l.ios = !0), l;
    }(e)), P;
  }
  function A() {
    return L || (L = function () {
      var e = r(),
        t = z();
      var s = !1;
      function a() {
        var t = e.navigator.userAgent.toLowerCase();
        return t.indexOf("safari") >= 0 && t.indexOf("chrome") < 0 && t.indexOf("android") < 0;
      }
      if (a()) {
        var _t2 = String(e.navigator.userAgent);
        if (_t2.includes("Version/")) {
          var _t2$split$1$split$0$s = _t2.split("Version/")[1].split(" ")[0].split(".").map(function (e) {
              return Number(e);
            }),
            _t2$split$1$split$0$s2 = _slicedToArray(_t2$split$1$split$0$s, 2),
            _e = _t2$split$1$split$0$s2[0],
            _a4 = _t2$split$1$split$0$s2[1];
          s = _e < 16 || 16 === _e && _a4 < 2;
        }
      }
      var i = /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(e.navigator.userAgent),
        n = a();
      return {
        isSafari: s || n,
        needPerspectiveFix: s,
        need3dFix: n || i && t.ios,
        isWebView: i
      };
    }()), L;
  }
  var $ = {
    on: function on(e, t, s) {
      var a = this;
      if (!a.eventsListeners || a.destroyed) return a;
      if ("function" != typeof t) return a;
      var i = s ? "unshift" : "push";
      return e.split(" ").forEach(function (e) {
        a.eventsListeners[e] || (a.eventsListeners[e] = []), a.eventsListeners[e][i](t);
      }), a;
    },
    once: function once(e, t, s) {
      var a = this;
      if (!a.eventsListeners || a.destroyed) return a;
      if ("function" != typeof t) return a;
      function i() {
        a.off(e, i), i.__emitterProxy && delete i.__emitterProxy;
        for (var s = arguments.length, r = new Array(s), n = 0; n < s; n++) r[n] = arguments[n];
        t.apply(a, r);
      }
      return i.__emitterProxy = t, a.on(e, i, s);
    },
    onAny: function onAny(e, t) {
      var s = this;
      if (!s.eventsListeners || s.destroyed) return s;
      if ("function" != typeof e) return s;
      var a = t ? "unshift" : "push";
      return s.eventsAnyListeners.indexOf(e) < 0 && s.eventsAnyListeners[a](e), s;
    },
    offAny: function offAny(e) {
      var t = this;
      if (!t.eventsListeners || t.destroyed) return t;
      if (!t.eventsAnyListeners) return t;
      var s = t.eventsAnyListeners.indexOf(e);
      return s >= 0 && t.eventsAnyListeners.splice(s, 1), t;
    },
    off: function off(e, t) {
      var s = this;
      return !s.eventsListeners || s.destroyed ? s : s.eventsListeners ? (e.split(" ").forEach(function (e) {
        void 0 === t ? s.eventsListeners[e] = [] : s.eventsListeners[e] && s.eventsListeners[e].forEach(function (a, i) {
          (a === t || a.__emitterProxy && a.__emitterProxy === t) && s.eventsListeners[e].splice(i, 1);
        });
      }), s) : s;
    },
    emit: function emit() {
      var e = this;
      if (!e.eventsListeners || e.destroyed) return e;
      if (!e.eventsListeners) return e;
      var t, s, a;
      for (var i = arguments.length, r = new Array(i), n = 0; n < i; n++) r[n] = arguments[n];
      "string" == typeof r[0] || Array.isArray(r[0]) ? (t = r[0], s = r.slice(1, r.length), a = e) : (t = r[0].events, s = r[0].data, a = r[0].context || e), s.unshift(a);
      return (Array.isArray(t) ? t : t.split(" ")).forEach(function (t) {
        e.eventsAnyListeners && e.eventsAnyListeners.length && e.eventsAnyListeners.forEach(function (e) {
          e.apply(a, [t].concat(_toConsumableArray(s)));
        }), e.eventsListeners && e.eventsListeners[t] && e.eventsListeners[t].forEach(function (e) {
          e.apply(a, s);
        });
      }), e;
    }
  };
  var k = function k(e, t, s) {
    t && !e.classList.contains(s) ? e.classList.add(s) : !t && e.classList.contains(s) && e.classList.remove(s);
  };
  var O = function O(e, t, s) {
    t && !e.classList.contains(s) ? e.classList.add(s) : !t && e.classList.contains(s) && e.classList.remove(s);
  };
  var D = function D(e, t) {
      if (!e || e.destroyed || !e.params) return;
      var s = t.closest(e.isElement ? "swiper-slide" : ".".concat(e.params.slideClass));
      if (s) {
        var _t3 = s.querySelector(".".concat(e.params.lazyPreloaderClass));
        !_t3 && e.isElement && (s.shadowRoot ? _t3 = s.shadowRoot.querySelector(".".concat(e.params.lazyPreloaderClass)) : requestAnimationFrame(function () {
          s.shadowRoot && (_t3 = s.shadowRoot.querySelector(".".concat(e.params.lazyPreloaderClass)), _t3 && _t3.remove());
        })), _t3 && _t3.remove();
      }
    },
    G = function G(e, t) {
      if (!e.slides[t]) return;
      var s = e.slides[t].querySelector('[loading="lazy"]');
      s && s.removeAttribute("loading");
    },
    H = function H(e) {
      if (!e || e.destroyed || !e.params) return;
      var t = e.params.lazyPreloadPrevNext;
      var s = e.slides.length;
      if (!s || !t || t < 0) return;
      t = Math.min(t, s);
      var a = "auto" === e.params.slidesPerView ? e.slidesPerViewDynamic() : Math.ceil(e.params.slidesPerView),
        i = e.activeIndex;
      if (e.params.grid && e.params.grid.rows > 1) {
        var _s2 = i,
          _r2 = [_s2 - t];
        return _r2.push.apply(_r2, _toConsumableArray(Array.from({
          length: t
        }).map(function (e, t) {
          return _s2 + a + t;
        }))), void e.slides.forEach(function (t, s) {
          _r2.includes(t.column) && G(e, s);
        });
      }
      var r = i + a - 1;
      if (e.params.rewind || e.params.loop) for (var _a5 = i - t; _a5 <= r + t; _a5 += 1) {
        var _t4 = (_a5 % s + s) % s;
        (_t4 < i || _t4 > r) && G(e, _t4);
      } else for (var _a6 = Math.max(i - t, 0); _a6 <= Math.min(r + t, s - 1); _a6 += 1) _a6 !== i && (_a6 > r || _a6 < i) && G(e, _a6);
    };
  var X = {
    updateSize: function updateSize() {
      var e = this;
      var t, s;
      var a = e.el;
      t = void 0 !== e.params.width && null !== e.params.width ? e.params.width : a.clientWidth, s = void 0 !== e.params.height && null !== e.params.height ? e.params.height : a.clientHeight, 0 === t && e.isHorizontal() || 0 === s && e.isVertical() || (t = t - parseInt(b(a, "padding-left") || 0, 10) - parseInt(b(a, "padding-right") || 0, 10), s = s - parseInt(b(a, "padding-top") || 0, 10) - parseInt(b(a, "padding-bottom") || 0, 10), Number.isNaN(t) && (t = 0), Number.isNaN(s) && (s = 0), Object.assign(e, {
        width: t,
        height: s,
        size: e.isHorizontal() ? t : s
      }));
    },
    updateSlides: function updateSlides() {
      var e = this;
      function t(t, s) {
        return parseFloat(t.getPropertyValue(e.getDirectionLabel(s)) || 0);
      }
      var s = e.params,
        a = e.wrapperEl,
        i = e.slidesEl,
        r = e.size,
        n = e.rtlTranslate,
        l = e.wrongRTL,
        o = e.virtual && s.virtual.enabled,
        d = o ? e.virtual.slides.length : e.slides.length,
        c = f(i, ".".concat(e.params.slideClass, ", swiper-slide")),
        p = o ? e.virtual.slides.length : c.length;
      var m = [];
      var h = [],
        g = [];
      var v = s.slidesOffsetBefore;
      "function" == typeof v && (v = s.slidesOffsetBefore.call(e));
      var w = s.slidesOffsetAfter;
      "function" == typeof w && (w = s.slidesOffsetAfter.call(e));
      var y = e.snapGrid.length,
        E = e.slidesGrid.length;
      var x = s.spaceBetween,
        T = -v,
        M = 0,
        C = 0;
      if (void 0 === r) return;
      "string" == typeof x && x.indexOf("%") >= 0 ? x = parseFloat(x.replace("%", "")) / 100 * r : "string" == typeof x && (x = parseFloat(x)), e.virtualSize = -x, c.forEach(function (e) {
        n ? e.style.marginLeft = "" : e.style.marginRight = "", e.style.marginBottom = "", e.style.marginTop = "";
      }), s.centeredSlides && s.cssMode && (u(a, "--swiper-centered-offset-before", ""), u(a, "--swiper-centered-offset-after", ""));
      var P = s.grid && s.grid.rows > 1 && e.grid;
      var L;
      P ? e.grid.initSlides(c) : e.grid && e.grid.unsetSlides();
      var I = "auto" === s.slidesPerView && s.breakpoints && Object.keys(s.breakpoints).filter(function (e) {
        return void 0 !== s.breakpoints[e].slidesPerView;
      }).length > 0;
      for (var _a7 = 0; _a7 < p; _a7 += 1) {
        var _i2 = void 0;
        if (L = 0, c[_a7] && (_i2 = c[_a7]), P && e.grid.updateSlide(_a7, _i2, c), !c[_a7] || "none" !== b(_i2, "display")) {
          if ("auto" === s.slidesPerView) {
            I && (c[_a7].style[e.getDirectionLabel("width")] = "");
            var _r3 = getComputedStyle(_i2),
              _n = _i2.style.transform,
              _l = _i2.style.webkitTransform;
            if (_n && (_i2.style.transform = "none"), _l && (_i2.style.webkitTransform = "none"), s.roundLengths) L = e.isHorizontal() ? S(_i2, "width", !0) : S(_i2, "height", !0);else {
              var _e2 = t(_r3, "width"),
                _s3 = t(_r3, "padding-left"),
                _a8 = t(_r3, "padding-right"),
                _n2 = t(_r3, "margin-left"),
                _l2 = t(_r3, "margin-right"),
                _o = _r3.getPropertyValue("box-sizing");
              if (_o && "border-box" === _o) L = _e2 + _n2 + _l2;else {
                var _i3 = _i2,
                  _t5 = _i3.clientWidth,
                  _r4 = _i3.offsetWidth;
                L = _e2 + _s3 + _a8 + _n2 + _l2 + (_r4 - _t5);
              }
            }
            _n && (_i2.style.transform = _n), _l && (_i2.style.webkitTransform = _l), s.roundLengths && (L = Math.floor(L));
          } else L = (r - (s.slidesPerView - 1) * x) / s.slidesPerView, s.roundLengths && (L = Math.floor(L)), c[_a7] && (c[_a7].style[e.getDirectionLabel("width")] = "".concat(L, "px"));
          c[_a7] && (c[_a7].swiperSlideSize = L), g.push(L), s.centeredSlides ? (T = T + L / 2 + M / 2 + x, 0 === M && 0 !== _a7 && (T = T - r / 2 - x), 0 === _a7 && (T = T - r / 2 - x), Math.abs(T) < .001 && (T = 0), s.roundLengths && (T = Math.floor(T)), C % s.slidesPerGroup == 0 && m.push(T), h.push(T)) : (s.roundLengths && (T = Math.floor(T)), (C - Math.min(e.params.slidesPerGroupSkip, C)) % e.params.slidesPerGroup == 0 && m.push(T), h.push(T), T = T + L + x), e.virtualSize += L + x, M = L, C += 1;
        }
      }
      if (e.virtualSize = Math.max(e.virtualSize, r) + w, n && l && ("slide" === s.effect || "coverflow" === s.effect) && (a.style.width = "".concat(e.virtualSize + x, "px")), s.setWrapperSize && (a.style[e.getDirectionLabel("width")] = "".concat(e.virtualSize + x, "px")), P && e.grid.updateWrapperSize(L, m), !s.centeredSlides) {
        var _t6 = [];
        for (var _a9 = 0; _a9 < m.length; _a9 += 1) {
          var _i4 = m[_a9];
          s.roundLengths && (_i4 = Math.floor(_i4)), m[_a9] <= e.virtualSize - r && _t6.push(_i4);
        }
        m = _t6, Math.floor(e.virtualSize - r) - Math.floor(m[m.length - 1]) > 1 && m.push(e.virtualSize - r);
      }
      if (o && s.loop) {
        var _t7 = g[0] + x;
        if (s.slidesPerGroup > 1) {
          var _a10 = Math.ceil((e.virtual.slidesBefore + e.virtual.slidesAfter) / s.slidesPerGroup),
            _i5 = _t7 * s.slidesPerGroup;
          for (var _e3 = 0; _e3 < _a10; _e3 += 1) m.push(m[m.length - 1] + _i5);
        }
        for (var _a11 = 0; _a11 < e.virtual.slidesBefore + e.virtual.slidesAfter; _a11 += 1) 1 === s.slidesPerGroup && m.push(m[m.length - 1] + _t7), h.push(h[h.length - 1] + _t7), e.virtualSize += _t7;
      }
      if (0 === m.length && (m = [0]), 0 !== x) {
        var _t8 = e.isHorizontal() && n ? "marginLeft" : e.getDirectionLabel("marginRight");
        c.filter(function (e, t) {
          return !(s.cssMode && !s.loop) || t !== c.length - 1;
        }).forEach(function (e) {
          e.style[_t8] = "".concat(x, "px");
        });
      }
      if (s.centeredSlides && s.centeredSlidesBounds) {
        var _e4 = 0;
        g.forEach(function (t) {
          _e4 += t + (x || 0);
        }), _e4 -= x;
        var _t9 = _e4 > r ? _e4 - r : 0;
        m = m.map(function (e) {
          return e <= 0 ? -v : e > _t9 ? _t9 + w : e;
        });
      }
      if (s.centerInsufficientSlides) {
        var _e5 = 0;
        g.forEach(function (t) {
          _e5 += t + (x || 0);
        }), _e5 -= x;
        var _t10 = (s.slidesOffsetBefore || 0) + (s.slidesOffsetAfter || 0);
        if (_e5 + _t10 < r) {
          var _s4 = (r - _e5 - _t10) / 2;
          m.forEach(function (e, t) {
            m[t] = e - _s4;
          }), h.forEach(function (e, t) {
            h[t] = e + _s4;
          });
        }
      }
      if (Object.assign(e, {
        slides: c,
        snapGrid: m,
        slidesGrid: h,
        slidesSizesGrid: g
      }), s.centeredSlides && s.cssMode && !s.centeredSlidesBounds) {
        u(a, "--swiper-centered-offset-before", -m[0] + "px"), u(a, "--swiper-centered-offset-after", e.size / 2 - g[g.length - 1] / 2 + "px");
        var _t11 = -e.snapGrid[0],
          _s5 = -e.slidesGrid[0];
        e.snapGrid = e.snapGrid.map(function (e) {
          return e + _t11;
        }), e.slidesGrid = e.slidesGrid.map(function (e) {
          return e + _s5;
        });
      }
      if (p !== d && e.emit("slidesLengthChange"), m.length !== y && (e.params.watchOverflow && e.checkOverflow(), e.emit("snapGridLengthChange")), h.length !== E && e.emit("slidesGridLengthChange"), s.watchSlidesProgress && e.updateSlidesOffset(), e.emit("slidesUpdated"), !(o || s.cssMode || "slide" !== s.effect && "fade" !== s.effect)) {
        var _t12 = "".concat(s.containerModifierClass, "backface-hidden"),
          _a12 = e.el.classList.contains(_t12);
        p <= s.maxBackfaceHiddenSlides ? _a12 || e.el.classList.add(_t12) : _a12 && e.el.classList.remove(_t12);
      }
    },
    updateAutoHeight: function updateAutoHeight(e) {
      var t = this,
        s = [],
        a = t.virtual && t.params.virtual.enabled;
      var i,
        r = 0;
      "number" == typeof e ? t.setTransition(e) : !0 === e && t.setTransition(t.params.speed);
      var n = function n(e) {
        return a ? t.slides[t.getSlideIndexByData(e)] : t.slides[e];
      };
      if ("auto" !== t.params.slidesPerView && t.params.slidesPerView > 1) {
        if (t.params.centeredSlides) (t.visibleSlides || []).forEach(function (e) {
          s.push(e);
        });else for (i = 0; i < Math.ceil(t.params.slidesPerView); i += 1) {
          var _e6 = t.activeIndex + i;
          if (_e6 > t.slides.length && !a) break;
          s.push(n(_e6));
        }
      } else s.push(n(t.activeIndex));
      for (i = 0; i < s.length; i += 1) if (void 0 !== s[i]) {
        var _e7 = s[i].offsetHeight;
        r = _e7 > r ? _e7 : r;
      }
      (r || 0 === r) && (t.wrapperEl.style.height = "".concat(r, "px"));
    },
    updateSlidesOffset: function updateSlidesOffset() {
      var e = this,
        t = e.slides,
        s = e.isElement ? e.isHorizontal() ? e.wrapperEl.offsetLeft : e.wrapperEl.offsetTop : 0;
      for (var _a13 = 0; _a13 < t.length; _a13 += 1) t[_a13].swiperSlideOffset = (e.isHorizontal() ? t[_a13].offsetLeft : t[_a13].offsetTop) - s - e.cssOverflowAdjustment();
    },
    updateSlidesProgress: function updateSlidesProgress(e) {
      void 0 === e && (e = this && this.translate || 0);
      var t = this,
        s = t.params,
        a = t.slides,
        i = t.rtlTranslate,
        r = t.snapGrid;
      if (0 === a.length) return;
      void 0 === a[0].swiperSlideOffset && t.updateSlidesOffset();
      var n = -e;
      i && (n = e), t.visibleSlidesIndexes = [], t.visibleSlides = [];
      var l = s.spaceBetween;
      "string" == typeof l && l.indexOf("%") >= 0 ? l = parseFloat(l.replace("%", "")) / 100 * t.size : "string" == typeof l && (l = parseFloat(l));
      for (var _e8 = 0; _e8 < a.length; _e8 += 1) {
        var _o2 = a[_e8];
        var _d = _o2.swiperSlideOffset;
        s.cssMode && s.centeredSlides && (_d -= a[0].swiperSlideOffset);
        var _c = (n + (s.centeredSlides ? t.minTranslate() : 0) - _d) / (_o2.swiperSlideSize + l),
          _p = (n - r[0] + (s.centeredSlides ? t.minTranslate() : 0) - _d) / (_o2.swiperSlideSize + l),
          _u2 = -(n - _d),
          _m = _u2 + t.slidesSizesGrid[_e8],
          _h = _u2 >= 0 && _u2 <= t.size - t.slidesSizesGrid[_e8],
          _f = _u2 >= 0 && _u2 < t.size - 1 || _m > 1 && _m <= t.size || _u2 <= 0 && _m >= t.size;
        _f && (t.visibleSlides.push(_o2), t.visibleSlidesIndexes.push(_e8)), k(_o2, _f, s.slideVisibleClass), k(_o2, _h, s.slideFullyVisibleClass), _o2.progress = i ? -_c : _c, _o2.originalProgress = i ? -_p : _p;
      }
    },
    updateProgress: function updateProgress(e) {
      var t = this;
      if (void 0 === e) {
        var _s6 = t.rtlTranslate ? -1 : 1;
        e = t && t.translate && t.translate * _s6 || 0;
      }
      var s = t.params,
        a = t.maxTranslate() - t.minTranslate();
      var i = t.progress,
        r = t.isBeginning,
        n = t.isEnd,
        l = t.progressLoop;
      var o = r,
        d = n;
      if (0 === a) i = 0, r = !0, n = !0;else {
        i = (e - t.minTranslate()) / a;
        var _s7 = Math.abs(e - t.minTranslate()) < 1,
          _l3 = Math.abs(e - t.maxTranslate()) < 1;
        r = _s7 || i <= 0, n = _l3 || i >= 1, _s7 && (i = 0), _l3 && (i = 1);
      }
      if (s.loop) {
        var _s8 = t.getSlideIndexByData(0),
          _a14 = t.getSlideIndexByData(t.slides.length - 1),
          _i6 = t.slidesGrid[_s8],
          _r5 = t.slidesGrid[_a14],
          _n3 = t.slidesGrid[t.slidesGrid.length - 1],
          _o3 = Math.abs(e);
        l = _o3 >= _i6 ? (_o3 - _i6) / _n3 : (_o3 + _n3 - _r5) / _n3, l > 1 && (l -= 1);
      }
      Object.assign(t, {
        progress: i,
        progressLoop: l,
        isBeginning: r,
        isEnd: n
      }), (s.watchSlidesProgress || s.centeredSlides && s.autoHeight) && t.updateSlidesProgress(e), r && !o && t.emit("reachBeginning toEdge"), n && !d && t.emit("reachEnd toEdge"), (o && !r || d && !n) && t.emit("fromEdge"), t.emit("progress", i);
    },
    updateSlidesClasses: function updateSlidesClasses() {
      var e = this,
        t = e.slides,
        s = e.params,
        a = e.slidesEl,
        i = e.activeIndex,
        r = e.virtual && s.virtual.enabled,
        n = e.grid && s.grid && s.grid.rows > 1,
        l = function l(e) {
          return f(a, ".".concat(s.slideClass).concat(e, ", swiper-slide").concat(e))[0];
        };
      var o, d, c;
      if (r) {
        if (s.loop) {
          var _t13 = i - e.virtual.slidesBefore;
          _t13 < 0 && (_t13 = e.virtual.slides.length + _t13), _t13 >= e.virtual.slides.length && (_t13 -= e.virtual.slides.length), o = l("[data-swiper-slide-index=\"".concat(_t13, "\"]"));
        } else o = l("[data-swiper-slide-index=\"".concat(i, "\"]"));
      } else n ? (o = t.find(function (e) {
        return e.column === i;
      }), c = t.find(function (e) {
        return e.column === i + 1;
      }), d = t.find(function (e) {
        return e.column === i - 1;
      })) : o = t[i];
      o && (n || (c = function (e, t) {
        var s = [];
        for (; e.nextElementSibling;) {
          var _a15 = e.nextElementSibling;
          t ? _a15.matches(t) && s.push(_a15) : s.push(_a15), e = _a15;
        }
        return s;
      }(o, ".".concat(s.slideClass, ", swiper-slide"))[0], s.loop && !c && (c = t[0]), d = function (e, t) {
        var s = [];
        for (; e.previousElementSibling;) {
          var _a16 = e.previousElementSibling;
          t ? _a16.matches(t) && s.push(_a16) : s.push(_a16), e = _a16;
        }
        return s;
      }(o, ".".concat(s.slideClass, ", swiper-slide"))[0], s.loop && 0 === !d && (d = t[t.length - 1]))), t.forEach(function (e) {
        O(e, e === o, s.slideActiveClass), O(e, e === c, s.slideNextClass), O(e, e === d, s.slidePrevClass);
      }), e.emitSlidesClasses();
    },
    updateActiveIndex: function updateActiveIndex(e) {
      var t = this,
        s = t.rtlTranslate ? t.translate : -t.translate,
        a = t.snapGrid,
        i = t.params,
        r = t.activeIndex,
        n = t.realIndex,
        l = t.snapIndex;
      var o,
        d = e;
      var c = function c(e) {
        var s = e - t.virtual.slidesBefore;
        return s < 0 && (s = t.virtual.slides.length + s), s >= t.virtual.slides.length && (s -= t.virtual.slides.length), s;
      };
      if (void 0 === d && (d = function (e) {
        var t = e.slidesGrid,
          s = e.params,
          a = e.rtlTranslate ? e.translate : -e.translate;
        var i;
        for (var _e9 = 0; _e9 < t.length; _e9 += 1) void 0 !== t[_e9 + 1] ? a >= t[_e9] && a < t[_e9 + 1] - (t[_e9 + 1] - t[_e9]) / 2 ? i = _e9 : a >= t[_e9] && a < t[_e9 + 1] && (i = _e9 + 1) : a >= t[_e9] && (i = _e9);
        return s.normalizeSlideIndex && (i < 0 || void 0 === i) && (i = 0), i;
      }(t)), a.indexOf(s) >= 0) o = a.indexOf(s);else {
        var _e10 = Math.min(i.slidesPerGroupSkip, d);
        o = _e10 + Math.floor((d - _e10) / i.slidesPerGroup);
      }
      if (o >= a.length && (o = a.length - 1), d === r && !t.params.loop) return void (o !== l && (t.snapIndex = o, t.emit("snapIndexChange")));
      if (d === r && t.params.loop && t.virtual && t.params.virtual.enabled) return void (t.realIndex = c(d));
      var p = t.grid && i.grid && i.grid.rows > 1;
      var u;
      if (t.virtual && i.virtual.enabled && i.loop) u = c(d);else if (p) {
        var _e11 = t.slides.find(function (e) {
          return e.column === d;
        });
        var _s9 = parseInt(_e11.getAttribute("data-swiper-slide-index"), 10);
        Number.isNaN(_s9) && (_s9 = Math.max(t.slides.indexOf(_e11), 0)), u = Math.floor(_s9 / i.grid.rows);
      } else if (t.slides[d]) {
        var _e12 = t.slides[d].getAttribute("data-swiper-slide-index");
        u = _e12 ? parseInt(_e12, 10) : d;
      } else u = d;
      Object.assign(t, {
        previousSnapIndex: l,
        snapIndex: o,
        previousRealIndex: n,
        realIndex: u,
        previousIndex: r,
        activeIndex: d
      }), t.initialized && H(t), t.emit("activeIndexChange"), t.emit("snapIndexChange"), (t.initialized || t.params.runCallbacksOnInit) && (n !== u && t.emit("realIndexChange"), t.emit("slideChange"));
    },
    updateClickedSlide: function updateClickedSlide(e, t) {
      var s = this,
        a = s.params;
      var i = e.closest(".".concat(a.slideClass, ", swiper-slide"));
      !i && s.isElement && t && t.length > 1 && t.includes(e) && _toConsumableArray(t.slice(t.indexOf(e) + 1, t.length)).forEach(function (e) {
        !i && e.matches && e.matches(".".concat(a.slideClass, ", swiper-slide")) && (i = e);
      });
      var r,
        n = !1;
      if (i) for (var _e13 = 0; _e13 < s.slides.length; _e13 += 1) if (s.slides[_e13] === i) {
        n = !0, r = _e13;
        break;
      }
      if (!i || !n) return s.clickedSlide = void 0, void (s.clickedIndex = void 0);
      s.clickedSlide = i, s.virtual && s.params.virtual.enabled ? s.clickedIndex = parseInt(i.getAttribute("data-swiper-slide-index"), 10) : s.clickedIndex = r, a.slideToClickedSlide && void 0 !== s.clickedIndex && s.clickedIndex !== s.activeIndex && s.slideToClickedSlide();
    }
  };
  var B = {
    getTranslate: function getTranslate(e) {
      void 0 === e && (e = this.isHorizontal() ? "x" : "y");
      var t = this.params,
        s = this.rtlTranslate,
        a = this.translate,
        i = this.wrapperEl;
      if (t.virtualTranslate) return s ? -a : a;
      if (t.cssMode) return a;
      var r = d(i, e);
      return r += this.cssOverflowAdjustment(), s && (r = -r), r || 0;
    },
    setTranslate: function setTranslate(e, t) {
      var s = this,
        a = s.rtlTranslate,
        i = s.params,
        r = s.wrapperEl,
        n = s.progress;
      var l,
        o = 0,
        d = 0;
      s.isHorizontal() ? o = a ? -e : e : d = e, i.roundLengths && (o = Math.floor(o), d = Math.floor(d)), s.previousTranslate = s.translate, s.translate = s.isHorizontal() ? o : d, i.cssMode ? r[s.isHorizontal() ? "scrollLeft" : "scrollTop"] = s.isHorizontal() ? -o : -d : i.virtualTranslate || (s.isHorizontal() ? o -= s.cssOverflowAdjustment() : d -= s.cssOverflowAdjustment(), r.style.transform = "translate3d(".concat(o, "px, ").concat(d, "px, 0px)"));
      var c = s.maxTranslate() - s.minTranslate();
      l = 0 === c ? 0 : (e - s.minTranslate()) / c, l !== n && s.updateProgress(e), s.emit("setTranslate", s.translate, t);
    },
    minTranslate: function minTranslate() {
      return -this.snapGrid[0];
    },
    maxTranslate: function maxTranslate() {
      return -this.snapGrid[this.snapGrid.length - 1];
    },
    translateTo: function translateTo(e, t, s, a, i) {
      void 0 === e && (e = 0), void 0 === t && (t = this.params.speed), void 0 === s && (s = !0), void 0 === a && (a = !0);
      var r = this,
        n = r.params,
        l = r.wrapperEl;
      if (r.animating && n.preventInteractionOnTransition) return !1;
      var o = r.minTranslate(),
        d = r.maxTranslate();
      var c;
      if (c = a && e > o ? o : a && e < d ? d : e, r.updateProgress(c), n.cssMode) {
        var _e14 = r.isHorizontal();
        if (0 === t) l[_e14 ? "scrollLeft" : "scrollTop"] = -c;else {
          if (!r.support.smoothScroll) return m({
            swiper: r,
            targetPosition: -c,
            side: _e14 ? "left" : "top"
          }), !0;
          l.scrollTo(_defineProperty(_defineProperty({}, _e14 ? "left" : "top", -c), "behavior", "smooth"));
        }
        return !0;
      }
      return 0 === t ? (r.setTransition(0), r.setTranslate(c), s && (r.emit("beforeTransitionStart", t, i), r.emit("transitionEnd"))) : (r.setTransition(t), r.setTranslate(c), s && (r.emit("beforeTransitionStart", t, i), r.emit("transitionStart")), r.animating || (r.animating = !0, r.onTranslateToWrapperTransitionEnd || (r.onTranslateToWrapperTransitionEnd = function (e) {
        r && !r.destroyed && e.target === this && (r.wrapperEl.removeEventListener("transitionend", r.onTranslateToWrapperTransitionEnd), r.onTranslateToWrapperTransitionEnd = null, delete r.onTranslateToWrapperTransitionEnd, r.animating = !1, s && r.emit("transitionEnd"));
      }), r.wrapperEl.addEventListener("transitionend", r.onTranslateToWrapperTransitionEnd))), !0;
    }
  };
  function Y(e) {
    var t = e.swiper,
      s = e.runCallbacks,
      a = e.direction,
      i = e.step;
    var r = t.activeIndex,
      n = t.previousIndex;
    var l = a;
    if (l || (l = r > n ? "next" : r < n ? "prev" : "reset"), t.emit("transition".concat(i)), s && r !== n) {
      if ("reset" === l) return void t.emit("slideResetTransition".concat(i));
      t.emit("slideChangeTransition".concat(i)), "next" === l ? t.emit("slideNextTransition".concat(i)) : t.emit("slidePrevTransition".concat(i));
    }
  }
  var N = {
    slideTo: function slideTo(e, t, s, a, i) {
      void 0 === e && (e = 0), void 0 === s && (s = !0), "string" == typeof e && (e = parseInt(e, 10));
      var r = this;
      var n = e;
      n < 0 && (n = 0);
      var l = r.params,
        o = r.snapGrid,
        d = r.slidesGrid,
        c = r.previousIndex,
        p = r.activeIndex,
        u = r.rtlTranslate,
        h = r.wrapperEl,
        f = r.enabled;
      if (!f && !a && !i || r.destroyed || r.animating && l.preventInteractionOnTransition) return !1;
      void 0 === t && (t = r.params.speed);
      var g = Math.min(r.params.slidesPerGroupSkip, n);
      var v = g + Math.floor((n - g) / r.params.slidesPerGroup);
      v >= o.length && (v = o.length - 1);
      var w = -o[v];
      if (l.normalizeSlideIndex) for (var _e15 = 0; _e15 < d.length; _e15 += 1) {
        var _t14 = -Math.floor(100 * w),
          _s10 = Math.floor(100 * d[_e15]),
          _a17 = Math.floor(100 * d[_e15 + 1]);
        void 0 !== d[_e15 + 1] ? _t14 >= _s10 && _t14 < _a17 - (_a17 - _s10) / 2 ? n = _e15 : _t14 >= _s10 && _t14 < _a17 && (n = _e15 + 1) : _t14 >= _s10 && (n = _e15);
      }
      if (r.initialized && n !== p) {
        if (!r.allowSlideNext && (u ? w > r.translate && w > r.minTranslate() : w < r.translate && w < r.minTranslate())) return !1;
        if (!r.allowSlidePrev && w > r.translate && w > r.maxTranslate() && (p || 0) !== n) return !1;
      }
      var b;
      n !== (c || 0) && s && r.emit("beforeSlideChangeStart"), r.updateProgress(w), b = n > p ? "next" : n < p ? "prev" : "reset";
      var y = r.virtual && r.params.virtual.enabled;
      if (!(y && i) && (u && -w === r.translate || !u && w === r.translate)) return r.updateActiveIndex(n), l.autoHeight && r.updateAutoHeight(), r.updateSlidesClasses(), "slide" !== l.effect && r.setTranslate(w), "reset" !== b && (r.transitionStart(s, b), r.transitionEnd(s, b)), !1;
      if (l.cssMode) {
        var _e16 = r.isHorizontal(),
          _s11 = u ? w : -w;
        if (0 === t) y && (r.wrapperEl.style.scrollSnapType = "none", r._immediateVirtual = !0), y && !r._cssModeVirtualInitialSet && r.params.initialSlide > 0 ? (r._cssModeVirtualInitialSet = !0, requestAnimationFrame(function () {
          h[_e16 ? "scrollLeft" : "scrollTop"] = _s11;
        })) : h[_e16 ? "scrollLeft" : "scrollTop"] = _s11, y && requestAnimationFrame(function () {
          r.wrapperEl.style.scrollSnapType = "", r._immediateVirtual = !1;
        });else {
          if (!r.support.smoothScroll) return m({
            swiper: r,
            targetPosition: _s11,
            side: _e16 ? "left" : "top"
          }), !0;
          h.scrollTo(_defineProperty(_defineProperty({}, _e16 ? "left" : "top", _s11), "behavior", "smooth"));
        }
        return !0;
      }
      var E = A().isSafari;
      return y && !i && E && r.isElement && r.virtual.update(!1, !1, n), r.setTransition(t), r.setTranslate(w), r.updateActiveIndex(n), r.updateSlidesClasses(), r.emit("beforeTransitionStart", t, a), r.transitionStart(s, b), 0 === t ? r.transitionEnd(s, b) : r.animating || (r.animating = !0, r.onSlideToWrapperTransitionEnd || (r.onSlideToWrapperTransitionEnd = function (e) {
        r && !r.destroyed && e.target === this && (r.wrapperEl.removeEventListener("transitionend", r.onSlideToWrapperTransitionEnd), r.onSlideToWrapperTransitionEnd = null, delete r.onSlideToWrapperTransitionEnd, r.transitionEnd(s, b));
      }), r.wrapperEl.addEventListener("transitionend", r.onSlideToWrapperTransitionEnd)), !0;
    },
    slideToLoop: function slideToLoop(e, t, s, a) {
      if (void 0 === e && (e = 0), void 0 === s && (s = !0), "string" == typeof e) {
        e = parseInt(e, 10);
      }
      var i = this;
      if (i.destroyed) return;
      void 0 === t && (t = i.params.speed);
      var r = i.grid && i.params.grid && i.params.grid.rows > 1;
      var n = e;
      if (i.params.loop) if (i.virtual && i.params.virtual.enabled) n += i.virtual.slidesBefore;else {
        var _e17;
        if (r) {
          var _t15 = n * i.params.grid.rows;
          _e17 = i.slides.find(function (e) {
            return 1 * e.getAttribute("data-swiper-slide-index") === _t15;
          }).column;
        } else _e17 = i.getSlideIndexByData(n);
        var _t16 = r ? Math.ceil(i.slides.length / i.params.grid.rows) : i.slides.length,
          _s12 = i.params.centeredSlides;
        var _l4 = i.params.slidesPerView;
        "auto" === _l4 ? _l4 = i.slidesPerViewDynamic() : (_l4 = Math.ceil(parseFloat(i.params.slidesPerView, 10)), _s12 && _l4 % 2 == 0 && (_l4 += 1));
        var _o4 = _t16 - _e17 < _l4;
        if (_s12 && (_o4 = _o4 || _e17 < Math.ceil(_l4 / 2)), a && _s12 && "auto" !== i.params.slidesPerView && !r && (_o4 = !1), _o4) {
          var _a18 = _s12 ? _e17 < i.activeIndex ? "prev" : "next" : _e17 - i.activeIndex - 1 < i.params.slidesPerView ? "next" : "prev";
          i.loopFix({
            direction: _a18,
            slideTo: !0,
            activeSlideIndex: "next" === _a18 ? _e17 + 1 : _e17 - _t16 + 1,
            slideRealIndex: "next" === _a18 ? i.realIndex : void 0
          });
        }
        if (r) {
          var _e18 = n * i.params.grid.rows;
          n = i.slides.find(function (t) {
            return 1 * t.getAttribute("data-swiper-slide-index") === _e18;
          }).column;
        } else n = i.getSlideIndexByData(n);
      }
      return requestAnimationFrame(function () {
        i.slideTo(n, t, s, a);
      }), i;
    },
    slideNext: function slideNext(e, t, s) {
      void 0 === t && (t = !0);
      var a = this,
        i = a.enabled,
        r = a.params,
        n = a.animating;
      if (!i || a.destroyed) return a;
      void 0 === e && (e = a.params.speed);
      var l = r.slidesPerGroup;
      "auto" === r.slidesPerView && 1 === r.slidesPerGroup && r.slidesPerGroupAuto && (l = Math.max(a.slidesPerViewDynamic("current", !0), 1));
      var o = a.activeIndex < r.slidesPerGroupSkip ? 1 : l,
        d = a.virtual && r.virtual.enabled;
      if (r.loop) {
        if (n && !d && r.loopPreventsSliding) return !1;
        if (a.loopFix({
          direction: "next"
        }), a._clientLeft = a.wrapperEl.clientLeft, a.activeIndex === a.slides.length - 1 && r.cssMode) return requestAnimationFrame(function () {
          a.slideTo(a.activeIndex + o, e, t, s);
        }), !0;
      }
      return r.rewind && a.isEnd ? a.slideTo(0, e, t, s) : a.slideTo(a.activeIndex + o, e, t, s);
    },
    slidePrev: function slidePrev(e, t, s) {
      void 0 === t && (t = !0);
      var a = this,
        i = a.params,
        r = a.snapGrid,
        n = a.slidesGrid,
        l = a.rtlTranslate,
        o = a.enabled,
        d = a.animating;
      if (!o || a.destroyed) return a;
      void 0 === e && (e = a.params.speed);
      var c = a.virtual && i.virtual.enabled;
      if (i.loop) {
        if (d && !c && i.loopPreventsSliding) return !1;
        a.loopFix({
          direction: "prev"
        }), a._clientLeft = a.wrapperEl.clientLeft;
      }
      function p(e) {
        return e < 0 ? -Math.floor(Math.abs(e)) : Math.floor(e);
      }
      var u = p(l ? a.translate : -a.translate),
        m = r.map(function (e) {
          return p(e);
        }),
        h = i.freeMode && i.freeMode.enabled;
      var f = r[m.indexOf(u) - 1];
      if (void 0 === f && (i.cssMode || h)) {
        var _e19;
        r.forEach(function (t, s) {
          u >= t && (_e19 = s);
        }), void 0 !== _e19 && (f = h ? r[_e19] : r[_e19 > 0 ? _e19 - 1 : _e19]);
      }
      var g = 0;
      if (void 0 !== f && (g = n.indexOf(f), g < 0 && (g = a.activeIndex - 1), "auto" === i.slidesPerView && 1 === i.slidesPerGroup && i.slidesPerGroupAuto && (g = g - a.slidesPerViewDynamic("previous", !0) + 1, g = Math.max(g, 0))), i.rewind && a.isBeginning) {
        var _i7 = a.params.virtual && a.params.virtual.enabled && a.virtual ? a.virtual.slides.length - 1 : a.slides.length - 1;
        return a.slideTo(_i7, e, t, s);
      }
      return i.loop && 0 === a.activeIndex && i.cssMode ? (requestAnimationFrame(function () {
        a.slideTo(g, e, t, s);
      }), !0) : a.slideTo(g, e, t, s);
    },
    slideReset: function slideReset(e, t, s) {
      void 0 === t && (t = !0);
      var a = this;
      if (!a.destroyed) return void 0 === e && (e = a.params.speed), a.slideTo(a.activeIndex, e, t, s);
    },
    slideToClosest: function slideToClosest(e, t, s, a) {
      void 0 === t && (t = !0), void 0 === a && (a = .5);
      var i = this;
      if (i.destroyed) return;
      void 0 === e && (e = i.params.speed);
      var r = i.activeIndex;
      var n = Math.min(i.params.slidesPerGroupSkip, r),
        l = n + Math.floor((r - n) / i.params.slidesPerGroup),
        o = i.rtlTranslate ? i.translate : -i.translate;
      if (o >= i.snapGrid[l]) {
        var _e20 = i.snapGrid[l];
        o - _e20 > (i.snapGrid[l + 1] - _e20) * a && (r += i.params.slidesPerGroup);
      } else {
        var _e21 = i.snapGrid[l - 1];
        o - _e21 <= (i.snapGrid[l] - _e21) * a && (r -= i.params.slidesPerGroup);
      }
      return r = Math.max(r, 0), r = Math.min(r, i.slidesGrid.length - 1), i.slideTo(r, e, t, s);
    },
    slideToClickedSlide: function slideToClickedSlide() {
      var e = this;
      if (e.destroyed) return;
      var t = e.params,
        s = e.slidesEl,
        a = "auto" === t.slidesPerView ? e.slidesPerViewDynamic() : t.slidesPerView;
      var i,
        r = e.clickedIndex;
      var n = e.isElement ? "swiper-slide" : ".".concat(t.slideClass);
      if (t.loop) {
        if (e.animating) return;
        i = parseInt(e.clickedSlide.getAttribute("data-swiper-slide-index"), 10), t.centeredSlides ? r < e.loopedSlides - a / 2 || r > e.slides.length - e.loopedSlides + a / 2 ? (e.loopFix(), r = e.getSlideIndex(f(s, "".concat(n, "[data-swiper-slide-index=\"").concat(i, "\"]"))[0]), l(function () {
          e.slideTo(r);
        })) : e.slideTo(r) : r > e.slides.length - a ? (e.loopFix(), r = e.getSlideIndex(f(s, "".concat(n, "[data-swiper-slide-index=\"").concat(i, "\"]"))[0]), l(function () {
          e.slideTo(r);
        })) : e.slideTo(r);
      } else e.slideTo(r);
    }
  };
  var R = {
    loopCreate: function loopCreate(e) {
      var t = this,
        s = t.params,
        a = t.slidesEl;
      if (!s.loop || t.virtual && t.params.virtual.enabled) return;
      var i = function i() {
          f(a, ".".concat(s.slideClass, ", swiper-slide")).forEach(function (e, t) {
            e.setAttribute("data-swiper-slide-index", t);
          });
        },
        r = t.grid && s.grid && s.grid.rows > 1,
        n = s.slidesPerGroup * (r ? s.grid.rows : 1),
        l = t.slides.length % n != 0,
        o = r && t.slides.length % s.grid.rows != 0,
        d = function d(e) {
          for (var _a19 = 0; _a19 < e; _a19 += 1) {
            var _e22 = t.isElement ? v("swiper-slide", [s.slideBlankClass]) : v("div", [s.slideClass, s.slideBlankClass]);
            t.slidesEl.append(_e22);
          }
        };
      if (l) {
        if (s.loopAddBlankSlides) {
          d(n - t.slides.length % n), t.recalcSlides(), t.updateSlides();
        } else g("Swiper Loop Warning: The number of slides is not even to slidesPerGroup, loop mode may not function properly. You need to add more slides (or make duplicates, or empty slides)");
        i();
      } else if (o) {
        if (s.loopAddBlankSlides) {
          d(s.grid.rows - t.slides.length % s.grid.rows), t.recalcSlides(), t.updateSlides();
        } else g("Swiper Loop Warning: The number of slides is not even to grid.rows, loop mode may not function properly. You need to add more slides (or make duplicates, or empty slides)");
        i();
      } else i();
      t.loopFix({
        slideRealIndex: e,
        direction: s.centeredSlides ? void 0 : "next"
      });
    },
    loopFix: function loopFix(e) {
      var _ref2 = void 0 === e ? {} : e,
        t = _ref2.slideRealIndex,
        _ref2$slideTo = _ref2.slideTo,
        s = _ref2$slideTo === void 0 ? !0 : _ref2$slideTo,
        a = _ref2.direction,
        i = _ref2.setTranslate,
        r = _ref2.activeSlideIndex,
        n = _ref2.byController,
        l = _ref2.byMousewheel;
      var o = this;
      if (!o.params.loop) return;
      o.emit("beforeLoopFix");
      var d = o.slides,
        c = o.allowSlidePrev,
        p = o.allowSlideNext,
        u = o.slidesEl,
        m = o.params,
        h = m.centeredSlides;
      if (o.allowSlidePrev = !0, o.allowSlideNext = !0, o.virtual && m.virtual.enabled) return s && (m.centeredSlides || 0 !== o.snapIndex ? m.centeredSlides && o.snapIndex < m.slidesPerView ? o.slideTo(o.virtual.slides.length + o.snapIndex, 0, !1, !0) : o.snapIndex === o.snapGrid.length - 1 && o.slideTo(o.virtual.slidesBefore, 0, !1, !0) : o.slideTo(o.virtual.slides.length, 0, !1, !0)), o.allowSlidePrev = c, o.allowSlideNext = p, void o.emit("loopFix");
      var f = m.slidesPerView;
      "auto" === f ? f = o.slidesPerViewDynamic() : (f = Math.ceil(parseFloat(m.slidesPerView, 10)), h && f % 2 == 0 && (f += 1));
      var v = m.slidesPerGroupAuto ? f : m.slidesPerGroup;
      var w = v;
      w % v != 0 && (w += v - w % v), w += m.loopAdditionalSlides, o.loopedSlides = w;
      var b = o.grid && m.grid && m.grid.rows > 1;
      d.length < f + w ? g("Swiper Loop Warning: The number of slides is not enough for loop mode, it will be disabled and not function properly. You need to add more slides (or make duplicates) or lower the values of slidesPerView and slidesPerGroup parameters") : b && "row" === m.grid.fill && g("Swiper Loop Warning: Loop mode is not compatible with grid.fill = `row`");
      var y = [],
        E = [];
      var x = o.activeIndex;
      void 0 === r ? r = o.getSlideIndex(d.find(function (e) {
        return e.classList.contains(m.slideActiveClass);
      })) : x = r;
      var S = "next" === a || !a,
        T = "prev" === a || !a;
      var M = 0,
        C = 0;
      var P = b ? Math.ceil(d.length / m.grid.rows) : d.length,
        L = (b ? d[r].column : r) + (h && void 0 === i ? -f / 2 + .5 : 0);
      if (L < w) {
        M = Math.max(w - L, v);
        for (var _e23 = 0; _e23 < w - L; _e23 += 1) {
          var _t17 = _e23 - Math.floor(_e23 / P) * P;
          if (b) {
            var _e24 = P - _t17 - 1;
            for (var _t18 = d.length - 1; _t18 >= 0; _t18 -= 1) d[_t18].column === _e24 && y.push(_t18);
          } else y.push(P - _t17 - 1);
        }
      } else if (L + f > P - w) {
        C = Math.max(L - (P - 2 * w), v);
        var _loop = function _loop() {
          var t = _e25 - Math.floor(_e25 / P) * P;
          b ? d.forEach(function (e, s) {
            e.column === t && E.push(s);
          }) : E.push(t);
        };
        for (var _e25 = 0; _e25 < C; _e25 += 1) {
          _loop();
        }
      }
      if (o.__preventObserver__ = !0, requestAnimationFrame(function () {
        o.__preventObserver__ = !1;
      }), T && y.forEach(function (e) {
        d[e].swiperLoopMoveDOM = !0, u.prepend(d[e]), d[e].swiperLoopMoveDOM = !1;
      }), S && E.forEach(function (e) {
        d[e].swiperLoopMoveDOM = !0, u.append(d[e]), d[e].swiperLoopMoveDOM = !1;
      }), o.recalcSlides(), "auto" === m.slidesPerView ? o.updateSlides() : b && (y.length > 0 && T || E.length > 0 && S) && o.slides.forEach(function (e, t) {
        o.grid.updateSlide(t, e, o.slides);
      }), m.watchSlidesProgress && o.updateSlidesOffset(), s) if (y.length > 0 && T) {
        if (void 0 === t) {
          var _e26 = o.slidesGrid[x],
            _t19 = o.slidesGrid[x + M] - _e26;
          l ? o.setTranslate(o.translate - _t19) : (o.slideTo(x + Math.ceil(M), 0, !1, !0), i && (o.touchEventsData.startTranslate = o.touchEventsData.startTranslate - _t19, o.touchEventsData.currentTranslate = o.touchEventsData.currentTranslate - _t19));
        } else if (i) {
          var _e27 = b ? y.length / m.grid.rows : y.length;
          o.slideTo(o.activeIndex + _e27, 0, !1, !0), o.touchEventsData.currentTranslate = o.translate;
        }
      } else if (E.length > 0 && S) if (void 0 === t) {
        var _e28 = o.slidesGrid[x],
          _t20 = o.slidesGrid[x - C] - _e28;
        l ? o.setTranslate(o.translate - _t20) : (o.slideTo(x - C, 0, !1, !0), i && (o.touchEventsData.startTranslate = o.touchEventsData.startTranslate - _t20, o.touchEventsData.currentTranslate = o.touchEventsData.currentTranslate - _t20));
      } else {
        var _e29 = b ? E.length / m.grid.rows : E.length;
        o.slideTo(o.activeIndex - _e29, 0, !1, !0);
      }
      if (o.allowSlidePrev = c, o.allowSlideNext = p, o.controller && o.controller.control && !n) {
        var _e30 = {
          slideRealIndex: t,
          direction: a,
          setTranslate: i,
          activeSlideIndex: r,
          byController: !0
        };
        Array.isArray(o.controller.control) ? o.controller.control.forEach(function (t) {
          !t.destroyed && t.params.loop && t.loopFix(_objectSpread(_objectSpread({}, _e30), {}, {
            slideTo: t.params.slidesPerView === m.slidesPerView && s
          }));
        }) : o.controller.control instanceof o.constructor && o.controller.control.params.loop && o.controller.control.loopFix(_objectSpread(_objectSpread({}, _e30), {}, {
          slideTo: o.controller.control.params.slidesPerView === m.slidesPerView && s
        }));
      }
      o.emit("loopFix");
    },
    loopDestroy: function loopDestroy() {
      var e = this,
        t = e.params,
        s = e.slidesEl;
      if (!t.loop || e.virtual && e.params.virtual.enabled) return;
      e.recalcSlides();
      var a = [];
      e.slides.forEach(function (e) {
        var t = void 0 === e.swiperSlideIndex ? 1 * e.getAttribute("data-swiper-slide-index") : e.swiperSlideIndex;
        a[t] = e;
      }), e.slides.forEach(function (e) {
        e.removeAttribute("data-swiper-slide-index");
      }), a.forEach(function (e) {
        s.append(e);
      }), e.recalcSlides(), e.slideTo(e.realIndex, 0);
    }
  };
  function q(e, t, s) {
    var a = r(),
      i = e.params,
      n = i.edgeSwipeDetection,
      l = i.edgeSwipeThreshold;
    return !n || !(s <= l || s >= a.innerWidth - l) || "prevent" === n && (t.preventDefault(), !0);
  }
  function _(e) {
    var t = this,
      s = a();
    var i = e;
    i.originalEvent && (i = i.originalEvent);
    var n = t.touchEventsData;
    if ("pointerdown" === i.type) {
      if (null !== n.pointerId && n.pointerId !== i.pointerId) return;
      n.pointerId = i.pointerId;
    } else "touchstart" === i.type && 1 === i.targetTouches.length && (n.touchId = i.targetTouches[0].identifier);
    if ("touchstart" === i.type) return void q(t, i, i.targetTouches[0].pageX);
    var l = t.params,
      d = t.touches,
      c = t.enabled;
    if (!c) return;
    if (!l.simulateTouch && "mouse" === i.pointerType) return;
    if (t.animating && l.preventInteractionOnTransition) return;
    !t.animating && l.cssMode && l.loop && t.loopFix();
    var p = i.target;
    if ("wrapper" === l.touchEventsTarget && !function (e, t) {
      var s = r();
      var a = t.contains(e);
      !a && s.HTMLSlotElement && t instanceof HTMLSlotElement && (a = _toConsumableArray(t.assignedElements()).includes(e), a || (a = function (e, t) {
        var s = [t];
        for (; s.length > 0;) {
          var _t21 = s.shift();
          if (e === _t21) return !0;
          s.push.apply(s, _toConsumableArray(_t21.children).concat(_toConsumableArray(_t21.shadowRoot ? _t21.shadowRoot.children : []), _toConsumableArray(_t21.assignedElements ? _t21.assignedElements() : [])));
        }
      }(e, t)));
      return a;
    }(p, t.wrapperEl)) return;
    if ("which" in i && 3 === i.which) return;
    if ("button" in i && i.button > 0) return;
    if (n.isTouched && n.isMoved) return;
    var u = !!l.noSwipingClass && "" !== l.noSwipingClass,
      m = i.composedPath ? i.composedPath() : i.path;
    u && i.target && i.target.shadowRoot && m && (p = m[0]);
    var h = l.noSwipingSelector ? l.noSwipingSelector : ".".concat(l.noSwipingClass),
      f = !(!i.target || !i.target.shadowRoot);
    if (l.noSwiping && (f ? function (e, t) {
      return void 0 === t && (t = this), function t(s) {
        if (!s || s === a() || s === r()) return null;
        s.assignedSlot && (s = s.assignedSlot);
        var i = s.closest(e);
        return i || s.getRootNode ? i || t(s.getRootNode().host) : null;
      }(t);
    }(h, p) : p.closest(h))) return void (t.allowClick = !0);
    if (l.swipeHandler && !p.closest(l.swipeHandler)) return;
    d.currentX = i.pageX, d.currentY = i.pageY;
    var g = d.currentX,
      v = d.currentY;
    if (!q(t, i, g)) return;
    Object.assign(n, {
      isTouched: !0,
      isMoved: !1,
      allowTouchCallbacks: !0,
      isScrolling: void 0,
      startMoving: void 0
    }), d.startX = g, d.startY = v, n.touchStartTime = o(), t.allowClick = !0, t.updateSize(), t.swipeDirection = void 0, l.threshold > 0 && (n.allowThresholdMove = !1);
    var w = !0;
    p.matches(n.focusableElements) && (w = !1, "SELECT" === p.nodeName && (n.isTouched = !1)), s.activeElement && s.activeElement.matches(n.focusableElements) && s.activeElement !== p && ("mouse" === i.pointerType || "mouse" !== i.pointerType && !p.matches(n.focusableElements)) && s.activeElement.blur();
    var b = w && t.allowTouchMove && l.touchStartPreventDefault;
    !l.touchStartForcePreventDefault && !b || p.isContentEditable || i.preventDefault(), l.freeMode && l.freeMode.enabled && t.freeMode && t.animating && !l.cssMode && t.freeMode.onTouchStart(), t.emit("touchStart", i);
  }
  function F(e) {
    var t = a(),
      s = this,
      i = s.touchEventsData,
      r = s.params,
      n = s.touches,
      l = s.rtlTranslate,
      d = s.enabled;
    if (!d) return;
    if (!r.simulateTouch && "mouse" === e.pointerType) return;
    var c,
      p = e;
    if (p.originalEvent && (p = p.originalEvent), "pointermove" === p.type) {
      if (null !== i.touchId) return;
      if (p.pointerId !== i.pointerId) return;
    }
    if ("touchmove" === p.type) {
      if (c = _toConsumableArray(p.changedTouches).find(function (e) {
        return e.identifier === i.touchId;
      }), !c || c.identifier !== i.touchId) return;
    } else c = p;
    if (!i.isTouched) return void (i.startMoving && i.isScrolling && s.emit("touchMoveOpposite", p));
    var u = c.pageX,
      m = c.pageY;
    if (p.preventedByNestedSwiper) return n.startX = u, void (n.startY = m);
    if (!s.allowTouchMove) return p.target.matches(i.focusableElements) || (s.allowClick = !1), void (i.isTouched && (Object.assign(n, {
      startX: u,
      startY: m,
      currentX: u,
      currentY: m
    }), i.touchStartTime = o()));
    if (r.touchReleaseOnEdges && !r.loop) if (s.isVertical()) {
      if (m < n.startY && s.translate <= s.maxTranslate() || m > n.startY && s.translate >= s.minTranslate()) return i.isTouched = !1, void (i.isMoved = !1);
    } else if (u < n.startX && s.translate <= s.maxTranslate() || u > n.startX && s.translate >= s.minTranslate()) return;
    if (t.activeElement && t.activeElement.matches(i.focusableElements) && t.activeElement !== p.target && "mouse" !== p.pointerType && t.activeElement.blur(), t.activeElement && p.target === t.activeElement && p.target.matches(i.focusableElements)) return i.isMoved = !0, void (s.allowClick = !1);
    i.allowTouchCallbacks && s.emit("touchMove", p), n.previousX = n.currentX, n.previousY = n.currentY, n.currentX = u, n.currentY = m;
    var h = n.currentX - n.startX,
      f = n.currentY - n.startY;
    if (s.params.threshold && Math.sqrt(Math.pow(h, 2) + Math.pow(f, 2)) < s.params.threshold) return;
    if (void 0 === i.isScrolling) {
      var _e31;
      s.isHorizontal() && n.currentY === n.startY || s.isVertical() && n.currentX === n.startX ? i.isScrolling = !1 : h * h + f * f >= 25 && (_e31 = 180 * Math.atan2(Math.abs(f), Math.abs(h)) / Math.PI, i.isScrolling = s.isHorizontal() ? _e31 > r.touchAngle : 90 - _e31 > r.touchAngle);
    }
    if (i.isScrolling && s.emit("touchMoveOpposite", p), void 0 === i.startMoving && (n.currentX === n.startX && n.currentY === n.startY || (i.startMoving = !0)), i.isScrolling || "touchmove" === p.type && i.preventTouchMoveFromPointerMove) return void (i.isTouched = !1);
    if (!i.startMoving) return;
    s.allowClick = !1, !r.cssMode && p.cancelable && p.preventDefault(), r.touchMoveStopPropagation && !r.nested && p.stopPropagation();
    var g = s.isHorizontal() ? h : f,
      v = s.isHorizontal() ? n.currentX - n.previousX : n.currentY - n.previousY;
    r.oneWayMovement && (g = Math.abs(g) * (l ? 1 : -1), v = Math.abs(v) * (l ? 1 : -1)), n.diff = g, g *= r.touchRatio, l && (g = -g, v = -v);
    var w = s.touchesDirection;
    s.swipeDirection = g > 0 ? "prev" : "next", s.touchesDirection = v > 0 ? "prev" : "next";
    var b = s.params.loop && !r.cssMode,
      y = "next" === s.touchesDirection && s.allowSlideNext || "prev" === s.touchesDirection && s.allowSlidePrev;
    if (!i.isMoved) {
      if (b && y && s.loopFix({
        direction: s.swipeDirection
      }), i.startTranslate = s.getTranslate(), s.setTransition(0), s.animating) {
        var _e32 = new window.CustomEvent("transitionend", {
          bubbles: !0,
          cancelable: !0,
          detail: {
            bySwiperTouchMove: !0
          }
        });
        s.wrapperEl.dispatchEvent(_e32);
      }
      i.allowMomentumBounce = !1, !r.grabCursor || !0 !== s.allowSlideNext && !0 !== s.allowSlidePrev || s.setGrabCursor(!0), s.emit("sliderFirstMove", p);
    }
    if (new Date().getTime(), !1 !== r._loopSwapReset && i.isMoved && i.allowThresholdMove && w !== s.touchesDirection && b && y && Math.abs(g) >= 1) return Object.assign(n, {
      startX: u,
      startY: m,
      currentX: u,
      currentY: m,
      startTranslate: i.currentTranslate
    }), i.loopSwapReset = !0, void (i.startTranslate = i.currentTranslate);
    s.emit("sliderMove", p), i.isMoved = !0, i.currentTranslate = g + i.startTranslate;
    var E = !0,
      x = r.resistanceRatio;
    if (r.touchReleaseOnEdges && (x = 0), g > 0 ? (b && y && i.allowThresholdMove && i.currentTranslate > (r.centeredSlides ? s.minTranslate() - s.slidesSizesGrid[s.activeIndex + 1] - ("auto" !== r.slidesPerView && s.slides.length - r.slidesPerView >= 2 ? s.slidesSizesGrid[s.activeIndex + 1] + s.params.spaceBetween : 0) - s.params.spaceBetween : s.minTranslate()) && s.loopFix({
      direction: "prev",
      setTranslate: !0,
      activeSlideIndex: 0
    }), i.currentTranslate > s.minTranslate() && (E = !1, r.resistance && (i.currentTranslate = s.minTranslate() - 1 + Math.pow(-s.minTranslate() + i.startTranslate + g, x)))) : g < 0 && (b && y && i.allowThresholdMove && i.currentTranslate < (r.centeredSlides ? s.maxTranslate() + s.slidesSizesGrid[s.slidesSizesGrid.length - 1] + s.params.spaceBetween + ("auto" !== r.slidesPerView && s.slides.length - r.slidesPerView >= 2 ? s.slidesSizesGrid[s.slidesSizesGrid.length - 1] + s.params.spaceBetween : 0) : s.maxTranslate()) && s.loopFix({
      direction: "next",
      setTranslate: !0,
      activeSlideIndex: s.slides.length - ("auto" === r.slidesPerView ? s.slidesPerViewDynamic() : Math.ceil(parseFloat(r.slidesPerView, 10)))
    }), i.currentTranslate < s.maxTranslate() && (E = !1, r.resistance && (i.currentTranslate = s.maxTranslate() + 1 - Math.pow(s.maxTranslate() - i.startTranslate - g, x)))), E && (p.preventedByNestedSwiper = !0), !s.allowSlideNext && "next" === s.swipeDirection && i.currentTranslate < i.startTranslate && (i.currentTranslate = i.startTranslate), !s.allowSlidePrev && "prev" === s.swipeDirection && i.currentTranslate > i.startTranslate && (i.currentTranslate = i.startTranslate), s.allowSlidePrev || s.allowSlideNext || (i.currentTranslate = i.startTranslate), r.threshold > 0) {
      if (!(Math.abs(g) > r.threshold || i.allowThresholdMove)) return void (i.currentTranslate = i.startTranslate);
      if (!i.allowThresholdMove) return i.allowThresholdMove = !0, n.startX = n.currentX, n.startY = n.currentY, i.currentTranslate = i.startTranslate, void (n.diff = s.isHorizontal() ? n.currentX - n.startX : n.currentY - n.startY);
    }
    r.followFinger && !r.cssMode && ((r.freeMode && r.freeMode.enabled && s.freeMode || r.watchSlidesProgress) && (s.updateActiveIndex(), s.updateSlidesClasses()), r.freeMode && r.freeMode.enabled && s.freeMode && s.freeMode.onTouchMove(), s.updateProgress(i.currentTranslate), s.setTranslate(i.currentTranslate));
  }
  function V(e) {
    var t = this,
      s = t.touchEventsData;
    var a,
      i = e;
    i.originalEvent && (i = i.originalEvent);
    if ("touchend" === i.type || "touchcancel" === i.type) {
      if (a = _toConsumableArray(i.changedTouches).find(function (e) {
        return e.identifier === s.touchId;
      }), !a || a.identifier !== s.touchId) return;
    } else {
      if (null !== s.touchId) return;
      if (i.pointerId !== s.pointerId) return;
      a = i;
    }
    if (["pointercancel", "pointerout", "pointerleave", "contextmenu"].includes(i.type)) {
      if (!(["pointercancel", "contextmenu"].includes(i.type) && (t.browser.isSafari || t.browser.isWebView))) return;
    }
    s.pointerId = null, s.touchId = null;
    var r = t.params,
      n = t.touches,
      d = t.rtlTranslate,
      c = t.slidesGrid,
      p = t.enabled;
    if (!p) return;
    if (!r.simulateTouch && "mouse" === i.pointerType) return;
    if (s.allowTouchCallbacks && t.emit("touchEnd", i), s.allowTouchCallbacks = !1, !s.isTouched) return s.isMoved && r.grabCursor && t.setGrabCursor(!1), s.isMoved = !1, void (s.startMoving = !1);
    r.grabCursor && s.isMoved && s.isTouched && (!0 === t.allowSlideNext || !0 === t.allowSlidePrev) && t.setGrabCursor(!1);
    var u = o(),
      m = u - s.touchStartTime;
    if (t.allowClick) {
      var _e33 = i.path || i.composedPath && i.composedPath();
      t.updateClickedSlide(_e33 && _e33[0] || i.target, _e33), t.emit("tap click", i), m < 300 && u - s.lastClickTime < 300 && t.emit("doubleTap doubleClick", i);
    }
    if (s.lastClickTime = o(), l(function () {
      t.destroyed || (t.allowClick = !0);
    }), !s.isTouched || !s.isMoved || !t.swipeDirection || 0 === n.diff && !s.loopSwapReset || s.currentTranslate === s.startTranslate && !s.loopSwapReset) return s.isTouched = !1, s.isMoved = !1, void (s.startMoving = !1);
    var h;
    if (s.isTouched = !1, s.isMoved = !1, s.startMoving = !1, h = r.followFinger ? d ? t.translate : -t.translate : -s.currentTranslate, r.cssMode) return;
    if (r.freeMode && r.freeMode.enabled) return void t.freeMode.onTouchEnd({
      currentPos: h
    });
    var f = h >= -t.maxTranslate() && !t.params.loop;
    var g = 0,
      v = t.slidesSizesGrid[0];
    for (var _e34 = 0; _e34 < c.length; _e34 += _e34 < r.slidesPerGroupSkip ? 1 : r.slidesPerGroup) {
      var _t22 = _e34 < r.slidesPerGroupSkip - 1 ? 1 : r.slidesPerGroup;
      void 0 !== c[_e34 + _t22] ? (f || h >= c[_e34] && h < c[_e34 + _t22]) && (g = _e34, v = c[_e34 + _t22] - c[_e34]) : (f || h >= c[_e34]) && (g = _e34, v = c[c.length - 1] - c[c.length - 2]);
    }
    var w = null,
      b = null;
    r.rewind && (t.isBeginning ? b = r.virtual && r.virtual.enabled && t.virtual ? t.virtual.slides.length - 1 : t.slides.length - 1 : t.isEnd && (w = 0));
    var y = (h - c[g]) / v,
      E = g < r.slidesPerGroupSkip - 1 ? 1 : r.slidesPerGroup;
    if (m > r.longSwipesMs) {
      if (!r.longSwipes) return void t.slideTo(t.activeIndex);
      "next" === t.swipeDirection && (y >= r.longSwipesRatio ? t.slideTo(r.rewind && t.isEnd ? w : g + E) : t.slideTo(g)), "prev" === t.swipeDirection && (y > 1 - r.longSwipesRatio ? t.slideTo(g + E) : null !== b && y < 0 && Math.abs(y) > r.longSwipesRatio ? t.slideTo(b) : t.slideTo(g));
    } else {
      if (!r.shortSwipes) return void t.slideTo(t.activeIndex);
      t.navigation && (i.target === t.navigation.nextEl || i.target === t.navigation.prevEl) ? i.target === t.navigation.nextEl ? t.slideTo(g + E) : t.slideTo(g) : ("next" === t.swipeDirection && t.slideTo(null !== w ? w : g + E), "prev" === t.swipeDirection && t.slideTo(null !== b ? b : g));
    }
  }
  function W() {
    var e = this,
      t = e.params,
      s = e.el;
    if (s && 0 === s.offsetWidth) return;
    t.breakpoints && e.setBreakpoint();
    var a = e.allowSlideNext,
      i = e.allowSlidePrev,
      r = e.snapGrid,
      n = e.virtual && e.params.virtual.enabled;
    e.allowSlideNext = !0, e.allowSlidePrev = !0, e.updateSize(), e.updateSlides(), e.updateSlidesClasses();
    var l = n && t.loop;
    !("auto" === t.slidesPerView || t.slidesPerView > 1) || !e.isEnd || e.isBeginning || e.params.centeredSlides || l ? e.params.loop && !n ? e.slideToLoop(e.realIndex, 0, !1, !0) : e.slideTo(e.activeIndex, 0, !1, !0) : e.slideTo(e.slides.length - 1, 0, !1, !0), e.autoplay && e.autoplay.running && e.autoplay.paused && (clearTimeout(e.autoplay.resizeTimeout), e.autoplay.resizeTimeout = setTimeout(function () {
      e.autoplay && e.autoplay.running && e.autoplay.paused && e.autoplay.resume();
    }, 500)), e.allowSlidePrev = i, e.allowSlideNext = a, e.params.watchOverflow && r !== e.snapGrid && e.checkOverflow();
  }
  function j(e) {
    var t = this;
    t.enabled && (t.allowClick || (t.params.preventClicks && e.preventDefault(), t.params.preventClicksPropagation && t.animating && (e.stopPropagation(), e.stopImmediatePropagation())));
  }
  function U() {
    var e = this,
      t = e.wrapperEl,
      s = e.rtlTranslate,
      a = e.enabled;
    if (!a) return;
    var i;
    e.previousTranslate = e.translate, e.isHorizontal() ? e.translate = -t.scrollLeft : e.translate = -t.scrollTop, 0 === e.translate && (e.translate = 0), e.updateActiveIndex(), e.updateSlidesClasses();
    var r = e.maxTranslate() - e.minTranslate();
    i = 0 === r ? 0 : (e.translate - e.minTranslate()) / r, i !== e.progress && e.updateProgress(s ? -e.translate : e.translate), e.emit("setTranslate", e.translate, !1);
  }
  function K(e) {
    var t = this;
    D(t, e.target), t.params.cssMode || "auto" !== t.params.slidesPerView && !t.params.autoHeight || t.update();
  }
  function Z() {
    var e = this;
    e.documentTouchHandlerProceeded || (e.documentTouchHandlerProceeded = !0, e.params.touchReleaseOnEdges && (e.el.style.touchAction = "auto"));
  }
  var Q = function Q(e, t) {
    var s = a(),
      i = e.params,
      r = e.el,
      n = e.wrapperEl,
      l = e.device,
      o = !!i.nested,
      d = "on" === t ? "addEventListener" : "removeEventListener",
      c = t;
    r && "string" != typeof r && (s[d]("touchstart", e.onDocumentTouchStart, {
      passive: !1,
      capture: o
    }), r[d]("touchstart", e.onTouchStart, {
      passive: !1
    }), r[d]("pointerdown", e.onTouchStart, {
      passive: !1
    }), s[d]("touchmove", e.onTouchMove, {
      passive: !1,
      capture: o
    }), s[d]("pointermove", e.onTouchMove, {
      passive: !1,
      capture: o
    }), s[d]("touchend", e.onTouchEnd, {
      passive: !0
    }), s[d]("pointerup", e.onTouchEnd, {
      passive: !0
    }), s[d]("pointercancel", e.onTouchEnd, {
      passive: !0
    }), s[d]("touchcancel", e.onTouchEnd, {
      passive: !0
    }), s[d]("pointerout", e.onTouchEnd, {
      passive: !0
    }), s[d]("pointerleave", e.onTouchEnd, {
      passive: !0
    }), s[d]("contextmenu", e.onTouchEnd, {
      passive: !0
    }), (i.preventClicks || i.preventClicksPropagation) && r[d]("click", e.onClick, !0), i.cssMode && n[d]("scroll", e.onScroll), i.updateOnWindowResize ? e[c](l.ios || l.android ? "resize orientationchange observerUpdate" : "resize observerUpdate", W, !0) : e[c]("observerUpdate", W, !0), r[d]("load", e.onLoad, {
      capture: !0
    }));
  };
  var J = function J(e, t) {
    return e.grid && t.grid && t.grid.rows > 1;
  };
  var ee = {
    init: !0,
    direction: "horizontal",
    oneWayMovement: !1,
    swiperElementNodeName: "SWIPER-CONTAINER",
    touchEventsTarget: "wrapper",
    initialSlide: 0,
    speed: 300,
    cssMode: !1,
    updateOnWindowResize: !0,
    resizeObserver: !0,
    nested: !1,
    createElements: !1,
    eventsPrefix: "swiper",
    enabled: !0,
    focusableElements: "input, select, option, textarea, button, video, label",
    width: null,
    height: null,
    preventInteractionOnTransition: !1,
    userAgent: null,
    url: null,
    edgeSwipeDetection: !1,
    edgeSwipeThreshold: 20,
    autoHeight: !1,
    setWrapperSize: !1,
    virtualTranslate: !1,
    effect: "slide",
    breakpoints: void 0,
    breakpointsBase: "window",
    spaceBetween: 0,
    slidesPerView: 1,
    slidesPerGroup: 1,
    slidesPerGroupSkip: 0,
    slidesPerGroupAuto: !1,
    centeredSlides: !1,
    centeredSlidesBounds: !1,
    slidesOffsetBefore: 0,
    slidesOffsetAfter: 0,
    normalizeSlideIndex: !0,
    centerInsufficientSlides: !1,
    watchOverflow: !0,
    roundLengths: !1,
    touchRatio: 1,
    touchAngle: 45,
    simulateTouch: !0,
    shortSwipes: !0,
    longSwipes: !0,
    longSwipesRatio: .5,
    longSwipesMs: 300,
    followFinger: !0,
    allowTouchMove: !0,
    threshold: 5,
    touchMoveStopPropagation: !1,
    touchStartPreventDefault: !0,
    touchStartForcePreventDefault: !1,
    touchReleaseOnEdges: !1,
    uniqueNavElements: !0,
    resistance: !0,
    resistanceRatio: .85,
    watchSlidesProgress: !1,
    grabCursor: !1,
    preventClicks: !0,
    preventClicksPropagation: !0,
    slideToClickedSlide: !1,
    loop: !1,
    loopAddBlankSlides: !0,
    loopAdditionalSlides: 0,
    loopPreventsSliding: !0,
    rewind: !1,
    allowSlidePrev: !0,
    allowSlideNext: !0,
    swipeHandler: null,
    noSwiping: !0,
    noSwipingClass: "swiper-no-swiping",
    noSwipingSelector: null,
    passiveListeners: !0,
    maxBackfaceHiddenSlides: 10,
    containerModifierClass: "swiper-",
    slideClass: "swiper-slide",
    slideBlankClass: "swiper-slide-blank",
    slideActiveClass: "swiper-slide-active",
    slideVisibleClass: "swiper-slide-visible",
    slideFullyVisibleClass: "swiper-slide-fully-visible",
    slideNextClass: "swiper-slide-next",
    slidePrevClass: "swiper-slide-prev",
    wrapperClass: "swiper-wrapper",
    lazyPreloaderClass: "swiper-lazy-preloader",
    lazyPreloadPrevNext: 0,
    runCallbacksOnInit: !0,
    _emitClasses: !1
  };
  function te(e, t) {
    return function (s) {
      void 0 === s && (s = {});
      var a = Object.keys(s)[0],
        i = s[a];
      "object" == _typeof(i) && null !== i ? (!0 === e[a] && (e[a] = {
        enabled: !0
      }), "navigation" === a && e[a] && e[a].enabled && !e[a].prevEl && !e[a].nextEl && (e[a].auto = !0), ["pagination", "scrollbar"].indexOf(a) >= 0 && e[a] && e[a].enabled && !e[a].el && (e[a].auto = !0), a in e && "enabled" in i ? ("object" != _typeof(e[a]) || "enabled" in e[a] || (e[a].enabled = !0), e[a] || (e[a] = {
        enabled: !1
      }), p(t, s)) : p(t, s)) : p(t, s);
    };
  }
  var se = {
      eventsEmitter: $,
      update: X,
      translate: B,
      transition: {
        setTransition: function setTransition(e, t) {
          var s = this;
          s.params.cssMode || (s.wrapperEl.style.transitionDuration = "".concat(e, "ms"), s.wrapperEl.style.transitionDelay = 0 === e ? "0ms" : ""), s.emit("setTransition", e, t);
        },
        transitionStart: function transitionStart(e, t) {
          void 0 === e && (e = !0);
          var s = this,
            a = s.params;
          a.cssMode || (a.autoHeight && s.updateAutoHeight(), Y({
            swiper: s,
            runCallbacks: e,
            direction: t,
            step: "Start"
          }));
        },
        transitionEnd: function transitionEnd(e, t) {
          void 0 === e && (e = !0);
          var s = this,
            a = s.params;
          s.animating = !1, a.cssMode || (s.setTransition(0), Y({
            swiper: s,
            runCallbacks: e,
            direction: t,
            step: "End"
          }));
        }
      },
      slide: N,
      loop: R,
      grabCursor: {
        setGrabCursor: function setGrabCursor(e) {
          var t = this;
          if (!t.params.simulateTouch || t.params.watchOverflow && t.isLocked || t.params.cssMode) return;
          var s = "container" === t.params.touchEventsTarget ? t.el : t.wrapperEl;
          t.isElement && (t.__preventObserver__ = !0), s.style.cursor = "move", s.style.cursor = e ? "grabbing" : "grab", t.isElement && requestAnimationFrame(function () {
            t.__preventObserver__ = !1;
          });
        },
        unsetGrabCursor: function unsetGrabCursor() {
          var e = this;
          e.params.watchOverflow && e.isLocked || e.params.cssMode || (e.isElement && (e.__preventObserver__ = !0), e["container" === e.params.touchEventsTarget ? "el" : "wrapperEl"].style.cursor = "", e.isElement && requestAnimationFrame(function () {
            e.__preventObserver__ = !1;
          }));
        }
      },
      events: {
        attachEvents: function attachEvents() {
          var e = this,
            t = e.params;
          e.onTouchStart = _.bind(e), e.onTouchMove = F.bind(e), e.onTouchEnd = V.bind(e), e.onDocumentTouchStart = Z.bind(e), t.cssMode && (e.onScroll = U.bind(e)), e.onClick = j.bind(e), e.onLoad = K.bind(e), Q(e, "on");
        },
        detachEvents: function detachEvents() {
          Q(this, "off");
        }
      },
      breakpoints: {
        setBreakpoint: function setBreakpoint() {
          var e = this,
            t = e.realIndex,
            s = e.initialized,
            i = e.params,
            r = e.el,
            n = i.breakpoints;
          if (!n || n && 0 === Object.keys(n).length) return;
          var l = a(),
            o = "window" !== i.breakpointsBase && i.breakpointsBase ? "container" : i.breakpointsBase,
            d = ["window", "container"].includes(i.breakpointsBase) || !i.breakpointsBase ? e.el : l.querySelector(i.breakpointsBase),
            c = e.getBreakpoint(n, o, d);
          if (!c || e.currentBreakpoint === c) return;
          var u = (c in n ? n[c] : void 0) || e.originalParams,
            m = J(e, i),
            h = J(e, u),
            f = e.params.grabCursor,
            g = u.grabCursor,
            v = i.enabled;
          m && !h ? (r.classList.remove("".concat(i.containerModifierClass, "grid"), "".concat(i.containerModifierClass, "grid-column")), e.emitContainerClasses()) : !m && h && (r.classList.add("".concat(i.containerModifierClass, "grid")), (u.grid.fill && "column" === u.grid.fill || !u.grid.fill && "column" === i.grid.fill) && r.classList.add("".concat(i.containerModifierClass, "grid-column")), e.emitContainerClasses()), f && !g ? e.unsetGrabCursor() : !f && g && e.setGrabCursor(), ["navigation", "pagination", "scrollbar"].forEach(function (t) {
            if (void 0 === u[t]) return;
            var s = i[t] && i[t].enabled,
              a = u[t] && u[t].enabled;
            s && !a && e[t].disable(), !s && a && e[t].enable();
          });
          var w = u.direction && u.direction !== i.direction,
            b = i.loop && (u.slidesPerView !== i.slidesPerView || w),
            y = i.loop;
          w && s && e.changeDirection(), p(e.params, u);
          var E = e.params.enabled,
            x = e.params.loop;
          Object.assign(e, {
            allowTouchMove: e.params.allowTouchMove,
            allowSlideNext: e.params.allowSlideNext,
            allowSlidePrev: e.params.allowSlidePrev
          }), v && !E ? e.disable() : !v && E && e.enable(), e.currentBreakpoint = c, e.emit("_beforeBreakpoint", u), s && (b ? (e.loopDestroy(), e.loopCreate(t), e.updateSlides()) : !y && x ? (e.loopCreate(t), e.updateSlides()) : y && !x && e.loopDestroy()), e.emit("breakpoint", u);
        },
        getBreakpoint: function getBreakpoint(e, t, s) {
          if (void 0 === t && (t = "window"), !e || "container" === t && !s) return;
          var a = !1;
          var i = r(),
            n = "window" === t ? i.innerHeight : s.clientHeight,
            l = Object.keys(e).map(function (e) {
              if ("string" == typeof e && 0 === e.indexOf("@")) {
                var _t23 = parseFloat(e.substr(1));
                return {
                  value: n * _t23,
                  point: e
                };
              }
              return {
                value: e,
                point: e
              };
            });
          l.sort(function (e, t) {
            return parseInt(e.value, 10) - parseInt(t.value, 10);
          });
          for (var _e35 = 0; _e35 < l.length; _e35 += 1) {
            var _l$_e = l[_e35],
              _r6 = _l$_e.point,
              _n4 = _l$_e.value;
            "window" === t ? i.matchMedia("(min-width: ".concat(_n4, "px)")).matches && (a = _r6) : _n4 <= s.clientWidth && (a = _r6);
          }
          return a || "max";
        }
      },
      checkOverflow: {
        checkOverflow: function checkOverflow() {
          var e = this,
            t = e.isLocked,
            s = e.params,
            a = s.slidesOffsetBefore;
          if (a) {
            var _t24 = e.slides.length - 1,
              _s13 = e.slidesGrid[_t24] + e.slidesSizesGrid[_t24] + 2 * a;
            e.isLocked = e.size > _s13;
          } else e.isLocked = 1 === e.snapGrid.length;
          !0 === s.allowSlideNext && (e.allowSlideNext = !e.isLocked), !0 === s.allowSlidePrev && (e.allowSlidePrev = !e.isLocked), t && t !== e.isLocked && (e.isEnd = !1), t !== e.isLocked && e.emit(e.isLocked ? "lock" : "unlock");
        }
      },
      classes: {
        addClasses: function addClasses() {
          var _i$classList;
          var e = this,
            t = e.classNames,
            s = e.params,
            a = e.rtl,
            i = e.el,
            r = e.device,
            n = function (e, t) {
              var s = [];
              return e.forEach(function (e) {
                "object" == _typeof(e) ? Object.keys(e).forEach(function (a) {
                  e[a] && s.push(t + a);
                }) : "string" == typeof e && s.push(t + e);
              }), s;
            }(["initialized", s.direction, {
              "free-mode": e.params.freeMode && s.freeMode.enabled
            }, {
              autoheight: s.autoHeight
            }, {
              rtl: a
            }, {
              grid: s.grid && s.grid.rows > 1
            }, {
              "grid-column": s.grid && s.grid.rows > 1 && "column" === s.grid.fill
            }, {
              android: r.android
            }, {
              ios: r.ios
            }, {
              "css-mode": s.cssMode
            }, {
              centered: s.cssMode && s.centeredSlides
            }, {
              "watch-progress": s.watchSlidesProgress
            }], s.containerModifierClass);
          t.push.apply(t, _toConsumableArray(n)), (_i$classList = i.classList).add.apply(_i$classList, _toConsumableArray(t)), e.emitContainerClasses();
        },
        removeClasses: function removeClasses() {
          var _e$classList;
          var e = this.el,
            t = this.classNames;
          e && "string" != typeof e && ((_e$classList = e.classList).remove.apply(_e$classList, _toConsumableArray(t)), this.emitContainerClasses());
        }
      }
    },
    ae = {};
  var ie = /*#__PURE__*/function () {
    function ie() {
      var _i8, _i9, _l$modules;
      _classCallCheck(this, ie);
      var e, t;
      for (var s = arguments.length, i = new Array(s), r = 0; r < s; r++) i[r] = arguments[r];
      1 === i.length && i[0].constructor && "Object" === Object.prototype.toString.call(i[0]).slice(8, -1) ? t = i[0] : (_i8 = i, _i9 = _slicedToArray(_i8, 2), e = _i9[0], t = _i9[1], _i8), t || (t = {}), t = p({}, t), e && !t.el && (t.el = e);
      var n = a();
      if (t.el && "string" == typeof t.el && n.querySelectorAll(t.el).length > 1) {
        var _e36 = [];
        return n.querySelectorAll(t.el).forEach(function (s) {
          var a = p({}, t, {
            el: s
          });
          _e36.push(new ie(a));
        }), _e36;
      }
      var l = this;
      l.__swiper__ = !0, l.support = I(), l.device = z({
        userAgent: t.userAgent
      }), l.browser = A(), l.eventsListeners = {}, l.eventsAnyListeners = [], l.modules = _toConsumableArray(l.__modules__), t.modules && Array.isArray(t.modules) && (_l$modules = l.modules).push.apply(_l$modules, _toConsumableArray(t.modules));
      var o = {};
      l.modules.forEach(function (e) {
        e({
          params: t,
          swiper: l,
          extendParams: te(t, o),
          on: l.on.bind(l),
          once: l.once.bind(l),
          off: l.off.bind(l),
          emit: l.emit.bind(l)
        });
      });
      var d = p({}, ee, o);
      return l.params = p({}, d, ae, t), l.originalParams = p({}, l.params), l.passedParams = p({}, t), l.params && l.params.on && Object.keys(l.params.on).forEach(function (e) {
        l.on(e, l.params.on[e]);
      }), l.params && l.params.onAny && l.onAny(l.params.onAny), Object.assign(l, {
        enabled: l.params.enabled,
        el: e,
        classNames: [],
        slides: [],
        slidesGrid: [],
        snapGrid: [],
        slidesSizesGrid: [],
        isHorizontal: function isHorizontal() {
          return "horizontal" === l.params.direction;
        },
        isVertical: function isVertical() {
          return "vertical" === l.params.direction;
        },
        activeIndex: 0,
        realIndex: 0,
        isBeginning: !0,
        isEnd: !1,
        translate: 0,
        previousTranslate: 0,
        progress: 0,
        velocity: 0,
        animating: !1,
        cssOverflowAdjustment: function cssOverflowAdjustment() {
          return Math.trunc(this.translate / Math.pow(2, 23)) * Math.pow(2, 23);
        },
        allowSlideNext: l.params.allowSlideNext,
        allowSlidePrev: l.params.allowSlidePrev,
        touchEventsData: {
          isTouched: void 0,
          isMoved: void 0,
          allowTouchCallbacks: void 0,
          touchStartTime: void 0,
          isScrolling: void 0,
          currentTranslate: void 0,
          startTranslate: void 0,
          allowThresholdMove: void 0,
          focusableElements: l.params.focusableElements,
          lastClickTime: 0,
          clickTimeout: void 0,
          velocities: [],
          allowMomentumBounce: void 0,
          startMoving: void 0,
          pointerId: null,
          touchId: null
        },
        allowClick: !0,
        allowTouchMove: l.params.allowTouchMove,
        touches: {
          startX: 0,
          startY: 0,
          currentX: 0,
          currentY: 0,
          diff: 0
        },
        imagesToLoad: [],
        imagesLoaded: 0
      }), l.emit("_swiper"), l.params.init && l.init(), l;
    }
    return _createClass(ie, [{
      key: "getDirectionLabel",
      value: function getDirectionLabel(e) {
        return this.isHorizontal() ? e : {
          width: "height",
          "margin-top": "margin-left",
          "margin-bottom ": "margin-right",
          "margin-left": "margin-top",
          "margin-right": "margin-bottom",
          "padding-left": "padding-top",
          "padding-right": "padding-bottom",
          marginRight: "marginBottom"
        }[e];
      }
    }, {
      key: "getSlideIndex",
      value: function getSlideIndex(e) {
        var t = this.slidesEl,
          s = this.params,
          a = y(f(t, ".".concat(s.slideClass, ", swiper-slide"))[0]);
        return y(e) - a;
      }
    }, {
      key: "getSlideIndexByData",
      value: function getSlideIndexByData(e) {
        return this.getSlideIndex(this.slides.find(function (t) {
          return 1 * t.getAttribute("data-swiper-slide-index") === e;
        }));
      }
    }, {
      key: "recalcSlides",
      value: function recalcSlides() {
        var e = this.slidesEl,
          t = this.params;
        this.slides = f(e, ".".concat(t.slideClass, ", swiper-slide"));
      }
    }, {
      key: "enable",
      value: function enable() {
        var e = this;
        e.enabled || (e.enabled = !0, e.params.grabCursor && e.setGrabCursor(), e.emit("enable"));
      }
    }, {
      key: "disable",
      value: function disable() {
        var e = this;
        e.enabled && (e.enabled = !1, e.params.grabCursor && e.unsetGrabCursor(), e.emit("disable"));
      }
    }, {
      key: "setProgress",
      value: function setProgress(e, t) {
        var s = this;
        e = Math.min(Math.max(e, 0), 1);
        var a = s.minTranslate(),
          i = (s.maxTranslate() - a) * e + a;
        s.translateTo(i, void 0 === t ? 0 : t), s.updateActiveIndex(), s.updateSlidesClasses();
      }
    }, {
      key: "emitContainerClasses",
      value: function emitContainerClasses() {
        var e = this;
        if (!e.params._emitClasses || !e.el) return;
        var t = e.el.className.split(" ").filter(function (t) {
          return 0 === t.indexOf("swiper") || 0 === t.indexOf(e.params.containerModifierClass);
        });
        e.emit("_containerClasses", t.join(" "));
      }
    }, {
      key: "getSlideClasses",
      value: function getSlideClasses(e) {
        var t = this;
        return t.destroyed ? "" : e.className.split(" ").filter(function (e) {
          return 0 === e.indexOf("swiper-slide") || 0 === e.indexOf(t.params.slideClass);
        }).join(" ");
      }
    }, {
      key: "emitSlidesClasses",
      value: function emitSlidesClasses() {
        var e = this;
        if (!e.params._emitClasses || !e.el) return;
        var t = [];
        e.slides.forEach(function (s) {
          var a = e.getSlideClasses(s);
          t.push({
            slideEl: s,
            classNames: a
          }), e.emit("_slideClass", s, a);
        }), e.emit("_slideClasses", t);
      }
    }, {
      key: "slidesPerViewDynamic",
      value: function slidesPerViewDynamic(e, t) {
        void 0 === e && (e = "current"), void 0 === t && (t = !1);
        var s = this.params,
          a = this.slides,
          i = this.slidesGrid,
          r = this.slidesSizesGrid,
          n = this.size,
          l = this.activeIndex;
        var o = 1;
        if ("number" == typeof s.slidesPerView) return s.slidesPerView;
        if (s.centeredSlides) {
          var _e37,
            _t25 = a[l] ? Math.ceil(a[l].swiperSlideSize) : 0;
          for (var _s14 = l + 1; _s14 < a.length; _s14 += 1) a[_s14] && !_e37 && (_t25 += Math.ceil(a[_s14].swiperSlideSize), o += 1, _t25 > n && (_e37 = !0));
          for (var _s15 = l - 1; _s15 >= 0; _s15 -= 1) a[_s15] && !_e37 && (_t25 += a[_s15].swiperSlideSize, o += 1, _t25 > n && (_e37 = !0));
        } else if ("current" === e) for (var _e38 = l + 1; _e38 < a.length; _e38 += 1) {
          (t ? i[_e38] + r[_e38] - i[l] < n : i[_e38] - i[l] < n) && (o += 1);
        } else for (var _e39 = l - 1; _e39 >= 0; _e39 -= 1) {
          i[l] - i[_e39] < n && (o += 1);
        }
        return o;
      }
    }, {
      key: "update",
      value: function update() {
        var e = this;
        if (!e || e.destroyed) return;
        var t = e.snapGrid,
          s = e.params;
        function a() {
          var t = e.rtlTranslate ? -1 * e.translate : e.translate,
            s = Math.min(Math.max(t, e.maxTranslate()), e.minTranslate());
          e.setTranslate(s), e.updateActiveIndex(), e.updateSlidesClasses();
        }
        var i;
        if (s.breakpoints && e.setBreakpoint(), _toConsumableArray(e.el.querySelectorAll('[loading="lazy"]')).forEach(function (t) {
          t.complete && D(e, t);
        }), e.updateSize(), e.updateSlides(), e.updateProgress(), e.updateSlidesClasses(), s.freeMode && s.freeMode.enabled && !s.cssMode) a(), s.autoHeight && e.updateAutoHeight();else {
          if (("auto" === s.slidesPerView || s.slidesPerView > 1) && e.isEnd && !s.centeredSlides) {
            var _t26 = e.virtual && s.virtual.enabled ? e.virtual.slides : e.slides;
            i = e.slideTo(_t26.length - 1, 0, !1, !0);
          } else i = e.slideTo(e.activeIndex, 0, !1, !0);
          i || a();
        }
        s.watchOverflow && t !== e.snapGrid && e.checkOverflow(), e.emit("update");
      }
    }, {
      key: "changeDirection",
      value: function changeDirection(e, t) {
        void 0 === t && (t = !0);
        var s = this,
          a = s.params.direction;
        return e || (e = "horizontal" === a ? "vertical" : "horizontal"), e === a || "horizontal" !== e && "vertical" !== e || (s.el.classList.remove("".concat(s.params.containerModifierClass).concat(a)), s.el.classList.add("".concat(s.params.containerModifierClass).concat(e)), s.emitContainerClasses(), s.params.direction = e, s.slides.forEach(function (t) {
          "vertical" === e ? t.style.width = "" : t.style.height = "";
        }), s.emit("changeDirection"), t && s.update()), s;
      }
    }, {
      key: "changeLanguageDirection",
      value: function changeLanguageDirection(e) {
        var t = this;
        t.rtl && "rtl" === e || !t.rtl && "ltr" === e || (t.rtl = "rtl" === e, t.rtlTranslate = "horizontal" === t.params.direction && t.rtl, t.rtl ? (t.el.classList.add("".concat(t.params.containerModifierClass, "rtl")), t.el.dir = "rtl") : (t.el.classList.remove("".concat(t.params.containerModifierClass, "rtl")), t.el.dir = "ltr"), t.update());
      }
    }, {
      key: "mount",
      value: function mount(e) {
        var t = this;
        if (t.mounted) return !0;
        var s = e || t.params.el;
        if ("string" == typeof s && (s = document.querySelector(s)), !s) return !1;
        s.swiper = t, s.parentNode && s.parentNode.host && s.parentNode.host.nodeName === t.params.swiperElementNodeName.toUpperCase() && (t.isElement = !0);
        var a = function a() {
          return ".".concat((t.params.wrapperClass || "").trim().split(" ").join("."));
        };
        var i = function () {
          if (s && s.shadowRoot && s.shadowRoot.querySelector) {
            return s.shadowRoot.querySelector(a());
          }
          return f(s, a())[0];
        }();
        return !i && t.params.createElements && (i = v("div", t.params.wrapperClass), s.append(i), f(s, ".".concat(t.params.slideClass)).forEach(function (e) {
          i.append(e);
        })), Object.assign(t, {
          el: s,
          wrapperEl: i,
          slidesEl: t.isElement && !s.parentNode.host.slideSlots ? s.parentNode.host : i,
          hostEl: t.isElement ? s.parentNode.host : s,
          mounted: !0,
          rtl: "rtl" === s.dir.toLowerCase() || "rtl" === b(s, "direction"),
          rtlTranslate: "horizontal" === t.params.direction && ("rtl" === s.dir.toLowerCase() || "rtl" === b(s, "direction")),
          wrongRTL: "-webkit-box" === b(i, "display")
        }), !0;
      }
    }, {
      key: "init",
      value: function init(e) {
        var t = this;
        if (t.initialized) return t;
        if (!1 === t.mount(e)) return t;
        t.emit("beforeInit"), t.params.breakpoints && t.setBreakpoint(), t.addClasses(), t.updateSize(), t.updateSlides(), t.params.watchOverflow && t.checkOverflow(), t.params.grabCursor && t.enabled && t.setGrabCursor(), t.params.loop && t.virtual && t.params.virtual.enabled ? t.slideTo(t.params.initialSlide + t.virtual.slidesBefore, 0, t.params.runCallbacksOnInit, !1, !0) : t.slideTo(t.params.initialSlide, 0, t.params.runCallbacksOnInit, !1, !0), t.params.loop && t.loopCreate(), t.attachEvents();
        var s = _toConsumableArray(t.el.querySelectorAll('[loading="lazy"]'));
        return t.isElement && s.push.apply(s, _toConsumableArray(t.hostEl.querySelectorAll('[loading="lazy"]'))), s.forEach(function (e) {
          e.complete ? D(t, e) : e.addEventListener("load", function (e) {
            D(t, e.target);
          });
        }), H(t), t.initialized = !0, H(t), t.emit("init"), t.emit("afterInit"), t;
      }
    }, {
      key: "destroy",
      value: function destroy(e, t) {
        void 0 === e && (e = !0), void 0 === t && (t = !0);
        var s = this,
          a = s.params,
          i = s.el,
          r = s.wrapperEl,
          n = s.slides;
        return void 0 === s.params || s.destroyed || (s.emit("beforeDestroy"), s.initialized = !1, s.detachEvents(), a.loop && s.loopDestroy(), t && (s.removeClasses(), i && "string" != typeof i && i.removeAttribute("style"), r && r.removeAttribute("style"), n && n.length && n.forEach(function (e) {
          e.classList.remove(a.slideVisibleClass, a.slideFullyVisibleClass, a.slideActiveClass, a.slideNextClass, a.slidePrevClass), e.removeAttribute("style"), e.removeAttribute("data-swiper-slide-index");
        })), s.emit("destroy"), Object.keys(s.eventsListeners).forEach(function (e) {
          s.off(e);
        }), !1 !== e && (s.el && "string" != typeof s.el && (s.el.swiper = null), function (e) {
          var t = e;
          Object.keys(t).forEach(function (e) {
            try {
              t[e] = null;
            } catch (e) {}
            try {
              delete t[e];
            } catch (e) {}
          });
        }(s)), s.destroyed = !0), null;
      }
    }], [{
      key: "extendDefaults",
      value: function extendDefaults(e) {
        p(ae, e);
      }
    }, {
      key: "extendedDefaults",
      get: function get() {
        return ae;
      }
    }, {
      key: "defaults",
      get: function get() {
        return ee;
      }
    }, {
      key: "installModule",
      value: function installModule(e) {
        ie.prototype.__modules__ || (ie.prototype.__modules__ = []);
        var t = ie.prototype.__modules__;
        "function" == typeof e && t.indexOf(e) < 0 && t.push(e);
      }
    }, {
      key: "use",
      value: function use(e) {
        return Array.isArray(e) ? (e.forEach(function (e) {
          return ie.installModule(e);
        }), ie) : (ie.installModule(e), ie);
      }
    }]);
  }();
  function re(e, t, s, a) {
    return e.params.createElements && Object.keys(a).forEach(function (i) {
      if (!s[i] && !0 === s.auto) {
        var _r7 = f(e.el, ".".concat(a[i]))[0];
        _r7 || (_r7 = v("div", a[i]), _r7.className = a[i], e.el.append(_r7)), s[i] = _r7, t[i] = _r7;
      }
    }), s;
  }
  function ne(e) {
    return void 0 === e && (e = ""), ".".concat(e.trim().replace(/([\.:!+\/])/g, "\\$1").replace(/ /g, "."));
  }
  function le(e) {
    var t = this,
      s = t.params,
      a = t.slidesEl;
    s.loop && t.loopDestroy();
    var i = function i(e) {
      if ("string" == typeof e) {
        var _t27 = document.createElement("div");
        _t27.innerHTML = e, a.append(_t27.children[0]), _t27.innerHTML = "";
      } else a.append(e);
    };
    if ("object" == _typeof(e) && "length" in e) for (var _t28 = 0; _t28 < e.length; _t28 += 1) e[_t28] && i(e[_t28]);else i(e);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update();
  }
  function oe(e) {
    var t = this,
      s = t.params,
      a = t.activeIndex,
      i = t.slidesEl;
    s.loop && t.loopDestroy();
    var r = a + 1;
    var n = function n(e) {
      if ("string" == typeof e) {
        var _t29 = document.createElement("div");
        _t29.innerHTML = e, i.prepend(_t29.children[0]), _t29.innerHTML = "";
      } else i.prepend(e);
    };
    if ("object" == _typeof(e) && "length" in e) {
      for (var _t30 = 0; _t30 < e.length; _t30 += 1) e[_t30] && n(e[_t30]);
      r = a + e.length;
    } else n(e);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update(), t.slideTo(r, 0, !1);
  }
  function de(e, t) {
    var s = this,
      a = s.params,
      i = s.activeIndex,
      r = s.slidesEl;
    var n = i;
    a.loop && (n -= s.loopedSlides, s.loopDestroy(), s.recalcSlides());
    var l = s.slides.length;
    if (e <= 0) return void s.prependSlide(t);
    if (e >= l) return void s.appendSlide(t);
    var o = n > e ? n + 1 : n;
    var d = [];
    for (var _t31 = l - 1; _t31 >= e; _t31 -= 1) {
      var _e40 = s.slides[_t31];
      _e40.remove(), d.unshift(_e40);
    }
    if ("object" == _typeof(t) && "length" in t) {
      for (var _e41 = 0; _e41 < t.length; _e41 += 1) t[_e41] && r.append(t[_e41]);
      o = n > e ? n + t.length : n;
    } else r.append(t);
    for (var _e42 = 0; _e42 < d.length; _e42 += 1) r.append(d[_e42]);
    s.recalcSlides(), a.loop && s.loopCreate(), a.observer && !s.isElement || s.update(), a.loop ? s.slideTo(o + s.loopedSlides, 0, !1) : s.slideTo(o, 0, !1);
  }
  function ce(e) {
    var t = this,
      s = t.params,
      a = t.activeIndex;
    var i = a;
    s.loop && (i -= t.loopedSlides, t.loopDestroy());
    var r,
      n = i;
    if ("object" == _typeof(e) && "length" in e) {
      for (var _s16 = 0; _s16 < e.length; _s16 += 1) r = e[_s16], t.slides[r] && t.slides[r].remove(), r < n && (n -= 1);
      n = Math.max(n, 0);
    } else r = e, t.slides[r] && t.slides[r].remove(), r < n && (n -= 1), n = Math.max(n, 0);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update(), s.loop ? t.slideTo(n + t.loopedSlides, 0, !1) : t.slideTo(n, 0, !1);
  }
  function pe() {
    var e = this,
      t = [];
    for (var _s17 = 0; _s17 < e.slides.length; _s17 += 1) t.push(_s17);
    e.removeSlide(t);
  }
  function ue(e) {
    var t = e.effect,
      s = e.swiper,
      a = e.on,
      i = e.setTranslate,
      r = e.setTransition,
      n = e.overwriteParams,
      l = e.perspective,
      o = e.recreateShadows,
      d = e.getEffectParams;
    var c;
    a("beforeInit", function () {
      if (s.params.effect !== t) return;
      s.classNames.push("".concat(s.params.containerModifierClass).concat(t)), l && l() && s.classNames.push("".concat(s.params.containerModifierClass, "3d"));
      var e = n ? n() : {};
      Object.assign(s.params, e), Object.assign(s.originalParams, e);
    }), a("setTranslate", function () {
      s.params.effect === t && i();
    }), a("setTransition", function (e, a) {
      s.params.effect === t && r(a);
    }), a("transitionEnd", function () {
      if (s.params.effect === t && o) {
        if (!d || !d().slideShadows) return;
        s.slides.forEach(function (e) {
          e.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach(function (e) {
            return e.remove();
          });
        }), o();
      }
    }), a("virtualUpdate", function () {
      s.params.effect === t && (s.slides.length || (c = !0), requestAnimationFrame(function () {
        c && s.slides && s.slides.length && (i(), c = !1);
      }));
    });
  }
  function me(e, t) {
    var s = h(t);
    return s !== t && (s.style.backfaceVisibility = "hidden", s.style["-webkit-backface-visibility"] = "hidden"), s;
  }
  function he(e) {
    var t = e.swiper,
      s = e.duration,
      a = e.transformElements,
      i = e.allSlides;
    var r = t.activeIndex;
    if (t.params.virtualTranslate && 0 !== s) {
      var _e43,
        _s18 = !1;
      _e43 = i ? a : a.filter(function (e) {
        var s = e.classList.contains("swiper-slide-transform") ? function (e) {
          if (!e.parentElement) return t.slides.find(function (t) {
            return t.shadowRoot && t.shadowRoot === e.parentNode;
          });
          return e.parentElement;
        }(e) : e;
        return t.getSlideIndex(s) === r;
      }), _e43.forEach(function (e) {
        x(e, function () {
          if (_s18) return;
          if (!t || t.destroyed) return;
          _s18 = !0, t.animating = !1;
          var e = new window.CustomEvent("transitionend", {
            bubbles: !0,
            cancelable: !0
          });
          t.wrapperEl.dispatchEvent(e);
        });
      });
    }
  }
  function fe(e, t, s) {
    var a = "swiper-slide-shadow".concat(s ? "-".concat(s) : "").concat(e ? " swiper-slide-shadow-".concat(e) : ""),
      i = h(t);
    var r = i.querySelector(".".concat(a.split(" ").join(".")));
    return r || (r = v("div", a.split(" ")), i.append(r)), r;
  }
  Object.keys(se).forEach(function (e) {
    Object.keys(se[e]).forEach(function (t) {
      ie.prototype[t] = se[e][t];
    });
  }), ie.use([function (e) {
    var t = e.swiper,
      s = e.on,
      a = e.emit;
    var i = r();
    var n = null,
      l = null;
    var o = function o() {
        t && !t.destroyed && t.initialized && (a("beforeResize"), a("resize"));
      },
      d = function d() {
        t && !t.destroyed && t.initialized && a("orientationchange");
      };
    s("init", function () {
      t.params.resizeObserver && void 0 !== i.ResizeObserver ? t && !t.destroyed && t.initialized && (n = new ResizeObserver(function (e) {
        l = i.requestAnimationFrame(function () {
          var s = t.width,
            a = t.height;
          var i = s,
            r = a;
          e.forEach(function (e) {
            var s = e.contentBoxSize,
              a = e.contentRect,
              n = e.target;
            n && n !== t.el || (i = a ? a.width : (s[0] || s).inlineSize, r = a ? a.height : (s[0] || s).blockSize);
          }), i === s && r === a || o();
        });
      }), n.observe(t.el)) : (i.addEventListener("resize", o), i.addEventListener("orientationchange", d));
    }), s("destroy", function () {
      l && i.cancelAnimationFrame(l), n && n.unobserve && t.el && (n.unobserve(t.el), n = null), i.removeEventListener("resize", o), i.removeEventListener("orientationchange", d);
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on,
      i = e.emit;
    var n = [],
      l = r(),
      o = function o(e, s) {
        void 0 === s && (s = {});
        var a = new (l.MutationObserver || l.WebkitMutationObserver)(function (e) {
          if (t.__preventObserver__) return;
          if (1 === e.length) return void i("observerUpdate", e[0]);
          var s = function s() {
            i("observerUpdate", e[0]);
          };
          l.requestAnimationFrame ? l.requestAnimationFrame(s) : l.setTimeout(s, 0);
        });
        a.observe(e, {
          attributes: void 0 === s.attributes || s.attributes,
          childList: t.isElement || (void 0 === s.childList || s).childList,
          characterData: void 0 === s.characterData || s.characterData
        }), n.push(a);
      };
    s({
      observer: !1,
      observeParents: !1,
      observeSlideChildren: !1
    }), a("init", function () {
      if (t.params.observer) {
        if (t.params.observeParents) {
          var _e44 = E(t.hostEl);
          for (var _t32 = 0; _t32 < _e44.length; _t32 += 1) o(_e44[_t32]);
        }
        o(t.hostEl, {
          childList: t.params.observeSlideChildren
        }), o(t.wrapperEl, {
          attributes: !1
        });
      }
    }), a("destroy", function () {
      n.forEach(function (e) {
        e.disconnect();
      }), n.splice(0, n.length);
    });
  }]);
  var ge = [function (e) {
    var t,
      s = e.swiper,
      i = e.extendParams,
      r = e.on,
      n = e.emit;
    i({
      virtual: {
        enabled: !1,
        slides: [],
        cache: !0,
        renderSlide: null,
        renderExternal: null,
        renderExternalUpdate: !0,
        addSlidesBefore: 0,
        addSlidesAfter: 0
      }
    });
    var l = a();
    s.virtual = {
      cache: {},
      from: void 0,
      to: void 0,
      slides: [],
      offset: 0,
      slidesGrid: []
    };
    var o = l.createElement("div");
    function d(e, t) {
      var a = s.params.virtual;
      if (a.cache && s.virtual.cache[t]) return s.virtual.cache[t];
      var i;
      return a.renderSlide ? (i = a.renderSlide.call(s, e, t), "string" == typeof i && (o.innerHTML = i, i = o.children[0])) : i = s.isElement ? v("swiper-slide") : v("div", s.params.slideClass), i.setAttribute("data-swiper-slide-index", t), a.renderSlide || (i.innerHTML = e), a.cache && (s.virtual.cache[t] = i), i;
    }
    function c(e, t, a) {
      var _s$params = s.params,
        i = _s$params.slidesPerView,
        r = _s$params.slidesPerGroup,
        l = _s$params.centeredSlides,
        o = _s$params.loop,
        c = _s$params.initialSlide;
      if (t && !o && c > 0) return;
      var _s$params$virtual = s.params.virtual,
        p = _s$params$virtual.addSlidesBefore,
        u = _s$params$virtual.addSlidesAfter,
        _s$virtual = s.virtual,
        m = _s$virtual.from,
        h = _s$virtual.to,
        g = _s$virtual.slides,
        v = _s$virtual.slidesGrid,
        w = _s$virtual.offset;
      s.params.cssMode || s.updateActiveIndex();
      var b = void 0 === a ? s.activeIndex || 0 : a;
      var y, E, x;
      y = s.rtlTranslate ? "right" : s.isHorizontal() ? "left" : "top", l ? (E = Math.floor(i / 2) + r + u, x = Math.floor(i / 2) + r + p) : (E = i + (r - 1) + u, x = (o ? i : r) + p);
      var S = b - x,
        T = b + E;
      o || (S = Math.max(S, 0), T = Math.min(T, g.length - 1));
      var M = (s.slidesGrid[S] || 0) - (s.slidesGrid[0] || 0);
      function C() {
        s.updateSlides(), s.updateProgress(), s.updateSlidesClasses(), n("virtualUpdate");
      }
      if (o && b >= x ? (S -= x, l || (M += s.slidesGrid[0])) : o && b < x && (S = -x, l && (M += s.slidesGrid[0])), Object.assign(s.virtual, {
        from: S,
        to: T,
        offset: M,
        slidesGrid: s.slidesGrid,
        slidesBefore: x,
        slidesAfter: E
      }), m === S && h === T && !e) return s.slidesGrid !== v && M !== w && s.slides.forEach(function (e) {
        e.style[y] = M - Math.abs(s.cssOverflowAdjustment()) + "px";
      }), s.updateProgress(), void n("virtualUpdate");
      if (s.params.virtual.renderExternal) return s.params.virtual.renderExternal.call(s, {
        offset: M,
        from: S,
        to: T,
        slides: function () {
          var e = [];
          for (var _t33 = S; _t33 <= T; _t33 += 1) e.push(g[_t33]);
          return e;
        }()
      }), void (s.params.virtual.renderExternalUpdate ? C() : n("virtualUpdate"));
      var P = [],
        L = [],
        I = function I(e) {
          var t = e;
          return e < 0 ? t = g.length + e : t >= g.length && (t -= g.length), t;
        };
      if (e) s.slides.filter(function (e) {
        return e.matches(".".concat(s.params.slideClass, ", swiper-slide"));
      }).forEach(function (e) {
        e.remove();
      });else {
        var _loop2 = function _loop2() {
          if (_e45 < S || _e45 > T) {
            var _t34 = I(_e45);
            s.slides.filter(function (e) {
              return e.matches(".".concat(s.params.slideClass, "[data-swiper-slide-index=\"").concat(_t34, "\"], swiper-slide[data-swiper-slide-index=\"").concat(_t34, "\"]"));
            }).forEach(function (e) {
              e.remove();
            });
          }
        };
        for (var _e45 = m; _e45 <= h; _e45 += 1) {
          _loop2();
        }
      }
      var z = o ? -g.length : 0,
        A = o ? 2 * g.length : g.length;
      for (var _t35 = z; _t35 < A; _t35 += 1) if (_t35 >= S && _t35 <= T) {
        var _s19 = I(_t35);
        void 0 === h || e ? L.push(_s19) : (_t35 > h && L.push(_s19), _t35 < m && P.push(_s19));
      }
      if (L.forEach(function (e) {
        s.slidesEl.append(d(g[e], e));
      }), o) for (var _e46 = P.length - 1; _e46 >= 0; _e46 -= 1) {
        var _t36 = P[_e46];
        s.slidesEl.prepend(d(g[_t36], _t36));
      } else P.sort(function (e, t) {
        return t - e;
      }), P.forEach(function (e) {
        s.slidesEl.prepend(d(g[e], e));
      });
      f(s.slidesEl, ".swiper-slide, swiper-slide").forEach(function (e) {
        e.style[y] = M - Math.abs(s.cssOverflowAdjustment()) + "px";
      }), C();
    }
    r("beforeInit", function () {
      if (!s.params.virtual.enabled) return;
      var e;
      if (void 0 === s.passedParams.virtual.slides) {
        var _t37 = _toConsumableArray(s.slidesEl.children).filter(function (e) {
          return e.matches(".".concat(s.params.slideClass, ", swiper-slide"));
        });
        _t37 && _t37.length && (s.virtual.slides = _toConsumableArray(_t37), e = !0, _t37.forEach(function (e, t) {
          e.setAttribute("data-swiper-slide-index", t), s.virtual.cache[t] = e, e.remove();
        }));
      }
      e || (s.virtual.slides = s.params.virtual.slides), s.classNames.push("".concat(s.params.containerModifierClass, "virtual")), s.params.watchSlidesProgress = !0, s.originalParams.watchSlidesProgress = !0, c(!1, !0);
    }), r("setTranslate", function () {
      s.params.virtual.enabled && (s.params.cssMode && !s._immediateVirtual ? (clearTimeout(t), t = setTimeout(function () {
        c();
      }, 100)) : c());
    }), r("init update resize", function () {
      s.params.virtual.enabled && s.params.cssMode && u(s.wrapperEl, "--swiper-virtual-size", "".concat(s.virtualSize, "px"));
    }), Object.assign(s.virtual, {
      appendSlide: function appendSlide(e) {
        if ("object" == _typeof(e) && "length" in e) for (var _t38 = 0; _t38 < e.length; _t38 += 1) e[_t38] && s.virtual.slides.push(e[_t38]);else s.virtual.slides.push(e);
        c(!0);
      },
      prependSlide: function prependSlide(e) {
        var t = s.activeIndex;
        var a = t + 1,
          i = 1;
        if (Array.isArray(e)) {
          for (var _t39 = 0; _t39 < e.length; _t39 += 1) e[_t39] && s.virtual.slides.unshift(e[_t39]);
          a = t + e.length, i = e.length;
        } else s.virtual.slides.unshift(e);
        if (s.params.virtual.cache) {
          var _e47 = s.virtual.cache,
            _t40 = {};
          Object.keys(_e47).forEach(function (s) {
            var a = _e47[s],
              r = a.getAttribute("data-swiper-slide-index");
            r && a.setAttribute("data-swiper-slide-index", parseInt(r, 10) + i), _t40[parseInt(s, 10) + i] = a;
          }), s.virtual.cache = _t40;
        }
        c(!0), s.slideTo(a, 0);
      },
      removeSlide: function removeSlide(e) {
        if (null == e) return;
        var t = s.activeIndex;
        if (Array.isArray(e)) for (var _a20 = e.length - 1; _a20 >= 0; _a20 -= 1) s.params.virtual.cache && (delete s.virtual.cache[e[_a20]], Object.keys(s.virtual.cache).forEach(function (t) {
          t > e && (s.virtual.cache[t - 1] = s.virtual.cache[t], s.virtual.cache[t - 1].setAttribute("data-swiper-slide-index", t - 1), delete s.virtual.cache[t]);
        })), s.virtual.slides.splice(e[_a20], 1), e[_a20] < t && (t -= 1), t = Math.max(t, 0);else s.params.virtual.cache && (delete s.virtual.cache[e], Object.keys(s.virtual.cache).forEach(function (t) {
          t > e && (s.virtual.cache[t - 1] = s.virtual.cache[t], s.virtual.cache[t - 1].setAttribute("data-swiper-slide-index", t - 1), delete s.virtual.cache[t]);
        })), s.virtual.slides.splice(e, 1), e < t && (t -= 1), t = Math.max(t, 0);
        c(!0), s.slideTo(t, 0);
      },
      removeAllSlides: function removeAllSlides() {
        s.virtual.slides = [], s.params.virtual.cache && (s.virtual.cache = {}), c(!0), s.slideTo(0, 0);
      },
      update: c
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      i = e.on,
      n = e.emit;
    var l = a(),
      o = r();
    function d(e) {
      if (!t.enabled) return;
      var s = t.rtlTranslate;
      var a = e;
      a.originalEvent && (a = a.originalEvent);
      var i = a.keyCode || a.charCode,
        r = t.params.keyboard.pageUpDown,
        d = r && 33 === i,
        c = r && 34 === i,
        p = 37 === i,
        u = 39 === i,
        m = 38 === i,
        h = 40 === i;
      if (!t.allowSlideNext && (t.isHorizontal() && u || t.isVertical() && h || c)) return !1;
      if (!t.allowSlidePrev && (t.isHorizontal() && p || t.isVertical() && m || d)) return !1;
      if (!(a.shiftKey || a.altKey || a.ctrlKey || a.metaKey || l.activeElement && l.activeElement.nodeName && ("input" === l.activeElement.nodeName.toLowerCase() || "textarea" === l.activeElement.nodeName.toLowerCase()))) {
        if (t.params.keyboard.onlyInViewport && (d || c || p || u || m || h)) {
          var _e48 = !1;
          if (E(t.el, ".".concat(t.params.slideClass, ", swiper-slide")).length > 0 && 0 === E(t.el, ".".concat(t.params.slideActiveClass)).length) return;
          var _a21 = t.el,
            _i10 = _a21.clientWidth,
            _r8 = _a21.clientHeight,
            _n5 = o.innerWidth,
            _l5 = o.innerHeight,
            _d2 = w(_a21);
          s && (_d2.left -= _a21.scrollLeft);
          var _c2 = [[_d2.left, _d2.top], [_d2.left + _i10, _d2.top], [_d2.left, _d2.top + _r8], [_d2.left + _i10, _d2.top + _r8]];
          for (var _t41 = 0; _t41 < _c2.length; _t41 += 1) {
            var _s20 = _c2[_t41];
            if (_s20[0] >= 0 && _s20[0] <= _n5 && _s20[1] >= 0 && _s20[1] <= _l5) {
              if (0 === _s20[0] && 0 === _s20[1]) continue;
              _e48 = !0;
            }
          }
          if (!_e48) return;
        }
        t.isHorizontal() ? ((d || c || p || u) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), ((c || u) && !s || (d || p) && s) && t.slideNext(), ((d || p) && !s || (c || u) && s) && t.slidePrev()) : ((d || c || m || h) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), (c || h) && t.slideNext(), (d || m) && t.slidePrev()), n("keyPress", i);
      }
    }
    function c() {
      t.keyboard.enabled || (l.addEventListener("keydown", d), t.keyboard.enabled = !0);
    }
    function p() {
      t.keyboard.enabled && (l.removeEventListener("keydown", d), t.keyboard.enabled = !1);
    }
    t.keyboard = {
      enabled: !1
    }, s({
      keyboard: {
        enabled: !1,
        onlyInViewport: !0,
        pageUpDown: !0
      }
    }), i("init", function () {
      t.params.keyboard.enabled && c();
    }), i("destroy", function () {
      t.keyboard.enabled && p();
    }), Object.assign(t.keyboard, {
      enable: c,
      disable: p
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on,
      i = e.emit;
    var n = r();
    var d;
    s({
      mousewheel: {
        enabled: !1,
        releaseOnEdges: !1,
        invert: !1,
        forceToAxis: !1,
        sensitivity: 1,
        eventsTarget: "container",
        thresholdDelta: null,
        thresholdTime: null,
        noMousewheelClass: "swiper-no-mousewheel"
      }
    }), t.mousewheel = {
      enabled: !1
    };
    var c,
      p = o();
    var u = [];
    function m() {
      t.enabled && (t.mouseEntered = !0);
    }
    function h() {
      t.enabled && (t.mouseEntered = !1);
    }
    function f(e) {
      return !(t.params.mousewheel.thresholdDelta && e.delta < t.params.mousewheel.thresholdDelta) && !(t.params.mousewheel.thresholdTime && o() - p < t.params.mousewheel.thresholdTime) && (e.delta >= 6 && o() - p < 60 || (e.direction < 0 ? t.isEnd && !t.params.loop || t.animating || (t.slideNext(), i("scroll", e.raw)) : t.isBeginning && !t.params.loop || t.animating || (t.slidePrev(), i("scroll", e.raw)), p = new n.Date().getTime(), !1));
    }
    function g(e) {
      var s = e,
        a = !0;
      if (!t.enabled) return;
      if (e.target.closest(".".concat(t.params.mousewheel.noMousewheelClass))) return;
      var r = t.params.mousewheel;
      t.params.cssMode && s.preventDefault();
      var n = t.el;
      "container" !== t.params.mousewheel.eventsTarget && (n = document.querySelector(t.params.mousewheel.eventsTarget));
      var p = n && n.contains(s.target);
      if (!t.mouseEntered && !p && !r.releaseOnEdges) return !0;
      s.originalEvent && (s = s.originalEvent);
      var m = 0;
      var h = t.rtlTranslate ? -1 : 1,
        g = function (e) {
          var t = 0,
            s = 0,
            a = 0,
            i = 0;
          return "detail" in e && (s = e.detail), "wheelDelta" in e && (s = -e.wheelDelta / 120), "wheelDeltaY" in e && (s = -e.wheelDeltaY / 120), "wheelDeltaX" in e && (t = -e.wheelDeltaX / 120), "axis" in e && e.axis === e.HORIZONTAL_AXIS && (t = s, s = 0), a = 10 * t, i = 10 * s, "deltaY" in e && (i = e.deltaY), "deltaX" in e && (a = e.deltaX), e.shiftKey && !a && (a = i, i = 0), (a || i) && e.deltaMode && (1 === e.deltaMode ? (a *= 40, i *= 40) : (a *= 800, i *= 800)), a && !t && (t = a < 1 ? -1 : 1), i && !s && (s = i < 1 ? -1 : 1), {
            spinX: t,
            spinY: s,
            pixelX: a,
            pixelY: i
          };
        }(s);
      if (r.forceToAxis) {
        if (t.isHorizontal()) {
          if (!(Math.abs(g.pixelX) > Math.abs(g.pixelY))) return !0;
          m = -g.pixelX * h;
        } else {
          if (!(Math.abs(g.pixelY) > Math.abs(g.pixelX))) return !0;
          m = -g.pixelY;
        }
      } else m = Math.abs(g.pixelX) > Math.abs(g.pixelY) ? -g.pixelX * h : -g.pixelY;
      if (0 === m) return !0;
      r.invert && (m = -m);
      var v = t.getTranslate() + m * r.sensitivity;
      if (v >= t.minTranslate() && (v = t.minTranslate()), v <= t.maxTranslate() && (v = t.maxTranslate()), a = !!t.params.loop || !(v === t.minTranslate() || v === t.maxTranslate()), a && t.params.nested && s.stopPropagation(), t.params.freeMode && t.params.freeMode.enabled) {
        var _e49 = {
            time: o(),
            delta: Math.abs(m),
            direction: Math.sign(m)
          },
          _a22 = c && _e49.time < c.time + 500 && _e49.delta <= c.delta && _e49.direction === c.direction;
        if (!_a22) {
          c = void 0;
          var _n6 = t.getTranslate() + m * r.sensitivity;
          var _o5 = t.isBeginning,
            _p2 = t.isEnd;
          if (_n6 >= t.minTranslate() && (_n6 = t.minTranslate()), _n6 <= t.maxTranslate() && (_n6 = t.maxTranslate()), t.setTransition(0), t.setTranslate(_n6), t.updateProgress(), t.updateActiveIndex(), t.updateSlidesClasses(), (!_o5 && t.isBeginning || !_p2 && t.isEnd) && t.updateSlidesClasses(), t.params.loop && t.loopFix({
            direction: _e49.direction < 0 ? "next" : "prev",
            byMousewheel: !0
          }), t.params.freeMode.sticky) {
            clearTimeout(d), d = void 0, u.length >= 15 && u.shift();
            var _s21 = u.length ? u[u.length - 1] : void 0,
              _a23 = u[0];
            if (u.push(_e49), _s21 && (_e49.delta > _s21.delta || _e49.direction !== _s21.direction)) u.splice(0);else if (u.length >= 15 && _e49.time - _a23.time < 500 && _a23.delta - _e49.delta >= 1 && _e49.delta <= 6) {
              var _s22 = m > 0 ? .8 : .2;
              c = _e49, u.splice(0), d = l(function () {
                !t.destroyed && t.params && t.slideToClosest(t.params.speed, !0, void 0, _s22);
              }, 0);
            }
            d || (d = l(function () {
              if (t.destroyed || !t.params) return;
              c = _e49, u.splice(0), t.slideToClosest(t.params.speed, !0, void 0, .5);
            }, 500));
          }
          if (_a22 || i("scroll", s), t.params.autoplay && t.params.autoplay.disableOnInteraction && t.autoplay.stop(), r.releaseOnEdges && (_n6 === t.minTranslate() || _n6 === t.maxTranslate())) return !0;
        }
      } else {
        var _s23 = {
          time: o(),
          delta: Math.abs(m),
          direction: Math.sign(m),
          raw: e
        };
        u.length >= 2 && u.shift();
        var _a24 = u.length ? u[u.length - 1] : void 0;
        if (u.push(_s23), _a24 ? (_s23.direction !== _a24.direction || _s23.delta > _a24.delta || _s23.time > _a24.time + 150) && f(_s23) : f(_s23), function (e) {
          var s = t.params.mousewheel;
          if (e.direction < 0) {
            if (t.isEnd && !t.params.loop && s.releaseOnEdges) return !0;
          } else if (t.isBeginning && !t.params.loop && s.releaseOnEdges) return !0;
          return !1;
        }(_s23)) return !0;
      }
      return s.preventDefault ? s.preventDefault() : s.returnValue = !1, !1;
    }
    function v(e) {
      var s = t.el;
      "container" !== t.params.mousewheel.eventsTarget && (s = document.querySelector(t.params.mousewheel.eventsTarget)), s[e]("mouseenter", m), s[e]("mouseleave", h), s[e]("wheel", g);
    }
    function w() {
      return t.params.cssMode ? (t.wrapperEl.removeEventListener("wheel", g), !0) : !t.mousewheel.enabled && (v("addEventListener"), t.mousewheel.enabled = !0, !0);
    }
    function b() {
      return t.params.cssMode ? (t.wrapperEl.addEventListener(event, g), !0) : !!t.mousewheel.enabled && (v("removeEventListener"), t.mousewheel.enabled = !1, !0);
    }
    a("init", function () {
      !t.params.mousewheel.enabled && t.params.cssMode && b(), t.params.mousewheel.enabled && w();
    }), a("destroy", function () {
      t.params.cssMode && w(), t.mousewheel.enabled && b();
    }), Object.assign(t.mousewheel, {
      enable: w,
      disable: b
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on,
      i = e.emit;
    function r(e) {
      var s;
      return e && "string" == typeof e && t.isElement && (s = t.el.querySelector(e) || t.hostEl.querySelector(e), s) ? s : (e && ("string" == typeof e && (s = _toConsumableArray(document.querySelectorAll(e))), t.params.uniqueNavElements && "string" == typeof e && s && s.length > 1 && 1 === t.el.querySelectorAll(e).length ? s = t.el.querySelector(e) : s && 1 === s.length && (s = s[0])), e && !s ? e : s);
    }
    function n(e, s) {
      var a = t.params.navigation;
      (e = T(e)).forEach(function (e) {
        var _e$classList2;
        e && ((_e$classList2 = e.classList)[s ? "add" : "remove"].apply(_e$classList2, _toConsumableArray(a.disabledClass.split(" "))), "BUTTON" === e.tagName && (e.disabled = s), t.params.watchOverflow && t.enabled && e.classList[t.isLocked ? "add" : "remove"](a.lockClass));
      });
    }
    function l() {
      var _t$navigation = t.navigation,
        e = _t$navigation.nextEl,
        s = _t$navigation.prevEl;
      if (t.params.loop) return n(s, !1), void n(e, !1);
      n(s, t.isBeginning && !t.params.rewind), n(e, t.isEnd && !t.params.rewind);
    }
    function o(e) {
      e.preventDefault(), (!t.isBeginning || t.params.loop || t.params.rewind) && (t.slidePrev(), i("navigationPrev"));
    }
    function d(e) {
      e.preventDefault(), (!t.isEnd || t.params.loop || t.params.rewind) && (t.slideNext(), i("navigationNext"));
    }
    function c() {
      var e = t.params.navigation;
      if (t.params.navigation = re(t, t.originalParams.navigation, t.params.navigation, {
        nextEl: "swiper-button-next",
        prevEl: "swiper-button-prev"
      }), !e.nextEl && !e.prevEl) return;
      var s = r(e.nextEl),
        a = r(e.prevEl);
      Object.assign(t.navigation, {
        nextEl: s,
        prevEl: a
      }), s = T(s), a = T(a);
      var i = function i(s, a) {
        var _s$classList2;
        s && s.addEventListener("click", "next" === a ? d : o), !t.enabled && s && (_s$classList2 = s.classList).add.apply(_s$classList2, _toConsumableArray(e.lockClass.split(" ")));
      };
      s.forEach(function (e) {
        return i(e, "next");
      }), a.forEach(function (e) {
        return i(e, "prev");
      });
    }
    function p() {
      var _t$navigation2 = t.navigation,
        e = _t$navigation2.nextEl,
        s = _t$navigation2.prevEl;
      e = T(e), s = T(s);
      var a = function a(e, s) {
        var _e$classList3;
        e.removeEventListener("click", "next" === s ? d : o), (_e$classList3 = e.classList).remove.apply(_e$classList3, _toConsumableArray(t.params.navigation.disabledClass.split(" ")));
      };
      e.forEach(function (e) {
        return a(e, "next");
      }), s.forEach(function (e) {
        return a(e, "prev");
      });
    }
    s({
      navigation: {
        nextEl: null,
        prevEl: null,
        hideOnClick: !1,
        disabledClass: "swiper-button-disabled",
        hiddenClass: "swiper-button-hidden",
        lockClass: "swiper-button-lock",
        navigationDisabledClass: "swiper-navigation-disabled"
      }
    }), t.navigation = {
      nextEl: null,
      prevEl: null
    }, a("init", function () {
      !1 === t.params.navigation.enabled ? u() : (c(), l());
    }), a("toEdge fromEdge lock unlock", function () {
      l();
    }), a("destroy", function () {
      p();
    }), a("enable disable", function () {
      var _t$navigation3 = t.navigation,
        e = _t$navigation3.nextEl,
        s = _t$navigation3.prevEl;
      e = T(e), s = T(s), t.enabled ? l() : [].concat(_toConsumableArray(e), _toConsumableArray(s)).filter(function (e) {
        return !!e;
      }).forEach(function (e) {
        return e.classList.add(t.params.navigation.lockClass);
      });
    }), a("click", function (e, s) {
      var _t$navigation4 = t.navigation,
        a = _t$navigation4.nextEl,
        r = _t$navigation4.prevEl;
      a = T(a), r = T(r);
      var n = s.target;
      var l = r.includes(n) || a.includes(n);
      if (t.isElement && !l) {
        var _e50 = s.path || s.composedPath && s.composedPath();
        _e50 && (l = _e50.find(function (e) {
          return a.includes(e) || r.includes(e);
        }));
      }
      if (t.params.navigation.hideOnClick && !l) {
        if (t.pagination && t.params.pagination && t.params.pagination.clickable && (t.pagination.el === n || t.pagination.el.contains(n))) return;
        var _e51;
        a.length ? _e51 = a[0].classList.contains(t.params.navigation.hiddenClass) : r.length && (_e51 = r[0].classList.contains(t.params.navigation.hiddenClass)), i(!0 === _e51 ? "navigationShow" : "navigationHide"), [].concat(_toConsumableArray(a), _toConsumableArray(r)).filter(function (e) {
          return !!e;
        }).forEach(function (e) {
          return e.classList.toggle(t.params.navigation.hiddenClass);
        });
      }
    });
    var u = function u() {
      var _t$el$classList;
      (_t$el$classList = t.el.classList).add.apply(_t$el$classList, _toConsumableArray(t.params.navigation.navigationDisabledClass.split(" "))), p();
    };
    Object.assign(t.navigation, {
      enable: function enable() {
        var _t$el$classList2;
        (_t$el$classList2 = t.el.classList).remove.apply(_t$el$classList2, _toConsumableArray(t.params.navigation.navigationDisabledClass.split(" "))), c(), l();
      },
      disable: u,
      update: l,
      init: c,
      destroy: p
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on,
      i = e.emit;
    var r = "swiper-pagination";
    var n;
    s({
      pagination: {
        el: null,
        bulletElement: "span",
        clickable: !1,
        hideOnClick: !1,
        renderBullet: null,
        renderProgressbar: null,
        renderFraction: null,
        renderCustom: null,
        progressbarOpposite: !1,
        type: "bullets",
        dynamicBullets: !1,
        dynamicMainBullets: 1,
        formatFractionCurrent: function formatFractionCurrent(e) {
          return e;
        },
        formatFractionTotal: function formatFractionTotal(e) {
          return e;
        },
        bulletClass: "".concat(r, "-bullet"),
        bulletActiveClass: "".concat(r, "-bullet-active"),
        modifierClass: "".concat(r, "-"),
        currentClass: "".concat(r, "-current"),
        totalClass: "".concat(r, "-total"),
        hiddenClass: "".concat(r, "-hidden"),
        progressbarFillClass: "".concat(r, "-progressbar-fill"),
        progressbarOppositeClass: "".concat(r, "-progressbar-opposite"),
        clickableClass: "".concat(r, "-clickable"),
        lockClass: "".concat(r, "-lock"),
        horizontalClass: "".concat(r, "-horizontal"),
        verticalClass: "".concat(r, "-vertical"),
        paginationDisabledClass: "".concat(r, "-disabled")
      }
    }), t.pagination = {
      el: null,
      bullets: []
    };
    var l = 0;
    function o() {
      return !t.params.pagination.el || !t.pagination.el || Array.isArray(t.pagination.el) && 0 === t.pagination.el.length;
    }
    function d(e, s) {
      var a = t.params.pagination.bulletActiveClass;
      e && (e = e[("prev" === s ? "previous" : "next") + "ElementSibling"]) && (e.classList.add("".concat(a, "-").concat(s)), (e = e[("prev" === s ? "previous" : "next") + "ElementSibling"]) && e.classList.add("".concat(a, "-").concat(s, "-").concat(s)));
    }
    function c(e) {
      var s = e.target.closest(ne(t.params.pagination.bulletClass));
      if (!s) return;
      e.preventDefault();
      var a = y(s) * t.params.slidesPerGroup;
      if (t.params.loop) {
        if (t.realIndex === a) return;
        var _e52 = (i = t.realIndex, r = a, n = t.slides.length, (r %= n) == 1 + (i %= n) ? "next" : r === i - 1 ? "previous" : void 0);
        "next" === _e52 ? t.slideNext() : "previous" === _e52 ? t.slidePrev() : t.slideToLoop(a);
      } else t.slideTo(a);
      var i, r, n;
    }
    function p() {
      var e = t.rtl,
        s = t.params.pagination;
      if (o()) return;
      var a,
        r,
        c = t.pagination.el;
      c = T(c);
      var p = t.virtual && t.params.virtual.enabled ? t.virtual.slides.length : t.slides.length,
        u = t.params.loop ? Math.ceil(p / t.params.slidesPerGroup) : t.snapGrid.length;
      if (t.params.loop ? (r = t.previousRealIndex || 0, a = t.params.slidesPerGroup > 1 ? Math.floor(t.realIndex / t.params.slidesPerGroup) : t.realIndex) : void 0 !== t.snapIndex ? (a = t.snapIndex, r = t.previousSnapIndex) : (r = t.previousIndex || 0, a = t.activeIndex || 0), "bullets" === s.type && t.pagination.bullets && t.pagination.bullets.length > 0) {
        var _i11 = t.pagination.bullets;
        var _o6, _p3, _u3;
        if (s.dynamicBullets && (n = S(_i11[0], t.isHorizontal() ? "width" : "height", !0), c.forEach(function (e) {
          e.style[t.isHorizontal() ? "width" : "height"] = n * (s.dynamicMainBullets + 4) + "px";
        }), s.dynamicMainBullets > 1 && void 0 !== r && (l += a - (r || 0), l > s.dynamicMainBullets - 1 ? l = s.dynamicMainBullets - 1 : l < 0 && (l = 0)), _o6 = Math.max(a - l, 0), _p3 = _o6 + (Math.min(_i11.length, s.dynamicMainBullets) - 1), _u3 = (_p3 + _o6) / 2), _i11.forEach(function (e) {
          var _e$classList4;
          var t = _toConsumableArray(["", "-next", "-next-next", "-prev", "-prev-prev", "-main"].map(function (e) {
            return "".concat(s.bulletActiveClass).concat(e);
          })).map(function (e) {
            return "string" == typeof e && e.includes(" ") ? e.split(" ") : e;
          }).flat();
          (_e$classList4 = e.classList).remove.apply(_e$classList4, _toConsumableArray(t));
        }), c.length > 1) _i11.forEach(function (e) {
          var _e$classList5, _e$classList6;
          var i = y(e);
          i === a ? (_e$classList5 = e.classList).add.apply(_e$classList5, _toConsumableArray(s.bulletActiveClass.split(" "))) : t.isElement && e.setAttribute("part", "bullet"), s.dynamicBullets && (i >= _o6 && i <= _p3 && (_e$classList6 = e.classList).add.apply(_e$classList6, _toConsumableArray("".concat(s.bulletActiveClass, "-main").split(" "))), i === _o6 && d(e, "prev"), i === _p3 && d(e, "next"));
        });else {
          var _e53$classList;
          var _e53 = _i11[a];
          if (_e53 && (_e53$classList = _e53.classList).add.apply(_e53$classList, _toConsumableArray(s.bulletActiveClass.split(" "))), t.isElement && _i11.forEach(function (e, t) {
            e.setAttribute("part", t === a ? "bullet-active" : "bullet");
          }), s.dynamicBullets) {
            var _e54 = _i11[_o6],
              _t42 = _i11[_p3];
            for (var _e55 = _o6; _e55 <= _p3; _e55 += 1) {
              var _i11$_e55$classList;
              _i11[_e55] && (_i11$_e55$classList = _i11[_e55].classList).add.apply(_i11$_e55$classList, _toConsumableArray("".concat(s.bulletActiveClass, "-main").split(" ")));
            }
            d(_e54, "prev"), d(_t42, "next");
          }
        }
        if (s.dynamicBullets) {
          var _a25 = Math.min(_i11.length, s.dynamicMainBullets + 4),
            _r9 = (n * _a25 - n) / 2 - _u3 * n,
            _l6 = e ? "right" : "left";
          _i11.forEach(function (e) {
            e.style[t.isHorizontal() ? _l6 : "top"] = "".concat(_r9, "px");
          });
        }
      }
      c.forEach(function (e, r) {
        if ("fraction" === s.type && (e.querySelectorAll(ne(s.currentClass)).forEach(function (e) {
          e.textContent = s.formatFractionCurrent(a + 1);
        }), e.querySelectorAll(ne(s.totalClass)).forEach(function (e) {
          e.textContent = s.formatFractionTotal(u);
        })), "progressbar" === s.type) {
          var _i12;
          _i12 = s.progressbarOpposite ? t.isHorizontal() ? "vertical" : "horizontal" : t.isHorizontal() ? "horizontal" : "vertical";
          var _r10 = (a + 1) / u;
          var _n7 = 1,
            _l7 = 1;
          "horizontal" === _i12 ? _n7 = _r10 : _l7 = _r10, e.querySelectorAll(ne(s.progressbarFillClass)).forEach(function (e) {
            e.style.transform = "translate3d(0,0,0) scaleX(".concat(_n7, ") scaleY(").concat(_l7, ")"), e.style.transitionDuration = "".concat(t.params.speed, "ms");
          });
        }
        "custom" === s.type && s.renderCustom ? (e.innerHTML = s.renderCustom(t, a + 1, u), 0 === r && i("paginationRender", e)) : (0 === r && i("paginationRender", e), i("paginationUpdate", e)), t.params.watchOverflow && t.enabled && e.classList[t.isLocked ? "add" : "remove"](s.lockClass);
      });
    }
    function u() {
      var e = t.params.pagination;
      if (o()) return;
      var s = t.virtual && t.params.virtual.enabled ? t.virtual.slides.length : t.grid && t.params.grid.rows > 1 ? t.slides.length / Math.ceil(t.params.grid.rows) : t.slides.length;
      var a = t.pagination.el;
      a = T(a);
      var r = "";
      if ("bullets" === e.type) {
        var _a26 = t.params.loop ? Math.ceil(s / t.params.slidesPerGroup) : t.snapGrid.length;
        t.params.freeMode && t.params.freeMode.enabled && _a26 > s && (_a26 = s);
        for (var _s24 = 0; _s24 < _a26; _s24 += 1) e.renderBullet ? r += e.renderBullet.call(t, _s24, e.bulletClass) : r += "<".concat(e.bulletElement, " ").concat(t.isElement ? 'part="bullet"' : "", " class=\"").concat(e.bulletClass, "\"></").concat(e.bulletElement, ">");
      }
      "fraction" === e.type && (r = e.renderFraction ? e.renderFraction.call(t, e.currentClass, e.totalClass) : "<span class=\"".concat(e.currentClass, "\"></span> / <span class=\"").concat(e.totalClass, "\"></span>")), "progressbar" === e.type && (r = e.renderProgressbar ? e.renderProgressbar.call(t, e.progressbarFillClass) : "<span class=\"".concat(e.progressbarFillClass, "\"></span>")), t.pagination.bullets = [], a.forEach(function (s) {
        var _t$pagination$bullets;
        "custom" !== e.type && (s.innerHTML = r || ""), "bullets" === e.type && (_t$pagination$bullets = t.pagination.bullets).push.apply(_t$pagination$bullets, _toConsumableArray(s.querySelectorAll(ne(e.bulletClass))));
      }), "custom" !== e.type && i("paginationRender", a[0]);
    }
    function m() {
      t.params.pagination = re(t, t.originalParams.pagination, t.params.pagination, {
        el: "swiper-pagination"
      });
      var e = t.params.pagination;
      if (!e.el) return;
      var s;
      "string" == typeof e.el && t.isElement && (s = t.el.querySelector(e.el)), s || "string" != typeof e.el || (s = _toConsumableArray(document.querySelectorAll(e.el))), s || (s = e.el), s && 0 !== s.length && (t.params.uniqueNavElements && "string" == typeof e.el && Array.isArray(s) && s.length > 1 && (s = _toConsumableArray(t.el.querySelectorAll(e.el)), s.length > 1 && (s = s.find(function (e) {
        return E(e, ".swiper")[0] === t.el;
      }))), Array.isArray(s) && 1 === s.length && (s = s[0]), Object.assign(t.pagination, {
        el: s
      }), s = T(s), s.forEach(function (s) {
        var _s$classList3;
        "bullets" === e.type && e.clickable && (_s$classList3 = s.classList).add.apply(_s$classList3, _toConsumableArray((e.clickableClass || "").split(" "))), s.classList.add(e.modifierClass + e.type), s.classList.add(t.isHorizontal() ? e.horizontalClass : e.verticalClass), "bullets" === e.type && e.dynamicBullets && (s.classList.add("".concat(e.modifierClass).concat(e.type, "-dynamic")), l = 0, e.dynamicMainBullets < 1 && (e.dynamicMainBullets = 1)), "progressbar" === e.type && e.progressbarOpposite && s.classList.add(e.progressbarOppositeClass), e.clickable && s.addEventListener("click", c), t.enabled || s.classList.add(e.lockClass);
      }));
    }
    function h() {
      var e = t.params.pagination;
      if (o()) return;
      var s = t.pagination.el;
      s && (s = T(s), s.forEach(function (s) {
        var _s$classList4;
        s.classList.remove(e.hiddenClass), s.classList.remove(e.modifierClass + e.type), s.classList.remove(t.isHorizontal() ? e.horizontalClass : e.verticalClass), e.clickable && ((_s$classList4 = s.classList).remove.apply(_s$classList4, _toConsumableArray((e.clickableClass || "").split(" "))), s.removeEventListener("click", c));
      })), t.pagination.bullets && t.pagination.bullets.forEach(function (t) {
        var _t$classList;
        return (_t$classList = t.classList).remove.apply(_t$classList, _toConsumableArray(e.bulletActiveClass.split(" ")));
      });
    }
    a("changeDirection", function () {
      if (!t.pagination || !t.pagination.el) return;
      var e = t.params.pagination;
      var s = t.pagination.el;
      s = T(s), s.forEach(function (s) {
        s.classList.remove(e.horizontalClass, e.verticalClass), s.classList.add(t.isHorizontal() ? e.horizontalClass : e.verticalClass);
      });
    }), a("init", function () {
      !1 === t.params.pagination.enabled ? f() : (m(), u(), p());
    }), a("activeIndexChange", function () {
      void 0 === t.snapIndex && p();
    }), a("snapIndexChange", function () {
      p();
    }), a("snapGridLengthChange", function () {
      u(), p();
    }), a("destroy", function () {
      h();
    }), a("enable disable", function () {
      var e = t.pagination.el;
      e && (e = T(e), e.forEach(function (e) {
        return e.classList[t.enabled ? "remove" : "add"](t.params.pagination.lockClass);
      }));
    }), a("lock unlock", function () {
      p();
    }), a("click", function (e, s) {
      var a = s.target,
        r = T(t.pagination.el);
      if (t.params.pagination.el && t.params.pagination.hideOnClick && r && r.length > 0 && !a.classList.contains(t.params.pagination.bulletClass)) {
        if (t.navigation && (t.navigation.nextEl && a === t.navigation.nextEl || t.navigation.prevEl && a === t.navigation.prevEl)) return;
        var _e56 = r[0].classList.contains(t.params.pagination.hiddenClass);
        i(!0 === _e56 ? "paginationShow" : "paginationHide"), r.forEach(function (e) {
          return e.classList.toggle(t.params.pagination.hiddenClass);
        });
      }
    });
    var f = function f() {
      t.el.classList.add(t.params.pagination.paginationDisabledClass);
      var e = t.pagination.el;
      e && (e = T(e), e.forEach(function (e) {
        return e.classList.add(t.params.pagination.paginationDisabledClass);
      })), h();
    };
    Object.assign(t.pagination, {
      enable: function enable() {
        t.el.classList.remove(t.params.pagination.paginationDisabledClass);
        var e = t.pagination.el;
        e && (e = T(e), e.forEach(function (e) {
          return e.classList.remove(t.params.pagination.paginationDisabledClass);
        })), m(), u(), p();
      },
      disable: f,
      render: u,
      update: p,
      init: m,
      destroy: h
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      i = e.on,
      r = e.emit;
    var o = a();
    var d,
      c,
      p,
      u,
      m = !1,
      h = null,
      f = null;
    function g() {
      if (!t.params.scrollbar.el || !t.scrollbar.el) return;
      var e = t.scrollbar,
        s = t.rtlTranslate,
        a = e.dragEl,
        i = e.el,
        r = t.params.scrollbar,
        n = t.params.loop ? t.progressLoop : t.progress;
      var l = c,
        o = (p - c) * n;
      s ? (o = -o, o > 0 ? (l = c - o, o = 0) : -o + c > p && (l = p + o)) : o < 0 ? (l = c + o, o = 0) : o + c > p && (l = p - o), t.isHorizontal() ? (a.style.transform = "translate3d(".concat(o, "px, 0, 0)"), a.style.width = "".concat(l, "px")) : (a.style.transform = "translate3d(0px, ".concat(o, "px, 0)"), a.style.height = "".concat(l, "px")), r.hide && (clearTimeout(h), i.style.opacity = 1, h = setTimeout(function () {
        i.style.opacity = 0, i.style.transitionDuration = "400ms";
      }, 1e3));
    }
    function b() {
      if (!t.params.scrollbar.el || !t.scrollbar.el) return;
      var e = t.scrollbar,
        s = e.dragEl,
        a = e.el;
      s.style.width = "", s.style.height = "", p = t.isHorizontal() ? a.offsetWidth : a.offsetHeight, u = t.size / (t.virtualSize + t.params.slidesOffsetBefore - (t.params.centeredSlides ? t.snapGrid[0] : 0)), c = "auto" === t.params.scrollbar.dragSize ? p * u : parseInt(t.params.scrollbar.dragSize, 10), t.isHorizontal() ? s.style.width = "".concat(c, "px") : s.style.height = "".concat(c, "px"), a.style.display = u >= 1 ? "none" : "", t.params.scrollbar.hide && (a.style.opacity = 0), t.params.watchOverflow && t.enabled && e.el.classList[t.isLocked ? "add" : "remove"](t.params.scrollbar.lockClass);
    }
    function y(e) {
      return t.isHorizontal() ? e.clientX : e.clientY;
    }
    function E(e) {
      var s = t.scrollbar,
        a = t.rtlTranslate,
        i = s.el;
      var r;
      r = (y(e) - w(i)[t.isHorizontal() ? "left" : "top"] - (null !== d ? d : c / 2)) / (p - c), r = Math.max(Math.min(r, 1), 0), a && (r = 1 - r);
      var n = t.minTranslate() + (t.maxTranslate() - t.minTranslate()) * r;
      t.updateProgress(n), t.setTranslate(n), t.updateActiveIndex(), t.updateSlidesClasses();
    }
    function x(e) {
      var s = t.params.scrollbar,
        a = t.scrollbar,
        i = t.wrapperEl,
        n = a.el,
        l = a.dragEl;
      m = !0, d = e.target === l ? y(e) - e.target.getBoundingClientRect()[t.isHorizontal() ? "left" : "top"] : null, e.preventDefault(), e.stopPropagation(), i.style.transitionDuration = "100ms", l.style.transitionDuration = "100ms", E(e), clearTimeout(f), n.style.transitionDuration = "0ms", s.hide && (n.style.opacity = 1), t.params.cssMode && (t.wrapperEl.style["scroll-snap-type"] = "none"), r("scrollbarDragStart", e);
    }
    function S(e) {
      var s = t.scrollbar,
        a = t.wrapperEl,
        i = s.el,
        n = s.dragEl;
      m && (e.preventDefault && e.cancelable ? e.preventDefault() : e.returnValue = !1, E(e), a.style.transitionDuration = "0ms", i.style.transitionDuration = "0ms", n.style.transitionDuration = "0ms", r("scrollbarDragMove", e));
    }
    function M(e) {
      var s = t.params.scrollbar,
        a = t.scrollbar,
        i = t.wrapperEl,
        n = a.el;
      m && (m = !1, t.params.cssMode && (t.wrapperEl.style["scroll-snap-type"] = "", i.style.transitionDuration = ""), s.hide && (clearTimeout(f), f = l(function () {
        n.style.opacity = 0, n.style.transitionDuration = "400ms";
      }, 1e3)), r("scrollbarDragEnd", e), s.snapOnRelease && t.slideToClosest());
    }
    function C(e) {
      var s = t.scrollbar,
        a = t.params,
        i = s.el;
      if (!i) return;
      var r = i,
        n = !!a.passiveListeners && {
          passive: !1,
          capture: !1
        },
        l = !!a.passiveListeners && {
          passive: !0,
          capture: !1
        };
      if (!r) return;
      var d = "on" === e ? "addEventListener" : "removeEventListener";
      r[d]("pointerdown", x, n), o[d]("pointermove", S, n), o[d]("pointerup", M, l);
    }
    function P() {
      var _i$classList2;
      var e = t.scrollbar,
        s = t.el;
      t.params.scrollbar = re(t, t.originalParams.scrollbar, t.params.scrollbar, {
        el: "swiper-scrollbar"
      });
      var a = t.params.scrollbar;
      if (!a.el) return;
      var i, r;
      if ("string" == typeof a.el && t.isElement && (i = t.el.querySelector(a.el)), i || "string" != typeof a.el) i || (i = a.el);else if (i = o.querySelectorAll(a.el), !i.length) return;
      t.params.uniqueNavElements && "string" == typeof a.el && i.length > 1 && 1 === s.querySelectorAll(a.el).length && (i = s.querySelector(a.el)), i.length > 0 && (i = i[0]), i.classList.add(t.isHorizontal() ? a.horizontalClass : a.verticalClass), i && (r = i.querySelector(ne(t.params.scrollbar.dragClass)), r || (r = v("div", t.params.scrollbar.dragClass), i.append(r))), Object.assign(e, {
        el: i,
        dragEl: r
      }), a.draggable && t.params.scrollbar.el && t.scrollbar.el && C("on"), i && (_i$classList2 = i.classList)[t.enabled ? "remove" : "add"].apply(_i$classList2, _toConsumableArray(n(t.params.scrollbar.lockClass)));
    }
    function L() {
      var _s$classList5;
      var e = t.params.scrollbar,
        s = t.scrollbar.el;
      s && (_s$classList5 = s.classList).remove.apply(_s$classList5, _toConsumableArray(n(t.isHorizontal() ? e.horizontalClass : e.verticalClass))), t.params.scrollbar.el && t.scrollbar.el && C("off");
    }
    s({
      scrollbar: {
        el: null,
        dragSize: "auto",
        hide: !1,
        draggable: !1,
        snapOnRelease: !0,
        lockClass: "swiper-scrollbar-lock",
        dragClass: "swiper-scrollbar-drag",
        scrollbarDisabledClass: "swiper-scrollbar-disabled",
        horizontalClass: "swiper-scrollbar-horizontal",
        verticalClass: "swiper-scrollbar-vertical"
      }
    }), t.scrollbar = {
      el: null,
      dragEl: null
    }, i("changeDirection", function () {
      if (!t.scrollbar || !t.scrollbar.el) return;
      var e = t.params.scrollbar;
      var s = t.scrollbar.el;
      s = T(s), s.forEach(function (s) {
        s.classList.remove(e.horizontalClass, e.verticalClass), s.classList.add(t.isHorizontal() ? e.horizontalClass : e.verticalClass);
      });
    }), i("init", function () {
      !1 === t.params.scrollbar.enabled ? I() : (P(), b(), g());
    }), i("update resize observerUpdate lock unlock changeDirection", function () {
      b();
    }), i("setTranslate", function () {
      g();
    }), i("setTransition", function (e, s) {
      !function (e) {
        t.params.scrollbar.el && t.scrollbar.el && (t.scrollbar.dragEl.style.transitionDuration = "".concat(e, "ms"));
      }(s);
    }), i("enable disable", function () {
      var _e$classList7;
      var e = t.scrollbar.el;
      e && (_e$classList7 = e.classList)[t.enabled ? "remove" : "add"].apply(_e$classList7, _toConsumableArray(n(t.params.scrollbar.lockClass)));
    }), i("destroy", function () {
      L();
    });
    var I = function I() {
      var _t$el$classList3, _t$scrollbar$el$class;
      (_t$el$classList3 = t.el.classList).add.apply(_t$el$classList3, _toConsumableArray(n(t.params.scrollbar.scrollbarDisabledClass))), t.scrollbar.el && (_t$scrollbar$el$class = t.scrollbar.el.classList).add.apply(_t$scrollbar$el$class, _toConsumableArray(n(t.params.scrollbar.scrollbarDisabledClass))), L();
    };
    Object.assign(t.scrollbar, {
      enable: function enable() {
        var _t$el$classList4, _t$scrollbar$el$class2;
        (_t$el$classList4 = t.el.classList).remove.apply(_t$el$classList4, _toConsumableArray(n(t.params.scrollbar.scrollbarDisabledClass))), t.scrollbar.el && (_t$scrollbar$el$class2 = t.scrollbar.el.classList).remove.apply(_t$scrollbar$el$class2, _toConsumableArray(n(t.params.scrollbar.scrollbarDisabledClass))), P(), b(), g();
      },
      disable: I,
      updateSize: b,
      setTranslate: g,
      init: P,
      destroy: L
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      parallax: {
        enabled: !1
      }
    });
    var i = "[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]",
      r = function r(e, s) {
        var a = t.rtl,
          i = a ? -1 : 1,
          r = e.getAttribute("data-swiper-parallax") || "0";
        var n = e.getAttribute("data-swiper-parallax-x"),
          l = e.getAttribute("data-swiper-parallax-y");
        var o = e.getAttribute("data-swiper-parallax-scale"),
          d = e.getAttribute("data-swiper-parallax-opacity"),
          c = e.getAttribute("data-swiper-parallax-rotate");
        if (n || l ? (n = n || "0", l = l || "0") : t.isHorizontal() ? (n = r, l = "0") : (l = r, n = "0"), n = n.indexOf("%") >= 0 ? parseInt(n, 10) * s * i + "%" : n * s * i + "px", l = l.indexOf("%") >= 0 ? parseInt(l, 10) * s + "%" : l * s + "px", null != d) {
          var _t43 = d - (d - 1) * (1 - Math.abs(s));
          e.style.opacity = _t43;
        }
        var p = "translate3d(".concat(n, ", ").concat(l, ", 0px)");
        if (null != o) {
          p += " scale(".concat(o - (o - 1) * (1 - Math.abs(s)), ")");
        }
        if (c && null != c) {
          p += " rotate(".concat(c * s * -1, "deg)");
        }
        e.style.transform = p;
      },
      n = function n() {
        var e = t.el,
          s = t.slides,
          a = t.progress,
          n = t.snapGrid,
          l = t.isElement,
          o = f(e, i);
        t.isElement && o.push.apply(o, _toConsumableArray(f(t.hostEl, i))), o.forEach(function (e) {
          r(e, a);
        }), s.forEach(function (e, s) {
          var l = e.progress;
          t.params.slidesPerGroup > 1 && "auto" !== t.params.slidesPerView && (l += Math.ceil(s / 2) - a * (n.length - 1)), l = Math.min(Math.max(l, -1), 1), e.querySelectorAll("".concat(i, ", [data-swiper-parallax-rotate]")).forEach(function (e) {
            r(e, l);
          });
        });
      };
    a("beforeInit", function () {
      t.params.parallax.enabled && (t.params.watchSlidesProgress = !0, t.originalParams.watchSlidesProgress = !0);
    }), a("init", function () {
      t.params.parallax.enabled && n();
    }), a("setTranslate", function () {
      t.params.parallax.enabled && n();
    }), a("setTransition", function (e, s) {
      t.params.parallax.enabled && function (e) {
        void 0 === e && (e = t.params.speed);
        var s = t.el,
          a = t.hostEl,
          r = _toConsumableArray(s.querySelectorAll(i));
        t.isElement && r.push.apply(r, _toConsumableArray(a.querySelectorAll(i))), r.forEach(function (t) {
          var s = parseInt(t.getAttribute("data-swiper-parallax-duration"), 10) || e;
          0 === e && (s = 0), t.style.transitionDuration = "".concat(s, "ms");
        });
      }(s);
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on,
      i = e.emit;
    var n = r();
    s({
      zoom: {
        enabled: !1,
        limitToOriginalSize: !1,
        maxRatio: 3,
        minRatio: 1,
        panOnMouseMove: !1,
        toggle: !0,
        containerClass: "swiper-zoom-container",
        zoomedSlideClass: "swiper-slide-zoomed"
      }
    }), t.zoom = {
      enabled: !1
    };
    var l = 1,
      o = !1,
      c = !1,
      p = {
        x: 0,
        y: 0
      };
    var u = -3;
    var m, h;
    var g = [],
      v = {
        originX: 0,
        originY: 0,
        slideEl: void 0,
        slideWidth: void 0,
        slideHeight: void 0,
        imageEl: void 0,
        imageWrapEl: void 0,
        maxRatio: 3
      },
      b = {
        isTouched: void 0,
        isMoved: void 0,
        currentX: void 0,
        currentY: void 0,
        minX: void 0,
        minY: void 0,
        maxX: void 0,
        maxY: void 0,
        width: void 0,
        height: void 0,
        startX: void 0,
        startY: void 0,
        touchesStart: {},
        touchesCurrent: {}
      },
      y = {
        x: void 0,
        y: void 0,
        prevPositionX: void 0,
        prevPositionY: void 0,
        prevTime: void 0
      };
    var x,
      S = 1;
    function T() {
      if (g.length < 2) return 1;
      var e = g[0].pageX,
        t = g[0].pageY,
        s = g[1].pageX,
        a = g[1].pageY;
      return Math.sqrt(Math.pow(s - e, 2) + Math.pow(a - t, 2));
    }
    function M() {
      var e = t.params.zoom,
        s = v.imageWrapEl.getAttribute("data-swiper-zoom") || e.maxRatio;
      if (e.limitToOriginalSize && v.imageEl && v.imageEl.naturalWidth) {
        var _e57 = v.imageEl.naturalWidth / v.imageEl.offsetWidth;
        return Math.min(_e57, s);
      }
      return s;
    }
    function C(e) {
      var s = t.isElement ? "swiper-slide" : ".".concat(t.params.slideClass);
      return !!e.target.matches(s) || t.slides.filter(function (t) {
        return t.contains(e.target);
      }).length > 0;
    }
    function P(e) {
      var s = ".".concat(t.params.zoom.containerClass);
      return !!e.target.matches(s) || _toConsumableArray(t.hostEl.querySelectorAll(s)).filter(function (t) {
        return t.contains(e.target);
      }).length > 0;
    }
    function L(e) {
      if ("mouse" === e.pointerType && g.splice(0, g.length), !C(e)) return;
      var s = t.params.zoom;
      if (m = !1, h = !1, g.push(e), !(g.length < 2)) {
        if (m = !0, v.scaleStart = T(), !v.slideEl) {
          v.slideEl = e.target.closest(".".concat(t.params.slideClass, ", swiper-slide")), v.slideEl || (v.slideEl = t.slides[t.activeIndex]);
          var _a27 = v.slideEl.querySelector(".".concat(s.containerClass));
          if (_a27 && (_a27 = _a27.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), v.imageEl = _a27, v.imageWrapEl = _a27 ? E(v.imageEl, ".".concat(s.containerClass))[0] : void 0, !v.imageWrapEl) return void (v.imageEl = void 0);
          v.maxRatio = M();
        }
        if (v.imageEl) {
          var _ref3 = function () {
              if (g.length < 2) return {
                x: null,
                y: null
              };
              var e = v.imageEl.getBoundingClientRect();
              return [(g[0].pageX + (g[1].pageX - g[0].pageX) / 2 - e.x - n.scrollX) / l, (g[0].pageY + (g[1].pageY - g[0].pageY) / 2 - e.y - n.scrollY) / l];
            }(),
            _ref4 = _slicedToArray(_ref3, 2),
            _e58 = _ref4[0],
            _t44 = _ref4[1];
          v.originX = _e58, v.originY = _t44, v.imageEl.style.transitionDuration = "0ms";
        }
        o = !0;
      }
    }
    function I(e) {
      if (!C(e)) return;
      var s = t.params.zoom,
        a = t.zoom,
        i = g.findIndex(function (t) {
          return t.pointerId === e.pointerId;
        });
      i >= 0 && (g[i] = e), g.length < 2 || (h = !0, v.scaleMove = T(), v.imageEl && (a.scale = v.scaleMove / v.scaleStart * l, a.scale > v.maxRatio && (a.scale = v.maxRatio - 1 + Math.pow(a.scale - v.maxRatio + 1, .5)), a.scale < s.minRatio && (a.scale = s.minRatio + 1 - Math.pow(s.minRatio - a.scale + 1, .5)), v.imageEl.style.transform = "translate3d(0,0,0) scale(".concat(a.scale, ")")));
    }
    function z(e) {
      if (!C(e)) return;
      if ("mouse" === e.pointerType && "pointerout" === e.type) return;
      var s = t.params.zoom,
        a = t.zoom,
        i = g.findIndex(function (t) {
          return t.pointerId === e.pointerId;
        });
      i >= 0 && g.splice(i, 1), m && h && (m = !1, h = !1, v.imageEl && (a.scale = Math.max(Math.min(a.scale, v.maxRatio), s.minRatio), v.imageEl.style.transitionDuration = "".concat(t.params.speed, "ms"), v.imageEl.style.transform = "translate3d(0,0,0) scale(".concat(a.scale, ")"), l = a.scale, o = !1, a.scale > 1 && v.slideEl ? v.slideEl.classList.add("".concat(s.zoomedSlideClass)) : a.scale <= 1 && v.slideEl && v.slideEl.classList.remove("".concat(s.zoomedSlideClass)), 1 === a.scale && (v.originX = 0, v.originY = 0, v.slideEl = void 0)));
    }
    function A() {
      t.touchEventsData.preventTouchMoveFromPointerMove = !1;
    }
    function $(e) {
      var s = "mouse" === e.pointerType && t.params.zoom.panOnMouseMove;
      if (!C(e) || !P(e)) return;
      var a = t.zoom;
      if (!v.imageEl) return;
      if (!b.isTouched || !v.slideEl) return void (s && O(e));
      if (s) return void O(e);
      b.isMoved || (b.width = v.imageEl.offsetWidth || v.imageEl.clientWidth, b.height = v.imageEl.offsetHeight || v.imageEl.clientHeight, b.startX = d(v.imageWrapEl, "x") || 0, b.startY = d(v.imageWrapEl, "y") || 0, v.slideWidth = v.slideEl.offsetWidth, v.slideHeight = v.slideEl.offsetHeight, v.imageWrapEl.style.transitionDuration = "0ms");
      var i = b.width * a.scale,
        r = b.height * a.scale;
      b.minX = Math.min(v.slideWidth / 2 - i / 2, 0), b.maxX = -b.minX, b.minY = Math.min(v.slideHeight / 2 - r / 2, 0), b.maxY = -b.minY, b.touchesCurrent.x = g.length > 0 ? g[0].pageX : e.pageX, b.touchesCurrent.y = g.length > 0 ? g[0].pageY : e.pageY;
      if (Math.max(Math.abs(b.touchesCurrent.x - b.touchesStart.x), Math.abs(b.touchesCurrent.y - b.touchesStart.y)) > 5 && (t.allowClick = !1), !b.isMoved && !o) {
        if (t.isHorizontal() && (Math.floor(b.minX) === Math.floor(b.startX) && b.touchesCurrent.x < b.touchesStart.x || Math.floor(b.maxX) === Math.floor(b.startX) && b.touchesCurrent.x > b.touchesStart.x)) return b.isTouched = !1, void A();
        if (!t.isHorizontal() && (Math.floor(b.minY) === Math.floor(b.startY) && b.touchesCurrent.y < b.touchesStart.y || Math.floor(b.maxY) === Math.floor(b.startY) && b.touchesCurrent.y > b.touchesStart.y)) return b.isTouched = !1, void A();
      }
      e.cancelable && e.preventDefault(), e.stopPropagation(), clearTimeout(x), t.touchEventsData.preventTouchMoveFromPointerMove = !0, x = setTimeout(function () {
        t.destroyed || A();
      }), b.isMoved = !0;
      var n = (a.scale - l) / (v.maxRatio - t.params.zoom.minRatio),
        c = v.originX,
        p = v.originY;
      b.currentX = b.touchesCurrent.x - b.touchesStart.x + b.startX + n * (b.width - 2 * c), b.currentY = b.touchesCurrent.y - b.touchesStart.y + b.startY + n * (b.height - 2 * p), b.currentX < b.minX && (b.currentX = b.minX + 1 - Math.pow(b.minX - b.currentX + 1, .8)), b.currentX > b.maxX && (b.currentX = b.maxX - 1 + Math.pow(b.currentX - b.maxX + 1, .8)), b.currentY < b.minY && (b.currentY = b.minY + 1 - Math.pow(b.minY - b.currentY + 1, .8)), b.currentY > b.maxY && (b.currentY = b.maxY - 1 + Math.pow(b.currentY - b.maxY + 1, .8)), y.prevPositionX || (y.prevPositionX = b.touchesCurrent.x), y.prevPositionY || (y.prevPositionY = b.touchesCurrent.y), y.prevTime || (y.prevTime = Date.now()), y.x = (b.touchesCurrent.x - y.prevPositionX) / (Date.now() - y.prevTime) / 2, y.y = (b.touchesCurrent.y - y.prevPositionY) / (Date.now() - y.prevTime) / 2, Math.abs(b.touchesCurrent.x - y.prevPositionX) < 2 && (y.x = 0), Math.abs(b.touchesCurrent.y - y.prevPositionY) < 2 && (y.y = 0), y.prevPositionX = b.touchesCurrent.x, y.prevPositionY = b.touchesCurrent.y, y.prevTime = Date.now(), v.imageWrapEl.style.transform = "translate3d(".concat(b.currentX, "px, ").concat(b.currentY, "px,0)");
    }
    function k() {
      var e = t.zoom;
      v.slideEl && t.activeIndex !== t.slides.indexOf(v.slideEl) && (v.imageEl && (v.imageEl.style.transform = "translate3d(0,0,0) scale(1)"), v.imageWrapEl && (v.imageWrapEl.style.transform = "translate3d(0,0,0)"), v.slideEl.classList.remove("".concat(t.params.zoom.zoomedSlideClass)), e.scale = 1, l = 1, v.slideEl = void 0, v.imageEl = void 0, v.imageWrapEl = void 0, v.originX = 0, v.originY = 0);
    }
    function O(e) {
      if (l <= 1 || !v.imageWrapEl) return;
      if (!C(e) || !P(e)) return;
      var t = n.getComputedStyle(v.imageWrapEl).transform,
        s = new n.DOMMatrix(t);
      if (!c) return c = !0, p.x = e.clientX, p.y = e.clientY, b.startX = s.e, b.startY = s.f, b.width = v.imageEl.offsetWidth || v.imageEl.clientWidth, b.height = v.imageEl.offsetHeight || v.imageEl.clientHeight, v.slideWidth = v.slideEl.offsetWidth, void (v.slideHeight = v.slideEl.offsetHeight);
      var a = (e.clientX - p.x) * u,
        i = (e.clientY - p.y) * u,
        r = b.width * l,
        o = b.height * l,
        d = v.slideWidth,
        m = v.slideHeight,
        h = Math.min(d / 2 - r / 2, 0),
        f = -h,
        g = Math.min(m / 2 - o / 2, 0),
        w = -g,
        y = Math.max(Math.min(b.startX + a, f), h),
        E = Math.max(Math.min(b.startY + i, w), g);
      v.imageWrapEl.style.transitionDuration = "0ms", v.imageWrapEl.style.transform = "translate3d(".concat(y, "px, ").concat(E, "px, 0)"), p.x = e.clientX, p.y = e.clientY, b.startX = y, b.startY = E;
    }
    function D(e) {
      var s = t.zoom,
        a = t.params.zoom;
      if (!v.slideEl) {
        e && e.target && (v.slideEl = e.target.closest(".".concat(t.params.slideClass, ", swiper-slide"))), v.slideEl || (t.params.virtual && t.params.virtual.enabled && t.virtual ? v.slideEl = f(t.slidesEl, ".".concat(t.params.slideActiveClass))[0] : v.slideEl = t.slides[t.activeIndex]);
        var _s25 = v.slideEl.querySelector(".".concat(a.containerClass));
        _s25 && (_s25 = _s25.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), v.imageEl = _s25, v.imageWrapEl = _s25 ? E(v.imageEl, ".".concat(a.containerClass))[0] : void 0;
      }
      if (!v.imageEl || !v.imageWrapEl) return;
      var i, r, o, d, c, p, u, m, h, g, y, x, S, T, C, P, L, I;
      t.params.cssMode && (t.wrapperEl.style.overflow = "hidden", t.wrapperEl.style.touchAction = "none"), v.slideEl.classList.add("".concat(a.zoomedSlideClass)), void 0 === b.touchesStart.x && e ? (i = e.pageX, r = e.pageY) : (i = b.touchesStart.x, r = b.touchesStart.y);
      var z = "number" == typeof e ? e : null;
      1 === l && z && (i = void 0, r = void 0, b.touchesStart.x = void 0, b.touchesStart.y = void 0);
      var A = M();
      s.scale = z || A, l = z || A, !e || 1 === l && z ? (u = 0, m = 0) : (L = v.slideEl.offsetWidth, I = v.slideEl.offsetHeight, o = w(v.slideEl).left + n.scrollX, d = w(v.slideEl).top + n.scrollY, c = o + L / 2 - i, p = d + I / 2 - r, h = v.imageEl.offsetWidth || v.imageEl.clientWidth, g = v.imageEl.offsetHeight || v.imageEl.clientHeight, y = h * s.scale, x = g * s.scale, S = Math.min(L / 2 - y / 2, 0), T = Math.min(I / 2 - x / 2, 0), C = -S, P = -T, u = c * s.scale, m = p * s.scale, u < S && (u = S), u > C && (u = C), m < T && (m = T), m > P && (m = P)), z && 1 === s.scale && (v.originX = 0, v.originY = 0), v.imageWrapEl.style.transitionDuration = "300ms", v.imageWrapEl.style.transform = "translate3d(".concat(u, "px, ").concat(m, "px,0)"), v.imageEl.style.transitionDuration = "300ms", v.imageEl.style.transform = "translate3d(0,0,0) scale(".concat(s.scale, ")");
    }
    function G() {
      var e = t.zoom,
        s = t.params.zoom;
      if (!v.slideEl) {
        t.params.virtual && t.params.virtual.enabled && t.virtual ? v.slideEl = f(t.slidesEl, ".".concat(t.params.slideActiveClass))[0] : v.slideEl = t.slides[t.activeIndex];
        var _e59 = v.slideEl.querySelector(".".concat(s.containerClass));
        _e59 && (_e59 = _e59.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), v.imageEl = _e59, v.imageWrapEl = _e59 ? E(v.imageEl, ".".concat(s.containerClass))[0] : void 0;
      }
      v.imageEl && v.imageWrapEl && (t.params.cssMode && (t.wrapperEl.style.overflow = "", t.wrapperEl.style.touchAction = ""), e.scale = 1, l = 1, b.touchesStart.x = void 0, b.touchesStart.y = void 0, v.imageWrapEl.style.transitionDuration = "300ms", v.imageWrapEl.style.transform = "translate3d(0,0,0)", v.imageEl.style.transitionDuration = "300ms", v.imageEl.style.transform = "translate3d(0,0,0) scale(1)", v.slideEl.classList.remove("".concat(s.zoomedSlideClass)), v.slideEl = void 0, v.originX = 0, v.originY = 0, t.params.zoom.panOnMouseMove && (p = {
        x: 0,
        y: 0
      }, c && (c = !1, b.startX = 0, b.startY = 0)));
    }
    function H(e) {
      var s = t.zoom;
      s.scale && 1 !== s.scale ? G() : D(e);
    }
    function X() {
      return {
        passiveListener: !!t.params.passiveListeners && {
          passive: !0,
          capture: !1
        },
        activeListenerWithCapture: !t.params.passiveListeners || {
          passive: !1,
          capture: !0
        }
      };
    }
    function B() {
      var e = t.zoom;
      if (e.enabled) return;
      e.enabled = !0;
      var _X = X(),
        s = _X.passiveListener,
        a = _X.activeListenerWithCapture;
      t.wrapperEl.addEventListener("pointerdown", L, s), t.wrapperEl.addEventListener("pointermove", I, a), ["pointerup", "pointercancel", "pointerout"].forEach(function (e) {
        t.wrapperEl.addEventListener(e, z, s);
      }), t.wrapperEl.addEventListener("pointermove", $, a);
    }
    function Y() {
      var e = t.zoom;
      if (!e.enabled) return;
      e.enabled = !1;
      var _X2 = X(),
        s = _X2.passiveListener,
        a = _X2.activeListenerWithCapture;
      t.wrapperEl.removeEventListener("pointerdown", L, s), t.wrapperEl.removeEventListener("pointermove", I, a), ["pointerup", "pointercancel", "pointerout"].forEach(function (e) {
        t.wrapperEl.removeEventListener(e, z, s);
      }), t.wrapperEl.removeEventListener("pointermove", $, a);
    }
    Object.defineProperty(t.zoom, "scale", {
      get: function get() {
        return S;
      },
      set: function set(e) {
        if (S !== e) {
          var _t45 = v.imageEl,
            _s26 = v.slideEl;
          i("zoomChange", e, _t45, _s26);
        }
        S = e;
      }
    }), a("init", function () {
      t.params.zoom.enabled && B();
    }), a("destroy", function () {
      Y();
    }), a("touchStart", function (e, s) {
      t.zoom.enabled && function (e) {
        var s = t.device;
        if (!v.imageEl) return;
        if (b.isTouched) return;
        s.android && e.cancelable && e.preventDefault(), b.isTouched = !0;
        var a = g.length > 0 ? g[0] : e;
        b.touchesStart.x = a.pageX, b.touchesStart.y = a.pageY;
      }(s);
    }), a("touchEnd", function (e, s) {
      t.zoom.enabled && function () {
        var e = t.zoom;
        if (g.length = 0, !v.imageEl) return;
        if (!b.isTouched || !b.isMoved) return b.isTouched = !1, void (b.isMoved = !1);
        b.isTouched = !1, b.isMoved = !1;
        var s = 300,
          a = 300;
        var i = y.x * s,
          r = b.currentX + i,
          n = y.y * a,
          l = b.currentY + n;
        0 !== y.x && (s = Math.abs((r - b.currentX) / y.x)), 0 !== y.y && (a = Math.abs((l - b.currentY) / y.y));
        var o = Math.max(s, a);
        b.currentX = r, b.currentY = l;
        var d = b.width * e.scale,
          c = b.height * e.scale;
        b.minX = Math.min(v.slideWidth / 2 - d / 2, 0), b.maxX = -b.minX, b.minY = Math.min(v.slideHeight / 2 - c / 2, 0), b.maxY = -b.minY, b.currentX = Math.max(Math.min(b.currentX, b.maxX), b.minX), b.currentY = Math.max(Math.min(b.currentY, b.maxY), b.minY), v.imageWrapEl.style.transitionDuration = "".concat(o, "ms"), v.imageWrapEl.style.transform = "translate3d(".concat(b.currentX, "px, ").concat(b.currentY, "px,0)");
      }();
    }), a("doubleTap", function (e, s) {
      !t.animating && t.params.zoom.enabled && t.zoom.enabled && t.params.zoom.toggle && H(s);
    }), a("transitionEnd", function () {
      t.zoom.enabled && t.params.zoom.enabled && k();
    }), a("slideChange", function () {
      t.zoom.enabled && t.params.zoom.enabled && t.params.cssMode && k();
    }), Object.assign(t.zoom, {
      enable: B,
      disable: Y,
      "in": D,
      out: G,
      toggle: H
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    function i(e, t) {
      var s = function () {
        var e, t, s;
        return function (a, i) {
          for (t = -1, e = a.length; e - t > 1;) s = e + t >> 1, a[s] <= i ? t = s : e = s;
          return e;
        };
      }();
      var a, i;
      return this.x = e, this.y = t, this.lastIndex = e.length - 1, this.interpolate = function (e) {
        return e ? (i = s(this.x, e), a = i - 1, (e - this.x[a]) * (this.y[i] - this.y[a]) / (this.x[i] - this.x[a]) + this.y[a]) : 0;
      }, this;
    }
    function r() {
      t.controller.control && t.controller.spline && (t.controller.spline = void 0, delete t.controller.spline);
    }
    s({
      controller: {
        control: void 0,
        inverse: !1,
        by: "slide"
      }
    }), t.controller = {
      control: void 0
    }, a("beforeInit", function () {
      if ("undefined" != typeof window && ("string" == typeof t.params.controller.control || t.params.controller.control instanceof HTMLElement)) {
        ("string" == typeof t.params.controller.control ? _toConsumableArray(document.querySelectorAll(t.params.controller.control)) : [t.params.controller.control]).forEach(function (e) {
          if (t.controller.control || (t.controller.control = []), e && e.swiper) t.controller.control.push(e.swiper);else if (e) {
            var _s27 = "".concat(t.params.eventsPrefix, "init"),
              _a29 = function _a28(i) {
                t.controller.control.push(i.detail[0]), t.update(), e.removeEventListener(_s27, _a29);
              };
            e.addEventListener(_s27, _a29);
          }
        });
      } else t.controller.control = t.params.controller.control;
    }), a("update", function () {
      r();
    }), a("resize", function () {
      r();
    }), a("observerUpdate", function () {
      r();
    }), a("setTranslate", function (e, s, a) {
      t.controller.control && !t.controller.control.destroyed && t.controller.setTranslate(s, a);
    }), a("setTransition", function (e, s, a) {
      t.controller.control && !t.controller.control.destroyed && t.controller.setTransition(s, a);
    }), Object.assign(t.controller, {
      setTranslate: function setTranslate(e, s) {
        var a = t.controller.control;
        var r, n;
        var l = t.constructor;
        function o(e) {
          if (e.destroyed) return;
          var s = t.rtlTranslate ? -t.translate : t.translate;
          "slide" === t.params.controller.by && (!function (e) {
            t.controller.spline = t.params.loop ? new i(t.slidesGrid, e.slidesGrid) : new i(t.snapGrid, e.snapGrid);
          }(e), n = -t.controller.spline.interpolate(-s)), n && "container" !== t.params.controller.by || (r = (e.maxTranslate() - e.minTranslate()) / (t.maxTranslate() - t.minTranslate()), !Number.isNaN(r) && Number.isFinite(r) || (r = 1), n = (s - t.minTranslate()) * r + e.minTranslate()), t.params.controller.inverse && (n = e.maxTranslate() - n), e.updateProgress(n), e.setTranslate(n, t), e.updateActiveIndex(), e.updateSlidesClasses();
        }
        if (Array.isArray(a)) for (var _e60 = 0; _e60 < a.length; _e60 += 1) a[_e60] !== s && a[_e60] instanceof l && o(a[_e60]);else a instanceof l && s !== a && o(a);
      },
      setTransition: function setTransition(e, s) {
        var a = t.constructor,
          i = t.controller.control;
        var r;
        function n(s) {
          s.destroyed || (s.setTransition(e, t), 0 !== e && (s.transitionStart(), s.params.autoHeight && l(function () {
            s.updateAutoHeight();
          }), x(s.wrapperEl, function () {
            i && s.transitionEnd();
          })));
        }
        if (Array.isArray(i)) for (r = 0; r < i.length; r += 1) i[r] !== s && i[r] instanceof a && n(i[r]);else i instanceof a && s !== i && n(i);
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      i = e.on;
    s({
      a11y: {
        enabled: !0,
        notificationClass: "swiper-notification",
        prevSlideMessage: "Previous slide",
        nextSlideMessage: "Next slide",
        firstSlideMessage: "This is the first slide",
        lastSlideMessage: "This is the last slide",
        paginationBulletMessage: "Go to slide {{index}}",
        slideLabelMessage: "{{index}} / {{slidesLength}}",
        containerMessage: null,
        containerRoleDescriptionMessage: null,
        containerRole: null,
        itemRoleDescriptionMessage: null,
        slideRole: "group",
        id: null,
        scrollOnFocus: !0
      }
    }), t.a11y = {
      clicked: !1
    };
    var r,
      n,
      l = null,
      o = new Date().getTime();
    function d(e) {
      var t = l;
      0 !== t.length && (t.innerHTML = "", t.innerHTML = e);
    }
    function c(e) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("tabIndex", "0");
      });
    }
    function p(e) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("tabIndex", "-1");
      });
    }
    function u(e, t) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("role", t);
      });
    }
    function m(e, t) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("aria-roledescription", t);
      });
    }
    function h(e, t) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("aria-label", t);
      });
    }
    function f(e) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("aria-disabled", !0);
      });
    }
    function g(e) {
      (e = T(e)).forEach(function (e) {
        e.setAttribute("aria-disabled", !1);
      });
    }
    function w(e) {
      if (13 !== e.keyCode && 32 !== e.keyCode) return;
      var s = t.params.a11y,
        a = e.target;
      if (!t.pagination || !t.pagination.el || a !== t.pagination.el && !t.pagination.el.contains(e.target) || e.target.matches(ne(t.params.pagination.bulletClass))) {
        if (t.navigation && t.navigation.prevEl && t.navigation.nextEl) {
          var _e61 = T(t.navigation.prevEl);
          T(t.navigation.nextEl).includes(a) && (t.isEnd && !t.params.loop || t.slideNext(), t.isEnd ? d(s.lastSlideMessage) : d(s.nextSlideMessage)), _e61.includes(a) && (t.isBeginning && !t.params.loop || t.slidePrev(), t.isBeginning ? d(s.firstSlideMessage) : d(s.prevSlideMessage));
        }
        t.pagination && a.matches(ne(t.params.pagination.bulletClass)) && a.click();
      }
    }
    function b() {
      return t.pagination && t.pagination.bullets && t.pagination.bullets.length;
    }
    function E() {
      return b() && t.params.pagination.clickable;
    }
    var x = function x(e, t, s) {
        c(e), "BUTTON" !== e.tagName && (u(e, "button"), e.addEventListener("keydown", w)), h(e, s), function (e, t) {
          (e = T(e)).forEach(function (e) {
            e.setAttribute("aria-controls", t);
          });
        }(e, t);
      },
      S = function S(e) {
        n && n !== e.target && !n.contains(e.target) && (r = !0), t.a11y.clicked = !0;
      },
      M = function M() {
        r = !1, requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            t.destroyed || (t.a11y.clicked = !1);
          });
        });
      },
      C = function C(e) {
        o = new Date().getTime();
      },
      P = function P(e) {
        if (t.a11y.clicked || !t.params.a11y.scrollOnFocus) return;
        if (new Date().getTime() - o < 100) return;
        var s = e.target.closest(".".concat(t.params.slideClass, ", swiper-slide"));
        if (!s || !t.slides.includes(s)) return;
        n = s;
        var a = t.slides.indexOf(s) === t.activeIndex,
          i = t.params.watchSlidesProgress && t.visibleSlides && t.visibleSlides.includes(s);
        a || i || e.sourceCapabilities && e.sourceCapabilities.firesTouchEvents || (t.isHorizontal() ? t.el.scrollLeft = 0 : t.el.scrollTop = 0, requestAnimationFrame(function () {
          r || (t.params.loop ? t.slideToLoop(parseInt(s.getAttribute("data-swiper-slide-index")), 0) : t.slideTo(t.slides.indexOf(s), 0), r = !1);
        }));
      },
      L = function L() {
        var e = t.params.a11y;
        e.itemRoleDescriptionMessage && m(t.slides, e.itemRoleDescriptionMessage), e.slideRole && u(t.slides, e.slideRole);
        var s = t.slides.length;
        e.slideLabelMessage && t.slides.forEach(function (a, i) {
          var r = t.params.loop ? parseInt(a.getAttribute("data-swiper-slide-index"), 10) : i;
          h(a, e.slideLabelMessage.replace(/\{\{index\}\}/, r + 1).replace(/\{\{slidesLength\}\}/, s));
        });
      },
      I = function I() {
        var e = t.params.a11y;
        t.el.append(l);
        var s = t.el;
        e.containerRoleDescriptionMessage && m(s, e.containerRoleDescriptionMessage), e.containerMessage && h(s, e.containerMessage), e.containerRole && u(s, e.containerRole);
        var i = t.wrapperEl,
          r = e.id || i.getAttribute("id") || "swiper-wrapper-".concat((n = 16, void 0 === n && (n = 16), "x".repeat(n).replace(/x/g, function () {
            return Math.round(16 * Math.random()).toString(16);
          })));
        var n;
        var o = t.params.autoplay && t.params.autoplay.enabled ? "off" : "polite";
        var d;
        d = r, T(i).forEach(function (e) {
          e.setAttribute("id", d);
        }), function (e, t) {
          (e = T(e)).forEach(function (e) {
            e.setAttribute("aria-live", t);
          });
        }(i, o), L();
        var _ref5 = t.navigation ? t.navigation : {},
          c = _ref5.nextEl,
          p = _ref5.prevEl;
        if (c = T(c), p = T(p), c && c.forEach(function (t) {
          return x(t, r, e.nextSlideMessage);
        }), p && p.forEach(function (t) {
          return x(t, r, e.prevSlideMessage);
        }), E()) {
          T(t.pagination.el).forEach(function (e) {
            e.addEventListener("keydown", w);
          });
        }
        a().addEventListener("visibilitychange", C), t.el.addEventListener("focus", P, !0), t.el.addEventListener("focus", P, !0), t.el.addEventListener("pointerdown", S, !0), t.el.addEventListener("pointerup", M, !0);
      };
    i("beforeInit", function () {
      l = v("span", t.params.a11y.notificationClass), l.setAttribute("aria-live", "assertive"), l.setAttribute("aria-atomic", "true");
    }), i("afterInit", function () {
      t.params.a11y.enabled && I();
    }), i("slidesLengthChange snapGridLengthChange slidesGridLengthChange", function () {
      t.params.a11y.enabled && L();
    }), i("fromEdge toEdge afterInit lock unlock", function () {
      t.params.a11y.enabled && function () {
        if (t.params.loop || t.params.rewind || !t.navigation) return;
        var _t$navigation5 = t.navigation,
          e = _t$navigation5.nextEl,
          s = _t$navigation5.prevEl;
        s && (t.isBeginning ? (f(s), p(s)) : (g(s), c(s))), e && (t.isEnd ? (f(e), p(e)) : (g(e), c(e)));
      }();
    }), i("paginationUpdate", function () {
      t.params.a11y.enabled && function () {
        var e = t.params.a11y;
        b() && t.pagination.bullets.forEach(function (s) {
          t.params.pagination.clickable && (c(s), t.params.pagination.renderBullet || (u(s, "button"), h(s, e.paginationBulletMessage.replace(/\{\{index\}\}/, y(s) + 1)))), s.matches(ne(t.params.pagination.bulletActiveClass)) ? s.setAttribute("aria-current", "true") : s.removeAttribute("aria-current");
        });
      }();
    }), i("destroy", function () {
      t.params.a11y.enabled && function () {
        l && l.remove();
        var _ref6 = t.navigation ? t.navigation : {},
          e = _ref6.nextEl,
          s = _ref6.prevEl;
        e = T(e), s = T(s), e && e.forEach(function (e) {
          return e.removeEventListener("keydown", w);
        }), s && s.forEach(function (e) {
          return e.removeEventListener("keydown", w);
        }), E() && T(t.pagination.el).forEach(function (e) {
          e.removeEventListener("keydown", w);
        });
        a().removeEventListener("visibilitychange", C), t.el && "string" != typeof t.el && (t.el.removeEventListener("focus", P, !0), t.el.removeEventListener("pointerdown", S, !0), t.el.removeEventListener("pointerup", M, !0));
      }();
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      history: {
        enabled: !1,
        root: "",
        replaceState: !1,
        key: "slides",
        keepQuery: !1
      }
    });
    var i = !1,
      n = {};
    var l = function l(e) {
        return e.toString().replace(/\s+/g, "-").replace(/[^\w-]+/g, "").replace(/--+/g, "-").replace(/^-+/, "").replace(/-+$/, "");
      },
      o = function o(e) {
        var t = r();
        var s;
        s = e ? new URL(e) : t.location;
        var a = s.pathname.slice(1).split("/").filter(function (e) {
            return "" !== e;
          }),
          i = a.length;
        return {
          key: a[i - 2],
          value: a[i - 1]
        };
      },
      d = function d(e, s) {
        var a = r();
        if (!i || !t.params.history.enabled) return;
        var n;
        n = t.params.url ? new URL(t.params.url) : a.location;
        var o = t.virtual && t.params.virtual.enabled ? t.slidesEl.querySelector("[data-swiper-slide-index=\"".concat(s, "\"]")) : t.slides[s];
        var d = l(o.getAttribute("data-history"));
        if (t.params.history.root.length > 0) {
          var _s28 = t.params.history.root;
          "/" === _s28[_s28.length - 1] && (_s28 = _s28.slice(0, _s28.length - 1)), d = "".concat(_s28, "/").concat(e ? "".concat(e, "/") : "").concat(d);
        } else n.pathname.includes(e) || (d = "".concat(e ? "".concat(e, "/") : "").concat(d));
        t.params.history.keepQuery && (d += n.search);
        var c = a.history.state;
        c && c.value === d || (t.params.history.replaceState ? a.history.replaceState({
          value: d
        }, null, d) : a.history.pushState({
          value: d
        }, null, d));
      },
      c = function c(e, s, a) {
        if (s) for (var _i13 = 0, _r11 = t.slides.length; _i13 < _r11; _i13 += 1) {
          var _r12 = t.slides[_i13];
          if (l(_r12.getAttribute("data-history")) === s) {
            var _s29 = t.getSlideIndex(_r12);
            t.slideTo(_s29, e, a);
          }
        } else t.slideTo(0, e, a);
      },
      p = function p() {
        n = o(t.params.url), c(t.params.speed, n.value, !1);
      };
    a("init", function () {
      t.params.history.enabled && function () {
        var e = r();
        if (t.params.history) {
          if (!e.history || !e.history.pushState) return t.params.history.enabled = !1, void (t.params.hashNavigation.enabled = !0);
          i = !0, n = o(t.params.url), n.key || n.value ? (c(0, n.value, t.params.runCallbacksOnInit), t.params.history.replaceState || e.addEventListener("popstate", p)) : t.params.history.replaceState || e.addEventListener("popstate", p);
        }
      }();
    }), a("destroy", function () {
      t.params.history.enabled && function () {
        var e = r();
        t.params.history.replaceState || e.removeEventListener("popstate", p);
      }();
    }), a("transitionEnd _freeModeNoMomentumRelease", function () {
      i && d(t.params.history.key, t.activeIndex);
    }), a("slideChange", function () {
      i && t.params.cssMode && d(t.params.history.key, t.activeIndex);
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      i = e.emit,
      n = e.on,
      l = !1;
    var o = a(),
      d = r();
    s({
      hashNavigation: {
        enabled: !1,
        replaceState: !1,
        watchState: !1,
        getSlideIndex: function getSlideIndex(e, s) {
          if (t.virtual && t.params.virtual.enabled) {
            var _e62 = t.slides.find(function (e) {
              return e.getAttribute("data-hash") === s;
            });
            if (!_e62) return 0;
            return parseInt(_e62.getAttribute("data-swiper-slide-index"), 10);
          }
          return t.getSlideIndex(f(t.slidesEl, ".".concat(t.params.slideClass, "[data-hash=\"").concat(s, "\"], swiper-slide[data-hash=\"").concat(s, "\"]"))[0]);
        }
      }
    });
    var c = function c() {
        i("hashChange");
        var e = o.location.hash.replace("#", ""),
          s = t.virtual && t.params.virtual.enabled ? t.slidesEl.querySelector("[data-swiper-slide-index=\"".concat(t.activeIndex, "\"]")) : t.slides[t.activeIndex];
        if (e !== (s ? s.getAttribute("data-hash") : "")) {
          var _s30 = t.params.hashNavigation.getSlideIndex(t, e);
          if (void 0 === _s30 || Number.isNaN(_s30)) return;
          t.slideTo(_s30);
        }
      },
      p = function p() {
        if (!l || !t.params.hashNavigation.enabled) return;
        var e = t.virtual && t.params.virtual.enabled ? t.slidesEl.querySelector("[data-swiper-slide-index=\"".concat(t.activeIndex, "\"]")) : t.slides[t.activeIndex],
          s = e ? e.getAttribute("data-hash") || e.getAttribute("data-history") : "";
        t.params.hashNavigation.replaceState && d.history && d.history.replaceState ? (d.history.replaceState(null, null, "#".concat(s) || 0), i("hashSet")) : (o.location.hash = s || "", i("hashSet"));
      };
    n("init", function () {
      t.params.hashNavigation.enabled && function () {
        if (!t.params.hashNavigation.enabled || t.params.history && t.params.history.enabled) return;
        l = !0;
        var e = o.location.hash.replace("#", "");
        if (e) {
          var _s31 = 0,
            _a30 = t.params.hashNavigation.getSlideIndex(t, e);
          t.slideTo(_a30 || 0, _s31, t.params.runCallbacksOnInit, !0);
        }
        t.params.hashNavigation.watchState && d.addEventListener("hashchange", c);
      }();
    }), n("destroy", function () {
      t.params.hashNavigation.enabled && t.params.hashNavigation.watchState && d.removeEventListener("hashchange", c);
    }), n("transitionEnd _freeModeNoMomentumRelease", function () {
      l && p();
    }), n("slideChange", function () {
      l && t.params.cssMode && p();
    });
  }, function (e) {
    var t,
      s,
      i = e.swiper,
      r = e.extendParams,
      n = e.on,
      l = e.emit,
      o = e.params;
    i.autoplay = {
      running: !1,
      paused: !1,
      timeLeft: 0
    }, r({
      autoplay: {
        enabled: !1,
        delay: 3e3,
        waitForTransition: !0,
        disableOnInteraction: !1,
        stopOnLastSlide: !1,
        reverseDirection: !1,
        pauseOnMouseEnter: !1
      }
    });
    var d,
      c,
      p,
      u,
      m,
      h,
      f,
      g,
      v = o && o.autoplay ? o.autoplay.delay : 3e3,
      w = o && o.autoplay ? o.autoplay.delay : 3e3,
      b = new Date().getTime();
    function y(e) {
      i && !i.destroyed && i.wrapperEl && e.target === i.wrapperEl && (i.wrapperEl.removeEventListener("transitionend", y), g || e.detail && e.detail.bySwiperTouchMove || C());
    }
    var _E = function E() {
        if (i.destroyed || !i.autoplay.running) return;
        i.autoplay.paused ? c = !0 : c && (w = d, c = !1);
        var e = i.autoplay.paused ? d : b + w - new Date().getTime();
        i.autoplay.timeLeft = e, l("autoplayTimeLeft", e, e / v), s = requestAnimationFrame(function () {
          _E();
        });
      },
      _x = function x(e) {
        if (i.destroyed || !i.autoplay.running) return;
        cancelAnimationFrame(s), _E();
        var a = void 0 === e ? i.params.autoplay.delay : e;
        v = i.params.autoplay.delay, w = i.params.autoplay.delay;
        var r = function () {
          var e;
          if (e = i.virtual && i.params.virtual.enabled ? i.slides.find(function (e) {
            return e.classList.contains("swiper-slide-active");
          }) : i.slides[i.activeIndex], !e) return;
          return parseInt(e.getAttribute("data-swiper-autoplay"), 10);
        }();
        !Number.isNaN(r) && r > 0 && void 0 === e && (a = r, v = r, w = r), d = a;
        var n = i.params.speed,
          o = function o() {
            i && !i.destroyed && (i.params.autoplay.reverseDirection ? !i.isBeginning || i.params.loop || i.params.rewind ? (i.slidePrev(n, !0, !0), l("autoplay")) : i.params.autoplay.stopOnLastSlide || (i.slideTo(i.slides.length - 1, n, !0, !0), l("autoplay")) : !i.isEnd || i.params.loop || i.params.rewind ? (i.slideNext(n, !0, !0), l("autoplay")) : i.params.autoplay.stopOnLastSlide || (i.slideTo(0, n, !0, !0), l("autoplay")), i.params.cssMode && (b = new Date().getTime(), requestAnimationFrame(function () {
              _x();
            })));
          };
        return a > 0 ? (clearTimeout(t), t = setTimeout(function () {
          o();
        }, a)) : requestAnimationFrame(function () {
          o();
        }), a;
      },
      S = function S() {
        b = new Date().getTime(), i.autoplay.running = !0, _x(), l("autoplayStart");
      },
      T = function T() {
        i.autoplay.running = !1, clearTimeout(t), cancelAnimationFrame(s), l("autoplayStop");
      },
      M = function M(e, s) {
        if (i.destroyed || !i.autoplay.running) return;
        clearTimeout(t), e || (f = !0);
        var a = function a() {
          l("autoplayPause"), i.params.autoplay.waitForTransition ? i.wrapperEl.addEventListener("transitionend", y) : C();
        };
        if (i.autoplay.paused = !0, s) return h && (d = i.params.autoplay.delay), h = !1, void a();
        var r = d || i.params.autoplay.delay;
        d = r - (new Date().getTime() - b), i.isEnd && d < 0 && !i.params.loop || (d < 0 && (d = 0), a());
      },
      C = function C() {
        i.isEnd && d < 0 && !i.params.loop || i.destroyed || !i.autoplay.running || (b = new Date().getTime(), f ? (f = !1, _x(d)) : _x(), i.autoplay.paused = !1, l("autoplayResume"));
      },
      P = function P() {
        if (i.destroyed || !i.autoplay.running) return;
        var e = a();
        "hidden" === e.visibilityState && (f = !0, M(!0)), "visible" === e.visibilityState && C();
      },
      L = function L(e) {
        "mouse" === e.pointerType && (f = !0, g = !0, i.animating || i.autoplay.paused || M(!0));
      },
      I = function I(e) {
        "mouse" === e.pointerType && (g = !1, i.autoplay.paused && C());
      };
    n("init", function () {
      i.params.autoplay.enabled && (i.params.autoplay.pauseOnMouseEnter && (i.el.addEventListener("pointerenter", L), i.el.addEventListener("pointerleave", I)), a().addEventListener("visibilitychange", P), S());
    }), n("destroy", function () {
      i.el && "string" != typeof i.el && (i.el.removeEventListener("pointerenter", L), i.el.removeEventListener("pointerleave", I)), a().removeEventListener("visibilitychange", P), i.autoplay.running && T();
    }), n("_freeModeStaticRelease", function () {
      (u || f) && C();
    }), n("_freeModeNoMomentumRelease", function () {
      i.params.autoplay.disableOnInteraction ? T() : M(!0, !0);
    }), n("beforeTransitionStart", function (e, t, s) {
      !i.destroyed && i.autoplay.running && (s || !i.params.autoplay.disableOnInteraction ? M(!0, !0) : T());
    }), n("sliderFirstMove", function () {
      !i.destroyed && i.autoplay.running && (i.params.autoplay.disableOnInteraction ? T() : (p = !0, u = !1, f = !1, m = setTimeout(function () {
        f = !0, u = !0, M(!0);
      }, 200)));
    }), n("touchEnd", function () {
      if (!i.destroyed && i.autoplay.running && p) {
        if (clearTimeout(m), clearTimeout(t), i.params.autoplay.disableOnInteraction) return u = !1, void (p = !1);
        u && i.params.cssMode && C(), u = !1, p = !1;
      }
    }), n("slideChange", function () {
      !i.destroyed && i.autoplay.running && (h = !0);
    }), Object.assign(i.autoplay, {
      start: S,
      stop: T,
      pause: M,
      resume: C
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      i = e.on;
    s({
      thumbs: {
        swiper: null,
        multipleActiveThumbs: !0,
        autoScrollOffset: 0,
        slideThumbActiveClass: "swiper-slide-thumb-active",
        thumbsContainerClass: "swiper-thumbs"
      }
    });
    var r = !1,
      n = !1;
    function l() {
      var e = t.thumbs.swiper;
      if (!e || e.destroyed) return;
      var s = e.clickedIndex,
        a = e.clickedSlide;
      if (a && a.classList.contains(t.params.thumbs.slideThumbActiveClass)) return;
      if (null == s) return;
      var i;
      i = e.params.loop ? parseInt(e.clickedSlide.getAttribute("data-swiper-slide-index"), 10) : s, t.params.loop ? t.slideToLoop(i) : t.slideTo(i);
    }
    function o() {
      var e = t.params.thumbs;
      if (r) return !1;
      r = !0;
      var s = t.constructor;
      if (e.swiper instanceof s) t.thumbs.swiper = e.swiper, Object.assign(t.thumbs.swiper.originalParams, {
        watchSlidesProgress: !0,
        slideToClickedSlide: !1
      }), Object.assign(t.thumbs.swiper.params, {
        watchSlidesProgress: !0,
        slideToClickedSlide: !1
      }), t.thumbs.swiper.update();else if (c(e.swiper)) {
        var _a31 = Object.assign({}, e.swiper);
        Object.assign(_a31, {
          watchSlidesProgress: !0,
          slideToClickedSlide: !1
        }), t.thumbs.swiper = new s(_a31), n = !0;
      }
      return t.thumbs.swiper.el.classList.add(t.params.thumbs.thumbsContainerClass), t.thumbs.swiper.on("tap", l), !0;
    }
    function d(e) {
      var s = t.thumbs.swiper;
      if (!s || s.destroyed) return;
      var a = "auto" === s.params.slidesPerView ? s.slidesPerViewDynamic() : s.params.slidesPerView;
      var i = 1;
      var r = t.params.thumbs.slideThumbActiveClass;
      if (t.params.slidesPerView > 1 && !t.params.centeredSlides && (i = t.params.slidesPerView), t.params.thumbs.multipleActiveThumbs || (i = 1), i = Math.floor(i), s.slides.forEach(function (e) {
        return e.classList.remove(r);
      }), s.params.loop || s.params.virtual && s.params.virtual.enabled) for (var _e63 = 0; _e63 < i; _e63 += 1) f(s.slidesEl, "[data-swiper-slide-index=\"".concat(t.realIndex + _e63, "\"]")).forEach(function (e) {
        e.classList.add(r);
      });else for (var _e64 = 0; _e64 < i; _e64 += 1) s.slides[t.realIndex + _e64] && s.slides[t.realIndex + _e64].classList.add(r);
      var n = t.params.thumbs.autoScrollOffset,
        l = n && !s.params.loop;
      if (t.realIndex !== s.realIndex || l) {
        var _i14 = s.activeIndex;
        var _r13, _o7;
        if (s.params.loop) {
          var _e65 = s.slides.find(function (e) {
            return e.getAttribute("data-swiper-slide-index") === "".concat(t.realIndex);
          });
          _r13 = s.slides.indexOf(_e65), _o7 = t.activeIndex > t.previousIndex ? "next" : "prev";
        } else _r13 = t.realIndex, _o7 = _r13 > t.previousIndex ? "next" : "prev";
        l && (_r13 += "next" === _o7 ? n : -1 * n), s.visibleSlidesIndexes && s.visibleSlidesIndexes.indexOf(_r13) < 0 && (s.params.centeredSlides ? _r13 = _r13 > _i14 ? _r13 - Math.floor(a / 2) + 1 : _r13 + Math.floor(a / 2) - 1 : _r13 > _i14 && s.params.slidesPerGroup, s.slideTo(_r13, e ? 0 : void 0));
      }
    }
    t.thumbs = {
      swiper: null
    }, i("beforeInit", function () {
      var e = t.params.thumbs;
      if (e && e.swiper) if ("string" == typeof e.swiper || e.swiper instanceof HTMLElement) {
        var _s32 = a(),
          _i15 = function _i15() {
            var a = "string" == typeof e.swiper ? _s32.querySelector(e.swiper) : e.swiper;
            if (a && a.swiper) e.swiper = a.swiper, o(), d(!0);else if (a) {
              var _s33 = "".concat(t.params.eventsPrefix, "init"),
                _i17 = function _i16(r) {
                  e.swiper = r.detail[0], a.removeEventListener(_s33, _i17), o(), d(!0), e.swiper.update(), t.update();
                };
              a.addEventListener(_s33, _i17);
            }
            return a;
          },
          _r15 = function _r14() {
            if (t.destroyed) return;
            _i15() || requestAnimationFrame(_r15);
          };
        requestAnimationFrame(_r15);
      } else o(), d(!0);
    }), i("slideChange update resize observerUpdate", function () {
      d();
    }), i("setTransition", function (e, s) {
      var a = t.thumbs.swiper;
      a && !a.destroyed && a.setTransition(s);
    }), i("beforeDestroy", function () {
      var e = t.thumbs.swiper;
      e && !e.destroyed && n && e.destroy();
    }), Object.assign(t.thumbs, {
      init: o,
      update: d
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.emit,
      i = e.once;
    s({
      freeMode: {
        enabled: !1,
        momentum: !0,
        momentumRatio: 1,
        momentumBounce: !0,
        momentumBounceRatio: 1,
        momentumVelocityRatio: 1,
        sticky: !1,
        minimumVelocity: .02
      }
    }), Object.assign(t, {
      freeMode: {
        onTouchStart: function onTouchStart() {
          if (t.params.cssMode) return;
          var e = t.getTranslate();
          t.setTranslate(e), t.setTransition(0), t.touchEventsData.velocities.length = 0, t.freeMode.onTouchEnd({
            currentPos: t.rtl ? t.translate : -t.translate
          });
        },
        onTouchMove: function onTouchMove() {
          if (t.params.cssMode) return;
          var e = t.touchEventsData,
            s = t.touches;
          0 === e.velocities.length && e.velocities.push({
            position: s[t.isHorizontal() ? "startX" : "startY"],
            time: e.touchStartTime
          }), e.velocities.push({
            position: s[t.isHorizontal() ? "currentX" : "currentY"],
            time: o()
          });
        },
        onTouchEnd: function onTouchEnd(e) {
          var s = e.currentPos;
          if (t.params.cssMode) return;
          var r = t.params,
            n = t.wrapperEl,
            l = t.rtlTranslate,
            d = t.snapGrid,
            c = t.touchEventsData,
            p = o() - c.touchStartTime;
          if (s < -t.minTranslate()) t.slideTo(t.activeIndex);else if (s > -t.maxTranslate()) t.slides.length < d.length ? t.slideTo(d.length - 1) : t.slideTo(t.slides.length - 1);else {
            if (r.freeMode.momentum) {
              if (c.velocities.length > 1) {
                var _e66 = c.velocities.pop(),
                  _s34 = c.velocities.pop(),
                  _a32 = _e66.position - _s34.position,
                  _i18 = _e66.time - _s34.time;
                t.velocity = _a32 / _i18, t.velocity /= 2, Math.abs(t.velocity) < r.freeMode.minimumVelocity && (t.velocity = 0), (_i18 > 150 || o() - _e66.time > 300) && (t.velocity = 0);
              } else t.velocity = 0;
              t.velocity *= r.freeMode.momentumVelocityRatio, c.velocities.length = 0;
              var _e67 = 1e3 * r.freeMode.momentumRatio;
              var _s35 = t.velocity * _e67;
              var _p4 = t.translate + _s35;
              l && (_p4 = -_p4);
              var _u4,
                _m2 = !1;
              var _h2 = 20 * Math.abs(t.velocity) * r.freeMode.momentumBounceRatio;
              var _f2;
              if (_p4 < t.maxTranslate()) r.freeMode.momentumBounce ? (_p4 + t.maxTranslate() < -_h2 && (_p4 = t.maxTranslate() - _h2), _u4 = t.maxTranslate(), _m2 = !0, c.allowMomentumBounce = !0) : _p4 = t.maxTranslate(), r.loop && r.centeredSlides && (_f2 = !0);else if (_p4 > t.minTranslate()) r.freeMode.momentumBounce ? (_p4 - t.minTranslate() > _h2 && (_p4 = t.minTranslate() + _h2), _u4 = t.minTranslate(), _m2 = !0, c.allowMomentumBounce = !0) : _p4 = t.minTranslate(), r.loop && r.centeredSlides && (_f2 = !0);else if (r.freeMode.sticky) {
                var _e68;
                for (var _t46 = 0; _t46 < d.length; _t46 += 1) if (d[_t46] > -_p4) {
                  _e68 = _t46;
                  break;
                }
                _p4 = Math.abs(d[_e68] - _p4) < Math.abs(d[_e68 - 1] - _p4) || "next" === t.swipeDirection ? d[_e68] : d[_e68 - 1], _p4 = -_p4;
              }
              if (_f2 && i("transitionEnd", function () {
                t.loopFix();
              }), 0 !== t.velocity) {
                if (_e67 = l ? Math.abs((-_p4 - t.translate) / t.velocity) : Math.abs((_p4 - t.translate) / t.velocity), r.freeMode.sticky) {
                  var _s36 = Math.abs((l ? -_p4 : _p4) - t.translate),
                    _a33 = t.slidesSizesGrid[t.activeIndex];
                  _e67 = _s36 < _a33 ? r.speed : _s36 < 2 * _a33 ? 1.5 * r.speed : 2.5 * r.speed;
                }
              } else if (r.freeMode.sticky) return void t.slideToClosest();
              r.freeMode.momentumBounce && _m2 ? (t.updateProgress(_u4), t.setTransition(_e67), t.setTranslate(_p4), t.transitionStart(!0, t.swipeDirection), t.animating = !0, x(n, function () {
                t && !t.destroyed && c.allowMomentumBounce && (a("momentumBounce"), t.setTransition(r.speed), setTimeout(function () {
                  t.setTranslate(_u4), x(n, function () {
                    t && !t.destroyed && t.transitionEnd();
                  });
                }, 0));
              })) : t.velocity ? (a("_freeModeNoMomentumRelease"), t.updateProgress(_p4), t.setTransition(_e67), t.setTranslate(_p4), t.transitionStart(!0, t.swipeDirection), t.animating || (t.animating = !0, x(n, function () {
                t && !t.destroyed && t.transitionEnd();
              }))) : t.updateProgress(_p4), t.updateActiveIndex(), t.updateSlidesClasses();
            } else {
              if (r.freeMode.sticky) return void t.slideToClosest();
              r.freeMode && a("_freeModeNoMomentumRelease");
            }
            (!r.freeMode.momentum || p >= r.longSwipesMs) && (a("_freeModeStaticRelease"), t.updateProgress(), t.updateActiveIndex(), t.updateSlidesClasses());
          }
        }
      }
    });
  }, function (e) {
    var t,
      s,
      a,
      i,
      r = e.swiper,
      n = e.extendParams,
      l = e.on;
    n({
      grid: {
        rows: 1,
        fill: "column"
      }
    });
    var o = function o() {
      var e = r.params.spaceBetween;
      return "string" == typeof e && e.indexOf("%") >= 0 ? e = parseFloat(e.replace("%", "")) / 100 * r.size : "string" == typeof e && (e = parseFloat(e)), e;
    };
    l("init", function () {
      i = r.params.grid && r.params.grid.rows > 1;
    }), l("update", function () {
      var e = r.params,
        t = r.el,
        s = e.grid && e.grid.rows > 1;
      i && !s ? (t.classList.remove("".concat(e.containerModifierClass, "grid"), "".concat(e.containerModifierClass, "grid-column")), a = 1, r.emitContainerClasses()) : !i && s && (t.classList.add("".concat(e.containerModifierClass, "grid")), "column" === e.grid.fill && t.classList.add("".concat(e.containerModifierClass, "grid-column")), r.emitContainerClasses()), i = s;
    }), r.grid = {
      initSlides: function initSlides(e) {
        var i = r.params.slidesPerView,
          _r$params$grid = r.params.grid,
          n = _r$params$grid.rows,
          l = _r$params$grid.fill,
          o = r.virtual && r.params.virtual.enabled ? r.virtual.slides.length : e.length;
        a = Math.floor(o / n), t = Math.floor(o / n) === o / n ? o : Math.ceil(o / n) * n, "auto" !== i && "row" === l && (t = Math.max(t, i * n)), s = t / n;
      },
      unsetSlides: function unsetSlides() {
        r.slides && r.slides.forEach(function (e) {
          e.swiperSlideGridSet && (e.style.height = "", e.style[r.getDirectionLabel("margin-top")] = "");
        });
      },
      updateSlide: function updateSlide(e, i, n) {
        var l = r.params.slidesPerGroup,
          d = o(),
          _r$params$grid2 = r.params.grid,
          c = _r$params$grid2.rows,
          p = _r$params$grid2.fill,
          u = r.virtual && r.params.virtual.enabled ? r.virtual.slides.length : n.length;
        var m, h, f;
        if ("row" === p && l > 1) {
          var _s37 = Math.floor(e / (l * c)),
            _a34 = e - c * l * _s37,
            _r16 = 0 === _s37 ? l : Math.min(Math.ceil((u - _s37 * c * l) / c), l);
          f = Math.floor(_a34 / _r16), h = _a34 - f * _r16 + _s37 * l, m = h + f * t / c, i.style.order = m;
        } else "column" === p ? (h = Math.floor(e / c), f = e - h * c, (h > a || h === a && f === c - 1) && (f += 1, f >= c && (f = 0, h += 1))) : (f = Math.floor(e / s), h = e - f * s);
        i.row = f, i.column = h, i.style.height = "calc((100% - ".concat((c - 1) * d, "px) / ").concat(c, ")"), i.style[r.getDirectionLabel("margin-top")] = 0 !== f ? d && "".concat(d, "px") : "", i.swiperSlideGridSet = !0;
      },
      updateWrapperSize: function updateWrapperSize(e, s) {
        var _r$params = r.params,
          a = _r$params.centeredSlides,
          i = _r$params.roundLengths,
          n = o(),
          l = r.params.grid.rows;
        if (r.virtualSize = (e + n) * t, r.virtualSize = Math.ceil(r.virtualSize / l) - n, r.params.cssMode || (r.wrapperEl.style[r.getDirectionLabel("width")] = "".concat(r.virtualSize + n, "px")), a) {
          var _e69 = [];
          for (var _t47 = 0; _t47 < s.length; _t47 += 1) {
            var _a35 = s[_t47];
            i && (_a35 = Math.floor(_a35)), s[_t47] < r.virtualSize + s[0] && _e69.push(_a35);
          }
          s.splice(0, s.length), s.push.apply(s, _e69);
        }
      }
    };
  }, function (e) {
    var t = e.swiper;
    Object.assign(t, {
      appendSlide: le.bind(t),
      prependSlide: oe.bind(t),
      addSlide: de.bind(t),
      removeSlide: ce.bind(t),
      removeAllSlides: pe.bind(t)
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      fadeEffect: {
        crossFade: !1
      }
    }), ue({
      effect: "fade",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.slides;
        t.params.fadeEffect;
        for (var _s38 = 0; _s38 < e.length; _s38 += 1) {
          var _e70 = t.slides[_s38];
          var _a36 = -_e70.swiperSlideOffset;
          t.params.virtualTranslate || (_a36 -= t.translate);
          var _i19 = 0;
          t.isHorizontal() || (_i19 = _a36, _a36 = 0);
          var _r17 = t.params.fadeEffect.crossFade ? Math.max(1 - Math.abs(_e70.progress), 0) : 1 + Math.min(Math.max(_e70.progress, -1), 0),
            _n8 = me(0, _e70);
          _n8.style.opacity = _r17, _n8.style.transform = "translate3d(".concat(_a36, "px, ").concat(_i19, "px, 0px)");
        }
      },
      setTransition: function setTransition(e) {
        var s = t.slides.map(function (e) {
          return h(e);
        });
        s.forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms");
        }), he({
          swiper: t,
          duration: e,
          transformElements: s,
          allSlides: !0
        });
      },
      overwriteParams: function overwriteParams() {
        return {
          slidesPerView: 1,
          slidesPerGroup: 1,
          watchSlidesProgress: !0,
          spaceBetween: 0,
          virtualTranslate: !t.params.cssMode
        };
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      cubeEffect: {
        slideShadows: !0,
        shadow: !0,
        shadowOffset: 20,
        shadowScale: .94
      }
    });
    var i = function i(e, t, s) {
      var a = s ? e.querySelector(".swiper-slide-shadow-left") : e.querySelector(".swiper-slide-shadow-top"),
        i = s ? e.querySelector(".swiper-slide-shadow-right") : e.querySelector(".swiper-slide-shadow-bottom");
      a || (a = v("div", ("swiper-slide-shadow-cube swiper-slide-shadow-" + (s ? "left" : "top")).split(" ")), e.append(a)), i || (i = v("div", ("swiper-slide-shadow-cube swiper-slide-shadow-" + (s ? "right" : "bottom")).split(" ")), e.append(i)), a && (a.style.opacity = Math.max(-t, 0)), i && (i.style.opacity = Math.max(t, 0));
    };
    ue({
      effect: "cube",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.el,
          s = t.wrapperEl,
          a = t.slides,
          r = t.width,
          n = t.height,
          l = t.rtlTranslate,
          o = t.size,
          d = t.browser,
          c = M(t),
          p = t.params.cubeEffect,
          u = t.isHorizontal(),
          m = t.virtual && t.params.virtual.enabled;
        var h,
          f = 0;
        p.shadow && (u ? (h = t.wrapperEl.querySelector(".swiper-cube-shadow"), h || (h = v("div", "swiper-cube-shadow"), t.wrapperEl.append(h)), h.style.height = "".concat(r, "px")) : (h = e.querySelector(".swiper-cube-shadow"), h || (h = v("div", "swiper-cube-shadow"), e.append(h))));
        for (var _e71 = 0; _e71 < a.length; _e71 += 1) {
          var _t48 = a[_e71];
          var _s39 = _e71;
          m && (_s39 = parseInt(_t48.getAttribute("data-swiper-slide-index"), 10));
          var _r18 = 90 * _s39,
            _n9 = Math.floor(_r18 / 360);
          l && (_r18 = -_r18, _n9 = Math.floor(-_r18 / 360));
          var _d3 = Math.max(Math.min(_t48.progress, 1), -1);
          var _h3 = 0,
            _g = 0,
            _v = 0;
          _s39 % 4 == 0 ? (_h3 = 4 * -_n9 * o, _v = 0) : (_s39 - 1) % 4 == 0 ? (_h3 = 0, _v = 4 * -_n9 * o) : (_s39 - 2) % 4 == 0 ? (_h3 = o + 4 * _n9 * o, _v = o) : (_s39 - 3) % 4 == 0 && (_h3 = -o, _v = 3 * o + 4 * o * _n9), l && (_h3 = -_h3), u || (_g = _h3, _h3 = 0);
          var _w = "rotateX(".concat(c(u ? 0 : -_r18), "deg) rotateY(").concat(c(u ? _r18 : 0), "deg) translate3d(").concat(_h3, "px, ").concat(_g, "px, ").concat(_v, "px)");
          _d3 <= 1 && _d3 > -1 && (f = 90 * _s39 + 90 * _d3, l && (f = 90 * -_s39 - 90 * _d3)), _t48.style.transform = _w, p.slideShadows && i(_t48, _d3, u);
        }
        if (s.style.transformOrigin = "50% 50% -".concat(o / 2, "px"), s.style["-webkit-transform-origin"] = "50% 50% -".concat(o / 2, "px"), p.shadow) if (u) h.style.transform = "translate3d(0px, ".concat(r / 2 + p.shadowOffset, "px, ").concat(-r / 2, "px) rotateX(89.99deg) rotateZ(0deg) scale(").concat(p.shadowScale, ")");else {
          var _e72 = Math.abs(f) - 90 * Math.floor(Math.abs(f) / 90),
            _t49 = 1.5 - (Math.sin(2 * _e72 * Math.PI / 360) / 2 + Math.cos(2 * _e72 * Math.PI / 360) / 2),
            _s40 = p.shadowScale,
            _a37 = p.shadowScale / _t49,
            _i20 = p.shadowOffset;
          h.style.transform = "scale3d(".concat(_s40, ", 1, ").concat(_a37, ") translate3d(0px, ").concat(n / 2 + _i20, "px, ").concat(-n / 2 / _a37, "px) rotateX(-89.99deg)");
        }
        var g = (d.isSafari || d.isWebView) && d.needPerspectiveFix ? -o / 2 : 0;
        s.style.transform = "translate3d(0px,0,".concat(g, "px) rotateX(").concat(c(t.isHorizontal() ? 0 : f), "deg) rotateY(").concat(c(t.isHorizontal() ? -f : 0), "deg)"), s.style.setProperty("--swiper-cube-translate-z", "".concat(g, "px"));
      },
      setTransition: function setTransition(e) {
        var s = t.el,
          a = t.slides;
        if (a.forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms"), t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach(function (t) {
            t.style.transitionDuration = "".concat(e, "ms");
          });
        }), t.params.cubeEffect.shadow && !t.isHorizontal()) {
          var _t50 = s.querySelector(".swiper-cube-shadow");
          _t50 && (_t50.style.transitionDuration = "".concat(e, "ms"));
        }
      },
      recreateShadows: function recreateShadows() {
        var e = t.isHorizontal();
        t.slides.forEach(function (t) {
          var s = Math.max(Math.min(t.progress, 1), -1);
          i(t, s, e);
        });
      },
      getEffectParams: function getEffectParams() {
        return t.params.cubeEffect;
      },
      perspective: function perspective() {
        return !0;
      },
      overwriteParams: function overwriteParams() {
        return {
          slidesPerView: 1,
          slidesPerGroup: 1,
          watchSlidesProgress: !0,
          resistanceRatio: 0,
          spaceBetween: 0,
          centeredSlides: !1,
          virtualTranslate: !0
        };
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      flipEffect: {
        slideShadows: !0,
        limitRotation: !0
      }
    });
    var i = function i(e, s) {
      var a = t.isHorizontal() ? e.querySelector(".swiper-slide-shadow-left") : e.querySelector(".swiper-slide-shadow-top"),
        i = t.isHorizontal() ? e.querySelector(".swiper-slide-shadow-right") : e.querySelector(".swiper-slide-shadow-bottom");
      a || (a = fe("flip", e, t.isHorizontal() ? "left" : "top")), i || (i = fe("flip", e, t.isHorizontal() ? "right" : "bottom")), a && (a.style.opacity = Math.max(-s, 0)), i && (i.style.opacity = Math.max(s, 0));
    };
    ue({
      effect: "flip",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.slides,
          s = t.rtlTranslate,
          a = t.params.flipEffect,
          r = M(t);
        for (var _n10 = 0; _n10 < e.length; _n10 += 1) {
          var _l8 = e[_n10];
          var _o8 = _l8.progress;
          t.params.flipEffect.limitRotation && (_o8 = Math.max(Math.min(_l8.progress, 1), -1));
          var _d4 = _l8.swiperSlideOffset;
          var _c3 = -180 * _o8,
            _p5 = 0,
            _u5 = t.params.cssMode ? -_d4 - t.translate : -_d4,
            _m3 = 0;
          t.isHorizontal() ? s && (_c3 = -_c3) : (_m3 = _u5, _u5 = 0, _p5 = -_c3, _c3 = 0), _l8.style.zIndex = -Math.abs(Math.round(_o8)) + e.length, a.slideShadows && i(_l8, _o8);
          var _h4 = "translate3d(".concat(_u5, "px, ").concat(_m3, "px, 0px) rotateX(").concat(r(_p5), "deg) rotateY(").concat(r(_c3), "deg)");
          me(0, _l8).style.transform = _h4;
        }
      },
      setTransition: function setTransition(e) {
        var s = t.slides.map(function (e) {
          return h(e);
        });
        s.forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms"), t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach(function (t) {
            t.style.transitionDuration = "".concat(e, "ms");
          });
        }), he({
          swiper: t,
          duration: e,
          transformElements: s
        });
      },
      recreateShadows: function recreateShadows() {
        t.params.flipEffect, t.slides.forEach(function (e) {
          var s = e.progress;
          t.params.flipEffect.limitRotation && (s = Math.max(Math.min(e.progress, 1), -1)), i(e, s);
        });
      },
      getEffectParams: function getEffectParams() {
        return t.params.flipEffect;
      },
      perspective: function perspective() {
        return !0;
      },
      overwriteParams: function overwriteParams() {
        return {
          slidesPerView: 1,
          slidesPerGroup: 1,
          watchSlidesProgress: !0,
          spaceBetween: 0,
          virtualTranslate: !t.params.cssMode
        };
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        scale: 1,
        modifier: 1,
        slideShadows: !0
      }
    }), ue({
      effect: "coverflow",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.width,
          s = t.height,
          a = t.slides,
          i = t.slidesSizesGrid,
          r = t.params.coverflowEffect,
          n = t.isHorizontal(),
          l = t.translate,
          o = n ? e / 2 - l : s / 2 - l,
          d = n ? r.rotate : -r.rotate,
          c = r.depth,
          p = M(t);
        for (var _e73 = 0, _t51 = a.length; _e73 < _t51; _e73 += 1) {
          var _t52 = a[_e73],
            _s41 = i[_e73],
            _l9 = (o - _t52.swiperSlideOffset - _s41 / 2) / _s41,
            _u6 = "function" == typeof r.modifier ? r.modifier(_l9) : _l9 * r.modifier;
          var _m4 = n ? d * _u6 : 0,
            _h5 = n ? 0 : d * _u6,
            _f3 = -c * Math.abs(_u6),
            _g2 = r.stretch;
          "string" == typeof _g2 && -1 !== _g2.indexOf("%") && (_g2 = parseFloat(r.stretch) / 100 * _s41);
          var _v2 = n ? 0 : _g2 * _u6,
            _w2 = n ? _g2 * _u6 : 0,
            _b = 1 - (1 - r.scale) * Math.abs(_u6);
          Math.abs(_w2) < .001 && (_w2 = 0), Math.abs(_v2) < .001 && (_v2 = 0), Math.abs(_f3) < .001 && (_f3 = 0), Math.abs(_m4) < .001 && (_m4 = 0), Math.abs(_h5) < .001 && (_h5 = 0), Math.abs(_b) < .001 && (_b = 0);
          var _y = "translate3d(".concat(_w2, "px,").concat(_v2, "px,").concat(_f3, "px)  rotateX(").concat(p(_h5), "deg) rotateY(").concat(p(_m4), "deg) scale(").concat(_b, ")");
          if (me(0, _t52).style.transform = _y, _t52.style.zIndex = 1 - Math.abs(Math.round(_u6)), r.slideShadows) {
            var _e74 = n ? _t52.querySelector(".swiper-slide-shadow-left") : _t52.querySelector(".swiper-slide-shadow-top"),
              _s42 = n ? _t52.querySelector(".swiper-slide-shadow-right") : _t52.querySelector(".swiper-slide-shadow-bottom");
            _e74 || (_e74 = fe("coverflow", _t52, n ? "left" : "top")), _s42 || (_s42 = fe("coverflow", _t52, n ? "right" : "bottom")), _e74 && (_e74.style.opacity = _u6 > 0 ? _u6 : 0), _s42 && (_s42.style.opacity = -_u6 > 0 ? -_u6 : 0);
          }
        }
      },
      setTransition: function setTransition(e) {
        t.slides.map(function (e) {
          return h(e);
        }).forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms"), t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach(function (t) {
            t.style.transitionDuration = "".concat(e, "ms");
          });
        });
      },
      perspective: function perspective() {
        return !0;
      },
      overwriteParams: function overwriteParams() {
        return {
          watchSlidesProgress: !0
        };
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      creativeEffect: {
        limitProgress: 1,
        shadowPerProgress: !1,
        progressMultiplier: 1,
        perspective: !0,
        prev: {
          translate: [0, 0, 0],
          rotate: [0, 0, 0],
          opacity: 1,
          scale: 1
        },
        next: {
          translate: [0, 0, 0],
          rotate: [0, 0, 0],
          opacity: 1,
          scale: 1
        }
      }
    });
    var i = function i(e) {
      return "string" == typeof e ? e : "".concat(e, "px");
    };
    ue({
      effect: "creative",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.slides,
          s = t.wrapperEl,
          a = t.slidesSizesGrid,
          r = t.params.creativeEffect,
          n = r.progressMultiplier,
          l = t.params.centeredSlides,
          o = M(t);
        if (l) {
          var _e75 = a[0] / 2 - t.params.slidesOffsetBefore || 0;
          s.style.transform = "translateX(calc(50% - ".concat(_e75, "px))");
        }
        var _loop3 = function _loop3() {
          var a = e[_s43],
            d = a.progress,
            c = Math.min(Math.max(a.progress, -r.limitProgress), r.limitProgress);
          var p = c;
          l || (p = Math.min(Math.max(a.originalProgress, -r.limitProgress), r.limitProgress));
          var u = a.swiperSlideOffset,
            m = [t.params.cssMode ? -u - t.translate : -u, 0, 0],
            h = [0, 0, 0];
          var f = !1;
          t.isHorizontal() || (m[1] = m[0], m[0] = 0);
          var g = {
            translate: [0, 0, 0],
            rotate: [0, 0, 0],
            scale: 1,
            opacity: 1
          };
          c < 0 ? (g = r.next, f = !0) : c > 0 && (g = r.prev, f = !0), m.forEach(function (e, t) {
            m[t] = "calc(".concat(e, "px + (").concat(i(g.translate[t]), " * ").concat(Math.abs(c * n), "))");
          }), h.forEach(function (e, t) {
            var s = g.rotate[t] * Math.abs(c * n);
            h[t] = s;
          }), a.style.zIndex = -Math.abs(Math.round(d)) + e.length;
          var v = m.join(", "),
            w = "rotateX(".concat(o(h[0]), "deg) rotateY(").concat(o(h[1]), "deg) rotateZ(").concat(o(h[2]), "deg)"),
            b = p < 0 ? "scale(".concat(1 + (1 - g.scale) * p * n, ")") : "scale(".concat(1 - (1 - g.scale) * p * n, ")"),
            y = p < 0 ? 1 + (1 - g.opacity) * p * n : 1 - (1 - g.opacity) * p * n,
            E = "translate3d(".concat(v, ") ").concat(w, " ").concat(b);
          if (f && g.shadow || !f) {
            var _e76 = a.querySelector(".swiper-slide-shadow");
            if (!_e76 && g.shadow && (_e76 = fe("creative", a)), _e76) {
              var _t53 = r.shadowPerProgress ? c * (1 / r.limitProgress) : c;
              _e76.style.opacity = Math.min(Math.max(Math.abs(_t53), 0), 1);
            }
          }
          var x = me(0, a);
          x.style.transform = E, x.style.opacity = y, g.origin && (x.style.transformOrigin = g.origin);
        };
        for (var _s43 = 0; _s43 < e.length; _s43 += 1) {
          _loop3();
        }
      },
      setTransition: function setTransition(e) {
        var s = t.slides.map(function (e) {
          return h(e);
        });
        s.forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms"), t.querySelectorAll(".swiper-slide-shadow").forEach(function (t) {
            t.style.transitionDuration = "".concat(e, "ms");
          });
        }), he({
          swiper: t,
          duration: e,
          transformElements: s,
          allSlides: !0
        });
      },
      perspective: function perspective() {
        return t.params.creativeEffect.perspective;
      },
      overwriteParams: function overwriteParams() {
        return {
          watchSlidesProgress: !0,
          virtualTranslate: !t.params.cssMode
        };
      }
    });
  }, function (e) {
    var t = e.swiper,
      s = e.extendParams,
      a = e.on;
    s({
      cardsEffect: {
        slideShadows: !0,
        rotate: !0,
        perSlideRotate: 2,
        perSlideOffset: 8
      }
    }), ue({
      effect: "cards",
      swiper: t,
      on: a,
      setTranslate: function setTranslate() {
        var e = t.slides,
          s = t.activeIndex,
          a = t.rtlTranslate,
          i = t.params.cardsEffect,
          _t$touchEventsData = t.touchEventsData,
          r = _t$touchEventsData.startTranslate,
          n = _t$touchEventsData.isTouched,
          l = a ? -t.translate : t.translate;
        for (var _o9 = 0; _o9 < e.length; _o9 += 1) {
          var _d5 = e[_o9],
            _c4 = _d5.progress,
            _p6 = Math.min(Math.max(_c4, -4), 4);
          var _u7 = _d5.swiperSlideOffset;
          t.params.centeredSlides && !t.params.cssMode && (t.wrapperEl.style.transform = "translateX(".concat(t.minTranslate(), "px)")), t.params.centeredSlides && t.params.cssMode && (_u7 -= e[0].swiperSlideOffset);
          var _m5 = t.params.cssMode ? -_u7 - t.translate : -_u7,
            _h6 = 0;
          var _f4 = -100 * Math.abs(_p6);
          var _g3 = 1,
            _v3 = -i.perSlideRotate * _p6,
            _w3 = i.perSlideOffset - .75 * Math.abs(_p6);
          var _b2 = t.virtual && t.params.virtual.enabled ? t.virtual.from + _o9 : _o9,
            _y2 = (_b2 === s || _b2 === s - 1) && _p6 > 0 && _p6 < 1 && (n || t.params.cssMode) && l < r,
            _E2 = (_b2 === s || _b2 === s + 1) && _p6 < 0 && _p6 > -1 && (n || t.params.cssMode) && l > r;
          if (_y2 || _E2) {
            var _e77 = Math.pow(1 - Math.abs((Math.abs(_p6) - .5) / .5), .5);
            _v3 += -28 * _p6 * _e77, _g3 += -.5 * _e77, _w3 += 96 * _e77, _h6 = -25 * _e77 * Math.abs(_p6) + "%";
          }
          if (_m5 = _p6 < 0 ? "calc(".concat(_m5, "px ").concat(a ? "-" : "+", " (").concat(_w3 * Math.abs(_p6), "%))") : _p6 > 0 ? "calc(".concat(_m5, "px ").concat(a ? "-" : "+", " (-").concat(_w3 * Math.abs(_p6), "%))") : "".concat(_m5, "px"), !t.isHorizontal()) {
            var _e78 = _h6;
            _h6 = _m5, _m5 = _e78;
          }
          var _x2 = _p6 < 0 ? "" + (1 + (1 - _g3) * _p6) : "" + (1 - (1 - _g3) * _p6),
            _S = "\n        translate3d(".concat(_m5, ", ").concat(_h6, ", ").concat(_f4, "px)\n        rotateZ(").concat(i.rotate ? a ? -_v3 : _v3 : 0, "deg)\n        scale(").concat(_x2, ")\n      ");
          if (i.slideShadows) {
            var _e79 = _d5.querySelector(".swiper-slide-shadow");
            _e79 || (_e79 = fe("cards", _d5)), _e79 && (_e79.style.opacity = Math.min(Math.max((Math.abs(_p6) - .5) / .5, 0), 1));
          }
          _d5.style.zIndex = -Math.abs(Math.round(_c4)) + e.length;
          me(0, _d5).style.transform = _S;
        }
      },
      setTransition: function setTransition(e) {
        var s = t.slides.map(function (e) {
          return h(e);
        });
        s.forEach(function (t) {
          t.style.transitionDuration = "".concat(e, "ms"), t.querySelectorAll(".swiper-slide-shadow").forEach(function (t) {
            t.style.transitionDuration = "".concat(e, "ms");
          });
        }), he({
          swiper: t,
          duration: e,
          transformElements: s
        });
      },
      perspective: function perspective() {
        return !0;
      },
      overwriteParams: function overwriteParams() {
        return {
          _loopSwapReset: !1,
          watchSlidesProgress: !0,
          loopAdditionalSlides: 3,
          centeredSlides: !0,
          virtualTranslate: !t.params.cssMode
        };
      }
    });
  }];
  return ie.use(ge), ie;
}();

/***/ }),

/***/ "./assets/src/js/swiper.js":
/*!*********************************!*\
  !*** ./assets/src/js/swiper.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initializeSwipers: () => (/* binding */ initializeSwipers),
/* harmony export */   reinitializeAllSwipers: () => (/* binding */ reinitializeAllSwipers),
/* harmony export */   sliderConfigs: () => (/* binding */ sliderConfigs)
/* harmony export */ });


var sliderConfigs = {
  ".zwssgr-slider-1": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 3,
        slidesPerGroup: 3
      },
      768: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-2": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 3,
        slidesPerGroup: 3
      },
      768: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-3": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-4": {
    slidesPerView: 1,
    slidesPerGroup: 1
  },
  ".zwssgr-slider-5": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-6": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 3,
        slidesPerGroup: 3
      },
      768: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-7": {
    slidesPerView: 1,
    slidesPerGroup: 1,
    breakpoints: {
      1200: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      480: {
        slidesPerView: 1,
        slidesPerGroup: 1
      }
    }
  },
  ".zwssgr-slider-8": {
    slidesPerView: 1,
    slidesPerGroup: 1
  }
};

// Store Swiper instances
var swiperInstances = {};
function initializeSwipers() {
  Object.keys(sliderConfigs).forEach(function (selector) {
    var sliderElements = document.querySelectorAll(selector);
    if (sliderElements.length > 0) {
      var parentElement = sliderElements[0].parentElement;
      sliderElements.forEach(function (sliderElement) {
        var slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
        var config = sliderConfigs[selector];
        var minSlidesRequired = (config.slidesPerView || 1) + 1;
        var enableLoop = slideCount >= minSlidesRequired;
        swiperInstances[selector] = new Swiper(sliderElement, {
          slidesPerView: config.slidesPerView,
          slidesPerGroup: config.slidesPerGroup,
          spaceBetween: 20,
          loop: enableLoop,
          navigation: {
            nextEl: parentElement.querySelector(".swiper-button-next"),
            prevEl: parentElement.querySelector(".swiper-button-prev")
          },
          breakpoints: config.breakpoints || {}
        });
      });
    }
  });
}
function reinitializeAllSwipers(container) {
  if (!(container instanceof HTMLElement)) {
    console.error("Invalid container element!", container);
    return;
  }
  Object.keys(sliderConfigs).forEach(function (selector) {
    var sliderElements = container.querySelectorAll(selector);
    sliderElements.forEach(function (sliderElement) {
      var slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
      var config = sliderConfigs[selector];
      var minSlidesRequired = (config.slidesPerView || 1) + 1;
      var enableLoop = slideCount >= minSlidesRequired;
      if (swiperInstances[selector]) {
        swiperInstances[selector].destroy(true, true);
      }
      swiperInstances[selector] = new Swiper(sliderElement, {
        slidesPerView: config.slidesPerView,
        slidesPerGroup: config.slidesPerGroup,
        spaceBetween: 20,
        loop: enableLoop,
        navigation: {
          nextEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-next"),
          prevEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-prev")
        },
        breakpoints: config.breakpoints || {}
      });
    });
  });
}

// Export functions and sliderConfigs

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  initializeSwipers();
});

/***/ }),

/***/ "./assets/src/js/tabbing.js":
/*!**********************************!*\
  !*** ./assets/src/js/tabbing.js ***!
  \**********************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  // Select Layout option functionality
  var radioButtons = document.querySelectorAll('input[name="display_option"]');
  var currentDisplayOption = 'all';

  // Add event listeners to radio buttons for dynamic filtering
  radioButtons.forEach(function (button) {
    button.addEventListener('change', function () {
      currentDisplayOption = this.value;
      updateOptions(currentDisplayOption);
      saveSelectedOption(currentDisplayOption); // Save the selected display option
    });
  });

  // Function to save the selected display option and layout option via AJAX
  function saveSelectedOption(option) {
    var _document$querySelect;
    var postId = getQueryParam('zwssgr_widget_id');
    var settings = (_document$querySelect = document.querySelector('.tab-item.active')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('data-tab');
    var selectedLayout = null; // Initialize selectedLayout as null

    // Select all elements with class 'zwssgr-option-item'
    var optionItems = document.querySelectorAll('.zwssgr-option-item');
    optionItems.forEach(function (optionItem) {
      // Check if the element is visible
      if (optionItem.offsetParent !== null) {
        // offsetParent is null for hidden elements
        var selectedButton = optionItem.querySelector('.select-btn.selected');
        if (selectedButton) {
          selectedLayout = selectedButton.dataset.option;
        }
      }
    });

    // Ensure selectedLayout has a valid value before sending the requests
    if (!selectedLayout) {
      // console.error("No layout option selected.");
      return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxurl, true); // Use asynchronous request for better performance
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var params = new URLSearchParams({
      action: 'zwssgr_save_widget_data',
      security: my_widget.nonce,
      display_option: option,
      layout_option: selectedLayout,
      post_id: postId,
      settings: settings
    });
    xhr.onload = function () {
      if (xhr.status === 200) {
        // console.log('Display and layout option saved:', xhr.responseText);
      } else {
        console.error('Error saving options:', xhr.statusText);
      }
    };
    xhr.onerror = function () {
      console.error('Request failed');
    };
    xhr.send(params.toString());
  }

  // Function to show/hide options based on the selected radio button
  function updateOptions(value) {
    document.querySelectorAll('.zwssgr-option-item').forEach(function (item) {
      if (value === 'all' || item.dataset.type === value) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  }

  // Function to get query parameter by name
  function getQueryParam(param) {
    var urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  // Set active tab and selected option from the URL
  window.zwssgrActiveTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
  window.zwssgrSelectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL

  // Hide all tab content
  document.querySelectorAll('.tab-content').forEach(function (tab) {
    tab.style.display = 'none';
  });

  // Show the active tab content
  var activeTabElement = document.getElementById(window.zwssgrActiveTab);
  if (activeTabElement) {
    activeTabElement.style.display = 'block';
  }

  // Remove 'active' class from all tab items
  document.querySelectorAll('.tab-item').forEach(function (tabItem) {
    tabItem.classList.remove('active');
  });

  // Add 'active' class to the selected tab item
  var activeTabItem = document.querySelector('.tab-item[data-tab="' + window.zwssgrActiveTab + '"]');
  if (activeTabItem) {
    activeTabItem.classList.add('active');
  }

  // If there's a selected option in the URL and the active tab is 'tab-selected'
  if (window.zwssgrSelectedOption && window.zwssgrActiveTab === 'tab-selected') {
    var selectedOptionElement = document.getElementById(window.zwssgrSelectedOption);
    var selectedOptionDisplay = document.getElementById('selected-option-display');
    if (selectedOptionElement && selectedOptionDisplay) {
      selectedOptionDisplay.innerHTML = ''; // Clear previous content
      selectedOptionDisplay.appendChild(selectedOptionElement); // Move the selected option

      // Remove the select button from the moved element
      var selectBtn = selectedOptionDisplay.querySelector('.select-btn');
      if (selectBtn) {
        selectBtn.remove();
      }
    }
  }

  // Handle click events for the tab navigation items
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('tab-item')) {
      var tabId = event.target.getAttribute('data-tab');
      var currentUrl = window.location.href.split('?')[0]; // Get the base URL

      // Get existing query parameters
      var selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists
      var postId = getQueryParam('zwssgr_widget_id'); // Get the post_id from the URL if it exists

      // Start building the new URL with page and tab parameters
      var newUrl = "".concat(currentUrl, "?page=zwssgr_widget_configurator&tab=").concat(tabId);

      // Add selectedOption to the URL if it exists
      if (selectedOption) {
        newUrl += "&selectedOption=".concat(selectedOption);
      }

      // Add post_id to the URL if it exists
      if (postId) {
        newUrl += "&zwssgr_widget_id=".concat(postId);
      }

      // Redirect to the new URL
      window.location.href = newUrl;
    }
  });
});

/***/ }),

/***/ "./assets/src/js/toogle-btn.js":
/*!*************************************!*\
  !*** ./assets/src/js/toogle-btn.js ***!
  \*************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var enableLoadMore = document.getElementById('enable-load-more');
  var loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');
  if (enableLoadMore && loadMoreOptions) {
    // Ensure elements exist
    if (enableLoadMore.checked) {
      loadMoreOptions.style.display = 'flex';
    } else {
      loadMoreOptions.style.display = 'none';
    }
  }
});

/***/ }),

/***/ "./assets/src/js/update-shortcode.js":
/*!*******************************************!*\
  !*** ./assets/src/js/update-shortcode.js ***!
  \*******************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  // Ensure the input element exists before adding the event listener
  var zwssgrInput = document.querySelector(".zwssgr-shortcode-input");
  if (!zwssgrInput) {
    // console.error("Element with class 'zwssgr-shortcode-input' not found.");
    return; // Exit script if element is not found
  }
  zwssgrInput.addEventListener("input", function () {
    var zwssgrinputValue = this.value.trim(); // Get input value
    var zwssgrsuccessDiv = document.getElementById("zwssgr-success-response");
    var zwssgrerrorDiv = document.getElementById("zwssgr-error-response");

    // Regular expressions to extract values
    var zwssgrpostIdMatch = zwssgrinputValue.match(/post-id=["'](\d+)["']/);
    var zwssgrlayoutMatch = zwssgrinputValue.match(/layout=["']([\w-]+)["']/);
    var zwssgrlayoutOptionMatch = zwssgrinputValue.match(/layout-option=["']([\w-]+)["']/);

    // Extract values or set defaults
    var zwssgrpostId = zwssgrpostIdMatch ? zwssgrpostIdMatch[1] : '';
    var zwssgrlayout = zwssgrlayoutMatch ? zwssgrlayoutMatch[1] : '';
    var zwssgrlayoutOption = zwssgrlayoutOptionMatch ? zwssgrlayoutOptionMatch[1] : '';

    // Define allowed layout-option pairs
    var zwssgrvalidLayouts = {
      "all": ["slider-1", "slider-2", "slider-3", "slider-4", "slider-5", "slider-6", "slider-7", "slider-8", "grid-1", "grid-2", "grid-3", "grid-4", "grid-5", "grid-6", "grid-7", "list-1", "list-2", "list-3", "list-4", "list-5", "list-6", "list-7", "badge-1", "badge-2", "badge-3", "badge-4", "badge-5", "badge-6", "badge-7", "badge-8", "badge-9", "badge-10", "badge-11", "popup-1", "popup-2"],
      "slider": ["slider-1", "slider-2", "slider-3", "slider-4", "slider-5", "slider-6", "slider-7", "slider-8"],
      "grid": ["grid-1", "grid-2", "grid-3", "grid-4", "grid-5", "grid-6", "grid-7"],
      "list": ["list-1", "list-2", "list-3", "list-4", "list-5", "list-6", "list-7"],
      "badge": ["badge-1", "badge-2", "badge-3", "badge-4", "badge-5", "badge-6", "badge-7", "badge-8", "badge-9", "badge-10", "badge-11"],
      "popup": ["popup-1", "popup-2"]
    };

    // Validate layout and layout-option
    if (!zwssgrvalidLayouts[zwssgrlayout] || !zwssgrvalidLayouts[zwssgrlayout].includes(zwssgrlayoutOption)) {
      if (zwssgrerrorDiv) {
        zwssgrsuccessDiv.style.display = "none";
        zwssgrerrorDiv.innerText = "layout or layout-option do not match!";
        zwssgrerrorDiv.style.display = "block";

        // Hide error message after 10 seconds
        setTimeout(function () {
          zwssgrerrorDiv.style.display = "none";
          zwssgrerrorDiv.innerText = "";
        }, 30000); // 10000ms = 10 seconds
      }
      return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open("POST", ajaxurl, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          var zwssgrresponse = JSON.parse(xhr.responseText);
          if (zwssgrresponse.success) {
            if (zwssgrerrorDiv) zwssgrerrorDiv.style.display = "none"; // Hide error message
            if (zwssgrsuccessDiv) {
              zwssgrsuccessDiv.innerText = zwssgrresponse.data.message;
              zwssgrsuccessDiv.style.display = "block";
              setTimeout(function () {
                zwssgrsuccessDiv.style.display = "none";
                zwssgrsuccessDiv.innerText = "";
              }, 3000);
            }

            // **Redirect after successful response**
            if (zwssgrresponse.data.redirect_url) {
              setTimeout(function () {
                window.location.href = zwssgrresponse.data.redirect_url;
              }, 3000);
            }
          } else {
            if (zwssgrsuccessDiv) zwssgrsuccessDiv.style.display = "none"; // Hide success message
            if (zwssgrerrorDiv) {
              zwssgrerrorDiv.innerText = zwssgrresponse.data.message;
              zwssgrerrorDiv.style.display = "block";
              setTimeout(function () {
                zwssgrerrorDiv.style.display = "none";
                zwssgrerrorDiv.innerText = "";
              }, 3000);
            }
          }
        } catch (e) {
          console.error("Invalid JSON response", e);
        }
      }
    };

    // Send data via AJAX (include post-id)
    xhr.send("action=zwssgr_update_shortcode&post_id=" + encodeURIComponent(zwssgrpostId) + "&layout=" + encodeURIComponent(zwssgrlayout) + "&layout_option=" + encodeURIComponent(zwssgrlayoutOption));
  });
});

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"main": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunksmart_showcase_for_google_reviews"] = self["webpackChunksmart_showcase_for_google_reviews"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/js/index.js");
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=main.js.map