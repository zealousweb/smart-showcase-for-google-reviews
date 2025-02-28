/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/js/backend sync \\.js$":
/*!********************************************************!*\
  !*** ./assets/src/js/backend/ sync nonrecursive \.js$ ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var map = {
	"./add-update-review-reply.js": "./assets/src/js/backend/add-update-review-reply.js",
	"./check-batch-status.js": "./assets/src/js/backend/check-batch-status.js",
	"./dashboard-filter.js": "./assets/src/js/backend/dashboard-filter.js",
	"./delete-review-reply.js": "./assets/src/js/backend/delete-review-reply.js",
	"./disconnect-auth.js": "./assets/src/js/backend/disconnect-auth.js",
	"./draw-chart.js": "./assets/src/js/backend/draw-chart.js",
	"./fetch-gmb-accounts-on-change.js": "./assets/src/js/backend/fetch-gmb-accounts-on-change.js",
	"./fetch-gmb-accounts.js": "./assets/src/js/backend/fetch-gmb-accounts.js",
	"./fetch-gmb-auth-url.js": "./assets/src/js/backend/fetch-gmb-auth-url.js",
	"./fetch-gmb-reviews.js": "./assets/src/js/backend/fetch-gmb-reviews.js",
	"./get-url-parameter.js": "./assets/src/js/backend/get-url-parameter.js",
	"./google-chart.js": "./assets/src/js/backend/google-chart.js",
	"./index.js": "./assets/src/js/backend/index.js",
	"./process-batches.js": "./assets/src/js/backend/process-batches.js",
	"./redirect-to-options-tab.js": "./assets/src/js/backend/redirect-to-options-tab.js",
	"./render-data-callback.js": "./assets/src/js/backend/render-data-callback.js"
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
webpackContext.id = "./assets/src/js/backend sync \\.js$";

/***/ }),

/***/ "./assets/src/js/backend/add-update-review-reply.js":
/*!**********************************************************!*\
  !*** ./assets/src/js/backend/add-update-review-reply.js ***!
  \**********************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.querySelectorAll("#gmb-review-data #add-reply, #gmb-review-data #update-reply").forEach(function (zwssgrButton) {
    zwssgrButton.addEventListener("click", function (zwssgrEv) {
      "use strict";

      zwssgrEv.preventDefault();
      var zwssgrReplyCommentElement = document.querySelector("#gmb-review-data textarea[name='zwssgr_reply_comment']");
      var zwssgrReplyComment = zwssgrReplyCommentElement.value.trim();
      var zwssgrJsonMessage = document.querySelector("#gmb-review-data #json-response-message");
      if (!zwssgrReplyComment) {
        zwssgrJsonMessage.innerHTML = '<div class="notice notice-error"> <p> Please enter a valid reply. </p> </div>';
        return;
      }
      if (zwssgrReplyComment.length > 4086) {
        zwssgrJsonMessage.innerHTML = '<div class="notice notice-error"> <p> Reply cannot exceed 4086 characters. </p> </div>';
        return;
      }
      var zwssgrLoader = document.createElement("span");
      zwssgrLoader.className = "zwssgr-loader is-active";
      zwssgrLoader.style.marginLeft = "10px";
      var zwssgrButtons = document.querySelectorAll("#gmb-review-data #add-reply, #gmb-review-data #update-reply, #gmb-review-data #delete-reply");
      var zwssgrUrlParams = new URLSearchParams(window.location.search);
      var zwssgrWpReviewId = zwssgrUrlParams.get("post");
      zwssgrButtons.forEach(function (btn) {
        return btn.classList.add("disabled");
      });
      zwssgrReplyCommentElement.readOnly = true;
      document.querySelector("#gmb-review-data #add-reply, #gmb-review-data #delete-reply").after(zwssgrLoader.cloneNode(true));
      fetch(zwssgr_admin.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "zwssgr_add_update_review_reply",
          zwssgr_wp_review_id: zwssgrWpReviewId,
          zwssgr_reply_comment: zwssgrReplyComment,
          security: zwssgr_admin.zwssgr_add_update_reply_nonce
        })
      }).then(function (zwssgrResponse) {
        return zwssgrResponse.json();
      }).then(function (data) {
        if (data.success) {
          var zwssgrSafeMessage = document.createElement("div");
          zwssgrSafeMessage.textContent = data.data.message;
          zwssgrJsonMessage.innerHTML = "<div class=\"notice notice-success\"><p>".concat(zwssgrSafeMessage.innerHTML, "</p></div>");
          setTimeout(function () {
            return location.reload();
          }, 2000);
        }
      })["catch"](function (zwssgrError) {
        zwssgrJsonMessage.innerHTML = "<div class=\"notice notice-error\"><p>Error: ".concat(zwssgrError.message, "</p></div>");
      })["finally"](function () {
        document.querySelectorAll("#gmb-review-data .zwssgr-loader.is-active").forEach(function (zwssgrLoader) {
          return zwssgrLoader.remove();
        });
      });
    });
  });
});

/***/ }),

/***/ "./assets/src/js/backend/check-batch-status.js":
/*!*****************************************************!*\
  !*** ./assets/src/js/backend/check-batch-status.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgrCheckBatchStatus: () => (/* binding */ zwssgrCheckBatchStatus)
/* harmony export */ });
/* harmony import */ var _get_url_parameter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./get-url-parameter */ "./assets/src/js/backend/get-url-parameter.js");
/* harmony import */ var _redirect_to_options_tab__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./redirect-to-options-tab */ "./assets/src/js/backend/redirect-to-options-tab.js");



// Check if we're on the specific page URL that contains zwssgr_widget_id dynamically
var zwssgrUrlParams = new URLSearchParams(window.location.search);
if (zwssgrUrlParams.has('zwssgr_widget_id') && window.location.href.includes('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data')) {
  var zwssgrBatchInterval = setInterval(function () {
    try {
      zwssgrCheckBatchStatus();
    } catch (zwssrError) {
      clearInterval(zwssgrBatchInterval); // Stop the interval on failure
    }
  }, 2500);
}
function zwssgrCheckBatchStatus() {
  var zwssgrWidgetId = (0,_get_url_parameter__WEBPACK_IMPORTED_MODULE_0__.zwssgrGetUrlParameter)('zwssgr_widget_id');
  fetch(zwssgr_admin.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({
      action: "zwssgr_get_batch_processing_status",
      security: zwssgr_admin.zwssgr_queue_manager_nounce,
      zwssgr_widget_id: zwssgrWidgetId
    })
  }).then(function (zwssgrResponse) {
    return zwssgrResponse.json();
  }).then(function (zwssgrResponse) {
    if (zwssgrResponse.success && zwssgrResponse.data.zwssgr_data_processing_init === 'false' && zwssgrResponse.data.zwssgr_data_sync_once === 'true') {
      document.querySelector('#fetch-gmb-data .progress-bar #progress').value = 100;
      document.querySelector('#fetch-gmb-data .progress-bar #progress-percentage').textContent = '100%';
      document.querySelector('#fetch-gmb-data .progress-bar #progress-percentage').textContent = 'Processed';
      if (zwssgrResponse.data.zwssgr_gmb_data_type === 'zwssgr_gmb_locations') {
        document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="success">Locations processed successfully</p>';
      } else if (zwssgrResponse.data.zwssgr_gmb_data_type === 'zwssgr_gmb_reviews') {
        document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="success">Reviews processed successfully</p>';
        document.querySelector('#fetch-gmb-data #fetch-gmd-reviews').innerHTML = 'Fetched';
      }
      setTimeout(function () {
        document.querySelector('#fetch-gmb-data .progress-bar').style.display = 'none';
        if (zwssgrResponse.data.zwssgr_gmb_data_type === 'zwssgr_gmb_reviews') {
          (0,_redirect_to_options_tab__WEBPACK_IMPORTED_MODULE_1__.zwssgrRedirectToOptionsTab)();
        } else {
          location.reload();
        }
      }, 2000);
    } else {
      var zwssgr_batch_progress = zwssgrResponse.data.zwssgr_batch_progress;
      if (!isNaN(zwssgr_batch_progress) && zwssgr_batch_progress >= 0 && zwssgr_batch_progress <= 100) {
        document.querySelector('#fetch-gmb-data .progress-bar #progress').value = zwssgr_batch_progress;
        document.querySelector('#fetch-gmb-data .progress-bar #progress-percentage').textContent = Math.round(zwssgr_batch_progress) + '%';
      }
    }
  })["catch"](function (zwssgrError) {
    console.error('Error:', zwssgrError);
  });
}

/***/ }),

/***/ "./assets/src/js/backend/dashboard-filter.js":
/*!***************************************************!*\
  !*** ./assets/src/js/backend/dashboard-filter.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr */ "./node_modules/flatpickr/dist/esm/index.js");
/* harmony import */ var _render_data_callback__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./render-data-callback */ "./assets/src/js/backend/render-data-callback.js");


document.addEventListener('DOMContentLoaded', function () {
  var zwssgrAccountSelect = document.getElementById('zwssgr-account-select');
  if (zwssgrAccountSelect) {
    zwssgrAccountSelect.addEventListener('change', function (zwssgrEv) {
      "use strict";

      zwssgrEv.preventDefault();
      var zwssgrAccountNumber = this.value;
      var zwssgrDropdown = this;
      var zwssgrDataFilter = document.getElementById('zwssgr-gmb-data-filter');
      zwssgrDropdown.classList.add('disabled');
      var zwssgrLoader = document.createElement('span');
      zwssgrLoader.className = 'loader is-active';
      fetch(zwssgr_admin.ajax_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'zwssgr_gmb_dashboard_data_filter',
          zwssgr_account_number: zwssgrAccountNumber,
          security: zwssgr_admin.zwssgr_gmb_dashboard_filter
        })
      }).then(function (zwssgrResponse) {
        return zwssgrResponse.json();
      }).then(function (zwssgrData) {
        var zwssgrLocationSelect = document.getElementById('zwssgr-location-select');
        if (zwssgrLocationSelect) {
          zwssgrLocationSelect.remove();
        }
        if (zwssgrData.success) {
          zwssgrDataFilter.insertAdjacentHTML('beforeend', zwssgrData.data);
        } else {
          //zwssgrDataFilter.insertAdjacentHTML('beforeend', '<div class="notice notice-error"> No data available. </div>');
        }
      })["catch"](function (zwssgrError) {
        //zwssgrDataFilter.insertAdjacentHTML('beforeend', '<div class="notice notice-error"> Error occurred while processing your request. </div>');
      })["finally"](function () {
        zwssgrDropdown.classList.remove('disabled');
        if (zwssgrLoader.parentNode) {
          zwssgrLoader.parentNode.removeChild(zwssgrLoader);
        }
      });
    });
  }
  var zwgrDashboard = document.getElementById('zwssgr-dashboard');
  if (zwgrDashboard) {
    zwgrDashboard.addEventListener('change', function (zwssgrEv) {
      "use strict";

      var zwssgrTargetId = zwssgrEv.target.id;
      if (zwssgrTargetId !== 'zwssgr-account-select' && zwssgrTargetId !== 'zwssgr-location-select') {
        return;
      }
      var zwssgrRangeFilterData = null;
      var zwssgrRangeFilterType = null;
      var zwssgrInputs = document.querySelectorAll('#zwssgr-account-select, #zwssgr-location-select');
      zwssgrInputs.forEach(function (zwssgrInput) {
        zwssgrInput.classList.add('disabled');
        zwssgrInput.disabled = true;
      });
      var zwssgrActiveButton = document.querySelector('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button.active');
      var zwssgrActiveDateInput = document.querySelector('.zwssgr-dashboard .zwssgr-filters-wrapper input[name="dates"].active');
      if (zwssgrActiveButton) {
        zwssgrRangeFilterType = 'rangeofdays';
        zwssgrRangeFilterData = zwssgrActiveButton.textContent.trim().toLowerCase();
      } else if (zwssgrActiveDateInput) {
        zwssgrRangeFilterType = 'rangeofdate';
        zwssgrRangeFilterData = zwssgrActiveDateInput.value.trim().replace(" to ", " - ");
      }
      (0,_render_data_callback__WEBPACK_IMPORTED_MODULE_1__.zwssgrRenderDataCallback)(zwssgrEv, zwssgrRangeFilterData, zwssgrRangeFilterType);
    });
  }
  ;
  if (zwgrDashboard) {
    zwgrDashboard.addEventListener('click', function (zwssgrEv) {
      "use strict";

      var zwssgrButton = zwssgrEv.target.closest('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button');
      if (!zwssgrButton) {
        return;
      }
      var zwssgrRangeFilterData = zwssgrButton.textContent.trim().toLowerCase();
      if (!zwssgrRangeFilterData) {
        return;
      }
      document.querySelectorAll('.zwssgr-filter-button').forEach(function (button) {
        button.classList.remove('active');
      });
      zwssgrButton.classList.add('active');
      (0,_render_data_callback__WEBPACK_IMPORTED_MODULE_1__.zwssgrRenderDataCallback)(zwssgrEv, zwssgrRangeFilterData, "rangeofdays");
    });
  }
  var zwssgrDateInput = document.querySelector('.zwssgr-dashboard-header .zwssgr-filters-wrapper .zwssgr-date-range-picker');
  if (zwssgrDateInput) {
    (0,flatpickr__WEBPACK_IMPORTED_MODULE_0__["default"])(zwssgrDateInput, {
      mode: "range",
      dateFormat: "d-m-Y",
      altInput: true,
      altFormat: "d-m-Y",
      maxDate: "today",
      onReady: function onReady(selectedDates, dateStr, instance) {
        instance.altInput.setAttribute("placeholder", "Custom");
      },
      onChange: function onChange(selectedDates, dateStr, instance) {
        if (selectedDates.length < 2) {
          return;
        }
        var zwssgrFilterButtons = document.querySelectorAll('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button');
        zwssgrFilterButtons.forEach(function (button) {
          return button.classList.remove('active');
        });
        zwssgrDateInput.classList.add('active');
        var zwssgrStartDate = instance.formatDate(selectedDates[0], "d-m-Y");
        var zwssgrEndDate = instance.formatDate(selectedDates[1], "d-m-Y");
        var zwssgrRangeFilterData = "".concat(zwssgrStartDate, " - ").concat(zwssgrEndDate);
        var zwssgrEv = {
          type: "flatpickr-change",
          target: zwssgrDateInput,
          selectedDates: selectedDates,
          dateStr: dateStr,
          instance: instance,
          preventDefault: function preventDefault() {
            return console.log("");
          }
        };
        if (typeof _render_data_callback__WEBPACK_IMPORTED_MODULE_1__.zwssgrRenderDataCallback === "function") {
          (0,_render_data_callback__WEBPACK_IMPORTED_MODULE_1__.zwssgrRenderDataCallback)(zwssgrEv, zwssgrRangeFilterData, 'rangeofdate');
        } else {}
      }
    });
  }
});

/***/ }),

/***/ "./assets/src/js/backend/delete-review-reply.js":
/*!******************************************************!*\
  !*** ./assets/src/js/backend/delete-review-reply.js ***!
  \******************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  document.querySelectorAll("#gmb-review-data #delete-reply").forEach(function (zwssgrButton) {
    zwssgrButton.addEventListener("click", function (zwssgrEv) {
      "use strict";

      zwssgrEv.preventDefault();
      var zwssgrLoader = document.createElement("span");
      zwssgrLoader.className = "zwssgr-loader is-active";
      zwssgrLoader.style.marginLeft = "10px";
      var zwssgrButtons = document.querySelectorAll("#gmb-review-data #update-reply, #gmb-review-data #delete-reply");
      var zwssgrUrlParams = new URLSearchParams(window.location.search);
      var zwssgrWpReviewId = zwssgrUrlParams.get("post");
      var zwssgrJsonMessage = document.querySelector("#gmb-review-data #json-response-message");
      zwssgrButtons.forEach(function (btn) {
        return btn.classList.add("disabled");
      });
      document.querySelector("#gmb-review-data textarea[name='zwssgr_reply_comment']").readOnly = true;
      document.querySelector("#gmb-review-data #delete-reply").after(zwssgrLoader);
      fetch(zwssgr_admin.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "zwssgr_delete_review_reply",
          zwssgr_wp_review_id: zwssgrWpReviewId,
          security: zwssgr_admin.zwssgr_delete_review_reply
        })
      }).then(function (zwssgrResponse) {
        return zwssgrResponse.json();
      }).then(function (zwssgrData) {
        if (zwssgrData.success) {
          var zwssgrSafeMessage = document.createElement("div");
          zwssgrSafeMessage.textContent = zwssgrData.data.message;
          zwssgrJsonMessage.innerHTML = "<div class=\"notice notice-success\"><p>".concat(zwssgrSafeMessage.innerHTML, "</p></div>");
          setTimeout(function () {
            return location.reload();
          }, 2000);
        }
      })["catch"](function (zwssgrError) {
        zwssgrJsonMessage.innerHTML = "<div class=\"notice notice-error\"><p>Error: ".concat(zwssgrError.message, "</p></div>");
      })["finally"](function () {
        document.querySelectorAll("#gmb-review-data .zwssgr-loader.is-active").forEach(function (zwssgrLoader) {
          return zwssgrLoader.remove();
        });
      });
    });
  });
});

/***/ }),

/***/ "./assets/src/js/backend/disconnect-auth.js":
/*!**************************************************!*\
  !*** ./assets/src/js/backend/disconnect-auth.js ***!
  \**************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var zwssgrDisconnectButton = document.querySelector("#disconnect-gmb-auth #disconnect-auth");
  if (zwssgrDisconnectButton) {
    zwssgrDisconnectButton.addEventListener("click", function (zwssgrEv) {
      zwssgrEv.preventDefault();
      zwssgrDisconnectButton.disabled = true;
      zwssgrDisconnectButton.textContent = "Disconnecting...";
      var zwssgrDeletePluginData = document.querySelector("#disconnect-gmb-auth #delete-all-data").checked ? '1' : '0';
      var zwssgrDeleteDataResponse = document.getElementById("disconnect-gmb-auth-response");
      fetch(zwssgr_admin.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "zwssgr_delete_oauth_connection",
          zwssgr_delete_plugin_data: zwssgrDeletePluginData,
          security: zwssgr_admin.zwssgr_delete_oauth_connection
        })
      }).then(function (response) {
        return response.json();
      }).then(function (zwssgrData) {
        if (zwssgrData.success) {
          var _zwssgrData$data;
          zwssgrDisconnectButton.disabled = false;
          zwssgrDisconnectButton.textContent = "Disconnected";
          zwssgrDeleteDataResponse.innerHTML = "<p class='success response'> OAuth Disconnected: " + (((_zwssgrData$data = zwssgrData.data) === null || _zwssgrData$data === void 0 ? void 0 : _zwssgrData$data.message) || "Unknown error") + "</p>";
          document.querySelector("#disconnect-gmb-auth .zwssgr-th-label").innerHTML = "";
          document.querySelector("#disconnect-gmb-auth .zwssgr-caution-div").style.display = "none";
          document.querySelector("#disconnect-gmb-auth .danger-note").style.display = "none";
          setTimeout(function () {
            window.location.href = zwssgr_admin.zwssgr_redirect;
          }, 1500);
        } else {
          var _zwssgrData$data2;
          zwssgrDeleteDataResponse.innerHTML = "<p class='error response'>Error disconnecting OAuth connection: " + (((_zwssgrData$data2 = zwssgrData.data) === null || _zwssgrData$data2 === void 0 ? void 0 : _zwssgrData$data2.message) || "Unknown error") + "</p>";
        }
      })["catch"](function (zwssgrError) {
        zwssgrDisconnectButton.disabled = false;
        zwssgrDisconnectButton.textContent = "Disconnect";
        zwssgrDeleteDataResponse.innerHTML = "<p class='error response'> An unexpected error occurred: " + zwssgrError + "</p>";
      });
    });
  }
});

/***/ }),

/***/ "./assets/src/js/backend/draw-chart.js":
/*!*********************************************!*\
  !*** ./assets/src/js/backend/draw-chart.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgr_draw_chart: () => (/* binding */ zwssgr_draw_chart)
/* harmony export */ });
var zwssgrChart;
var zwssgrData;
var zwssgrOptions;
google.charts.load('current', {
  packages: ['corechart']
});
google.charts.setOnLoadCallback(function () {
  return zwssgr_draw_chart(zwssgr_admin.zwssgr_dynamic_chart_data);
});
function zwssgr_draw_chart(zwssgrChartData) {
  "use strict";

  var chartWrapper = document.getElementById('zwssgr_chart_wrapper');
  if (!Array.isArray(zwssgrChartData) && chartWrapper) {
    chartWrapper.innerHTML = '<div class="zwssgr-dashboard-text"> No enough data available. </div>';
    return;
  }
  var zwssgr_all_zero = zwssgrChartData.every(function (row) {
    return Array.isArray(row) && row[1] === 0;
  });
  if (zwssgr_all_zero && chartWrapper) {
    chartWrapper.innerHTML = '<div class="zwssgr-dashboard-text"> No enough data available. </div>';
    return;
  }
  zwssgrChartData.unshift(['Rating', 'Number of Reviews']);
  var zwssgrData = google.visualization.arrayToDataTable(zwssgrChartData);
  var zwssgrOptions = {
    pieHole: 0.4,
    width: 276,
    height: 276,
    pieSliceText: 'percentage',
    pieSliceTextStyle: {
      color: '#000000',
      fontSize: 16
    },
    legend: 'none',
    chartArea: {
      width: '90%',
      height: '90%'
    },
    colors: ['#F08C3C', '#3CAAB4', '#A9C6CC', '#285064', '#F44336'],
    backgroundColor: 'transparent'
  };
  try {
    if (chartWrapper) {
      var _zwssgrChart = new google.visualization.PieChart(chartWrapper);
      _zwssgrChart.draw(zwssgrData, zwssgrOptions);
    }
  } catch (error) {
    if (chartWrapper) {
      chartWrapper.innerHTML = '<div class="zwssgr-dashboard-text">Failed to render chart</div>';
    }
  }
}
var zwssgrResizeTimeout;
window.addEventListener('resize', function () {
  clearTimeout(zwssgrResizeTimeout);
  zwssgrResizeTimeout = setTimeout(function () {
    if (zwssgrChart && zwssgrData && zwssgrOptions) {
      zwssgrChart.draw(zwssgrData, zwssgrOptions);
    } else {}
  }, 200);
});

/***/ }),

/***/ "./assets/src/js/backend/fetch-gmb-accounts-on-change.js":
/*!***************************************************************!*\
  !*** ./assets/src/js/backend/fetch-gmb-accounts-on-change.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _process_batches__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./process-batches */ "./assets/src/js/backend/process-batches.js");
/* harmony import */ var _get_url_parameter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./get-url-parameter */ "./assets/src/js/backend/get-url-parameter.js");


document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var zwssgrAccountSelect = document.querySelector("#fetch-gmb-data #zwssgr-account-select");
  if (zwssgrAccountSelect) {
    zwssgrAccountSelect.addEventListener("change", function () {
      "use strict";

      var zwssgrAccountNumber = zwssgrAccountSelect.value;
      var zwssgrAccountName = zwssgrAccountSelect.options[zwssgrAccountSelect.selectedIndex].text;
      document.querySelectorAll("#fetch-gmb-data #zwssgr-location-select, .zwssgr-submit-btn, #fetch-gmd-reviews").forEach(function (zwssgrEl) {
        return zwssgrEl.remove();
      });
      if (zwssgrAccountNumber) {
        zwssgrAccountSelect.disabled = true;
        var zwssgrWidgetId = (0,_get_url_parameter__WEBPACK_IMPORTED_MODULE_1__.zwssgrGetUrlParameter)("zwssgr_widget_id");
        document.querySelector("#fetch-gmb-data .response").innerHTML = '';
        document.querySelector("#fetch-gmb-data .progress-bar").classList.add("active");
        try {
          (0,_process_batches__WEBPACK_IMPORTED_MODULE_0__.zwssgrProcessBatch)("zwssgr_gmb_locations", zwssgrAccountNumber, null, zwssgrWidgetId, null, null, zwssgrAccountName);
        } catch (error) {
          document.querySelector("#fetch-gmb-data .progress-bar").classList.remove("active");
          document.querySelector("#fetch-gmb-data .response").innerHTML = "<p class='error'>An error occurred while processing your request.</p>";
        }
      }
    });
  }
});

/***/ }),

/***/ "./assets/src/js/backend/fetch-gmb-accounts.js":
/*!*****************************************************!*\
  !*** ./assets/src/js/backend/fetch-gmb-accounts.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _process_batches__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./process-batches */ "./assets/src/js/backend/process-batches.js");
/* harmony import */ var _get_url_parameter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./get-url-parameter */ "./assets/src/js/backend/get-url-parameter.js");


document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var zwssgrButton = document.querySelector("#fetch-gmb-data #fetch-gmd-accounts");
  if (zwssgrButton) {
    zwssgrButton.addEventListener("click", function (zwssgrEv) {
      "use strict";

      zwssgrEv.preventDefault();
      var zwssgrGmbDataType = zwssgrButton.getAttribute("data-fetch-type");
      var zwssgrWidgetId = (0,_get_url_parameter__WEBPACK_IMPORTED_MODULE_1__.zwssgrGetUrlParameter)("zwssgr_widget_id");
      document.querySelector("#fetch-gmb-data .zwssgr-progress-bar").classList.add("active");
      zwssgrButton.classList.add("disabled");
      zwssgrButton.innerHTML = '<span class="spinner is-active"></span> Fetching...';
      try {
        (0,_process_batches__WEBPACK_IMPORTED_MODULE_0__.zwssgrProcessBatch)(zwssgrGmbDataType, null, null, zwssgrWidgetId, null, null, null, null);
      } catch (zwssgrError) {
        console.error("Error processing batch:", zwssgrError);
      }
    });
  }
});

/***/ }),

/***/ "./assets/src/js/backend/fetch-gmb-auth-url.js":
/*!*****************************************************!*\
  !*** ./assets/src/js/backend/fetch-gmb-auth-url.js ***!
  \*****************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var zwssgrAuthButton = document.getElementById("fetch-gmb-auth-url");
  if (zwssgrAuthButton) {
    zwssgrAuthButton.addEventListener("click", function (zwssgrEv) {
      "use strict";

      zwssgrEv.preventDefault();
      var zwssgrAuthResponse = document.getElementById("fetch-gmb-auth-url-response");
      zwssgrAuthButton.disabled = true;
      zwssgrAuthButton.textContent = "Connecting...";
      fetch(zwssgr_admin.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "zwssgr_fetch_oauth_url"
        })
      }).then(function (zwssgrResponse) {
        return zwssgrResponse.json();
      }).then(function (zwssgrData) {
        if (zwssgrData.success) {
          zwssgrAuthButton.disabled = false;
          zwssgrAuthButton.textContent = "Redirecting...";
          window.location.href = zwssgrData.data.zwssgr_oauth_url;
        } else {
          var _zwssgrData$data;
          var zwssgrErrorMessage = document.createElement("p");
          zwssgrErrorMessage.classList.add("error", "response");
          zwssgrErrorMessage.textContent = "Error generating OAuth URL: " + (((_zwssgrData$data = zwssgrData.data) === null || _zwssgrData$data === void 0 ? void 0 : _zwssgrData$data.message) || "Unknown error");
          zwssgrAuthResponse.innerHTML = "";
          zwssgrAuthResponse.appendChild(zwssgrErrorMessage);
        }
      })["catch"](function (zwssgrError) {
        zwssgrAuthButton.disabled = false;
        zwssgrAuthButton.textContent = "Connect with Google";
        var zwssgrUnexpectedError = document.createElement("p");
        zwssgrUnexpectedError.classList.add("error", "response");
        zwssgrUnexpectedError.textContent = "An unexpected error occurred: " + zwssgrError;
        zwssgrAuthResponse.innerHTML = "";
        zwssgrAuthResponse.appendChild(zwssgrUnexpectedError);
      });
    });
  }
});

/***/ }),

/***/ "./assets/src/js/backend/fetch-gmb-reviews.js":
/*!****************************************************!*\
  !*** ./assets/src/js/backend/fetch-gmb-reviews.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _process_batches__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./process-batches */ "./assets/src/js/backend/process-batches.js");
