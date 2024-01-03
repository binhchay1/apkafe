<?php 
// blog loop item's thumbnail
$format = get_post_format();
if($format == 'gallery'){ ?>
<div class="item-thumbnail-gallery">
	<?php $images=get_children('post_type=attachment&numberposts=10&post_mime_type=image&post_parent='.get_the_ID());
    if(count($images) > 0){ ?>
        <div class="init-carousel single-carousel post-gallery" id="post-gallery-<?php the_ID() ?>">
            <?php
            foreach((array)$images as $attachment_id => $attachment){
                $image = wp_get_attachment_image_src( $attachment_id, 'thumb_409x258' ); ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <div class='item-thumbnail'>
                    <img src='<?php echo esc_url($image[0]) ?>'>
                    <div class='thumbnail-hoverlay main-color-1-bg'></div>
                    <div class='thumbnail-hoverlay-icon'><i class="fa fa-photo"></i></div>
                    <div class="thumbnail-overflow-2">
                        <div class="date-block-2 dark-div">
                            <div class="day"><?php the_time( 'd' ); ?></div>
                            <div class="month-year">
                                <?php the_time( 'F' ); ?><br />
                                <?php the_time( 'Y' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                </a>
            <?php } ?>
        </div><!--/init-carousel-->
    <?php }else{ ?>
    	<div class='item-thumbnail'>
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php
            if(has_post_thumbnail()){ 
                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),'thumb_409x258', true); 
            }else{
                $thumbnail = ia_print_default_thumbnail();
            }?>
            <img src="<?php echo esc_url($thumbnail[0]) ?>" width="<?php echo esc_attr($thumbnail[1]) ?>" height="<?php echo esc_attr($thumbnail[2]) ?>" title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">
            <div class="thumbnail-hoverlay main-color-1-bg"></div>
            <div class='thumbnail-hoverlay-icon'><i class="fa fa-photo"></i></div>
            <div class="thumbnail-overflow-2">
                <div class="date-block-2 dark-div">
                    <div class="day"><?php the_time( 'd' ); ?></div>
                    <div class="month-year">
                        <?php the_time( 'F' ); ?><br />
                        <?php the_time( 'Y' ); ?>
                    </div>
                </div>
            </div>
        </a>
        </div>
    <?php }?>
</div>
<?php }elseif($format=='video' && !is_search()){
	global $post;
	$has_video = 0;
	preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $post->post_content, $match);
	foreach($match[0] as $amatch){
		if (strpos($amatch,'youtube.com') !== false || strpos($amatch,'vimeo.com') !== false) { ?>
			<div class="item-thumbnail-video">
            	<div class="item-thumbnail-video-inner">
            	<?php echo wp_oembed_get($amatch); ?>
                </div>
            </div>
            <?php $has_video = 1;
			break;
		}
	}
	if(!$has_video){ ?>
    	<div class="item-thumbnail">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php
                if(has_post_thumbnail()){ 
                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),'thumb_409x258', true); 
                }else{
                    $thumbnail = ia_print_default_thumbnail();
                }?>
                <img src="<?php echo esc_url($thumbnail[0]) ?>" width="<?php echo esc_attr($thumbnail[1]) ?>" height="<?php echo esc_attr($thumbnail[2]) ?>" title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">
                <div class="thumbnail-hoverlay main-color-1-bg"></div>
                <div class='thumbnail-hoverlay-icon'><i class="fa fa-play-circle-o"></i></div>
                <div class="thumbnail-overflow-2">
                    <div class="date-block-2 dark-div">
                        <div class="day"><?php the_time( 'd' ); ?></div>
                        <div class="month-year">
                            <?php the_time( 'F' ); ?><br />
                            <?php the_time( 'Y' ); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
	<?php }
}else{ ?>
<div class="item-thumbnail">
    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
        <?php
		if(has_post_thumbnail()){ 
			$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),'thumb_409x258', true); 
		}else{
			$thumbnail = ia_print_default_thumbnail();
		}?>
        <img src="<?php echo esc_url($thumbnail[0]) ?>" width="<?php echo esc_attr($thumbnail[1]) ?>" height="<?php echo esc_attr($thumbnail[2]) ?>" title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">
        <div class="thumbnail-hoverlay main-color-1-bg"></div>
        <div class='thumbnail-hoverlay-icon'><i class="fa fa-<?php if($format=='audio'){?>headphones<?php }else{ ?>search<?php }?>"></i></div>
        <div class="thumbnail-overflow-2">
            <div class="date-block-2 dark-div">
                <div class="day"><?php the_time( 'd' ); ?></div>
                <div class="month-year">
                    <?php the_time( 'F' ); ?><br />
                    <?php the_time( 'Y' ); ?>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<?php /*?><div class="thumbnail-overflow">
    <?php if(comments_open()){ ?>
    <div class="comment-block main-color-1-bg dark-div">
        <a href="<?php the_permalink(); ?>#comment" title="<?php _e('View comments','leafcolor'); ?>">
            <i class="fa fa-comment"></i> <?php echo get_comments_number(); ?>
        </a>
    </div>
    <?php }?>
    <div class="date-block main-color-2-bg dark-div">
        <div class="month"><?php the_time( 'M' ); ?></div>
        <div class="day"><?php the_time( 'd' ); ?></div>
    </div>
</div><?php */?>
