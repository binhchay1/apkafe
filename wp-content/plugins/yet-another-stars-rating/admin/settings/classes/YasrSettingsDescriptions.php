<?php

/**
 * This class is a collection of methods to print settings description
 *
 * @author Dario Curvino <@dudo>
 * @since  3.4.1
 */
class YasrSettingsDescriptions {

    /**
     * Return the description of auto insert
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionAutoInsert() {
        $name = esc_html__('Auto Insert Options', 'yet-another-stars-rating');

        $description = sprintf(
            esc_html__(
                'Automatically adds YASR in your posts or pages. %s
            Disable this if you prefer to use shortcodes.', 'yet-another-stars-rating'
            ), '<br />'
        );

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionStarsTitle() {
        $name = esc_html__('Enable stars next to the title?', 'yet-another-stars-rating');

        $description = esc_html__(
            'Enable this if you want to show stars next to the title.', 'yet-another-stars-rating'
        );
        $description .= '<br />';
        $description .= esc_html__('Please note that this may not work with all themes', 'yet-another-stars-rating');

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionArchivePage() {
        $name = esc_html__('Archive Pages', 'yet-another-stars-rating');

        $description = esc_html__(
            'Here you can order your posts by ratings (please note that this may not work with all themes)
            and enable/disable ratings in your archive pages (homepage, categories, tags, etc.)',
            'yet-another-stars-rating'
        );

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionAllowVote() {
        $name = esc_html__('Who is allowed to vote?', 'yet-another-stars-rating');

        $description = sprintf(
            esc_html__(
                'Select who can rate your posts for %syasr_visitor_votes%s and %syasr_visitor_multiset%s shortcodes',
                'yet-another-stars-rating'
            ), '<em>', '</em>', '<em>', '</em>'
        );

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionVVStats() {
        $name = esc_html__('Show stats for visitors votes?', 'yet-another-stars-rating');

        $description = sprintf(
            esc_html__(
                'Enable or disable the chart bar icon (and tooltip hover it) next to the %syasr_visitor_votes%s shortcode',
                'yet-another-stars-rating'
            ), '<em>', '</em>'
        );

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionCSTMTxt() {
        $name = esc_html__('Customize strings', 'yet-another-stars-rating');

        $description = '<p>' . esc_html__('Customize YASR strings.', 'yet-another-stars-rating') . '</p>';

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionStructuredData() {
        $name = esc_html__('Structured data options', 'yet-another-stars-rating');

        $description = esc_html__(
            'If ratings in a post or page are found, YASR will create structured data to show them in search results
    (SERP)', 'yet-another-stars-rating'
        );
        $description .= '<br /><a href="https://yetanotherstarsrating.com/docs/rich-snippet/reviewrating-and-aggregaterating/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr_rischnippets_desc"
                        target="_blank">';
        $description .= esc_html__('More info here', 'yet-another-stars-rating');
        $description .= '</a>';

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * Return description for multiset color
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     * @return string
     */
    public function multisetColorDescription() {
        $name = __('Which color scheme do you want to use?', 'yet-another-stars-rating');
        $description = sprintf(
            esc_html__(
                'This only applies to multi criteria rating', 'yet-another-stars-rating'
            ), '<br />'
        );

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since
     * @return string
     */
    public function customCssDescription() {
        $name = __('Custom CSS Styles', 'yet-another-stars-rating');
        $description = esc_html__('Use the text area to write your own CSS styles and override the default ones.',
            'yet-another-stars-rating');
        $description .= '<br /><strong>';
        $description .= esc_html__('Leave it blank if you don\'t know what you\'re doing.', 'yet-another-stars-rating');
        $description .= '</strong><p>';

        return $this->settingsFieldDescription($name, $description);
    }

    /**
     * Describe what is a Multiset in the setting page
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     * @return string
     */
    public function descriptionMultiset() {
        $title = esc_html__('Multi-criteria based rating system.', 'yet-another-stars-rating');

        $description = sprintf(
            esc_html__(
                'A Multi-criteria set allows you to insert a rating for each aspect of your review (up to nine rows).
                    %s Once you\'ve saved it, you can insert 
                    the rates while typing your article in the %s box below the editor.%s %s
                    See it in action %s here%s .', 'yet-another-stars-rating'
            ), '<br />', '<a href=' . esc_url(YASR_IMG_DIR . 'yasr-multi-set-insert-rating.png') . ' target="_blank">',
            '</a>', '<br />', '<a href=' . esc_url(
                "https://yetanotherstarsrating.com/yasr-shortcodes/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr_newmultiset_desc#yasr-multiset-shortcodes"
            ) . '  target="_blank">', '</a>'
        );

        return $this->settingsFieldDescription($title, $description);

    }

    /**
     * Show the description for "Show average" row in multi criteria setting page
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     * @return string
     */
    public function descriptionShowAverage() {
        $title = esc_html__('Show average?', 'yet-another-stars-rating');

        $description = esc_html__(
            'If you select no, the "Average" row will not be displayed. 
        You can override this in the single multi set by using the parameter "show_average".',
            'yet-another-stars-rating'
        );

        return $this->settingsFieldDescription($title, $description);
    }

    /**
     * Return the title and the setting description
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     *
     * @param $title
     * @param $description
     *
     * @return string
     */
    public function settingsFieldDescription($title, $description) {
        $div_desc    = '<div class="yasr-settings-description">';
        $end_div     = '</div>';

        return $title . $div_desc . $description . $end_div;
    }
}