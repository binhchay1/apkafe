<?php
/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/**
 * Every hooks related to edit page are here
 *
 * @author Dario Curvino <@dudo>
 * @since 2.8.4
 * Class YasrEditorHooks
 */

class YasrEditorHooks {

    private $custom_post_types = null;
    public  $multi_set         = false;
    public  $n_multi_set       = false;

    public function init() {

        //enable attribute "custom-fields" in cpt
        add_action('init',                          array($this, 'enableCptCustomFields'), 100);

        //This filter is used to add a new category in gutenberg
        add_filter('block_categories_all',          array($this, 'addGutenbergCategory'), 10, 2);

        // Create 2 metaboxes in post and pages
        add_action('add_meta_boxes',                array($this, 'addMetaboxes'));

        // Add a media content button
        add_action('media_buttons',                 array($this, 'openTinymceButton'), 99);

        // Get Set name from post or page and output the set used in yasr-metabox-multiple-rating
        add_action('wp_ajax_yasr_send_id_nameset',  array($this, 'metaboxOutputMultisets'));

        $yasr_save_post = new YasrOnSavePost();
        add_action('save_post', array($yasr_save_post, 'yasrSavePost'));

        //delete data when post is deleted
        add_action('delete_post', array($this, 'deletePostData'));


    }

    /**
     * Adds attribute custom-fields for all cpts
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.7
     */
    public function enableCptCustomFields () {

        $custom_post_types = $this->cptAttribute();

        //if custom post type exists, add attribute 'custom-fields'
        if($custom_post_types) {
            foreach ( $custom_post_types as $cpt ) {
                YasrCustomPostTypes::enableCustomFields( $cpt );
            }
        }

    }

