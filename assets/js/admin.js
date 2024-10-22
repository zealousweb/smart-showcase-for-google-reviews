jQuery(document).ready(function($) {

	//widget should active 
    var widget_post_type = 'zwsgr_data_widget';
    if ($('body.post-type-' + widget_post_type).length || $('body.post-php.post-type-' + widget_post_type).length ) {
		$('.toplevel_page_zwsgr_dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
		$('ul.wp-submenu li a[href="edit.php?post_type=zwsgr_data_widget"]').parent('li').addClass('current');
	}

	if ($('body.post-new-php.post-type-' + widget_post_type).length) {
		$('.toplevel_page_zwsgr_dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
		$('ul.wp-submenu li a[href="edit.php?post_type=zwsgr_data_widget"]').parent('li').addClass('current');
	}
	
});
