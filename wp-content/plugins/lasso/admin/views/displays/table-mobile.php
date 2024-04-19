<?php
/**
 * Table Mobile
 *
 * @package table-mobile
 */

/** @var array $vertical_mobile_items Table items for mobile view */

?>

<div class="table-vertical table-vertical-mobile tb-vt tb-vt-mb template-1" style="box-shadow: none !important;">
<?php foreach ( $vertical_mobile_items as $item ):  ?>
	<div class="mobile-box <?php echo ! empty( $item['badge'] ) ? "has-badge-mobile has-bm" : "" ?>">
		<?php if ( ! empty( $item['badge'] ) ) { ?>
		<div class="badge"> <?php echo $item['badge'] ?> </div>
		<?php }  ?>
		<div class="mobile-item-wrapper mb-i-wp">
		<?php foreach ( $item['fields'] as $item_data ): ?>
			<?php $class = implode(' ', $item_data['class']); ?>
			<div class="mobile-item-child mb-i-chl <?php echo $class ?>">
				<?php echo $item_data['html']; ?>
			</div>
		<?php endforeach; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>