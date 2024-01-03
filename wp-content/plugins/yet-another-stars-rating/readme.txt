=== Yasr - Yet Another Stars Rating ===
Donate link: https://www.paypal.com/donate/?hosted_button_id=SVTAVUF62QZ4W
Tags: rating, rate post, star rating, google rating, block
Requires at least: 4.7
Contributors: Dudo, paretodigital
Tested up to: 6.3
Stable tag: 3.4.5
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost the way people interact with your site with an easy WordPress stars rating system! With schema.org rich snippets YASR will improve your SEO

== Description ==
Improving the user experience with your website is a top priority for everyone who cares about their online activity,
as it promotes familiarity and loyalty with your brand, and enhances visibility of your activity.

Yasr - Yet Another Stars Rating is a powerful way to add SEO-friendly user-generated reviews and testimonials to your
website posts, pages and CPT, without affecting its speed.

== How To use ==

== Reviewer Vote ==
With the classic editor, when you create or update a page or a post, a box (metabox) will be available in the upper right corner where you'll
be able to insert the overall rating.
With the new Gutenberg editor, just click on the "+" icon to add a block and search for Yasr Overall Rating.
You can either place the overall rating automatically at the beginning or the end of a post (look in "Settings"
-> "Yet Another Stars Rating: Settings"), or wherever you want in the page using the shortcode [yasr_overall_rating] (easily added through the visual editor).

== Visitor Votes ==
You can give your users the ability to vote, pasting the shortcode [yasr_visitor_votes] where you want the stars to appear.
If you're using the new Gutenberg editor, just click on the "+" icon to add a block and search for Yasr Visitor Votes
Again, this can be placed automatically at the beginning or the end of each post; the option is in "Settings" -> "Yet Another Stars Rating: Settings".

== Multi Set ==
Multisets give the opportunity to score different aspects for each review: for example, if you're reviewing a videogame, you can create the aspects "Graphics", "Gameplay", "Story", etc.

== Migration tools ==
You can easily migrate from *WP-PostRatings*, *kk Star Ratings*, *Rate My Post* and *Multi Rating*
A tab will appear in the settings if one of these plugin is detected.

== Supported itemtypes ==
YASR supports the following schema.org itemtypes:

BlogPosting ✝,
Book ¶,
Course,
CreativeWorkSeason,
CreativeWorkSeries,
Episode,
Event,
Game,
LocalBusiness ‡,
MediaObject,
Movie Δ,
MusicPlaylist,
MusicRecording,
Organization,
Product §,
Recipe ||,
SoftwareApplication ◊

