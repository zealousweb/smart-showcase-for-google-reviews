document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('toggle-content')) {
            let link = e.target;
            let fullText = link.getAttribute('data-full-text');
            let parentParagraph = link.closest('p');
    
            // Replace the trimmed content with the full content
            if (parentParagraph) {
                parentParagraph.innerHTML = fullText;
            }
        }
    });

});