/* harmony import */ var _get_url_parameter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./get-url-parameter */ "./assets/src/js/backend/get-url-parameter.js");


document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  var zwssgrFetchReviewButton = document.querySelector("#fetch-gmb-data #fetch-gmd-reviews");
  if (zwssgrFetchReviewButton) {
    zwssgrFetchReviewButton.addEventListener("click", function (zwssgrEv) {
      zwssgrEv.preventDefault();
      zwssgrFetchGmbData(this);
    });
  }
  function zwssgrFetchGmbData(zwssgrFetchReviewButton) {
    var zwssgrGmbDataType = zwssgrFetchReviewButton.getAttribute("data-fetch-type");
    var zwssgrAccountSelect = document.querySelector("#fetch-gmb-data #zwssgr-account-select");
    if (!zwssgrAccountSelect) {
      return;
    }
    var zwssgrAccountNumber = zwssgrAccountSelect.value;
    zwssgrAccountSelect.classList.add('disabled');
    var zwssgrAccountName = zwssgrAccountSelect.options[zwssgrAccountSelect.selectedIndex].text;
    var zwssgrLocationSelect = document.querySelector("#fetch-gmb-data #zwssgr-location-select");
    if (!zwssgrLocationSelect) {
      return;
    }
    var zwssgrLocationNumber = zwssgrLocationSelect.value;
    zwssgrLocationSelect.classList.add('disabled');
    var zwssgrLocationName = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].text;
    var zwssgrLocationNewReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-new-review-url");
    var zwssgrLocationAllReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-all-reviews-url");
    var zwssgrWidgetId = (0,_get_url_parameter__WEBPACK_IMPORTED_MODULE_1__.zwssgrGetUrlParameter)("zwssgr_widget_id");
    zwssgrFetchReviewButton.classList.add("disabled");
    zwssgrFetchReviewButton.innerHTML = '<span class="spinner is-active"></span> Fetching...';
    if (!zwssgrAccountNumber && !zwssgrLocationNumber) {
      document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Both account and location are required.</p>';
      setTimeout(function () {
        return location.reload();
      }, 1500);
      return;
    }
    if (!zwssgrAccountNumber) {
      document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Account is required.</p>';
      setTimeout(function () {
        return location.reload();
      }, 1500);
      return;
    }
    if (!zwssgrLocationNumber) {
      document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Location is required.</p>';
      setTimeout(function () {
        return location.reload();
      }, 1500);
      return;
    }
    if (!zwssgrWidgetId) {
      document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">No valid widget ID found.</p>';
      setTimeout(function () {
        return location.reload();
      }, 1500);
      return;
    }
    document.querySelector('#fetch-gmb-data .response').innerHTML = '';
    document.querySelector('#fetch-gmb-data .progress-bar').style.display = 'block';
    (0,_process_batches__WEBPACK_IMPORTED_MODULE_0__.zwssgrProcessBatch)(zwssgrGmbDataType, zwssgrAccountNumber, zwssgrLocationNumber, zwssgrWidgetId, zwssgrLocationName, zwssgrLocationNewReviewUri, zwssgrAccountName, zwssgrLocationAllReviewUri);
  }
});

/***/ }),

/***/ "./assets/src/js/backend/get-url-parameter.js":
/*!****************************************************!*\
  !*** ./assets/src/js/backend/get-url-parameter.js ***!
  \****************************************************/
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

