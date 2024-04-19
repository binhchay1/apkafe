<?php	
use Lasso\Classes\Helper as Lasso_Helper;
?>

<a target="_blank" href="<?php echo Lasso_Amazon_Api::get_product_review_url( $lasso_url->public_link ) ?>" class="lasso-stars" style="--rating: <?php echo $lasso_url->amazon->rating; ?>">
	<span class="lasso-stars-value">
		<?php echo Lasso_Helper::show_decimal_field_rate( $lasso_url->amazon->rating ); ?>
	</span>
</a>