    /**
     * Adds a category in gutenberg blocks list
     *
     * @author Dario Curvino <@dudo>
     * @param $categories
     *
     * @return array
     */
    public function addGutenbergCategory($categories) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug'  => 'yet-another-stars-rating',
                    'title' => 'Yasr: Yet Another Stars Rating',
                ),
            )
        );
    }

    /**
     * Adds two metaboxes
     *
     * @author Dario Curvino <@dudo>
     */
    public function addMetaboxes() {

        //Default post type where display metabox
        $post_type_where_display_metabox = array('post', 'page', 'wp_template', 'wp_template_part');

        $custom_post_types = $this->cptAttribute();

        if ($custom_post_types) {
            //First merge array then changes keys to int
            $post_type_where_display_metabox = array_values(array_merge($post_type_where_display_metabox, $custom_post_types));
        }

        //For classic editor, add this metabox
        foreach ($post_type_where_display_metabox as $post_type) {
            add_meta_box(
                'yasr_metabox_overall_rating',
                'YASR',
                array($this, 'yasr_metabox_overall_rating_content'),
                $post_type,
                'side',
                'high',
                //Set this to true, so this metabox will be only loaded to classic editor
                array(
                    '__back_compat_meta_box' => true,
                )
            );
        }

        foreach ($post_type_where_display_metabox as $post_type) {
            add_meta_box(
                'yasr_metabox_below_editor',
                'Yet Another Stars Rating',
                array($this, 'yasr_metabox_below_editor_callback'),
                $post_type,
                'normal',
                'high'
            );
        }

    } //End function

    /**
     * Metabox for classic editor
     *
     * @author Dario Curvino <@dudo>
     */
    public function yasr_metabox_overall_rating_content() {
        if (current_user_can(YASR_USER_CAPABILITY_EDIT_POST)) {
            include(YASR_ABSOLUTE_PATH_ADMIN . '/editor/yasr-metabox-top-right.php');
        } else {
            esc_html_e('You don\'t have enought privileges to insert Overall Rating', 'yet-another-stars-rating');
        }
    }

    /**
     * Metabox below editor
     *
     * @author Dario Curvino <@dudo>
     */
    public function yasr_metabox_below_editor_callback() {
        if (current_user_can(YASR_USER_CAPABILITY_EDIT_POST)) {
            include(YASR_ABSOLUTE_PATH_ADMIN . '/editor/YasrMetaboxBelowEditor.php');
            $metabox = new YasrMetaboxBelowEditor();
            $metabox->printMetabox($this->multi_set, $this->n_multi_set);
        } else {
            esc_html_e('You don\'t have enough privileges to insert a Multi Set', 'yet-another-stars-rating');
        }
    }

    /**
     * Add YASR button for tinymce
     *
     * @author Dario Curvino <@dudo>
     */
    public function openTinymceButton() {
        if (is_admin()) {
            add_thickbox();
            ?>
            <a
                 id="yasr-shortcode-creator"
                 class="button">
                 <span class="dashicons dashicons-star-half" style="vertical-align: text-bottom;"></span> Yasr Shortcodes
            </a>
            <?php
            $this->tinymcePopupContent();
        }
    }

    /**
     * TinyMce Button content
     * 
     * @author Dario Curvino <@dudo>
     * @since  3.0.6
     */
    public function tinymcePopupContent () {
        global $wpdb;
        $multi_set   = YasrDB::returnMultiSetNames();
        $n_multi_set = $wpdb->num_rows;

        $this->multi_set   = $multi_set;
        $this->n_multi_set = $n_multi_set;

        $this->addTinymcePopupContent();
        ?>
        <div id="yasr-tinypopup-form" style="display: none;">
            <h2 class="nav-tab-wrapper yasr-underline">
                <?php
                    /**
                     * Use this action to add tabs inside shortcode creator for tinymce
                     */
                    do_action('yasr_add_tabs_on_tinypopupform');
                ?>
                <a href="https://yetanotherstarsrating.com/yasr-basics-shortcode/?utm_source=wp-plugin&utm_medium=tinymce-popup&utm_campaign=yasr_editor_screen"
                    target="_blank"
                    id="yasr-tinypopup-link-doc">
                    <?php esc_html_e('Read the doc', 'yet-another-stars-rating'); ?>
                </a>
            </h2>

            <?php
                /**
                 * Use this action to add content inside shortcode creator
                 *
                 * @param int $n_multi_set
                 * @param string $multi_set the multiset name
                 */
                do_action('yasr_add_content_on_tinypopupform', $n_multi_set, $multi_set);
            ?>

        </div>

        <?php
    }

    /**
     * Add Actions for tab and content of Tinymce
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.6
     */
    public function addTinymcePopupContent() {
        //Add tabs
        add_action('yasr_add_tabs_on_tinypopupform', array($this, 'tinymcePopupMainTab'), 10);
        add_action('yasr_add_tabs_on_tinypopupform', array($this, 'tinymcePopupRankingsTab'), 20);

        //Tab content
        add_action('yasr_add_content_on_tinypopupform', array($this, 'tinymcePopupMainTabContent'), 10, 2);
        add_action('yasr_add_content_on_tinypopupform', array($this, 'tinymcePopupRankingTabContent'), 20);
    }

    /**
     * Adds the tab "Main"
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.6
     */
    public function tinymcePopupMainTab() {
        ?>
        <a href="#" id="yasr-link-tab-main"
           class="nav-tab nav-tab-active yasr-nav-tab">
            <?php esc_html_e('Main', 'yet-another-stars-rating'); ?>
        </a>
        <?php
    }

    /**
     * Adds the tab "Rankings"
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.6
     */
    public function tinymcePopupRankingsTab() {
        ?>
        <a href="#" id="yasr-link-tab-charts"
           class="nav-tab yasr-nav-tab">
            <?php esc_html_e('Rankings', 'yet-another-stars-rating'); ?>
        </a>
        <?php
    }

    /**
     * Content for the "main" tab in shortcode creator
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.6
     * @param $n_multi_set
     * @param $multi_set
     */
    public function tinymcePopupMainTabContent ($n_multi_set, $multi_set) {
        ?>
        <div id="yasr-content-tab-main" class="yasr-content-tab-tinymce">
            <table id="yasr-table-tiny-popup-main" class="form-table">
                <tr>
                    <th>
                        <label for="yasr-overall">
                            <?php esc_html_e('Overall Rating', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="button"
                            class="button-primary"
                            id="yasr-overall"
                            name="yasr-overall"
                            value="<?php esc_attr_e('Insert Overall Rating', 'yet-another-stars-rating'); ?>"
                        />
                        <br/>
                        <small>
                            <?php esc_html_e('Insert the author rating', 'yet-another-stars-rating'); ?>
                        </small>

                        <div id="yasr-overall-choose-size">
                            <small>
                                <?php esc_html_e('Choose Size', 'yet-another-stars-rating'); ?>
                            </small>
                            <div class="yasr-tinymce-button-size">
                                <?php
                                    echo yasr_kses($this->tinyMceButtonCreator('yasr_overall_rating'));
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="yasr-id">
                            <?php esc_html_e('Visitor Votes', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="button"
                           class="button-primary"
                           name="yasr-visitor-votes"
                           id="yasr-visitor-votes"
                           value="<?php esc_attr_e("Insert Visitor Votes", 'yet-another-stars-rating'); ?>"/>
                        <br/>
                        <small>
                            <?php esc_html_e('Insert the ability for your visitors to vote', 'yet-another-stars-rating'); ?>
                        </small>

                        <div id="yasr-visitor-choose-size">
                            <small>
                                <?php esc_html_e('Choose Size', 'yet-another-stars-rating'); ?>
                            </small>
                            <div class="yasr-tinymce-button-size">
                                <?php
                                    echo yasr_kses($this->tinyMceButtonCreator('yasr_visitor_votes'));
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php if ($n_multi_set > 0) { //If multiple Set are found ?>
                    <tr>
                        <th>
                            <?php esc_html_e('Insert Multiset:', 'yet-another-stars-rating'); ?>
                        </th>
                        <td>
                            <?php foreach ($multi_set as $name) { ?>
                                <label>
                                    <input type="radio"
                                           value="<?php echo esc_attr($name->set_id) ?>"
                                           name="yasr_tinymce_pick_set"
                                           class="yasr_tinymce_select_set">
                                    <?php echo esc_attr($name->set_name); ?>
                                </label>
                                <br/>
                            <?php } //End foreach ?>
                            <small>
                                <?php esc_html_e('Choose wich set you want to insert.', 'yet-another-stars-rating'); ?>
                            </small>

                            <p>
                                <label for="yasr-allow-vote-multiset">
                                    <input type="checkbox" id="yasr-allow-vote-multiset">
                                    <?php esc_html_e('Readonly?', 'yet-another-stars-rating'); ?>
                                </label>
                                <br/>
                            </p>

                            <small>
                                <?php esc_html_e('If Readonly is checked, only you can insert the votes (in the box above the editor)',
                                    'yet-another-stars-rating'); ?>
                            </small>

                            <p>
                                <label for="yasr-hide-average-multiset">
                                    <input type="checkbox" id="yasr-hide-average-multiset">
                                    <?php esc_html_e("Hide Average?", 'yet-another-stars-rating'); ?>
                                </label>
                                <br/>
                            </p>

                            <p>
                                <input type="button"
                                       class="button-primary"
                                       name="yasr-insert-multiset"
                                       id="yasr-insert-multiset-select"
                                       value="<?php esc_attr_e("Insert Multi Set", 'yet-another-stars-rating') ?>"/
                                >
                                <br/>
                            </p>

                        </td>
                    </tr>
                    <?php
                } //End if
                ?>
                <tr>
                    <th>
                        <label for="yasr-user-rate-history">
                            <?php esc_html_e('User Rate History', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                            echo yasr_kses(
                                    $this->tinyMceButtonCreator('yasr_user_rate_history', 'Insert User Rate History')
                            );
                        ?>
                        <br/>
                        <small>
                            <?php
                                esc_html_e('If user is logged in, this shortcode shows a list of all the ratings 
                                provided by the user on [yasr_visitor_votes] shortcode.', 'yet-another-stars-rating');
                            ?>
                        </small>
                    </td>
                </tr>
            </table>

        </div>
        <?php
    }

    /**
     * Content for the "ranking" tab in shortcode creator
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.6
     */
    public function tinymcePopupRankingTabContent () {
        ?>
        <div id="yasr-content-tab-charts" class="yasr-content-tab-tinymce" style="display:none">
            <table id="yasr-table-tiny-popup-charts" class="form-table">
                <tr>
                    <th>
                        <label for="yasr-10-overall">
                            <?php esc_html_e('Ranking by overall rating', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                            echo yasr_kses($this->tinyMceButtonCreator(
                                'yasr_ov_ranking', 'Insert Ranking reviews'
                            ));
                        ?>
                        <br/>
                        <small>
                            <?php esc_html_e('This ranking shows the highest rated posts rated through the 
                                yasr_overall_rating shortcode',
                                'yet-another-stars-rating'); ?>
                        </small>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="yasr-10-highest-most-rated">
                            <?php esc_html_e('Ranking by visitors votes', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                            echo yasr_kses($this->tinyMceButtonCreator(
                                'yasr_most_or_highest_rated_posts', 'Insert Users ranking'
                            ));
                        ?>
                        <br/>
                        <small>
                            <?php esc_html_e(
                                'This ranking shows both the highest and most rated posts rated through the 
                                    yasr_visitor_votes shortcode.  For an item to appear in this chart, it has to be rated at least twice. ',
                                'yet-another-stars-rating'); ?>
                        </small>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="yasr-5-active-reviewers">
                            <?php esc_html_e('Most Active Authors', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                            echo yasr_kses($this->tinyMceButtonCreator(
                                'yasr_top_reviewers', 'Insert Most Active Reviewers'
                            ));
                        ?>
                        <br/>
                        <small>
                            <?php
                            esc_html_e('This ranking shows the most active reviewers on your site.',
                                'yet-another-stars-rating'); ?>
                        </small>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="yasr-10-active-users">
                            <?php esc_html_e('Most Active Users', 'yet-another-stars-rating'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                            echo yasr_kses($this->tinyMceButtonCreator(
                                'yasr_most_active_users', 'Insert Most Active Reviewers'
                            ));
                        ?>
                        <br/>
                        <small>
                            <?php esc_html_e('This ranking shows the most active users, displaying the login name if logged in or “Anonymous” if not.',
                                'yet-another-stars-rating'); ?>
                        </small>
                    </td>
                </tr>
            </table>

            <div style="font-size: medium">
                <?php
                    echo(
                        sprintf(__('%s Click here %s to customize the ranking and see a live preview',
                            'yet-another-stars-rating'),
                            '<a href="options-general.php?page=yasr_settings_page&tab=rankings">', '</a>'
                        )
                    );
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Returns button to be used in tinymce
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.5
     *
     * @param      $shortcode
     * @param bool $value
     *
     * @return string
     */
    public function tinyMceButtonCreator($shortcode, $value = false) {
        $html_to_return = '';

        if ($value === false) {
            $array_size = array('Small', 'Medium', 'Large');

            foreach ($array_size as $size) {
                $size_low = strtolower($size);
                $data_attribute = "[$shortcode size=\"$size_low\"]";

                $html_to_return .= '<input type="button"
                                       class="button-secondary yasr-tinymce-shortcode-buttons"
                                       value="' . esc_attr__($size, 'yet-another-stars-rating') . '"
                                       data-shortcode=\''.esc_attr($data_attribute).'\'
                                    />&nbsp;';
            }
        }
        else {
            $data_attribute = "[$shortcode]";
            $html_to_return .= '<input type="button"
                                   class="button-primary yasr-tinymce-shortcode-buttons"
                                   value="' . esc_attr__($value, 'yet-another-stars-rating') . '"
                                   data-shortcode=\''.esc_attr($data_attribute).'\'
                                />&nbsp;';
        }

        return $html_to_return;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.8.4
     */
    public function metaboxOutputMultisets() {
        if (!current_user_can(YASR_USER_CAPABILITY_EDIT_POST)) {
            /** @noinspection ForgottenDebugOutputInspection */
            wp_die(__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
        }

        $nonce = $_POST['yasr_send_id_nameset_nonce'];

        //First nonce is used at page load, second nonce is used when a set is changed if >1 is used
        if(!wp_verify_nonce($nonce, 'yasr_nonce_set_id') && !wp_verify_nonce($nonce, 'yasr_nonce_change_set')) {
            echo json_encode(__('Wrong nonce', 'yet-another-stars-rating'));
            die();
        }

        //in version < 2.1.0 set id could be 0
        $set_id  = (int) $_POST['set_id'];
        $post_id = (int) $_POST['post_id'];

        //set fields name and ids
        $set_fields      = YasrDB::multisetFieldsAndID($set_id);

        //set meta values
        $array_to_return = YasrDB::returnArrayFieldsRatingsAuthor($set_id, $set_fields, $post_id);

        echo json_encode($array_to_return);

        die();
    }

    /**
     * Return the value of the attribute $custom_post_type
     *
     * @return bool|array
     */
    protected function cptAttribute() {
        if($this->custom_post_types === null) {
            //get the custom post type
            $custom_post_types = YasrCustomPostTypes::getCustomPostTypes();
        } else {
            $custom_post_types = $this->custom_post_types;
        }
        return $custom_post_types;
    }

    /**
     * Delete data value from yasr tabs when a post or page is deleted
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $post_id
     *
     * @since  0.3.3, moved into class since 3.1.7
     * @return void
     */
    public function deletePostData($post_id) {
        if (!current_user_can('delete_posts')) {
            return;
        }

        global $wpdb;

        delete_metadata('post', $post_id, 'yasr_overall_rating');
        delete_metadata('post', $post_id, 'yasr_review_type');
        delete_metadata('post', $post_id, 'yasr_multiset_author_votes');

        //Delete multi value
        $wpdb->delete(
            YASR_LOG_MULTI_SET, array(
                'post_id' => $post_id
            ), array(
                '%d'
            )
        );

        $wpdb->delete(
            YASR_LOG_TABLE, array(
                'post_id' => $post_id
            ), array(
                '%d'
            )
        );
    }

}//End Class