/***/ "./assets/src/js/backend/google-chart.js":
/*!***********************************************!*\
  !*** ./assets/src/js/backend/google-chart.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
(function () {
  /*
  Copyright The Closure Library Authors.
  SPDX-License-Identifier: Apache-2.0
  */
  'use strict';

  function aa(a) {
    var b = 0;
    return function () {
      return b < a.length ? {
        done: !1,
        value: a[b++]
      } : {
        done: !0
      };
    };
  }
  var ba = "function" == typeof Object.defineProperties ? Object.defineProperty : function (a, b, c) {
    if (a == Array.prototype || a == Object.prototype) return a;
    a[b] = c.value;
    return a;
  };
  function ca(a) {
    a = ["object" == (typeof globalThis === "undefined" ? "undefined" : _typeof(globalThis)) && globalThis, a, "object" == (typeof window === "undefined" ? "undefined" : _typeof(window)) && window, "object" == (typeof self === "undefined" ? "undefined" : _typeof(self)) && self, "object" == (typeof __webpack_require__.g === "undefined" ? "undefined" : _typeof(__webpack_require__.g)) && __webpack_require__.g];
    for (var b = 0; b < a.length; ++b) {
      var c = a[b];
      if (c && c.Math == Math) return c;
    }
    throw Error("Cannot find global object");
  }
  var l = ca(this);
  function p(a, b) {
    if (b) a: {
      var c = l;
      a = a.split(".");
      for (var d = 0; d < a.length - 1; d++) {
        var e = a[d];
        if (!(e in c)) break a;
        c = c[e];
      }
      a = a[a.length - 1];
      d = c[a];
      b = b(d);
      b != d && null != b && ba(c, a, {
        configurable: !0,
        writable: !0,
        value: b
      });
    }
  }
  p("Symbol", function (a) {
    function b(h) {
      if (this instanceof b) throw new TypeError("Symbol is not a constructor");
      return new c(d + (h || "") + "_" + e++, h);
    }
    function c(h, f) {
      this.g = h;
      ba(this, "description", {
        configurable: !0,
        writable: !0,
        value: f
      });
    }
    if (a) return a;
    c.prototype.toString = function () {
      return this.g;
    };
    var d = "jscomp_symbol_" + (1E9 * Math.random() >>> 0) + "_",
      e = 0;
    return b;
  });
  p("Symbol.iterator", function (a) {
    if (a) return a;
    a = Symbol("Symbol.iterator");
    for (var b = "Array Int8Array Uint8Array Uint8ClampedArray Int16Array Uint16Array Int32Array Uint32Array Float32Array Float64Array".split(" "), c = 0; c < b.length; c++) {
      var d = l[b[c]];
      "function" === typeof d && "function" != typeof d.prototype[a] && ba(d.prototype, a, {
        configurable: !0,
        writable: !0,
        value: function value() {
          return da(aa(this));
        }
      });
    }
    return a;
  });
  p("Symbol.asyncIterator", function (a) {
    return a ? a : Symbol("Symbol.asyncIterator");
  });
  function da(a) {
    a = {
      next: a
    };
    a[Symbol.iterator] = function () {
      return this;
    };
    return a;
  }
  function q(a) {
    var b = "undefined" != typeof Symbol && Symbol.iterator && a[Symbol.iterator];
    if (b) return b.call(a);
    if ("number" == typeof a.length) return {
      next: aa(a)
    };
    throw Error(String(a) + " is not an iterable or ArrayLike");
  }
  function ea(a) {
    if (!(a instanceof Array)) {
      a = q(a);
      for (var b, c = []; !(b = a.next()).done;) c.push(b.value);
      a = c;
    }
    return a;
  }
  function r(a, b) {
    return Object.prototype.hasOwnProperty.call(a, b);
  }
  var fa = "function" == typeof Object.assign ? Object.assign : function (a, b) {
    for (var c = 1; c < arguments.length; c++) {
      var d = arguments[c];
      if (d) for (var e in d) r(d, e) && (a[e] = d[e]);
    }
    return a;
  };
  p("Object.assign", function (a) {
    return a || fa;
  });
  var ha;
  if ("function" == typeof Object.setPrototypeOf) ha = Object.setPrototypeOf;else {
    var ia;
    a: {
      var ja = {
          a: !0
        },
        ka = {};
      try {
        ka.__proto__ = ja;
        ia = ka.a;
        break a;
      } catch (a) {}
      ia = !1;
    }
    ha = ia ? function (a, b) {
      a.__proto__ = b;
      if (a.__proto__ !== b) throw new TypeError(a + " is not extensible");
      return a;
    } : null;
  }
  var t = ha;
  function la() {
    for (var a = Number(this), b = [], c = a; c < arguments.length; c++) b[c - a] = arguments[c];
    return b;
  }
  p("Promise", function (a) {
    function b(f) {
      this.g = 0;
      this.i = void 0;
      this.h = [];
      this.o = !1;
      var g = this.j();
      try {
        f(g.resolve, g.reject);
      } catch (k) {
        g.reject(k);
      }
    }
    function c() {
      this.g = null;
    }
    function d(f) {
      return f instanceof b ? f : new b(function (g) {
        g(f);
      });
    }
    if (a) return a;
    c.prototype.h = function (f) {
      if (null == this.g) {
        this.g = [];
        var g = this;
        this.i(function () {
          g.l();
        });
      }
      this.g.push(f);
    };
    var e = l.setTimeout;
    c.prototype.i = function (f) {
      e(f, 0);
    };
    c.prototype.l = function () {
      for (; this.g && this.g.length;) {
        var f = this.g;
        this.g = [];
        for (var g = 0; g < f.length; ++g) {
          var k = f[g];
          f[g] = null;
          try {
            k();
          } catch (m) {
            this.j(m);
          }
        }
      }
      this.g = null;
    };
    c.prototype.j = function (f) {
      this.i(function () {
        throw f;
      });
    };
    b.prototype.j = function () {
      function f(m) {
        return function (n) {
          k || (k = !0, m.call(g, n));
        };
      }
      var g = this,
        k = !1;
      return {
        resolve: f(this.D),
        reject: f(this.l)
      };
    };
    b.prototype.D = function (f) {
      if (f === this) this.l(new TypeError("A Promise cannot resolve to itself"));else if (f instanceof b) this.O(f);else {
        a: switch (_typeof(f)) {
          case "object":
            var g = null != f;
            break a;
          case "function":
            g = !0;
            break a;
          default:
            g = !1;
        }
        g ? this.C(f) : this.m(f);
      }
    };
    b.prototype.C = function (f) {
      var g = void 0;
      try {
        g = f.then;
      } catch (k) {
        this.l(k);
        return;
      }
      "function" == typeof g ? this.P(g, f) : this.m(f);
    };
    b.prototype.l = function (f) {
      this.u(2, f);
    };
    b.prototype.m = function (f) {
      this.u(1, f);
    };
    b.prototype.u = function (f, g) {
      if (0 != this.g) throw Error("Cannot settle(" + f + ", " + g + "): Promise already settled in state" + this.g);
      this.g = f;
      this.i = g;
      2 === this.g && this.G();
      this.A();
    };
    b.prototype.G = function () {
      var f = this;
      e(function () {
        if (f.B()) {
          var g = l.console;
          "undefined" !== typeof g && g.error(f.i);
        }
      }, 1);
    };
    b.prototype.B = function () {
      if (this.o) return !1;
      var f = l.CustomEvent,
        g = l.Event,
        k = l.dispatchEvent;
      if ("undefined" === typeof k) return !0;
      "function" === typeof f ? f = new f("unhandledrejection", {
        cancelable: !0
      }) : "function" === typeof g ? f = new g("unhandledrejection", {
        cancelable: !0
      }) : (f = l.document.createEvent("CustomEvent"), f.initCustomEvent("unhandledrejection", !1, !0, f));
      f.promise = this;
      f.reason = this.i;
      return k(f);
    };
    b.prototype.A = function () {
      if (null != this.h) {
        for (var f = 0; f < this.h.length; ++f) h.h(this.h[f]);
        this.h = null;
      }
    };
    var h = new c();
    b.prototype.O = function (f) {
      var g = this.j();
      f.F(g.resolve, g.reject);
    };
    b.prototype.P = function (f, g) {
      var k = this.j();
      try {
        f.call(g, k.resolve, k.reject);
      } catch (m) {
        k.reject(m);
      }
    };
    b.prototype.then = function (f, g) {
      function k(y, G) {
        return "function" == typeof y ? function (ra) {
          try {
            m(y(ra));
          } catch (sa) {
            n(sa);
          }
        } : G;
      }
      var m,
        n,
        u = new b(function (y, G) {
          m = y;
          n = G;
        });
      this.F(k(f, m), k(g, n));
      return u;
    };
    b.prototype["catch"] = function (f) {
      return this.then(void 0, f);
    };
    b.prototype.F = function (f, g) {
      function k() {
        switch (m.g) {
          case 1:
            f(m.i);
            break;
          case 2:
            g(m.i);
            break;
          default:
            throw Error("Unexpected state: " + m.g);
        }
      }
      var m = this;
      null == this.h ? h.h(k) : this.h.push(k);
      this.o = !0;
    };
    b.resolve = d;
    b.reject = function (f) {
      return new b(function (g, k) {
        k(f);
      });
    };
    b.race = function (f) {
      return new b(function (g, k) {
        for (var m = q(f), n = m.next(); !n.done; n = m.next()) d(n.value).F(g, k);
      });
    };
    b.all = function (f) {
      var g = q(f),
        k = g.next();
      return k.done ? d([]) : new b(function (m, n) {
        function u(ra) {
          return function (sa) {
            y[ra] = sa;
            G--;
            0 == G && m(y);
          };
        }
        var y = [],
          G = 0;
        do y.push(void 0), G++, d(k.value).F(u(y.length - 1), n), k = g.next(); while (!k.done);
      });
    };
    return b;
  });
  p("Object.setPrototypeOf", function (a) {
    return a || t;
  });
  p("WeakMap", function (a) {
    function b(k) {
      this.g = (g += Math.random() + 1).toString();
      if (k) {
        k = q(k);
        for (var m; !(m = k.next()).done;) m = m.value, this.set(m[0], m[1]);
      }
    }
    function c() {}
    function d(k) {
      var m = _typeof(k);
      return "object" === m && null !== k || "function" === m;
    }
    function e(k) {
      if (!r(k, f)) {
        var m = new c();
        ba(k, f, {
          value: m
        });
      }
    }
    function h(k) {
      var m = Object[k];
      m && (Object[k] = function (n) {
        if (n instanceof c) return n;
        Object.isExtensible(n) && e(n);
        return m(n);
      });
    }
    if (function () {
      if (!a || !Object.seal) return !1;
      try {
        var k = Object.seal({}),
          m = Object.seal({}),
          n = new a([[k, 2], [m, 3]]);
        if (2 != n.get(k) || 3 != n.get(m)) return !1;
        n["delete"](k);
        n.set(m, 4);
        return !n.has(k) && 4 == n.get(m);
      } catch (u) {
        return !1;
      }
    }()) return a;
    var f = "$jscomp_hidden_" + Math.random();
    h("freeze");
    h("preventExtensions");
    h("seal");
    var g = 0;
    b.prototype.set = function (k, m) {
      if (!d(k)) throw Error("Invalid WeakMap key");
      e(k);
      if (!r(k, f)) throw Error("WeakMap key fail: " + k);
      k[f][this.g] = m;
      return this;
    };
    b.prototype.get = function (k) {
      return d(k) && r(k, f) ? k[f][this.g] : void 0;
    };
    b.prototype.has = function (k) {
      return d(k) && r(k, f) && r(k[f], this.g);
    };
    b.prototype["delete"] = function (k) {
      return d(k) && r(k, f) && r(k[f], this.g) ? delete k[f][this.g] : !1;
    };
    return b;
  });
  p("Map", function (a) {
    function b() {
      var g = {};
      return g.v = g.next = g.head = g;
    }
    function c(g, k) {
      var m = g.g;
      return da(function () {
        if (m) {
          for (; m.head != g.g;) m = m.v;
          for (; m.next != m.head;) return m = m.next, {
            done: !1,
            value: k(m)
          };
          m = null;
        }
        return {
          done: !0,
          value: void 0
        };
      });
    }
    function d(g, k) {
      var m = k && _typeof(k);
      "object" == m || "function" == m ? h.has(k) ? m = h.get(k) : (m = "" + ++f, h.set(k, m)) : m = "p_" + k;
      var n = g.h[m];
      if (n && r(g.h, m)) for (g = 0; g < n.length; g++) {
        var u = n[g];
        if (k !== k && u.key !== u.key || k === u.key) return {
          id: m,
          list: n,
          index: g,
          s: u
        };
      }
      return {
        id: m,
        list: n,
        index: -1,
        s: void 0
      };
    }
    function e(g) {
      this.h = {};
      this.g = b();
      this.size = 0;
      if (g) {
        g = q(g);
        for (var k; !(k = g.next()).done;) k = k.value, this.set(k[0], k[1]);
      }
    }
    if (function () {
      if (!a || "function" != typeof a || !a.prototype.entries || "function" != typeof Object.seal) return !1;
      try {
        var g = Object.seal({
            x: 4
          }),
          k = new a(q([[g, "s"]]));
        if ("s" != k.get(g) || 1 != k.size || k.get({
          x: 4
        }) || k.set({
          x: 4
        }, "t") != k || 2 != k.size) return !1;
        var m = k.entries(),
          n = m.next();
        if (n.done || n.value[0] != g || "s" != n.value[1]) return !1;
        n = m.next();
        return n.done || 4 != n.value[0].x || "t" != n.value[1] || !m.next().done ? !1 : !0;
      } catch (u) {
        return !1;
      }
    }()) return a;
    var h = new WeakMap();
    e.prototype.set = function (g, k) {
      g = 0 === g ? 0 : g;
      var m = d(this, g);
      m.list || (m.list = this.h[m.id] = []);
      m.s ? m.s.value = k : (m.s = {
        next: this.g,
        v: this.g.v,
        head: this.g,
        key: g,
        value: k
      }, m.list.push(m.s), this.g.v.next = m.s, this.g.v = m.s, this.size++);
      return this;
    };
    e.prototype["delete"] = function (g) {
      g = d(this, g);
      return g.s && g.list ? (g.list.splice(g.index, 1), g.list.length || delete this.h[g.id], g.s.v.next = g.s.next, g.s.next.v = g.s.v, g.s.head = null, this.size--, !0) : !1;
    };
    e.prototype.clear = function () {
      this.h = {};
      this.g = this.g.v = b();
      this.size = 0;
    };
    e.prototype.has = function (g) {
      return !!d(this, g).s;
    };
    e.prototype.get = function (g) {
      return (g = d(this, g).s) && g.value;
    };
    e.prototype.entries = function () {
      return c(this, function (g) {
        return [g.key, g.value];
      });
    };
    e.prototype.keys = function () {
      return c(this, function (g) {
        return g.key;
      });
    };
    e.prototype.values = function () {
      return c(this, function (g) {
        return g.value;
      });
    };
    e.prototype.forEach = function (g, k) {
      for (var m = this.entries(), n; !(n = m.next()).done;) n = n.value, g.call(k, n[1], n[0], this);
    };
    e.prototype[Symbol.iterator] = e.prototype.entries;
    var f = 0;
    return e;
  });
  function ma(a, b) {
    a instanceof String && (a += "");
    var c = 0,
      d = !1,
      e = {
        next: function next() {
          if (!d && c < a.length) {
            var h = c++;
            return {
              value: b(h, a[h]),
              done: !1
            };
          }
          d = !0;
          return {
            done: !0,
            value: void 0
          };
        }
      };
    e[Symbol.iterator] = function () {
      return e;
    };
    return e;
  }
  p("Array.prototype.values", function (a) {
    return a ? a : function () {
      return ma(this, function (b, c) {
        return c;
      });
    };
  });
  p("Array.prototype.keys", function (a) {
    return a ? a : function () {
      return ma(this, function (b) {
        return b;
      });
    };
  });
  function v(a, b, c) {
    if (null == a) throw new TypeError("The 'this' value for String.prototype." + c + " must not be null or undefined");
    if (b instanceof RegExp) throw new TypeError("First argument to String.prototype." + c + " must not be a regular expression");
    return a + "";
  }
  p("String.prototype.endsWith", function (a) {
    return a ? a : function (b, c) {
      var d = v(this, b, "endsWith");
      void 0 === c && (c = d.length);
      c = Math.max(0, Math.min(c | 0, d.length));
      for (var e = b.length; 0 < e && 0 < c;) if (d[--c] != b[--e]) return !1;
      return 0 >= e;
    };
  });
  function na(a, b, c) {
    a instanceof String && (a = String(a));
    for (var d = a.length, e = 0; e < d; e++) {
      var h = a[e];
      if (b.call(c, h, e, a)) return {
        J: e,
        N: h
      };
    }
    return {
      J: -1,
      N: void 0
    };
  }
  p("Array.prototype.find", function (a) {
    return a ? a : function (b, c) {
      return na(this, b, c).N;
    };
  });
  p("String.prototype.startsWith", function (a) {
    return a ? a : function (b, c) {
      var d = v(this, b, "startsWith"),
        e = d.length,
        h = b.length;
      c = Math.max(0, Math.min(c | 0, d.length));
      for (var f = 0; f < h && c < e;) if (d[c++] != b[f++]) return !1;
      return f >= h;
    };
  });
  p("Number.isFinite", function (a) {
    return a ? a : function (b) {
      return "number" !== typeof b ? !1 : !isNaN(b) && Infinity !== b && -Infinity !== b;
    };
  });
  p("String.prototype.repeat", function (a) {
    return a ? a : function (b) {
      var c = v(this, null, "repeat");
      if (0 > b || 1342177279 < b) throw new RangeError("Invalid count value");
      b |= 0;
      for (var d = ""; b;) if (b & 1 && (d += c), b >>>= 1) c += c;
      return d;
    };
  });
  p("Array.from", function (a) {
    return a ? a : function (b, c, d) {
      c = null != c ? c : function (g) {
        return g;
      };
      var e = [],
        h = "undefined" != typeof Symbol && Symbol.iterator && b[Symbol.iterator];
      if ("function" == typeof h) {
        b = h.call(b);
        for (var f = 0; !(h = b.next()).done;) e.push(c.call(d, h.value, f++));
      } else for (h = b.length, f = 0; f < h; f++) e.push(c.call(d, b[f], f));
      return e;
    };
  });
  p("String.prototype.trimLeft", function (a) {
    function b() {
      return this.replace(/^[\s\xa0]+/, "");
    }
    return a || b;
  });
  p("String.prototype.trimStart", function (a) {
    return a || String.prototype.trimLeft;
  });
  p("Object.is", function (a) {
    return a ? a : function (b, c) {
      return b === c ? 0 !== b || 1 / b === 1 / c : b !== b && c !== c;
    };
  });
  p("Array.prototype.includes", function (a) {
    return a ? a : function (b, c) {
      var d = this;
      d instanceof String && (d = String(d));
      var e = d.length;
      c = c || 0;
      for (0 > c && (c = Math.max(c + e, 0)); c < e; c++) {
        var h = d[c];
        if (h === b || Object.is(h, b)) return !0;
      }
      return !1;
    };
  });
  p("String.prototype.includes", function (a) {
    return a ? a : function (b, c) {
      return -1 !== v(this, b, "includes").indexOf(b, c || 0);
    };
  });
  p("Math.trunc", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      if (isNaN(b) || Infinity === b || -Infinity === b || 0 === b) return b;
      var c = Math.floor(Math.abs(b));
      return 0 > b ? -c : c;
    };
  });
  function oa(a) {
    a = Math.trunc(a) || 0;
    0 > a && (a += this.length);
    if (!(0 > a || a >= this.length)) return this[a];
  }
  p("Array.prototype.at", function (a) {
    return a ? a : oa;
  });
  p("Array.prototype.copyWithin", function (a) {
    function b(c) {
      c = Number(c);
      return Infinity === c || -Infinity === c ? c : c | 0;
    }
    return a ? a : function (c, d, e) {
      var h = this.length;
      c = b(c);
      d = b(d);
      e = void 0 === e ? h : b(e);
      c = 0 > c ? Math.max(h + c, 0) : Math.min(c, h);
      d = 0 > d ? Math.max(h + d, 0) : Math.min(d, h);
      e = 0 > e ? Math.max(h + e, 0) : Math.min(e, h);
      if (c < d) for (; d < e;) d in this ? this[c++] = this[d++] : (delete this[c++], d++);else for (e = Math.min(e, h + d - c), c += e - d; e > d;) --e in this ? this[--c] = this[e] : delete this[--c];
      return this;
    };
  });
  p("Array.prototype.entries", function (a) {
    return a ? a : function () {
      return ma(this, function (b, c) {
        return [b, c];
      });
    };
  });
  p("Array.prototype.fill", function (a) {
    return a ? a : function (b, c, d) {
      var e = this.length || 0;
      0 > c && (c = Math.max(0, e + c));
      if (null == d || d > e) d = e;
      d = Number(d);
      0 > d && (d = Math.max(0, e + d));
      for (c = Number(c || 0); c < d; c++) this[c] = b;
      return this;
    };
  });
  p("Array.prototype.findIndex", function (a) {
    return a ? a : function (b, c) {
      return na(this, b, c).J;
    };
  });
  p("Array.prototype.flat", function (a) {
    return a ? a : function (b) {
      b = void 0 === b ? 1 : b;
      var c = [];
      Array.prototype.forEach.call(this, function (d) {
        Array.isArray(d) && 0 < b ? (d = Array.prototype.flat.call(d, b - 1), c.push.apply(c, d)) : c.push(d);
      });
      return c;
    };
  });
  p("Array.prototype.flatMap", function (a) {
    return a ? a : function (b, c) {
      var d = [];
      Array.prototype.forEach.call(this, function (e, h) {
        e = b.call(c, e, h, this);
        Array.isArray(e) ? d.push.apply(d, e) : d.push(e);
      });
      return d;
    };
  });
  p("Array.of", function (a) {
    return a ? a : function (b) {
      return Array.from(arguments);
    };
  });
  p("globalThis", function (a) {
    return a || l;
  });
  p("Math.acosh", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      return Math.log(b + Math.sqrt(b * b - 1));
    };
  });
  p("Math.asinh", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      if (0 === b) return b;
      var c = Math.log(Math.abs(b) + Math.sqrt(b * b + 1));
      return 0 > b ? -c : c;
    };
  });
  p("Math.log1p", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      if (.25 > b && -.25 < b) {
        for (var c = b, d = 1, e = b, h = 0, f = 1; h != e;) c *= b, f *= -1, e = (h = e) + f * c / ++d;
        return e;
      }
      return Math.log(1 + b);
    };
  });
  p("Math.atanh", function (a) {
    if (a) return a;
    var b = Math.log1p;
    return function (c) {
      c = Number(c);
      return (b(c) - b(-c)) / 2;
    };
  });
  p("Math.cbrt", function (a) {
    return a ? a : function (b) {
      if (0 === b) return b;
      b = Number(b);
      var c = Math.pow(Math.abs(b), 1 / 3);
      return 0 > b ? -c : c;
    };
  });
  p("Math.clz32", function (a) {
    return a ? a : function (b) {
      b = Number(b) >>> 0;
      if (0 === b) return 32;
      var c = 0;
      0 === (b & 4294901760) && (b <<= 16, c += 16);
      0 === (b & 4278190080) && (b <<= 8, c += 8);
      0 === (b & 4026531840) && (b <<= 4, c += 4);
      0 === (b & 3221225472) && (b <<= 2, c += 2);
      0 === (b & 2147483648) && c++;
      return c;
    };
  });
  p("Math.cosh", function (a) {
    if (a) return a;
    var b = Math.exp;
    return function (c) {
      c = Number(c);
      return (b(c) + b(-c)) / 2;
    };
  });
  p("Math.expm1", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      if (.25 > b && -.25 < b) {
        for (var c = b, d = 1, e = b, h = 0; h != e;) c *= b / ++d, e = (h = e) + c;
        return e;
      }
      return Math.exp(b) - 1;
    };
  });
  p("Math.fround", function (a) {
    if (a) return a;
    if ("function" !== typeof Float32Array) return function (c) {
      return c;
    };
    var b = new Float32Array(1);
    return function (c) {
      b[0] = c;
      return b[0];
    };
  });
  p("Math.hypot", function (a) {
    return a ? a : function (b) {
      if (2 > arguments.length) return arguments.length ? Math.abs(arguments[0]) : 0;
      var c, d, e;
      for (c = e = 0; c < arguments.length; c++) e = Math.max(e, Math.abs(arguments[c]));
      if (1E100 < e || 1E-100 > e) {
        if (!e) return e;
        for (c = d = 0; c < arguments.length; c++) {
          var h = Number(arguments[c]) / e;
          d += h * h;
        }
        return Math.sqrt(d) * e;
      }
      for (c = d = 0; c < arguments.length; c++) h = Number(arguments[c]), d += h * h;
      return Math.sqrt(d);
    };
  });
  p("Math.imul", function (a) {
    return a ? a : function (b, c) {
      b = Number(b);
      c = Number(c);
      var d = b & 65535,
        e = c & 65535;
      return d * e + ((b >>> 16 & 65535) * e + d * (c >>> 16 & 65535) << 16 >>> 0) | 0;
    };
  });
  p("Math.log10", function (a) {
    return a ? a : function (b) {
      return Math.log(b) / Math.LN10;
    };
  });
  p("Math.log2", function (a) {
    return a ? a : function (b) {
      return Math.log(b) / Math.LN2;
    };
  });
  p("Math.sign", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      return 0 === b || isNaN(b) ? b : 0 < b ? 1 : -1;
    };
  });
  p("Math.sinh", function (a) {
    if (a) return a;
    var b = Math.exp;
    return function (c) {
      c = Number(c);
      return 0 === c ? c : (b(c) - b(-c)) / 2;
    };
  });
  p("Math.tanh", function (a) {
    return a ? a : function (b) {
      b = Number(b);
      if (0 === b) return b;
      var c = Math.exp(-2 * Math.abs(b));
      c = (1 - c) / (1 + c);
      return 0 > b ? -c : c;
    };
  });
  p("Number.EPSILON", function () {
    return Math.pow(2, -52);
  });
  p("Number.MAX_SAFE_INTEGER", function () {
    return 9007199254740991;
  });
  p("Number.MIN_SAFE_INTEGER", function () {
    return -9007199254740991;
  });
  p("Number.isInteger", function (a) {
    return a ? a : function (b) {
      return Number.isFinite(b) ? b === Math.floor(b) : !1;
    };
  });
  p("Number.isNaN", function (a) {
    return a ? a : function (b) {
      return "number" === typeof b && isNaN(b);
    };
  });
  p("Number.isSafeInteger", function (a) {
    return a ? a : function (b) {
      return Number.isInteger(b) && Math.abs(b) <= Number.MAX_SAFE_INTEGER;
    };
  });
  p("Number.parseFloat", function (a) {
    return a || parseFloat;
  });
  p("Number.parseInt", function (a) {
    return a || parseInt;
  });
  p("Object.entries", function (a) {
    return a ? a : function (b) {
      var c = [],
        d;
      for (d in b) r(b, d) && c.push([d, b[d]]);
      return c;
    };
  });
  p("Object.fromEntries", function (a) {
    return a ? a : function (b) {
      var c = {};
      if (!(Symbol.iterator in b)) throw new TypeError("" + b + " is not iterable");
      b = b[Symbol.iterator].call(b);
      for (var d = b.next(); !d.done; d = b.next()) {
        d = d.value;
        if (Object(d) !== d) throw new TypeError("iterable for fromEntries should yield objects");
        c[d[0]] = d[1];
      }
      return c;
    };
  });
  p("Reflect", function (a) {
    return a ? a : {};
  });
  p("Object.getOwnPropertySymbols", function (a) {
    return a ? a : function () {
      return [];
    };
  });
  p("Reflect.ownKeys", function (a) {
    return a ? a : function (b) {
      var c = [],
        d = Object.getOwnPropertyNames(b);
      b = Object.getOwnPropertySymbols(b);
      for (var e = 0; e < d.length; e++) ("jscomp_symbol_" == d[e].substring(0, 14) ? b : c).push(d[e]);
      return c.concat(b);
    };
  });
  p("Object.getOwnPropertyDescriptors", function (a) {
    return a ? a : function (b) {
      for (var c = {}, d = Reflect.ownKeys(b), e = 0; e < d.length; e++) c[d[e]] = Object.getOwnPropertyDescriptor(b, d[e]);
      return c;
    };
  });
  p("Object.values", function (a) {
    return a ? a : function (b) {
      var c = [],
        d;
      for (d in b) r(b, d) && c.push(b[d]);
      return c;
    };
  });
  p("Object.hasOwn", function (a) {
    return a ? a : function (b, c) {
      return Object.prototype.hasOwnProperty.call(b, c);
    };
  });
  p("Promise.allSettled", function (a) {
    function b(d) {
      return {
        status: "fulfilled",
        value: d
      };
    }
    function c(d) {
      return {
        status: "rejected",
        reason: d
      };
    }
    return a ? a : function (d) {
      var e = this;
      d = Array.from(d, function (h) {
        return e.resolve(h).then(b, c);
      });
      return e.all(d);
    };
  });
  p("Promise.prototype.finally", function (a) {
    return a ? a : function (b) {
      return this.then(function (c) {
        return Promise.resolve(b()).then(function () {
          return c;
        });
      }, function (c) {
        return Promise.resolve(b()).then(function () {
          throw c;
        });
      });
    };
  });
  var pa = "function" == typeof Object.create ? Object.create : function (a) {
    function b() {}
    b.prototype = a;
    return new b();
  };
  function qa(a, b) {
    a.prototype = pa(b.prototype);
    a.prototype.constructor = a;
    if (t) t(a, b);else for (var c in b) if ("prototype" != c) if (Object.defineProperties) {
      var d = Object.getOwnPropertyDescriptor(b, c);
      d && Object.defineProperty(a, c, d);
    } else a[c] = b[c];
    a.T = b.prototype;
  }
  p("AggregateError", function (a) {
    function b(c, d) {
      d = Error(d);
      "stack" in d && (this.stack = d.stack);
      this.errors = c;
      this.message = d.message;
    }
    if (a) return a;
    qa(b, Error);
    b.prototype.name = "AggregateError";
    return b;
  });
  p("Promise.any", function (a) {
    return a ? a : function (b) {
      b = b instanceof Array ? b : Array.from(b);
      return Promise.all(b.map(function (c) {
        return Promise.resolve(c).then(function (d) {
          throw d;
        }, function (d) {
          return d;
        });
      })).then(function (c) {
        throw new AggregateError(c, "All promises were rejected");
      }, function (c) {
        return c;
      });
    };
  });
  p("Reflect.apply", function (a) {
    if (a) return a;
    var b = Function.prototype.apply;
    return function (c, d, e) {
      return b.call(c, d, e);
    };
  });
  var ta = function () {
    function a() {
      function c() {}
      new c();
      Reflect.construct(c, [], function () {});
      return new c() instanceof c;
    }
    if ("undefined" != typeof Reflect && Reflect.construct) {
      if (a()) return Reflect.construct;
      var b = Reflect.construct;
      return function (c, d, e) {
        c = b(c, d);
        e && Reflect.setPrototypeOf(c, e.prototype);
        return c;
      };
    }
    return function (c, d, e) {
      void 0 === e && (e = c);
      e = pa(e.prototype || Object.prototype);
      return Function.prototype.apply.call(c, e, d) || e;
    };
  }();
  p("Reflect.construct", function () {
    return ta;
  });
  p("Reflect.defineProperty", function (a) {
    return a ? a : function (b, c, d) {
      try {
        Object.defineProperty(b, c, d);
        var e = Object.getOwnPropertyDescriptor(b, c);
        return e ? e.configurable === (d.configurable || !1) && e.enumerable === (d.enumerable || !1) && ("value" in e ? e.value === d.value && e.writable === (d.writable || !1) : e.get === d.get && e.set === d.set) : !1;
      } catch (h) {
        return !1;
      }
    };
  });
  p("Reflect.deleteProperty", function (a) {
    return a ? a : function (b, c) {
      if (!r(b, c)) return !0;
      try {
        return delete b[c];
      } catch (d) {
        return !1;
      }
    };
  });
  p("Reflect.getOwnPropertyDescriptor", function (a) {
    return a || Object.getOwnPropertyDescriptor;
  });
  p("Reflect.getPrototypeOf", function (a) {
    return a || Object.getPrototypeOf;
  });
  function ua(a, b) {
    for (; a;) {
      var c = Reflect.getOwnPropertyDescriptor(a, b);
      if (c) return c;
      a = Reflect.getPrototypeOf(a);
    }
  }
  p("Reflect.get", function (a) {
    return a ? a : function (b, c, d) {
      if (2 >= arguments.length) return b[c];
      var e = ua(b, c);
      if (e) return e.get ? e.get.call(d) : e.value;
    };
  });
  p("Reflect.has", function (a) {
    return a ? a : function (b, c) {
      return c in b;
    };
  });
  p("Reflect.isExtensible", function (a) {
    return a ? a : "function" == typeof Object.isExtensible ? Object.isExtensible : function () {
      return !0;
    };
  });
  p("Reflect.preventExtensions", function (a) {
    return a ? a : "function" != typeof Object.preventExtensions ? function () {
      return !1;
    } : function (b) {
      Object.preventExtensions(b);
      return !Object.isExtensible(b);
    };
  });
  p("Reflect.set", function (a) {
    return a ? a : function (b, c, d, e) {
      var h = ua(b, c);
      return h ? h.set ? (h.set.call(3 < arguments.length ? e : b, d), !0) : h.writable && !Object.isFrozen(b) ? (b[c] = d, !0) : !1 : Reflect.isExtensible(b) ? (b[c] = d, !0) : !1;
    };
  });
  p("Reflect.setPrototypeOf", function (a) {
    return a ? a : t ? function (b, c) {
      try {
        return t(b, c), !0;
      } catch (d) {
        return !1;
      }
    } : null;
  });
  p("Set", function (a) {
    function b(c) {
      this.g = new Map();
      if (c) {
        c = q(c);
        for (var d; !(d = c.next()).done;) this.add(d.value);
      }
      this.size = this.g.size;
    }
    if (function () {
      if (!a || "function" != typeof a || !a.prototype.entries || "function" != typeof Object.seal) return !1;
      try {
        var c = Object.seal({
            x: 4
          }),
          d = new a(q([c]));
        if (!d.has(c) || 1 != d.size || d.add(c) != d || 1 != d.size || d.add({
          x: 4
        }) != d || 2 != d.size) return !1;
        var e = d.entries(),
          h = e.next();
        if (h.done || h.value[0] != c || h.value[1] != c) return !1;
        h = e.next();
        return h.done || h.value[0] == c || 4 != h.value[0].x || h.value[1] != h.value[0] ? !1 : e.next().done;
      } catch (f) {
        return !1;
      }
    }()) return a;
    b.prototype.add = function (c) {
      c = 0 === c ? 0 : c;
      this.g.set(c, c);
      this.size = this.g.size;
      return this;
    };
    b.prototype["delete"] = function (c) {
      c = this.g["delete"](c);
      this.size = this.g.size;
      return c;
    };
    b.prototype.clear = function () {
      this.g.clear();
      this.size = 0;
    };
    b.prototype.has = function (c) {
      return this.g.has(c);
    };
    b.prototype.entries = function () {
      return this.g.entries();
    };
    b.prototype.values = function () {
      return this.g.values();
    };
    b.prototype.keys = b.prototype.values;
    b.prototype[Symbol.iterator] = b.prototype.values;
    b.prototype.forEach = function (c, d) {
      var e = this;
      this.g.forEach(function (h) {
        return c.call(d, h, h, e);
      });
    };
    return b;
  });
  p("String.prototype.at", function (a) {
    return a ? a : oa;
  });
  p("String.prototype.codePointAt", function (a) {
    return a ? a : function (b) {
      var c = v(this, null, "codePointAt"),
        d = c.length;
      b = Number(b) || 0;
      if (0 <= b && b < d) {
        b |= 0;
        var e = c.charCodeAt(b);
        if (55296 > e || 56319 < e || b + 1 === d) return e;
        b = c.charCodeAt(b + 1);
        return 56320 > b || 57343 < b ? e : 1024 * (e - 55296) + b + 9216;
      }
    };
  });
  p("String.fromCodePoint", function (a) {
    return a ? a : function (b) {
      for (var c = "", d = 0; d < arguments.length; d++) {
        var e = Number(arguments[d]);
        if (0 > e || 1114111 < e || e !== Math.floor(e)) throw new RangeError("invalid_code_point " + e);
        65535 >= e ? c += String.fromCharCode(e) : (e -= 65536, c += String.fromCharCode(e >>> 10 & 1023 | 55296), c += String.fromCharCode(e & 1023 | 56320));
      }
      return c;
    };
  });
  p("String.prototype.matchAll", function (a) {
    return a ? a : function (b) {
      if (b instanceof RegExp && !b.global) throw new TypeError("RegExp passed into String.prototype.matchAll() must have global tag.");
      var c = new RegExp(b, b instanceof RegExp ? void 0 : "g"),
        d = this,
        e = !1,
        h = {
          next: function next() {
            if (e) return {
              value: void 0,
              done: !0
            };
            var f = c.exec(d);
            if (!f) return e = !0, {
              value: void 0,
              done: !0
            };
            "" === f[0] && (c.lastIndex += 1);
            return {
              value: f,
              done: !1
            };
          }
        };
      h[Symbol.iterator] = function () {
        return h;
      };
      return h;
    };
  });
  function va(a, b) {
    a = void 0 !== a ? String(a) : " ";
    return 0 < b && a ? a.repeat(Math.ceil(b / a.length)).substring(0, b) : "";
  }
  p("String.prototype.padEnd", function (a) {
    return a ? a : function (b, c) {
      var d = v(this, null, "padStart");
      return d + va(c, b - d.length);
    };
  });
  p("String.prototype.padStart", function (a) {
    return a ? a : function (b, c) {
      var d = v(this, null, "padStart");
      return va(c, b - d.length) + d;
    };
  });
  p("String.raw", function (a) {
    return a ? a : function (b, c) {
      if (null == b) throw new TypeError("Cannot convert undefined or null to object");
      for (var d = b.raw, e = d.length, h = "", f = 0; f < e; ++f) h += d[f], f + 1 < e && f + 1 < arguments.length && (h += String(arguments[f + 1]));
      return h;
    };
  });
  p("String.prototype.replaceAll", function (a) {
    return a ? a : function (b, c) {
      if (b instanceof RegExp && !b.global) throw new TypeError("String.prototype.replaceAll called with a non-global RegExp argument.");
      return b instanceof RegExp ? this.replace(b, c) : this.replace(new RegExp(String(b).replace(/([-()\[\]{}+?*.$\^|,:#<!\\])/g, "\\$1").replace(/\x08/g, "\\x08"), "g"), c);
    };
  });
  p("String.prototype.trimRight", function (a) {
    function b() {
      return this.replace(/[\s\xa0]+$/, "");
    }
    return a || b;
  });
  p("String.prototype.trimEnd", function (a) {
    return a || String.prototype.trimRight;
  });
  function w(a) {
    return a ? a : oa;
  }
  p("Int8Array.prototype.at", w);
  p("Uint8Array.prototype.at", w);
  p("Uint8ClampedArray.prototype.at", w);
  p("Int16Array.prototype.at", w);
  p("Uint16Array.prototype.at", w);
  p("Int32Array.prototype.at", w);
  p("Uint32Array.prototype.at", w);
  p("Float32Array.prototype.at", w);
  p("Float64Array.prototype.at", w);
  function x(a) {
    return a ? a : Array.prototype.copyWithin;
  }
  p("Int8Array.prototype.copyWithin", x);
  p("Uint8Array.prototype.copyWithin", x);
  p("Uint8ClampedArray.prototype.copyWithin", x);
  p("Int16Array.prototype.copyWithin", x);
  p("Uint16Array.prototype.copyWithin", x);
  p("Int32Array.prototype.copyWithin", x);
  p("Uint32Array.prototype.copyWithin", x);
  p("Float32Array.prototype.copyWithin", x);
  p("Float64Array.prototype.copyWithin", x);
  function z(a) {
    return a ? a : Array.prototype.fill;
  }
  p("Int8Array.prototype.fill", z);
  p("Uint8Array.prototype.fill", z);
  p("Uint8ClampedArray.prototype.fill", z);
  p("Int16Array.prototype.fill", z);
  p("Uint16Array.prototype.fill", z);
  p("Int32Array.prototype.fill", z);
  p("Uint32Array.prototype.fill", z);
  p("Float32Array.prototype.fill", z);
  p("Float64Array.prototype.fill", z);
  p("WeakSet", function (a) {
    function b(c) {
      this.g = new WeakMap();
      if (c) {
        c = q(c);
        for (var d; !(d = c.next()).done;) this.add(d.value);
      }
    }
    if (function () {
      if (!a || !Object.seal) return !1;
      try {
        var c = Object.seal({}),
          d = Object.seal({}),
          e = new a([c]);
        if (!e.has(c) || e.has(d)) return !1;
        e["delete"](c);
        e.add(d);
        return !e.has(c) && e.has(d);
      } catch (h) {
        return !1;
      }
    }()) return a;
    b.prototype.add = function (c) {
      this.g.set(c, !0);
      return this;
    };
    b.prototype.has = function (c) {
      return this.g.has(c);
    };
    b.prototype["delete"] = function (c) {
      return this.g["delete"](c);
    };
    return b;
  });
  var A = this || self;
  function B(a) {
    a = a.split(".");
    for (var b = A, c = 0; c < a.length; c++) if (b = b[a[c]], null == b) return null;
    return b;
  }
  function wa(a) {
    var b = _typeof(a);
    return "object" == b && null != a || "function" == b;
  }
  function xa(a, b, c) {
    return a.call.apply(a.bind, arguments);
  }
  function ya(a, b, c) {
    if (!a) throw Error();
    if (2 < arguments.length) {
      var d = Array.prototype.slice.call(arguments, 2);
      return function () {
        var e = Array.prototype.slice.call(arguments);
        Array.prototype.unshift.apply(e, d);
        return a.apply(b, e);
      };
    }
    return function () {
      return a.apply(b, arguments);
    };
  }
  function C(a, b, c) {
    Function.prototype.bind && -1 != Function.prototype.bind.toString().indexOf("native code") ? C = xa : C = ya;
    return C.apply(null, arguments);
  }
  function D(a, b) {
    a = a.split(".");
    var c = A;
    a[0] in c || "undefined" == typeof c.execScript || c.execScript("var " + a[0]);
    for (var d; a.length && (d = a.shift());) a.length || void 0 === b ? c[d] && c[d] !== Object.prototype[d] ? c = c[d] : c = c[d] = {} : c[d] = b;
  }
  function E(a, b) {
    function c() {}
    c.prototype = b.prototype;
    a.T = b.prototype;
    a.prototype = new c();
    a.prototype.constructor = a;
    a.U = function (d, e, h) {
      for (var f = Array(arguments.length - 2), g = 2; g < arguments.length; g++) f[g - 2] = arguments[g];
      return b.prototype[e].apply(d, f);
    };
  }
  function za(a) {
    return a;
  }
  ;
  function F(a, b) {
    if (Error.captureStackTrace) Error.captureStackTrace(this, F);else {
      var c = Error().stack;
      c && (this.stack = c);
    }
    a && (this.message = String(a));
    void 0 !== b && (this.cause = b);
  }
  E(F, Error);
  F.prototype.name = "CustomError";
  function H(a, b) {
    this.g = a === Aa && b || "";
    this.h = Ba;
  }
  H.prototype.K = !0;
  H.prototype.I = function () {
    return this.g;
  };
  function Ca(a) {
    return a instanceof H && a.constructor === H && a.h === Ba ? a.g : "type_error:Const";
  }
  function I(a) {
    return new H(Aa, a);
  }
  var Ba = {},
    Aa = {};
  var Da = {
      "gstatic.com": {
        loader: I("https://www.gstatic.com/charts/%{version}/loader.js"),
        debug: I("https://www.gstatic.com/charts/debug/%{version}/js/jsapi_debug_%{package}_module.js"),
        debug_i18n: I("https://www.gstatic.com/charts/debug/%{version}/i18n/jsapi_debug_i18n_%{package}_module__%{language}.js"),
        compiled: I("https://www.gstatic.com/charts/%{version}/js/jsapi_compiled_%{package}_module.js"),
        compiled_i18n: I("https://www.gstatic.com/charts/%{version}/i18n/jsapi_compiled_i18n_%{package}_module__%{language}.js"),
        css: I("https://www.gstatic.com/charts/%{version}/css/%{subdir}/%{filename}"),
        css2: I("https://www.gstatic.com/charts/%{version}/css/%{subdir1}/%{subdir2}/%{filename}"),
        third_party: I("https://www.gstatic.com/charts/%{version}/third_party/%{subdir}/%{filename}"),
        third_party2: I("https://www.gstatic.com/charts/%{version}/third_party/%{subdir1}/%{subdir2}/%{filename}"),
        third_party_gen: I("https://www.gstatic.com/charts/%{version}/third_party/%{subdir}/%{filename}")
      },
      "gstatic.cn": {
        loader: I("https://www.gstatic.cn/charts/%{version}/loader.js"),
        debug: I("https://www.gstatic.cn/charts/debug/%{version}/js/jsapi_debug_%{package}_module.js"),
        debug_i18n: I("https://www.gstatic.cn/charts/debug/%{version}/i18n/jsapi_debug_i18n_%{package}_module__%{language}.js"),
        compiled: I("https://www.gstatic.cn/charts/%{version}/js/jsapi_compiled_%{package}_module.js"),
        compiled_i18n: I("https://www.gstatic.cn/charts/%{version}/i18n/jsapi_compiled_i18n_%{package}_module__%{language}.js"),
        css: I("https://www.gstatic.cn/charts/%{version}/css/%{subdir}/%{filename}"),
        css2: I("https://www.gstatic.cn/charts/%{version}/css/%{subdir1}/%{subdir2}/%{filename}"),
        third_party: I("https://www.gstatic.cn/charts/%{version}/third_party/%{subdir}/%{filename}"),
        third_party2: I("https://www.gstatic.cn/charts/%{version}/third_party/%{subdir1}/%{subdir2}/%{filename}"),
        third_party_gen: I("https://www.gstatic.cn/charts/%{version}/third_party/%{subdir}/%{filename}")
      }
    },
    Ea = ["default"];
  var Fa = {
    "chrome-frame": {
      versions: {
        "1.0.0": {
          uncompressed: "CFInstall.js",
          compressed: "CFInstall.min.js"
        },
        "1.0.1": {
          uncompressed: "CFInstall.js",
          compressed: "CFInstall.min.js"
        },
        "1.0.2": {
          uncompressed: "CFInstall.js",
          compressed: "CFInstall.min.js"
        }
      },
      aliases: {
        1: "1.0.2",
        "1.0": "1.0.2"
      }
    },
    swfobject: {
      versions: {
        "2.1": {
          uncompressed: "swfobject_src.js",
          compressed: "swfobject.js"
        },
        "2.2": {
          uncompressed: "swfobject_src.js",
          compressed: "swfobject.js"
        }
      },
      aliases: {
        2: "2.2"
      }
    },
    "ext-core": {
      versions: {
        "3.1.0": {
          uncompressed: "ext-core-debug.js",
          compressed: "ext-core.js"
        },
        "3.0.0": {
          uncompressed: "ext-core-debug.js",
          compressed: "ext-core.js"
        }
      },
      aliases: {
        3: "3.1.0",
        "3.0": "3.0.0",
        "3.1": "3.1.0"
      }
    },
    scriptaculous: {
      versions: {
        "1.8.3": {
          uncompressed: "scriptaculous.js",
          compressed: "scriptaculous.js"
        },
        "1.9.0": {
          uncompressed: "scriptaculous.js",
          compressed: "scriptaculous.js"
        },
        "1.8.1": {
          uncompressed: "scriptaculous.js",
          compressed: "scriptaculous.js"
        },
        "1.8.2": {
          uncompressed: "scriptaculous.js",
          compressed: "scriptaculous.js"
        }
      },
      aliases: {
        1: "1.9.0",
        "1.8": "1.8.3",
        "1.9": "1.9.0"
      }
    },
    webfont: {
      versions: {
        "1.0.12": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.13": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.14": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.15": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.10": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.11": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.27": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.28": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.29": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.23": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.24": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.25": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.26": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.21": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.22": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.3": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.4": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.5": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.6": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.9": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.16": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.17": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.0": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.18": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.1": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.19": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        },
        "1.0.2": {
          uncompressed: "webfont_debug.js",
          compressed: "webfont.js"
        }
      },
      aliases: {
        1: "1.0.29",
        "1.0": "1.0.29"
      }
    },
    jqueryui: {
      versions: {
        "1.8.17": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.16": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.15": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.14": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.4": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.13": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.5": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.12": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.6": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.11": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.7": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.10": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.8": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.9": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.6.0": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.7.0": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.5.2": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.0": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.7.1": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.5.3": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.1": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.7.2": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.8.2": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        },
        "1.7.3": {
          uncompressed: "jquery-ui.js",
          compressed: "jquery-ui.min.js"
        }
      },
      aliases: {
        1: "1.8.17",
        "1.5": "1.5.3",
        "1.6": "1.6.0",
        "1.7": "1.7.3",
        "1.8": "1.8.17",
        "1.8.3": "1.8.4"
      }
    },
    mootools: {
      versions: {
        "1.3.0": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.2.1": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.1.2": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.4.0": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.3.1": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.2.2": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.4.1": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.3.2": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.2.3": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.4.2": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.2.4": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.2.5": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        },
        "1.1.1": {
          uncompressed: "mootools.js",
          compressed: "mootools-yui-compressed.js"
        }
      },
      aliases: {
        1: "1.1.2",
        "1.1": "1.1.2",
        "1.2": "1.2.5",
        "1.3": "1.3.2",
        "1.4": "1.4.2",
        "1.11": "1.1.1"
      }
    },
    yui: {
      versions: {
        "2.8.0r4": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        },
        "2.9.0": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        },
        "2.8.1": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        },
        "2.6.0": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        },
        "2.7.0": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        },
        "3.3.0": {
          uncompressed: "build/yui/yui.js",
          compressed: "build/yui/yui-min.js"
        },
        "2.8.2r1": {
          uncompressed: "build/yuiloader/yuiloader.js",
          compressed: "build/yuiloader/yuiloader-min.js"
        }
      },
      aliases: {
        2: "2.9.0",
        "2.6": "2.6.0",
        "2.7": "2.7.0",
        "2.8": "2.8.2r1",
        "2.8.0": "2.8.0r4",
        "2.8.2": "2.8.2r1",
        "2.9": "2.9.0",
        3: "3.3.0",
        "3.3": "3.3.0"
      }
    },
    prototype: {
      versions: {
        "1.6.1.0": {
          uncompressed: "prototype.js",
          compressed: "prototype.js"
        },
        "1.6.0.2": {
          uncompressed: "prototype.js",
          compressed: "prototype.js"
        },
        "1.7.0.0": {
          uncompressed: "prototype.js",
          compressed: "prototype.js"
        },
        "1.6.0.3": {
          uncompressed: "prototype.js",
          compressed: "prototype.js"
        }
      },
      aliases: {
        1: "1.7.0.0",
        "1.6": "1.6.1.0",
        "1.6.0": "1.6.0.3",
        "1.6.1": "1.6.1.0",
        "1.7": "1.7.0.0",
        "1.7.0": "1.7.0.0"
      }
    },
    jquery: {
      versions: {
        "1.2.3": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.2.6": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.3.0": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.3.1": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.3.2": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.4.0": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.4.1": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.4.2": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.4.3": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.4.4": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.5.0": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.5.1": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.5.2": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.6.0": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.6.1": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.6.2": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.6.3": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.6.4": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.7.0": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        },
        "1.7.1": {
          uncompressed: "jquery.js",
          compressed: "jquery.min.js"
        }
      },
      aliases: {
        1: "1.7.1",
        "1.2": "1.2.6",
        "1.3": "1.3.2",
        "1.4": "1.4.4",
        "1.5": "1.5.2",
        "1.6": "1.6.4",
        "1.7": "1.7.1"
      }
    },
    dojo: {
      versions: {
        "1.3.0": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.4.0": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.3.1": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.5.0": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.4.1": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.3.2": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.2.3": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.6.0": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.5.1": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.7.0": {
          uncompressed: "dojo/dojo.js.uncompressed.js",
          compressed: "dojo/dojo.js"
        },
        "1.6.1": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.4.3": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.7.1": {
          uncompressed: "dojo/dojo.js.uncompressed.js",
          compressed: "dojo/dojo.js"
        },
        "1.7.2": {
          uncompressed: "dojo/dojo.js.uncompressed.js",
          compressed: "dojo/dojo.js"
        },
        "1.2.0": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        },
        "1.1.1": {
          uncompressed: "dojo/dojo.xd.js.uncompressed.js",
          compressed: "dojo/dojo.xd.js"
        }
      },
      aliases: {
        1: "1.6.1",
        "1.1": "1.1.1",
        "1.2": "1.2.3",
        "1.3": "1.3.2",
        "1.4": "1.4.3",
        "1.5": "1.5.1",
        "1.6": "1.6.1",
        "1.7": "1.7.2"
      }
    }
  };
  var Ga = {
    af: !0,
    am: !0,
    az: !0,
    ar: !0,
    arb: "ar",
    bg: !0,
    bn: !0,
    ca: !0,
    cs: !0,
    cmn: "zh",
    da: !0,
    de: !0,
    el: !0,
    en: !0,
    en_gb: !0,
    es: !0,
    es_419: !0,
    et: !0,
    eu: !0,
    fa: !0,
    fi: !0,
    fil: !0,
    fr: !0,
    fr_ca: !0,
    gl: !0,
    ka: !0,
    gu: !0,
    he: "iw",
    hi: !0,
    hr: !0,
    hu: !0,
    hy: !0,
    id: !0,
    "in": "id",
    is: !0,
    it: !0,
    iw: !0,
    ja: !0,
    ji: "yi",
    jv: !1,
    jw: "jv",
    km: !0,
    kn: !0,
    ko: !0,
    lo: !0,
    lt: !0,
    lv: !0,
    ml: !0,
    mn: !0,
    mo: "ro",
    mr: !0,
    ms: !0,
    nb: "no",
    ne: !0,
    nl: !0,
    no: !0,
    pl: !0,
    pt: "pt_br",
    pt_br: !0,
    pt_pt: !0,
    ro: !0,
    ru: !0,
    si: !0,
    sk: !0,
    sl: !0,
    sr: !0,
    sv: !0,
    sw: !0,
    swh: "sw",
    ta: !0,
    te: !0,
    th: !0,
    tl: "fil",
    tr: !0,
    uk: !0,
    ur: !0,
    vi: !0,
    yi: !1,
    zh: "zh_cn",
    zh_cn: !0,
    zh_hk: !0,
    zh_tw: !0,
    zsm: "ms",
    zu: !0
  };
  var Ha = {
    1: "1.0",
    "1.0": "current",
    "1.1": "upcoming",
    "1.2": "testing",
    41: "pre-45",
    42: "pre-45",
    43: "pre-45",
    44: "pre-45",
    46: "46.1",
    "46.1": "46.2",
    48: "48.1",
    current: "51",
    upcoming: "52"
  };
  var Ia;
  function J(a, b) {
    this.g = b === Ja ? a : "";
  }
  J.prototype.toString = function () {
    return this.g + "";
  };
  J.prototype.K = !0;
  J.prototype.I = function () {
    return this.g.toString();
  };
  function Ka(a) {
    return a instanceof J && a.constructor === J ? a.g : "type_error:TrustedResourceUrl";
  }
  function La(a, b) {
    var c = Ca(a);
    if (!Ma.test(c)) throw Error("Invalid TrustedResourceUrl format: " + c);
    a = c.replace(Na, function (d, e) {
      if (!Object.prototype.hasOwnProperty.call(b, e)) throw Error('Found marker, "' + e + '", in format string, "' + c + '", but no valid label mapping found in args: ' + JSON.stringify(b));
      d = b[e];
      return d instanceof H ? Ca(d) : encodeURIComponent(String(d));
    });
    return Oa(a);
  }
  var Na = /%{(\w+)}/g,
    Ma = RegExp("^((https:)?//[0-9a-z.:[\\]-]+/|/[^/\\\\]|[^:/\\\\%]+/|[^:/\\\\%]*[?#]|about:blank#)", "i"),
    Pa = /^([^?#]*)(\?[^#]*)?(#[\s\S]*)?/;
  function Qa(a, b, c) {
    a = La(a, b);
    a = Pa.exec(Ka(a).toString());
    b = a[3] || "";
    return Oa(a[1] + Ra("?", a[2] || "", c) + Ra("#", b));
  }
  var Ja = {};
  function Oa(a) {
    if (void 0 === Ia) {
      var b = null;
      var c = A.trustedTypes;
      if (c && c.createPolicy) {
        try {
          b = c.createPolicy("goog#html", {
            createHTML: za,
            createScript: za,
            createScriptURL: za
          });
        } catch (d) {
          A.console && A.console.error(d.message);
        }
        Ia = b;
      } else Ia = b;
    }
    a = (b = Ia) ? b.createScriptURL(a) : a;
    return new J(a, Ja);
  }
  function Ra(a, b, c) {
    if (null == c) return b;
    if ("string" === typeof c) return c ? a + encodeURIComponent(c) : "";
    for (var d in c) if (Object.prototype.hasOwnProperty.call(c, d)) {
      var e = c[d];
      e = Array.isArray(e) ? e : [e];
      for (var h = 0; h < e.length; h++) {
        var f = e[h];
        null != f && (b || (b = a), b += (b.length > a.length ? "&" : "") + encodeURIComponent(d) + "=" + encodeURIComponent(String(f)));
      }
    }
    return b;
  }
  ;
  var Sa = Array.prototype.some ? function (a, b) {
    return Array.prototype.some.call(a, b, void 0);
  } : function (a, b) {
    for (var c = a.length, d = "string" === typeof a ? a.split("") : a, e = 0; e < c; e++) if (e in d && b.call(void 0, d[e], e, a)) return !0;
    return !1;
  };
  function Ta() {}
  ;
  function Ua(a, b) {
    for (var c in a) b.call(void 0, a[c], c, a);
  }
  var Va = "constructor hasOwnProperty isPrototypeOf propertyIsEnumerable toLocaleString toString valueOf".split(" ");
  function Wa(a, b) {
    for (var c, d, e = 1; e < arguments.length; e++) {
      d = arguments[e];
      for (c in d) a[c] = d[c];
      for (var h = 0; h < Va.length; h++) c = Va[h], Object.prototype.hasOwnProperty.call(d, c) && (a[c] = d[c]);
    }
  }
  ;
  var Xa,
    Ya = B("CLOSURE_FLAGS"),
    Za = Ya && Ya[610401301];
  Xa = null != Za ? Za : !1;
  function $a() {
    var a = A.navigator;
    return a && (a = a.userAgent) ? a : "";
  }
  var K,
    ab = A.navigator;
  K = ab ? ab.userAgentData || null : null;
  function bb(a, b) {
    a: {
      var c = (a.ownerDocument && a.ownerDocument.defaultView || A).document;
      if (c.querySelector && (c = c.querySelector("script[nonce]")) && (c = c.nonce || c.getAttribute("nonce")) && cb.test(c)) break a;
      c = "";
    }
    c && a.setAttribute("nonce", c);
    a.src = Ka(b);
  }
  var cb = /^[\w+/_-]+[=]{0,2}$/;
  var db = RegExp("^(?:([^:/?#.]+):)?(?://(?:([^\\\\/?#]*)@)?([^\\\\/?#]*?)(?::([0-9]+))?(?=[\\\\/?#]|$))?([^?#]+)?(?:\\?([^#]*))?(?:#([\\s\\S]*))?$");
  function eb(a, b) {
    if (a) {
      a = a.split("&");
      for (var c = 0; c < a.length; c++) {
        var d = a[c].indexOf("="),
          e = null;
        if (0 <= d) {
          var h = a[c].substring(0, d);
          e = a[c].substring(d + 1);
        } else h = a[c];
        b(h, e ? decodeURIComponent(e.replace(/\+/g, " ")) : "");
      }
    }
  }
  ;
  function L(a) {
    this.g = this.o = this.j = "";
    this.u = null;
    this.m = this.h = "";
    this.l = !1;
    var b;
    a instanceof L ? (this.l = a.l, fb(this, a.j), this.o = a.o, this.g = a.g, gb(this, a.u), this.h = a.h, hb(this, ib(a.i)), this.m = a.m) : a && (b = String(a).match(db)) ? (this.l = !1, fb(this, b[1] || "", !0), this.o = M(b[2] || ""), this.g = M(b[3] || "", !0), gb(this, b[4]), this.h = M(b[5] || "", !0), hb(this, b[6] || "", !0), this.m = M(b[7] || "")) : (this.l = !1, this.i = new N(null, this.l));
  }
  L.prototype.toString = function () {
    var a = [],
      b = this.j;
    b && a.push(O(b, jb, !0), ":");
    var c = this.g;
    if (c || "file" == b) a.push("//"), (b = this.o) && a.push(O(b, jb, !0), "@"), a.push(encodeURIComponent(String(c)).replace(/%25([0-9a-fA-F]{2})/g, "%$1")), c = this.u, null != c && a.push(":", String(c));
    if (c = this.h) this.g && "/" != c.charAt(0) && a.push("/"), a.push(O(c, "/" == c.charAt(0) ? kb : lb, !0));
    (c = this.i.toString()) && a.push("?", c);
    (c = this.m) && a.push("#", O(c, mb));
    return a.join("");
  };
  L.prototype.resolve = function (a) {
    var b = new L(this),
      c = !!a.j;
    c ? fb(b, a.j) : c = !!a.o;
    c ? b.o = a.o : c = !!a.g;
    c ? b.g = a.g : c = null != a.u;
    var d = a.h;
    if (c) gb(b, a.u);else if (c = !!a.h) {
      if ("/" != d.charAt(0)) if (this.g && !this.h) d = "/" + d;else {
        var e = b.h.lastIndexOf("/");
        -1 != e && (d = b.h.slice(0, e + 1) + d);
      }
      e = d;
      if (".." == e || "." == e) d = "";else if (-1 != e.indexOf("./") || -1 != e.indexOf("/.")) {
        d = 0 == e.lastIndexOf("/", 0);
        e = e.split("/");
        for (var h = [], f = 0; f < e.length;) {
          var g = e[f++];
          "." == g ? d && f == e.length && h.push("") : ".." == g ? ((1 < h.length || 1 == h.length && "" != h[0]) && h.pop(), d && f == e.length && h.push("")) : (h.push(g), d = !0);
        }
        d = h.join("/");
      } else d = e;
    }
    c ? b.h = d : c = "" !== a.i.toString();
    c ? hb(b, ib(a.i)) : c = !!a.m;
    c && (b.m = a.m);
    return b;
  };
  function fb(a, b, c) {
    a.j = c ? M(b, !0) : b;
    a.j && (a.j = a.j.replace(/:$/, ""));
  }
  function gb(a, b) {
    if (b) {
      b = Number(b);
      if (isNaN(b) || 0 > b) throw Error("Bad port number " + b);
      a.u = b;
    } else a.u = null;
  }
  function hb(a, b, c) {
    b instanceof N ? (a.i = b, nb(a.i, a.l)) : (c || (b = O(b, ob)), a.i = new N(b, a.l));
  }
  function M(a, b) {
    return a ? b ? decodeURI(a.replace(/%25/g, "%2525")) : decodeURIComponent(a) : "";
  }
  function O(a, b, c) {
    return "string" === typeof a ? (a = encodeURI(a).replace(b, pb), c && (a = a.replace(/%25([0-9a-fA-F]{2})/g, "%$1")), a) : null;
  }
  function pb(a) {
    a = a.charCodeAt(0);
    return "%" + (a >> 4 & 15).toString(16) + (a & 15).toString(16);
  }
  var jb = /[#\/\?@]/g,
    lb = /[#\?:]/g,
    kb = /[#\?]/g,
    ob = /[#\?@]/g,
    mb = /#/g;
  function N(a, b) {
    this.h = this.g = null;
    this.i = a || null;
    this.j = !!b;
  }
  function P(a) {
    a.g || (a.g = new Map(), a.h = 0, a.i && eb(a.i, function (b, c) {
      a.add(decodeURIComponent(b.replace(/\+/g, " ")), c);
    }));
  }
  N.prototype.add = function (a, b) {
    P(this);
    this.i = null;
    a = Q(this, a);
    var c = this.g.get(a);
    c || this.g.set(a, c = []);
    c.push(b);
    this.h += 1;
    return this;
  };
  function qb(a, b) {
    P(a);
    b = Q(a, b);
    a.g.has(b) && (a.i = null, a.h -= a.g.get(b).length, a.g["delete"](b));
  }
  function rb(a, b) {
    P(a);
    b = Q(a, b);
    return a.g.has(b);
  }
  N.prototype.forEach = function (a, b) {
    P(this);
    this.g.forEach(function (c, d) {
      c.forEach(function (e) {
        a.call(b, e, d, this);
      }, this);
    }, this);
  };
  function sb(a, b) {
    P(a);
    var c = [];
    if ("string" === typeof b) rb(a, b) && (c = c.concat(a.g.get(Q(a, b))));else for (a = Array.from(a.g.values()), b = 0; b < a.length; b++) c = c.concat(a[b]);
    return c;
  }
  N.prototype.set = function (a, b) {
    P(this);
    this.i = null;
    a = Q(this, a);
    rb(this, a) && (this.h -= this.g.get(a).length);
    this.g.set(a, [b]);
    this.h += 1;
    return this;
  };
  N.prototype.get = function (a, b) {
    if (!a) return b;
    a = sb(this, a);
    return 0 < a.length ? String(a[0]) : b;
  };
  N.prototype.toString = function () {
    if (this.i) return this.i;
    if (!this.g) return "";
    for (var a = [], b = Array.from(this.g.keys()), c = 0; c < b.length; c++) {
      var d = b[c],
        e = encodeURIComponent(String(d));
      d = sb(this, d);
      for (var h = 0; h < d.length; h++) {
        var f = e;
        "" !== d[h] && (f += "=" + encodeURIComponent(String(d[h])));
        a.push(f);
      }
    }
    return this.i = a.join("&");
  };
  function ib(a) {
    var b = new N();
    b.i = a.i;
    a.g && (b.g = new Map(a.g), b.h = a.h);
    return b;
  }
  function Q(a, b) {
    b = String(b);
    a.j && (b = b.toLowerCase());
    return b;
  }
  function nb(a, b) {
    b && !a.j && (P(a), a.i = null, a.g.forEach(function (c, d) {
      var e = d.toLowerCase();
      if (d != e && (qb(this, d), qb(this, e), 0 < c.length)) {
        this.i = null;
        d = this.g;
        var h = d.set;
        e = Q(this, e);
        var f = c.length;
        if (0 < f) {
          for (var g = Array(f), k = 0; k < f; k++) g[k] = c[k];
          f = g;
        } else f = [];
        h.call(d, e, f);
        this.h += c.length;
      }
    }, a));
    a.j = b;
  }
  ;
  function tb(a, b) {
    Ua(b, function (c, d) {
      c && "object" == _typeof(c) && c.K && (c = c.I());
      "style" == d ? a.style.cssText = c : "class" == d ? a.className = c : "for" == d ? a.htmlFor = c : ub.hasOwnProperty(d) ? a.setAttribute(ub[d], c) : 0 == d.lastIndexOf("aria-", 0) || 0 == d.lastIndexOf("data-", 0) ? a.setAttribute(d, c) : a[d] = c;
    });
  }
  var ub = {
    cellpadding: "cellPadding",
    cellspacing: "cellSpacing",
    colspan: "colSpan",
    frameborder: "frameBorder",
    height: "height",
    maxlength: "maxLength",
    nonce: "nonce",
    role: "role",
    rowspan: "rowSpan",
    type: "type",
    usemap: "useMap",
    valign: "vAlign",
    width: "width"
  };
  function vb(a, b) {
    b = String(b);
    "application/xhtml+xml" === a.contentType && (b = b.toLowerCase());
    return a.createElement(b);
  }
  function wb(a) {
    this.g = a || A.document || document;
  }
  ;
  function xb() {}
  ;
  function yb(a, b) {
    this.i = a;
    this.j = b;
    this.h = 0;
    this.g = null;
  }
  yb.prototype.get = function () {
    if (0 < this.h) {
      this.h--;
      var a = this.g;
      this.g = a.next;
      a.next = null;
    } else a = this.i();
    return a;
  };
  function zb(a, b) {
    a.j(b);
    100 > a.h && (a.h++, b.next = a.g, a.g = b);
  }
  ;
  var Ab;
  function Bb() {
    var a = A.MessageChannel;
    "undefined" === typeof a && "undefined" !== typeof window && window.postMessage && window.addEventListener && -1 == $a().indexOf("Presto") && (a = function a() {
      var e = vb(document, "IFRAME");
      e.style.display = "none";
      document.documentElement.appendChild(e);
      var h = e.contentWindow;
      e = h.document;
      e.open();
      e.close();
      var f = "callImmediate" + Math.random(),
        g = "file:" == h.location.protocol ? "*" : h.location.protocol + "//" + h.location.host;
      e = C(function (k) {
        if (("*" == g || k.origin == g) && k.data == f) this.port1.onmessage();
      }, this);
      h.addEventListener("message", e, !1);
      this.port1 = {};
      this.port2 = {
        postMessage: function postMessage() {
          h.postMessage(f, g);
        }
      };
    });
    if ("undefined" !== typeof a && (Xa && K && 0 < K.brands.length || -1 == $a().indexOf("Trident") && -1 == $a().indexOf("MSIE"))) {
      var b = new a(),
        c = {},
        d = c;
      b.port1.onmessage = function () {
        if (void 0 !== c.next) {
          c = c.next;
          var e = c.H;
          c.H = null;
          e();
        }
      };
      return function (e) {
        d.next = {
          H: e
        };
        d = d.next;
        b.port2.postMessage(0);
      };
    }
    return function (e) {
      A.setTimeout(e, 0);
    };
  }
  ;
  function Cb(a) {
    A.setTimeout(function () {
      throw a;
    }, 0);
  }
  ;
  function Db() {
    this.h = this.g = null;
  }
  Db.prototype.add = function (a, b) {
    var c = Eb.get();
    c.set(a, b);
    this.h ? this.h.next = c : this.g = c;
    this.h = c;
  };
  function Fb() {
    var a = Gb,
      b = null;
    a.g && (b = a.g, a.g = a.g.next, a.g || (a.h = null), b.next = null);
    return b;
  }
  var Eb = new yb(function () {
    return new Hb();
  }, function (a) {
    return a.reset();
  });
  function Hb() {
    this.next = this.g = this.h = null;
  }
  Hb.prototype.set = function (a, b) {
    this.h = a;
    this.g = b;
    this.next = null;
  };
  Hb.prototype.reset = function () {
    this.next = this.g = this.h = null;
  };
  var Ib,
    Jb = !1,
    Gb = new Db();
  function Kb(a, b) {
    Ib || Lb();
    Jb || (Ib(), Jb = !0);
    Gb.add(a, b);
  }
  function Lb() {
    if (A.Promise && A.Promise.resolve) {
      var a = A.Promise.resolve(void 0);
      Ib = function Ib() {
        a.then(Mb);
      };
    } else Ib = function Ib() {
      var b = Mb;
      "function" !== typeof A.setImmediate || A.Window && A.Window.prototype && (Xa && K && 0 < K.brands.length || -1 == $a().indexOf("Edge")) && A.Window.prototype.setImmediate == A.setImmediate ? (Ab || (Ab = Bb()), Ab(b)) : A.setImmediate(b);
    };
  }
  function Mb() {
    for (var a; a = Fb();) {
      try {
        a.h.call(a.g);
      } catch (b) {
        Cb(b);
      }
      zb(Eb, a);
    }
    Jb = !1;
  }
  ;
  function Nb(a) {
    if (!a) return !1;
    try {
      return !!a.$goog_Thenable;
    } catch (b) {
      return !1;
    }
  }
  ;
  function R(a) {
    this.g = 0;
    this.o = void 0;
    this.j = this.h = this.i = null;
    this.l = this.m = !1;
    if (a != Ta) try {
      var b = this;
      a.call(void 0, function (c) {
        S(b, 2, c);
      }, function (c) {
        S(b, 3, c);
      });
    } catch (c) {
      S(this, 3, c);
    }
  }
  function Ob() {
    this.next = this.i = this.h = this.j = this.g = null;
    this.l = !1;
  }
  Ob.prototype.reset = function () {
    this.i = this.h = this.j = this.g = null;
    this.l = !1;
  };
  var Pb = new yb(function () {
    return new Ob();
  }, function (a) {
    a.reset();
  });
  function Qb(a, b, c) {
    var d = Pb.get();
    d.j = a;
    d.h = b;
    d.i = c;
    return d;
  }
  R.prototype.then = function (a, b, c) {
    return Rb(this, "function" === typeof a ? a : null, "function" === typeof b ? b : null, c);
  };
  R.prototype.$goog_Thenable = !0;
  R.prototype.cancel = function (a) {
    if (0 == this.g) {
      var b = new T(a);
      Kb(function () {
        Sb(this, b);
      }, this);
    }
  };
  function Sb(a, b) {
    if (0 == a.g) if (a.i) {
      var c = a.i;
      if (c.h) {
        for (var d = 0, e = null, h = null, f = c.h; f && (f.l || (d++, f.g == a && (e = f), !(e && 1 < d))); f = f.next) e || (h = f);
        e && (0 == c.g && 1 == d ? Sb(c, b) : (h ? (d = h, d.next == c.j && (c.j = d), d.next = d.next.next) : Tb(c), Ub(c, e, 3, b)));
      }
      a.i = null;
    } else S(a, 3, b);
  }
  function Vb(a, b) {
    a.h || 2 != a.g && 3 != a.g || Wb(a);
    a.j ? a.j.next = b : a.h = b;
    a.j = b;
  }
  function Rb(a, b, c, d) {
    var e = Qb(null, null, null);
    e.g = new R(function (h, f) {
      e.j = b ? function (g) {
        try {
          var k = b.call(d, g);
          h(k);
        } catch (m) {
          f(m);
        }
      } : h;
      e.h = c ? function (g) {
        try {
          var k = c.call(d, g);
          void 0 === k && g instanceof T ? f(g) : h(k);
        } catch (m) {
          f(m);
        }
      } : f;
    });
    e.g.i = a;
    Vb(a, e);
    return e.g;
  }
  R.prototype.A = function (a) {
    this.g = 0;
    S(this, 2, a);
  };
  R.prototype.B = function (a) {
    this.g = 0;
    S(this, 3, a);
  };
  function S(a, b, c) {
    if (0 == a.g) {
      a === c && (b = 3, c = new TypeError("Promise cannot resolve to itself"));
      a.g = 1;
      a: {
        var d = c,
          e = a.A,
          h = a.B;
        if (d instanceof R) {
          Vb(d, Qb(e || Ta, h || null, a));
          var f = !0;
        } else if (Nb(d)) d.then(e, h, a), f = !0;else {
          if (wa(d)) try {
            var g = d.then;
            if ("function" === typeof g) {
              Xb(d, g, e, h, a);
              f = !0;
              break a;
            }
          } catch (k) {
            h.call(a, k);
            f = !0;
            break a;
          }
          f = !1;
        }
      }
      f || (a.o = c, a.g = b, a.i = null, Wb(a), 3 != b || c instanceof T || Yb(a, c));
    }
  }
  function Xb(a, b, c, d, e) {
    function h(k) {
      g || (g = !0, d.call(e, k));
    }
    function f(k) {
      g || (g = !0, c.call(e, k));
    }
    var g = !1;
    try {
      b.call(a, f, h);
    } catch (k) {
      h(k);
    }
  }
  function Wb(a) {
    a.m || (a.m = !0, Kb(a.u, a));
  }
  function Tb(a) {
    var b = null;
    a.h && (b = a.h, a.h = b.next, b.next = null);
    a.h || (a.j = null);
    return b;
  }
  R.prototype.u = function () {
    for (var a; a = Tb(this);) Ub(this, a, this.g, this.o);
    this.m = !1;
  };
  function Ub(a, b, c, d) {
    if (3 == c && b.h && !b.l) for (; a && a.l; a = a.i) a.l = !1;
    if (b.g) b.g.i = null, Zb(b, c, d);else try {
      b.l ? b.j.call(b.i) : Zb(b, c, d);
    } catch (e) {
      $b.call(null, e);
    }
    zb(Pb, b);
  }
  function Zb(a, b, c) {
    2 == b ? a.j.call(a.i, c) : a.h && a.h.call(a.i, c);
  }
  function Yb(a, b) {
    a.l = !0;
    Kb(function () {
      a.l && $b.call(null, b);
    });
  }
  var $b = Cb;
  function T(a) {
    F.call(this, a);
  }
  E(T, F);
  T.prototype.name = "cancel"; /*
                               Copyright 2005, 2007 Bob Ippolito. All Rights Reserved.
                               Copyright The Closure Library Authors.
                               SPDX-License-Identifier: MIT
                               */
  function U(a, b) {
    this.l = [];
    this.D = a;
    this.C = b || null;
    this.j = this.i = !1;
    this.h = void 0;
    this.A = this.G = this.o = !1;
    this.m = 0;
    this.g = null;
    this.u = 0;
  }
  E(U, xb);
  U.prototype.cancel = function (a) {
    if (this.i) this.h instanceof U && this.h.cancel();else {
      if (this.g) {
        var b = this.g;
        delete this.g;
        a ? b.cancel(a) : (b.u--, 0 >= b.u && b.cancel());
      }
      this.D ? this.D.call(this.C, this) : this.A = !0;
      this.i || (a = new V(this), ac(this), W(this, !1, a));
    }
  };
  U.prototype.B = function (a, b) {
    this.o = !1;
    W(this, a, b);
  };
  function W(a, b, c) {
    a.i = !0;
    a.h = c;
    a.j = !b;
    bc(a);
  }
  function ac(a) {
    if (a.i) {
      if (!a.A) throw new cc(a);
      a.A = !1;
    }
  }
  function dc(a, b, c, d) {
    a.l.push([b, c, d]);
    a.i && bc(a);
  }
  U.prototype.then = function (a, b, c) {
    var d,
      e,
      h = new R(function (f, g) {
        e = f;
        d = g;
      });
    dc(this, e, function (f) {
      f instanceof V ? h.cancel() : d(f);
      return ec;
    }, this);
    return h.then(a, b, c);
  };
  U.prototype.$goog_Thenable = !0;
  function fc(a) {
    return Sa(a.l, function (b) {
      return "function" === typeof b[1];
    });
  }
  var ec = {};
  function bc(a) {
    if (a.m && a.i && fc(a)) {
      var b = a.m,
        c = gc[b];
      c && (A.clearTimeout(c.g), delete gc[b]);
      a.m = 0;
    }
    a.g && (a.g.u--, delete a.g);
    b = a.h;
    for (var d = c = !1; a.l.length && !a.o;) {
      var e = a.l.shift(),
        h = e[0],
        f = e[1];
      e = e[2];
      if (h = a.j ? f : h) try {
        var g = h.call(e || a.C, b);
        g === ec && (g = void 0);
        void 0 !== g && (a.j = a.j && (g == b || g instanceof Error), a.h = b = g);
        if (Nb(b) || "function" === typeof A.Promise && b instanceof A.Promise) d = !0, a.o = !0;
      } catch (k) {
        b = k, a.j = !0, fc(a) || (c = !0);
      }
    }
    a.h = b;
    d && (g = C(a.B, a, !0), d = C(a.B, a, !1), b instanceof U ? (dc(b, g, d), b.G = !0) : b.then(g, d));
    c && (b = new hc(b), gc[b.g] = b, a.m = b.g);
  }
  function cc() {
    F.call(this);
  }
  E(cc, F);
  cc.prototype.message = "Deferred has already fired";
  cc.prototype.name = "AlreadyCalledError";
  function V() {
    F.call(this);
  }
  E(V, F);
  V.prototype.message = "Deferred was canceled";
  V.prototype.name = "CanceledError";
  function hc(a) {
    this.g = A.setTimeout(C(this.i, this), 0);
    this.h = a;
  }
  hc.prototype.i = function () {
    delete gc[this.g];
    throw this.h;
  };
  var gc = {};
  function ic(a) {
    var b;
    return (b = (a || document).getElementsByTagName("HEAD")) && 0 !== b.length ? b[0] : a.documentElement;
  }
  function jc() {
    if (this && this.L) {
      var a = this.L;
      a && "SCRIPT" == a.tagName && kc(a, !0, this.M);
    }
  }
  function kc(a, b, c) {
    null != c && A.clearTimeout(c);
    a.onload = function () {};
    a.onerror = function () {};
    a.onreadystatechange = function () {};
    b && window.setTimeout(function () {
      a && a.parentNode && a.parentNode.removeChild(a);
    }, 0);
  }
  function lc(a, b) {
    var c = "Jsloader error (code #" + a + ")";
    b && (c += ": " + b);
    F.call(this, c);
    this.code = a;
  }
  E(lc, F); /*
            Copyright 2021 Google LLC
            This code is released under the MIT license.
            SPDX-License-Identifier: MIT
            */
  function mc(a) {
    return Qa(a.format, a.R, a.X || {});
  }
  function nc(a) {
    var b = {
        timeout: 3E4,
        attributes: {
          async: !1,
          defer: !1
        }
      },
      c = b.document || document,
      d = Ka(a).toString(),
      e = vb(new wb(c).g, "SCRIPT"),
      h = {
        L: e,
        M: void 0
      },
      f = new U(jc, h),
      g = null,
      k = null != b.timeout ? b.timeout : 5E3;
    0 < k && (g = window.setTimeout(function () {
      kc(e, !0);
      var m = new lc(1, "Timeout reached for loading script " + d);
      ac(f);
      W(f, !1, m);
    }, k), h.M = g);
    e.onload = e.onreadystatechange = function () {
      e.readyState && "loaded" != e.readyState && "complete" != e.readyState || (kc(e, b.V || !1, g), ac(f), W(f, !0, null));
    };
    e.onerror = function () {
      kc(e, !0, g);
      var m = new lc(0, "Error while loading script " + d);
      ac(f);
      W(f, !1, m);
    };
    h = b.attributes || {};
    Wa(h, {
      type: "text/javascript",
      charset: "UTF-8"
    });
    tb(e, h);
    bb(e, a);
    ic(c).appendChild(e);
    return f;
  }
  function oc(a, b, c) {
    c = c || {};
    a = Qa(a, b, c);
    var d = nc(a);
    return new Promise(function (e) {
      dc(d, e, null);
    });
  }
  ; /*
    Copyright 2021 Google LLC
    This code is released under the MIT license.
    SPDX-License-Identifier: MIT
    */
  function pc() {
    return new Promise(function (a) {
      "undefined" === typeof window || "complete" === document.readyState ? a() : window.addEventListener ? (document.addEventListener("DOMContentLoaded", a, !0), window.addEventListener("load", a, !0)) : window.attachEvent ? window.attachEvent("onload", a) : "function" !== typeof window.onload ? window.onload = a : window.onload = function (b) {
        if (window.onload) window.onload(b);
        a();
      };
    });
  }
  ;
  var X = "",
    Y = "",
    qc,
    Z,
    rc = null,
    sc;
  function tc(a) {
    var b = a,
      c,
      d = a.match(/^testing-/);
    d && (b = b.replace(/^testing-/, ""));
    a = b;
    do {
      if (b === Ha[b]) throw Error("Infinite loop in version mapping: " + b);
      (c = Ha[b]) && (b = c);
    } while (c);
    c = (d ? "testing-" : "") + b;
    a = "pre-45" == b ? a : c;
    return {
      version: a,
      S: c
    };
  }
  function uc(a) {
    var b = Da[sc].loader,
      c = tc(a);
    return oc(b, {
      version: c.S
    }).then(function () {
      var d = B("google.charts.loader.versionSpecific.load") || B("google.charts.loader.VersionSpecific.load") || B("google.charts.loader.publicLoad") || B("google.charts.versionSpecific.load");
      if (!d) throw Error("Bad version: " + a);
      rc = function rc(e) {
        e = d(c.version, e);
        if (null == e || null == e.then) {
          var h = B("google.charts.loader.publicSetOnLoadCallback") || B("google.charts.versionSpecific.setOnLoadCallback");
          e = new Promise(function (f) {
            h(f);
          });
          e.then = h;
        }
        return e;
      };
    });
  }
  function vc(a) {
    "string" === typeof a && (a = [a]);
    Array.isArray(a) && 0 !== a.length || (a = Ea);
    var b = [];
    a.forEach(function (c) {
      c = c.toLowerCase();
      b = b.concat(c.split(/[\s,]+\s*/));
    });
    return b;
  }
  function wc(a) {
    a = a || "";
    for (var b = a.replace(/-/g, "_").toLowerCase(); "string" === typeof b;) a = b, b = Ga[b], b === a && (b = !1);
    b || (a.match(/_[^_]+$/) ? (a = a.replace(/_[^_]+$/, ""), a = wc(a)) : a = "en");
    return a;
  }
  function xc(a) {
    a = a || "";
    "" !== X && X !== a && (console.warn(" Attempting to load version '" + a + "' of Google Charts, but the previously loaded '" + (X + "' will be used instead.")), a = X);
    return X = a || "";
  }
  function yc(a) {
    a = a || "";
    "" !== Y && Y !== a && (console.warn(" Attempting to load Google Charts for language '" + a + "', but the previously loaded '" + (Y + "' will be used instead.")), a = Y);
    "en" === a && (a = "");
    return Y = a || "";
  }
  function zc(a) {
    var b = {},
      c;
    for (c in a) b[c] = a[c];
    return b;
  }
  function Ac(a, b) {
    b = zc(b);
    b.domain = sc;
    b.callback = Bc(b.callback);
    a = xc(a);
    var c = b.language;
    c = yc(wc(c));
    b.language = c;
    if (!qc) {
      if (b.enableUrlSettings && window.URLSearchParams) try {
        a = new URLSearchParams(top.location.search).get("charts-version") || a;
      } catch (d) {
        console.info("Failed to get charts-version from top URL", d);
      }
      qc = uc(a);
    }
    b.packages = vc(b.packages);
    return Z = qc.then(function () {
      return rc(b);
    });
  }
  function Cc(a) {
    if (!Z) throw Error("Must call google.charts.load before google.charts.setOnLoadCallback");
    return a ? Z.then(a) : Z;
  }
  D("google.charts.safeLoad", function (a) {
    return Dc(Object.assign({}, a, {
      safeMode: !0
    }));
  });
  function Dc() {
    var a = la.apply(0, arguments),
      b = 0;
    "visualization" === a[b] && b++;
    var c = "current";
    if ("string" === typeof a[b] || "number" === typeof a[b]) c = String(a[b]), b++;
    var d = {};
    wa(a[b]) && (d = a[b]);
    return Ac(c, d);
  }
  D("google.charts.load", Dc);
  D("google.charts.setOnLoadCallback", Cc);
  var Ec = I("https://maps.googleapis.com/maps/api/js?jsapiRedirect=true"),
    Fc = I("https://maps-api-ssl.google.com/maps?jsapiRedirect=true&file=googleapi");
  function Gc(a, b, c) {
    console.warn("Loading Maps API with the jsapi loader is deprecated.");
    c = c || {};
    a = c.key || c.client;
    var d = c.libraries,
      e = function (g) {
        for (var k = {}, m = 0; m < g.length; m++) {
          var n = g[m];
          k[n[0]] = n[1];
        }
        return k;
      }(c.other_params ? c.other_params.split("&").map(function (g) {
        return g.split("=");
      }) : []),
      h = Object.assign({}, {
        key: a,
        W: d
      }, e),
      f = "2" === b ? Fc : Ec;
    Z = new Promise(function (g) {
      var k = Bc(c && c.callback);
      oc(f, {}, h).then(k).then(g);
    });
  }
  var Hc = I("https://www.gstatic.com/inputtools/js/ita/inputtools_3.js");
  function Ic(a, b, c) {
    wa(c) && c.packages ? (Array.isArray(c.packages) ? c.packages : [c.packages]).includes("inputtools") ? (console.warn('Loading "elements" with the jsapi loader is deprecated.\nPlease load ' + (Hc + " directly.")), Z = new Promise(function (d) {
      var e = Bc(c && c.callback);
      oc(Hc, {}, {}).then(e).then(d);
    })) : console.error('Loading "elements" other than "inputtools" is unsupported.') : console.error("google.load of elements was invoked without specifying packages");
  }
  var Jc = I("https://ajax.googleapis.com/ajax/libs/%{module}/%{version}/%{file}");
  function Kc(a, b) {
    var c;
    do {
      if (a === b[a]) throw Error("Infinite loop in version mapping for version " + a);
      (c = b[a]) && (a = c);
    } while (c);
    return a;
  }
  function Lc(a, b, c) {
    var d = Fa[a];
    if (d) {
      b = Kc(b, d.aliases);
      d = d.versions[b];
      if (!d) throw Error("Unknown version, " + b + ", of " + a + ".");
      var e = {
        module: a,
        version: b || "",
        file: d.compressed
      };
      b = Ka(mc({
        format: Jc,
        R: e
      })).toString();
      console.warn("Loading modules with the jsapi loader is deprecated.\nPlease load " + (a + " directly from " + b + "."));
      Z = new Promise(function (h) {
        var f = Bc(c && c.callback);
        oc(Jc, e).then(f).then(h);
      });
    } else setTimeout(function () {
      throw Error('Module "' + a + '" is not supported.');
    }, 0);
  }
  function Bc(a) {
    return function () {
      if ("function" === typeof a) a();else if ("string" === typeof a && "" !== a) try {
        var b = B(a);
        if ("function" !== typeof b) throw Error("Type of '" + a + "' is " + _typeof(b) + ".");
        b();
      } catch (c) {
        throw Error("Callback of " + a + " failed with: " + c);
      }
    };
  }
  function Mc() {
    var a = la.apply(0, arguments);
    switch (a[0]) {
      case "maps":
        Gc.apply(null, ea(a));
        break;
      case "elements":
        Ic.apply(null, ea(a));
        break;
      case "visualization":
        Dc.apply(null, ea(a));
        break;
      default:
        Lc.apply(null, ea(a));
    }
  }
  D("google.loader.LoadFailure", !1);
  if (sc) console.warn("Google Charts loader.js should only be loaded once.");else {
    Y = X = "";
    rc = Z = qc = null;
    B("google.load") || (D("google.load", Mc), D("google.setOnLoadCallback", Cc));
    var Nc = document.getElementsByTagName("script"),
      Oc = (document.currentScript || Nc[Nc.length - 1]).getAttribute("src"),
      Pc = new L(Oc),
      Qc = Pc.g;
    sc = Qc = Qc.match(/^www\.gstatic\.cn/) ? "gstatic.cn" : "gstatic.com";
    var Rc = new N(Pc.i.toString()),
      Sc = Rc.get("callback");
    if ("string" === typeof Sc) {
      var Tc = Bc(Sc);
      pc().then(Tc);
    }
    var Uc = Rc.get("autoload");
    if ("string" === typeof Uc) try {
      if ("" !== Uc) for (var Vc = JSON.parse(Uc).modules, Wc = 0; Wc < Vc.length; Wc++) {
        var Xc = Vc[Wc];
        Mc(Xc.name, Xc.version, Xc);
      }
    } catch (a) {
      throw Error("Autoload failed with: " + a);
    }
  }
  ;
}).call(this);

/***/ }),

/***/ "./assets/src/js/backend/index.js":
/*!****************************************!*\
  !*** ./assets/src/js/backend/index.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var requireAll = __webpack_require__("./assets/src/js/backend sync \\.js$");
requireAll.keys().forEach(function (fileName) {
  if (fileName === './index.js') return; // Avoid importing itself
  requireAll(fileName);
});

/***/ }),

