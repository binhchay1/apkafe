<?php
/**
 * Group header
 *
 * @package Group header
 */

use Lasso\Classes\Group as Lasso_Group;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

$page    = $_GET['page'] ?? '';
$post_id = isset( $_GET['post_id'] ) && $_GET['post_id'] > 0 ? $_GET['post_id'] : 0;

$allow_add_a_link = false;
if ( $post_id > 0 ) {
	$lasso_db = new Lasso_DB();

	$term             = get_term( $post_id, LASSO_CATEGORY, ARRAY_A );
	$lasso_group      = Lasso_Group::get_by_id( $post_id );
	$term_name        = $term['name'];
	$term_h1          = $term_name;
	$term_description = $term['description'];
	$slug             = $term['slug'];
	$create_new_group = false;
	$allow_add_a_link = true;
	if ( Lasso_Setting_Enum::PAGE_GROUP_DETAILS === $page ) {
		$allow_add_a_link = false;
	}
	$url_count        = $lasso_group ? $lasso_group->get_total_links() : 0;
	$url_count_text   = ' <span class="badge px-2 purple-bg white" id="group-badge">' . esc_html( $url_count ) . '</span>';
} else {
	$term_name        = '';
	$term_h1          = 'Add a New Group';
	$term_description = '';
	$slug             = '';
	$create_new_group = true;
	$url_count_text = '';
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
		<h1 class="m-0 mr-2 d-inline-block align-middle"><?php echo $term_h1; ?></h1>
		<?php if ( $allow_add_a_link ) : ?>
		<button class="btn ml-1 btn-sm" data-toggle="modal" data-target="#link-monetize">
			<i class="far fa-plus-circle"></i> Add a Link
		</button>
		<?php endif; ?>
	</div>
</div>

<!-- SUB NAVIGATION & SHORTCODE -->
<div class="row align-items-center mb-4">
	<div class="col-lg">
		<ul class="nav justify-content-lg-start justify-content-center font-weight-bold">
			<?php if ( ! $create_new_group ) { ?>
			<li class="nav-item mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'group-urls' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=group-urls&post_id=<?php echo $post_id; ?>&urls=<?php echo $url_count; ?>">Links<?php echo $url_count_text; ?></a>
			</li>
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'group-details' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=group-details&post_id=<?php echo $post_id; ?>&urls=<?php echo $url_count; ?>">Details</a>
			</li>
			<?php } ?>
		</ul>
	</div>

	<?php if ( ! $create_new_group ) { ?>
	<div class="col-lg-4 text-right">
		<input id="shortcode" type="text" style="opacity: 0;" value='[lasso type="grid" category="<?php echo $slug; ?>"]'>
		<a id="copy-shortcode" onclick="copy_shortcode()" class="purple d-inline-block" data-tooltip="Copy this Display to your clipboard."><i class="far fa-pager"></i> <strong>Copy Shortcode</strong></a>
	</div>
	<?php } ?>
</div>
