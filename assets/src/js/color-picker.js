document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    // Listen for changes on the checkbox
    document.addEventListener('change', function (event) {
        if (event.target.id === 'toggle-google-review') {
            // Update button colors based on the color pickers
            let bgColor = document.getElementById('bg-color-picker').value;
            let textColor = document.getElementById('text-color-picker').value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.backgroundColor = bgColor;
                button.style.color = textColor;
            });

            // Toggle button visibility
            toggleButtonVisibility();
        }
    });

    // When the background color picker changes
    document.addEventListener('input', function (event) {
        if (event.target.id === 'bg-color-picker') {
            let bgColor = event.target.value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.backgroundColor = bgColor;
            });
        }
    });

    // When the text color picker changes
    document.addEventListener('input', function (event) {
        if (event.target.id === 'text-color-picker') {
            let textColor = event.target.value;
            let buttons = document.querySelectorAll('.zwssgr-google-toggle');

            buttons.forEach(button => {
                button.style.color = textColor;
            });
        }
    });

    function toggleButtonVisibility() {
        let toggleCheckbox = document.getElementById('toggle-google-review');
        let buttons = document.querySelectorAll('.zwssgr-google-toggle');

        buttons.forEach(button => {
            button.style.display = toggleCheckbox.checked ? 'inline-block' : 'none';
        });
    }

    // Run the function when the page loads
    toggleButtonVisibility();

    // Run the function whenever the checkbox state changes
    let toggleCheckbox = document.getElementById('toggle-google-review');
    if (toggleCheckbox) {
        toggleCheckbox.addEventListener('change', toggleButtonVisibility);
    }

    // Fade In function
    function fadeIn(element) {
        element.style.opacity = 0;
        element.style.display = 'flex';
        let opacity = 0;
        const interval = setInterval(function () {
            if (opacity < 1) {
                opacity += 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(interval);
            }
        }, 30);
    }

    // Fade Out function
    function fadeOut(element) {
        let opacity = 1;
        const interval = setInterval(function () {
            if (opacity > 0) {
                opacity -= 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(interval);
                element.style.display = 'none';
            }
        }, 30);
    }

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'toggle-google-review') {
            const colorPickerOptions = document.getElementById('color-picker-options');
            if (event.target.checked) {
                colorPickerOptions.style.display = 'flex';
                fadeIn(colorPickerOptions);
            } else {
                fadeOut(colorPickerOptions);
            }
        }
    });

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'enable-load-more') {
            const loadMoreOptions = document.getElementById('zwssgr-load-color-picker-options');
            if (event.target.checked) {
                loadMoreOptions.style.display = 'flex';
                fadeIn(loadMoreOptions);
            } else {
                fadeOut(loadMoreOptions);
            }
        }
    });

});