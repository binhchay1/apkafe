<?php

/**
 * Single
 *
 * @package Single
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Post as Lasso_Post;

use Lasso\Classes\Setting_Enum;
use Lasso\Libraries\Lasso_URL;
use Lasso\Models\Fields;

$theme_name       = $settings['theme_name'] ?? Setting_Enum::THEME_CACTUS;
$theme_name       = strtolower('lasso-' . $theme_name);
$description_text = '';
$title_type_start = '';
$title_type_end   = '';
$anchor_id_html   = empty($category) && $anchor_id ? 'id="' . $anchor_id . '"' : '';

if (isset($is_demo_link)) {
	$lasso_url                      = Lasso_Affiliate_Link::get_lasso_url('');
	$lasso_url                      = clone $lasso_url;
	$last_updated                   = Lasso_Helper::convert_datetime_format(gmdate('Y-m-d H:i:s'), true);
	$lasso_url->display->badge_text = 'Our Pick';
	$custom_css_default             = '';
	$custom_css                     = '';
	$lasso_url->title_target        = '';
	$lasso_url->title_url           = '';
	$link_id                        = 0;
} else {
	if ('' !== $lasso_url->display->theme) {
		$theme_name = strtolower('lasso-' . $lasso_url->display->theme);
	}

	// ? Let theme be overridden in shortcode
	if ('' !== $theme) {
		$theme_name = strtolower('lasso-' . $theme);
	}

	if ('hide' === $badge) {
		$lasso_url->display->badge_text = '';
	} elseif ('' !== $badge) {
		$lasso_url->display->badge_text = $badge;
	}

	if ('hide' === $description) {
		$lasso_url->description = '';
	} elseif ('' !== $description) {
		$lasso_url->description               = $description;
		$lasso_url->display->show_description = 1; // ? Force to show description when client add description attribute
	}

	if ('hide' === $title) {
		$lasso_url->name = '';
	} elseif ('' !== $title) {
		$lasso_url->name = $title;
	}

	if ('' === $image_alt) {
		$image_alt = $lasso_url->name;
	}

	if ('' !== $rating && !$lasso_url->fields->primary_rating) {
		$lasso_url->fields->primary_rating = new stdClass();
	}
	if ('hide' === $rating) {
		$lasso_url->fields->primary_rating->field_value = '';
	} elseif ('' !== $rating) {
		$lasso_url->fields->primary_rating->field_value = $rating;
	}

	// ? customize Pros
	if ('Pros' !== $pros_label && '' === $pros) {	// ? Only custom label
		foreach ($lasso_url->fields->user_created as $key => $field) {
			if (Fields::PROS_FIELD_ID === intval($field->field_id)) {
				$lasso_url->fields->user_created[$key]->field_name = $pros_label;
			}
		}
	} elseif ('' !== $pros) {
		if (!$lasso_url->fields->user_created) {
			$lasso_url->fields->user_created = array();
		}
		$pros_exists = false;
		foreach ($lasso_url->fields->user_created as $key => $field) {
			if (Fields::PROS_FIELD_ID === intval($field->field_id)) {
				$pros_exists = true;
				$lasso_url->fields->user_created[$key]->field_value = $pros;
				$lasso_url->fields->user_created[$key]->field_name = $pros_label;
			}
		}
		if (!$pros_exists) {
			$lasso_url->fields->user_created[] = (object) array(
				'id'          => Fields::PROS_FIELD_ID,
				'field_id'    => Fields::PROS_FIELD_ID,
				'field_name'  => $pros_label,
				'field_value' => $pros,
				'lasso_id'    => $lasso_url->lasso_id,
			);
		}
	}

	// ? customize Cons
	if ('Cons' !== $cons_label && '' === $cons) {	// ? Only custom label
		foreach ($lasso_url->fields->user_created as $key => $field) {
			if (Fields::CONS_FIELD_ID === intval($field->field_id)) {
				$lasso_url->fields->user_created[$key]->field_name = $cons_label;
			}
		}
	} elseif ('' !== $cons) {
		if (!$lasso_url->fields->user_created) {
			$lasso_url->fields->user_created = array();
		}
		$cons_exists = false;
		foreach ($lasso_url->fields->user_created as $key => $field) {
			if (Fields::CONS_FIELD_ID === intval($field->field_id)) {
				$cons_exists = true;
				$lasso_url->fields->user_created[$key]->field_value = $cons;
				$lasso_url->fields->user_created[$key]->field_name = $cons_label;
			}
		}
		if (!$cons_exists) {
			$lasso_url->fields->user_created[] = (object) array(
				'id'          => Fields::CONS_FIELD_ID,
				'field_id'    => Fields::CONS_FIELD_ID,
				'field_name'  => $cons_label,
				'field_value' => $cons,
				'lasso_id'    => $lasso_url->lasso_id,
			);
		}
	}

	// ? Support hide fields for the old customize with "fields" attribute. For now, we change to "field" to stop conflict with AAWP shortcode.
	if ('hide' === $fields || 'hide' === $field) {
		$lasso_url->fields->user_created = '';
	} elseif ('demo' === $fields && isset($show_pros_cons)) {
		if (Lasso_Helper::cast_to_boolean($show_pros_cons)) {
			$lasso_url->fields->user_created = array(
				(object) array(
					'id'    	  => Fields::PROS_FIELD_ID,
					'field_id'    => Fields::PROS_FIELD_ID,
					'field_name'  => 'Pros',
					'field_value' => "Will make you more effective.\nQuick read and highly actionable.",
				),
				(object) array(
					'id'          => Fields::CONS_FIELD_ID,
					'field_id'    => Fields::CONS_FIELD_ID,
					'field_name'  => 'Cons',
					'field_value' => 'You have to be open minded.',
				),
			);
		} else {
			$lasso_url->fields->user_created = array();
		}
	}

	if ('' !== $disclosure_text) {
		$lasso_url->display->show_disclosure = true;
		$lasso_url->display->disclosure_text = $disclosure_text;
	}

	if ('' !== $basis_price) {
		$lasso_url->amazon->discount_pricing_html = '<strike>' . $basis_price . '</strike>';
	}

	// webp URL, fix webp file does not exist
	$webp_url = Lasso_Helper::get_webp_url($lasso_url->lasso_id);

	// ? Lasso image priority: Custom image link => Webp Image => Lasso Image
	$lasso_image = '' !== $image_url ? $image_url : $webp_url;
	$lasso_image = $lasso_image ? $lasso_image : $lasso_url->image_src;

	$lasso_url->public_link                    = '' !== $primary_url ? $primary_url : $lasso_url->public_link;
	$lasso_url->display->primary_button_text   = '' !== $primary_text ? $primary_text : $lasso_url->display->primary_button_text;
	$lasso_url->display->secondary_url         = '' !== $secondary_url ? $secondary_url : $lasso_url->display->secondary_url;
	$lasso_url->display->secondary_button_text = '' !== $secondary_text ? $secondary_text : $lasso_url->display->secondary_button_text;
	$lasso_url->image_src                      = $lasso_image;
	$lasso_url->title_url                      = '' !== $title_url ? $title_url : $lasso_url->public_link;
	$lasso_url->title_target                   = '' !== $title_url ? '' : $lasso_url->html_attribute->target;
	$lasso_url->link_from_display_title        = Lasso_Helper::cast_to_boolean($settings['link_from_display_title']);

	// ? use price in shortcode to override original price
	if ('hide' === $price) {
		$lasso_url->display->show_price = false;
	} elseif ('show' === $price) {
		$lasso_url->display->show_price = true;
	} elseif ('' !== $price) {
		$lasso_url->price               = $price;
		$lasso_url->display->show_price = true;
	}

	// ? use prime in shortcode to override original prime setting
	if ('' !== $prime) {
		$lasso_url->amazon->is_prime = true;
	}

	if (isset($title_type) && '' !== $title_type) {
		$title_type_start = '<' . $title_type . '>';
		$title_type_end   = '</' . $title_type . '>';
	}

	// ? Don't show secondary button if we're currently on the page it links to
	if (url_to_postid($lasso_url->display->secondary_url) === get_the_ID()) {
		$lasso_url->display->secondary_url = '';
	}

	// ? Apply defaults if needed
	$custom_css_default = $settings['custom_css_default'];

	if (Lasso_Link_Location::LINK_TYPE_LASSO === $lasso_url->link_type) {
		$lasso_url->display->show_date = false;
	} else {
		$lasso_url->display->show_date = $lasso_url->display->show_price; // ? show date depends on show price for Amazon product
	}

	// ? Css class that display mobile theme
	// ? If the shortcode inside a widget content, we set the default is mobile
	$css_display_theme_mobile = '';
	if (isset($is_from_widget) && $is_from_widget) {
		$css_display_theme_mobile = 'mobile';
	}
	// ? Override by style attribute if the value is mobile or desktop
	if (isset($style) && in_array(strtolower($style), array('mobile', 'desktop'), true)) {
		if ('mobile' === strtolower($style)) {
			$css_display_theme_mobile = 'mobile';
		} else {
			$css_display_theme_mobile = 'desktop';
		}
	}
}

$lasso_post    = Lasso_Post::create_instance($lasso_url->lasso_id, $lasso_url);
$lasso_url_obj = new Lasso_URL($lasso_url);

if ($sitestripe) {
	include LASSO_PLUGIN_PATH . '/admin/views/displays/sitestripe.php';
} elseif ('list' === $type) {
	include LASSO_PLUGIN_PATH . '/admin/views/displays/list-box.php';
} elseif ('remind' === $type) {
	include LASSO_PLUGIN_PATH . '/admin/views/displays/layout-7-box.php';
} else {
	// ? DISPLAY LAYOUTS BASED ON THEME CHOICE
	if (in_array($theme_name, array('lasso-cutter', 'lasso-flow', 'lasso-geek', 'lasso-splash'), true)) {
		include LASSO_PLUGIN_PATH . '/admin/views/displays/layout-2-box.php';
	}

	if (in_array($theme_name, array('lasso-lab', 'lasso-llama'), true)) {
		include LASSO_PLUGIN_PATH . '/admin/views/displays/layout-3-box.php';
	}

	if (in_array($theme_name, array('lasso-cactus', 'lasso-money'), true)) {
		include LASSO_PLUGIN_PATH . '/admin/views/displays/layout-6-box.php';
	}
}