/***/ "./assets/src/js/backend/process-batches.js":
/*!**************************************************!*\
  !*** ./assets/src/js/backend/process-batches.js ***!
  \**************************************************/
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

/***/ "./assets/src/js/backend/redirect-to-options-tab.js":
/*!**********************************************************!*\
  !*** ./assets/src/js/backend/redirect-to-options-tab.js ***!
  \**********************************************************/
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

/***/ "./assets/src/js/backend/render-data-callback.js":
/*!*******************************************************!*\
  !*** ./assets/src/js/backend/render-data-callback.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   zwssgrRenderDataCallback: () => (/* binding */ zwssgrRenderDataCallback)
/* harmony export */ });
/* harmony import */ var _draw_chart__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./draw-chart */ "./assets/src/js/backend/draw-chart.js");

function zwssgrRenderDataCallback(zwssgrEv, zwssgrRangeFilterData, zwssgrRangeFilterType) {
  "use strict";

  var _zwssgrDashboard$quer;
  if (zwssgrEv && typeof zwssgrEv.preventDefault === "function") {
    zwssgrEv.preventDefault();
  }
  var zwssgrGmbAccountDiv = document.getElementById('zwssgr-account-select');
  var zwssgrGmbLocationDiv = document.getElementById('zwssgr-location-select');
  var zwssgrDashboard = document.querySelector('.zwssgr-dashboard');
  if (!zwssgrDashboard) {
    return;
  }
  if (zwssgrEv.target.id === 'zwssgr-location-select') {
    zwssgrGmbAccountDiv.classList.add('disabled');
    zwssgrGmbLocationDiv.classList.add('disabled');
  }
  var zwssgrFilterData = {
    zwssgr_gmb_account_number: zwssgrGmbAccountDiv ? zwssgrGmbAccountDiv.value : '',
    zwssgr_gmb_account_location: zwssgrGmbLocationDiv ? zwssgrGmbLocationDiv.value : '',
    zwssgr_range_filter_type: zwssgrRangeFilterType,
    zwssgr_range_filter_data: zwssgrRangeFilterData
  };
  var zwssgrMinHeight = (zwssgrDashboard === null || zwssgrDashboard === void 0 || (_zwssgrDashboard$quer = zwssgrDashboard.querySelector('#zwssgr-render-dynamic')) === null || _zwssgrDashboard$quer === void 0 ? void 0 : _zwssgrDashboard$quer.offsetHeight) || 200;
  var zwssgrRenderDynamic = zwssgrDashboard === null || zwssgrDashboard === void 0 ? void 0 : zwssgrDashboard.querySelector('#zwssgr-render-dynamic');
  if (zwssgrRenderDynamic) {
    zwssgrRenderDynamic.remove();
  }
  var zwssgrLoaderWrapper = document.createElement('div');
  zwssgrLoaderWrapper.className = 'loader-outer-wrapper';
  zwssgrLoaderWrapper.style.height = "".concat(zwssgrMinHeight, "px");
  zwssgrLoaderWrapper.innerHTML = '<div class="loader"></div>';
  zwssgrDashboard.appendChild(zwssgrLoaderWrapper);
  fetch(zwssgr_admin.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
      action: 'zwssgr_data_render',
      zwssgr_filter_data: JSON.stringify(zwssgrFilterData),
      security: zwssgr_admin.zwssgr_data_render
    })
  }).then(function (zwssgrResponse) {
    return zwssgrResponse.json();
  }).then(function (zwssgrData) {
    zwssgrLoaderWrapper.remove();
    if (zwssgrData.success) {
      zwssgrDashboard.insertAdjacentHTML('beforeend', zwssgrData.data.html);
      var zwssgrNewContent = zwssgrDashboard.lastElementChild;
      zwssgrNewContent.style.display = 'none';
      zwssgrNewContent.style.opacity = 0;
      zwssgrNewContent.style.transition = 'opacity 0.3s';
      zwssgrNewContent.style.display = '';
      requestAnimationFrame(function () {
        zwssgrNewContent.style.opacity = 1;
      });
      if (zwssgrData.data.zwssgr_chart_data) {
        google.charts.setOnLoadCallback(function () {
          return (0,_draw_chart__WEBPACK_IMPORTED_MODULE_0__.zwssgr_draw_chart)(zwssgrData.data.zwssgr_chart_data);
        });
      }
    } else {
      zwssgrDashboard.innerHTML = '<p>Error loading data.</p>';
    }
  })["catch"](function (zwssgrError) {
    zwssgrDashboard.innerHTML = '<p>An error occurred while fetching data.</p>';
  })["finally"](function () {
    if (zwssgrGmbAccountDiv) {
      zwssgrGmbAccountDiv.classList.remove('disabled');
    }
    if (zwssgrGmbLocationDiv) {
      zwssgrGmbLocationDiv.classList.remove('disabled');
    }
    var zwssgrInputs = document.querySelectorAll('#zwssgr-account-select, #zwssgr-location-select');
    zwssgrInputs.forEach(function (zwssgrInput) {
      zwssgrInput === null || zwssgrInput === void 0 || zwssgrInput.classList.remove('disabled');
      zwssgrInput.disabled = false;
    });
  });
}

