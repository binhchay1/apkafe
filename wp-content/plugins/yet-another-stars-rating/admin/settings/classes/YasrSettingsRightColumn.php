<?php

/**
 * Print the right column
 *
 * @author Dario Curvino <@dudo>
 * @since  3.4.1
 */
class YasrSettingsRightColumn {
    public static function init() {
        add_thickbox();
        ?>
        <div id="yasr-settings-panel-right">
            <?php
                do_action('yasr_right_settings_panel_box');
                self::upgradeBox();
                self::resourcesBox();
                //self::donations();
                //self::relatedPlugins();
                self::askRating();
            ?>
        </div>
        <?php
    }

    public static function upgradeBox() {
        if (yasr_fs()->is_free_plan()) {
            ?>

            <div class="yasr-donatedivdx">
                <h2 class="yasr-donate-title" style="color: #34A7C1">
                    <?php esc_html_e('Upgrade to YASR Pro', 'yet-another-stars-rating'); ?>
                </h2>
                <div class="yasr-upgrade-to-pro">
                    <ul>
                        <li><strong><?php esc_html_e('User Reviews', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('Custom Rankings', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('20+ ready to use themes', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('Upload your own theme', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('Fake ratings', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('Export data', 'yet-another-stars-rating'); ?></strong></li>
                        <li><strong><?php esc_html_e('Dedicate support', 'yet-another-stars-rating'); ?></strong></li>
                        <li>
                            <strong>
                                <a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr-pro#yasr-pro">
                                    <?php esc_html_e('...And much more!!', 'yet-another-stars-rating'); ?>
                                </a>
                            </strong>
                        </li>
                    </ul>
                    <a href="<?php echo esc_url(yasr_fs()->get_upgrade_url()); ?>">
                        <button class="button button-primary">
                        <span style="font-size: large; font-weight: bold;">
                            <?php esc_html_e('Upgrade Now', 'yet-another-stars-rating')?>
                        </span>
                        </button>
                    </a>
                    <div style="display: block; margin-top: 10px; margin-bottom: 10px; ">
                        --- or ---
                    </div>
                    <a href="<?php echo esc_url(yasr_fs()->get_trial_url()); ?>">
                        <button class="button button-primary">
                        <span style="display: block; font-size: large; font-weight: bold; margin: -3px;">
                            <?php esc_html_e('Start Free Trial', 'yet-another-stars-rating') ?>
                        </span>
                            <span style="display: block; margin-top: -10px; font-size: smaller;">
                             <?php esc_html_e('No credit-card, risk free!', 'yet-another-stars-rating') ?>
                        </span>
                        </button>
                    </a>
                </div>
            </div>

            <?php

        }

    }

    /*
     *   Add a box on with the resouces
     *   Since version 1.9.5
     *
    */
    public static function resourcesBox() {
        ?>

        <div class='yasr-donatedivdx' id='yasr-resources-box'>
            <div class="yasr-donate-title">Resources</div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-star-filled" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr_official">
                    <?php esc_html_e('YASR official website', 'yet-another-stars-rating') ?>
                </a>
            </div>

            <div class="yasr-donate-single-resource">
                <img src="<?php  echo esc_attr(YASR_IMG_DIR . 'github.svg') ?>"
                     width="20" height="20" alt="github logo" style="vertical-align: bottom;">
                <a target="blank" href="https://github.com/maucherOnline/Yet-Another-Stars-Rating">
                    GitHub Page
                </a>
            </div>

            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-edit" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/docs/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=documentation">
                    <?php esc_html_e('Documentation', 'yet-another-stars-rating') ?>
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-video-alt3" style="color: #6c6c6c"></span>
                <a target="blank" href="https://www.youtube.com/channel/UCU5jbO1PJsUUsCNbME9S-Zw">
                    <?php esc_html_e('Youtube channel', 'yet-another-stars-rating') ?>
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-smiley" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr-pro#yasr-pro">
                    Yasr Pro
                </a>
            </div>
        </div>

        <?php

    }

    /**
     * Adds buy a cofee box
     *
     * @author Dario Curvino <@dudo>
     */
    public static function donations() {
        $donation_text = '<p>';
        $donation_text .= esc_html__('First version of YASR was released in 2014.','yet-another-stars-rating');
        $donation_text .= '</p>';
        $donation_text .= '<p>';

        if(yasr_fs()->is_free_plan()) {
            $donation_text .= esc_html__('I can still work on it only thanks to all the people who bought the PRO version over the years.', 'yet-another-stars-rating');
            $donation_text .= '</p>';
            $donation_text .= esc_html__("If you don't need the pro version, you may consider to make a donation, thanks!", 'yet-another-stars-rating');
        } else {
            $donation_text .= esc_html__('I can still work on it only thanks to all the amazing people like you who bought the PRO version over the years.', 'yet-another-stars-rating');
            $donation_text .= '</p>';
            $donation_text .= esc_html__("If you want, you can also help with a donation, thanks!", 'yet-another-stars-rating');
        }

        $donation_text .= '<br />';
        $lp_image = '<a href="https://liberapay.com/~1775681" target="_blank">
                        <img src="'.YASR_IMG_DIR.'/liberapay.svg" alt="liberapay" width="150">
                     </a>';

        $kofi_image = '<a href="https://ko-fi.com/L4L6HBQQ4" target="_blank">
                        <img src="'.YASR_IMG_DIR.'/kofi.png" alt="kofi" width="150">
                     </a>';

        $div = "<div class='yasr-donatedivdx' id='yasr-buy-cofee'>";

        $text  = '<div class="yasr-donate-title">' . __('Donations', 'yet-another-stars-rating') .'</div>';
        $text .= '<div>';
        $text .= $donation_text;
        $text .= '</div>';
        $text .= '<div style="margin-top:10px;">';
        $text .= $lp_image;
        $text .= '</div>';
        $text .= '<div style="margin-top:10px;">';
        $text .= $kofi_image;
        $text .= '</div>';
        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * Show related plugins
     *
     * @author Dario Curvino <@dudo>
     */
    public static function relatedPlugins() {

        $div = "<div class='yasr-donatedivdx' id='yasr-related-plugins'>";

        $text  = '<div class="yasr-donate-title">' . esc_html__('You may also like...', 'yet-another-stars-rating') .'</div>';
        $text .=  self::movieHelper();
        $text .= '<hr />';
        $text .= self::cnrt();
        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.3
     * @return string
     */
    public static function movieHelper() {
        $url = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => 'yet-another-movie',
                'TB_iframe' => 'true',
                'width'     => '772',
                'height'    => '670'
            ),
            network_admin_url( 'plugin-install.php' )
        );

        $movie_helper_description = esc_html__('Movie Helper allows you to easily add links to movie and tv shows, just by searching
    them while you\'re writing your content. Search, click, done!', 'yet-another-stars-rating');
        $text = '<h4>Movie Helper</h4>';
        $text .= '<div style="margin-top: 15px;">';
        $text .= $movie_helper_description;
        $text .= '</div>';
        $text .= '<div style="margin-top: 15px;">
                <a href="'. esc_url( $url ).'"
                   class="install-now button thickbox open-plugin-details-modal"
                   target="_blank">'. __( 'Install', 'yet-another-stars-rating' ).'</a>';
        $text .= '</div>';

        return $text;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.3
     * @return string
     */
    public static function cnrt() {
        $url = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => 'comments-not-replied-to',
                'TB_iframe' => 'true',
                'width'     => '772',
                'height'    => '670'
            ),
            network_admin_url( 'plugin-install.php' )
        );

        $text  = '<h4>Comments Not Replied To</h4>';
        $text .= '<div style="margin-top: 15px;">';
        $text .= esc_html__('"Comments Not Replied To" introduces a new area in the administrative dashboard that allows you to
        see what comments to which you - as the site author - have not yet replied.', 'yet-another-stars-rating');
        $text .= '</div>';
        $text .= '<div style="margin-top: 15px;">
                <a href="'. esc_url( $url ).'"
                   class="install-now button thickbox open-plugin-details-modal"
                   target="_blank">'. __( 'Install', 'yet-another-stars-rating' ).'</a>';
        $text .= '</div>';

        return $text;
    }

    /** Add a box on the right for asking to rate 5 stars on Wordpress.org
     *   Since version 0.9.0
     */
    public static function askRating() {
        $div = "<div class='yasr-donatedivdx' id='yasr-ask-five-stars'>";

        $text = '<div class="yasr-donate-title">' . esc_html__('Leave a review', 'yet-another-stars-rating') .'</div>';
        $text .= '<div style="font-size: 32px; color: #F1CB32; text-align:center; margin-bottom: 20px; margin-top: -5px;">
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
            </div>';
        $text .= esc_html__('Please rate YASR 5 stars on', 'yet-another-stars-rating');
        $text .= ' <a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5">
        WordPress.org.</a><br />';
        $text .= esc_html__(' It will require just 1 min but it\'s a HUGE help for me. Thank you.', 'yet-another-stars-rating');
        $text .= "<br /><br />";
        $text .= "<em>> Dario Curvino</em>";

        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);

    }
}
