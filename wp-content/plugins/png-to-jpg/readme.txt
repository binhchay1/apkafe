=== PNG to JPG ===
Contributors: kubiq
Donate link: https://www.paypal.me/jakubnovaksl
Tags: png, jpg, optimize, save space, convert, image, media
Requires at least: 3.0.1
Tested up to: 6.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Convert PNG images to JPG, free up web space and speed up your webpage

== Description ==

Convert PNG images to JPG, free up web space and speed up your webpage

<ul>
	<li>set quality of converted JPG</li>
	<li>auto convert on upload</li>
	<li>auto convert on upload only when PNG has no transparency</li>
	<li>only convert image if JPG filesize is lower than PNG filesize</li>
	<li>leave original PNG images on the server</li>
	<li>convert existing PNG image to JPG</li>
	<li>bulk convert existing PNG images to JPG</li>
	<li>conversion statistics</li>
</ul>

== Installation ==

1. Upload `png-to-jpg` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 4.3 =
* add notice for Yoast SEO users and button to reindex database after conversion

= 4.2 =
* tested on WP 6.4
* continue converting images after one of them fail
* added pagination to Convert existing screen

= 4.1 =
* added nonce and security checks
* added button to stop transparency detection or conversion process
* removed DB prefix from notice table names to make it more readable
* remove preview box flex centering to make it works with bigger images
* auto delete PNG backup when JPG deleted in admin

= 4.0 =
* replace images also in post_excerpt
* separate SQL queries
* added support for FV Player plugin

= 3.9.1 =
* tested on WP 5.9
* check if file really exists and if has .png extension

= 3.9 =
* fix transparency default state

= 3.8 =
* tested on WP 5.4
* save transparency meta and load it instantly next time
* image viewer - background switch
* image viewer - centered image
* image viewer - highlight image borders and show image size on hover

= 3.7 =
* do not run second transparency detection if first one return true

= 3.6 =
* metadata update fix

= 3.5 =
* added support for Broken Link Checker plugin ( blc_instances, blc_links )

= 3.4 =
* replace image url also in these database tables: yoast_seo_links, revslider_static_slides

= 3.3 =
* tested on WP 5.2
* handle duplicate names like WP - adding increment
* optimizing code for faster processing

= 3.2 =
* added support for Fancy Product Designer plugin

= 3.1 =
* tested on WP 5.0
* small cosmetic code changes

= 3.0 =
* new option: convert only if JPG will have lower filesize then PNG
* new feature: show converted images statistics
* fix: conflict when there is already JPEG with a same name as PNG
* fix: conflict when PNG name is part of another PNG name ( eg. 'xyz.png' can rename also 'abcxyz.png' )
* optimized for translations

= 2.6 =
* rename PNG image if JPG with the same name already exists

= 2.5 =
* BUG FIXED - disabled checkboxes when autodetect is disabled

= 2.4 =
* now you can disable autodetect PNG transparency

= 2.3 =
* WP 4.9.1 compatibility check
* new compatibility with Toolset Types

= 2.2 =
* Repair revslider database table detection

= 2.1 =
* Added option to leave original PNG image on server after conversion
* Repair SQL replacement query

= 2.0 =
* Replace image and thumbnails extension in database tables
* Moved from Settings to Tools submenu
* Some small fixes

= 1.2 =
* Fix generating background for transparent images (thanks @darkcobalt)

= 1.1 =
* Fix PNG transparency detection

= 1.0 =
* First version