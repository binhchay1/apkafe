=== Amazon Affiliate for WordPress ===
Contributors: flowdee
Donate link: https://donate.flowdee.de
Tags: amazon, affiliate
Requires at least: 5.0.0
Requires PHP: 5.6.0
Tested up to: 5.9.0
Stable tag: 3.17.3

The best WordPress plugin for Amazon Affiliates.

== Changelog ==

= Version 3.17.3 (27th January 2022) =
* Info: WordPress v5.9 compatibility

= Version 3.17.2 (18th January 2022) =
* Fix: `Uncaught TypeError: method_exists` which sometimes occurred in the context of the `list-helper-functions.php` file
* Info: Updated plugin updater to version 1.9.1

= Version 3.17.1 (7th January 2022) =
* Fix: Improved escaping on settings page to address XSS concerns
* Info: WordPress v5.8.3 compatibility

= Version 3.17 (17th November 2021) =
* New: Included "max_input_vars" in debug information
* Tweak: Using new endpoint for license & update server requests
* Info: Updated plugin updater to version 1.8
* Info: Updated translation files
* Info: WordPress v5.8.2 compatibility

= Version 3.16 (10th August 2021) =
* New: Added `rel="sponsored"` to all outgoing Amazon affiliate links
* New: Added `rel="noopener"` to all outgoing links with `target="_blank"`
* Fix: Currency symbol/format for Amazon.se was incorrect
* Info: Updated all template files
* Info: WordPress v5.8 compatibility

= Version 3.15.2 (22th April 2021) =
* Tweak: Moved away from "spacer images" in templates
* Info: Updated template files `vertical.php`, `list.php`, `widget-small.php`
* Info: WordPress v5.7.1 compatibility

= Version 3.15.1 (18th March 2021) =
* New: Added support for Amazon Poland
* Tweak: Optimized bestseller results returned by the Amazon API
* Tweak: Removed no longer needed `simplexml` dependency checks
* Tweak: Optimized output of admin notice for AMP compatibility
* Tweak: Minor text adjustments on the settings page
* Fix: Geotargeting for Amazon Sweden didn't work as expected
* Info: Updated translation files
* Info: WordPress v5.7 compatibility

= Version 3.15 (9th February 2021) =
* Tweak: Optimized assets building and moved files to /assets/dist/ instead of /public/assets/
* Tweak: Optimized license data handling for better performance
* Fix: Click tracking incorrectly used product title when ASIN was selected
* Fix: Product images available as PNG file could not be displayed correctly
* Fix: Browser console JavaScript "sameSite" warning
* PHP v8.0.0 compatibility
* WordPress v5.6.1 compatibility

= Version 3.14.3 (11th November 2020) =
* New: Added support for Amazon Sweden
* Fix: The placeholder `%post_title%` didn't work as expected with special chars
* Fix: License check slowed down a site in case there was a response issue
* Fix: License check failed in case the plugin has stored a deprecated license server URL
* Updated translation files
* Plugin Updater v1.8
* WordPress v5.5.3 compatibility

= Version 3.14.2 (16th October 2020) =
* New: Added filter "aawp_admin_menu_cap" for overwriting the admin menu capability
* Fix: Geotargeting didn't work as expected when a site's default store was set to Canada
* Fix: In a specific case, the plugin settings page tab "Amazon API" showed broken HTML output when informing about a connection issue

= Version 3.14.1 (22nd September 2020) =
* Tweak: Minor adjustments related to the license handling