/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/index.js":
/*!**************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _types_options__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./types/options */ "./node_modules/flatpickr/dist/esm/types/options.js");
/* harmony import */ var _l10n_default__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./l10n/default */ "./node_modules/flatpickr/dist/esm/l10n/default.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils */ "./node_modules/flatpickr/dist/esm/utils/index.js");
/* harmony import */ var _utils_dom__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./utils/dom */ "./node_modules/flatpickr/dist/esm/utils/dom.js");
/* harmony import */ var _utils_dates__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./utils/dates */ "./node_modules/flatpickr/dist/esm/utils/dates.js");
/* harmony import */ var _utils_formatting__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./utils/formatting */ "./node_modules/flatpickr/dist/esm/utils/formatting.js");
/* harmony import */ var _utils_polyfills__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./utils/polyfills */ "./node_modules/flatpickr/dist/esm/utils/polyfills.js");
/* harmony import */ var _utils_polyfills__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_utils_polyfills__WEBPACK_IMPORTED_MODULE_6__);
var __assign = (undefined && undefined.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArrays = (undefined && undefined.__spreadArrays) || function () {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++)
        for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
            r[k] = a[j];
    return r;
};







var DEBOUNCED_CHANGE_MS = 300;
function FlatpickrInstance(element, instanceConfig) {
    var self = {
        config: __assign(__assign({}, _types_options__WEBPACK_IMPORTED_MODULE_0__.defaults), flatpickr.defaultConfig),
        l10n: _l10n_default__WEBPACK_IMPORTED_MODULE_1__["default"],
    };
    self.parseDate = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.createDateParser)({ config: self.config, l10n: self.l10n });
    self._handlers = [];
    self.pluginElements = [];
    self.loadedPlugins = [];
    self._bind = bind;
    self._setHoursFromDate = setHoursFromDate;
    self._positionCalendar = positionCalendar;
    self.changeMonth = changeMonth;
    self.changeYear = changeYear;
    self.clear = clear;
    self.close = close;
    self.onMouseOver = onMouseOver;
    self._createElement = _utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement;
    self.createDay = createDay;
    self.destroy = destroy;
    self.isEnabled = isEnabled;
    self.jumpToDate = jumpToDate;
    self.updateValue = updateValue;
    self.open = open;
    self.redraw = redraw;
    self.set = set;
    self.setDate = setDate;
    self.toggle = toggle;
    function setupHelperFunctions() {
        self.utils = {
            getDaysInMonth: function (month, yr) {
                if (month === void 0) { month = self.currentMonth; }
                if (yr === void 0) { yr = self.currentYear; }
                if (month === 1 && ((yr % 4 === 0 && yr % 100 !== 0) || yr % 400 === 0))
                    return 29;
                return self.l10n.daysInMonth[month];
            },
        };
    }
    function init() {
        self.element = self.input = element;
        self.isOpen = false;
        parseConfig();
        setupLocale();
        setupInputs();
        setupDates();
        setupHelperFunctions();
        if (!self.isMobile)
            build();
        bindEvents();
        if (self.selectedDates.length || self.config.noCalendar) {
            if (self.config.enableTime) {
                setHoursFromDate(self.config.noCalendar ? self.latestSelectedDateObj : undefined);
            }
            updateValue(false);
        }
        setCalendarWidth();
        var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        if (!self.isMobile && isSafari) {
            positionCalendar();
        }
        triggerEvent("onReady");
    }
    function getClosestActiveElement() {
        var _a;
        return (((_a = self.calendarContainer) === null || _a === void 0 ? void 0 : _a.getRootNode())
            .activeElement || document.activeElement);
    }
    function bindToInstance(fn) {
        return fn.bind(self);
    }
    function setCalendarWidth() {
        var config = self.config;
        if (config.weekNumbers === false && config.showMonths === 1) {
            return;
        }
        else if (config.noCalendar !== true) {
            window.requestAnimationFrame(function () {
                if (self.calendarContainer !== undefined) {
                    self.calendarContainer.style.visibility = "hidden";
                    self.calendarContainer.style.display = "block";
                }
                if (self.daysContainer !== undefined) {
                    var daysWidth = (self.days.offsetWidth + 1) * config.showMonths;
                    self.daysContainer.style.width = daysWidth + "px";
                    self.calendarContainer.style.width =
                        daysWidth +
                            (self.weekWrapper !== undefined
                                ? self.weekWrapper.offsetWidth
                                : 0) +
                            "px";
                    self.calendarContainer.style.removeProperty("visibility");
                    self.calendarContainer.style.removeProperty("display");
                }
            });
        }
    }
    function updateTime(e) {
        if (self.selectedDates.length === 0) {
            var defaultDate = self.config.minDate === undefined ||
                (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(new Date(), self.config.minDate) >= 0
                ? new Date()
                : new Date(self.config.minDate.getTime());
            var defaults = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.getDefaultHours)(self.config);
            defaultDate.setHours(defaults.hours, defaults.minutes, defaults.seconds, defaultDate.getMilliseconds());
            self.selectedDates = [defaultDate];
            self.latestSelectedDateObj = defaultDate;
        }
        if (e !== undefined && e.type !== "blur") {
            timeWrapper(e);
        }
        var prevValue = self._input.value;
        setHoursFromInputs();
        updateValue();
        if (self._input.value !== prevValue) {
            self._debouncedChange();
        }
    }
    function ampm2military(hour, amPM) {
        return (hour % 12) + 12 * (0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(amPM === self.l10n.amPM[1]);
    }
    function military2ampm(hour) {
        switch (hour % 24) {
            case 0:
            case 12:
                return 12;
            default:
                return hour % 12;
        }
    }
    function setHoursFromInputs() {
        if (self.hourElement === undefined || self.minuteElement === undefined)
            return;
        var hours = (parseInt(self.hourElement.value.slice(-2), 10) || 0) % 24, minutes = (parseInt(self.minuteElement.value, 10) || 0) % 60, seconds = self.secondElement !== undefined
            ? (parseInt(self.secondElement.value, 10) || 0) % 60
            : 0;
        if (self.amPM !== undefined) {
            hours = ampm2military(hours, self.amPM.textContent);
        }
        var limitMinHours = self.config.minTime !== undefined ||
            (self.config.minDate &&
                self.minDateHasTime &&
                self.latestSelectedDateObj &&
                (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(self.latestSelectedDateObj, self.config.minDate, true) ===
                    0);
        var limitMaxHours = self.config.maxTime !== undefined ||
            (self.config.maxDate &&
                self.maxDateHasTime &&
                self.latestSelectedDateObj &&
                (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(self.latestSelectedDateObj, self.config.maxDate, true) ===
                    0);
        if (self.config.maxTime !== undefined &&
            self.config.minTime !== undefined &&
            self.config.minTime > self.config.maxTime) {
            var minBound = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.calculateSecondsSinceMidnight)(self.config.minTime.getHours(), self.config.minTime.getMinutes(), self.config.minTime.getSeconds());
            var maxBound = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.calculateSecondsSinceMidnight)(self.config.maxTime.getHours(), self.config.maxTime.getMinutes(), self.config.maxTime.getSeconds());
            var currentTime = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.calculateSecondsSinceMidnight)(hours, minutes, seconds);
            if (currentTime > maxBound && currentTime < minBound) {
                var result = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.parseSeconds)(minBound);
                hours = result[0];
                minutes = result[1];
                seconds = result[2];
            }
        }
        else {
            if (limitMaxHours) {
                var maxTime = self.config.maxTime !== undefined
                    ? self.config.maxTime
                    : self.config.maxDate;
                hours = Math.min(hours, maxTime.getHours());
                if (hours === maxTime.getHours())
                    minutes = Math.min(minutes, maxTime.getMinutes());
                if (minutes === maxTime.getMinutes())
                    seconds = Math.min(seconds, maxTime.getSeconds());
            }
            if (limitMinHours) {
                var minTime = self.config.minTime !== undefined
                    ? self.config.minTime
                    : self.config.minDate;
                hours = Math.max(hours, minTime.getHours());
                if (hours === minTime.getHours() && minutes < minTime.getMinutes())
                    minutes = minTime.getMinutes();
                if (minutes === minTime.getMinutes())
                    seconds = Math.max(seconds, minTime.getSeconds());
            }
        }
        setHours(hours, minutes, seconds);
    }
    function setHoursFromDate(dateObj) {
        var date = dateObj || self.latestSelectedDateObj;
        if (date && date instanceof Date) {
            setHours(date.getHours(), date.getMinutes(), date.getSeconds());
        }
    }
    function setHours(hours, minutes, seconds) {
        if (self.latestSelectedDateObj !== undefined) {
            self.latestSelectedDateObj.setHours(hours % 24, minutes, seconds || 0, 0);
        }
        if (!self.hourElement || !self.minuteElement || self.isMobile)
            return;
        self.hourElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(!self.config.time_24hr
            ? ((12 + hours) % 12) + 12 * (0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(hours % 12 === 0)
            : hours);
        self.minuteElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(minutes);
        if (self.amPM !== undefined)
            self.amPM.textContent = self.l10n.amPM[(0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(hours >= 12)];
        if (self.secondElement !== undefined)
            self.secondElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(seconds);
    }
    function onYearInput(event) {
        var eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(event);
        var year = parseInt(eventTarget.value) + (event.delta || 0);
        if (year / 1000 > 1 ||
            (event.key === "Enter" && !/[^\d]/.test(year.toString()))) {
            changeYear(year);
        }
    }
    function bind(element, event, handler, options) {
        if (event instanceof Array)
            return event.forEach(function (ev) { return bind(element, ev, handler, options); });
        if (element instanceof Array)
            return element.forEach(function (el) { return bind(el, event, handler, options); });
        element.addEventListener(event, handler, options);
        self._handlers.push({
            remove: function () { return element.removeEventListener(event, handler, options); },
        });
    }
    function triggerChange() {
        triggerEvent("onChange");
    }
    function bindEvents() {
        if (self.config.wrap) {
            ["open", "close", "toggle", "clear"].forEach(function (evt) {
                Array.prototype.forEach.call(self.element.querySelectorAll("[data-" + evt + "]"), function (el) {
                    return bind(el, "click", self[evt]);
                });
            });
        }
        if (self.isMobile) {
            setupMobile();
            return;
        }
        var debouncedResize = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.debounce)(onResize, 50);
        self._debouncedChange = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.debounce)(triggerChange, DEBOUNCED_CHANGE_MS);
        if (self.daysContainer && !/iPhone|iPad|iPod/i.test(navigator.userAgent))
            bind(self.daysContainer, "mouseover", function (e) {
                if (self.config.mode === "range")
                    onMouseOver((0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e));
            });
        bind(self._input, "keydown", onKeyDown);
        if (self.calendarContainer !== undefined) {
            bind(self.calendarContainer, "keydown", onKeyDown);
        }
        if (!self.config.inline && !self.config.static)
            bind(window, "resize", debouncedResize);
        if (window.ontouchstart !== undefined)
            bind(window.document, "touchstart", documentClick);
        else
            bind(window.document, "mousedown", documentClick);
        bind(window.document, "focus", documentClick, { capture: true });
        if (self.config.clickOpens === true) {
            bind(self._input, "focus", self.open);
            bind(self._input, "click", self.open);
        }
        if (self.daysContainer !== undefined) {
            bind(self.monthNav, "click", onMonthNavClick);
            bind(self.monthNav, ["keyup", "increment"], onYearInput);
            bind(self.daysContainer, "click", selectDate);
        }
        if (self.timeContainer !== undefined &&
            self.minuteElement !== undefined &&
            self.hourElement !== undefined) {
            var selText = function (e) {
                return (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e).select();
            };
            bind(self.timeContainer, ["increment"], updateTime);
            bind(self.timeContainer, "blur", updateTime, { capture: true });
            bind(self.timeContainer, "click", timeIncrement);
            bind([self.hourElement, self.minuteElement], ["focus", "click"], selText);
            if (self.secondElement !== undefined)
                bind(self.secondElement, "focus", function () { return self.secondElement && self.secondElement.select(); });
            if (self.amPM !== undefined) {
                bind(self.amPM, "click", function (e) {
                    updateTime(e);
                });
            }
        }
        if (self.config.allowInput) {
            bind(self._input, "blur", onBlur);
        }
    }
    function jumpToDate(jumpDate, triggerChange) {
        var jumpTo = jumpDate !== undefined
            ? self.parseDate(jumpDate)
            : self.latestSelectedDateObj ||
                (self.config.minDate && self.config.minDate > self.now
                    ? self.config.minDate
                    : self.config.maxDate && self.config.maxDate < self.now
                        ? self.config.maxDate
                        : self.now);
        var oldYear = self.currentYear;
        var oldMonth = self.currentMonth;
        try {
            if (jumpTo !== undefined) {
                self.currentYear = jumpTo.getFullYear();
                self.currentMonth = jumpTo.getMonth();
            }
        }
        catch (e) {
            e.message = "Invalid date supplied: " + jumpTo;
            self.config.errorHandler(e);
        }
        if (triggerChange && self.currentYear !== oldYear) {
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        if (triggerChange &&
            (self.currentYear !== oldYear || self.currentMonth !== oldMonth)) {
            triggerEvent("onMonthChange");
        }
        self.redraw();
    }
    function timeIncrement(e) {
        var eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
        if (~eventTarget.className.indexOf("arrow"))
            incrementNumInput(e, eventTarget.classList.contains("arrowUp") ? 1 : -1);
    }
    function incrementNumInput(e, delta, inputElem) {
        var target = e && (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
        var input = inputElem ||
            (target && target.parentNode && target.parentNode.firstChild);
        var event = createEvent("increment");
        event.delta = delta;
        input && input.dispatchEvent(event);
    }
    function build() {
        var fragment = window.document.createDocumentFragment();
        self.calendarContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-calendar");
        self.calendarContainer.tabIndex = -1;
        if (!self.config.noCalendar) {
            fragment.appendChild(buildMonthNav());
            self.innerContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-innerContainer");
            if (self.config.weekNumbers) {
                var _a = buildWeeks(), weekWrapper = _a.weekWrapper, weekNumbers = _a.weekNumbers;
                self.innerContainer.appendChild(weekWrapper);
                self.weekNumbers = weekNumbers;
                self.weekWrapper = weekWrapper;
            }
            self.rContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-rContainer");
            self.rContainer.appendChild(buildWeekdays());
            if (!self.daysContainer) {
                self.daysContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-days");
                self.daysContainer.tabIndex = -1;
            }
            buildDays();
            self.rContainer.appendChild(self.daysContainer);
            self.innerContainer.appendChild(self.rContainer);
            fragment.appendChild(self.innerContainer);
        }
        if (self.config.enableTime) {
            fragment.appendChild(buildTime());
        }
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "rangeMode", self.config.mode === "range");
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "animate", self.config.animate === true);
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "multiMonth", self.config.showMonths > 1);
        self.calendarContainer.appendChild(fragment);
        var customAppend = self.config.appendTo !== undefined &&
            self.config.appendTo.nodeType !== undefined;
        if (self.config.inline || self.config.static) {
            self.calendarContainer.classList.add(self.config.inline ? "inline" : "static");
            if (self.config.inline) {
                if (!customAppend && self.element.parentNode)
                    self.element.parentNode.insertBefore(self.calendarContainer, self._input.nextSibling);
                else if (self.config.appendTo !== undefined)
                    self.config.appendTo.appendChild(self.calendarContainer);
            }
            if (self.config.static) {
                var wrapper = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-wrapper");
                if (self.element.parentNode)
                    self.element.parentNode.insertBefore(wrapper, self.element);
                wrapper.appendChild(self.element);
                if (self.altInput)
                    wrapper.appendChild(self.altInput);
                wrapper.appendChild(self.calendarContainer);
            }
        }
        if (!self.config.static && !self.config.inline)
            (self.config.appendTo !== undefined
                ? self.config.appendTo
                : window.document.body).appendChild(self.calendarContainer);
    }
    function createDay(className, date, _dayNumber, i) {
        var dateIsEnabled = isEnabled(date, true), dayElement = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", className, date.getDate().toString());
        dayElement.dateObj = date;
        dayElement.$i = i;
        dayElement.setAttribute("aria-label", self.formatDate(date, self.config.ariaDateFormat));
        if (className.indexOf("hidden") === -1 &&
            (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(date, self.now) === 0) {
            self.todayDateElem = dayElement;
            dayElement.classList.add("today");
            dayElement.setAttribute("aria-current", "date");
        }
        if (dateIsEnabled) {
            dayElement.tabIndex = -1;
            if (isDateSelected(date)) {
                dayElement.classList.add("selected");
                self.selectedDateElem = dayElement;
                if (self.config.mode === "range") {
                    (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(dayElement, "startRange", self.selectedDates[0] &&
                        (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(date, self.selectedDates[0], true) === 0);
                    (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(dayElement, "endRange", self.selectedDates[1] &&
                        (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(date, self.selectedDates[1], true) === 0);
                    if (className === "nextMonthDay")
                        dayElement.classList.add("inRange");
                }
            }
        }
        else {
            dayElement.classList.add("flatpickr-disabled");
        }
        if (self.config.mode === "range") {
            if (isDateInRange(date) && !isDateSelected(date))
                dayElement.classList.add("inRange");
        }
        if (self.weekNumbers &&
            self.config.showMonths === 1 &&
            className !== "prevMonthDay" &&
            i % 7 === 6) {
            self.weekNumbers.insertAdjacentHTML("beforeend", "<span class='flatpickr-day'>" + self.config.getWeek(date) + "</span>");
        }
        triggerEvent("onDayCreate", dayElement);
        return dayElement;
    }
    function focusOnDayElem(targetNode) {
        targetNode.focus();
        if (self.config.mode === "range")
            onMouseOver(targetNode);
    }
    function getFirstAvailableDay(delta) {
        var startMonth = delta > 0 ? 0 : self.config.showMonths - 1;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        for (var m = startMonth; m != endMonth; m += delta) {
            var month = self.daysContainer.children[m];
            var startIndex = delta > 0 ? 0 : month.children.length - 1;
            var endIndex = delta > 0 ? month.children.length : -1;
            for (var i = startIndex; i != endIndex; i += delta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 && isEnabled(c.dateObj))
                    return c;
            }
        }
        return undefined;
    }
    function getNextAvailableDay(current, delta) {
        var givenMonth = current.className.indexOf("Month") === -1
            ? current.dateObj.getMonth()
            : self.currentMonth;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        var loopDelta = delta > 0 ? 1 : -1;
        for (var m = givenMonth - self.currentMonth; m != endMonth; m += loopDelta) {
            var month = self.daysContainer.children[m];
            var startIndex = givenMonth - self.currentMonth === m
                ? current.$i + delta
                : delta < 0
                    ? month.children.length - 1
                    : 0;
            var numMonthDays = month.children.length;
            for (var i = startIndex; i >= 0 && i < numMonthDays && i != (delta > 0 ? numMonthDays : -1); i += loopDelta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 &&
                    isEnabled(c.dateObj) &&
                    Math.abs(current.$i - i) >= Math.abs(delta))
                    return focusOnDayElem(c);
            }
        }
        self.changeMonth(loopDelta);
        focusOnDay(getFirstAvailableDay(loopDelta), 0);
        return undefined;
    }
    function focusOnDay(current, offset) {
        var activeElement = getClosestActiveElement();
        var dayFocused = isInView(activeElement || document.body);
        var startElem = current !== undefined
            ? current
            : dayFocused
                ? activeElement
                : self.selectedDateElem !== undefined && isInView(self.selectedDateElem)
                    ? self.selectedDateElem
                    : self.todayDateElem !== undefined && isInView(self.todayDateElem)
                        ? self.todayDateElem
                        : getFirstAvailableDay(offset > 0 ? 1 : -1);
        if (startElem === undefined) {
            self._input.focus();
        }
        else if (!dayFocused) {
            focusOnDayElem(startElem);
        }
        else {
            getNextAvailableDay(startElem, offset);
        }
    }
    function buildMonthDays(year, month) {
        var firstOfMonth = (new Date(year, month, 1).getDay() - self.l10n.firstDayOfWeek + 7) % 7;
        var prevMonthDays = self.utils.getDaysInMonth((month - 1 + 12) % 12, year);
        var daysInMonth = self.utils.getDaysInMonth(month, year), days = window.document.createDocumentFragment(), isMultiMonth = self.config.showMonths > 1, prevMonthDayClass = isMultiMonth ? "prevMonthDay hidden" : "prevMonthDay", nextMonthDayClass = isMultiMonth ? "nextMonthDay hidden" : "nextMonthDay";
        var dayNumber = prevMonthDays + 1 - firstOfMonth, dayIndex = 0;
        for (; dayNumber <= prevMonthDays; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + prevMonthDayClass, new Date(year, month - 1, dayNumber), dayNumber, dayIndex));
        }
        for (dayNumber = 1; dayNumber <= daysInMonth; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day", new Date(year, month, dayNumber), dayNumber, dayIndex));
        }
        for (var dayNum = daysInMonth + 1; dayNum <= 42 - firstOfMonth &&
            (self.config.showMonths === 1 || dayIndex % 7 !== 0); dayNum++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + nextMonthDayClass, new Date(year, month + 1, dayNum % daysInMonth), dayNum, dayIndex));
        }
        var dayContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "dayContainer");
        dayContainer.appendChild(days);
        return dayContainer;
    }
    function buildDays() {
        if (self.daysContainer === undefined) {
            return;
        }
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.clearNode)(self.daysContainer);
        if (self.weekNumbers)
            (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.clearNode)(self.weekNumbers);
        var frag = document.createDocumentFragment();
        for (var i = 0; i < self.config.showMonths; i++) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            frag.appendChild(buildMonthDays(d.getFullYear(), d.getMonth()));
        }
        self.daysContainer.appendChild(frag);
        self.days = self.daysContainer.firstChild;
        if (self.config.mode === "range" && self.selectedDates.length === 1) {
            onMouseOver();
        }
    }
    function buildMonthSwitch() {
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType !== "dropdown")
            return;
        var shouldBuildMonth = function (month) {
            if (self.config.minDate !== undefined &&
                self.currentYear === self.config.minDate.getFullYear() &&
                month < self.config.minDate.getMonth()) {
                return false;
            }
            return !(self.config.maxDate !== undefined &&
                self.currentYear === self.config.maxDate.getFullYear() &&
                month > self.config.maxDate.getMonth());
        };
        self.monthsDropdownContainer.tabIndex = -1;
        self.monthsDropdownContainer.innerHTML = "";
        for (var i = 0; i < 12; i++) {
            if (!shouldBuildMonth(i))
                continue;
            var month = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("option", "flatpickr-monthDropdown-month");
            month.value = new Date(self.currentYear, i).getMonth().toString();
            month.textContent = (0,_utils_formatting__WEBPACK_IMPORTED_MODULE_5__.monthToStr)(i, self.config.shorthandCurrentMonth, self.l10n);
            month.tabIndex = -1;
            if (self.currentMonth === i) {
                month.selected = true;
            }
            self.monthsDropdownContainer.appendChild(month);
        }
    }
    function buildMonth() {
        var container = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-month");
        var monthNavFragment = window.document.createDocumentFragment();
        var monthElement;
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType === "static") {
            monthElement = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "cur-month");
        }
        else {
            self.monthsDropdownContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("select", "flatpickr-monthDropdown-months");
            self.monthsDropdownContainer.setAttribute("aria-label", self.l10n.monthAriaLabel);
            bind(self.monthsDropdownContainer, "change", function (e) {
                var target = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
                var selectedMonth = parseInt(target.value, 10);
                self.changeMonth(selectedMonth - self.currentMonth);
                triggerEvent("onMonthChange");
            });
            buildMonthSwitch();
            monthElement = self.monthsDropdownContainer;
        }
        var yearInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createNumberInput)("cur-year", { tabindex: "-1" });
        var yearElement = yearInput.getElementsByTagName("input")[0];
        yearElement.setAttribute("aria-label", self.l10n.yearAriaLabel);
        if (self.config.minDate) {
            yearElement.setAttribute("min", self.config.minDate.getFullYear().toString());
        }
        if (self.config.maxDate) {
            yearElement.setAttribute("max", self.config.maxDate.getFullYear().toString());
            yearElement.disabled =
                !!self.config.minDate &&
                    self.config.minDate.getFullYear() === self.config.maxDate.getFullYear();
        }
        var currentMonth = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-current-month");
        currentMonth.appendChild(monthElement);
        currentMonth.appendChild(yearInput);
        monthNavFragment.appendChild(currentMonth);
        container.appendChild(monthNavFragment);
        return {
            container: container,
            yearElement: yearElement,
            monthElement: monthElement,
        };
    }
    function buildMonths() {
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.clearNode)(self.monthNav);
        self.monthNav.appendChild(self.prevMonthNav);
        if (self.config.showMonths) {
            self.yearElements = [];
            self.monthElements = [];
        }
        for (var m = self.config.showMonths; m--;) {
            var month = buildMonth();
            self.yearElements.push(month.yearElement);
            self.monthElements.push(month.monthElement);
            self.monthNav.appendChild(month.container);
        }
        self.monthNav.appendChild(self.nextMonthNav);
    }
    function buildMonthNav() {
        self.monthNav = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-months");
        self.yearElements = [];
        self.monthElements = [];
        self.prevMonthNav = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-prev-month");
        self.prevMonthNav.innerHTML = self.config.prevArrow;
        self.nextMonthNav = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-next-month");
        self.nextMonthNav.innerHTML = self.config.nextArrow;
        buildMonths();
        Object.defineProperty(self, "_hidePrevMonthArrow", {
            get: function () { return self.__hidePrevMonthArrow; },
            set: function (bool) {
                if (self.__hidePrevMonthArrow !== bool) {
                    (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.prevMonthNav, "flatpickr-disabled", bool);
                    self.__hidePrevMonthArrow = bool;
                }
            },
        });
        Object.defineProperty(self, "_hideNextMonthArrow", {
            get: function () { return self.__hideNextMonthArrow; },
            set: function (bool) {
                if (self.__hideNextMonthArrow !== bool) {
                    (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.nextMonthNav, "flatpickr-disabled", bool);
                    self.__hideNextMonthArrow = bool;
                }
            },
        });
        self.currentYearElement = self.yearElements[0];
        updateNavigationCurrentMonth();
        return self.monthNav;
    }
    function buildTime() {
        self.calendarContainer.classList.add("hasTime");
        if (self.config.noCalendar)
            self.calendarContainer.classList.add("noCalendar");
        var defaults = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.getDefaultHours)(self.config);
        self.timeContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-time");
        self.timeContainer.tabIndex = -1;
        var separator = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-time-separator", ":");
        var hourInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createNumberInput)("flatpickr-hour", {
            "aria-label": self.l10n.hourAriaLabel,
        });
        self.hourElement = hourInput.getElementsByTagName("input")[0];
        var minuteInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createNumberInput)("flatpickr-minute", {
            "aria-label": self.l10n.minuteAriaLabel,
        });
        self.minuteElement = minuteInput.getElementsByTagName("input")[0];
        self.hourElement.tabIndex = self.minuteElement.tabIndex = -1;
        self.hourElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getHours()
            : self.config.time_24hr
                ? defaults.hours
                : military2ampm(defaults.hours));
        self.minuteElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getMinutes()
            : defaults.minutes);
        self.hourElement.setAttribute("step", self.config.hourIncrement.toString());
        self.minuteElement.setAttribute("step", self.config.minuteIncrement.toString());
        self.hourElement.setAttribute("min", self.config.time_24hr ? "0" : "1");
        self.hourElement.setAttribute("max", self.config.time_24hr ? "23" : "12");
        self.hourElement.setAttribute("maxlength", "2");
        self.minuteElement.setAttribute("min", "0");
        self.minuteElement.setAttribute("max", "59");
        self.minuteElement.setAttribute("maxlength", "2");
        self.timeContainer.appendChild(hourInput);
        self.timeContainer.appendChild(separator);
        self.timeContainer.appendChild(minuteInput);
        if (self.config.time_24hr)
            self.timeContainer.classList.add("time24hr");
        if (self.config.enableSeconds) {
            self.timeContainer.classList.add("hasSeconds");
            var secondInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createNumberInput)("flatpickr-second");
            self.secondElement = secondInput.getElementsByTagName("input")[0];
            self.secondElement.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(self.latestSelectedDateObj
                ? self.latestSelectedDateObj.getSeconds()
                : defaults.seconds);
            self.secondElement.setAttribute("step", self.minuteElement.getAttribute("step"));
            self.secondElement.setAttribute("min", "0");
            self.secondElement.setAttribute("max", "59");
            self.secondElement.setAttribute("maxlength", "2");
            self.timeContainer.appendChild((0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-time-separator", ":"));
            self.timeContainer.appendChild(secondInput);
        }
        if (!self.config.time_24hr) {
            self.amPM = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-am-pm", self.l10n.amPM[(0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)((self.latestSelectedDateObj
                ? self.hourElement.value
                : self.config.defaultHour) > 11)]);
            self.amPM.title = self.l10n.toggleTitle;
            self.amPM.tabIndex = -1;
            self.timeContainer.appendChild(self.amPM);
        }
        return self.timeContainer;
    }
    function buildWeekdays() {
        if (!self.weekdayContainer)
            self.weekdayContainer = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-weekdays");
        else
            (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.clearNode)(self.weekdayContainer);
        for (var i = self.config.showMonths; i--;) {
            var container = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-weekdaycontainer");
            self.weekdayContainer.appendChild(container);
        }
        updateWeekdays();
        return self.weekdayContainer;
    }
    function updateWeekdays() {
        if (!self.weekdayContainer) {
            return;
        }
        var firstDayOfWeek = self.l10n.firstDayOfWeek;
        var weekdays = __spreadArrays(self.l10n.weekdays.shorthand);
        if (firstDayOfWeek > 0 && firstDayOfWeek < weekdays.length) {
            weekdays = __spreadArrays(weekdays.splice(firstDayOfWeek, weekdays.length), weekdays.splice(0, firstDayOfWeek));
        }
        for (var i = self.config.showMonths; i--;) {
            self.weekdayContainer.children[i].innerHTML = "\n      <span class='flatpickr-weekday'>\n        " + weekdays.join("</span><span class='flatpickr-weekday'>") + "\n      </span>\n      ";
        }
    }
    function buildWeeks() {
        self.calendarContainer.classList.add("hasWeeks");
        var weekWrapper = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-weekwrapper");
        weekWrapper.appendChild((0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("span", "flatpickr-weekday", self.l10n.weekAbbreviation));
        var weekNumbers = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("div", "flatpickr-weeks");
        weekWrapper.appendChild(weekNumbers);
        return {
            weekWrapper: weekWrapper,
            weekNumbers: weekNumbers,
        };
    }
    function changeMonth(value, isOffset) {
        if (isOffset === void 0) { isOffset = true; }
        var delta = isOffset ? value : value - self.currentMonth;
        if ((delta < 0 && self._hidePrevMonthArrow === true) ||
            (delta > 0 && self._hideNextMonthArrow === true))
            return;
        self.currentMonth += delta;
        if (self.currentMonth < 0 || self.currentMonth > 11) {
            self.currentYear += self.currentMonth > 11 ? 1 : -1;
            self.currentMonth = (self.currentMonth + 12) % 12;
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        buildDays();
        triggerEvent("onMonthChange");
        updateNavigationCurrentMonth();
    }
    function clear(triggerChangeEvent, toInitial) {
        if (triggerChangeEvent === void 0) { triggerChangeEvent = true; }
        if (toInitial === void 0) { toInitial = true; }
        self.input.value = "";
        if (self.altInput !== undefined)
            self.altInput.value = "";
        if (self.mobileInput !== undefined)
            self.mobileInput.value = "";
        self.selectedDates = [];
        self.latestSelectedDateObj = undefined;
        if (toInitial === true) {
            self.currentYear = self._initialDate.getFullYear();
            self.currentMonth = self._initialDate.getMonth();
        }
        if (self.config.enableTime === true) {
            var _a = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.getDefaultHours)(self.config), hours = _a.hours, minutes = _a.minutes, seconds = _a.seconds;
            setHours(hours, minutes, seconds);
        }
        self.redraw();
        if (triggerChangeEvent)
            triggerEvent("onChange");
    }
    function close() {
        self.isOpen = false;
        if (!self.isMobile) {
            if (self.calendarContainer !== undefined) {
                self.calendarContainer.classList.remove("open");
            }
            if (self._input !== undefined) {
                self._input.classList.remove("active");
            }
        }
        triggerEvent("onClose");
    }
    function destroy() {
        if (self.config !== undefined)
            triggerEvent("onDestroy");
        for (var i = self._handlers.length; i--;) {
            self._handlers[i].remove();
        }
        self._handlers = [];
        if (self.mobileInput) {
            if (self.mobileInput.parentNode)
                self.mobileInput.parentNode.removeChild(self.mobileInput);
            self.mobileInput = undefined;
        }
        else if (self.calendarContainer && self.calendarContainer.parentNode) {
            if (self.config.static && self.calendarContainer.parentNode) {
                var wrapper = self.calendarContainer.parentNode;
                wrapper.lastChild && wrapper.removeChild(wrapper.lastChild);
                if (wrapper.parentNode) {
                    while (wrapper.firstChild)
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    wrapper.parentNode.removeChild(wrapper);
                }
            }
            else
                self.calendarContainer.parentNode.removeChild(self.calendarContainer);
        }
        if (self.altInput) {
            self.input.type = "text";
            if (self.altInput.parentNode)
                self.altInput.parentNode.removeChild(self.altInput);
            delete self.altInput;
        }
        if (self.input) {
            self.input.type = self.input._type;
            self.input.classList.remove("flatpickr-input");
            self.input.removeAttribute("readonly");
        }
        [
            "_showTimeInput",
            "latestSelectedDateObj",
            "_hideNextMonthArrow",
            "_hidePrevMonthArrow",
            "__hideNextMonthArrow",
            "__hidePrevMonthArrow",
            "isMobile",
            "isOpen",
            "selectedDateElem",
            "minDateHasTime",
            "maxDateHasTime",
            "days",
            "daysContainer",
            "_input",
            "_positionElement",
            "innerContainer",
            "rContainer",
            "monthNav",
            "todayDateElem",
            "calendarContainer",
            "weekdayContainer",
            "prevMonthNav",
            "nextMonthNav",
            "monthsDropdownContainer",
            "currentMonthElement",
            "currentYearElement",
            "navigationCurrentMonth",
            "selectedDateElem",
            "config",
        ].forEach(function (k) {
            try {
                delete self[k];
            }
            catch (_) { }
        });
    }
    function isCalendarElem(elem) {
        return self.calendarContainer.contains(elem);
    }
    function documentClick(e) {
        if (self.isOpen && !self.config.inline) {
            var eventTarget_1 = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
            var isCalendarElement = isCalendarElem(eventTarget_1);
            var isInput = eventTarget_1 === self.input ||
                eventTarget_1 === self.altInput ||
                self.element.contains(eventTarget_1) ||
                (e.path &&
                    e.path.indexOf &&
                    (~e.path.indexOf(self.input) ||
                        ~e.path.indexOf(self.altInput)));
            var lostFocus = !isInput &&
                !isCalendarElement &&
                !isCalendarElem(e.relatedTarget);
            var isIgnored = !self.config.ignoredFocusElements.some(function (elem) {
                return elem.contains(eventTarget_1);
            });
            if (lostFocus && isIgnored) {
                if (self.config.allowInput) {
                    self.setDate(self._input.value, false, self.config.altInput
                        ? self.config.altFormat
                        : self.config.dateFormat);
                }
                if (self.timeContainer !== undefined &&
                    self.minuteElement !== undefined &&
                    self.hourElement !== undefined &&
                    self.input.value !== "" &&
                    self.input.value !== undefined) {
                    updateTime();
                }
                self.close();
                if (self.config &&
                    self.config.mode === "range" &&
                    self.selectedDates.length === 1)
                    self.clear(false);
            }
        }
    }
    function changeYear(newYear) {
        if (!newYear ||
            (self.config.minDate && newYear < self.config.minDate.getFullYear()) ||
            (self.config.maxDate && newYear > self.config.maxDate.getFullYear()))
            return;
        var newYearNum = newYear, isNewYear = self.currentYear !== newYearNum;
        self.currentYear = newYearNum || self.currentYear;
        if (self.config.maxDate &&
            self.currentYear === self.config.maxDate.getFullYear()) {
            self.currentMonth = Math.min(self.config.maxDate.getMonth(), self.currentMonth);
        }
        else if (self.config.minDate &&
            self.currentYear === self.config.minDate.getFullYear()) {
            self.currentMonth = Math.max(self.config.minDate.getMonth(), self.currentMonth);
        }
        if (isNewYear) {
            self.redraw();
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
    }
    function isEnabled(date, timeless) {
        var _a;
        if (timeless === void 0) { timeless = true; }
        var dateToCheck = self.parseDate(date, undefined, timeless);
        if ((self.config.minDate &&
            dateToCheck &&
            (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(dateToCheck, self.config.minDate, timeless !== undefined ? timeless : !self.minDateHasTime) < 0) ||
            (self.config.maxDate &&
                dateToCheck &&
                (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(dateToCheck, self.config.maxDate, timeless !== undefined ? timeless : !self.maxDateHasTime) > 0))
            return false;
        if (!self.config.enable && self.config.disable.length === 0)
            return true;
        if (dateToCheck === undefined)
            return false;
        var bool = !!self.config.enable, array = (_a = self.config.enable) !== null && _a !== void 0 ? _a : self.config.disable;
        for (var i = 0, d = void 0; i < array.length; i++) {
            d = array[i];
            if (typeof d === "function" &&
                d(dateToCheck))
                return bool;
            else if (d instanceof Date &&
                dateToCheck !== undefined &&
                d.getTime() === dateToCheck.getTime())
                return bool;
            else if (typeof d === "string") {
                var parsed = self.parseDate(d, undefined, true);
                return parsed && parsed.getTime() === dateToCheck.getTime()
                    ? bool
                    : !bool;
            }
            else if (typeof d === "object" &&
                dateToCheck !== undefined &&
                d.from &&
                d.to &&
                dateToCheck.getTime() >= d.from.getTime() &&
                dateToCheck.getTime() <= d.to.getTime())
                return bool;
        }
        return !bool;
    }
    function isInView(elem) {
        if (self.daysContainer !== undefined)
            return (elem.className.indexOf("hidden") === -1 &&
                elem.className.indexOf("flatpickr-disabled") === -1 &&
                self.daysContainer.contains(elem));
        return false;
    }
    function onBlur(e) {
        var isInput = e.target === self._input;
        var valueChanged = self._input.value.trimEnd() !== getDateStr();
        if (isInput &&
            valueChanged &&
            !(e.relatedTarget && isCalendarElem(e.relatedTarget))) {
            self.setDate(self._input.value, true, e.target === self.altInput
                ? self.config.altFormat
                : self.config.dateFormat);
        }
    }
    function onKeyDown(e) {
        var eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
        var isInput = self.config.wrap
            ? element.contains(eventTarget)
            : eventTarget === self._input;
        var allowInput = self.config.allowInput;
        var allowKeydown = self.isOpen && (!allowInput || !isInput);
        var allowInlineKeydown = self.config.inline && isInput && !allowInput;
        if (e.keyCode === 13 && isInput) {
            if (allowInput) {
                self.setDate(self._input.value, true, eventTarget === self.altInput
                    ? self.config.altFormat
                    : self.config.dateFormat);
                self.close();
                return eventTarget.blur();
            }
            else {
                self.open();
            }
        }
        else if (isCalendarElem(eventTarget) ||
            allowKeydown ||
            allowInlineKeydown) {
            var isTimeObj = !!self.timeContainer &&
                self.timeContainer.contains(eventTarget);
            switch (e.keyCode) {
                case 13:
                    if (isTimeObj) {
                        e.preventDefault();
                        updateTime();
                        focusAndClose();
                    }
                    else
                        selectDate(e);
                    break;
                case 27:
                    e.preventDefault();
                    focusAndClose();
                    break;
                case 8:
                case 46:
                    if (isInput && !self.config.allowInput) {
                        e.preventDefault();
                        self.clear();
                    }
                    break;
                case 37:
                case 39:
                    if (!isTimeObj && !isInput) {
                        e.preventDefault();
                        var activeElement = getClosestActiveElement();
                        if (self.daysContainer !== undefined &&
                            (allowInput === false ||
                                (activeElement && isInView(activeElement)))) {
                            var delta_1 = e.keyCode === 39 ? 1 : -1;
                            if (!e.ctrlKey)
                                focusOnDay(undefined, delta_1);
                            else {
                                e.stopPropagation();
                                changeMonth(delta_1);
                                focusOnDay(getFirstAvailableDay(1), 0);
                            }
                        }
                    }
                    else if (self.hourElement)
                        self.hourElement.focus();
                    break;
                case 38:
                case 40:
                    e.preventDefault();
                    var delta = e.keyCode === 40 ? 1 : -1;
                    if ((self.daysContainer &&
                        eventTarget.$i !== undefined) ||
                        eventTarget === self.input ||
                        eventTarget === self.altInput) {
                        if (e.ctrlKey) {
                            e.stopPropagation();
                            changeYear(self.currentYear - delta);
                            focusOnDay(getFirstAvailableDay(1), 0);
                        }
                        else if (!isTimeObj)
                            focusOnDay(undefined, delta * 7);
                    }
                    else if (eventTarget === self.currentYearElement) {
                        changeYear(self.currentYear - delta);
                    }
                    else if (self.config.enableTime) {
                        if (!isTimeObj && self.hourElement)
                            self.hourElement.focus();
                        updateTime(e);
                        self._debouncedChange();
                    }
                    break;
                case 9:
                    if (isTimeObj) {
                        var elems = [
                            self.hourElement,
                            self.minuteElement,
                            self.secondElement,
                            self.amPM,
                        ]
                            .concat(self.pluginElements)
                            .filter(function (x) { return x; });
                        var i = elems.indexOf(eventTarget);
                        if (i !== -1) {
                            var target = elems[i + (e.shiftKey ? -1 : 1)];
                            e.preventDefault();
                            (target || self._input).focus();
                        }
                    }
                    else if (!self.config.noCalendar &&
                        self.daysContainer &&
                        self.daysContainer.contains(eventTarget) &&
                        e.shiftKey) {
                        e.preventDefault();
                        self._input.focus();
                    }
                    break;
                default:
                    break;
            }
        }
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            switch (e.key) {
                case self.l10n.amPM[0].charAt(0):
                case self.l10n.amPM[0].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[0];
                    setHoursFromInputs();
                    updateValue();
                    break;
                case self.l10n.amPM[1].charAt(0):
                case self.l10n.amPM[1].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[1];
                    setHoursFromInputs();
                    updateValue();
                    break;
            }
        }
        if (isInput || isCalendarElem(eventTarget)) {
            triggerEvent("onKeyDown", e);
        }
    }
    function onMouseOver(elem, cellClass) {
        if (cellClass === void 0) { cellClass = "flatpickr-day"; }
        if (self.selectedDates.length !== 1 ||
            (elem &&
                (!elem.classList.contains(cellClass) ||
                    elem.classList.contains("flatpickr-disabled"))))
            return;
        var hoverDate = elem
            ? elem.dateObj.getTime()
            : self.days.firstElementChild.dateObj.getTime(), initialDate = self.parseDate(self.selectedDates[0], undefined, true).getTime(), rangeStartDate = Math.min(hoverDate, self.selectedDates[0].getTime()), rangeEndDate = Math.max(hoverDate, self.selectedDates[0].getTime());
        var containsDisabled = false;
        var minRange = 0, maxRange = 0;
        for (var t = rangeStartDate; t < rangeEndDate; t += _utils_dates__WEBPACK_IMPORTED_MODULE_4__.duration.DAY) {
            if (!isEnabled(new Date(t), true)) {
                containsDisabled =
                    containsDisabled || (t > rangeStartDate && t < rangeEndDate);
                if (t < initialDate && (!minRange || t > minRange))
                    minRange = t;
                else if (t > initialDate && (!maxRange || t < maxRange))
                    maxRange = t;
            }
        }
        var hoverableCells = Array.from(self.rContainer.querySelectorAll("*:nth-child(-n+" + self.config.showMonths + ") > ." + cellClass));
        hoverableCells.forEach(function (dayElem) {
            var date = dayElem.dateObj;
            var timestamp = date.getTime();
            var outOfRange = (minRange > 0 && timestamp < minRange) ||
                (maxRange > 0 && timestamp > maxRange);
            if (outOfRange) {
                dayElem.classList.add("notAllowed");
                ["inRange", "startRange", "endRange"].forEach(function (c) {
                    dayElem.classList.remove(c);
                });
                return;
            }
            else if (containsDisabled && !outOfRange)
                return;
            ["startRange", "inRange", "endRange", "notAllowed"].forEach(function (c) {
                dayElem.classList.remove(c);
            });
            if (elem !== undefined) {
                elem.classList.add(hoverDate <= self.selectedDates[0].getTime()
                    ? "startRange"
                    : "endRange");
                if (initialDate < hoverDate && timestamp === initialDate)
                    dayElem.classList.add("startRange");
                else if (initialDate > hoverDate && timestamp === initialDate)
                    dayElem.classList.add("endRange");
                if (timestamp >= minRange &&
                    (maxRange === 0 || timestamp <= maxRange) &&
                    (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.isBetween)(timestamp, initialDate, hoverDate))
                    dayElem.classList.add("inRange");
            }
        });
    }
    function onResize() {
        if (self.isOpen && !self.config.static && !self.config.inline)
            positionCalendar();
    }
    function open(e, positionElement) {
        if (positionElement === void 0) { positionElement = self._positionElement; }
        if (self.isMobile === true) {
            if (e) {
                e.preventDefault();
                var eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
                if (eventTarget) {
                    eventTarget.blur();
                }
            }
            if (self.mobileInput !== undefined) {
                self.mobileInput.focus();
                self.mobileInput.click();
            }
            triggerEvent("onOpen");
            return;
        }
        else if (self._input.disabled || self.config.inline) {
            return;
        }
        var wasOpen = self.isOpen;
        self.isOpen = true;
        if (!wasOpen) {
            self.calendarContainer.classList.add("open");
            self._input.classList.add("active");
            triggerEvent("onOpen");
            positionCalendar(positionElement);
        }
        if (self.config.enableTime === true && self.config.noCalendar === true) {
            if (self.config.allowInput === false &&
                (e === undefined ||
                    !self.timeContainer.contains(e.relatedTarget))) {
                setTimeout(function () { return self.hourElement.select(); }, 50);
            }
        }
    }
    function minMaxDateSetter(type) {
        return function (date) {
            var dateObj = (self.config["_" + type + "Date"] = self.parseDate(date, self.config.dateFormat));
            var inverseDateObj = self.config["_" + (type === "min" ? "max" : "min") + "Date"];
            if (dateObj !== undefined) {
                self[type === "min" ? "minDateHasTime" : "maxDateHasTime"] =
                    dateObj.getHours() > 0 ||
                        dateObj.getMinutes() > 0 ||
                        dateObj.getSeconds() > 0;
            }
            if (self.selectedDates) {
                self.selectedDates = self.selectedDates.filter(function (d) { return isEnabled(d); });
                if (!self.selectedDates.length && type === "min")
                    setHoursFromDate(dateObj);
                updateValue();
            }
            if (self.daysContainer) {
                redraw();
                if (dateObj !== undefined)
                    self.currentYearElement[type] = dateObj.getFullYear().toString();
                else
                    self.currentYearElement.removeAttribute(type);
                self.currentYearElement.disabled =
                    !!inverseDateObj &&
                        dateObj !== undefined &&
                        inverseDateObj.getFullYear() === dateObj.getFullYear();
            }
        };
    }
    function parseConfig() {
        var boolOpts = [
            "wrap",
            "weekNumbers",
            "allowInput",
            "allowInvalidPreload",
            "clickOpens",
            "time_24hr",
            "enableTime",
            "noCalendar",
            "altInput",
            "shorthandCurrentMonth",
            "inline",
            "static",
            "enableSeconds",
            "disableMobile",
        ];
        var userConfig = __assign(__assign({}, JSON.parse(JSON.stringify(element.dataset || {}))), instanceConfig);
        var formats = {};
        self.config.parseDate = userConfig.parseDate;
        self.config.formatDate = userConfig.formatDate;
        Object.defineProperty(self.config, "enable", {
            get: function () { return self.config._enable; },
            set: function (dates) {
                self.config._enable = parseDateRules(dates);
            },
        });
        Object.defineProperty(self.config, "disable", {
            get: function () { return self.config._disable; },
            set: function (dates) {
                self.config._disable = parseDateRules(dates);
            },
        });
        var timeMode = userConfig.mode === "time";
        if (!userConfig.dateFormat && (userConfig.enableTime || timeMode)) {
            var defaultDateFormat = flatpickr.defaultConfig.dateFormat || _types_options__WEBPACK_IMPORTED_MODULE_0__.defaults.dateFormat;
            formats.dateFormat =
                userConfig.noCalendar || timeMode
                    ? "H:i" + (userConfig.enableSeconds ? ":S" : "")
                    : defaultDateFormat + " H:i" + (userConfig.enableSeconds ? ":S" : "");
        }
        if (userConfig.altInput &&
            (userConfig.enableTime || timeMode) &&
            !userConfig.altFormat) {
            var defaultAltFormat = flatpickr.defaultConfig.altFormat || _types_options__WEBPACK_IMPORTED_MODULE_0__.defaults.altFormat;
            formats.altFormat =
                userConfig.noCalendar || timeMode
                    ? "h:i" + (userConfig.enableSeconds ? ":S K" : " K")
                    : defaultAltFormat + (" h:i" + (userConfig.enableSeconds ? ":S" : "") + " K");
        }
        Object.defineProperty(self.config, "minDate", {
            get: function () { return self.config._minDate; },
            set: minMaxDateSetter("min"),
        });
        Object.defineProperty(self.config, "maxDate", {
            get: function () { return self.config._maxDate; },
            set: minMaxDateSetter("max"),
        });
        var minMaxTimeSetter = function (type) { return function (val) {
            self.config[type === "min" ? "_minTime" : "_maxTime"] = self.parseDate(val, "H:i:S");
        }; };
        Object.defineProperty(self.config, "minTime", {
            get: function () { return self.config._minTime; },
            set: minMaxTimeSetter("min"),
        });
        Object.defineProperty(self.config, "maxTime", {
            get: function () { return self.config._maxTime; },
            set: minMaxTimeSetter("max"),
        });
        if (userConfig.mode === "time") {
            self.config.noCalendar = true;
            self.config.enableTime = true;
        }
        Object.assign(self.config, formats, userConfig);
        for (var i = 0; i < boolOpts.length; i++)
            self.config[boolOpts[i]] =
                self.config[boolOpts[i]] === true ||
                    self.config[boolOpts[i]] === "true";
        _types_options__WEBPACK_IMPORTED_MODULE_0__.HOOKS.filter(function (hook) { return self.config[hook] !== undefined; }).forEach(function (hook) {
            self.config[hook] = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.arrayify)(self.config[hook] || []).map(bindToInstance);
        });
        self.isMobile =
            !self.config.disableMobile &&
                !self.config.inline &&
                self.config.mode === "single" &&
                !self.config.disable.length &&
                !self.config.enable &&
                !self.config.weekNumbers &&
                /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        for (var i = 0; i < self.config.plugins.length; i++) {
            var pluginConf = self.config.plugins[i](self) || {};
            for (var key in pluginConf) {
                if (_types_options__WEBPACK_IMPORTED_MODULE_0__.HOOKS.indexOf(key) > -1) {
                    self.config[key] = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.arrayify)(pluginConf[key])
                        .map(bindToInstance)
                        .concat(self.config[key]);
                }
                else if (typeof userConfig[key] === "undefined")
                    self.config[key] = pluginConf[key];
            }
        }
        if (!userConfig.altInputClass) {
            self.config.altInputClass =
                getInputElem().className + " " + self.config.altInputClass;
        }
        triggerEvent("onParseConfig");
    }
    function getInputElem() {
        return self.config.wrap
            ? element.querySelector("[data-input]")
            : element;
    }
    function setupLocale() {
        if (typeof self.config.locale !== "object" &&
            typeof flatpickr.l10ns[self.config.locale] === "undefined")
            self.config.errorHandler(new Error("flatpickr: invalid locale " + self.config.locale));
        self.l10n = __assign(__assign({}, flatpickr.l10ns.default), (typeof self.config.locale === "object"
            ? self.config.locale
            : self.config.locale !== "default"
                ? flatpickr.l10ns[self.config.locale]
                : undefined));
        _utils_formatting__WEBPACK_IMPORTED_MODULE_5__.tokenRegex.D = "(" + self.l10n.weekdays.shorthand.join("|") + ")";
        _utils_formatting__WEBPACK_IMPORTED_MODULE_5__.tokenRegex.l = "(" + self.l10n.weekdays.longhand.join("|") + ")";
        _utils_formatting__WEBPACK_IMPORTED_MODULE_5__.tokenRegex.M = "(" + self.l10n.months.shorthand.join("|") + ")";
        _utils_formatting__WEBPACK_IMPORTED_MODULE_5__.tokenRegex.F = "(" + self.l10n.months.longhand.join("|") + ")";
        _utils_formatting__WEBPACK_IMPORTED_MODULE_5__.tokenRegex.K = "(" + self.l10n.amPM[0] + "|" + self.l10n.amPM[1] + "|" + self.l10n.amPM[0].toLowerCase() + "|" + self.l10n.amPM[1].toLowerCase() + ")";
        var userConfig = __assign(__assign({}, instanceConfig), JSON.parse(JSON.stringify(element.dataset || {})));
        if (userConfig.time_24hr === undefined &&
            flatpickr.defaultConfig.time_24hr === undefined) {
            self.config.time_24hr = self.l10n.time_24hr;
        }
        self.formatDate = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.createDateFormatter)(self);
        self.parseDate = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.createDateParser)({ config: self.config, l10n: self.l10n });
    }
    function positionCalendar(customPositionElement) {
        if (typeof self.config.position === "function") {
            return void self.config.position(self, customPositionElement);
        }
        if (self.calendarContainer === undefined)
            return;
        triggerEvent("onPreCalendarPosition");
        var positionElement = customPositionElement || self._positionElement;
        var calendarHeight = Array.prototype.reduce.call(self.calendarContainer.children, (function (acc, child) { return acc + child.offsetHeight; }), 0), calendarWidth = self.calendarContainer.offsetWidth, configPos = self.config.position.split(" "), configPosVertical = configPos[0], configPosHorizontal = configPos.length > 1 ? configPos[1] : null, inputBounds = positionElement.getBoundingClientRect(), distanceFromBottom = window.innerHeight - inputBounds.bottom, showOnTop = configPosVertical === "above" ||
            (configPosVertical !== "below" &&
                distanceFromBottom < calendarHeight &&
                inputBounds.top > calendarHeight);
        var top = window.pageYOffset +
            inputBounds.top +
            (!showOnTop ? positionElement.offsetHeight + 2 : -calendarHeight - 2);
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "arrowTop", !showOnTop);
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "arrowBottom", showOnTop);
        if (self.config.inline)
            return;
        var left = window.pageXOffset + inputBounds.left;
        var isCenter = false;
        var isRight = false;
        if (configPosHorizontal === "center") {
            left -= (calendarWidth - inputBounds.width) / 2;
            isCenter = true;
        }
        else if (configPosHorizontal === "right") {
            left -= calendarWidth - inputBounds.width;
            isRight = true;
        }
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "arrowLeft", !isCenter && !isRight);
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "arrowCenter", isCenter);
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "arrowRight", isRight);
        var right = window.document.body.offsetWidth -
            (window.pageXOffset + inputBounds.right);
        var rightMost = left + calendarWidth > window.document.body.offsetWidth;
        var centerMost = right + calendarWidth > window.document.body.offsetWidth;
        (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "rightMost", rightMost);
        if (self.config.static)
            return;
        self.calendarContainer.style.top = top + "px";
        if (!rightMost) {
            self.calendarContainer.style.left = left + "px";
            self.calendarContainer.style.right = "auto";
        }
        else if (!centerMost) {
            self.calendarContainer.style.left = "auto";
            self.calendarContainer.style.right = right + "px";
        }
        else {
            var doc = getDocumentStyleSheet();
            if (doc === undefined)
                return;
            var bodyWidth = window.document.body.offsetWidth;
            var centerLeft = Math.max(0, bodyWidth / 2 - calendarWidth / 2);
            var centerBefore = ".flatpickr-calendar.centerMost:before";
            var centerAfter = ".flatpickr-calendar.centerMost:after";
            var centerIndex = doc.cssRules.length;
            var centerStyle = "{left:" + inputBounds.left + "px;right:auto;}";
            (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "rightMost", false);
            (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.toggleClass)(self.calendarContainer, "centerMost", true);
            doc.insertRule(centerBefore + "," + centerAfter + centerStyle, centerIndex);
            self.calendarContainer.style.left = centerLeft + "px";
            self.calendarContainer.style.right = "auto";
        }
    }
    function getDocumentStyleSheet() {
        var editableSheet = null;
        for (var i = 0; i < document.styleSheets.length; i++) {
            var sheet = document.styleSheets[i];
            if (!sheet.cssRules)
                continue;
            try {
                sheet.cssRules;
            }
            catch (err) {
                continue;
            }
            editableSheet = sheet;
            break;
        }
        return editableSheet != null ? editableSheet : createStyleSheet();
    }
    function createStyleSheet() {
        var style = document.createElement("style");
        document.head.appendChild(style);
        return style.sheet;
    }
    function redraw() {
        if (self.config.noCalendar || self.isMobile)
            return;
        buildMonthSwitch();
        updateNavigationCurrentMonth();
        buildDays();
    }
    function focusAndClose() {
        self._input.focus();
        if (window.navigator.userAgent.indexOf("MSIE") !== -1 ||
            navigator.msMaxTouchPoints !== undefined) {
            setTimeout(self.close, 0);
        }
        else {
            self.close();
        }
    }
    function selectDate(e) {
        e.preventDefault();
        e.stopPropagation();
        var isSelectable = function (day) {
            return day.classList &&
                day.classList.contains("flatpickr-day") &&
                !day.classList.contains("flatpickr-disabled") &&
                !day.classList.contains("notAllowed");
        };
        var t = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.findParent)((0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e), isSelectable);
        if (t === undefined)
            return;
        var target = t;
        var selectedDate = (self.latestSelectedDateObj = new Date(target.dateObj.getTime()));
        var shouldChangeMonth = (selectedDate.getMonth() < self.currentMonth ||
            selectedDate.getMonth() >
                self.currentMonth + self.config.showMonths - 1) &&
            self.config.mode !== "range";
        self.selectedDateElem = target;
        if (self.config.mode === "single")
            self.selectedDates = [selectedDate];
        else if (self.config.mode === "multiple") {
            var selectedIndex = isDateSelected(selectedDate);
            if (selectedIndex)
                self.selectedDates.splice(parseInt(selectedIndex), 1);
            else
                self.selectedDates.push(selectedDate);
        }
        else if (self.config.mode === "range") {
            if (self.selectedDates.length === 2) {
                self.clear(false, false);
            }
            self.latestSelectedDateObj = selectedDate;
            self.selectedDates.push(selectedDate);
            if ((0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(selectedDate, self.selectedDates[0], true) !== 0)
                self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
        }
        setHoursFromInputs();
        if (shouldChangeMonth) {
            var isNewYear = self.currentYear !== selectedDate.getFullYear();
            self.currentYear = selectedDate.getFullYear();
            self.currentMonth = selectedDate.getMonth();
            if (isNewYear) {
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
            triggerEvent("onMonthChange");
        }
        updateNavigationCurrentMonth();
        buildDays();
        updateValue();
        if (!shouldChangeMonth &&
            self.config.mode !== "range" &&
            self.config.showMonths === 1)
            focusOnDayElem(target);
        else if (self.selectedDateElem !== undefined &&
            self.hourElement === undefined) {
            self.selectedDateElem && self.selectedDateElem.focus();
        }
        if (self.hourElement !== undefined)
            self.hourElement !== undefined && self.hourElement.focus();
        if (self.config.closeOnSelect) {
            var single = self.config.mode === "single" && !self.config.enableTime;
            var range = self.config.mode === "range" &&
                self.selectedDates.length === 2 &&
                !self.config.enableTime;
            if (single || range) {
                focusAndClose();
            }
        }
        triggerChange();
    }
    var CALLBACKS = {
        locale: [setupLocale, updateWeekdays],
        showMonths: [buildMonths, setCalendarWidth, buildWeekdays],
        minDate: [jumpToDate],
        maxDate: [jumpToDate],
        positionElement: [updatePositionElement],
        clickOpens: [
            function () {
                if (self.config.clickOpens === true) {
                    bind(self._input, "focus", self.open);
                    bind(self._input, "click", self.open);
                }
                else {
                    self._input.removeEventListener("focus", self.open);
                    self._input.removeEventListener("click", self.open);
                }
            },
        ],
    };
    function set(option, value) {
        if (option !== null && typeof option === "object") {
            Object.assign(self.config, option);
            for (var key in option) {
                if (CALLBACKS[key] !== undefined)
                    CALLBACKS[key].forEach(function (x) { return x(); });
            }
        }
        else {
            self.config[option] = value;
            if (CALLBACKS[option] !== undefined)
                CALLBACKS[option].forEach(function (x) { return x(); });
            else if (_types_options__WEBPACK_IMPORTED_MODULE_0__.HOOKS.indexOf(option) > -1)
                self.config[option] = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.arrayify)(value);
        }
        self.redraw();
        updateValue(true);
    }
    function setSelectedDate(inputDate, format) {
        var dates = [];
        if (inputDate instanceof Array)
            dates = inputDate.map(function (d) { return self.parseDate(d, format); });
        else if (inputDate instanceof Date || typeof inputDate === "number")
            dates = [self.parseDate(inputDate, format)];
        else if (typeof inputDate === "string") {
            switch (self.config.mode) {
                case "single":
                case "time":
                    dates = [self.parseDate(inputDate, format)];
                    break;
                case "multiple":
                    dates = inputDate
                        .split(self.config.conjunction)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
                case "range":
                    dates = inputDate
                        .split(self.l10n.rangeSeparator)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
                default:
                    break;
            }
        }
        else
            self.config.errorHandler(new Error("Invalid date supplied: " + JSON.stringify(inputDate)));
        self.selectedDates = (self.config.allowInvalidPreload
            ? dates
            : dates.filter(function (d) { return d instanceof Date && isEnabled(d, false); }));
        if (self.config.mode === "range")
            self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
    }
    function setDate(date, triggerChange, format) {
        if (triggerChange === void 0) { triggerChange = false; }
        if (format === void 0) { format = self.config.dateFormat; }
        if ((date !== 0 && !date) || (date instanceof Array && date.length === 0))
            return self.clear(triggerChange);
        setSelectedDate(date, format);
        self.latestSelectedDateObj =
            self.selectedDates[self.selectedDates.length - 1];
        self.redraw();
        jumpToDate(undefined, triggerChange);
        setHoursFromDate();
        if (self.selectedDates.length === 0) {
            self.clear(false);
        }
        updateValue(triggerChange);
        if (triggerChange)
            triggerEvent("onChange");
    }
    function parseDateRules(arr) {
        return arr
            .slice()
            .map(function (rule) {
            if (typeof rule === "string" ||
                typeof rule === "number" ||
                rule instanceof Date) {
                return self.parseDate(rule, undefined, true);
            }
            else if (rule &&
                typeof rule === "object" &&
                rule.from &&
                rule.to)
                return {
                    from: self.parseDate(rule.from, undefined),
                    to: self.parseDate(rule.to, undefined),
                };
            return rule;
        })
            .filter(function (x) { return x; });
    }
    function setupDates() {
        self.selectedDates = [];
        self.now = self.parseDate(self.config.now) || new Date();
        var preloadedDate = self.config.defaultDate ||
            ((self.input.nodeName === "INPUT" ||
                self.input.nodeName === "TEXTAREA") &&
                self.input.placeholder &&
                self.input.value === self.input.placeholder
                ? null
                : self.input.value);
        if (preloadedDate)
            setSelectedDate(preloadedDate, self.config.dateFormat);
        self._initialDate =
            self.selectedDates.length > 0
                ? self.selectedDates[0]
                : self.config.minDate &&
                    self.config.minDate.getTime() > self.now.getTime()
                    ? self.config.minDate
                    : self.config.maxDate &&
                        self.config.maxDate.getTime() < self.now.getTime()
                        ? self.config.maxDate
                        : self.now;
        self.currentYear = self._initialDate.getFullYear();
        self.currentMonth = self._initialDate.getMonth();
        if (self.selectedDates.length > 0)
            self.latestSelectedDateObj = self.selectedDates[0];
        if (self.config.minTime !== undefined)
            self.config.minTime = self.parseDate(self.config.minTime, "H:i");
        if (self.config.maxTime !== undefined)
            self.config.maxTime = self.parseDate(self.config.maxTime, "H:i");
        self.minDateHasTime =
            !!self.config.minDate &&
                (self.config.minDate.getHours() > 0 ||
                    self.config.minDate.getMinutes() > 0 ||
                    self.config.minDate.getSeconds() > 0);
        self.maxDateHasTime =
            !!self.config.maxDate &&
                (self.config.maxDate.getHours() > 0 ||
                    self.config.maxDate.getMinutes() > 0 ||
                    self.config.maxDate.getSeconds() > 0);
    }
    function setupInputs() {
        self.input = getInputElem();
        if (!self.input) {
            self.config.errorHandler(new Error("Invalid input element specified"));
            return;
        }
        self.input._type = self.input.type;
        self.input.type = "text";
        self.input.classList.add("flatpickr-input");
        self._input = self.input;
        if (self.config.altInput) {
            self.altInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)(self.input.nodeName, self.config.altInputClass);
            self._input = self.altInput;
            self.altInput.placeholder = self.input.placeholder;
            self.altInput.disabled = self.input.disabled;
            self.altInput.required = self.input.required;
            self.altInput.tabIndex = self.input.tabIndex;
            self.altInput.type = "text";
            self.input.setAttribute("type", "hidden");
            if (!self.config.static && self.input.parentNode)
                self.input.parentNode.insertBefore(self.altInput, self.input.nextSibling);
        }
        if (!self.config.allowInput)
            self._input.setAttribute("readonly", "readonly");
        updatePositionElement();
    }
    function updatePositionElement() {
        self._positionElement = self.config.positionElement || self._input;
    }
    function setupMobile() {
        var inputType = self.config.enableTime
            ? self.config.noCalendar
                ? "time"
                : "datetime-local"
            : "date";
        self.mobileInput = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.createElement)("input", self.input.className + " flatpickr-mobile");
        self.mobileInput.tabIndex = 1;
        self.mobileInput.type = inputType;
        self.mobileInput.disabled = self.input.disabled;
        self.mobileInput.required = self.input.required;
        self.mobileInput.placeholder = self.input.placeholder;
        self.mobileFormatStr =
            inputType === "datetime-local"
                ? "Y-m-d\\TH:i:S"
                : inputType === "date"
                    ? "Y-m-d"
                    : "H:i:S";
        if (self.selectedDates.length > 0) {
            self.mobileInput.defaultValue = self.mobileInput.value = self.formatDate(self.selectedDates[0], self.mobileFormatStr);
        }
        if (self.config.minDate)
            self.mobileInput.min = self.formatDate(self.config.minDate, "Y-m-d");
        if (self.config.maxDate)
            self.mobileInput.max = self.formatDate(self.config.maxDate, "Y-m-d");
        if (self.input.getAttribute("step"))
            self.mobileInput.step = String(self.input.getAttribute("step"));
        self.input.type = "hidden";
        if (self.altInput !== undefined)
            self.altInput.type = "hidden";
        try {
            if (self.input.parentNode)
                self.input.parentNode.insertBefore(self.mobileInput, self.input.nextSibling);
        }
        catch (_a) { }
        bind(self.mobileInput, "change", function (e) {
            self.setDate((0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e).value, false, self.mobileFormatStr);
            triggerEvent("onChange");
            triggerEvent("onClose");
        });
    }
    function toggle(e) {
        if (self.isOpen === true)
            return self.close();
        self.open(e);
    }
    function triggerEvent(event, data) {
        if (self.config === undefined)
            return;
        var hooks = self.config[event];
        if (hooks !== undefined && hooks.length > 0) {
            for (var i = 0; hooks[i] && i < hooks.length; i++)
                hooks[i](self.selectedDates, self.input.value, self, data);
        }
        if (event === "onChange") {
            self.input.dispatchEvent(createEvent("change"));
            self.input.dispatchEvent(createEvent("input"));
        }
    }
    function createEvent(name) {
        var e = document.createEvent("Event");
        e.initEvent(name, true, true);
        return e;
    }
    function isDateSelected(date) {
        for (var i = 0; i < self.selectedDates.length; i++) {
            var selectedDate = self.selectedDates[i];
            if (selectedDate instanceof Date &&
                (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(selectedDate, date) === 0)
                return "" + i;
        }
        return false;
    }
    function isDateInRange(date) {
        if (self.config.mode !== "range" || self.selectedDates.length < 2)
            return false;
        return ((0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(date, self.selectedDates[0]) >= 0 &&
            (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates)(date, self.selectedDates[1]) <= 0);
    }
    function updateNavigationCurrentMonth() {
        if (self.config.noCalendar || self.isMobile || !self.monthNav)
            return;
        self.yearElements.forEach(function (yearElement, i) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            if (self.config.showMonths > 1 ||
                self.config.monthSelectorType === "static") {
                self.monthElements[i].textContent =
                    (0,_utils_formatting__WEBPACK_IMPORTED_MODULE_5__.monthToStr)(d.getMonth(), self.config.shorthandCurrentMonth, self.l10n) + " ";
            }
            else {
                self.monthsDropdownContainer.value = d.getMonth().toString();
            }
            yearElement.value = d.getFullYear().toString();
        });
        self._hidePrevMonthArrow =
            self.config.minDate !== undefined &&
                (self.currentYear === self.config.minDate.getFullYear()
                    ? self.currentMonth <= self.config.minDate.getMonth()
                    : self.currentYear < self.config.minDate.getFullYear());
        self._hideNextMonthArrow =
            self.config.maxDate !== undefined &&
                (self.currentYear === self.config.maxDate.getFullYear()
                    ? self.currentMonth + 1 > self.config.maxDate.getMonth()
                    : self.currentYear > self.config.maxDate.getFullYear());
    }
    function getDateStr(specificFormat) {
        var format = specificFormat ||
            (self.config.altInput ? self.config.altFormat : self.config.dateFormat);
        return self.selectedDates
            .map(function (dObj) { return self.formatDate(dObj, format); })
            .filter(function (d, i, arr) {
            return self.config.mode !== "range" ||
                self.config.enableTime ||
                arr.indexOf(d) === i;
        })
            .join(self.config.mode !== "range"
            ? self.config.conjunction
            : self.l10n.rangeSeparator);
    }
    function updateValue(triggerChange) {
        if (triggerChange === void 0) { triggerChange = true; }
        if (self.mobileInput !== undefined && self.mobileFormatStr) {
            self.mobileInput.value =
                self.latestSelectedDateObj !== undefined
                    ? self.formatDate(self.latestSelectedDateObj, self.mobileFormatStr)
                    : "";
        }
        self.input.value = getDateStr(self.config.dateFormat);
        if (self.altInput !== undefined) {
            self.altInput.value = getDateStr(self.config.altFormat);
        }
        if (triggerChange !== false)
            triggerEvent("onValueUpdate");
    }
    function onMonthNavClick(e) {
        var eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e);
        var isPrevMonth = self.prevMonthNav.contains(eventTarget);
        var isNextMonth = self.nextMonthNav.contains(eventTarget);
        if (isPrevMonth || isNextMonth) {
            changeMonth(isPrevMonth ? -1 : 1);
        }
        else if (self.yearElements.indexOf(eventTarget) >= 0) {
            eventTarget.select();
        }
        else if (eventTarget.classList.contains("arrowUp")) {
            self.changeYear(self.currentYear + 1);
        }
        else if (eventTarget.classList.contains("arrowDown")) {
            self.changeYear(self.currentYear - 1);
        }
    }
    function timeWrapper(e) {
        e.preventDefault();
        var isKeyDown = e.type === "keydown", eventTarget = (0,_utils_dom__WEBPACK_IMPORTED_MODULE_3__.getEventTarget)(e), input = eventTarget;
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            self.amPM.textContent =
                self.l10n.amPM[(0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(self.amPM.textContent === self.l10n.amPM[0])];
        }
        var min = parseFloat(input.getAttribute("min")), max = parseFloat(input.getAttribute("max")), step = parseFloat(input.getAttribute("step")), curValue = parseInt(input.value, 10), delta = e.delta ||
            (isKeyDown ? (e.which === 38 ? 1 : -1) : 0);
        var newValue = curValue + step * delta;
        if (typeof input.value !== "undefined" && input.value.length === 2) {
            var isHourElem = input === self.hourElement, isMinuteElem = input === self.minuteElement;
            if (newValue < min) {
                newValue =
                    max +
                        newValue +
                        (0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(!isHourElem) +
                        ((0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(isHourElem) && (0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(!self.amPM));
                if (isMinuteElem)
                    incrementNumInput(undefined, -1, self.hourElement);
            }
            else if (newValue > max) {
                newValue =
                    input === self.hourElement ? newValue - max - (0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(!self.amPM) : min;
                if (isMinuteElem)
                    incrementNumInput(undefined, 1, self.hourElement);
            }
            if (self.amPM &&
                isHourElem &&
                (step === 1
                    ? newValue + curValue === 23
                    : Math.abs(newValue - curValue) > step)) {
                self.amPM.textContent =
                    self.l10n.amPM[(0,_utils__WEBPACK_IMPORTED_MODULE_2__.int)(self.amPM.textContent === self.l10n.amPM[0])];
            }
            input.value = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.pad)(newValue);
        }
    }
    init();
    return self;
}
function _flatpickr(nodeList, config) {
    var nodes = Array.prototype.slice
        .call(nodeList)
        .filter(function (x) { return x instanceof HTMLElement; });
    var instances = [];
    for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];
        try {
            if (node.getAttribute("data-fp-omit") !== null)
                continue;
            if (node._flatpickr !== undefined) {
                node._flatpickr.destroy();
                node._flatpickr = undefined;
            }
            node._flatpickr = FlatpickrInstance(node, config || {});
            instances.push(node._flatpickr);
        }
        catch (e) {
            console.error(e);
        }
    }
    return instances.length === 1 ? instances[0] : instances;
}
if (typeof HTMLElement !== "undefined" &&
    typeof HTMLCollection !== "undefined" &&
    typeof NodeList !== "undefined") {
    HTMLCollection.prototype.flatpickr = NodeList.prototype.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
    HTMLElement.prototype.flatpickr = function (config) {
        return _flatpickr([this], config);
    };
}
var flatpickr = function (selector, config) {
    if (typeof selector === "string") {
        return _flatpickr(window.document.querySelectorAll(selector), config);
    }
    else if (selector instanceof Node) {
        return _flatpickr([selector], config);
    }
    else {
        return _flatpickr(selector, config);
    }
};
flatpickr.defaultConfig = {};
flatpickr.l10ns = {
    en: __assign({}, _l10n_default__WEBPACK_IMPORTED_MODULE_1__["default"]),
    default: __assign({}, _l10n_default__WEBPACK_IMPORTED_MODULE_1__["default"]),
};
flatpickr.localize = function (l10n) {
    flatpickr.l10ns.default = __assign(__assign({}, flatpickr.l10ns.default), l10n);
};
flatpickr.setDefaults = function (config) {
    flatpickr.defaultConfig = __assign(__assign({}, flatpickr.defaultConfig), config);
};
flatpickr.parseDate = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.createDateParser)({});
flatpickr.formatDate = (0,_utils_dates__WEBPACK_IMPORTED_MODULE_4__.createDateFormatter)({});
flatpickr.compareDates = _utils_dates__WEBPACK_IMPORTED_MODULE_4__.compareDates;
if (typeof jQuery !== "undefined" && typeof jQuery.fn !== "undefined") {
    jQuery.fn.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
}
Date.prototype.fp_incr = function (days) {
    return new Date(this.getFullYear(), this.getMonth(), this.getDate() + (typeof days === "string" ? parseInt(days, 10) : days));
};
if (typeof window !== "undefined") {
    window.flatpickr = flatpickr;
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (flatpickr);


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/l10n/default.js":
/*!*********************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/l10n/default.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   english: () => (/* binding */ english)
/* harmony export */ });
var english = {
    weekdays: {
        shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        longhand: [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ],
    },
    months: {
        shorthand: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ],
        longhand: [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ],
    },
    daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    firstDayOfWeek: 0,
    ordinal: function (nth) {
        var s = nth % 100;
        if (s > 3 && s < 21)
            return "th";
        switch (s % 10) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    },
    rangeSeparator: " to ",
    weekAbbreviation: "Wk",
    scrollTitle: "Scroll to increment",
    toggleTitle: "Click to toggle",
    amPM: ["AM", "PM"],
    yearAriaLabel: "Year",
    monthAriaLabel: "Month",
    hourAriaLabel: "Hour",
    minuteAriaLabel: "Minute",
    time_24hr: false,
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (english);


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/types/options.js":
/*!**********************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/types/options.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   HOOKS: () => (/* binding */ HOOKS),
/* harmony export */   defaults: () => (/* binding */ defaults)
/* harmony export */ });
var HOOKS = [
    "onChange",
    "onClose",
    "onDayCreate",
    "onDestroy",
    "onKeyDown",
    "onMonthChange",
    "onOpen",
    "onParseConfig",
    "onReady",
    "onValueUpdate",
    "onYearChange",
    "onPreCalendarPosition",
];
var defaults = {
    _disable: [],
    allowInput: false,
    allowInvalidPreload: false,
    altFormat: "F j, Y",
    altInput: false,
    altInputClass: "form-control input",
    animate: typeof window === "object" &&
        window.navigator.userAgent.indexOf("MSIE") === -1,
    ariaDateFormat: "F j, Y",
    autoFillDefaultTime: true,
    clickOpens: true,
    closeOnSelect: true,
    conjunction: ", ",
    dateFormat: "Y-m-d",
    defaultHour: 12,
    defaultMinute: 0,
    defaultSeconds: 0,
    disable: [],
    disableMobile: false,
    enableSeconds: false,
    enableTime: false,
    errorHandler: function (err) {
        return typeof console !== "undefined" && console.warn(err);
    },
    getWeek: function (givenDate) {
        var date = new Date(givenDate.getTime());
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
        var week1 = new Date(date.getFullYear(), 0, 4);
        return (1 +
            Math.round(((date.getTime() - week1.getTime()) / 86400000 -
                3 +
                ((week1.getDay() + 6) % 7)) /
                7));
    },
    hourIncrement: 1,
    ignoredFocusElements: [],
    inline: false,
    locale: "default",
    minuteIncrement: 5,
    mode: "single",
    monthSelectorType: "dropdown",
    nextArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",
    noCalendar: false,
    now: new Date(),
    onChange: [],
    onClose: [],
    onDayCreate: [],
    onDestroy: [],
    onKeyDown: [],
    onMonthChange: [],
    onOpen: [],
    onParseConfig: [],
    onReady: [],
    onValueUpdate: [],
    onYearChange: [],
    onPreCalendarPosition: [],
    plugins: [],
    position: "auto",
    positionElement: undefined,
    prevArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",
    shorthandCurrentMonth: false,
    showMonths: 1,
    static: false,
    time_24hr: false,
    weekNumbers: false,
    wrap: false,
};


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/utils/dates.js":
/*!********************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/utils/dates.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   calculateSecondsSinceMidnight: () => (/* binding */ calculateSecondsSinceMidnight),
/* harmony export */   compareDates: () => (/* binding */ compareDates),
/* harmony export */   compareTimes: () => (/* binding */ compareTimes),
/* harmony export */   createDateFormatter: () => (/* binding */ createDateFormatter),
/* harmony export */   createDateParser: () => (/* binding */ createDateParser),
/* harmony export */   duration: () => (/* binding */ duration),
/* harmony export */   getDefaultHours: () => (/* binding */ getDefaultHours),
/* harmony export */   isBetween: () => (/* binding */ isBetween),
/* harmony export */   parseSeconds: () => (/* binding */ parseSeconds)
/* harmony export */ });
/* harmony import */ var _formatting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./formatting */ "./node_modules/flatpickr/dist/esm/utils/formatting.js");
/* harmony import */ var _types_options__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../types/options */ "./node_modules/flatpickr/dist/esm/types/options.js");
/* harmony import */ var _l10n_default__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../l10n/default */ "./node_modules/flatpickr/dist/esm/l10n/default.js");



