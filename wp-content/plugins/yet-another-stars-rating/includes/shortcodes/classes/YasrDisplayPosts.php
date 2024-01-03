<?php

/**
 * This shortcode must be used in a page, and returns posts ordered by rating.
 * accepted args:
 *  - orderby         Optional, accepts values 'overall', 'vv_average', default 'vv_count'
 *  - sort            Optional, accepts values ASC or asc. Default DESC
 *  - posts_per_page  Optional, default 10
 *
 * @author Dario Curvino <@dudo>
 *
 * @since 3.3.0
 *
 * YasrOrderPosts
 */
class YasrDisplayPosts extends YasrShortcode {

    /**
     * The data source.
     * Default value, vv_count.
     * Accepted values 'vv_average', 'overall'
     */
    public  $orderby;

    /**
     * Default DESC, accepted 'value' ASC and 'asc'
     */
    public  $order;

    /**
     * int between 2 and 20
     */
    public  $posts_per_page;

    /**
     * Query args
     */
    private $query_args;

    /**
     * Number of page, default 1
     */
    private $paged;

    public function __construct($atts, $shortcode_name) {
        parent::__construct($atts, $shortcode_name);

        $this->initMembers($atts, $shortcode_name);
    }

    /**
     * Sanitize shortcode atts and init class members
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.0
     *
     * @param $atts
     * @param $shortcode_name
     *
     * @return void
     */
    public function initMembers ($atts, $shortcode_name) {
        //default page
        $this->paged = 1;

        //if get_query_var('paged'), get the new page
        if (get_query_var('paged')) {
            $this->paged = (int)get_query_var('paged');
        }

        if ($atts !== false) {
            $atts = (shortcode_atts(
                array(
                    'orderby'        => 'vv_count',
                    'order'          => 'DESC',
                    'posts_per_page' => 10
                ), $atts, $shortcode_name
            ));
        }

        $posts_per_page = (int)$atts['posts_per_page'];

        if($atts['order'] !== 'ASC' && $atts['order'] !== 'asc') {
            $atts['order'] = 'DESC';
        }

        if($posts_per_page > 20) {
            $posts_per_page = 20;
        }
        if($posts_per_page < 2 ) {
            $posts_per_page = 2;
        }

        $this->order          = $atts['order'];
        $this->posts_per_page = $posts_per_page;
        $this->query_args     = $this->defaultQuery();

        if($atts['orderby'] === 'overall') {
            $this->orderby = 'overall';
            $this->queryOverall();
        } else if($atts['orderby'] === 'vv_average') {
            $this->orderby = 'vv_average';
        } else {
            $this->orderby = 'vv_count';
        }

    }

    /**
     * Set query args
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.0
     */
    public function defaultQuery () {
        return array(
            'posts_per_page' => $this->posts_per_page,
            'post_status'    => 'publish',
            'paged'          => $this->paged,
            'order'          => $this->order
        );
    }

    /**
     * Adds params to the default query to get data from meta value yasr_overall_rating
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.0
     * @return void
     */
    public function queryOverall () {
        $this->query_args['orderby']  = 'meta_value';
        $this->query_args['meta_key'] = 'yasr_overall_rating';
    }

    /**
     * Filter the default query to get ratings from yasr_visitor_votes
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.0
     * @return void
     */
    public function filterQueryVV () {
        add_action('posts_join_paged', static function($join, $query) {
            $join .= YasrDB::returnQuerySelectPostsVV();
            return $join;
        }, 10, 2);

        add_action('posts_orderby', function() {
            return YasrDB::returnQueryOrderByPostsVV($this->orderby, $this->order);
        }, 10, 2);
    }

    /**
     * Return the shortcode
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.0
     * @return string
     */
    public function returnShortcode() {
        if($this->orderby === 'vv_count' || $this->orderby === 'vv_average') {
            $this->filterQueryVV();
        }

        // The Query
        $the_query = new WP_Query($this->query_args);

        ob_start();

        // The Loop
        if ($the_query->have_posts() ) {
            while ($the_query->have_posts()) : $the_query->the_post();
                $this->content();
            endwhile;

            echo $this->pagination($the_query);

            /* Restore original Post Data */
            wp_reset_postdata();

            return ob_get_clean();
        } else {
            return esc_html__('No posts found', 'yet-another-stars-rating');
        }
    }

    /**
     * Return the shortcode template, using get_template_part provided by Gamajo Template Loader
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.0
     *
     */
    public function content() {
        $templates = new YasrTemplateLoader();

        //this will search for templates in a directory called "yasr" first in the child theme, then in the parent theme;
        // if nothing is found, load yet-another-stars-rating/templates/content.php
        $templates->get_template_part('content');
    }

    /**
     * Return the pagination links
     * https://developer.wordpress.org/reference/functions/paginate_links/
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.0
     *
     * @param $query
     *
     * @return string|string[]|null
     */
    public function pagination ($query) {
        $big = 999999999; // need an unlikely integer

        return paginate_links(
            array(
                'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'  => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total'   => $query->max_num_pages
            )
        );
    }
}