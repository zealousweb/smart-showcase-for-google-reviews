document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    function formatDate(dateString, format, lang) {
		let dateParts;
		let date;
	
		// Check for various formats and parse accordingly
		if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
			dateParts = dateString.split('/');
			date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]); // DD/MM/YYYY
		} else if (/^\d{2}-\d{2}-\d{4}$/.test(dateString)) {
			dateParts = dateString.split('-');
			date = new Date(dateParts[2], dateParts[0] - 1, dateParts[1]); // MM-DD-YYYY
		} else if (/^\d{4}\/\d{2}\/\d{2}$/.test(dateString)) {
			dateParts = dateString.split('/');
			date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // YYYY/MM/DD
		} else {
			date = new Date(dateString); // ISO or fallback
		}
	
		// Return original if date is invalid
		if (isNaN(date.getTime())) return dateString;
	
		// Format date based on selected format and language
		const options = { year: 'numeric', month: 'long', day: 'numeric' };
		switch (format) {
			case 'DD/MM/YYYY':
				return date.toLocaleDateString('en-GB'); // e.g., 01/01/2024
			case 'MM-DD-YYYY':
				return date.toLocaleDateString('en-US').replace(/\//g, '-'); // e.g., 01-01-2024
			case 'YYYY/MM/DD':
				return date.toISOString().split('T')[0].replace(/-/g, '/'); // e.g., 2024/01/01
			case 'full':
				return date.toLocaleDateString(lang, options); // January 1, 2024 in selected language
			default:
				return dateString;
		}
	}

    function updateDisplayedDates() {
        const lang = document.getElementById("language-select").value; // Get selected language
        const format = document.getElementById("date-format-select").value; // Get selected date format

        document.querySelectorAll(".date").forEach(function (element) {
            const originalDate = element.getAttribute("data-original-date"); // Get the original date
            if (format === "hide") {
                element.textContent = ""; // Hide the date
            } else {
                const formattedDate = formatDate(originalDate, format, lang); // Pass lang to formatDate
                element.textContent = formattedDate; // Update the text with the formatted date
            }
        });
    }

    document.body.addEventListener("change", function (event) {
        if (event.target && event.target.id === "date-format-select") {
            updateDisplayedDates();
        }
    });
    
    
    window.zwssgrTranslations = {
        en: 'Read more',
        es: 'Leer más',
        fr: 'Lire la suite',
        de: 'Mehr lesen',
        it: 'Leggi di più',
        pt: 'Leia mais',
        ru: 'Читать дальше',
        zh: '阅读更多',
        ja: '続きを読む',
        hi: 'और पढ़ें',
        ar: 'اقرأ أكثر',
        ko: '더 읽기',
        tr: 'Daha fazla oku',
        bn: 'আরও পড়ুন',
        ms: 'Baca lagi',
        nl: 'Lees verder',
        pl: 'Czytaj więcej',
        sv: 'Läs mer',
        th: 'อ่านเพิ่มเติม'
    };
    
    // Function to update Read more link based on language
    function updateReadMoreLink(element, lang) {
        let charLimit = parseInt(document.getElementById('review-char-limit').value, 10); // Get character limit
        let fullText = element.getAttribute('data-full-text'); // Get the stored full text
    
        if (charLimit && fullText.length > charLimit) {
            let trimmedText = fullText.substring(0, charLimit) + '... ';
            element.innerHTML = trimmedText + `<a href="javascript:void(0);" class="read-more-link">${window.zwssgrTranslations[lang]}</a>`;
        } else {
            element.textContent = fullText; // Show full text if no limit
        }
    }
    
    // Event delegation for "Read more" click
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('read-more-link')) {
            event.preventDefault();
            let parent = event.target.parentElement;
            if (parent) {
                let fullText = parent.getAttribute('data-full-text');
                if (fullText) {
                    parent.textContent = fullText;
                }
            }
        }
    });
    
    // On character limit input change
    document.body.addEventListener('input', function (event) {
        if (event.target && event.target.id === 'review-char-limit') {
            let charLimit = parseInt(event.target.value, 10);
            let lang = document.getElementById('language-select').value;
            let errorContainer = document.getElementById('char-limit-error');
            errorContainer.textContent = '';
    
            if (charLimit < 1 || isNaN(charLimit)) {
                if (event.target.value.trim() === '') {
                    document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                        let fullText = element.getAttribute('data-full-text') || element.textContent;
                        element.textContent = fullText;
                    });
                } else {
                    errorContainer.textContent = 'Character limit must be 1 or greater.';
                    event.target.value = '';
                }
                return;
            }
    
            document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                let fullText = element.getAttribute('data-full-text') || element.textContent;
                if (!element.getAttribute('data-full-text')) {
                    element.setAttribute('data-full-text', fullText);
                }
                updateReadMoreLink(element, lang);
            });
        }
    });
    
    
    // Function to update displayed dates based on selected language and format
    function updateDisplayedDates() {
        const lang = document.getElementById('language-select').value;
        const format = document.getElementById('date-format-select').value;
    
        document.querySelectorAll('.zwssgr-date').forEach(function (element) {
            const originalDate = element.getAttribute('data-original-date');
            if (format === 'hide') {
                element.textContent = '';
            } else {
                const formattedDate = formatDate(originalDate, format, lang);
                element.textContent = formattedDate;
            }
        });
    }

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'language-select') {
            let lang = event.target.value;
            updateDisplayedDates();
        
            document.querySelectorAll('.zwssgr-content').forEach(function (element) {
                let fullText = element.getAttribute('data-full-text') || element.textContent;
                if (!element.getAttribute('data-full-text')) {
                    element.setAttribute('data-full-text', fullText);
                }
                updateReadMoreLink(element, lang);
            });
        }
    });
    
    
    // On date format select change
    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'date-format-select') {
            updateDisplayedDates();
        }
    });

});