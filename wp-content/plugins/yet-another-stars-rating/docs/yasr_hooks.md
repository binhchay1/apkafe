
 ### `do_action('yasr_add_admin_scripts_begin')` 

 Source: [../admin/classes/YasrAdmin.php, line 170](../.././admin/classes/YasrAdmin.php:170)

_Add custom script in one of the page used by YASR, at the beginning_

|Argument | Type | Description |
| --- | --- | --- |
|$hook | string |  |
___
 ### `do_action('yasr_add_admin_scripts_end')` 

 Source: [../admin/classes/YasrAdmin.php, line 187](../.././admin/classes/YasrAdmin.php:187)

_Add custom script in one of the page used by YASR, at the end_

|Argument | Type | Description |
| --- | --- | --- |
|$hook | string |  |
___

 ### `do_action('yasr_add_tabs_on_tinypopupform')` 

 Source: [../admin/editor/YasrEditorHooks.php, line 218](../.././admin/editor/YasrEditorHooks.php:218)

_Use this action to add tabs inside shortcode creator for tinymce_

___
 ### `do_action('yasr_add_content_on_tinypopupform')` 

 Source: [../admin/editor/YasrEditorHooks.php, line 234](../.././admin/editor/YasrEditorHooks.php:234)

_Use this action to add content inside shortcode creator_

|Argument | Type | Description |
| --- | --- | --- |
|$n_multi_set | int |  |
|$multi_set | string |  the multiset name |
___

 ### `do_action('yasr_metabox_below_editor_add_tab')` 

 Source: [../admin/editor/YasrMetaboxBelowEditor.php, line 60](../.././admin/editor/YasrMetaboxBelowEditor.php:60)

_Use this hook to add new tabs into the metabox below the editor_

___
 ### `do_action('yasr_metabox_below_editor_content')` 

 Source: [../admin/editor/YasrMetaboxBelowEditor.php, line 66](../.././admin/editor/YasrMetaboxBelowEditor.php:66)
___
 ### `do_action('yasr_add_content_multiset_tab_top')` 

 Source: [../admin/editor/YasrMetaboxBelowEditor.php, line 210](../.././admin/editor/YasrMetaboxBelowEditor.php:210)

_Hook here to add new content at the beginning of the div_

|Argument | Type | Description |
| --- | --- | --- |
|$post_id | int |  |
|$set_id | int |  |
___
 ### `do_action('yasr_add_content_multiset_tab_pro')` 

 Source: [../admin/editor/YasrMetaboxBelowEditor.php, line 292](../.././admin/editor/YasrMetaboxBelowEditor.php:292)

_Hook here to add new content_

|Argument | Type | Description |
| --- | --- | --- |
|$post_id | int |  |
|$set_id | int |  |
___

 ### `do_action('yasr_on_save_post')` 

 Source: [../admin/editor/YasrOnSavePost.php, line 61](../.././admin/editor/YasrOnSavePost.php:61)

_Hook here to add actions when YASR save data on save_post_

|Argument | Type | Description |
| --- | --- | --- |
|$post_id | int |  |
___
 ### `do_action('yasr_action_on_overall_rating')` 

 Source: [../admin/editor/YasrOnSavePost.php, line 106](../.././admin/editor/YasrOnSavePost.php:106)

_Do action before overall rating is saved, works only in classic editor_

|Argument | Type | Description |
| --- | --- | --- |
|$post_id | int |  |
|$rating | float |  |
___

 ### `do_action('yasr_add_content_bottom_topright_metabox')` 

 Source: [../admin/editor/yasr-metabox-top-right.php, line 121](../.././admin/editor/yasr-metabox-top-right.php:121)

_Hook here to add content at the bottom of the metabox_

|Argument | Type | Description |
| --- | --- | --- |
|$post_id | int |  |
___

 ### `do_action('yasr_add_settings_tab')` 

 Source: [../admin/settings/classes/YasrSettings.php, line 255](../.././admin/settings/classes/YasrSettings.php:255)

_Hook here to add new settings tab_

___
 ### `do_action('yasr_settings_tab_content')` 

 Source: [../admin/settings/classes/YasrSettings.php, line 330](../.././admin/settings/classes/YasrSettings.php:330)

_Hook here to add new settings tab content_

___

 ### `apply_filters('yasr_setting_page_footer')` 

 Source: [../admin/settings/classes/YasrSettingsFooter.php, line 135](../.././admin/settings/classes/YasrSettingsFooter.php:135)

_Filter to customize the footer page on the "Aspect & style" page._

