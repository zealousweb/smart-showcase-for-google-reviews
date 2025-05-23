document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const enableLoadMore = document.getElementById('enable-load-more');
    const loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');

    if (enableLoadMore && loadMoreOptions) {  // Ensure elements exist
        if (enableLoadMore.checked) {
            loadMoreOptions.style.display = 'flex';
        } else {
            loadMoreOptions.style.display = 'none';
        }
    }

});