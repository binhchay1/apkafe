<?php

/**
 * Header
 *
 * @package Header
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Link_Locations as Model_Link_Locations;
use Lasso\Models\Model;

$new_url  = $_GET['url'] ?? '';
$new_url   = '' !== $new_url ? $new_url : '';
$post_id = $_GET['post_id'] ?? 0;
$post_id = intval($post_id) > 0 ? $post_id : 0;
$is_update = $post_id > 0 ? 1 : 0;

$page_get = $_GET['page'] ?? '';

$post_id_url       = '';
$link_count        = 0;
$display_count     = 0;
$opportunity_count = 0;

$lasso_url = Lasso_Affiliate_Link::get_lasso_url($post_id, true);

// ? Defaults
$amazon_aff       = false;
$category_options = '';
$old_keywords     = '';
$all_categories   = get_terms(
	array(
		'taxonomy'   => LASSO_CATEGORY,
		'hide_empty' => false,
	)
);
$count_categories = count($all_categories);
for ($i = 0; $i < $count_categories; $i++) {
	unset($all_categories[$i]->description);
}

if ($new_url) {
	$lasso_amazon_api = new Lasso_Amazon_Api();
	$lasso_cron       = new Lasso_Cron();
	$link_type        = $lasso_cron->get_link_type(0, $new_url);

	// Check if https:// or http:// exist in string. If missing, add https:// by default.
	$https = strpos($new_url, 'https://');
	$http  = strpos($new_url, 'http://');

	$new_url = (false === $https && false === $http) ? 'https://' . $new_url : $new_url;

	$lasso_url->target_url = $new_url;

	if (LASSO_AMAZON_PRODUCT_TYPE === $link_type) {
		$amazon_aff           = true;
		$amazon_product_id    = Lasso_Amazon_Api::get_product_id_by_url($new_url);
		$is_amazon_configured = Lasso_Amazon_Api::is_configured();
		$error_code           = '';

		if ($is_amazon_configured) {
			$amazon_product = $lasso_amazon_api->fetch_product_info($amazon_product_id, false, false, $lasso_url->target_url);
			$error_code     = $amazon_product['error_code'] ?? $error_code;
		}

		if ((!$is_amazon_configured && '' !== $amazon_product_id) || in_array($error_code, Lasso_Amazon_Api::get_ignore_error_codes(), true)) {
			$res = Lasso_Helper::get_url_status_code_by_broken_link_service($new_url, true);
			if (200 === $res['status_code']) {
				$amazon_tracking_id = Lasso_Setting::lasso_get_setting('amazon_tracking_id', '');
				$amz_url       = $res['response']->url ?? $new_url;
				$img_url       = $res['response']->imgUrl ?? $lasso_url->image_src;
				$img_url       = '' !== $img_url ? $img_url : $lasso_url->image_src;
				$product_name  = $res['response']->productName ?? '';
				$quantity      = $res['response']->quantity ?? 200;
				$m_link        = Lasso_Amazon_Api::get_amazon_product_url($amz_url);

				$lasso_amazon_api->update_amazon_product_in_db(
					array(
						'product_id'  => $amazon_product_id,
						'title'       => $product_name,
						'price'       => '',
						'default_url' => $amz_url,
						'url'         => '' === $amazon_tracking_id ? $new_url : $m_link,
						'image'       => $img_url,
						'quantity'    => intval($quantity),
						'is_manual'   => 1,
					)
				);

				$amazon_product['product']['title'] = $product_name;
				$amazon_product['product']['image'] = $img_url;
				$amazon_product['product']['url']   = $amz_url;
				$amazon_product['product']['price'] = '';
			}
		}

		if (isset($amazon_product['product']['title'])) {
			$lasso_url->name       = $amazon_product['product']['title'];
			$lasso_url->target_url = Lasso_Amazon_Api::get_amazon_product_url($amazon_product['product']['url']);
			$lasso_url->price      = $amazon_product['product']['price'];
			$lasso_url->image_src  = $amazon_product['product']['image'];
		}
	} else {
		$lasso_url->target_url = $new_url;
		$amazon_product_id     = '';

		if (Lasso_Amazon_Api::is_amazon_url($lasso_url->target_url)) {
			$lasso_url->target_url = Lasso_Amazon_Api::get_amazon_product_url($lasso_url->target_url);
		}
	}

	// ? Categories
	foreach ($all_categories as $category) {
		$category_options .= '<option name="affiliate_categories" value="' . $category->term_id . '">' . $category->name . '</option>';
	}
}

if ($post_id >= 0) {
	global $wpdb;
	$lasso_db = new Lasso_DB();


	if ($post_id > 0) {
		if (!$lasso_url) {
			$obj_affiliate = new Lasso_Affiliate_Link();
			echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $obj_affiliate->get_new_affiliate_link_url() . '">';
			die;
		}

		$post_id_url = '&post_id=' . $post_id;
		$link_count  = Model_Link_Locations::total_locations_by_lasso_id($post_id);

		$sql               = $lasso_db->get_link_opportunities_query('', $post_id);
		$opportunity_count = Model::get_count($sql);

		$old_open_new_tab    = $lasso_url->open_new_tab;
		$old_enable_nofollow = $lasso_url->enable_nofollow;

		$old_keywords = implode(',', $lasso_url->keyword);
	}

	// ? Details
	if ('url-details' === $page_get) {
		// ? Keywords
		$keyword_options = '';
		if (isset($lasso_url->keyword)) {
			foreach ($lasso_url->keyword as $keywrd) {
				$selected         = 'selected';
				$keyword_options .= '<option name="affiliate_keywords" value="' . esc_html($keywrd) . '" ' . $selected . '>' . esc_html($keywrd) . '</option>';
			}
		}

		// ? Categories
		if (isset($lasso_url->category)) {
			foreach ($all_categories as $category) {
				$selected = '';
				if (in_array($category->term_id, $lasso_url->category, true)) {
					$selected = 'selected';
				}
				$category_options .= '<option name="affiliate_categories" value="' . $category->term_id . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
			}
		}

		$amazon_product_id = $lasso_url->amazon->amazon_id ?? '';
	}
}

$image_src_box = strpos(($lasso_url->image_src ?? ''), 'lasso-no-thumbnail.jpg') !== false ? '' : ($lasso_url->image_src ?? '');

if ($new_url) {
	$link_type                      = $lasso_cron->get_link_type(0, $new_url);
	$lasso_url->display->badge_text = '';
} else {
	$link_type = $lasso_url->link_type;
}

if ('#' === $lasso_url->permalink) {
	$lasso_url->permalink = '';
}

if ('' === $lasso_url->name) {
	$url_details_h1 = 'Enter a URL Name';
} else {
	$url_details_h1 = esc_html($lasso_url->name);
}

if ($lasso_url->display->disclosure_text_default === $lasso_url->display->disclosure_text) {
	$lasso_url->display->disclosure_text = '';
}
$lasso_url->public_link = esc_html($lasso_url->public_link);
?>

<!-- TITLE -->
<div class="row align-items-center">
	<div class="col-lg text-lg-left text-center mb-4">
		<h1 id="lasso-url-heading" class="font-weight-bold lasso-url-heading"><?php echo $url_details_h1; ?></h1>
		<?php if ('#' !== $lasso_url->public_link) { ?>
			<a class="purple underline mt-2 js-permalink" href="<?php echo $lasso_url->public_link ?? ''; ?>" target="_blank">
				<?php echo $lasso_url->public_link ?? ''; ?></a>
		<?php } ?>
	</div>
</div>

<!-- SUB NAVIGATION -->
<div class="row align-items-center mb-4">
	<div class="col-lg js-sub-nav">
		<ul class="nav font-weight-bold">
			<li class="nav-item mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ('url-details' === $page) {
					echo 'active';
				}
				?>
				" href="edit.php?post_type=lasso-urls&page=url-details<?php echo $post_id_url; ?>">Details</a>
			</li>
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ('url-links' === $page) {
					echo 'active';
				}
				?>
				" href="edit.php?post_type=lasso-urls&page=url-links<?php echo $post_id_url; ?>">
					Locations <span class="badge px-2 purple-bg white"><?php echo $link_count; ?></span></a>
			</li>
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ('url-opportunities' === $page) {
					echo 'active';
				}
				?>
				" href="edit.php?post_type=lasso-urls&page=url-opportunities<?php echo $post_id_url; ?>">
					Opportunities <span class="badge px-2 purple-bg white"><?php echo $opportunity_count; ?></span></a>
			</li>
		</ul>
	</div>

	<div class="col-lg-4 text-right">
		<?php
		if ('url-details' === $page_get) {
		?>
			<input id="shortcode" type="text" style="opacity: 0;" value='[lasso rel="<?php echo esc_html($lasso_url->slug); ?>" id="<?php echo $lasso_url->lasso_id; ?>"]'>
			<a id="copy-shortcode" class="purple d-inline-block" data-tooltip="Copy this Display to your clipboard."><i class="far fa-pager"></i> <strong>Copy Shortcode</strong></a>
		<?php } else { ?>
			<form role="search" method="get" autocomplete="off">
				<div id="search-links">
					<input type="search" class="form-control" placeholder="Search">
				</div>
			</form>
		<?php } ?>
	</div>
</div>