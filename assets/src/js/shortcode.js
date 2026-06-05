document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    document.addEventListener("click", function (e) {

        if (
            e.target.classList.contains("copy-shortcode-icon") ||
            e.target.classList.contains("zwssgr-copy-shortcode-icon")
        ) {

            let targetId = e.target.dataset.target;
            let inputElement = document.getElementById(targetId);

            if (!inputElement) {
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {

                navigator.clipboard.writeText(inputElement.value)
                    .then(() => {
                        showSuccess(e.target);
                    });

            } else {

                inputElement.select();
                inputElement.setSelectionRange(0, 99999);

                document.execCommand('copy');

                showSuccess(e.target);
            }
        }
    });

    function showSuccess(icon) {
        icon.classList.remove("dashicons-admin-page");
        icon.classList.add("dashicons-yes");

        setTimeout(() => {
            icon.classList.remove("dashicons-yes");
            icon.classList.add("dashicons-admin-page");
        }, 2000);
    }
});