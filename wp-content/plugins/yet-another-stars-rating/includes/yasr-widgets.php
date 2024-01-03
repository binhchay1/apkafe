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

add_action('widgets_init', 'yasr_init_widgets');

// register Yasr Overall Rating widget
function yasr_init_widgets() {
    register_widget('Yasr_Overall_Rating_Widget');
    register_widget('Yasr_Visitor_Votes_Widget');
    register_widget('Yasr_Recent_Ratings_Widget');
}

/**
 * Adds Yasr_Overall_Rating_Widget widget.
 */
class Yasr_Overall_Rating_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'yasr_overall_rating_widget', // Base ID
            __('Yasr Overall Rating', 'yet-another-stars-rating'), // Name
            array(
                'description' => __('Display Overall Rating', 'yet-another-stars-rating'),
                'size'        => 'large'
            ) // Args
        );

    }

    /**
     * Front-end display of widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     *
     * @see WP_Widget::widget()
     *
     */

    public function widget($args, $instance) {

        if (is_singular() && is_main_query()) {
            echo wp_kses_post($args['before_widget']);

            if (!empty($instance['title'])) {
                echo wp_kses_post($args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']);
            }

            $size = array();
            $size['size'] = $instance['size'];

            $widget_overall_rating = shortcode_overall_rating_callback($size);

            echo wp_kses_post($widget_overall_rating);
            echo wp_kses_post($args['after_widget']);

        }

    }


    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     *
     * @see WP_Widget::form()
     *
     */

    public function form($instance) {

        if (!empty($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Our Rating:', 'yet-another-stars-rating');
        }

        if (empty($instance['size'])) {
            $size = 'large';
        } else {
            $size = $instance['size'];
        }

        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <legend>
                <?php esc_html_e('Size:', 'yet-another-stars-rating'); ?>
            </legend>
            <label>
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="small" <?php if ($size === 'small') {echo 'checked';} ?>
                >
                <?php esc_html_e('Small', 'yet-another-stars-rating'); ?>
            </label>

            <br/>

            <label>
                <input type="radio" name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="medium" <?php if ($size === 'medium') {echo 'checked';} ?> >
                <?php esc_html_e('Medium', 'yet-another-stars-rating'); ?>
            </label>
            <br/>

            <label>
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="large" <?php if ($size === 'large') {echo 'checked';} ?> >
                <?php esc_html_e('Large', 'yet-another-stars-rating'); ?>
            </label>
        </p>

        <?php

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     * @see WP_Widget::update()
     *
     */
    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        if (!$instance) {
            $instance = array();
        }

        if (!empty($new_instance['title'])) {
            $instance['title'] = strip_tags($new_instance['title']);
        } else {
            $instance['title'] = '';
        }

        if (!empty($new_instance['size'])) {
            $instance['size'] = strip_tags($new_instance['size']);
        } else {
            $instance['size'] = 'large';
        }

        return $instance;
    }

} // class Yasr Overall Rating widget


////////////////////////////////////////////////////////


class Yasr_Visitor_Votes_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {

        parent::__construct(
            'yasr_visitor_votes_widget', // Base ID
            __('Yasr Visitor Votes', 'yet-another-stars-rating'), // Name
            array(
                'description' => __('Show Yasr Visitor Votes', 'yet-another-stars-rating'),
                'readonly'    => 'no',
                'size'        => 'large'
            ) // Args
        );

    }

    /**
     * Front-end display of widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     *
     * @see WP_Widget::widget()
     *
     */

    public function widget($args, $instance) {

        if (is_singular() && is_main_query()) {
            echo wp_kses_post($args['before_widget']);

            if (!empty($instance['title'])) {
                echo wp_kses_post($args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']);
            }

            $atts = array();
            $atts['size'] = $instance['size'];
            if ($instance['readonly'] === 'yes') {
                $atts['readonly'] = $instance['readonly'];
            }

            $widget_visitor_votes = shortcode_visitor_votes_callback($atts);

            echo $widget_visitor_votes;

            echo wp_kses_post($args['after_widget']);

        }

    }


    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     *
     * @see WP_Widget::form()
     *
     */

    public function form($instance) {

        if (!empty($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Rate This:', 'yet-another-stars-rating');
        }

        if (empty($instance['readonly'])) {
            $readonly = 'no';
        }

        else {
            $readonly = 'yes';
        }

        if (empty($instance['size'])) {
            $size = 'large';
        } else {
            $size = $instance['size'];
        }

        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('readonly')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('readonly')); ?>"
                   value="yes" <?php if ($readonly === 'yes') { echo " checked='checked' ";} ?>
            >
            <label for="<?php echo esc_attr($this->get_field_id('readonly')); ?>">Readonly?</label>
        </p>

        <p>
            <legend><?php esc_html_e('Size:', 'yet-another-stars-rating'); ?></legend>
            <label>
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="small" <?php if ($size === 'small') {echo 'checked';} ?> >
                <?php esc_html_e('Small', 'yet-another-stars-rating'); ?>
            </label>
            <br/>

            <label>
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="medium" <?php if ($size === 'medium') {echo 'checked';} ?> >
                <?php esc_html_e('Medium', 'yet-another-stars-rating'); ?>
            </label>

            <br/>
            <label>
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('size')); ?>"
                       value="large" <?php if ($size === 'large') {echo 'checked';} ?>
                >
                <?php esc_html_e('Large', 'yet-another-stars-rating'); ?>
            </label>
        </p>

        <?php

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     * @see WP_Widget::update()
     *
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;

        if (!$instance) {
            $instance = array();
        }

        if (!empty($new_instance['title'])) {
            $instance['title'] = strip_tags($new_instance['title']);
        } else {
            $instance['title'] = '';
        }

        if (!empty($new_instance['readonly'])) {
            $instance['readonly'] = strip_tags($new_instance['readonly']);
        } else {
            $instance['readonly'] = false;
        }

        if (!empty($new_instance['size'])) {
            $instance['size'] = strip_tags($new_instance['size']);
        } else {
            $instance['size'] = 'large';
        }


        return $instance;
    }

} // class Yasr Visitor Votes widget


