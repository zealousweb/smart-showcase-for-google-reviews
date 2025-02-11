document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const updateInputField = () => {
        const keywords = [];
        document.querySelectorAll('#keywords-list .keyword-item').forEach(item => {
            keywords.push(item.textContent.trim().replace(' ✖', ''));
        });

        const hiddenInput = document.getElementById('keywords-input-hidden');
        if (hiddenInput) { // Ensure the element exists before modifying it
            hiddenInput.value = keywords.join(', ');
        }
    };

    updateInputField(); // Call the function after DOM is ready
    
    // Function to handle adding new keywords
    const handleAddKeywords = (inputValue) => {
        // Get the input value and split it into keywords
        const newKeywords = inputValue.split(',').map(keyword => keyword.trim()).filter(keyword => keyword);
    
        // Get the current number of keywords in the list
        const currentKeywordsCount = document.querySelectorAll('#keywords-list .keyword-item').length;
    
        // Check if adding new keywords exceeds the limit of 5
        if (currentKeywordsCount + newKeywords.length > 5) {
            document.getElementById('error-message').style.display = 'block'; // Show the error message
            return; // Stop further execution
        } else {
            document.getElementById('error-message').style.display = 'none'; // Hide the error message if under limit
        }
    
        document.getElementById('keywords-input').value = ''; // Clear input field
    
        newKeywords.forEach(keyword => {
            // Check if the keyword is already in the list
            const existingKeywords = Array.from(document.querySelectorAll('#keywords-list .keyword-item')).map(item => item.textContent.trim().replace(' ✖', ''));
            if (!existingKeywords.includes(keyword)) {
                // Create a new keyword item
                const keywordItem = document.createElement('div');
                keywordItem.classList.add('keyword-item');
                keywordItem.textContent = keyword;
    
                // Create remove button
                const removeButton = document.createElement('span');
                removeButton.classList.add('remove-keyword');
                removeButton.textContent = ' ✖';
    
                // Append remove button to the keyword item
                keywordItem.appendChild(removeButton);
    
                // Append the keyword item to the keywords list
                document.getElementById('keywords-list').appendChild(keywordItem);
    
                // Update hidden input field
                updateInputField();
    
                // Set up click event to remove keyword
                removeButton.addEventListener('click', function () {
                    keywordItem.remove(); // Remove keyword from list
                    updateInputField(); // Update input field after removal
                });
            }
        });
    };

    document.body.addEventListener('keypress', function (event) {
        if (event.target && event.target.id === 'keywords-input') {
            if (event.key === 'Enter') { // Check for Enter key
                event.preventDefault(); // Prevent default form submission
                handleAddKeywords(event.target.value);
            }
        }
    });

    document.body.addEventListener('blur', function (event) {
        if (event.target && event.target.id === 'keywords-input') {
            handleAddKeywords(event.target.value);
        }
    }, true); // Use the "true" parameter to capture the event during the capturing phase

    
    // Function to set up click event to remove existing keywords (on page load and dynamically)
    const keywordsList = document.getElementById('keywords-list');
    if (keywordsList) {
        keywordsList.addEventListener('click', function (e) {
            document.getElementById('keywords-list').addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.remove-keyword'); // Find the closest remove button
                if (removeBtn) {
                    removeBtn.parentElement.remove(); // Remove the keyword item
                    updateInputField(); // Update the hidden input after removal
                }
            });
        });
    }

});