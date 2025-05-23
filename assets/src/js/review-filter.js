"use strict";

// Helper function: Formats date based on user selection
export function formatDate(dateString, format, lang) {
    let dateParts;
    let date;

    if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
        dateParts = dateString.split('/');
        date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
    } else if (/^\d{2}-\d{2}-\d{4}$/.test(dateString)) {
        dateParts = dateString.split('-');
        date = new Date(dateParts[2], dateParts[0] - 1, dateParts[1]);
    } else if (/^\d{4}\/\d{2}\/\d{2}$/.test(dateString)) {
        dateParts = dateString.split('/');
        date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
    } else {
        date = new Date(dateString);
    }

    if (isNaN(date.getTime())) return dateString;

    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    switch (format) {
        case 'DD/MM/YYYY':
            return date.toLocaleDateString('en-GB');
        case 'MM-DD-YYYY':
            return date.toLocaleDateString('en-US').replace(/\//g, '-');
        case 'YYYY/MM/DD':
            return date.toISOString().split('T')[0].replace(/-/g, '/');
        case 'full':
            return date.toLocaleDateString(lang, options);
        default:
            return dateString;
    }
}

// Exported function: Updates date display based on language/format
export function updateDisplayedDates() {
    const lang = document.getElementById('language-select')?.value;
    const format = document.getElementById('date-format-select')?.value;

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
    th: 'อ่านเพิ่มเติม',
};

// Updates "Read more" link based on language and char limit
export function updateReadMoreLink(element, lang) {
    let charLimit = parseInt(document.getElementById('review-char-limit')?.value, 10);
    let fullText = element.getAttribute('data-full-text');

    if (charLimit && fullText.length > charLimit) {
        let trimmedText = fullText.substring(0, charLimit) + '... ';
        element.innerHTML = trimmedText + `<a href="javascript:void(0);" class="read-more-link">${window.zwssgrTranslations[lang]}</a>`;
    } else {
        element.textContent = fullText;
    }
}

// Event Listeners (Triggers on load & user interactions)

document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    document.body.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'date-format-select') {
            updateDisplayedDates();
        }

        if (event.target && event.target.id === 'language-select') {
            const lang = event.target.value;
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

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('read-more-link')) {
            event.preventDefault();
            let parent = event.target.parentElement;
            let fullText = parent.getAttribute('data-full-text');
            if (fullText) {
                parent.textContent = fullText;
            }
        }
    });

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
});
