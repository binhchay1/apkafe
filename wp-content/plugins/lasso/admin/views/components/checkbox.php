<?php
/** @var string $name */
/** @var string $value */
/** @var string $label */
/** @var string $checked */
/** @var array  $addition_attrs */
$attr_html = '';
foreach ( $addition_attrs as $key => $value ) {
	$data_attribute = '%s="%s"';
	$attr_html     .= sprintf( $data_attribute, $key, $value ) . ' ';
}
?>
<label class="toggle m-0 mr-1">
	<input type="checkbox" name="<?php echo esc_html( $name ) ?>" value="<?php echo esc_html( $value ) ?>" <?php echo $checked . ' ' . $attr_html ?> >
	<span class="slider"></span>
</label>
<label class="m-0"><?php echo $label ?></label>
