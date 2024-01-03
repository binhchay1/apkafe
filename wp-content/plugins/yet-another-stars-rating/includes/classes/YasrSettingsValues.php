<?php

/**
 * Collection of methods to manage YASR settings values
 *
 * @author Dario Curvino <@dudo>
 * @since  3.0.4
 * Class YasrSettingsValues
 */
class YasrSettingsValues {

    /**
     * Returns YASR General Settings
     * If general settings are not found (i.e. option deleted from database) return YASR default values.
     * There is no more need to insert the new default value on YasrAdmin->updateVersion
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     * @return array
     */
    public function getGeneralSettings () {
        $saved_values   = get_option('yasr_general_options');
        $default_values = $this->defaultValuesGeneral();

        return $this->returnSettingsArray($saved_values, $default_values);
    }

    /**
     * Returns YASR General Settings
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     * @return array
     */
    public function getStyleSettings () {
        $saved_values   = get_option('yasr_style_options');
        $default_values = $this->defaultValuesStyle();

        return $this->returnSettingsArray($saved_values, $default_values);
    }

    /**
     * Returns YASR Multiset Settings
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     * @return array
     */
    public function getMultiSettings () {
        $saved_values   = get_option('yasr_multiset_options');
        $default_values = $this->defaultValuesMulti();

        return $this->returnSettingsArray($saved_values, $default_values);
    }

    /**
     * Return an array with the default values for general settings
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     * @return array
     */
    public function defaultValuesGeneral() {
        $caching_plugin       = new YasrCachingPlugins();
        $caching_plugin_found = $caching_plugin->cachingPluginFound();

        $options['auto_insert_enabled']          = 1;
        $options['auto_insert_what']             = 'visitor_rating';
        $options['auto_insert_where']            = 'bottom';
        $options['auto_insert_align']            = 'center';
        $options['auto_insert_size']             = 'large';
        $options['auto_insert_exclude_pages']    = 'yes';
        $options['auto_insert_custom_post_only'] = 'no';
        $options['stars_title']                  = 'no';
        $options['stars_title_what']             = 'visitor_rating';
        $options['stars_title_exclude_pages']    = 'yes';
        $options['stars_title_where']            = 'archive';
        $options['sort_posts_by']                = 'no';
        $options['sort_posts_in']                = array('is_home');
        $options['show_overall_in_loop']         = 'disabled';
        $options['show_visitor_votes_in_loop']   = 'disabled';
        $options['visitors_stats']               = 'yes';
        $options['allowed_user']                 = 'allow_anonymous';
        $options['text_before_overall']          = esc_html__('Our Score', 'yet-another-stars-rating');
        $options['text_before_visitor_rating']   = esc_html__('Click to rate this post!', 'yet-another-stars-rating');
        $options['text_after_visitor_rating']    = sprintf(
            esc_html__('[Total: %s  Average: %s]', 'yet-another-stars-rating'),
            '%total_count%', '%average%'
        );
        $options['custom_text_rating_saved']     = esc_html__('Rating saved!', 'yet-another-stars-rating');
        $options['custom_text_rating_updated']   = esc_html__('Rating updated!', 'yet-another-stars-rating');
        $options['custom_text_user_voted']       =
            esc_html__('You have already voted for this article with rating ', 'yet-another-stars-rating') . '%rating%';
        $options['custom_text_must_sign_in']     = esc_html__('You must sign in to vote', 'yet-another-stars-rating');
        $options['snippet_itemtype']             = 'Product';
        $options['publisher']                    = 'Organization';
        $options['publisher_name']               = get_bloginfo('name');
        $options['publisher_logo']               = get_site_icon_url();
        $options['enable_ip']                    = 'yes';

        if($caching_plugin_found !== false) {
            $options['enable_ajax'] = 'yes';
        } else {
            $options['enable_ajax'] = 'no';
        }

        return $options;
    }

    /**
     * Return an array with the default values for style settings
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     * @return array
     */
    public function defaultValuesStyle() {
        $style_options['stars_set_free']        = 'rater-yasr';
        $style_options['scheme_color_multiset'] = 'light';
        $style_options['textarea']              = false;

        return $style_options;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     * @return array
     */
    public function defaultValuesMulti() {
        $multi_set_options['show_average'] = 'yes';
        return $multi_set_options;
    }

    /**
     * Return a merged array, where the first array has precedence
     *
     * https://stackoverflow.com/a/866615/3472877
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     * @param $saved_values
     * @param $default_values
     *
     * @return array
     */
    public function returnSettingsArray($saved_values, $default_values) {
        if(is_array($saved_values) && is_array($default_values)) {
            return $saved_values + $default_values;
        }

        return $default_values;
    }

}