_Since this could contain js, this will only allow FALSE as value_

|Argument | Type | Description |
| --- | --- | --- |
|$style_page_upgrade_pro_js | string |  |
___

 ### `apply_filters('yasr_settings_select_ranking')` 

 Source: [../admin/settings/classes/YasrSettingsRankings.php, line 60](../.././admin/settings/classes/YasrSettingsRankings.php:60)
___

 ### `do_action('yasr_right_settings_panel_box')` 

 Source: [../admin/settings/classes/YasrSettingsRightColumn.php, line 15](../.././admin/settings/classes/YasrSettingsRightColumn.php:15)
___

 ### `apply_filters('yasr_filter_style_options')` 

 Source: [../admin/settings/classes/YasrSettingsStyle.php, line 37](../.././admin/settings/classes/YasrSettingsStyle.php:37)
___
 ### `do_action('yasr_style_options_add_settings_field')` 

 Source: [../admin/settings/classes/YasrSettingsStyle.php, line 48](../.././admin/settings/classes/YasrSettingsStyle.php:48)
___
 ### `apply_filters('yasr_sanitize_style_options')` 

 Source: [../admin/settings/classes/YasrSettingsStyle.php, line 462](../.././admin/settings/classes/YasrSettingsStyle.php:462)
___

 ### `do_action('yasr_add_stats_tab')` 

 Source: [../admin/settings/classes/YasrStats.php, line 56](../.././admin/settings/classes/YasrStats.php:56)

_Use this hook to add a tab into yasr_stats_page_

___

 ### `apply_filters('yasr_export_box_button')` 

 Source: [../admin/settings/classes/YasrStatsExport.php, line 144](../.././admin/settings/classes/YasrStatsExport.php:144)

_Use this hook to customize the button_

___
 ### `do_action('yasr_export_box_end')` 

 Source: [../admin/settings/classes/YasrStatsExport.php, line 160](../.././admin/settings/classes/YasrStatsExport.php:160)

_Hook here to do an action at the end of the box_

___

 ### `do_action('yasr_migration_page_bottom')` 

 Source: [../admin/settings/yasr-settings-migration.php, line 63](../.././admin/settings/yasr-settings-migration.php:63)
___

 ### `do_action('yasr_stats_tab_content')` 

 Source: [../admin/settings/yasr-stats-page.php, line 54](../.././admin/settings/yasr-stats-page.php:54)

_Hook here to add new stats tab content_

___

 ### `apply_filters('yasr_feature_locked')` 

 Source: [../admin/yasr-admin-init.php, line 33](../.././admin/yasr-admin-init.php:33)
___
 ### `apply_filters('yasr_feature_locked_html_attribute')` 

 Source: [../admin/yasr-admin-init.php, line 40](../.././admin/yasr-admin-init.php:40)
___
 ### `apply_filters('yasr_feature_locked_text')` 

 Source: [../admin/yasr-admin-init.php, line 56](../.././admin/yasr-admin-init.php:56)
___

 ### `apply_filters('yasr_rankings_query_ov')` 

 Source: [../includes/classes/YasrDB.php, line 421](../.././includes/classes/YasrDB.php:421)
___
 ### `apply_filters('yasr_rankings_query_vv')` 

 Source: [../includes/classes/YasrDB.php, line 465](../.././includes/classes/YasrDB.php:465)
___
 ### `apply_filters('yasr_rankings_query_tu')` 

 Source: [../includes/classes/YasrDB.php, line 515](../.././includes/classes/YasrDB.php:515)
___
 ### `apply_filters('yasr_rankings_multi_query')` 

 Source: [../includes/classes/YasrDB.php, line 560](../.././includes/classes/YasrDB.php:560)
___
 ### `apply_filters('yasr_rankings_query_tr')` 

 Source: [../includes/classes/YasrDB.php, line 614](../.././includes/classes/YasrDB.php:614)
___
 ### `apply_filters('yasr_rankings_multivv_query')` 

 Source: [../includes/classes/YasrDB.php, line 665](../.././includes/classes/YasrDB.php:665)
___

 ### `apply_filters('yasr_user_rate_history_must_login_text')` 

 Source: [../includes/classes/YasrLastRatingsWidget.php, line 85](../.././includes/classes/YasrLastRatingsWidget.php:85)

_Hook here to customize the message "You must login to see this widget." when_

_the shortcode yasr_user_rate_history is used_

___

 ### `apply_filters('yasr_filter_itemtypes')` 

 Source: [../includes/classes/YasrRichSnippetsItemTypes.php, line 91](../.././includes/classes/YasrRichSnippetsItemTypes.php:91)

