<?php
/*

Copyright 2020 Dario Curvino (email : d.curvino@gmail.com)

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

class YasrSettingsRankings {
    public  $custom_post_types;
    private $multi_set_array;
    private $n_multi_set;

    public function __construct($custom_post_types) {
        $this->custom_post_types = $custom_post_types;
        $this->multi_set_array = YasrDB::returnMultiSetNames();

        global $wpdb;
        //wpdb->num_rows always store the count number of rows of the last query
        $this->n_multi_set = $wpdb->num_rows;
    }

    public function selectRanking () {
        ?>
        <!-- Source -->
        <div class="yasr-builder-div-fluid">
            <strong>
                <?php esc_html_e('Select Ranking', 'yet-another-stars-rating'); ?>
            </strong>
            <br/>
            <?php
                $source_array = array(
                    'yasr_most_or_highest_rated_posts',
                    'yasr_ov_ranking',
                    'yasr_most_active_users',
                    'yasr_top_reviewers'
                );

                if($this->n_multi_set > 0) {
                    $source_array[] = 'yasr_multi_set_ranking';
                    $source_array[] = 'yasr_visitor_multi_set_ranking';
                }

                //Use this filter to add (or remove) new elements
                $select_array = apply_filters('yasr_settings_select_ranking', $source_array);

                $name  = 'yasr-ranking-source';
                $id    = 'yasr-ranking-source';

                echo yasr_kses(
                    YasrPhpFieldsHelper::select('','', $select_array, $name, $id, 'yasr_visitor_votes')
                );
            ?>
        </div>
        <?php
    }

    public function rows () {
        ?>
        <!-- Rows Number -->
        <div class="yasr-builder-div-fluid">
            <strong>
                <?php
                    esc_html_e( 'How many rows?', 'yet-another-stars-rating');
                    echo YASR_LOCKED_FEATURE;
                ?>
            </strong>
            <br/>
            <label for="yasr-builder-rows"></label>
            <select name="yasr-builder-rows-number"
                    id="yasr-builder-rows"
                    class="yasr-builder-elements-parents"
                    autocomplete="off">
                <?php

                for ($i = 2; $i <= 99; $i ++) {
                    if ($i === 10) { //default value
                        echo '<option value="'.esc_attr($i).'" selected="selected">'.esc_attr($i).'</option>\n';
                    } else {
                        echo '<option value="'.esc_attr($i).'">'.esc_attr($i).'</option>\n';
                    }
                } //End for

                ?>
            </select>
        </div>
        <?php
    }

    public function size () {
        ?>
        <!-- Size -->
        <div class="yasr-builder-div-fluid" id="builder-stars-size">
            <strong>
                <?php esc_html_e('Size', 'yet-another-stars-rating'); ?>
            </strong>
                <?php echo YASR_LOCKED_FEATURE; ?>
            <br/>
            <?php
            $name  = 'yasr-builder-size';
            $class = 'yasr-builder-size yasr-builder-elements-parents';
            $id    = 'yasr-builder-size-';

            echo yasr_kses(
                YasrSettings::radioSelectSize($name, $class, 'medium', $id, false)
            );
            ?>
        </div>
        <?php
    }

    public function vvDefaultView() {
        ?>
        <!-- Default View -->
        <div class="yasr-builder-div-fluid" id="builder-vv-default-view">
            <?php
            $option_title = '<strong>'.__('Default View', 'yet-another-stars-rating').'</strong>';
            $array_options = array (
                'most'      => __('Most Rated', 'yet-another-stars-rating'),
                'highest'   => __('Highest Rated', 'yet-another-stars-rating')
            );
            $default = 'most';
            $name    = 'yasr-vv-default-view';
            $class   = 'yasr-vv-default-view yasr-builder-elements-parents';
            $id      = 'yasr-default-view';

            echo wp_kses_post($option_title.YASR_LOCKED_FEATURE);
            echo yasr_kses(
                YasrPhpFieldsHelper::radio( '', $class, $array_options, $name, $default, $id )
            );
            ?>
        </div>
        <?php
    }

    public function vvRequiredVotes(){
        ?>
        <div class="yasr-builder-div-fluid" id="builder-vv-required-votes">
            <strong>
                <?php
                    esc_html_e('At least...', 'yet-another-stars-rating');
                    echo YASR_LOCKED_FEATURE;
                ?>
            </strong>
            <!-- Min. number of votes for most rated -->
            <div>
                <label for="yasr-required-votes-most">
                    <select name="yasr-required-votes"
                            id="yasr-required-votes-most"
                            class="yasr-builder-elements-parents"
                            autocomplete="off">
                        <?php
                        for ($i = 1; $i <= 99; $i ++) {
                            if ($i === 1) { //default value
                                echo '<option value="'.esc_attr($i).'" selected="selected">'.esc_attr($i).'</option>\n';
                            } else {
                                echo '<option value="'.esc_attr($i).'">'.esc_attr($i).'</option>\n';
                            }
                        } //End for
                        ?>
                    </select>
                </label>
                <br />
                <?php esc_html_e('votes required for most rated', 'yet-another-stars-rating'); ?>
            </div>

            <!-- Min. number of votes for highest rated -->
            <div style="margin-top: 3px;">
                <label for="yasr-required-votes-highest">
                    <select name="yasr-required-votes-highest"
                            id="yasr-required-votes-highest"
                            class="yasr-builder-elements-parents">
                        <?php
                        for ($i = 1; $i <= 99; $i ++) {
                            if ($i === 1) { //default value
                                echo '<option value="'.esc_attr($i).'" selected="selected">'.esc_attr($i).'</option>\n';
                            } else {
                                echo '<option value="'.esc_attr($i).'">'.esc_attr($i).'</option>\n';
                            }
                        } //End for
                        ?>
                    </select>
                </label>
                <br />
                <?php esc_html_e('votes required for highest rated', 'yet-another-stars-rating'); ?>
            </div>
        </div>
        <?php
    }

    public function ovCustomText(){
        ?>
        <!-- Customize text -->
        <div class="yasr-builder-div-fluid" id="builder-overall-text" style="display: none">
            <?php
                $option_title = __('Show text before or after the stars?', 'yet-another-stars-rating');
                $class   = 'yasr-builder-custom-text-overall yasr-builder-elements-parents';
                $array_options = array (
                    'no'     => __('No', 'yet-another-stars-rating'),
                    'before' => __('Yes, before the stars', 'yet-another-stars-rating'),
                    'after'  => __('Yes, after the stars', 'yet-another-stars-rating')
                );
                $name    = 'yasr-builder-customize-overall-text';
                $default = 'no';
                $id      = 'yasr-builder-ov-radio-text';

                echo wp_kses_post('<strong>'.$option_title.'</strong>'.YASR_LOCKED_FEATURE);
                echo yasr_kses(
                    YasrPhpFieldsHelper::radio('', $class, $array_options, $name, $default, $id)
                );
            ?>
            <br />
            <strong>
                <?php esc_html_e("Text to show", 'yet-another-stars-rating') ?>
            </strong>
            <br/>
            <label for="yasr-builder-customize-ov-text">
                <input type="text" name="yasr-builder-customize-ov-text"
                       value="<?php esc_attr_e("Rating:", 'yet-another-stars-rating') ?>"
                       id="yasr-builder-customize-ov-text"
                       class="yasr-builder-elements-childs"
                       maxlength="30">
            </label>
            <div>
                <small>
                    <?php
                        echo wp_kses_post(sprintf(
                            __('Use return %s to insert the text.', 'yet-another-stars-rating'),
                            '<strong>(&#8629;)</strong>'
                        ));
                    ?>
                </small>
            </div>
        </div>
        <?php
    }

    public function setDate() {
        ?>
        <div class="yasr-builder-div-fluid" id="yasr-builder-datepicker" style="display: none">
            <?php
                $name_id  = 'yasr-builder-datepicker-start';
                echo wp_kses_post('<strong>'.__('Start Date', 'yet-another-stars-rating').'</strong>' . YASR_LOCKED_FEATURE);
                echo yasr_kses(
                    YasrPhpFieldsHelper::text(
                        'yasr-option-div yasr-builder-elements-parents',
                        '',
                        $name_id,
                        $name_id
                ));

                $name_id  = 'yasr-builder-datepicker-end';
                echo wp_kses_post('<strong>'.__('End Date', 'yet-another-stars-rating').'</strong>' . YASR_LOCKED_FEATURE);
                echo yasr_kses(
                    YasrPhpFieldsHelper::text(
                        'yasr-option-div yasr-builder-elements-parents',
                        '',
                        $name_id,
                        $name_id
                ));
            ?>
        </div>
        <?php

    }

    public function categories(){
        if ($this->custom_post_types !== false) {
            $category_class = 'yasr-div-fixed-65';
        } else {
            $category_class = 'yasr-builder-div-fluid';
        }
        ?>
        <!-- Customize Category -->
        <div class="<?php echo esc_attr($category_class) ?>" id="builder-category">
            <?php
            $option_title = __('Do you want to specify a category?', 'yet-another-stars-rating');
            $array_options = array (
                0        => __('No', 'yet-another-stars-rating'),
                'yes'    => __('Yes', 'yet-another-stars-rating'),
            );
            $default = 0;
            $name    = 'yasr-builder-category-radio';
            $class   = 'yasr-builder-enable-category yasr-builder-elements-parents';
            $id      = 'yasr-builder-rankings-category';

            echo wp_kses_post($option_title.YASR_LOCKED_FEATURE);
            echo yasr_kses(
                    YasrPhpFieldsHelper::radio('', $class, $array_options, $name, $default, $id)
            );

            ?>

            <div>
                <label for="yasr-filter-categories">
                    <?php esc_html_e("Search Categories:", 'yet-another-stars-rating') ?>
                </label>
                <input type="text"
                       name="yasr-filter-categories"
                       id="yasr-filter-categories"
                       class="yasr-builder-category yasr-builder-elements-parents">
            </div>

            <div id="yasr-ranking-ctg-container">
                <?php
                $categories = get_categories();
                $i = 0;
                if($this->custom_post_types !== false) {
                    $newline_row = 3;
                } else {
                    $newline_row = 5;
                }
                foreach ($categories as $category) {
                    $id = "yasr-builder-category-checked[$i]";
                    echo '<span>
                              <input type="checkbox" 
                                  name="yasr-builder-category-checked" 
                                  class="yasr-builder-category yasr-builder-elements-childs" 
                                  id="'.esc_attr($id).'" 
                                  value="'.esc_attr($category->term_taxonomy_id) .'"
                                  data-category-name="'. esc_attr($category->name) .'"
                                  autocomplete="off"
                                  /><label for="'.esc_attr($id).'">'
                        . $category->name .
                        '</label>';
                    //close div
                    echo '</span>';

                    $i++;

                    if ($i%$newline_row === 0) {
                        echo '<br />';
                    }

                } //end foreach
                ?>
            </div>
        </div>
        <?php
    }

    public function cpt(){
        ?>
        <div class="yasr-div-fixed-35" id="builder-cpt">
            <?php
            $option_title  = __( 'Do you want to specify a custom post type?', 'yet-another-stars-rating' );
            $array_options = array(
                0     => __( 'No', 'yet-another-stars-rating' ),
                'yes' => __( 'Yes', 'yet-another-stars-rating' ),
            );
            $default       = 0;
            $name          = 'yasr-builder-cpt-radio';
            $class         = 'yasr-builder-enable-cpt yasr-builder-elements-parents';
            $id            = 'yasr-builder-enable-cpt-radio';

            echo wp_kses_post($option_title.YASR_LOCKED_FEATURE);
            echo yasr_kses(
                YasrPhpFieldsHelper::radio('', $class, $array_options, $name, $default, $id)
            );

            echo '<br />';

            $i = 0;
            $checked = 'checked';
            foreach ($this->custom_post_types as $post_type_slug) {
                $id = "yasr-builder-custom-post-radio[$i]";
                if($i > 0) {
                    $checked = '';
                }
                echo '<input type="radio" name="yasr-builder-custom-post-radio" 
                            class="yasr-builder-custom-post-radio yasr-builder-elements-childs" 
                            id="'.esc_attr($id).'"
                            value="'.esc_attr($post_type_slug).'" '.esc_attr($checked).' />
                            '.esc_attr($post_type_slug).'
                            <br />';
                $i ++;
            }
            ?>
        </div>
        <?php
    }

    public function usernameDisplay(){
        ?>
        <div class="yasr-builder-div-fluid" id="builder-username-options" style="display: none">
            <?php
            $option_title  = __('Do you want to use Username or Display Name?', 'yet-another-stars-rating');
            $array_options = array(
                'login'       => __('Username', 'yet-another-stars-rating'),
                'displayname' => __('User display name', 'yet-another-stars-rating')
            );
            $default       = 'login';
            $name          = 'yasr-builder-user-option';
            $class         = 'yasr-builder-user-option yasr-builder-elements-parents';
            $id            = 'yasr-builder-user-option';

            echo yasr_kses(
                YasrPhpFieldsHelper::radio($option_title, $class, $array_options, $name, $default, $id)
            );
            ?>

        </div>
        <?php
    }

    public function multiset() {
        if ($this->n_multi_set > 0) {
            ?>
            <div class="yasr-builder-div-fluid" id="yasr-ranking-multiset">
                <strong>
                    <?php
                        esc_html_e('Choose Multi Set', 'yet-another-stars-rating');
                    ?>
                </strong>
                <!-- Min. number of votes for most rated -->
                <div>
                    <label for="yasr-ranking-multiset-select">
                        <select id="yasr-ranking-multiset-select" autocomplete="off">
                            <?php
                                foreach ($this->multi_set_array as $name) {
                                    echo '<option value="'.esc_attr($name->set_id).'">'.esc_attr($name->set_name).'</option>';
                                } //End foreach
                            ?>
                        </select>
                    </label>
                </div>
            </div>
            <?php
        }

    }

    /**
     * do the shortcode for ranking preview
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.3
     */
    public static function rankingPreview() {
        if (!isset($_GET['shortcode']) || !isset($_GET['full_shortcode'])) {
            die();
        }

        if (!current_user_can('manage_options')) {
            die();
        }

        $shortcode = $_GET['shortcode'];

        if (!shortcode_exists($shortcode)) {
            die(json_encode(esc_html__('This shortcode was not found.', 'yet-another-stars-rating')));
        }

        $full_shortcode = stripslashes($_GET['full_shortcode']);

        echo json_encode(do_shortcode(wp_kses_post($full_shortcode)));

        die();
    }

}