import {zwssgrProcessBatch} from './process-batches';
import {zwssgrGetUrlParameter} from './get-url-parameter';

document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const zwssgrFetchReviewButton = document.querySelector("#fetch-gmb-data #fetch-gmd-reviews");

	if (zwssgrFetchReviewButton) {
		zwssgrFetchReviewButton.addEventListener("click", function (zwssgrEv) {
			zwssgrEv.preventDefault();
			zwssgrFetchGmbData(this);
		});
	}
	
	function zwssgrFetchGmbData(zwssgrFetchReviewButton) {

		const zwssgrGmbDataType = zwssgrFetchReviewButton.getAttribute("data-fetch-type");
	
		const zwssgrAccountSelect = document.querySelector("#fetch-gmb-data #zwssgr-account-select");
		if (!zwssgrAccountSelect) {
            return;
        }
	
		const zwssgrAccountNumber = zwssgrAccountSelect.value;
		zwssgrAccountSelect.classList.add('disabled');
	
		const zwssgrAccountName = zwssgrAccountSelect.options[zwssgrAccountSelect.selectedIndex].text;
	
		const zwssgrLocationSelect = document.querySelector("#fetch-gmb-data #zwssgr-location-select");
		if (!zwssgrLocationSelect) {
            return;
        }
	
		const zwssgrLocationNumber = zwssgrLocationSelect.value;
		zwssgrLocationSelect.classList.add('disabled');
	
		const zwssgrLocationName 		 = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].text;
		const zwssgrLocationNewReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-new-review-url");
		const zwssgrLocationAllReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-all-reviews-url");
	
		const zwssgrWidgetId = zwssgrGetUrlParameter("zwssgr_widget_id");
	
		zwssgrFetchReviewButton.classList.add("disabled");
		zwssgrFetchReviewButton.innerHTML = '<span class="spinner is-active"></span> Fetching...';
	
		if (!zwssgrAccountNumber && !zwssgrLocationNumber) {
			document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Both account and location are required.</p>';
			setTimeout(() => location.reload(), 1500);
			return;
		}
	
		if (!zwssgrAccountNumber) {
			document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Account is required.</p>';
			setTimeout(() => location.reload(), 1500);
			return;
		}
	
		if (!zwssgrLocationNumber) {
			document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">Location is required.</p>';
			setTimeout(() => location.reload(), 1500);
			return;
		}
	
		if (!zwssgrWidgetId) {
			document.querySelector('#fetch-gmb-data .response').innerHTML = '<p class="error">No valid widget ID found.</p>';
			setTimeout(() => location.reload(), 1500);
			return;
		}
	
		document.querySelector('#fetch-gmb-data .response').innerHTML = '';
		document.querySelector('#fetch-gmb-data .progress-bar').style.display = 'block';
	
		zwssgrProcessBatch(
			zwssgrGmbDataType,
			zwssgrAccountNumber,
			zwssgrLocationNumber,
			zwssgrWidgetId,
			zwssgrLocationName,
			zwssgrLocationNewReviewUri,
			zwssgrAccountName,
			zwssgrLocationAllReviewUri
		);

	}

});