jQuery(document).ready(function () {
	jQuery('#menu-posts-lasso-urls .wp-submenu li').each(function (i, el) {
		if ( jQuery(el).find('a').length > 0 ) {
			let path = jQuery(el).find('a').attr('href');
			let domain = 'https://domain.com/';
			let href = domain + path;

			const url = new URL(href);
			const params = new URLSearchParams(url.search);

			if ( params.get('page') !== null && params.get('post_type') !== 'lasso-urls' ) {
				params.append("post_type", "lasso-urls");
				url.search = params.toString();
				jQuery(el).find('a').attr('href', url.href.replace(domain, '').replace('admin.php', 'edit.php'));
			}
		}
	});
})
