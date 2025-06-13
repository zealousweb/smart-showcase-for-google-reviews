document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    function zwssgrReloadWithDelay(zwssgrDelay = 1500) {
      setTimeout(() => location.reload(), zwssgrDelay);
    }

    const zwssgrAccountSelect = document.querySelector('#fetch-gmb-data #zwssgr-account-select');
    const zwssgrLocationSelect = document.querySelector("#fetch-gmb-data #zwssgr-location-select");

    if (zwssgrLocationSelect) {

        zwssgrLocationSelect.addEventListener("change", function () {
            "use strict";

            zwssgrLocationSelect.classList.add('disabled');

            if (zwssgrAccountSelect) {
                zwssgrAccountSelect.classList.add('disabled');
            }

            const zwssgrLocationNumber = this.value;
            const zwssgrUrl            = new URL(window.location.href);
            zwssgrUrl.searchParams.set('zwssgr_location_number', zwssgrLocationNumber);
            window.location.href = zwssgrUrl.toString();

            zwssgrReloadWithDelay();

        });

    }
});