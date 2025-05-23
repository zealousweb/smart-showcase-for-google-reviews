document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    // Select Layout option functionality
    const radioButtons = document.querySelectorAll('input[name="display_option"]');
    let currentDisplayOption = 'all';

    // Add event listeners to radio buttons for dynamic filtering
    radioButtons.forEach(button => {
        button.addEventListener('change', function () {
            currentDisplayOption = this.value;
            updateOptions(currentDisplayOption);
            saveSelectedOption(currentDisplayOption); // Save the selected display option
        });
    });

    // Function to save the selected display option and layout option via AJAX
    function saveSelectedOption(option) {
        let postId = getQueryParam('zwssgr_widget_id');
        let settings = document.querySelector('.tab-item.active')?.getAttribute('data-tab');
        let selectedLayout = null; // Initialize selectedLayout as null

        // Select all elements with class 'zwssgr-option-item'
        const optionItems = document.querySelectorAll('.zwssgr-option-item');

        optionItems.forEach(optionItem => {
            // Check if the element is visible
            if (optionItem.offsetParent !== null) { // offsetParent is null for hidden elements
                const selectedButton = optionItem.querySelector('.select-btn.selected');
                if (selectedButton) {
                    selectedLayout = selectedButton.dataset.option;
                }
            } 
        });

        // Ensure selectedLayout has a valid value before sending the requests
        if (!selectedLayout) {
            // console.error("No layout option selected.");
            return;
        }

        let xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true); // Use asynchronous request for better performance
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        let params = new URLSearchParams({
            action: 'zwssgr_save_widget_data',
            security: my_widget.nonce,
            display_option: option,
            layout_option: selectedLayout,
            post_id: postId,
            settings: settings
        });
        
        xhr.onload = function () {
            if (xhr.status === 200) {
                // console.log('Display and layout option saved:', xhr.responseText);
            } else {
                console.error('Error saving options:', xhr.statusText);
            }
        };
        
        xhr.onerror = function () {
            console.error('Request failed');
        };
        
        xhr.send(params.toString());
    }

    // Function to show/hide options based on the selected radio button
    function updateOptions(value) {
        document.querySelectorAll('.zwssgr-option-item').forEach(item => {
            if (value === 'all' || item.dataset.type === value) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Function to get query parameter by name
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }  

    // Set active tab and selected option from the URL
    window.zwssgrActiveTab = getQueryParam('tab') || 'tab-options'; // Default to 'tab-options'
    window.zwssgrSelectedOption = getQueryParam('selectedOption'); // Get the selected option ID from URL


    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(function (tab) {
        tab.style.display = 'none';
    });

    // Show the active tab content
    let activeTabElement = document.getElementById(window.zwssgrActiveTab);
    if (activeTabElement) {
        activeTabElement.style.display = 'block';
    }

    // Remove 'active' class from all tab items
    document.querySelectorAll('.tab-item').forEach(function (tabItem) {
        tabItem.classList.remove('active');
    });

    // Add 'active' class to the selected tab item
    let activeTabItem = document.querySelector('.tab-item[data-tab="' + window.zwssgrActiveTab + '"]');
    if (activeTabItem) {
        activeTabItem.classList.add('active');
    }

    // If there's a selected option in the URL and the active tab is 'tab-selected'
    if (window.zwssgrSelectedOption && window.zwssgrActiveTab === 'tab-selected') {
        let selectedOptionElement = document.getElementById(window.zwssgrSelectedOption);
        let selectedOptionDisplay = document.getElementById('selected-option-display');

        if (selectedOptionElement && selectedOptionDisplay) {
            selectedOptionDisplay.innerHTML = ''; // Clear previous content
            selectedOptionDisplay.appendChild(selectedOptionElement); // Move the selected option
            
            // Remove the select button from the moved element
            let selectBtn = selectedOptionDisplay.querySelector('.select-btn');
            if (selectBtn) {
                selectBtn.remove();
            }
        }
    }


    // Handle click events for the tab navigation items
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('tab-item')) {
            let tabId = event.target.getAttribute('data-tab');
            let currentUrl = window.location.href.split('?')[0]; // Get the base URL

            // Get existing query parameters
            let selectedOption = getQueryParam('selectedOption'); // Keep the selected option in URL if it exists
            let postId = getQueryParam('zwssgr_widget_id'); // Get the post_id from the URL if it exists

            // Start building the new URL with page and tab parameters
            let newUrl = `${currentUrl}?page=zwssgr_widget_configurator&tab=${tabId}`;

            // Add selectedOption to the URL if it exists
            if (selectedOption) {
                newUrl += `&selectedOption=${selectedOption}`;
            }

            // Add post_id to the URL if it exists
            if (postId) {
                newUrl += `&zwssgr_widget_id=${postId}`;
            }

            // Redirect to the new URL
            window.location.href = newUrl;
        }
    });

});