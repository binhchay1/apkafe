<?php

/**
 * Manage the footer in the YASR settings page
 *
 * @author Dario Curvino <@dudo>
 * @since  3.4.1
 */
class YasrSettingsFooter {

    /**
     * @var $page, this is the value of the current page, e.g.
     * wp-admin/admin.php?page=yasr_settings_page
     */
    public $page;

    /**
     * @var $tab, this is the value of the current yasr tab, e.g.
     * wp-admin/admin.php?page=yasr_stats_page&tab=logs_multi
     */
    public $tab;

    /**
     * @var $default_text
     * will store the value from filter admin_footer_text
     */
    public $default_text;

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     *
     * @return void
     */
    public function init () {
        /** Change default admin footer on yasr settings pages
         *  $text is the default WordPress text
         *  Since 0.8.9, refactor and put into this class since 3.4.1
         */
        add_filter('admin_footer_text', array($this, 'returnFooter'));
    }

    /**
     * Return the footer for YASR setting page, filtered if needed
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     *
     * @param $text
     *
     * @return string
     */
    public function returnFooter($text) {
        $this->default_text = $text;
        if (isset($_GET['page'])) {
            $this->page = $_GET['page'];

            if(isset($_GET['tab'])) {
                $this->tab = $_GET['tab'];
            }

            return $this->customFooter();
        } else {
            return $this->default_text;
        }
    }

    /**
     * Footer to print in all YASR settings page
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     * @return string
     */
    public function customFooter() {
        if ($this->page === 'yasr_settings_page' || $this->page === 'yasr_stats_page') {

            if($this->tab === 'style_options') {
                $this->footerStylePage();
            }

            $custom_text = ' | <i>';
            $custom_text .= sprintf(esc_html__('Thank you for using %s. Please %s rate it%s 5 stars on %s', 'yet-another-stars-rating'),
                '<a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=footer&utm_campaign=yasr_settings"
                            target="_blank">Yet Another Stars Rating</a>',
                '<a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5" target="_blank">',
                '</a>',
                '<a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5" target="_blank">
                    WordPress.org</a>'
            );
            $custom_text .= '</i>';

            return $this->default_text . $custom_text;
        }
        return $this->default_text;
    }

    /**
     * Footer for "Aspect & Style page"
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     * @return void
     */
    public function footerStylePage() {
        $style_page_upgrade_pro_js =
            "<script>
                const performActions = () => {
                    const elementToHide  = jQuery('.yasr-stylish-locked');
                    const upgradeProText = jQuery('#yasr-settings-stylish-text');
                   
                    elementToHide.css({
                        'opacity': 0.5,
                        'pointer-events': 'none'
                    });
                    
                    upgradeProText.css({
                        'display': 'block',
                    });
                };
        
                jQuery('.yasr-stylish-locked').on('mouseover click', performActions);
            </script>";

        /**
         * Filter to customize the footer page on the "Aspect & style" page.
         * Since this could contain js, this will only allow FALSE as value
         *
         * @param string $style_page_upgrade_pro_js
         */
        $style_page_upgrade_pro_filtered = apply_filters('yasr_setting_page_footer', $style_page_upgrade_pro_js);

        //white list, the filtered value can only accept $style_page_upgrade_pro_js or false
        if($style_page_upgrade_pro_filtered === $style_page_upgrade_pro_js || $style_page_upgrade_pro_filtered === false) {
            echo ($style_page_upgrade_pro_filtered);
        }

    }
}