_Use this hook to add (or eventually remove) supported itemTypes_

|Argument | Type | Description |
| --- | --- | --- |
|$itemTypes | array |  an array containing all the default supported itemTypes |
___
 ### `apply_filters('yasr_filter_itemtypes_fields')` 

 Source: [../includes/classes/YasrRichSnippetsItemTypes.php, line 112](../.././includes/classes/YasrRichSnippetsItemTypes.php:112)

_Use this hook to add optional fields for an itemType_

_Here the array member must contain main itemType name_

_E.g. if you want to add the filed 'price' to 'SoftwareApplication, you need to add_

_yasr_softwareapplication_price_

|Argument | Type | Description |
| --- | --- | --- |
|$additionalFields | array |  an array containing all the default supported additional fields |
___

 ### `apply_filters('yasr_custom_loader')` 

 Source: [../includes/classes/YasrScriptsLoader.php, line 59](../.././includes/classes/YasrScriptsLoader.php:59)
___
 ### `apply_filters('yasr_custom_loader_url')` 

 Source: [../includes/classes/YasrScriptsLoader.php, line 63](../.././includes/classes/YasrScriptsLoader.php:63)
___
 ### `do_action('yasr_add_front_script_css')` 

 Source: [../includes/classes/YasrScriptsLoader.php, line 118](../.././includes/classes/YasrScriptsLoader.php:118)
___
 ### `do_action('yasr_add_front_script_js')` 

 Source: [../includes/classes/YasrScriptsLoader.php, line 127](../.././includes/classes/YasrScriptsLoader.php:127)
___
 ### `apply_filters('yasr_gutenberg_constants')` 

 Source: [../includes/classes/YasrScriptsLoader.php, line 525](../.././includes/classes/YasrScriptsLoader.php:525)
___

 ### `apply_filters('yasr_rest_rankings_args')` 

 Source: [../includes/rest/classes/YasrCustomEndpoint.php, line 146](../.././includes/rest/classes/YasrCustomEndpoint.php:146)
___
 ### `apply_filters('yasr_rest_sanitize')` 

 Source: [../includes/rest/classes/YasrCustomEndpoint.php, line 277](../.././includes/rest/classes/YasrCustomEndpoint.php:277)
___

 ### `apply_filters('yasr_tr_rankings_atts')` 

 Source: [../includes/shortcodes/classes/YasrNoStarsRankings.php, line 36](../.././includes/shortcodes/classes/YasrNoStarsRankings.php:36)
___
 ### `apply_filters('yasr_tu_rankings_atts')` 

 Source: [../includes/shortcodes/classes/YasrNoStarsRankings.php, line 63](../.././includes/shortcodes/classes/YasrNoStarsRankings.php:63)
___
 ### `apply_filters('yasr_tu_rankings_display')` 

 Source: [../includes/shortcodes/classes/YasrNoStarsRankings.php, line 124](../.././includes/shortcodes/classes/YasrNoStarsRankings.php:124)
___

 ### `apply_filters('yasr_overall_rating_shortcode')` 

 Source: [../includes/shortcodes/classes/YasrOverallRating.php, line 52](../.././includes/shortcodes/classes/YasrOverallRating.php:52)
___
 ### `apply_filters('yasr_cstm_text_before_overall')` 

 Source: [../includes/shortcodes/classes/YasrOverallRating.php, line 123](../.././includes/shortcodes/classes/YasrOverallRating.php:123)
___

 ### `apply_filters('yasr_ov_rankings_atts')` 

 Source: [../includes/shortcodes/classes/YasrRankings.php, line 54](../.././includes/shortcodes/classes/YasrRankings.php:54)
___
 ### `apply_filters('yasr_vv_rankings_atts')` 

 Source: [../includes/shortcodes/classes/YasrRankings.php, line 84](../.././includes/shortcodes/classes/YasrRankings.php:84)

_Hook here to use shortcode atts._

_If not used, will work with no support for atts_

|Argument | Type | Description |
| --- | --- | --- |
|$this->shortcode_name | string |  Name of shortcode caller |
|$atts | string|array |  Shortcode atts |
___
 ### `apply_filters('yasr_multi_set_ranking_atts')` 

 Source: [../includes/shortcodes/classes/YasrRankings.php, line 112](../.././includes/shortcodes/classes/YasrRankings.php:112)
___
 ### `apply_filters('yasr_visitor_multi_set_ranking_atts')` 

 Source: [../includes/shortcodes/classes/YasrRankings.php, line 144](../.././includes/shortcodes/classes/YasrRankings.php:144)