✝ BlogPosting itemtype will not show stars in search result.
More info [here](https://wordpress.org/plugins/yet-another-stars-rating/faq/)

¶ Book supports the following properties
* author
* bookEdition
* BookFormat
* ISBN
* numberOfPages

‡ LocalBusiness supports the following properties
* Address
* PriceRange
* Telephone

Δ Movie supports the following properties
* actor
* director
* Duration
* dateCreated

§ Products supports the following properties
* Brand
* Sku
* Global identifiers
* Price
* Currency
* Price Valid Until
* Availability
* Url

|| Recipe supports the following properties
* cookTime
* prepTime
* description
* keywords
* nutrition
* recipeCategory
* recipeCuisine
* recipeIngredient

◊ SoftwareApplication supports the following properties
* applicationCategory
* operatingSystem
* Currency
* Price Valid Until
* Availability
* Url

== Video Tutorial ==

= Old video, but still valid. =
[youtube https://www.youtube.com/watch?v=M47xsJMQJ1E]

= New videos, talked in Italian but easily to understand (eng subs will come) =
[Tutorial's playlist](https://www.youtube.com/playlist?list=PLFErQFOLUVMcx8Qb9--KKme3bQ_KGri71)

== Developers ==
While YASR - Yet Another Stars Rating does not require any coding, it is developer friendly!
It is the first (and for now only) rating plugin that uses REST API.
[Here](https://documenter.getpostman.com/view/12873985/TzJycbVz) you can find the documentation.
Further, it comes with a lot of hooks, you can find more info [here](https://github.com/Dudo1985/Yet-Another-Stars-Rating/tree/master/docs) .

== GitHub ==
* [Follow development on GitHub](https://github.com/Dudo1985/yet-another-stars-rating)

== Related Link ==
* [All available shortcodes in free version](https://yetanotherstarsrating.com/yasr-shortcodes/)
* Documentation at [Yasr Official Site](https://yetanotherstarsrating.com/docs/)

Do you want more feature? [Check out Yasr Pro!](https://yetanotherstarsrating.com/#yasr-pro)

== Press ==
[Tutorial by Qode Magazine](https://qodeinteractive.com/magazine/how-to-add-post-rating-to-your-wordpress-website/)
[Tutorial by Greengeeks](https://www.greengeeks.com/tutorials/star-rating-system-yasr-wordpress/)

== Installation ==
1. Navigate to Dashboard -> Plugins -> Add New and search for YASR
2. Click on "Install Now" and then "Activate"

== Frequently Asked Questions ==

= What is "Overall Rating"? =
It is the vote given by who writes the review: readers are able to see this vote in read-only mode. Reviewer can vote using the box on the top right in the editor screen. Remember to insert this shortcode **[yasr_overall_rating]** to make it appear where you like.

= What is "Visitor Rating"? =
It is the vote that allows your visitors to vote: just paste this shortcode **[yasr_visitor_votes]** where you want the stars to appear.

[Demo page for Overall Rating and Vistor Rating](https://yetanotherstarsrating.com/yasr-shortcodes/)

= What is "Multi Set"? =
It is the feature that makes YASR awesome. Multisets give the opportunity to score different aspects for each review: for example, if you're reviewing a videogame, you can create the aspects "Graphics", "Gameplay", "Story", etc. and give a vote for each one. To create a set, just go in "Settings" -> "Yet Another Stars Rating: Settings" and click on the "Multi Sets" tab. To insert it into a post, just paste the shortcode that YASR will create for you.

[Demo page for Multi Sets](https://yetanotherstarsrating.com/yasr-shortcodes/#yasr-multiset-shortcodes)

= What is "Ranking reviews" ? =
It is the 10 highest rated item ranking by reviewer. In order to insert it into a post or page, just paste this shortcode **[yasr_ov_ranking]**

= Wht is "Users' ranking" ? =
This is 2 charts in 1. Infact, this chart shows both the most rated posts/pages or the highest rated posts/pages.
For an item to appear in this chart, it has to be rated twice at least.
Paste this shortcode to make it appear where you want **[yasr_most_or_highest_rated_posts]**

= What is "Most active reviewers" ? =
If in your site there are more than 1 person writing reviews, this chart will show the 5 most active reviewers. Shortcode is **[yasr_top_reviewers]**

= What is "Most active users" ? =
When a visitor (logged in or not) rates a post/page, his rating is stored in the database. This chart will show the 10 most active users, displaying the login name if logged in or "Anonymous" otherwise. The shortcode : **[yasr_most_active_users]**

[Demo page for Rankings](https://yetanotherstarsrating.com/yasr-shortcodes/#yasr-rankings-shortcodes)

= Wait, wait! Do I need to keep in mind all this shortcode? =
If you're using the new Gutenberg editor, you don't need at all: just use the blocks.
If, instead, you're using the classic editor, in the visual tab just click the "Yasr Shortcode" button above the editor

= Does it work with caching plugins? =
Since version 2.3.0 YASR works with *every caching plugin available out there*.
In the settings, just select "yes" to "Load results with AJAX".
YASR has been tested with:
* Wp Super Cache
* LiteSpeed Cache
* Wp Fastest Cache
* WP-Optimize
* Cache Enabler
* Hyper Cache
* Wp Rocket

= Why I don't see stars in google? =
[Read here](https://yetanotherstarsrating.com/yasr-rich-snippets/) and find out how to set up rich snippets.
You can use the [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool/u/0/) to validate your pages.
Also [read this](https://webmasters.googleblog.com/2019/09/making-review-rich-results-more-helpful.html) google announcement.
If you set up everything fine, in 99% of cases your stars will appear in a week.
If doesn't, you should work on your seo reputation.

= Does it work with PHP 8? =
Yes, YASR is 100% fully compatible with PHP 8

== Screenshots ==
1. Example of Yasr Overall Rating and Yasr Visitor Votes shortcodes
2. Yasr Multi Set
3. User's ranking showing most rated posts
4. User's ranking showing the highest rated posts
5. Ranking reviews

== Changelog ==

The full changelog can be found in the plugin's directory. Recent entries:

= 3.4.5 =
* FIXED: This version fixes a warning that randomly appears on the settings page.

= 3.4.4 =
* TWEAKED: since this version, for non-logged-in users, or users that can't edit posts, it is no longer possible to vote for a post that do not exist or is marked as private.
If, for some reason, you need to do this, just add [this code](https://gist.github.com/Dudo1985/9105ee335f6104cc4ce4ea392416678c) into your functions.php file

= 3.4.3 =
* TWEAKED: The same mechanisms to prevent spam ratings for yasr_visitor_votes now also work for yasr_visitor_multiset.

= 3.4.2 =
* Minor Changes
* Updated Freemius SDK

= 3.4.1 =
* FIXED: in the Dashboard, the "Recent Ratings" widget did not update the IP upon page change
* TWEAKED: "Custom text to display when rating is saved" is now also returned on yasr_visitor_multiset
* TWEAKED: Minor changes in "Aspect & Styles" tab
* TWEAKED: updated freemius SDK to version 2.5.9

= 3.4.0 =
* FIXED: missing "description" field in rich snippets
* TWEAKED: updated freemius sdk to version 2.5.8
* minor changes


= 3.3.9 =
* TWEAKED: to prevent spam rating, now IP is saved by default. This is ok for the GDPR law, you can find more info into
General Settings -> Advanced Settings
* TWEAKED: if for some reason the rich snippets are not returned into the post content, YASR will insert it into the footer.
More info https://wordpress.org/support/topic/please-add-json-structured-data-output-to-wp_head/ and
https://wordpress.org/support/topic/structured-data-not-showing/
* FIXED: conflict with the plugin Optimize Press
* FIXED: in admin dashboard, a warning is returned in "Recent Ratings" widget in some circumstances

= 3.3.8 =
* FIXED: Impossible to rate in a multi set in the editor screen

= 3.3.7 =
* FIXED: Structured data options -> image url didn't get saved
* TWEAKED: added filters yasr_filter_itemtypes and yasr_filter_itemtypes_fields to add new rich snippet itemTypes
* FIXED: freemius translation didn't work.


= 3.3.6 =
* FIXED: Dashboard -> recent ratings widget didn't work on version 3.3.5

= 3.3.5 =
* TWEAKED: [yasr_user_rate_history] ajax response now returns json instead of HTML
* TWEAKED: recent ratings widget in Admin Dashboard now returns json instead of HTML
* TWEAKED: updated Freemius SDK to version 2.5.6
* TWEAKED: hooks documentation is generated by [WPDocGen](https://github.com/Dudo1985/WPDocGen)

= 3.3.4 =
* TWEAKED: updated Freemius SDK to version 2.5.5
* TWEAKED: minor changes

= 3.3.3 =
* TWEAKED: in Yet Another Stars Rating -> Manage Ratings, all the data is now fetched ~x3 time faster
* TWEAKED: added support for WP-CLI
* TWEAKED: updated Freemius SDK to version 2.5.4


= 3.3.2 =
* FIXED: "new criteria" button didn't work in Settings -> "Multi Criteria" tab

= 3.3.1 =
* FIXED:   in old YASR installations (first install < 2.0.9) new multi sets didn't get saved
* TWEAKED: minor changes



= Additional Info =
See credits.txt file
