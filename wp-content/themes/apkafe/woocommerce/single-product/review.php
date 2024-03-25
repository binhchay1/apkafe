<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

global $product, $post;
$ratingCount = $product->get_review_count();
$images = $product->get_gallery_image_ids();
$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
$image       = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'large' ), array(
    'title' => $image_title
) );

?>
<li itemprop="reviews" itemscope itemtype="http://schema.org/Review" <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container">

		<?php echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '', get_comment_author() ); ?>

		<div class="comment-text">

			<?php if ( $rating && get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) : ?>

				<div class="bg-des">
                    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf( __( 'Rated %d out of 5', 'woocommerce' ), $rating ) ?>">
                        <span style="width:<?php echo esc_attr(( $rating / 5 ) * 100); ?>%"><strong itemprop="ratingValue"><?php echo esc_html($rating); ?></strong> <?php _e( 'out of 5', 'woocommerce' ); ?></span>
                    </div>
                    <div itemprop="description" class="description"><?php comment_text(); ?></div>
                    <div itemprop="itemReviewed" itemscope itemtype="https://schema.org/SoftwareApplication">
                        <img  class="lazyloaded" itemprop="image" src="<?php echo esc_url(wp_get_attachment_url(get_post_thumbnail_id())); ?>" alt="<?php the_title(); ?>"/>
                        <span itemprop="name"><?php the_title(); ?> </span>
                        <div  class="hidden" itemprop="aggregateRating " itemscope itemtype="https://schema.org/aggregateRating ">
                            <span itemprop="ratingValue"><?php echo $rating;?></span>
                            <span itemprop="ratingCount"><?php echo $ratingCount;?></span>
                        </div>

                        <div class="hidden" itemprop="offers " itemscope itemtype="https://schema.org/offer">
                            <span itemprop="price ">0</span>
                            <span itemprop="priceCurrency ">USD</span>
                        </div>

                        <span class="hidden" itemprop="operatingSystem">Windows</span>
                        <link itemprop="applicationCategory" content="http://schema.org/BusinessApplication"/>
                    </div>

                </div>
			<?php endif; ?>

			<?php if ( $comment->comment_approved == '0' ) : ?>

				<p class="meta"><em><?php _e( 'Your comment is awaiting approval', 'woocommerce' ); ?></em></p>

			<?php else : ?>

				<p class="meta">
					<strong itemprop="author"><?php comment_author(); ?></strong>
                    <time class="time-cm" itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' ); ?>"><?php echo get_comment_date(  get_option( 'date_format' ) ); ?></time>
				</p>

			<?php endif; ?>

		</div>
	</div>
