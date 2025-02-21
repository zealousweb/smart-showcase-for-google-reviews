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
	"./swiper.js": "./assets/src/js/swiper.js",
	"./tabbing.js": "./assets/src/js/tabbing.js",
	"./toogle-btn.js": "./assets/src/js/toogle-btn.js"
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
          if (['slider-1', 'slider-2', 'slider-3', 'slider-4', 'slider-5', 'slider-6'].includes(selectedOption)) {
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
            ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(function (selector) {
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
          ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(function (selector) {
            var element = mainDivWrapper.querySelector(selector);
            if (element) {
              setTimeout(function () {
                return (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.reinitializeAllSwipers)(element);
              }, 100);
              element.innerHTML = '';
              element.insertAdjacentHTML('beforeend', response.data.content);
            }
          });
          if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5'].includes(layoutType)) {
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
            ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(function (selector) {
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
          ['#zwssgr-slider1', '#zwssgr-slider2', '#zwssgr-slider3', '#zwssgr-slider4', '#zwssgr-slider5', '#zwssgr-slider6'].forEach(function (selector) {
            var element = mainDivWrapper.querySelector(selector);
            if (element) {
              setTimeout(function () {
                return (0,_swiper_js__WEBPACK_IMPORTED_MODULE_0__.reinitializeAllSwipers)(element);
              }, 100);
              element.innerHTML = '';
              element.insertAdjacentHTML('beforeend', response.data.content);
            }
          });
          if (['list-1', 'list-2', 'list-3', 'list-4', 'list-5', 'grid-1', 'grid-2', 'grid-3', 'grid-4', 'grid-5'].includes(layoutType)) {
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
      params.append('nonce', load_more.nonce);
      xhr.send(params);
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