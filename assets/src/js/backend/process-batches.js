export function zwssgrProcessBatch (
    zwssgrGmbDataType,
    zwssgrAccountNumber,
    zwssgrLocationNumber,
    zwssgrWidgetId,
    zwssgrLocationName,
    zwssgrLocationNewReviewUri,
    zwssgrAccountName,
    zwssgrLocationAllReviewUri
) {
    function zwssgrReloadWithDelay(zwssgrDelay = 1500) {
        setTimeout(() => location.reload(), zwssgrDelay);
    }
    fetch(zwssgr_admin?.ajax_url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "zwssgr_fetch_gmb_data",
            security: zwssgr_admin?.zwssgr_queue_manager_nounce,
            zwssgr_gmb_data_type: zwssgrGmbDataType,
            zwssgr_account_number: zwssgrAccountNumber,
            zwssgr_location_number: zwssgrLocationNumber,
            zwssgr_widget_id: zwssgrWidgetId,
            zwssgr_location_name: zwssgrLocationName,
            zwssgr_location_new_review_uri: zwssgrLocationNewReviewUri,
            zwssgr_account_name: zwssgrAccountName,
            zwssgr_location_all_review_uri: zwssgrLocationAllReviewUri
        })
    })
    .then(zwssgrResponse => zwssgrResponse.json())
    .then(zwssgrResponse => {

        const zwssgrProgressBar 	  = document.querySelector('#fetch-gmb-data .progress-bar');
        const zwssgrResponseContainer = document.querySelector('#fetch-gmb-data .response');

        if (zwssgrResponse.success) {
            zwssgrProgressBar.style.display = 'block';
        } else {
            zwssgrResponseContainer.innerHTML = `<p class="error">${zwssgrResponse.data?.message || 'An error occurred.'}</p>`;
            zwssgrReloadWithDelay();
        }

    })
    .catch(zwssgrError => {

        const zwssgrResponseContainer 	  = document.querySelector('#fetch-gmb-data .response');
        zwssgrResponseContainer.innerHTML = `<p class="error">An unexpected error occurred.</p>`;
        zwssgrReloadWithDelay();

    });
}