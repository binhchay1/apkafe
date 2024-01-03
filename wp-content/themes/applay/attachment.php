<?php 
global $global_page_layout;
$layout = $global_page_layout ? $global_page_layout : ot_get_option('page_layout','right');

get_header();
?>
	<?php get_template_part( 'templates/header/header', 'heading' ); ?>   
    <div id="body">
    	<div class="container">
        	<div class="content-pad-4x">
                <div class="row">
                    <div id="content" class="col-md-8<?php if($layout == 'left'){?> revert-layout <?php }?>" role="main">
                        <article class="single-post-content single-content">
                        	<?php
							// The Loop
							while ( have_posts() ) : the_post();
								$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
								foreach ( $attachments as $k => $attachment ) :
									if ( $attachment->ID == $post->ID )
										break;
								endforeach;
								$prev_attachment_id = 0;
								$next_attachment_id = 0;
								$k++;
								// If there is more than 1 attachment in a gallery
								if ( count( $attachments ) > 1 ) :
									if ( isset( $attachments[ $k ] ) ) :
										// get the URL of the next image attachment
										$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
										$next_attachment_id = $attachments[ $k ]->ID;
									else :
										// or get the URL of the first image attachment
										$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
									endif;
								else :
									// or, if there's only 1 image, get the URL of the image
									$next_attachment_url = wp_get_attachment_url();
								endif;
								
								//previous id
								$k-=2;
								if ( count( $attachments ) > 1 ) :
									if ( isset( $attachments[ $k ] ) ) :
										// get the URL of the next image attachment
										$prev_attachment_id = $attachments[ $k ]->ID;
									endif;
								endif;
								
								?>
                                <a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment">
									<?php echo wp_get_attachment_image( $post->ID, 'full' ); ?>
                                </a><br /><br />
                                <?php 
							endwhile;
							?>
                        </article>
                        <?php comments_template( '', true ); ?>
                    </div><!--/content-->
                    <div id="sidebar" class="col-md-4 attachment-content">
                    	<div class="simple-navigation">
                            <div class="row">
                                <div class="simple-navigation-item col-md-6 col-sm-6 col-xs-6">
                                    <?php if($prev_attachment_id){ ?>
                                    	<a href="<?php echo esc_url(get_the_permalink($prev_attachment_id)) ?>"><i class="fa fa-angle-left pull-left"></i><div class="simple-navigation-item-content"><span><?php _e('Previous','leafcolor') ?></span><h4><?php echo get_the_title($prev_attachment_id) ?></h4></div></a>
                                    <?php }?>
                                </div>
                                <div class="simple-navigation-item col-md-6 col-sm-6 col-xs-6 text-right">
                                	<?php if($next_attachment_id){ ?>
                                    	<a href="<?php echo esc_url(get_the_permalink($next_attachment_id)) ?>"><i class="fa fa-angle-right pull-right"></i><div class="simple-navigation-item-content"><span><?php _e('Next','leafcolor') ?></span><h4><?php echo get_the_title($next_attachment_id) ?></h4></div></a>
                                    <?php }?>
                                </div>
                            </div>
                        </div><!--/simple-nav-->
                        <h2><?php the_title() ?></h2>
                        <div class="single-post-content-text content-pad">
							<?php the_content(); ?>
                        </div>
                        <ul class="list-inline social-light">
							<?php leafcolor_social_share(); ?>
                        </ul>
                    </div>
                </div><!--/row-->
            </div><!--/content-pad-->
        </div><!--/container-->
    </div><!--/body-->
<?php get_footer(); ?>