var createDateFormatter = function (_a) {
    var _b = _a.config, config = _b === void 0 ? _types_options__WEBPACK_IMPORTED_MODULE_1__.defaults : _b, _c = _a.l10n, l10n = _c === void 0 ? _l10n_default__WEBPACK_IMPORTED_MODULE_2__.english : _c, _d = _a.isMobile, isMobile = _d === void 0 ? false : _d;
    return function (dateObj, frmt, overrideLocale) {
        var locale = overrideLocale || l10n;
        if (config.formatDate !== undefined && !isMobile) {
            return config.formatDate(dateObj, frmt, locale);
        }
        return frmt
            .split("")
            .map(function (c, i, arr) {
            return _formatting__WEBPACK_IMPORTED_MODULE_0__.formats[c] && arr[i - 1] !== "\\"
                ? _formatting__WEBPACK_IMPORTED_MODULE_0__.formats[c](dateObj, locale, config)
                : c !== "\\"
                    ? c
                    : "";
        })
            .join("");
    };
};
var createDateParser = function (_a) {
    var _b = _a.config, config = _b === void 0 ? _types_options__WEBPACK_IMPORTED_MODULE_1__.defaults : _b, _c = _a.l10n, l10n = _c === void 0 ? _l10n_default__WEBPACK_IMPORTED_MODULE_2__.english : _c;
    return function (date, givenFormat, timeless, customLocale) {
        if (date !== 0 && !date)
            return undefined;
        var locale = customLocale || l10n;
        var parsedDate;
        var dateOrig = date;
        if (date instanceof Date)
            parsedDate = new Date(date.getTime());
        else if (typeof date !== "string" &&
            date.toFixed !== undefined)
            parsedDate = new Date(date);
        else if (typeof date === "string") {
            var format = givenFormat || (config || _types_options__WEBPACK_IMPORTED_MODULE_1__.defaults).dateFormat;
            var datestr = String(date).trim();
            if (datestr === "today") {
                parsedDate = new Date();
                timeless = true;
            }
            else if (config && config.parseDate) {
                parsedDate = config.parseDate(date, format);
            }
            else if (/Z$/.test(datestr) ||
                /GMT$/.test(datestr)) {
                parsedDate = new Date(date);
            }
            else {
                var matched = void 0, ops = [];
                for (var i = 0, matchIndex = 0, regexStr = ""; i < format.length; i++) {
                    var token = format[i];
                    var isBackSlash = token === "\\";
                    var escaped = format[i - 1] === "\\" || isBackSlash;
                    if (_formatting__WEBPACK_IMPORTED_MODULE_0__.tokenRegex[token] && !escaped) {
                        regexStr += _formatting__WEBPACK_IMPORTED_MODULE_0__.tokenRegex[token];
                        var match = new RegExp(regexStr).exec(date);
                        if (match && (matched = true)) {
                            ops[token !== "Y" ? "push" : "unshift"]({
                                fn: _formatting__WEBPACK_IMPORTED_MODULE_0__.revFormat[token],
                                val: match[++matchIndex],
                            });
                        }
                    }
                    else if (!isBackSlash)
                        regexStr += ".";
                }
                parsedDate =
                    !config || !config.noCalendar
                        ? new Date(new Date().getFullYear(), 0, 1, 0, 0, 0, 0)
                        : new Date(new Date().setHours(0, 0, 0, 0));
                ops.forEach(function (_a) {
                    var fn = _a.fn, val = _a.val;
                    return (parsedDate = fn(parsedDate, val, locale) || parsedDate);
                });
                parsedDate = matched ? parsedDate : undefined;
            }
        }
        if (!(parsedDate instanceof Date && !isNaN(parsedDate.getTime()))) {
            config.errorHandler(new Error("Invalid date provided: " + dateOrig));
            return undefined;
        }
        if (timeless === true)
            parsedDate.setHours(0, 0, 0, 0);
        return parsedDate;
    };
};
function compareDates(date1, date2, timeless) {
    if (timeless === void 0) { timeless = true; }
    if (timeless !== false) {
        return (new Date(date1.getTime()).setHours(0, 0, 0, 0) -
            new Date(date2.getTime()).setHours(0, 0, 0, 0));
    }
    return date1.getTime() - date2.getTime();
}
function compareTimes(date1, date2) {
    return (3600 * (date1.getHours() - date2.getHours()) +
        60 * (date1.getMinutes() - date2.getMinutes()) +
        date1.getSeconds() -
        date2.getSeconds());
}
var isBetween = function (ts, ts1, ts2) {
    return ts > Math.min(ts1, ts2) && ts < Math.max(ts1, ts2);
};
var calculateSecondsSinceMidnight = function (hours, minutes, seconds) {
    return hours * 3600 + minutes * 60 + seconds;
};
var parseSeconds = function (secondsSinceMidnight) {
    var hours = Math.floor(secondsSinceMidnight / 3600), minutes = (secondsSinceMidnight - hours * 3600) / 60;
    return [hours, minutes, secondsSinceMidnight - hours * 3600 - minutes * 60];
};
var duration = {
    DAY: 86400000,
};
function getDefaultHours(config) {
    var hours = config.defaultHour;
    var minutes = config.defaultMinute;
    var seconds = config.defaultSeconds;
    if (config.minDate !== undefined) {
        var minHour = config.minDate.getHours();
        var minMinutes = config.minDate.getMinutes();
        var minSeconds = config.minDate.getSeconds();
        if (hours < minHour) {
            hours = minHour;
        }
        if (hours === minHour && minutes < minMinutes) {
            minutes = minMinutes;
        }
        if (hours === minHour && minutes === minMinutes && seconds < minSeconds)
            seconds = config.minDate.getSeconds();
    }
    if (config.maxDate !== undefined) {
        var maxHr = config.maxDate.getHours();
        var maxMinutes = config.maxDate.getMinutes();
        hours = Math.min(hours, maxHr);
        if (hours === maxHr)
            minutes = Math.min(maxMinutes, minutes);
        if (hours === maxHr && minutes === maxMinutes)
            seconds = config.maxDate.getSeconds();
    }
    return { hours: hours, minutes: minutes, seconds: seconds };
}


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/utils/dom.js":
/*!******************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/utils/dom.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearNode: () => (/* binding */ clearNode),
/* harmony export */   createElement: () => (/* binding */ createElement),
/* harmony export */   createNumberInput: () => (/* binding */ createNumberInput),
/* harmony export */   findParent: () => (/* binding */ findParent),
/* harmony export */   getEventTarget: () => (/* binding */ getEventTarget),
/* harmony export */   toggleClass: () => (/* binding */ toggleClass)
/* harmony export */ });
function toggleClass(elem, className, bool) {
    if (bool === true)
        return elem.classList.add(className);
    elem.classList.remove(className);
}
function createElement(tag, className, content) {
    var e = window.document.createElement(tag);
    className = className || "";
    content = content || "";
    e.className = className;
    if (content !== undefined)
        e.textContent = content;
    return e;
}
function clearNode(node) {
    while (node.firstChild)
        node.removeChild(node.firstChild);
}
function findParent(node, condition) {
    if (condition(node))
        return node;
    else if (node.parentNode)
        return findParent(node.parentNode, condition);
    return undefined;
}
function createNumberInput(inputClassName, opts) {
    var wrapper = createElement("div", "numInputWrapper"), numInput = createElement("input", "numInput " + inputClassName), arrowUp = createElement("span", "arrowUp"), arrowDown = createElement("span", "arrowDown");
    if (navigator.userAgent.indexOf("MSIE 9.0") === -1) {
        numInput.type = "number";
    }
    else {
        numInput.type = "text";
        numInput.pattern = "\\d*";
    }
    if (opts !== undefined)
        for (var key in opts)
            numInput.setAttribute(key, opts[key]);
    wrapper.appendChild(numInput);
    wrapper.appendChild(arrowUp);
    wrapper.appendChild(arrowDown);
    return wrapper;
}
function getEventTarget(event) {
    try {
        if (typeof event.composedPath === "function") {
            var path = event.composedPath();
            return path[0];
        }
        return event.target;
    }
    catch (error) {
        return event.target;
    }
}


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/utils/formatting.js":
/*!*************************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/utils/formatting.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   formats: () => (/* binding */ formats),
/* harmony export */   monthToStr: () => (/* binding */ monthToStr),
/* harmony export */   revFormat: () => (/* binding */ revFormat),
/* harmony export */   tokenRegex: () => (/* binding */ tokenRegex)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils */ "./node_modules/flatpickr/dist/esm/utils/index.js");