_Hook here to use shortcode atts._

_If not used, shortcode will works only with setId param_

|Argument | Type | Description |
| --- | --- | --- |
|$this->shortcode_name | string |  Name of shortcode caller |
|$atts | string|array |  Shortcode atts |
___

 ### `apply_filters('yasr_size_ranking')` 

 Source: [../includes/shortcodes/classes/YasrShortcode.php, line 109](../.././includes/shortcodes/classes/YasrShortcode.php:109)
___
 ### `do_action('yasr_enqueue_assets_shortcode')` 

 Source: [../includes/shortcodes/classes/YasrShortcode.php, line 178](../.././includes/shortcodes/classes/YasrShortcode.php:178)
___

 ### `do_action('yasr_action_on_visitor_vote')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 93](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:93)

_Hook here to add an action on visitor votes (e.g. empty cache)_

|Argument | Type | Description |
| --- | --- | --- |
|$array_action_visitor_vote | array |  An array containing post_id and is_singular |
___
 ### `apply_filters('yasr_vv_cookie')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 201](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:201)
___
 ### `apply_filters('yasr_vv_updated_text')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 214](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:214)
___
 ### `apply_filters('yasr_vv_saved_text')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 217](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:217)
___
 ### `do_action('yasr_action_on_visitor_multiset_vote')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 328](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:328)
___
 ### `apply_filters('yasr_mv_cookie')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 433](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:433)
___
 ### `apply_filters('yasr_mv_saved_text')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 442](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:442)
___
 ### `apply_filters('yasr_filter_ranking_request')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 625](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:625)
___
 ### `apply_filters('yasr_add_sources_ranking_request')` 

 Source: [../includes/shortcodes/classes/YasrShortcodesAjax.php, line 676](../.././includes/shortcodes/classes/YasrShortcodesAjax.php:676)
___

 ### `apply_filters('yasr_mv_cookie')` 

 Source: [../includes/shortcodes/classes/YasrVisitorMultiSet.php, line 113](../.././includes/shortcodes/classes/YasrVisitorMultiSet.php:113)
___
 ### `apply_filters('yasr_must_sign_in')` 

 Source: [../includes/shortcodes/classes/YasrVisitorMultiSet.php, line 167](../.././includes/shortcodes/classes/YasrVisitorMultiSet.php:167)
___

 ### `apply_filters('yasr_vv_ro_shortcode')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 116](../.././includes/shortcodes/classes/YasrVisitorVotes.php:116)

_Use this filter to customize yasr visitor votes readonly._

|Argument | Type | Description |
| --- | --- | --- |
|$shortcode_html | string |  html for the shortcode |
|$stored_votes | array |  array with average rating data for the post id. |
|$this->post_id | int |  the post id |
|$stored_votes | YasrDB::visitorVotes() |  array |
___
 ### `apply_filters('yasr_vv_cookie')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 130](../.././includes/shortcodes/classes/YasrVisitorVotes.php:130)

_Use this filter to customize the visitor votes cookie name_

|Argument | Type | Description |
| --- | --- | --- |
| | string |  yasr_visitor_votes_cookie is the default name |
___
 ### `apply_filters('yasr_cstm_text_already_voted')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 209](../.././includes/shortcodes/classes/YasrVisitorVotes.php:209)

_Use this filter to customize the text "You have already voted for this article with rating %rating%"_

_Unless you're using a multi-language site, there is no need to use this hook; you can customize this in_

_"General Settings" -> "Custom text to display when an user has already rated"_

___
 ### `apply_filters('yasr_must_sign_in')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 226](../.././includes/shortcodes/classes/YasrVisitorVotes.php:226)

_Use this filter to customize the text "you must sign in"_

_Unless you're using a multi-language site, there is no need to use this hook; you can customize this in_

_"General Settings" -> "Custom text to display when login is required to vote"_

___
 ### `apply_filters('yasr_cstm_text_before_vv')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 261](../.././includes/shortcodes/classes/YasrVisitorVotes.php:261)

_Use this filter to customize text before visitor rating._

_Unless you're using a multi-language site, there is no need to use this hook; you can customize this in_

_"General Settings" -> "Custom text to display BEFORE Visitor Rating"_

|Argument | Type | Description |
| --- | --- | --- |
|$number_of_votes | int |  the total number of votes |
|$average_rating | float |  the average rating |
|$this->unique_id | string |  the dom ID |
___
 ### `apply_filters('yasr_cstm_text_after_vv')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 317](../.././includes/shortcodes/classes/YasrVisitorVotes.php:317)

