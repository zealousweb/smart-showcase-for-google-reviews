import {zwssgrGetUrlParameter } from './get-url-parameter';
import {zwssgrRedirectToOptionsTab} from './redirect-to-options-tab';


// Check if we're on the specific page URL that contains zwssgr_widget_id dynamically
const zwssgrUrlParams = new URLSearchParams(window.location.search);
if (zwssgrUrlParams.has('zwssgr_widget_id') && window.location.href.includes('admin.php?page=zwssgr_widget_configurator&tab=tab-fetch-data')) {
    let zwssgrBatchInterval = setInterval(function () {
        try {
            zwssgrCheckBatchStatus();
        } catch (zwssrError) {
            clearInterval(zwssgrBatchInterval); // Stop the interval on failure
        }
    }, 2500);
}

export function zwssgrCheckBatchStatus() {

    const zwssgrWidgetId = zwssgrGetUrlParameter('zwssgr_widget_id');

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
    })
    .then(zwssgrResponse => zwssgrResponse.json())
    .then(zwssgrResponse => {
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

            setTimeout(() => {
                document.querySelector('#fetch-gmb-data .progress-bar').style.display = 'none';
                if (zwssgrResponse.data.zwssgr_gmb_data_type === 'zwssgr_gmb_reviews') {
                    zwssgrRedirectToOptionsTab();
                } else {
                    location.reload();
                }
            }, 2000);
        } else {
            let zwssgr_batch_progress = zwssgrResponse.data.zwssgr_batch_progress;
            if (!isNaN(zwssgr_batch_progress) && zwssgr_batch_progress >= 0 && zwssgr_batch_progress <= 100) {
                document.querySelector('#fetch-gmb-data .progress-bar #progress').value = zwssgr_batch_progress;
                document.querySelector('#fetch-gmb-data .progress-bar #progress-percentage').textContent = Math.round(zwssgr_batch_progress) + '%';
            }
        }
    })
    .catch(zwssgrError => {
        console.error('Error:', zwssgrError);
    });
}