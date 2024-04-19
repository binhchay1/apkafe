<?php
/**
 * List
 *
 * @package List
 */

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

use Lasso\Models\Model;

$lasso_db = new Lasso_DB();

$sql                  = $lasso_db->get_urls_in_group( $category );
$order_by             = 'o.term_order';
$order_type           = 'asc';

// order by latest links
if ( 'true' === $latest ) {
	$order_by   = 'p.ID';
	$order_type = 'desc';
}

$posts_sql            = $lasso_db->set_order( $sql, $order_by, $order_type );
$posts_sql            = ( '' !== $limit ) ? $posts_sql . ' LIMIT ' . $limit : $posts_sql;
$could_get_from_cache = Lasso_Cache_Per_Process::get_instance()->get_cache( Lasso_Shortcode::OBJECT_KEY ) ? true : false;
$urls                 = Model::get_results( $posts_sql, OBJECT, $could_get_from_cache );
$count                = 0;

$list_start = '<ol id="' . $anchor_id . '" class="lasso-list-ol lasso-list-style-decimal">';
$list_end   = '</ol>';

if ( in_array( $bullets, array( 'decimal', 'alpha', 'roman' ), true ) ) {
	$list_start = '<ol id="' . $anchor_id . '" class="lasso-list lasso-list-style-' . strtolower( $bullets ) . '">';
} elseif ( in_array( $bullets, array( 'square', 'circle', 'hide' ), true ) ) {
	$list_start = '<ul id="' . $anchor_id . '" class="lasso-list lasso-list-style-' . strtolower( $bullets ) . '">';
	$list_end   = '</ul>';
}

print $list_start;

foreach ( $urls as $url ) {
	$post_id = $url->ID;
	$count++;

	$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $post_id );
	$lasso_url = Lasso_Affiliate_Link::clone_lasso_url_obj( $lasso_url );
	$image_alt = $url->post_title;

	include LASSO_PLUGIN_PATH . '/admin/views/displays/single.php';
}

print $list_end;
echo Lasso_Html_Helper::get_brag_icon();