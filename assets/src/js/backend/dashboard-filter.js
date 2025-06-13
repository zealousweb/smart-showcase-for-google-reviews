import flatpickr from "flatpickr";
import { zwssgrRenderDataCallback } from './render-data-callback';

document.addEventListener('DOMContentLoaded', function () {

    const zwssgrAccountSelect = document.getElementById('zwssgr-account-select');

    if (zwssgrAccountSelect) {

        zwssgrAccountSelect.addEventListener('change', function (zwssgrEv) {
            "use strict";

            zwssgrEv.preventDefault();

            const zwssgrAccountNumber = this.value;
            const zwssgrDropdown = this;
            const zwssgrDataFilter = document.getElementById('zwssgr-gmb-data-filter');
    
            zwssgrDropdown.classList.add('disabled');
    
            const zwssgrLoader = document.createElement('span');
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
            })
            .then(zwssgrResponse => zwssgrResponse.json())
            .then(zwssgrData => {
                const zwssgrLocationSelect = document.getElementById('zwssgr-location-select');
                if (zwssgrLocationSelect) {
                    zwssgrLocationSelect.remove();
                }
                if (zwssgrData.success) {
                    zwssgrDataFilter.insertAdjacentHTML('beforeend', zwssgrData.data);
                } else {
                    //zwssgrDataFilter.insertAdjacentHTML('beforeend', '<div class="notice notice-error"> No data available. </div>');
                }
            })
            .catch(zwssgrError => {
                //zwssgrDataFilter.insertAdjacentHTML('beforeend', '<div class="notice notice-error"> Error occurred while processing your request. </div>');
            })
            .finally(() => {
                zwssgrDropdown.classList.remove('disabled');
                if (zwssgrLoader.parentNode) {
                    zwssgrLoader.parentNode.removeChild(zwssgrLoader);
                }
            });

        });

    }

    const zwgrDashboard = document.getElementById('zwssgr-dashboard');

    if (zwgrDashboard) {
    
        zwgrDashboard.addEventListener('change', function (zwssgrEv) {
            "use strict";

            const zwssgrTargetId = zwssgrEv.target.id;

            if (zwssgrTargetId !== 'zwssgr-account-select' && zwssgrTargetId !== 'zwssgr-location-select') {
                return;
            }

            let zwssgrRangeFilterData = null;
            let zwssgrRangeFilterType = null;

            const zwssgrInputs = document.querySelectorAll('#zwssgr-account-select, #zwssgr-location-select');

            zwssgrInputs.forEach(zwssgrInput => {
                zwssgrInput.classList.add('disabled');
                zwssgrInput.disabled = true;
            });

            const zwssgrActiveButton    = document.querySelector('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button.active');
            const zwssgrActiveDateInput = document.querySelector('.zwssgr-dashboard .zwssgr-filters-wrapper input[name="dates"].active');

            if (zwssgrActiveButton) {
                zwssgrRangeFilterType = 'rangeofdays';
                zwssgrRangeFilterData = zwssgrActiveButton.textContent.trim().toLowerCase();
            } else if (zwssgrActiveDateInput) {
                zwssgrRangeFilterType = 'rangeofdate';
                zwssgrRangeFilterData = zwssgrActiveDateInput.value.trim().replace(" to ", " - ");
            }

            zwssgrRenderDataCallback(zwssgrEv, zwssgrRangeFilterData, zwssgrRangeFilterType);

        });

    };

    if (zwgrDashboard) {
        
        zwgrDashboard.addEventListener('click', function (zwssgrEv) {
            "use strict";

            const zwssgrButton = zwssgrEv.target.closest('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button');
            
            if (!zwssgrButton) {
                return;
            }
    
            const zwssgrRangeFilterData = zwssgrButton.textContent.trim().toLowerCase();
    
            if (!zwssgrRangeFilterData) {
                return;
            }
    
            document.querySelectorAll('.zwssgr-filter-button').forEach(button => {
                button.classList.remove('active');
            });

             document.querySelectorAll('.zwssgr-date-range-picker').forEach(zwssgrDateInput => {

                zwssgrDateInput.classList.remove('active');            

                const zwssgrFlatpickrInstance = zwssgrDateInput._flatpickr;

                if (zwssgrFlatpickrInstance) {
                    zwssgrFlatpickrInstance.clear();
                    zwssgrFlatpickrInstance.altInput.setAttribute("placeholder", "Custom");
                }

            });
    
            zwssgrButton.classList.add('active');
    
            zwssgrRenderDataCallback(zwssgrEv, zwssgrRangeFilterData, "rangeofdays");
    
        });

    }

    const zwssgrDateInput = document.querySelector('.zwssgr-dashboard-header .zwssgr-filters-wrapper .zwssgr-date-range-picker');
    
    if (zwssgrDateInput) {
        
        flatpickr(zwssgrDateInput, {
            mode: "range",
            dateFormat: "d-m-Y",
            altInput: true,
            altFormat: "d-m-Y",
            maxDate: "today",
            onReady: function(selectedDates, dateStr, instance) {
                instance.altInput.setAttribute("placeholder", "Custom");
            },
            onChange: function (selectedDates, dateStr, instance) {
    
                if (selectedDates.length < 2) {
                    return;
                }
    
                const zwssgrFilterButtons = document.querySelectorAll('.zwssgr-filters-wrapper .zwssgr-filter-item .zwssgr-filter-button');
                zwssgrFilterButtons.forEach(button => button.classList.remove('active'));
                zwssgrDateInput.classList.add('active');

                setTimeout(() => {
                    instance.altInput.classList.add('active');
                }, 0);
    
                const zwssgrStartDate       = instance.formatDate(selectedDates[0], "d-m-Y");
                const zwssgrEndDate         = instance.formatDate(selectedDates[1], "d-m-Y");
                const zwssgrRangeFilterData = `${zwssgrStartDate} - ${zwssgrEndDate}`;
    
                const zwssgrEv = {
                    type: "flatpickr-change",
                    target: zwssgrDateInput,
                    selectedDates: selectedDates,
                    dateStr: dateStr,
                    instance: instance,
                    preventDefault: () => {}
                };
    
                if (typeof zwssgrRenderDataCallback === "function") {
                    zwssgrDateInput.classList.add('active');
                    zwssgrRenderDataCallback(zwssgrEv, zwssgrRangeFilterData, 'rangeofdate');
                } else {
                }
    
            }
    
        });

    }    

});