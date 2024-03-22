=== SSL Mixed Content Fix ===
Contributors: Steve85b
Tags: SSL, https, force SSL, mixed content, insecure content, secure website, website security, TLS, security, secure socket layers, HSTS
Requires at least: 4.6
Tested up to: 6.4
Stable tag: 3.2.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A fix for mixed content! This Plugin creates protocol relative urls by removing http + https from links. Works in Front- and Backend!

== Description ==
**Try it out on your free dummy site: Click here => [https://tastewp.com/plugins/http-https-remover](https://tastewp.com/plugins/http-https-remover).**
(this trick works for all plugins in the WP repo - just replace "wordpress" with "tastewp" in the URL)

UPDATE: This plugin will be maintained again! It changed ownership and we're currently collecting ideas how to further improve it. If you have any cool ideas, please let us know in Support Forum. Thank you!

Major updated in the latest release (3.0):
- Plugin has a proper settings page now
- Many bugs fixed
- Code optimized, causing performance to increase a lot


Main features:

* Works in Front- and Backend
* Makes every Plugin compatible with https
* Compatible with WPBakery & Disqus
* Fixes Google Fonts issues
* Makes your website faster

= What does this Plugin do? =

With protocol relative url's you simply leave off the http: or https: part of the resource path. The browser will automatically load the resource using the same protocol that the page was loaded with.

For example, an absolute url may look like
`
src="http://domain.com/script.js"
`
If you were to load this from a https page the script will not be loaded – as non-https resources are not loaded from https pages (for security reasons).

The protocol relative url would look like
`
src="//domain.com/script.js"
`
and would load if the web page was http or https.

**Tipp:** Check your Settings -> General page and make sure your WordPress Address and Site Address are starting with "https".
Add the following two lines in your wp-config.php above the line that​ says "Stop Editing Here":
`
define('FORCE_SSL', true);
define('FORCE_SSL_ADMIN',true);
`

= What is Mixed Content? =

**Mixed content** occurs when initial HTML is loaded over a secure HTTPS connection, but other resources (such as images, videos, stylesheets, scripts) are loaded over an insecure HTTP connection. This is called mixed content because both HTTP and HTTPS content are being loaded to display the same page, and the initial request was secure over HTTPS. Modern browsers display warnings about this type of content to indicate to the user that this page contains insecure resources.

**Note: You should always protect all of your websites with HTTPS, even if they don’t handle sensitive communications.**

= Example =

Without Plugin:
`src="http://domain.com/script01.js"
src="https://domain.com/script02.js"
src="//domain.com/script03.js"`

With Plugin:
`src="//domain.com/script01.js"
src="//domain.com/script02.js"
src="//domain.com/script03.js"`

= If using Cache Plugins =

If the plugin isn't working like expected please purge/clear cache for the changes to take effect!

== Installation ==

1. Upload `http-https-remover` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin from Admin > Plugins menu.
3. Once activated your site is ready!

== Frequently Asked Questions ==

= How do I know if my site has mixed content? =

If a green padlock appears, then your site is secure with no mixed content.
In Chrome or Safari, there will be **no padlock** icon in the browser URL field with mixed content.
In Firefox the padlock icon will reflect a warning with mixed content.

= What if I am using a CDN? =

Change all your CDN references to load with // (this will adapt based on how the page is loaded)

== Screenshots ==

1. The Sourcecode of the Website will look like this!

== Changelog ==

= 3.2.6 =
* Updated carousel module 
* Updated opt-in module
* Resolved issues with PHP 8.2 
* Tested up to WP 6.4

= 3.2.5 =
* Forced "Try it out" module to be disabled by default, user can still enable it manually.

= 3.2.4 =
* Tested with WP 6.3 RC
* Updated all shared modules to their latest versions

= 3.2.3 =
* Adjusted PHP compatibility
* Tested with WP 6.1.1

= 3.2.2 =
* Added support for PHP 8
* Fixed activation issues on PHP 8 and 8.1
* Tested up to WordPress 6.1-RC5
* Fixed issues with option saving
* Resolved errors of missing options
* Added new option for enable/disable plugin testing module
* Updated carrousel banner

= 3.2.1 =
* Fixed function call issue

= 3.2 =
* Carrousel added

= 3.1 =
* Issue that redirection got de-activated after update fixed

= 3.0 =
* Added settings page for plugin configurations
* General bug fixes
* Code & Performance improvement

= 2.4 =
* Updated links

= 2.3 =
* Integrated feedback system

= 2.2 =
* Release Date - 7th Mai 2019*

* Fixed incompatibility with other plugins

= 2.1 =
*Release Date - 20th March 2019*

* Simplified Code

= 2.0 =
* Release Date - 1 March 2018*

* Completely rewritten code.
* Bug fixes

= 1.5.3 =
* Release Date - 28 April 2017*

* Fixed some Google API Issues

= 1.5.2 =
*Release Date - 26 April 2017*

* Improvements

= 1.5.1 =
*Release Date - 25 April 2017*

* Fixed a reCAPTCHA issue!

= 1.5 =
*Release Date - 25 April 2017*

* Now it removes http and https from source code again
* Fixed broken links in social sharing plugins

= 1.4 =
*Release Date - 02 March 2017*

* Finally fixed srcset Problems
* Changed the working method of the Plugin
* Some other bugfixes

= 1.3.1 =
*Release Date - 13 January 2017*

* Added support for srcset tag

= 1.3 =
*Release Date - 07 January 2017*

* Fixed the issue that Twitter card image is not displayed

= 1.2 =
*Release Date - 11 December 2016*

* Added support for Google (Fonts, Ajax, Maps etc.)
* Compatibility for Wordpress 4.7

= 1.1.1 =
*Release Date - 18 October 2016*

* Added support for "content" tag
* Added support for "loaderUrl" tag

= 1.1 =
*Release Date - 17 October 2016*

* Fixed the issue that videos in Revolution Slider stopped playing
* The plugin now works on backend too
* Other small changes

= 1.0 =
*Release Date - 16 October 2016*

* Initial release

== Upgrade Notice ==

= 1.5.3 =
*Release Date - 28 April 2017*

* Fixed some Google API Issues

= 1.5.2 =
*Release Date - 26 April 2017*

* Improvements

= 1.5.1 =
*Release Date - 25 April 2017*

* Fixed a reCAPTCHA issue!

= 1.5 =
*Release Date - 25 April 2017*

* Now it removes http and https from source code again
* Fixed broken links in social sharing plugins

= 1.4 =
*Release Date - 02 March 2017*

* Finally fixed srcset Problems
* Changed the working method of the Plugin
* Some other bugfixes

= 1.3.1 =
*Release Date - 13 January 2017*

* Added support for srcset tag

= 1.3 =
*Release Date - 07 January 2017*

* Fixed the issue that Twitter card image is not displayed

= 1.2 =
*Release Date - 11 December 2016*

* Added support for Google (Fonts, Ajax, Maps etc.)
* Compatibility for Wordpress 4.7

= 1.1.1 =
*Release Date - 18 October 2016*

* Added support for "content" tag
* Added support for "loaderUrl" tag

= 1.1 =
*Release Date - 17 October 2016*

* Fixed the issue that videos in Revolution Slider stopped playing
* The plugin now works on backend too
* Other small changes

= 1.0 =
*Release Date - 16 October 2016*

* Initial release

== Upgrade Notice ==
= 3.2.6 =
* Updated carousel module 
* Updated opt-in module
* Resolved issues with PHP 8.2 
* Tested up to WP 6.4