_Use this filter to customize text after visitor rating._

_Unless you're using a multi-language site, there is no need to use this hook; you can customize this in_

_"General Settings" -> "Custom text to display AFTER Visitor Rating"_

|Argument | Type | Description |
| --- | --- | --- |
|$number_of_votes | int |  the total number of votes |
|$average_rating | float |  the average rating |
|$this->unique_id | string |  the dom ID |
___
 ### `apply_filters('yasr_vv_shortcode')` 

 Source: [../includes/shortcodes/classes/YasrVisitorVotes.php, line 433](../.././includes/shortcodes/classes/YasrVisitorVotes.php:433)

_Use this filter to customize the yasr_visitor_votes shortcode_

|Argument | Type | Description |
| --- | --- | --- |
|$shortcode_html | string |  html for the shortcode |
|$this->post_id | int |  the post id |
|$this->starSize | string | () the star size |
|$this->readonly | string |  is the stars are readonly or not |
|$this->ajax_nonce_visitor | string |  the WordPress nonce |
|$this->is_singular | string |  if the current page is_singular or not |
___

 ### `apply_filters('yasr_seconds_between_ratings')` 

 Source: [../includes/yasr-includes-defines.php, line 33](../.././includes/yasr-includes-defines.php:33)
___

 ### `apply_filters('yasr_filter_ip')` 

 Source: [../includes/yasr-includes-functions.php, line 156](../.././includes/yasr-includes-functions.php:156)
___

 ### `apply_filters('yasr_auto_insert_disable')` 

 Source: [../public/classes/YasrPublicFilters.php, line 62](../.././public/classes/YasrPublicFilters.php:62)
___
 ### `apply_filters('yasr_auto_insert_exclude_cpt')` 

 Source: [../public/classes/YasrPublicFilters.php, line 92](../.././public/classes/YasrPublicFilters.php:92)
___
 ### `apply_filters('yasr_title_vv_widget')` 

 Source: [../public/classes/YasrPublicFilters.php, line 276](../.././public/classes/YasrPublicFilters.php:276)
___
 ### `apply_filters('yasr_title_overall_widget')` 

 Source: [../public/classes/YasrPublicFilters.php, line 313](../.././public/classes/YasrPublicFilters.php:313)
___

 ### `apply_filters('yasr_filter_schema_jsonld')` 

 Source: [../public/classes/YasrRichSnippets.php, line 77](../.././public/classes/YasrRichSnippets.php:77)

_Use this hook to write your custom microdata from scratch_

|Argument | Type | Description |
| --- | --- | --- |
|$item_type_for_post | string |  the itemType selected for the post |
___
 ### `apply_filters('yasr_filter_existing_schema')` 

 Source: [../public/classes/YasrRichSnippets.php, line 191](../.././public/classes/YasrRichSnippets.php:191)
___
 ### `apply_filters('yasr_filter_schema_title')` 

 Source: [../public/classes/YasrRichSnippets.php, line 223](../.././public/classes/YasrRichSnippets.php:223)
___

 ### `do_action('yasr_display_posts_top')` 

 Source: [../templates/content.php, line 28](../.././templates/content.php:28)

_hook here to add content at the beginning of yasr_display_posts_

___
 ### `do_action('yasr_display_posts_bottom')` 

 Source: [../templates/content.php, line 61](../.././templates/content.php:61)

_hook here to add content at the end of yasr_display_posts_

___

 ### `do_action('yasr_ur_add_custom_form_fields')` 

 Source: [../yasr_pro/public/classes/YasrProCommentForm.php, line 170](../.././yasr_pro/public/classes/YasrProCommentForm.php:170)
___
 ### `apply_filters('yasr_ur_display_custom_fields')` 

 Source: [../yasr_pro/public/classes/YasrProCommentForm.php, line 284](../.././yasr_pro/public/classes/YasrProCommentForm.php:284)
___
 ### `do_action('yasr_ur_save_custom_form_fields')` 

 Source: [../yasr_pro/public/classes/YasrProCommentForm.php, line 496](../.././yasr_pro/public/classes/YasrProCommentForm.php:496)
___
 ### `do_action('yasr_ur_do_content_after_save_commentmeta')` 

 Source: [../yasr_pro/public/classes/YasrProCommentForm.php, line 505](../.././yasr_pro/public/classes/YasrProCommentForm.php:505)
___

 ### `do_action('yasr_fs_loaded')` 

 Source: [../yet-another-stars-rating.php, line 82](../.././yet-another-stars-rating.php:82)
___