/////////////////////////////////////////////////////////

class Yasr_Recent_Ratings_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {

        parent::__construct(
            'yasr_recent_ratings_widget', // Base ID
            __('Yasr Recent Ratings', 'yet-another-stars-rating'), // Name
            array(
                'description' => __('Show the 5 most recent rated posts', 'yet-another-stars-rating'),
                'readonly'    => 'no',
                'size'        => 'large'
            ) // Args
        );

    }

    /**
     * Front-end display of widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     *
     * @see WP_Widget::widget()
     *
     */

    public function widget($args, $instance) {

        echo wp_kses_post($args['before_widget']);

        if (!empty($instance['title'])) {
            echo wp_kses_post($args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']);
        }

        global $wpdb;

        $log_result = $wpdb->get_results("SELECT post_id, vote, user_id FROM "
                                         . YASR_LOG_TABLE .
                                         " ORDER BY date DESC LIMIT 5");

        $widget_recent_ratings = "<table class=\"yasr-widget-recent-ratings-table\">";

        if ($log_result) {

            foreach ($log_result as $result) {
                $user = get_user_by('id', $result->user_id);

                //If ! user means that the vote are anonymous
                if ($user == false) {
                    $user             = (object) array('user_login');
                    $user->user_login = __('anonymous');
                }

                $title_post = wp_strip_all_tags(get_the_title($result->post_id));
                $link       = get_permalink($result->post_id);
                $vote       = round($result->vote);

                $widget_recent_ratings .= '<tr>
											<td class="yasr-widget-recent-ratings-td">';

                $widget_recent_ratings .=
                    sprintf(
                        __('Vote %s from %s on', 'yet-another-stars-rating'),
                        '<span class="yasr-widget-recent-ratings-text yasr-widget-recent-ratings-vote">'
                            . $vote .
                        '</span>
                        <span class="yasr-widget-recent-ratings-from-user">',
                        '<span class="yasr-widget-recent-ratings-text yasr-widget-recent-ratings-user">'
                            . $user->user_login .
                            '</span>
                        </span>'
                    )
                    . '<span class="yasr-widget-recent-ratings-text yasr-widget-recent-ratings-title">
                           <a href="' . $link . '"> '. $title_post .'</a>
                       </span>';

                $widget_recent_ratings .= '</td>
										</tr>';

            } //End foreach

        } else {
            $widget_recent_ratings .= '<tr>
									    <td>
									    '. __('No recent ratings yet' , 'yet-another-stars-rating') .'
									    </td>
									   </tr>';
        }

        $widget_recent_ratings .= "</table>";

        echo wp_kses_post($widget_recent_ratings);
        echo wp_kses_post($args['after_widget']);

    }


    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     *
     * @see WP_Widget::form()
     *
     */

    public function form($instance) {

        if (!empty($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Recent Ratings', 'yet-another-stars-rating');
        }

        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>

        <?php

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     * @see WP_Widget::update()
     *
     */
    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        if (!$instance) {
            $instance = array();
        }

        if (!empty($new_instance['title'])) {
            $instance['title'] = strip_tags($new_instance['title']);
        } else {
            $instance['title'] = '';
        }

        return $instance;
    }

} // class Yasr Visitor Votes widget