= Version 3.14 (22nd September 2020) =
* New: Added Google Accelerated Mobile Pages (AMP) support for all templates incl. comparison tables [(view documentation)](https://getaawp.com/docs/article/google-accelerated-mobile-pages-amp/)
* Tweak: Optimized license handling
* Updated translation files
* WordPress v5.5.1 compatibility

= Version 3.13 (17th August 2020) =
* New: Introducing our "Database Garbage Collection" which regularly cleans up your products and lists database tables [(view documentation)](https://getaawp.com/docs/article/database-garbage-collection/)
* New: As an affiliate, you can now enter your affiliate id on the settings page (tab "general") which will be taken for the credits link
* Tweak: Removed deprecated and no longer internal functions
* Updated translation files
* WordPress v5.5 compatibility

= Version 3.12.4 (20th July 2020) =
* New: You can now place products from Amazon.se
* New: Added geo targeting rule for New Zealand
* New: Added price currency formatting for Amazon.ae and Amazon.sg
* Tweak: Optimized debug logging in order to reduce amount of data stored
* Tweak: Debug logging is no longer activated by default
* Fix: Updated "Add to cart" documentation link on the settings page
* Updated translation files
* PHP v7.4.1 compatibility
* WordPress v5.4.2 compatibility

= Version 3.12.3 (14th May 2020) =
* Fix: Image proxy didn't work properly for medium or small sized images

= Version 3.12.2 (13th May 2020) =
* Tweak: Optimized image proxy

= Version 3.12.1 (11th May 2020) =
* Tweak: Optimized image proxy
* Fix: New releases lists didn't return the expected results from API
* Plugin Updater v1.7
* WordPress v5.4.1 compatibility

= Version 3.12 (8th April 2020) =
* New: Switched over to our Amazon API library to avoid third-party dependencies
* New: Added support for Amazon Netherlands
* New: Added support for Amazon Singapore
* Tweak: Shortcode attribute `items` limit increased to 20 products now
* Tweak: Shortcode attributes `filter_items` and `order_items` now accepts values up to 20 again
* Tweak: Field shortcode value `list_price` now returns nothing instead of a text string, in case there is no list price available
* Tweak: Instead of API status `undefined`, the plugin now returns the real API error code/message
* Tweak: Updated admin flag icons
* Fix: Shortcode attribute `filter="available"` didn't work properly
* WordPress v5.4 compatibility

= Version 3.11 (20th March 2020) =
* Tweak: Converted the plugin's database tables to utf8mb4
* Fix: Product titles and descriptions weren't able to display special characters properly after the last update

= Version 3.10.4 (12th March 2020) =
* Tweak: API response errors will now show up in the plugin log (which can be found in the admin menu "Support")
* Fix: Products couldn't be placed if their descriptions included emojis or so-called "fancy letters"

= Version 3.10.3 (10th March 2020) =
* Tweak: Rebuilt review crawler to no longer be dependant on the third-party PHP library "simple_html_dom"
* Tweak: Optimized "outdated PHP cURL notice" on the settings pages

= Version 3.10.2 (5th March 2020) =
* Tweak: Geotargeting API "geoip-db.com" changed URL to "geolocation-db.com"
* Tweak: Removed admin menu "dasboard" item
* Fix: Crawling reviews with the "simple_html_dom" library lead to issues when using certain PHP versions

= Version 3.10.1 (14th January 2020) =
* Tweak: Plugin now displays product data even when the API is temporary disconnected (Note: this data comes from the database directly and may be outdated)
* Fix: Geotargeting was applied to links which weren't placed by our plugin
* Fix: Shortcodes couldn't be hidden via settings when the API was disconnected
* Fix: Product price didn't show up when price data was stored with a comma instead of a dot as decimal point

= Version 3.10 (7th January 2020) =
* New: Added settings for description in list template
* New: Filtering and sorting of products with API v5 (Note: "filter_items" and "order_items" is limited to a maximum of 10)
* WordPress v5.3.2 compatibility

= Version 3.9.7 (11th December 2019) =
* Tweak: Optimized product data before storing in the database in order to avoid the missing output of product prices
* Tweak: Settings tab "Amazon API" now shows some additional technical information for troubleshooting
* Tweak: Optimized settings tab "Licensing" CSS styles for WordPress v5.3+

= Version 3.9.6 (27th November 2019) =
* Tweak: Optimized list template teaser text generation and length
* Fix: Shortcode attribute "link_type" and its values "cart" and "reviews" didn't work properly after API v5 migration
* Fix: Setting "add to cart links" didn't work properly
* Fix: Setting star-/rating link target "reviews" didn't work properly
* WordPress v5.3 compatibility

= Version 3.9.5 (8th November 2019) =
* Tweak: Optimized settings "licensing" tab for better usability and troubleshooting
* Tweak: Shortcode notice for used Browse Node IDs (after Amazon API v5 migration) will now only show up for logged in editors/administrators

= Version 3.9.4 (6th November 2019) =
* Tweak: Optimized loading of dependencies in order to avoid conflicts with third party plugins
* Tweak: Creating and emptying of the plugin's database tables can now be initiated via the plugin's support page

= Version 3.9.3 (3rd November 2019) =
* Tweak: Optimized loading of dependencies in order to avoid conflicts with third party plugins
* Tweak: Updated bestseller and new releases widget labels
* Tweak: Extended error messages shown on the plugin settings "Amazon API" tab
* Tweak: Extended documentation links on plugin settings "Amazon API" tab
* Fix: Product search on the comparison tables admin page did not work anymore
* Fix: "Cannot redeclare GuzzleHttp\Promise\coroutine()"
* Fix: "Cannot redeclare GuzzleHttp\Promise\queue()"

= Version 3.9.2 (30th October 2019) =
* Fix: "Error message: Uncaught Error: Call to undefined function GuzzleHttp\Psr7\build_query()"
* Fix: "Error message: Uncaught Error: Call to undefined function Promise\promise_for()"

= Version 3.9.1 (30th October 2019) =
* Tweak: Comparison tables saved as "draft" won't show up on the frontend anymore
* Fix: "Error message: Uncaught Error: Call to undefined function GuzzleHttp\choose_handler()"
* Fix: "Undefined index: highlight_text in /includes/functions/components/table-builder.php on line 1654"

= Version 3.9.0 (29th October 2019) =
* IMPORTANT: AWS API keys no longer work! Please migrate your keys as shown here: https://getaawp.com/docs/article/amazon-product-advertising-api-credentials/
* New: Amazon Product Advertising API v5 (More details can be found here: https://getaawp.com/docs/article/amazon-product-advertising-api-v5/)
* WordPress v5.2.4 compatibility

= Version 3.8.14 (7th October 2019) =
* Tweak: Further enhancements related to the previously experienced 'simple_html_dom' issue

= Version 3.8.13 (1st October 2019) =
* Tweak: Optimized loading of third party libraries in order to prevent issues with other plugins
* Fix: "Uncaught Error: Class 'simple_html_dom' not found in class.aawp-api.php"

= Version 3.8.12 (30th September 2019) =
* Fix: Table builder product search didn't work properly after the last plugin updates

= Version 3.8.11 (28th September 2019) =
* Fix: "PHP Warning: Use of undefined constant AAWP_DEBUG in /includes/scripts.php"

= Version 3.8.10 (27th September 2019) =
* New: Added support for Amazon United Arab States
* Tweak: Use minus sign instead of dash for price reduction
* Tweak: The plugin's shortcode cleanup function now fires later in order to avoid conflicts with third party plugins
* Tweak: Added description for error code "RequestThrottled" to the API settings page
* Fix: PHP warnings related to "simple_html_dom.php" library
* Minor improvements and fixes
* PHP v7.3.8 compatibility
* WordPress v5.2.3 compatibility

= Version 3.8.9 (14th December 2018) =
* New: Added geotargeting support for Amazon Turkey
* New: Added geotargeting service provider "db-ip.com" as new option
* Tweak: Accidentally entered white spaces at the beginning and end of main shortcode attributes will be removed automatically
* Tweak: License key will now be hidden to all users on by default
* Fix: Field value "old_price" didn't work as expected
* Fix: Removed geotargeting service provider "ipdata.co" due to API key requirement
* Fix: Geotargeting for Amazon Australia didn't work as expected
* WordPress v5.0.1 compatibility

= Version 3.8.8 (1st December 2018) =
* New: Added support for Amazon Turkey
* New: Added support for "tagDiv AMP" plugin
* New: Added field value "old_price" which displays the old (list) price if a product is on sale, otherwise it outputs nothing
* Tweak: Accidentally entered HTML will now be stripped out automatically from the main shortcode attributes (box, bestseller, new, link & fields)
* Tweak: From now on you can enter HTML into the "products not found" notice input field
* Fix: Geotargeting service "geoip-db.com" API url was missing a slash which may lead into redirecting the API call
* Fix: "TypeError: Argument 1 passed to AAWP_API::is_associative_array() must be of the type array, null given, called in /includes/aawp/class.aawp-api.php on line 948"

= Version 3.8.7 (31th August 2018) =
* Tweak: Shortcode attribute numbering="none" allows you to hide the numbering column of the table template
* Fix: Links to product review pages were broken, due to latest changes of Amazon. Added a temporary fix.

= Version 3.8.6 (21th August 2018) =
* Tweak: Optimized product variation handling
* Fix: WP Rocket's "Combine Javascript Files" setting striped out AAWP's geotargeting inline javascript code
* Fix: "Column 'title' cannot be null for query INSERT INTO `aawp_products`
* WordPress v4.9.8 compatibility

= Version 3.8.5 (14th June 2018) =
* Fix: Since the last update, geotargeting didn't work properly for every site visitor

= Version 3.8.4 (11th June 2018) =
* New: Added new geotargeting setting which allows you to choose from multiple API services
* Tweak: From now on "geoip-db.com" is the new default geotargeting API service
* Fix: Using the recently added geotargeting API may lead into the issue "No 'Access-Control-Allow-Origin' header is present on the requested resource"

= Version 3.8.3 (4th June 2018) =
* Tweak: Optimized error handling of image proxy
* Tweak: Added .htaccess file in order to prevent "Permission Denied" when accessing the image proxy file

= Version 3.8.2 (24th May 2018) =
* New: Added shortcode attribute "button_style", which allows adjusting the button style on a shortcode basis
* Tweak: Image proxy requires PHP "allow_url_fopen"; added note to settings and as well as a dependency checks to the output itself
* Tweak: Image proxy will now be applied even when passing custom Amazon images via shortcode
* Tweak: Optimized CSS for templates with spacer images (comparison table and grid)

= Version 3.8.1 (16th May 2018) =
* Fix: Image proxy (introduced with v3.8) didn't work properly

= Version 3.8.0 (15th May 2018) =
* New: Added support for Amazon Australia
* New: Added new setting which allows delivering product images via a privacy proxy (GDPR)
* Tweak: Geo-targeting now makes use of the faster and more reliable API "ipdata.co"
* Tweak: Removed output of cron events which may occur in cron logs
* Fix: "PHP Notice: Undefined index: filter in /aawp/includes/functions/components/items.php on line 352"

= Version 3.7.1 (13th April 2018) =
* Tweak: For security reasons, the previously introduced "local images" functionality must be enabled by using the filter "aawp_product_local_images_enabled" first
* Tweak: Added routine in order to remove local images on a daily basis

= Version 3.7.0 (12th April 2018) =
* New: Added new setting in order to download and serve product images locally instead of using Amazon's server
* Tweak: Optimized Plugin Update Checker

= Version 3.6.12 (10th April 2018) =
* New: Added filter value "prime", in order to show only products which are available via Amazon Prime
* New: Field thumbnails can now be centered, by adding image_align="center" to the shortcode
* Tweak: Optimize product description for downloadable music tracks
* Tweak: Added dependency check for "PHP XML" extension
* Fix: Google AMP validator criticized markup issues when using "Better AMP" plugin
* Fix: Using a combination of "filter" and "order" didn't return the expected results
* Fix: Custom bestseller ribbon text was not used properly in combination with the "table" template
* Fix: "PHP Warning: sizeof(): Parameter must be an array or an object that implements Countable in ../includes/aawp/class.aawp-api.php on line 744"
* PHP v7.2 compatibility
* WordPress v4.9.5 compatibility

= Version 3.6.11 (6th March 2018) =
* New: Added new setting which allows customizing the "Reviews" label
* New: Added field value "used_price" in order to display the used price of a product
* New: Added support for "Better AMP" plugin
* Tweak: Optimized Click Tracking for Google Analytics
* Tweak: Optimized performance of link shortcode
* Tweak: Optimized formatting of reviews number
* Tweak: Added HTML wrapper to "No rating yet" notice inside the table template
* Fix: "Cannot redeclare class simple_html_dom_node"
* Fix: "PHP Warning: sizeof(): Parameter must be an array or an object that implements Countable in ../includes/aawp/class.aawp-api.php on line 328"
* Fix: "PHP Warning: sizeof(): Parameter must be an array or an object that implements Countable in product-helper-functions.php on line 56"
* Minor CSS improvements
* Updated translations
* Updated the following templates: /table.php
* WordPress v4.9.4 compatibility

= Version 3.6.10 (23th January 2018) =
* Tweak: Amazon France and Spain now use "Prime" instead of "Premium" logo
* Fix: Manually renewing cache via settings was broken after last plugin update
* Fix: Undo programmatically setting of image width/height
* Minor improvements and fixes
* Updated translations
* WordPress v4.9.2 compatibility

= Version 3.6.9 (15th January 2018) =
* New: Added placeholder %yoast_focus_keyword% which can be used as keyword for bestseller lists
* New: Added shortcode attribute "button_class", which allows using your own button classes
* Tweak: Advertised price can be shown/hidden by using the shortcode attribute price="show" respectively price="hide"
* Tweak: Optimized geotargeting in order to provide better results, especially for North and South America
* Tweak: Optimized handling of HTML attributes "title" and "alt"
* Tweak: Better escaping of polysyllabic super url keyword parameters
* Tweak: Added fallback for loading AMP styles
* Tweak: Table builder field type "Custom HTML" now executes shortcodes
* Fix: Products with emojis in their description couldn't be stored in the database
* Fix: Settings paged used non-unique html ids
* Fix: Table builder edit screen showed an invalid settings link

= Version 3.6.8 (5th December 2017) =
* New: Added sale price info to "table" template
* Tweak: Optimized comparison table settings in order to hide labels (first column)
* Tweak: Setting "show description on mobile devices" will now be applied to "AMP" template too
* Tweak: Shortcode attribute "title_length" now works with link shortcode too
* Tweak: Optimized button html
* Fix: When using the "table" template, shortcode attribute price="none" didn't hide the price column correctly
* Fix: Due to latest WordPress core updates, our color picker in admin area was displayed incorrect
* Fix: In case of a long browse node id and a 32-bit web hosting server, the API didn't return the desired results
* Fix: In some cases Amazon's API returned "png" images which led into broken product images
* Fix: Adding super-url parameter lead into broken "add to cart" links
* Fix: PHP version required notice showed 5.3 instead of 5.6
* WordPress v4.9.1 compatibility

= Version 3.6.7 (5th November 2017) =
* Fix: Cache renewal didn't not working properly after the latest update
* Fix: Added missing "jquery-ui-sortable" for admin scripts
* Increased requirements to PHP v5.6 or newer (v7.0+ recommended)

= Version 3.6.6 (31th October 2017) =
* New: Shortcode attribute "tracking_id" now works for comparison tables as well
* New: Added shortcode attribute "keywords", which allows passing the "Super-URL" parameter
* New: Super-URL parameter will be applied to keyword-based bestseller lists automatically
* Tweak: Optimized and reduced AMP styles
* Tweak: Optimized product images when using the following templates: vertical, widget-small
* Tweak: Optimized cache handler in order to dynamically renew more products/lists during our routine
* Fix: In some cases Amazon's API returned "png" images which led into broken product images
* Fix: Filtering didn't return items correctly
* Fix: Filter "offer" may include items which are not on sale
* Fix: Ordering didn't return items correctly
* Fix: "filter_items" and "order_items" didn't provide the correct amount of products
* WordPress v4.8.3 compatibility
* Updated plugin updater class to v1.6.15
* Updated the following templates: /products/vertical.php

= Version 3.6.5 (9th October 2017) =
* New: Added shortcode attribute "image_align" to field shortcode, in order to float images (values "left" & "right")
* New: Added setting to comparison table, which allows hiding the first column
* New: Added button text placeholder %price%
* New: Added setting in order to hide buttons globally
* Tweak: The first column on a comparison table will automatically be hidden, when no labels were entered
* Tweak: "ribbon.php" was removed from template parts and will now be handled inside the plugin functions again
* Tweak: Optimized handling of user input when using the widgets
* Tweak: Optimized handling of user input when entering Amazon API credentials
* Tweak: Optimized handling of passing a custom tracking id via shortcode
* Tweak: From now on it's possible to use html along with the button text
* Fix: Price difference for a specific product
* Fix: Geotargeting and click tracking was not applied correctly to thumbnails and custom text (table builder)
* Fix: "Fatal error: Cannot unset string offsets in /includes/aawp/class.aawp-db-products.php on line 593"

= Version 3.6.4 (25th September 2017) =
* Tweak: Optimize output of thumbnails inside comparison tables
* Fix: Setting "image size" didn't work
* Fix: Support page showed older version than actually installed
* Removed affiliate link type "shortened" in order to be safe regarding Amazon's upcoming changes (see https://affiliate-program.amazon.co.uk/promotion/whatschanging, October 1, 2017)

= Version 3.6.3 (21th September 2017) =
* Tweak: Optimized product description generation
* Tweak: Optimized image id collection
* WordPress v4.8.2 compatibility

= Version 3.6.2 (19th September 2017) =
* Tweak: When changing the default Amazon store, the database tables get flushed now
* Fix: Product boxes showed current price instead of list price
* Fix: Button title wasn't set correctly
* Fix: Hiding products didn't work (table builder)
* Fix: Product links were broken in particular cases
* Fix: Buy now button was missing inside product boxes in particular cases
* Fix: Description items were missing inside product boxes in particular cases
* Fix: Thumbnails didn't show up correctly for Amazon.co.jp
* Fix: Pricing data was incorrect for Amazon.co.jp
* Fix: Division by zero in class.aawp-product.php on line 263

= Version 3.6.1 (14th September 2017) =
* Tweak: Optimized plugin upgrade handling
* Tweak: Reset database tables and remove products/lists via admin page "support"
* Fix: Table data showing "reviews" label two times (table builder)
* Fix: Field values "percentage_saved" and "amount_saved" accidentally returned the current price
* Fix: "Fatal error: Uncaught Error: Class 'AAWP_DB_Lists' not found in install.php"

= Version 3.6.0 (13th September 2017) =
* New: Introducing keyword placeholders %post_title%, %page_title%, %post_category% for bestseller lists
* Tweak: Optimized performance
* Fix: Resolved performance issues which which were related to the database storage
* Fix: Overwriting custom_text with custom_html field didn't save inputs (table builder)
* Fix: Custom CSS removed backslashes
* Rebuild database storage of products and lists
* Minor improvements and fixes

= Version 3.5.5 (31th August 2017) =
* Tweak: Disclaimer text now executes shortcodes
* Preparations for upcoming v3.6 update

= Version 3.5.4 (23th August 2017) =
* Tweak: Optimized click tracking Google Analytics code detection
* Tweak: Optimize CSS in order to prevent description items from out-sizing the html container
* Minor improvements and fixes

= Version 3.5.3 (14th August 2017) =
* Tweak: Optimized embedding "custom css"
* Tweak: Geo-targeting is now disabled for logged in administrators
* Tweak: Optimized comparison table CSS for mobile devices
* Tweak: Added star-/ratings info link to settings page
* Fix: After the last update, geo-targeting in some cases didn't work as it should
* Fix: Javascript "media" related errors blocked comparison table builder

= Version 3.5.2 (10th August 2017) =
* New: Added a new setting in order to force disclaimer to show up after widgets
* Fix: After the last update, saving a comparison table in some cases lead into removing the last product(s)
* Fix: Disclaimer notice placeholder %last_update% was not replaced when in combination with comparison tables

= Version 3.5.1 (9th August 2017) =
* New: Table builder product search results now show prime status
* New: Added Amazon Prime support for India & Mexico
* Tweak: Increased comparison table row limit from 20 up to 30
* Tweak: Optimized comparison table styles
* Tweak: Optimized comparison table product highlighting configuration
* Tweak: Optimized grid size limitation handling
* Tweak: Optimized CSS/JS loading
* Tweak: Optimized geotargeting country detection handling
* Fix: When showing multiple comparison tables on the same post/page, styles weren't applied correctly
* Fix: Disclaimer didn't show up after comparison tables
* Minor improvements and fixes
* Updated translations

= Version 3.5.0 (31th July 2017) =
* New: Comparison Tables (Builder)
* New: Template Variables
* Minor improvements and fixes
* Updated translations

= Version 3.4.9 (10th July 2017) =
* Tweak: Optimized product titles used for geotargeting search mode
* Fix: Geotargeting was not being applied correctly to buttons when using field shortcodes
* Fix: In some cases the product detail page links were broken due to special chars

= Version 3.4.8 (25th June 2017) =
* Tweak: Debug logging is now enabled by default in order to ensure better support
* Fix: In some cases the wrong product image was taken from API
* Fix: Fixed an issue which occasionally lead into API disconnects

= Version 3.4.7 (22th June 2017) =
* New: Added template function "get_product_numbering()" in order to get the number/index of the current product
* Tweak: Optimized "no products found" notice conditions in order to make it easier for new plugin users
* Tweak: Optimized CSS styles for product images
* Tweak: Adding a debug log entry when plugin disconnects rom API due to a bad response
* Tweak: From now on the plugin tries reconnecting to the API after being disconnected due to a bad API response
* Fix: Placing shortcodes in text widgets might lead into an unattractive output

= Version 3.4.6 (13th June 2017) =
* Tweak: Setting shortcode "disabled" now returns nothing instead of showing the shortcode itself
* Fix: Removed debugging and optimized debugging functions

= Version 3.4.5 (12th June 2017) =
* Tweak: When renewing a product manually, the plugin tries to update the ratings as well
* Tweak: Optimized storage of lists
* Tweak: Optimized general product rating renewals
* Fix: Renewing lists might not work correctly regarding when using browsenode="none"
* WordPress 4.8 compatibility checks

= Version 3.4.4 (23th May 2017) =
* New: Added shortcode attribute "button_detail_rel" in order to pass values as "nofollow"
* Tweak: In case the server does not support "allow_url_fopen", a notice will be shown on "licensing" tab
* Tweak: API status will automatically turn to disconnected in case there's something wrong with your API keys after some time
* Fix: Geo targeting mode "product search" did not work correctly outside of standard product containers
* Fix: Shortcode parameter browsenode="none" didn't work correctly
* Fix: When using "order_items", in some cases the lists might not contain the desired amount of items
* Fix: "PHP Notice: Array to string conversion in /aawp/templates/products/list.php on line 25"

= Version 3.4.3 (9th May 2017) =
* New: Float product boxes by using shortcode attribute float="left" (or right); Recommended templates: vertical (standard), list, widget-vertical, widget-small
* New: Hide price column when using "table" template via shortcode attribute price="none"
* New: When using fields "thumb" shortcode, you can define the width/height as follows: e.g. image_width="150", image_height="150"
* New: Hide bestseller/new/sale ribbon via shortcode as follows: ribbon="none" / sale_ribbon="none"
* Tweak: Rebuild price reduction settings
* Tweak: Optimized geotargeting and use search links instead of direct links as default link target
* Tweak: Added new shortcode setting option in order to deactivate shortcode activation
* Tweak: Optimized empty p/br fix when executing shortcodes
* Tweak: Optimized thumbnail CSS "background-size" for templates "vertical" and "widget-small"
* Fix: Grid template was overwritten when selecting a non-standard template via settings
* Fix: Amount saved calculation was wrong for products with very small discounts
* Updated the following templates: amp.php, /products/horizontal.php, /products/list.php, /products/vertical.php, /products/widget-vertical.php

= Version 3.4.2 (24th April 2017) =
* New: Added latest plugin version check and dashboard info
* Tweak: When using "list" template, the teaser can now be hidden with description="none" too (by default it's teaser="none")
* Tweak: Added extra styles for Thrive Content Builder
* Tweak: Minor global and template related style optimizations
* Tweak: Removed and disabled "editorial review" again in order to unload database
* Fix: Product and list posts were accidentally accessible on frontend and listed in sitemaps
* Fix: Ratings were not available in floating point numbers and lead to incorrect star ratings
* Fix: In some cases available plugin updates were not shown correctly
* Fix: Prime logo was not returned correctly when using the fields PHP function
* Fix: Shortcode usage was not detected correctly when using setting "disclaimer bottom"
* Fix: PHP Notice "Use of undefined constant in ... sysinfo.php on line 71"
* Updated plugin updater class to v1.6.12

= Version 3.4.1 (27th March 2017) =
* Fix: First product image was not taken correctly from the API result
* Fix: Button icons didn't show up
* Fix: Settings link on plugins overview page was wrong
* Fix: Using shortcode attribute "image" with a numeric value lead into a broken image
* Fix: Reviews link target "reviews" pointed to wishlist by mistake
* Fix: Using fields PHP function with description was not able to return an array

= Version 3.4 (21th March 2017) =
* New: Introducing our new admin pages setup
* New: Introducing our new product and list handling setup
* New: The default product image can now be selected very comfortable via its product edit page (more of this coming soon!)
* New: The "products not found" notice can now be edited via plugin settings (tab "functions")
* New: Bestseller ribbon text can now be edited via plugin settings (tab "functions") and overwritten via shortcode attribute "ribbon_text"
* New: New releases ribbon text can now be edited via plugin settings (tab "functions") and overwritten via shortcode attribute "ribbon_text"
* New: Added new value "reviews" for ordering of items
* New: Added fields "timestamp" parameter which returns the timestamp of the last update for a product (only makes sense when using the php functions)
* New: Re-added fields "editorial_review" parameter
* Tweak: Optimized shortcode cleanup and added an extra plugin setting
* Tweak: From now on the plugin itself frequently checks that all service events are running properly
* Tweak: Tracking ids are not longer stored in the database and can be changed immediately
* Tweak: Optimized fetching products from API (regarding no more available and not accessible products)
* Tweak: Optimized cache handling and renewing workflows
* Tweak: Optimized uninstall.php in order to remove data and settings only when activated via settings
* Fix: In some cases the shortcode cleanup prevented the content to be displayed properly
* Minor enhancements and fixes

= Version 3.3.8 (31th January 2017) =
* New: Added black and white Amazon icons for buttons
* Tweak: Optimized AMP style handling
* Tweak: Optimized cache handler
* Fix: In some cases lists showed an incorrect order of products
* Fix: The currency label setting (EUR vs. €) was not applied correctly for products with pricing variations
* Fix: "PHP Warning: Invalid argument supplied for foreach() in ... cache-handler.php on line 252"
* Minor CSS enhancements and fixes
* Updated plugin updater class to v1.6.10

= Version 3.3.7 (26th January 2017) =
* Tweak: Filter attributes "offer" and "available" can now be used in combination: e.g. filter="price" filterby="offer,available"
* Tweak: Optimized HTML & CSS for empty pricing inside boxes
* Fix: The last update 3.3.6 produced a fatal PHP error on web servers running PHP lower than 5.5. Sorry for this! :-(
* Fix: "Can't use method return value in write context in ... class.aawp-functions.php on line 1555"
* Fix: Pricing col in table template was missing

= Version 3.3.6 (25th January 2017) =
* New: Select default product thumbnail image size via plugin settings
* Tweak: Optimized styles when using image_size="large" shortcode attribute with horizontal template
* Tweak: Added shortcut for products available filter by using the attributes: filter="price" filterby="available"
* Tweak: In case a product is not available via API there will be a proper info instead of the previous "product not found" message
* Tweak: Support tab from now on shows feedback if cron events are running properly
* Fix: New releases lists with only 1 result didn't show up correctly
* Fix: Cached lists showed incorrect order of products
* Fix: In some cases product boxes showing an discount which was not visible on the Amazon product page
* Fix: Click-tracking didn't work when using Yoast's Google Analytics plugins
* Fix: Optimized url rewriting for geotargeting
* Fix: "PHP Warning: file_get_contents(): http:// wrapper is disabled in the server configuration by allow_url_fopen=0"
* Minor enhancements and fixes
* Updated plugin updater class to v1.6.9

= Version 3.3.5 (25th December 2016) =
* Fix: Plugin updates didn't show up correctly

= Version 3.3.4 (23th December 2016) =
* New: Added a shortcode ("amazon_last_update" respectively "aawp_last_update") in order to display the global last update date/time wherever you want to
* New: Added a shortcode ("amazon_disclaimer" respectively "aawp_disclaimer") in order to display the disclaimer wherever you want to
* New: Widget templates ("widget-vertical" and "widget-small") can now be used in main content as well
* Tweak: Linked field values from now on make use the standard class "aawp-field-link"; additionally you can use "link_class" in order to set a custom class
* Tweak: Amazon API from now on delivers secured image urls by default
* Fix: Link shortcode didn't find a product since the last update
* Fix: Geotargeting functionality accidentally added an uneeded tracking id to masked amzn.to links
* Fix: Removed the duplicated licensing settings tab issue
* Updated plugin updater class to v1.6.7

= Version 3.3.3 (19th December 2016) =
* New: Data field values can be linked by adding the shortcode attribute format="linked"
* New: Shortcode attribute button_detail can now be used with lists as well
* New: Added shortcode attribute "order_items" - which can be used in combination with "items" - in order to increase product radius
* New: Added (partial) French and Italian translations
* Tweak: Optimized sale price handling
* Tweak: Allow selecting templates when using certain page builder widgets
* Tweak: Optimized CSS styles when using vertical template and button_detail together
* Tweak: Optimized last update timestamp handling
* Tweak: Optimized cache renewal handling
* Tweak: Optimized AMP CSS styles
* Fix: Removed "adding a trailing slash" for detail_button urls
* Fix: Disclaimer "at bottom of the page" didn't show up when using widgets only
* Fix: In certain cases book descriptions showed "Array"
* Fix: Using orderby="amount_saved" accidentally lead into a fatal PHP error
* Fix: SQL syntax error when list keywords included apostrophes
* Fix: Link shortcode didn't return the product correctly
* Fix: "PHP Notice: Undefined offset:" when using link shortcodes
* Fix: "PHP Notice: Trying to get property of non-object in class.aawp-api.php"

= Version 3.3.2 (21th November 2016) =
* New: Added a separate template and CSS file for Accelerated Mobile Pages (AMP) only
* New: Amazon Prime logo can be unlinked via plugin settings
* New: The "sale" ribbon can be hidden via plugin settings
* New: Added separate cronjob for updating product ratings
* Tweak: Product ratings will be updated separately and use an alternative new source
* Tweak: From now on the plugin automatically detects when your site is secured by a SSL certificate
* Tweak: From now on "add to cart" links only affect buttons
* Tweak: Optimized custom CSS loading position in order to avoid being overwritten
* Fix: Geo-targeting didn't with Amazon.com didn't work correctly
* Fix: AMP validation showed "The author stylesheet specified in tag 'style amp-custom' is too long"
* Fix: CSS fix when using vertical template with "Thrive Content Builder"
* Fix: Table template might be displayed incorrectly on Chrome
* Fix: A previously entered custom template was used without selecting "custom" on plugin settings
* Fix: Increased prices lead to negative sale discounts
* Fix: Template "widget-small" wasn't loaded correctly due to some changes of the last updates
* Minor improvements and fixes

= Version 3.3.1 (9th November 2016) =
* Minor improvements and fixes
* Fix: Grids by default used a wrong template and lead into incorrect presentation
* Fix: Shortcodes in text widgets selected the wrong template
* Fix: Field values reviews/rating_count didn't return the number correctly

= Version 3.3.0 (7th November 2016) =
* New: From now on filtering can be applied to box shortcodes too
* New: Added fields "salesrank" parameter which returns the current salesrank the product's main category
* New: Smart Caching functionality which can be enabled on the settings page (tab "general")
* Fix: Shortcode attribute "store" didn't overwrite Amazon Prime urls correctly
* Fix: Settings page, tab "functions", listed deprecated templates only
* Fix: "Memory exhausted" issue
* Fix: "Error while sending QUERY packet. PID=XXXXX in /wp-includes/wp-db.php on line XXXX" issue

= Version 3.2.2 (30th October 2016) =
* New: Added fields "format" parameter which allows returning the numeric amount of prices
* New: Added fields "raw" parameter which allows returning the description as array
* Tweak: Optimized sale price calculation
* Tweak: Optimized star rating styles
* Tweak: Updated custom CSS example on settings page
* Tweak: Optimized table template styles
* Fix: Field template functions threw an error when API was not connected
* Fix: AMP styles weren't included correctly

= Version 3.2.1 (26th October 2016) =
* New: Added scripts compatibility mode
* New: Added shortcode attribute "select" for specifying ranges without using start/end
* Tweak: Optimized table template styles
* Tweak: Optimized shortcode value decoding in order use special chars probably
* Tweak: Shortcode attribute star_rating="none" can now be used to hide the rating col of the table template
* Tweak: Field value "reviews" replaces "rating_count"
* Fix: Button didn't show up correctly when using PHP field function
* Fix: Include error when displaying buttons via field shortcode

= Version 3.2.0 (23th October 2016) =
* New: Added Geo-targeting functionality
* New: Added list template
* New: Added grid template
* New: Added widgets for box, bestseller and new releases
* New: Added small widget template
* New: Added Google Tag Manager support for click tracking
* New: Added multiple star rating icon options
* New: Added "class" shortcode attribute in order to set a custom class for product container
* New: Added "star_rating_link" shortcode attribute in order to remove/overwrite the star rating link
* New: Added "image_title" shortcode attribute in order to set image title attribute
* New: Added "rating" shortcode attribute in order to manually set a rating value between 1 and 5
* New: Added settings which allows you to enable shortcodes execution for text widgets and taxonomy descriptions
* New: Added "Add to Cart" links (90-days-cookie)
* Tweak: Rebuild templating
* Tweak: Rebuild caching system
* Tweak: Optimized rating handling
* Tweak: Optimized table templates
* Tweak: Optimized presentation of settings functions tab
* Tweak: Last update placeholder can now be used at the bottom of the page as well
* Tweak: You can shown/hide product descriptions on mobile devices via plugin settings
* Tweak: Optimized scripts handling
* Tweak: Shortcode attribute "tracking_id" now works with long affiliate urls as well
* Fix: Description items showing "0"
* Fix: Box shortcodes including more than 10 asin returned an error
* Plenty of smaller enhancements and fixes
* !!! Thank you very much for all of your feedback !!!

= Version 3.1.8 (5th September 2016) =
* Tweak: Optimized fetching product ratings

= Version 3.1.7 (15th August 2016) =
* Tweak: Short affiliate links using https instead of http from now on
* Fix: Pricing issues for Amazon.co.jp
* Fix: "box_table" template couldn't be selected via plugin settings
* Fix: Admin menu might not show up when using older WP versions

= Version 3.1.6 (10th July 2016) =
* New: Added "price" shortcode attribute in order to set/overwrite the advertised price
* New: By using the "price" shortcode attribute and passing the value "none" you can hide the advertised price
* New: Default item amounts for lists can be predefined via plugin settings (tab "functions")
* New: You can hide the "price unavailable" message via plugin settings
* Tweak: Optimized data handling for products having multiple variations
* Fix: Four-digit rating counts weren't displayed correctly
* Fix: Warning: Invalid argument supplied for foreach() in .../includes/aawp/admin-actions.php on line 26

= Version 3.1.5 (5rd June 2016) =
* Tweak: Optimized advertised price for Amazon (Prime) Videos
* Fix: Prime logo didn't show up

= Version 3.1.4 (3rd June 2016) =
* Tweak: Optimized description and pricing for downloadable movies/series
* Fix: Empty cache didn't delete all transients
* Fix: Star rating wasn't displayed correctly in a certain case
* Fix: Cache didn't refresh correctly when updating filter_items value
* Fix: Filtering didn't work as expected in a certain case

= Version 3.1.3 (29th May 2016) =
* New: Added WP filter for replacing the "no products found" message
* Tweak: Added click tracking to fields
* Tweak: Prevent click tracking for detail button
* Tweak: Gallery image (when available) will be the fallback when thumbnail is not available too
* Fix: Filtering returned wrong amount of items if "filter" attribute was missing

= Version 3.1.2 (26th May 2016) =
* New: Added affiliate link click tracking which can be enabled via settings. Currently supporting Google Analytics & Piwik
* New: Added Amazon logo for link shortcode which can be selected via settings and/or shortcode
* New: Added "star_rating" shortcode attribute accepting the value "none" for hiding the star rating
* New: Added "reviews" shortcode attribute accepting the value "none" for hiding the star rating
* New: Added field values "asin", "ean" and "isbn"
* Tweak: Optimized cache handling when using third party caching plugins and object cache
* Tweak: Enhanced filter functionality, added opportunities by filtering prices and checking multiple strings for titles
* Fix: Error message: Could not resolve host: webservices.amazon.de
* Fix: PHP implode warning: class.aawp-api.php
* Fix: eBooks with costs accidentally showed 0.00 EUR as price
* Fix: Remove double quotes from html titel/alt tags which broke up the markup
* Fix: Affiliate links table on settings page broke layout on small window sizes using Chrome browser
* !! IMPORTANT: Please update your custom templates !!

= Version 3.1.1 (28th April 2016) =
* Fix: White page issue on several hosters after the last update
* Fix: Missing button preview text after plugin activation
* Fix: PHP Notice "Undefined index: status"
* Fix: PHP Notice "Undefined property: $items_amount"

= Version 3.1.0 (28th April 2016) =
* New: Added "Amazon.com.mx" as new country
* New: Added useful links regarding the Amazon API setup to the settings page
* New: Added filter functionality for lists
* Tweak: Shortcode "aawp" now available by default, "amazon" can be added/used as before
* Tweak: Added CSS class for "not available" price label
* Tweak: Optimized star rating and reviews options handling
* Tweak: Optimized Amazon API credentials validation
* Fix: Amazon.it API credentials validation issue
* Fix: Product boxes in some cases linked to the same page instead of the Amazon detail page

= Version 3.0.06 (16th April 2016) =
* Tweak: Optimized API reviews handling
* Fix: "Unsupported operand types" issue

= Version 3.0.05 (10th April 2016) =
* New: Added field value "editorial_review" for displaying an extended description
* Tweak: Optimized responsive box styles
* Tweak: Rebuild and optimized price reduction handling
* Tweak: AMP now includes custom styles as well
* Fix: AMP important declarations removal didn't work
* Fix: PHP Warning include() http:// wrapper is disabled in the server configuration
* Fix: FOLLOW_LOCATION issue

= Version 3.0.04 (28th March 2016) =
* New: Added "button" shortcode attribute accepting the value "none" for hiding the default amazon buy button
* New: Added "link_text" shortcode attribute in order to set a custom, or hide (value "none") the default text for link shortcodes
* New: Added "link_overwrite" shortcode attribute for orverwriting the default product url
* Tweak: Optimized button styles in order to prevent text decoration underline
* Fix: eBooks pricing accidently showed a "not available" note. Pricing temporarily hidden. eBook price crawling will be implemented very soon!
* Fix: The recently added AMP important filter didn't work

= Version 3.0.03 (24th March 2016) =
* New: Multiple boxes, bestseller and new releases lists results can now be reordered
* New: Added "orderby" shortcode attribute accepting the following values: title, price, amount_saved, percentage_saved, rating
* New: Added "order" shortcode attribute accepting the following values: ASC, DESC. Default value is DESC
* New: Added "description_text" attribute for adding extra text as a paragraph right under the description items via shortcode
* New: Added settings option which allows you to select the currency format (EUR or €). Available only for EURO countries
* New: Added settings option which allows you to enable AMP support (please reactivate if you prefer to use it)
* New: Added "box_table" template in order to display multiple boxes inside a table (according to the bestseller template)
* Tweak: Optimized affiliate links settings and set long api urls as default (switch back to short ones if you prefer)
* Tweak: Removed !important declarations from AMP styles
* Fix: Rating counts over 999 were wrongly displayed as a three-digit number
* Fix: "array_merge(): Argument #2 is not an array..." warning

= Version 3.0.02 (9th March 2016) =
* New: Added field value "prime" for displaying the prime/premium logo if available for the related product
* New: Added "image" attribute for overwriting (custom url) or selecting one of the other product images (max of 5) via shortcode: e.g. image="2"
* New: Added "aawp_button_detail" shortcode which accepting the attributes link, text, target, title, style (similar to the field "button_detail")
* New: Added AAWP settings link to the plugins overview list
* Tweak: Optimized advertised price handling for products with variations
* Added missing and updated existing translations on the plugin settings

= Version 3.0.01 (7th March 2016) =
* Fix: Amazon API couldn't establish a connection
* Fix: "simple_html_dom.php" not found

= Version 3.0.0 (6th March 2016) =
* New: Added "connect", "disconnect" and "reconnect" buttons to the API settings page
* New: Added support for Accelerated Mobile Pages (AMP) when using official WordPress AMP plugin: https://wordpress.org/plugins/amp/
* New: Added short affiliate urls
* New: Added settings option which allows you to force long affiliate urls
* New: Added "image_link" attribute for overwriting or removing (use "none") product thumbnail links via shortcode
* New: Added settings option which allows you apply the title adding on thumbnail link titles as well
* New: Added settings option which allows you display an Amazon Prime logo besides the pricing whenever Prime is available for the related product (not available for all countries)
* New: Earn extra commissions with the newly added Amazon Prime logo whenever a user clicks the logo and signs up for the free trial (not available for all countries)
* New: Added "tracking_id" attribute for overwriting the used Amazon affiliate tracking id via shortcode (doesn't work for long affiliate urls)
* New: Added Spanish translations (thanks Hendrik!)
* New: Added PHP 7 support
* Tweak: Better product description building for items of the following categories: Shoes, AudioCDs, Movies and Prime Videos
* Tweak: Products which are out of stock now show a note instead of an empty pricing
* Tweak: Products which are only available for "used" condition now show a note instead of an empty pricing
* Tweak: Enhanced plugin settings "Support" tab in order to give better (and visual) feedback about your current system and possibly missing PHP extensions
* Tweak: Enhanced plugin settings "Amazon API" in order to give visual feedback whenever the API is not connected
* Tweak: Updated plugin settings "tracking id" translations and added description in order to avoid unclarity
* Fix: Occasionally occurred missing product images
* Fix: Connection issues result in "Amazon Product Advertising API is currently not available" notice
* Fix: Removed some error notices coming from the API library
* Fix: Removed API routine checks to prevent connection issues
* Fix: Accidentally the product title adding was shown on image alt tags
* Replaced Amazon API library
* Removed support for PHP versions older than 5.3
* Renamed plugin folder and main file
* PHP templates files were updated and might require your custom templates to be adjusted!

= Version 2.1.05 (17th January 2016) =
* New: Added field value "rating_count" in order to show the total amount of reviews
* New: Bestseller Table template now supports hiding advertised price as well
* Tweak: Hide price reduction when advertised price is hidden
* Tweak: Added paragraph around "no products found" message
* Tweak: Optimized box heights and reduced empty spaces
* Fix: Added exception handling when Amazon Product Advertising API is not available

= Version 2.1.04 (10th January 2016) =
* New: Advertised price can be hidden via plugin settings (templates were updated!)
* Tweak: Moved responsive table note above the related table
* Fix: Custom styles were not executed when using only a widget
* Fix: Setting description items 0 via plugin settings was not possible
* Updated plugin updater class

= Version 2.1.03 (29th December 2015) =
* Fix: "Call to a member function find() on null" issue when products don't have reviews
* Tweak: Better image handling for API results
* Tweak: Added a swipe not under bestseller tables on mobile devices

= Version 2.1.02 (20th December 2015) =
* New: Added "title_length" attribute for overwriting the title length via shortcode
* New: Added "description_length" attribute for overwriting the length of each description list item via shortcode
* New: Added "button_detail_title" attribute for overwriting the detail button link title via shortcode
* New: Star ratings can be hidden via plugin settings
* New: Added reset plugin database option on "support" settings tab (this action is not reversible!)
* Tweak: Removed bottom margin for last box when using widgets
* Tweak: Remove line breaks and empty paragraphs from shortcode output
* Tweak: Renamed button classes to prevent conflicts with other plugins/theme styles or icon fonts
* Fow now re-added global style include to avoid issues with caching/minify/autoptimize plugins
* Re-added API support for India and Brazil
* Optimized box styles e.g. buttons margins and info box max-width
* Added missing translations for plugin settings

= Version 2.1.01 (15th December 2015) =
* Fix: Issue with frontend style loading for fields, tablepress and pagebuilder

= Version 2.1.0 (13th December 2015) =
* New: Added field value "list_price"
* New: Added "link_icon" attribute for overwriting the link icon via shortcode
* New: Added "button_text" attribute for overwriting the default button text via shortcode

* Optimized and scaled down widget styles
* Comeback of the list price and reduction for widgets
* Moved box pricing back above the buttons and optimized styling in order to fix the overlapping issues
* Optimized general layout styles to prevent issues with theme styling
* When using field values "price" & "list_price" and no price is available, a note will be returned

* Fix: Disclaimer text at the bottom of the page didn't show up
* Fix: Custom CSS was not executed
* Fix: Issue when multiple box items contain at least one wrong id
* Fix: Placeholder image url was broken
* Fix: Issue when bestseller items couldn't be fetched due to environment problems

= Version 2.0.02 (7th December 2015) =
* Fix: Bestseller search lead to results from a wrong category

= Version 2.0.01 (7th December 2015) =
* Fix: White page after plugin update/activation (occured only on webhostings with PHP < v5.3.0)
* Fix: Undefined infobox function call
* Fix: Missing button default German translation

= Version 2.0.0 (6th December 2015) =
* Complete rebuild, please take a look onto our website for the changelog

= Version 1.9.4 (29th November 2015) =
* Update 2.0 pre-release fixes and improvements

= Version 1.9.3 (22th November 2015) =
* New: Hide description by using desciption_items="none" or description="none" (box only)
* New: Added link_title attribute to set a custom link attribute
* Fix: Link icon now separated from anchor element to avoid whitespace underlines
* Added plugin 2.0 rebuild upgrade handler
* Added infobox on settings page

= Version 1.9.2 (19th November 2015) =
* Tremendous performance optimization
* New: Added inline info textfield to provide additional information within the product box
* New: Added field values: link, button, button_detail
* New: Added widget templates
* Field value thumb now includes a link
* Optimized widget & button styles
* Moved cart icon for buttons from img to css handling
* Details button shortcode attribute is now "button_detail" instead of "button"
* Added routine dependency checks

= Version 1.9.1 (15th November 2015) =
* Beta fixes

= Version 1.7.3 (15th November 2015) =
* Fixed some notices for empty results

= Version 1.7.2 (1st November 2015) =
* Fix: In some cases the list price was shown instead of the sale price

= Version 1.7.1 (24th October 2015) =
* Fix: Prefixed left function to solve "plugin_settings_link" issue
* Added new version of the integrated plugin updater

= Version 1.7.0 (23th October 2015) =
* Fix: Activation issue when installed PHP version is older than 5.3.0
* New: Added French & Italian translations. Thanks for your support!
* Help translating on https://poeditor.com/join/project/z6ncOXjq9i

= Version 1.6.0 (10th October 2015) =
* Optimized styles
* Cleared unneeded and old MySQL handling
* Rebuild license server handling (AAWP only)

= Version 1.5.2 (26th September 2015) =
* Optimized bestseller count handling

= Version 1.5.1 (13th September 2015) =
* Optimized bestseller count handling

= Version 1.5.0 (13th September 2015) =
* Fix: Half-star ratings sometimes were broken
* Reduced default bestseller entry count from 25 to 10. Max limit is still 25.
* Added SOAP check and notice if extension is missing

= Version 1.4.2 (7th September 2015) =
* Cleaned up settings code

= Version 1.4.1 (7th September 2015) =
* Fix: Description - especially books - sometimes showed "Array"

= Version 1.4.0 (28th August 2015) =
* Updated license server
* Optimized license activation error handling

= Version 1.3.0 (21th August 2015) =
* Rebuild plugin updater and license handler
* Added 'Add to cart' icon alt tag
* Fix: PHP notices for missing product attributes

= Version 1.2.5 (7th August 2015) =
* Optimized widget styles

= Version 1.2.4 (7th August 2015) =
* Optimized widget styles

= Version 1.2.3 (7th August 2015) =
* Optimized update checker implementation
* Added style support when placing shortcodes to widgets

= Version 1.2.2 (15th July 2015) =
* Optimized plugin update checker handling
* Included new plugin update checker v2.1 library

= Version 1.2.1 (15th June 2015) =
* Prefixed included Amazon library to avoid foreign plugin conflicts

= Version 1.2.0 (24th May 2015) =
* Optimized style implementation
* Rebuild admin menu
* Enhanced shortcode
* New: Templating
* New: Clear cache after entering a new tracking id

= Version 1.1.2 (12th May 2015) =
* Updated shortcode registration
* Optimized code and fixed PHP notices

= Version 1.1.1 (11th May 2015) =
* Rebuild caching including a tremendous performance optimization
* Cleaned up plugin assets

= Version 1.1.0 (3rd May 2015) =
* New: Added the first new style "Compact"
* New: Select a style on settings page and shortcode
* New: Set the number of description items on settings page and shortcode
* Rebuild description functionality
* Fix: Book description will show up correctly now
* Fix: Removed broken disclaimer links
* Moved Disclaimer to the end of a page/post and show only once

= Version 1.0.8 (1st May 2015) =
* New: Added link to product thumbnails

= Version 1.0.7 (18th April 2015) =
* New: Added disclaimer option
* Sidebar settings menu name
* Default caching time increased to 1 day
* Redesigned and optimized settings page

= Version 1.0.6 (7th April 2015) =
* New: Added Amazon India and Brazil
* Updated settings page

= Version 1.0.5 (3rd April 2015) =
* New: Display error message when disconnected

= Version 1.0.4 (29th March 2015) =
* Fix: "No products found" problem
* Improved caching functionality

= Version 1.0.3 (27th March 2015) =
* New: Added automatic updates
* New: Added star ratings
* Fix: Configuration page link
* Smaller improvements and fixes

= Version 1.0 (20th March 2015) =
* Initial release