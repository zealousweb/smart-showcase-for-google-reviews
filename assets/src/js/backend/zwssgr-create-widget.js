import {zwssgrGetUrlParameter} from './get-url-parameter';
import {zwssgrRedirectToOptionsTab} from './redirect-to-options-tab';

document.addEventListener('DOMContentLoaded', function () {
  "use strict";

  const zwssgrCreateWidgetBtn = document.querySelector("#fetch-gmb-data #zwssgr-create-widget");
  const zwssgrCreateFetchReviewBtn = document.querySelector("#fetch-gmb-data #fetch-gmd-reviews");

  if (zwssgrCreateWidgetBtn) {
    zwssgrCreateWidgetBtn.addEventListener("click", function (zwssgrEv) {
      zwssgrEv.preventDefault();
      zwssgrCreateGmbWidget();
    });
  }

  function zwssgrCreateGmbWidget() {

        const zwssgrAccountSelect  = document.querySelector("#zwssgr-account-select");
        const zwssgrLocationSelect = document.querySelector("#zwssgr-location-select");

        if (!zwssgrAccountSelect || !zwssgrLocationSelect) {
            console.error("Account or Location select element not found");
            return null;
        }

        const zwssgrWidgetId = zwssgrGetUrlParameter("zwssgr_widget_id");
        const zwssgrAccountNumber = zwssgrAccountSelect.value;
        const zwssgrLocationNumber = zwssgrLocationSelect.value;
        const zwssgrAccountName = zwssgrAccountSelect.options[zwssgrAccountSelect.selectedIndex].text;
        const zwssgrLocationName = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].text;

        const zwssgrLocationNewReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-new-review-url") || '';
        const zwssgrLocationAllReviewUri = zwssgrLocationSelect.options[zwssgrLocationSelect.selectedIndex].getAttribute("data-all-reviews-url") || '';

        zwssgrCreateWidgetBtn.classList.add('disabled');
        zwssgrCreateFetchReviewBtn.classList.add('disabled');

        if (zwssgrAccountSelect) {
            zwssgrAccountSelect.classList.add('disabled');
        }

        if (zwssgrLocationSelect) {
            zwssgrLocationSelect.classList.add('disabled');
        }

        fetch(zwssgr_admin?.ajax_url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
              action: "zwssgr_create_gmb_widget",
              security: zwssgr_admin?.zwssgr_create_gmb_widget,
              zwssgr_gmb_data_type: 'zwssgr_gmb_reviews',
              zwssgr_account_number: zwssgrAccountNumber,
              zwssgr_location_number: zwssgrLocationNumber,
              zwssgr_data_sync_once: true,
              zwssgr_location_new_review_uri: zwssgrLocationNewReviewUri,
              zwssgr_location_all_review_uri: zwssgrLocationAllReviewUri,
              zwssgr_account_name: zwssgrAccountName,
              zwssgr_location_name: zwssgrLocationName,
              zwssgr_widget_id: zwssgrWidgetId,
          })
      })
      .then(zwssgrResponse => zwssgrResponse.json())
      .then(zwssgrResponse => {
        if (zwssgrResponse.success) {
          zwssgrRedirectToOptionsTab();
        }
      })
      .catch(zwssgrError => {

      });

    }

});