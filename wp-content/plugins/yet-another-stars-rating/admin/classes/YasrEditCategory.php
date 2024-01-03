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
 * Class to manage the category page in dashboard
 *
 * @author Dario Curvino <@dudo>
 * @since  2.9.0
 * Class YasrEditCategory
 */
class YasrEditCategory {

    /**
     * Init YasrEditCategory class
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.0
     */
    public function init() {
        add_action('category_edit_form_fields', array($this, 'categoryEditFormFields'), 10, 2 );
    }

    /**
     * Callback for hook category_edit_form_fields
     *
     * This method adds a select when a category is edited, and, if PRO version is enabled,
     * is possible to set the same itemType for the current category
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.0
     *
     * @param $term
     */
    public function categoryEditFormFields($term) {
        $term_id  = 1;
        $disabled = true;

        if (YASR_LOCKED_FEATURE_HTML_ATTRIBUTE !== 'disabled') {
            $term_id  = (int)$term->term_id;
            $disabled = false;
        }

        ?>
        <tr class="form-field term-name-wrap">
        <th scope="row">
            <label for="yasr-default-itemtype-category">
                <?php esc_html_e( 'Select default itemType', 'yet-another-stars-rating' ) ?>
            </label>
            <?php echo YASR_LOCKED_FEATURE ?>

            <span class="description">
                <?php
                    echo YASR_LOCKED_TEXT;
                ?>
            </span>
        </th>
        <td>
            <?php yasr_select_itemtype('yasr-pro-select-itemtype-category', false, false, $term_id, $disabled); ?>
            <p></p>
            <label for="yasr-pro-checkbox-itemtype-category" class="yasr-indented-answer">
                <input type="checkbox"
                    name="yasr-pro-checkbox-itemtype-category"
                    id="yasr-pro-checkbox-itemtype-category"
                       <?php echo YASR_LOCKED_FEATURE_HTML_ATTRIBUTE; ?>
                >
                <span class="description">
                    <?php esc_html_e('Check to update YASR itemType', 'yet-another-stars-rating') ?>
                </span>
            </label>
            <p class="description">
                <?php esc_html_e(
                    'This will overwrite YASR itemType in all existing posts or pages for this category ',
                    'yasr-pro')
                ?>
            </p>
        </td>
    </tr >
    <?php
    }
}