var doNothing = function () { return undefined; };
var monthToStr = function (monthNumber, shorthand, locale) { return locale.months[shorthand ? "shorthand" : "longhand"][monthNumber]; };
var revFormat = {
    D: doNothing,
    F: function (dateObj, monthName, locale) {
        dateObj.setMonth(locale.months.longhand.indexOf(monthName));
    },
    G: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    H: function (dateObj, hour) {
        dateObj.setHours(parseFloat(hour));
    },
    J: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    K: function (dateObj, amPM, locale) {
        dateObj.setHours((dateObj.getHours() % 12) +
            12 * (0,_utils__WEBPACK_IMPORTED_MODULE_0__.int)(new RegExp(locale.amPM[1], "i").test(amPM)));
    },
    M: function (dateObj, shortMonth, locale) {
        dateObj.setMonth(locale.months.shorthand.indexOf(shortMonth));
    },
    S: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    U: function (_, unixSeconds) { return new Date(parseFloat(unixSeconds) * 1000); },
    W: function (dateObj, weekNum, locale) {
        var weekNumber = parseInt(weekNum);
        var date = new Date(dateObj.getFullYear(), 0, 2 + (weekNumber - 1) * 7, 0, 0, 0, 0);
        date.setDate(date.getDate() - date.getDay() + locale.firstDayOfWeek);
        return date;
    },
    Y: function (dateObj, year) {
        dateObj.setFullYear(parseFloat(year));
    },
    Z: function (_, ISODate) { return new Date(ISODate); },
    d: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    h: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    i: function (dateObj, minutes) {
        dateObj.setMinutes(parseFloat(minutes));
    },
    j: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    l: doNothing,
    m: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    n: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    s: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    u: function (_, unixMillSeconds) {
        return new Date(parseFloat(unixMillSeconds));
    },
    w: doNothing,
    y: function (dateObj, year) {
        dateObj.setFullYear(2000 + parseFloat(year));
    },
};
var tokenRegex = {
    D: "",
    F: "",
    G: "(\\d\\d|\\d)",
    H: "(\\d\\d|\\d)",
    J: "(\\d\\d|\\d)\\w+",
    K: "",
    M: "",
    S: "(\\d\\d|\\d)",
    U: "(.+)",
    W: "(\\d\\d|\\d)",
    Y: "(\\d{4})",
    Z: "(.+)",
    d: "(\\d\\d|\\d)",
    h: "(\\d\\d|\\d)",
    i: "(\\d\\d|\\d)",
    j: "(\\d\\d|\\d)",
    l: "",
    m: "(\\d\\d|\\d)",
    n: "(\\d\\d|\\d)",
    s: "(\\d\\d|\\d)",
    u: "(.+)",
    w: "(\\d\\d|\\d)",
    y: "(\\d{2})",
};
var formats = {
    Z: function (date) { return date.toISOString(); },
    D: function (date, locale, options) {
        return locale.weekdays.shorthand[formats.w(date, locale, options)];
    },
    F: function (date, locale, options) {
        return monthToStr(formats.n(date, locale, options) - 1, false, locale);
    },
    G: function (date, locale, options) {
        return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(formats.h(date, locale, options));
    },
    H: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getHours()); },
    J: function (date, locale) {
        return locale.ordinal !== undefined
            ? date.getDate() + locale.ordinal(date.getDate())
            : date.getDate();
    },
    K: function (date, locale) { return locale.amPM[(0,_utils__WEBPACK_IMPORTED_MODULE_0__.int)(date.getHours() > 11)]; },
    M: function (date, locale) {
        return monthToStr(date.getMonth(), true, locale);
    },
    S: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getSeconds()); },
    U: function (date) { return date.getTime() / 1000; },
    W: function (date, _, options) {
        return options.getWeek(date);
    },
    Y: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getFullYear(), 4); },
    d: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getDate()); },
    h: function (date) { return (date.getHours() % 12 ? date.getHours() % 12 : 12); },
    i: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getMinutes()); },
    j: function (date) { return date.getDate(); },
    l: function (date, locale) {
        return locale.weekdays.longhand[date.getDay()];
    },
    m: function (date) { return (0,_utils__WEBPACK_IMPORTED_MODULE_0__.pad)(date.getMonth() + 1); },
    n: function (date) { return date.getMonth() + 1; },
    s: function (date) { return date.getSeconds(); },
    u: function (date) { return date.getTime(); },
    w: function (date) { return date.getDay(); },
    y: function (date) { return String(date.getFullYear()).substring(2); },
};


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/utils/index.js":
/*!********************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/utils/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   arrayify: () => (/* binding */ arrayify),
/* harmony export */   debounce: () => (/* binding */ debounce),
/* harmony export */   int: () => (/* binding */ int),
/* harmony export */   pad: () => (/* binding */ pad)
/* harmony export */ });
var pad = function (number, length) {
    if (length === void 0) { length = 2; }
    return ("000" + number).slice(length * -1);
};
var int = function (bool) { return (bool === true ? 1 : 0); };
function debounce(fn, wait) {
    var t;
    return function () {
        var _this = this;
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { return fn.apply(_this, args); }, wait);
    };
}
var arrayify = function (obj) {
    return obj instanceof Array ? obj : [obj];
};


/***/ }),

/***/ "./node_modules/flatpickr/dist/esm/utils/polyfills.js":
/*!************************************************************!*\
  !*** ./node_modules/flatpickr/dist/esm/utils/polyfills.js ***!
  \************************************************************/
/***/ (() => {

"use strict";

if (typeof Object.assign !== "function") {
    Object.assign = function (target) {
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        if (!target) {
            throw TypeError("Cannot convert undefined or null to object");
        }
        var _loop_1 = function (source) {
            if (source) {
                Object.keys(source).forEach(function (key) { return (target[key] = source[key]); });
            }
        };
        for (var _a = 0, args_1 = args; _a < args_1.length; _a++) {
            var source = args_1[_a];
            _loop_1(source);
        }
        return target;
    };
}


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
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
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
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
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
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/js/backend/index.js");
/******/ 	
/******/ })()
;
//# sourceMappingURL=backend.js.map