document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    window.zwssgrWidgetPostType = "zwssgr_data_widget";

    // Function to check if an element exists
    function elementExists(selector) {
        return document.querySelector(selector) !== null;
    }

    // Check if we're on the edit, new post, or custom layout page for the widget post type
    if (
        elementExists(".post-type-" + window.zwssgrWidgetPostType) ||
        elementExists(".post-php.post-type-" + window.zwssgrWidgetPostType) ||
        elementExists(".post-new-php.post-type-" + window.zwssgrWidgetPostType) ||
        window.location.href.includes("admin.php?page=zwssgr_widget_configurator")
    ) {
        // Ensure the parent menu (dashboard) is highlighted as active
        let dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
        if (dashboardMenu) {
            dashboardMenu.classList.remove("wp-not-current-submenu");
            dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
        }

        // Ensure the specific submenu item for zwssgr_data_widget is active
        let widgetMenuItem = document.querySelector(
            'ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrWidgetPostType + '"]'
        );
        if (widgetMenuItem) {
            widgetMenuItem.closest("li").classList.add("current");
        }
    }

    window.zwssgrReviewPostType = "zwssgr_reviews";

    // Check if we're on the edit, new post, or custom layout page for the review post type
    if (
        elementExists(".post-type-" + window.zwssgrReviewPostType) ||
        elementExists(".post-php.post-type-" + window.zwssgrReviewPostType) ||
        elementExists(".post-new-php.post-type-" + window.zwssgrReviewPostType) ||
        window.location.href.includes("admin.php?page=zwssgr_review_configurator")
    ) {
        // Ensure the parent menu (dashboard) is highlighted as active
        let dashboardMenu = document.querySelector(".toplevel_page_zwssgr_dashboard");
        if (dashboardMenu) {
            dashboardMenu.classList.remove("wp-not-current-submenu");
            dashboardMenu.classList.add("wp-has-current-submenu", "wp-menu-open");
        }

        // Ensure the specific submenu item for zwssgr_reviews is active
        let reviewMenuItem = document.querySelector(
            'ul.wp-submenu li a[href="edit.php?post_type=' + window.zwssgrReviewPostType + '"]'
        );
        if (reviewMenuItem) {
            reviewMenuItem.closest("li").classList.add("current");
        }
    }

});