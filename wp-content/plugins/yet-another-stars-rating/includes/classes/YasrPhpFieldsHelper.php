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
 * Class based on https://github.com/Dudo1985/phpFieldsHelper
 *
 * Used as helper to create these html element
 *
 * <input type="text">
 * <select>
 * <textarea>
 * <input type="radio">
 *
 * Class YasrPhpFieldsHelper
 */

if (!class_exists('YasrPhpFieldsHelper') ) {

    class YasrPhpFieldsHelper {

        /**
         * Default class
         *
         * @var string
         */
        public static $field_class;

        public function __construct($field_class = false) {
            if ($field_class) {
                self::$field_class = htmlspecialchars($field_class);
            }
        }

        /**
         * @param bool|string     $title
         * @param bool|string     $class
         * @param array           $options
         * @param bool|string     $name
         * @param bool|string|int $default_value
         * @param bool|string     $id
         * @param string          $autocomplete
         *
         * @return string
         */

        public static function radio(
            $title = false, $class = false, $options = [], $name = false, $default_value = false, $id = false,
            $autocomplete = 'off'
        ) {

            $attribute     = self::escape_attributes($class, $title, $name, $id, $default_value);
            $radio_options = self::escape_array($options);

            $container     = '';
            $end_container = '';
            $title_string  = '';

            if ($attribute['title']) {
                $title_string .= '<strong>' . $attribute['title'] . '</strong><br />';
            }

            if (is_array($radio_options)) {

                if($attribute['class']) {
                    $container_class = $attribute['class'];
                } else {
                    $container_class = 'yasr-indented-answer';
                }

                $container .= '<div class="'.$container_class.'">';

                $radio_fields = '';
                foreach ($radio_options as $value => $label) {
                    $id_string = $attribute['id'] . '-' . strtolower(trim(str_replace(' ', '', $value)));
                    //must be inside foreach, or when loop arrive to last element
                    //checked is defined
                    $checked = '';

                    //escape_attributes use htmlspecialchars that always return a string, so control must be weak
                    /** @noinspection TypeUnsafeComparisonInspection */
                    if ($attribute['value'] == $value) {
                        $checked = 'checked';
                    }

                    //string value must be empty
                    if ($value === 0) {
                        $value = '';
                    }

                    $radio_fields .= sprintf(
                        '<div>
                        <label for="%s">
                            <input type="radio"
                                name="%s"
                                value="%s"
                                class="%s"
                                id="%s"
                                autocomplete="%s"
                                %s
                            >
                            %s
                        </label>
                    </div>', $id_string, $attribute['name'], $value, $attribute['class'], $id_string, $autocomplete,
                        $checked, __(ucfirst($label), 'yet-another-stars-rating')
                    );

                } //end foreach

                $end_container .= '</div>';

                return $container . $title_string . $radio_fields . $end_container;
            }
            return false;
        }

        /**
         * @param bool|string     $class
         * @param bool|string|int $label
         * @param bool|string|int $name
         * @param bool|string|int $id
         * @param bool|string|int $placeholder
         * @param bool|string|int $default_value
         * @param string          $autocomplete
         * @param bool|string     $disabled
         * @param bool|string     $readonly
         *
         * @return string
         */
        public static function text(
            $class = false, $label = false, $name = false, $id = false, $placeholder = false, $default_value = false,
            $autocomplete  = 'off', $disabled = false, $readonly = false
        ) {

            $attribute = self::escape_attributes(
                $class, $label, $name, $id, $default_value, $placeholder,
                $autocomplete, $disabled, $readonly
            );

            $container     = "<div class='$attribute[class]'>";
            $label_string  = "<label for='$attribute[id]'>$attribute[label]</label>";
            $input_text    = "<input type='text' 
                                     name='$attribute[name]' 
                                     id='$attribute[id]' 
                                     value='$attribute[value]'
                                     placeholder='$attribute[placeholder]' 
                                     autocomplete='$autocomplete' 
                                     $attribute[disabled]
                                     $attribute[readonly] />";
            $end_container = "</div>";

            return ($container . $label_string . $input_text . $end_container);
        }

        /**
         * @param bool|string     $class
         * @param bool|string|int $label
         * @param array           $options
         * @param bool|string|int $name
         * @param bool|string|int $id
         * @param bool|string|int $default_value
         * @param string          $autocomplete
         *
         * @return string
         */
        public static function select(
            $class = false, $label = false, $options = [], $name = false, $id = false, $default_value = false,
            $autocomplete = 'off'
        ) {
            $attribute      = self::escape_attributes($class, $label, $name, $id, $default_value);
            $select_options = self::escape_array($options);

            $container     = "<div class='$attribute[class]'>";
            $label_string  = "<label for='$attribute[id]'>$attribute[label]</label>";
            $select        = "<select name='$attribute[name]' id='$attribute[id]' autocomplete=$autocomplete>";
            $end_select    = "</select>";
            $end_container = "</div>";

            $selected = '';
            foreach ($select_options as $key => $option) {
                if ($option === $attribute['value']) {
                    $selected = 'selected';
                }

                $select .= "<option value='$option' $selected>$option</option>";

                //reset
                $selected = '';
            }

            return ($container . $label_string . $select . $end_select . $end_container);
        }

        /**
         * @param bool|string     $class
         * @param bool|string|int $label
         * @param bool|string|int $name
         * @param bool|string|int $id
         * @param bool|string|int $placeholder
         * @param bool|string|int $default_value
         * @param string          $autocomplete
         *
         * @return string
         */
        public static function textArea(
            $class = false, $label = false, $name = false, $id = false, $placeholder = false, $default_value = false,
            $autocomplete = 'off'
        ) {
            $attribute = self::escape_attributes($class, $label, $name, $id, $default_value, $placeholder);

            $container     = "<div class='$attribute[class]'>";
            $label_string  = "<label for='$attribute[id]'>$attribute[label]</label>";
            $textarea      = "<textarea name='$attribute[name]' 
                                id='$attribute[id]' 
                                placeholder='$attribute[placeholder]'
                                autocomplete=$autocomplete>";
            $end_textarea  = "</textarea>";
            $end_container = "</div>";

            return ($container . $label_string . $textarea . $attribute['value'] . $end_textarea . $end_container);
        }


        /**
         * @param bool|string     $class
         * @param bool|string|int $label
         * @param bool|string|int $name
         * @param bool|string|int $id
         * @param bool|string|int $default_value
         * @param bool|string|int $placeholder
         * @param string          $autocomplete
         * @param bool|string    $disabled
         * @param bool|string     $readonly
         *
         * @return array
         */
        private static function escape_attributes(
            $class          = false,
            $label          = false,
            $name           = false,
            $id             = false,
            $default_value  = false,
            $placeholder    = false,
            $autocomplete   = 'off',
            $disabled       = false,
            $readonly       = false
        ) {

            $dbt    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;

            //defalt value
            $title_or_label = 'label';

            if ($caller === 'radio') {
                $title_or_label = 'title';
                if (!$name) {
                    $name = 'radio_group';
                }
            }

            //Use the self::field_class attribute if $class is false or empty
            if (!$class && self::$field_class) {
                $class = self::$field_class;
            }

            //if id is not set but name is, id get same value as name
            if (!$id && $name) {
                $id = $name;
            }
            //viceversa
            elseif (!$name && $id) {
                $name = $id;
            }

            //Use a random string (uniqueid and str_shuffle to add randomness) if id is still empty
            if (!$id) {
                $id = str_shuffle(uniqid(''));
            }

            if($autocomplete !== 'on') {
                $autocomplete = 'off';
            }

            if($disabled !== false && $disabled !== '') {
                $disabled = 'disabled';
            }

            if($readonly !== false) {
                $readonly = 'readonly';
            }

            return array(
                'class'         => htmlspecialchars($class, ENT_QUOTES),
                'id'            => htmlspecialchars($id, ENT_QUOTES),
                $title_or_label => htmlspecialchars($label, ENT_QUOTES),
                'name'          => htmlspecialchars($name, ENT_QUOTES),
                'placeholder'   => htmlspecialchars($placeholder, ENT_QUOTES),
                'value'         => htmlspecialchars($default_value, ENT_QUOTES),
                'autocomplete'  => $autocomplete,
                'disabled'      => $disabled,
                'readonly'      => $readonly
            );
        }

        private static function escape_array($array = []) {
            $cleaned_array = [];
            if (!is_array($array)) {
                return $cleaned_array;
            }

            foreach ($array as $key => $value) {
                $key                 = htmlspecialchars($key, ENT_QUOTES);
                $cleaned_array[$key] = htmlspecialchars($value, ENT_QUOTES);
            }

            return $cleaned_array;
        }

        /**
         * Print HTML Select to change multi set
         *
         * @author Dario Curvino <@dudo>
         *
         * @param             $multi_set
         * @param bool|string $select_text
         * @param bool|string $select_id
         * @param string      $select_on_newline
         * @param bool        $nonce_name
         *
         * @since  3.0.6
         */
        public static function printSelectMultiset(
            $multi_set,
            $select_text = false,
            $select_id = false,
            $select_on_newline = '<br>',
            $nonce_name=false
        ) {
            if ($select_text === false) {
                $select_text = esc_html__('Choose which set you want to use', 'yet-another-stars-rating');
            }
            if ($select_id === false) {
                $select_id = 'yasr_select_set';
            }

            if($nonce_name === false) {
                return;
            }

            $id_nonce = $nonce_name.'-id';
            $nonce = wp_create_nonce($nonce_name);
            ?>
            <div>
                <?php echo esc_html($select_text) ?>
                <?php echo yasr_kses($select_on_newline) ?>
                <label for="<?php echo esc_attr($select_id) ?>">
                    <select id="<?php echo esc_attr($select_id) ?>" autocomplete="off">
                        <?php
                        foreach ($multi_set as $name) {
                            echo "<option value='" . esc_attr($name->set_id) . "'>" . esc_attr($name->set_name)
                                . "</option>";
                        } //End foreach
                        ?>
                    </select>
                </label>
                <input type="hidden" id="<?php echo esc_attr($id_nonce)?>" value="<?php echo esc_html($nonce) ?>">
                <span id="yasr-loader-select-multi-set" style="display:none;">&nbsp;
                    <img src="<?php echo esc_url(YASR_IMG_DIR . "/loader.gif") ?>" alt="yasr-loader">
                </span>
            </div>

            <?php
        }

    }

}
