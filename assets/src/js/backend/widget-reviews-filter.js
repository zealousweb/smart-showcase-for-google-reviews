document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const zwssgrSelectIds = [
        'zwssgr-account-select',
        'zwssgr-location-select'
    ];

    zwssgrSelectIds.forEach(function (zwssgrId) {
        const zwssgrElement = document.getElementById(zwssgrId);
        if (zwssgrElement) {
            zwssgrElement.addEventListener('change', function () {
                const zwssgrForm = document.getElementById('posts-filter');

                if (zwssgrId === 'zwssgr-account-select') {
                    const zwssgrLocationSelect = document.getElementById('zwssgr-location-select');
                    if (zwssgrLocationSelect) zwssgrLocationSelect.remove();
                }

                if (zwssgrForm) {
                    zwssgrForm.submit();
                }
            });
        } else {
        }
    });
    
});