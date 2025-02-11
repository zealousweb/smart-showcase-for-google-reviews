document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const sliderConfigs = {
        ".zwssgr-slider-1": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-2": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-3": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-4": { slidesPerView: 1, slidesPerGroup: 1 },
        ".zwssgr-slider-5": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
        ".zwssgr-slider-6": { slidesPerView: 1, slidesPerGroup: 1, breakpoints: { 1200: { slidesPerView: 3, slidesPerGroup: 3 }, 768: { slidesPerView: 2, slidesPerGroup: 2 }, 480: { slidesPerView: 1, slidesPerGroup: 1 } } },
    };

    Object.keys(sliderConfigs).forEach(selector => {
        const sliderElements = document.querySelectorAll(selector);
    
        if (sliderElements.length > 0) {
            const parentElement = sliderElements[0].parentElement;    
            sliderElements.forEach(sliderElement => {
                const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                const config = sliderConfigs[selector];
                const minSlidesRequired = (config.slidesPerView || 1) + 1;
                const enableLoop = slideCount >= minSlidesRequired;
    
                new Swiper(sliderElement, {
                    slidesPerView: config.slidesPerView,
                    slidesPerGroup: config.slidesPerGroup,
                    spaceBetween: 20,
                    loop: enableLoop,
                    navigation: {
                        nextEl: parentElement.querySelector(".swiper-button-next"),
                        prevEl: parentElement.querySelector(".swiper-button-prev"),
                    },
                    breakpoints: config.breakpoints || {},
                });
            });
        }
    });

    // Store Swiper instances in an object
    let swiperInstances = {};

    function reinitializeAllSwipers(container) {
        // Ensure container is a valid HTML element
        if (!(container instanceof HTMLElement)) {
            console.error(`Invalid container element!`, container);
            return;
        }
    
        // Loop through all configured Swiper sliders
        Object.keys(sliderConfigs).forEach(selector => {
            const sliderElements = container.querySelectorAll(selector); // Get all sliders within the container
    
            sliderElements.forEach(sliderElement => {
                const slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                const config = sliderConfigs[selector];
                const minSlidesRequired = (config.slidesPerView || 1) + 1;
                const enableLoop = slideCount >= minSlidesRequired;
    
                // Destroy existing Swiper instance if it exists
                if (swiperInstances[selector]) {
                    swiperInstances[selector].destroy(true, true);
                }
    
                // Initialize new Swiper instance
                swiperInstances[selector] = new Swiper(sliderElement, {
                    slidesPerView: config.slidesPerView,
                    slidesPerGroup: config.slidesPerGroup,
                    spaceBetween: 20,
                    loop: enableLoop,
                    navigation: {
                        nextEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-next"),
                        prevEl: sliderElement.closest(".zwssgr-slider").querySelector(".swiper-button-prev"),
                    },
                    breakpoints: config.breakpoints || {},
                });
            });
        });
    }

});