<?php

class CloudarcadeGameListWidget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'ca_game_list_widget',
            'CloudArcade Game List',
            array( 'description' => 'A Widget to display game list' )
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $list_amount = isset($instance['list_amount']) ? $instance['list_amount'] : 5;
        $layout = isset($instance['layout']) ? $instance['layout'] : 'vertical';

        $order_by = isset($instance['order_by']) ? $instance['order_by'] : 'date';
        $query_args = array(
            'post_type' => 'game',
            'posts_per_page' => $list_amount,
            'orderby' => $order_by == 'random' ? 'rand' : $order_by
        );

        $game_query = new WP_Query($query_args);
        if($game_query->have_posts()):
            echo '<ul class="cloudarcade-widget game-list-' . esc_attr($layout) . '">';
            while($game_query->have_posts()): $game_query->the_post();
                if($instance['layout'] == 'vertical'){
                    ?>
                    <li>
                        <a href="<?php echo get_the_permalink() ?>">
                            <div class="game-item-widget">
                                <div class="w-ca-game-thumbnail">
                                    <img src="<?php echo esc_url(ca_get_thumbnail(get_the_ID()), 'small'); ?>" alt="<?php the_title_attribute(); ?>">
                                </div>
                                <div class="w-ca-game-info">
                                    <div class="w-ca-game-title">
                                        <?php echo get_the_title() ?>
                                    </div>
                                    <div class="w-ca-game-category">
                                        <?php echo get_game_categories_str(get_the_ID()); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php
                } else if($instance['layout'] == 'grid'){
                    ?>
                    <li>
                        <a href="<?php echo get_the_permalink() ?>">
                            <div class="game-item-widget">
                                <div class="w-ca-game-thumbnail-grid">
                                    <img src="<?php echo esc_url(ca_get_thumbnail(get_the_ID()), 'small'); ?>" alt="<?php the_title_attribute(); ?>">
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php
                }
            endwhile;
            echo '</ul>';
        endif;
        wp_reset_postdata();

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $list_amount = !empty($instance['list_amount']) ? $instance['list_amount'] : 5;
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'vertical';
        $order_by = !empty($instance['order_by']) ? $instance['order_by'] : 'date';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('list_amount'); ?>">List Amount:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('list_amount'); ?>" name="<?php echo $this->get_field_name('list_amount'); ?>" type="number" value="<?php echo esc_attr($list_amount); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('order_by'); ?>">Order By:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>">
                <option value="date" <?php selected($order_by, 'date'); ?>>Newest</option>
                <option value="meta_value_num" <?php selected($order_by, 'meta_value_num'); ?>>Popularity</option>
                <option value="random" <?php selected($order_by, 'random'); ?>>Random</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>">Layout:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="vertical" <?php selected($layout, 'vertical'); ?>>Vertical</option>
                <option value="grid" <?php selected($layout, 'grid'); ?>>Grid</option>
            </select>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['list_amount'] = (!empty($new_instance['list_amount'])) ? intval($new_instance['list_amount']) : 5;
        $instance['layout'] = (!empty($new_instance['layout'])) ? strip_tags($new_instance['layout']) : 'vertical';
        $instance['order_by'] = (!empty($new_instance['order_by'])) ? strip_tags($new_instance['order_by']) : 'date';
        return $instance;
    }
}

// Register the widget
function register_ca_game_list_widget() {
    register_widget('CloudarcadeGameListWidget');
}
add_action('widgets_init', 'register_ca_game_list_widget');