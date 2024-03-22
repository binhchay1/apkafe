=== Remove jQuery Migrate ===
Contributors: frosdqy
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6AD46HAX3URN4
Tags: remove jquery migrate, disable jquery migrate, remove, jquery migrate, jquery
Requires at least: 3.6
Tested up to: 6.1
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A very lightweight plugin that removes jQuery Migrate script from your WordPress site's front end.

== Description ==

This plugin removes jQuery Migrate script (jquery-migrate.min.js or jquery-migrate.js) from your WordPress site's [front end](https://wordpress.org/documentation/article/wordpress-glossary/#front-end) when the plugin is active. To bring back the removed jQuery Migrate script, simply deactivate this plugin.

To check whether jQuery Migrate is required in the front end, you could use the development version of jQuery Migrate (unminified version) by temporarily turning on [SCRIPT_DEBUG](https://wordpress.org/documentation/article/debugging-in-wordpress/#script_debug) mode in wp-config.php file. The development version shows warnings in the browser console when removed and/or deprecated jQuery APIs are used.

For more info about jQuery Migrate, visit [here](https://github.com/jquery/jquery-migrate#readme).

GitHub: <https://github.com/icaru12/remove-jquery-migrate>


== Installation ==

1. From your WordPress dashboard, go to **Plugins** > **Add New**
2. Search for **Remove jQuery Migrate**
3. Click **Install Now**
4. Activate the plugin
5. Done!


== Changelog ==

= 1.0.3 =
* Minor code improvement
* Tested up to WordPress 6.0

= 1.0.2 =
* Tested up to WordPress 5.4

= 1.0.1 =
* Added function_exists check.

= 1.0 =
* Initial release.