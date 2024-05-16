<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to twentytwelve_comment() which is
 * located in the functions.php file.
 */

if ( post_password_required() )
	return;
if (!comments_open())
	return;
?>

<div id="comments" class="comments-area">
    <p class="count-title"><?php echo comments_number('');?></p>
	<div class="comment-form-tm">
	<?php $ycom= __('Your comment ...','leafcolor'); ?>
	<?php comment_form_leaf_custom(array('logged_in_as'=>'','comment_notes_before'=>'','comment_field'=>'
	
	<p class="comment-form-comment"><label for="comment">Your Comment</label><textarea id="comment-user" name="comment" cols="45" rows="8" aria-required="true" aria-label="User comment" onblur="if(this.value == \'\') this.value = \''.__('Your comment ...','leafcolor').'\';" onfocus="if(this.value == \''.__('Your comment ...','leafcolor').'\') this.value = \'\';">'.__('Your comment ...','leafcolor').'</textarea></p>','title_reply'=>'','id_submit'=>'comment-submit')); ?>
    <script type="text/javascript">
		jQuery(document).ready(function(e) {
			jQuery( "#comment-submit" ).click(function() {
				var $a = jQuery("#comment").val();
				var $b = "<?php echo esc_js($ycom) ?>";
				if ( $a == $b){
					jQuery("#comment").val('');
				}
			});
		});	
	</script>
    </div>
	<?php if ( have_comments() ) : ?>
		<ul class="commentlist">
			<?php wp_list_comments( array( 'callback' => 'leafcolor_comment', 'style' => 'ul' ) ); ?>
		</ul>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav id="comment-nav-below" class="navigation" role="navigation">
			<h1 class="assistive-text section-heading"><?php _e( 'Comment navigation', 'leafcolor' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'leafcolor' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'leafcolor' ) ); ?></div>
		</nav>
		<?php endif; ?>
	<?php endif; ?>

</div>