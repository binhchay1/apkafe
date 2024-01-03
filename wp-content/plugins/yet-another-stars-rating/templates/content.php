<?php
/**
 * Template file for shortcode yasr_display_posts
 *
 * @author Dario Curvino <@dudo>
 * @since  3.3.0
 *
 * To customize this template, create a folder in your child theme named "yasr" and copy it there.
 */

$post_id = get_the_ID();

$thumb = '';
if (has_post_thumbnail($post_id) === true) {
    $thumb = '<div class="yasr-post-thumbnail">
                  <a href="'.esc_url(get_the_permalink()).'">
                      '.get_the_post_thumbnail($post_id, 'thumbnail', array( 'class' => 'alignleft' ) ).'
                  </a>
              </div>';
}

?>
<div>
    <?php
        /**
         * hook here to add content at the beginning of yasr_display_posts
         */
        do_action('yasr_display_posts_top', $post_id);
    ?>
    <h3 class='yasr-entry-title'>
        <a href="<?php the_permalink()?>" rel='bookmark'>
            <?php echo esc_html(get_post_field( 'post_title', $post_id, 'raw' )) ?>
        </a>
    </h3>
    <div class='yasr-entry-meta'>
        <!-- Keep in the same line, or it will create a white space before between the_author and </a> -->
        <a href='<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID')))?>'><?php the_author()?></a>
        <span>
            <?php the_date() ?>
        </span>
        <span class="edit-link" style="display: inline;">
            <?php edit_post_link(__( 'Edit', 'yet-another-stars-rating' )); ?>
        </span>
    </div> <!-- End .entry-meta -->
    <div class='yasr-entry-content'>
        <?php
            the_content(
                sprintf(
                    /* translators: %s: Post title. Only visible to screen readers. */
                    __('Continue reading %s <span class="meta-nav">&rarr;</span>', 'yet-another-stars-rating'),
                    the_title( '<span class="screen-reader-text">', '</span>', false )
                )
            );
        ?>
    </div>

    <?php
        /**
         * hook here to add content at the end of yasr_display_posts
         */
        do_action('yasr_display_posts_bottom', $post_id);
    ?>
</div>
