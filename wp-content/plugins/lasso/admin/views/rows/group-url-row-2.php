<?php
/**
 * Row
 *
 * @package Row
 *
 * This row render by PHP
 */
/** @var object $lasso_url */
/** @var object $lasso_post */
// phpcs:ignore
?>

<!-- SINGLE URL -->
<div class="p-4 text-break black hover-gray cursor-move" data-post-id="<?php echo $lasso_url->lasso_id ?>">
	<div class="row align-items-center">
		<!-- GRIP -->
		<div class="grip">
			<i class="far fa-grip-vertical dark-gray"></i>
		</div>

		<!-- IMAGE -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3">
			<img src="<?php echo $lasso_url->image_src ?>" loading="lazy" class="rounded border" width="50" height="50">
		</div>

		<!-- NAME -->
		<div class="col-lg font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<object class="cursor-pointer">
				<a href="edit.php?post_type=lasso-urls&page=url-details&post_id=<?php echo $lasso_url->lasso_id ?>" class="black hover-purple-text"><?php echo $lasso_url->name ?></a>
			</object>
		</div>

		<!-- PERMALINK -->
		<div class="col-lg text-lg-left text-center pb-lg-0 pb-3">
			/<?php echo $lasso_post->post_name ?>/
		</div>
	</div>
</div>
