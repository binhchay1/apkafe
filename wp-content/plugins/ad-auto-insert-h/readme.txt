=== Ad Auto Insert H ===
Contributors: tabibitojin
Donate link: https://paypal.me/tabibitojin
Tags: google adsense, ad auto inserter, before headline, ads lazyload , Google Analytics, Header insert, グーグルアドセンス, 広告自動挿入, 見出し前, 遅延読込み, グーグルアナリティスク, ヘッダ挿入
Requires at least: 5.0
Tested up to: 6.1
Stable tag: 1.4.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically inserts Google AdSense ad codes before H tags, before the first H tag and at the end of a post or a page. Lazy Load of ads to speed up page display is available. Insertion of AdSense Auto Ads code and Google Analytics code can be set as well.

== Description ==

This plugin automatically extracts H tags (headlines) in the article and automatically inserts Google Adsense ad codes before the first H tag, before H tags and at the end of the article.

You can specify the number of characters between ads and automatically insert the ad codes at just the right interval. This plugin can be used easily by a wide range of users, from beginners to advanced users.
In addition, article display can be speeded up by lazy loading of ad codes, and the Adsense auto ad code and Google Analytics code can be inserted into the header as well.

If you already put ad codes using short codes into articles, you can easily replace them with specifying short codes of this plugin.

[Home](https://tabibitojin.com/) | [Documentation](https://tabibitojin.com/ad-auto-insert-h/)

== Features ==

* Automatically inserts an ad code before the first headline (H tag) in the article.
* Automatically inserts ad codes before headlines (H tags) in the article.
* Automatically inserts an ad code at the bottom of the article
* You can choose to insert ads into posts, pages, or both of them.
* You can specify the upper limit of the number of ads in the article to insert automatically.
* You can specify headlines to insert ads before them, only H2 tag or all H tags (H2-H6).
* You can specify the interval between automatically inserted ads by the number of characters (eg, insert an ad when it exceeds 1000 characters between ads.)
* Multiple ad codes can be set, and you can select which ad code to insert before the first headline, before headlines in the article, or at the end of the article.
* Google AdSense display ad, in-article ad, and multiplex ads can be selected.
* Top and bottom margins of ads, centering ads and labels above the ads also can be set.
* Ad codes can be manually inserted into articles using shortcodes
* If you have already placed an ad using shortcodes, this plugin can replace them with ads set in the plugin automatically.
* Lazy loading of Adsense ad codes is also available.
* About lazy loading, desktop display can be disabled ( or enable only for mobile and tablet ).
* About lazy loading, you can specify the number of seconds until loading automatically.
* You can insert the AdSense auto ad code into the header automatically by its setting.
* About Adsense auto ad, you can specify to exclude posts or pages.
* Google Analytics code can be insert automatically into the header as well.
* Access control can be used: automatically inserts ad cords only for users with administrator role.
* You can also customize the settings for an article at the bottom of the edit screen.

Check the following pages for more details.
[Official page](https://tabibitojin.com/ad-auto-insert-h/)

== Frequently Asked Questions ==

= Which language is supported ? =
Currently Japanese and English.
In the Japanese environment, it is automatically displayed in Japanese.

If you translated to other languages, let me know. I'll include them.

= Which ads of Google AdSense are supported ? =
It supports display ad, in-article ad, multiplex ads and AdSense auto ad.

= Are other ad codes supported except for Google Adsense ? =
It supports AdSense ad unit code and Adsense auto ad.

= Are there any cases in which the space between ads is too close when ads are automatically inserted before headlines in the article ? =
You can specify the space between ads as number of characters so that the ads aren't too close each other. (ex 1000 characters)

= Can the code used when applying for Google AdSense be inserted ? =
I think it is the code of Adsense auto ad, and it can be easily inserted into the headers automatically by setting.

= Does it support lazy loading ? =
Lazy loading of ads is available.
You can also set seconds to automatically load ads when users do nothing.

== Screenshots ==

1. Plugin setting screen (general setting): General settings such as selections of ad codes and ad display target. Basically this plugin can work once this settings are done.
2. Plugin setting screen (general setting): Adsense ad code setting part in the general setting.
3. Plugin setting screen (advanced setting): Settings used when you want to do more detailed settings.
4. Plugin setting screen (option): Adsense ads lazy loading and Adsense auto ad settings.
5. Plugin setting screen (optional): Google Analytics code insertion settings.
6. Plugin setting screen (Language, etc.): Language, Access control and debug mode settings. Access control works only for logged-in users, and ad codes are inserted only to users with administrator role. The debug mode is to show the plugin information and let you check ad insertions visually when articles are previewed.
7. Article individual setting: Unique settings for each articles in the edit screen.


== Changelog ==
= 1.4.0 =
Add Access control setting.
( While logging, automatically inserts ad cords only for users with administrator role.)

= 1.3.0 =
Fixed warnings when tags are included in H tags.
Add unnecessary processing skip when all settings for automatic ad insertion are off.

= 1.2.3 =
Corrected the appearance in the header of the Adsense code (no functional correction)

= 1.2.2 =
Deleted parts contained unnecessary characters


== Upgrade Notice ==
= 1.3.0 =
Fixed warning

= 1.2.3 =
Fixed the appearance on the source code (no functional correction)

= 1.2.2 =
string bug fix
