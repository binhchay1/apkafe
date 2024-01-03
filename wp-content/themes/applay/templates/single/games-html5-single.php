<div class="single-post-content-text content-pad">
	<?php

echo '<iframe src="'.get_post_meta( get_the_ID(), 'link', true ).'" width="100%" height="100%" frameborder="0" style="height: 477px; z-index: 99999; "></iframe>';


?>

</div>


<?php
$pagiarg = array(
	'before'           => '<div class="single-post-pagi">'.__( 'Pages:','leafcolor'),
	'after'            => '</div>',
	'link_before'      => '<span type="button" class="btn btn-default btn-sm">',
	'link_after'       => '</span>',
	'next_or_number'   => 'number',
	'separator'        => ' ',
	'nextpagelink'     => __( 'Next page','leafcolor'),
	'previouspagelink' => __( 'Previous page','leafcolor'),
	'pagelink'         => '%',
	'echo'             => 1
);
wp_link_pages($pagiarg); ?>
<div class="clearfix"></div>
<div class="item-meta single-post-meta content-pad">
	<?php if(ot_get_option('enable_author_info')!='off'){ ?>
    <div class="media">
        <div class="pull-left"><i class="fa fa-user"></i></div>
        <div class="media-body">
            <?php _e('Author','leafcolor') ?>
            <div class="media-heading"><span class="vcard author"><span class="fn"><?php the_author_posts_link(); ?></span></span></div>
        </div>
    </div>
    <?php }?>
    <?php if(ot_get_option('single_published_date')!='off'){ ?>
    <div class="media">
        <div class="pull-left"><i class="fa fa-calendar"></i></div>
        <div class="media-body">
            <?php _e('Published','leafcolor') ?>
            <div class="media-heading" rel="bookmark"><time datetime="<?php echo get_the_date('c', get_the_ID());?>" class="entry-date updated"><?php the_time(get_option('date_format')); ?></time></div>
        </div>
    </div>
    <?php }?>
    <?php if(ot_get_option('single_categories')!='off'){ ?>
    <div class="media">
        <div class="pull-left"><i class="fa fa-bookmark"></i></div>
        <div class="media-body">
            <?php _e('Categories','leafcolor') ?>
            <div class="media-heading"><?php the_category(' <span class="dot">.</span> '); ?></div>
        </div>
    </div>
    <?php }?>
    <?php if(ot_get_option('single_tags')!='off' && has_tag()){ ?>
    <div class="media">
        <div class="pull-left"><i class="fa fa-tags"></i></div>
        <div class="media-body">
            <?php _e('Tags','leafcolor') ?>
            <div class="media-heading"><?php the_tags('', ', ', ''); ?></div>
        </div>
    </div>
    <?php }?>
    <?php if(ot_get_option('single_cm_count')!='off'){ ?>
    <?php if(comments_open()){ ?>
    <div class="media">
        <div class="pull-left"><i class="fa fa-comment"></i></div>
        <div class="media-body">
            <?php _e('Comment','leafcolor') ?>
            <div class="media-heading"><a href="#comment"><?php comments_number(__('0 Comments','leafcolor'),__('1 Comment','leafcolor')); ?></a></div>
        </div>
    </div>
	<?php } //check comment open?>
    <?php }?>
</div>
<ul class="list-inline social-light single-post-share">
	<?php leafcolor_social_share(); ?>
</ul>

