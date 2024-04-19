<?php
/**
 * Field header
 *
 * @package Group header
 */

$page      = $_GET['page'] ?? '';
$post_id   = isset( $_GET['post_id'] ) && $_GET['post_id'] > 0 ? $_GET['post_id'] : 0;
$url_count = isset( $_GET['urls'] ) && $_GET['urls'] > 0 ? $_GET['urls'] : 0;

if ( $url_count > 0 ) {
	$url_count_text = ' <span class="badge px-2 purple-bg white">' . $url_count . '</span>';
} else {
	$url_count_text = '';
}

if ( $post_id > 0 ) {
	$lasso_db         = new Lasso_DB();
	$field             = $lasso_db->get_field( $post_id );
	$field_name        = $field->field_name;
	$field_h1          = $field_name;
	$field_description = $field->field_description;
	$field_type = $field->field_type;
	$create_new_field = false;
} else {
	$field_name        = '';
	$field_h1          = 'Add a New Field';
	$field_description = '';
	$field_type = '';
	$create_new_field = true;
}
?>

<script>
	// COPY SHORTCODE
	function copy_shortcode() {
		// ANIMATE CLICK
		jQuery('#copy-shortcode').addClass('animate-bounce-in').delay(500).queue(function(){
			jQuery(this).removeClass('animate-bounce-in').dequeue();
		});

		jQuery('#copy-shortcode').attr('data-tooltip', 'Copied!');

		var copyText = document.getElementById("shortcode");

		copyText.select();
		copyText.setSelectionRange(0, 99999); /*For mobile devices*/

		document.execCommand("copy");
	}
</script>

<!-- TITLE -->
<div class="row align-items-center mb-3">
	<div class="col-lg text-lg-left text-center">
		<h1 class="m-0"><?php echo $field_h1; ?></h1>
	</div>
</div>

<!-- SUB NAVIGATION & SHORTCODE -->
<div class="row align-items-center mb-4">
	<div class="col-lg">
		<ul class="nav justify-content-lg-start justify-content-center font-weight-bold">
			<?php if ( ! $create_new_field ) { ?>
			<li class="nav-item mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'field-urls' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=field-urls&post_id=<?php echo $post_id; ?>&urls=<?php echo $url_count; ?>">Links<?php echo $url_count_text; ?></a>
			</li>
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'field-details' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=field-details&post_id=<?php echo $post_id; ?>&urls=<?php echo $url_count; ?>">Details</a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
