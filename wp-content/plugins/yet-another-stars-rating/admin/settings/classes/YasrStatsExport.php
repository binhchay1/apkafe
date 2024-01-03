<?php

/**
 * @author Dario Curvino <@dudo>
 * @since  3.3.3
 */
class YasrStatsExport {

    /**
     * @var bool
     */
    public $upload_dir_writable;

    public function init () {
        add_action('yasr_stats_tab_content',     array($this, 'tabContent'));
    }

    /**
     * Tab content
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.3
     *
     * @param $active_tab
     *
     * @return void
     */
    public function tabContent ($active_tab) {
        if ($active_tab === 'yasr_csv_export') {
            $upload_dir                = wp_upload_dir();
            $this->upload_dir_writable = wp_is_writable($upload_dir ['path']);
            $nonce                     = wp_create_nonce('yasr-export-csv');
            ?>
            <div>
                <h3>
                    <?php esc_html_e('Export Data', 'yet-another-stars-rating'); ?>
                </h3>
                <div class="yasr-help-box-settings" style="display: block">
                    <?php
                        esc_html_e('All the .csv files are saved into', 'yet-another-stars-rating');
                        echo ' ' . '<strong>'.$upload_dir ['baseurl'].'</strong>. ';
                        esc_html_e('The files are deleted automatically after 7 days.', 'yet-another-stars-rating');

                        if($this->upload_dir_writable === false) {
                            $error = esc_html__("Upload folder is not writable, data can't be saved!", 'yet-another-stars-rating');
                            echo '<div style="margin-top: 20px; padding-left: 5px; border: 1px solid #c3c4c7; border-left-color: #d63638; 
                                              border-left-width: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                                              <h3>'.$error.'</h3>
                                  </div>';
                        }
                    ?>
                </div>

                <div class="yasr-container">
                    <input type="hidden"
                           name="yasr_csv_nonce"
                           value="<?php echo esc_attr($nonce) ?>"
                           id="yasr_csv_nonce">

                    <!-- Boxes starts here -->
                    <!-- Visitor Votes -->
                    <div class="yasr-box">
                        <?php
                            $description = esc_html__('Export all ratings saved through the shortcode ',
                                'yet-another-stars-rating');
                            $description .= ' <strong>yasr_visitor_votes</strong>';
                            $this->printExportBox('visitor_votes', 'Visitor Votes', $description);
                            ?>
                    </div>
                    <!-- Visitor Multiset -->
                    <div class="yasr-box">
                        <?php
                            $description = esc_html__('Export all ratings saved through the shortcode ',
                                'yet-another-stars-rating');
                            $description .= ' <strong>yasr_visitor_multiset</strong>';
                            $this->printExportBox('visitor_multiset', 'Visitor Multi Set', $description);
                        ?>
                    </div>
                    <!-- Overall Rating -->
                    <div class="yasr-box">
                        <?php
                            $description = esc_html__('Save all author ratings.', 'yet-another-stars-rating');
                            $description .= ' <strong>(yasr_overall_rating)</strong>';
                            $this->printExportBox('overall_rating', 'Overall Rating', $description);
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Print the box with button to export data
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.3
     *
     * @param $name            string     what to export
     * @param $readable_name   string     readable name
     * @param $description     string     box description
     *
     * @return void
     */
    private function printExportBox ($name, $readable_name, $description) {
        $button_disabled = '';
        if($this->upload_dir_writable === false) {
            $button_disabled = 'disabled';
        }

        $button_id    = 'yasr-export-csv-' . $name;
        $answer_id    = 'yasr-export-ajax-result-'.$name;
        $name_hidden  = 'yasr_export_'. $name;

        $translated_readable_name = sprintf('%s', esc_html__($readable_name));
        ?>
        <div>
            <h4>
                <?php
                    $h5_text  = esc_html__('Export', 'yet-another-stars-rating');
                    $h5_text .= ' ' . $translated_readable_name;

                    echo esc_html($h5_text);
                ?>
            </h4>
            <h5>
                <?php echo yasr_kses($description); ?>
            </h5>
            <hr />

            <?php
                $button = '<a href="'.esc_url(yasr_fs()->get_upgrade_url()).'">';
                $button .='<button class="button-primary yasr-export">'.
                                esc_html__( 'Unlock with premium', 'yet-another-stars-rating' )
                            .'&nbsp;'.YASR_LOCKED_FEATURE
                            .'</button>';
                $button .= '</a>';

                /**
                 *  Use this hook to customize the button
                 */
                $button = apply_filters('yasr_export_box_button', $button, $button_id, $button_disabled);

                echo wp_kses_post($button);
            ?>

            <input type="hidden"
                   name="<?php echo esc_attr($name_hidden) ?>"
                   value="<?php echo esc_attr($name) ?>">
        </div>
        <div id="<?php echo esc_attr($answer_id) ?>" style="margin: 5px 20px;" >
        </div>
        <div class="yasr-indented-answer">
            <?php
                /**
                 *  Hook here to do an action at the end of the box
                 */
                do_action('yasr_export_box_end', $name);
            ?>
        </div>
        <?php
    }
}