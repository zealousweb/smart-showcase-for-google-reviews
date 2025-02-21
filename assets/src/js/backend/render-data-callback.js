import {zwssgr_draw_chart} from './draw-chart';

export function zwssgrRenderDataCallback(zwssgrEv, zwssgrRangeFilterData, zwssgrRangeFilterType) {
    "use strict";

    if (zwssgrEv && typeof zwssgrEv.preventDefault === "function") {
        zwssgrEv.preventDefault();
    }

    const zwssgrGmbAccountDiv  = document.getElementById('zwssgr-account-select');
    const zwssgrGmbLocationDiv = document.getElementById('zwssgr-location-select');

    const zwssgrDashboard = document.querySelector('.zwssgr-dashboard');
    
    if (!zwssgrDashboard) {
        return;
    }

    if (zwssgrEv.target.id === 'zwssgr-location-select') {
        zwssgrGmbAccountDiv.classList.add('disabled');
        zwssgrGmbLocationDiv.classList.add('disabled');
    }

    const zwssgrFilterData = {
        zwssgr_gmb_account_number:   zwssgrGmbAccountDiv ? zwssgrGmbAccountDiv.value : '',
        zwssgr_gmb_account_location: zwssgrGmbLocationDiv ? zwssgrGmbLocationDiv.value : '',
        zwssgr_range_filter_type:    zwssgrRangeFilterType,
        zwssgr_range_filter_data:    zwssgrRangeFilterData
    };

    const zwssgrMinHeight     = zwssgrDashboard?.querySelector('#zwssgr-render-dynamic')?.offsetHeight || 200;
    const zwssgrRenderDynamic = zwssgrDashboard?.querySelector('#zwssgr-render-dynamic');
    
    if (zwssgrRenderDynamic) {
        zwssgrRenderDynamic.remove();
    }

    const zwssgrLoaderWrapper        = document.createElement('div');
    zwssgrLoaderWrapper.className    = 'loader-outer-wrapper';
    zwssgrLoaderWrapper.style.height = `${zwssgrMinHeight}px`;
    zwssgrLoaderWrapper.innerHTML    = '<div class="loader"></div>';
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
    })
    .then(zwssgrResponse => zwssgrResponse.json())
    .then(zwssgrData => {
        zwssgrLoaderWrapper.remove();
        if (zwssgrData.success) {
            zwssgrDashboard.insertAdjacentHTML('beforeend', zwssgrData.data.html);
            const zwssgrNewContent            = zwssgrDashboard.lastElementChild;
            zwssgrNewContent.style.display    = 'none';
            zwssgrNewContent.style.opacity    = 0;
            zwssgrNewContent.style.transition = 'opacity 0.3s';
            zwssgrNewContent.style.display    = '';
            requestAnimationFrame(() => {
                zwssgrNewContent.style.opacity = 1;
            });

            if (zwssgrData.data.zwssgr_chart_data) {
                google.charts.setOnLoadCallback(() =>
                    zwssgr_draw_chart(zwssgrData.data.zwssgr_chart_data)
                );
            }

        } else {
            zwssgrDashboard.innerHTML = '<p>Error loading data.</p>';
        }
    })
    .catch(zwssgrError => {
        zwssgrDashboard.innerHTML = '<p>An error occurred while fetching data.</p>';
    })
    .finally(() => {

        if (zwssgrGmbAccountDiv) {
            zwssgrGmbAccountDiv.classList.remove('disabled');
        }

        if (zwssgrGmbLocationDiv) {
            zwssgrGmbLocationDiv.classList.remove('disabled');
        }
            
        const zwssgrInputs = document.querySelectorAll('#zwssgr-account-select, #zwssgr-location-select');
        zwssgrInputs.forEach(zwssgrInput => {
            zwssgrInput?.classList.remove('disabled');
            zwssgrInput.disabled = false;
        });

    });

}