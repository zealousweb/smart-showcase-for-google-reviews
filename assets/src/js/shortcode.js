document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    function copyTextToClipboard(text) {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.setAttribute('readonly', '');
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            textArea.style.top = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Copy command was unsuccessful'));
                }
            } catch (err) {
                document.body.removeChild(textArea);
                reject(err);
            }
        });
    }

    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("copy-shortcode-icon") || e.target.classList.contains("zwssgr-copy-shortcode-icon")) {
            let targetId = e.target.dataset.target;
            let inputElement = document.getElementById(targetId);

            if (inputElement) {
                copyTextToClipboard(inputElement.value).then(() => {
                    e.target.classList.add("dashicons-yes");
                    setTimeout(() => {
                        e.target.classList.remove("dashicons-yes");
                        e.target.classList.add("dashicons-admin-page");
                    }, 2000);
                }).catch(err => {
                    console.error("Failed to copy text: ", err);
                });
            }
        }
    });

});
