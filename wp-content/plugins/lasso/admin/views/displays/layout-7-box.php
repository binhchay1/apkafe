<?php

/** @var bool $is_show_description */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

/** @var bool $is_show_disclosure */
/** @var bool $is_show_fields */
/** @var string $type */

?>

<style>
    .area-detail {
        margin-top: 20px;
    }

    @media screen and (max-width: 992px) {
        .area-detail {
            display: flex;
            flex-wrap: wrap;
            margin-top: 30px;
        }
    }
</style>

<div <?php echo $anchor_id_html ?> class="lasso-container">
    <div class="lasso-display <?php echo $theme_name . ' lasso-url-' . $lasso_url->slug . ' ' . $css_display_theme_mobile ?? ''; ?>">
        <?php if (!empty($lasso_url->display->badge_text)) { ?>
            <div class="lasso-badge">
                <?php echo $lasso_url->display->badge_text; ?>
            </div>
        <?php } ?>

        <div class="lasso-box-1">
            <?php if ($lasso_post->is_show_title()) : ?>
                <?php echo $title_type_start; ?>
                <?php if ($lasso_url->link_from_display_title) : ?>
                    <a class="lasso-title" <?php echo $lasso_url_obj->render_attributes($lasso_url->title_url) ?>>
                        <?php echo html_entity_decode($lasso_url->name); ?>
                    </a>
                <?php else : ?>
                    <span class="lasso-title"><?php echo html_entity_decode($lasso_url->name); ?></span>
                <?php endif; ?>
                <?php echo $title_type_end; ?>
            <?php endif; ?>

            <?php if ($is_show_fields && $lasso_url->fields->primary_rating && ($lasso_url->fields->primary_rating->field_value != '')) { ?>
                <div class="lasso-stars" style="--rating: <?php echo $lasso_url->fields->primary_rating->field_value; ?>">
                    <?php if ('true' === $lasso_url->fields->primary_rating->show_field_name) : ?>
                        <label class="lasso-stars-label float-left mr-1"><strong><?php echo $lasso_url->fields->primary_rating->field_name; ?>:</strong></label>
                    <?php endif; ?>
                    <span class="lasso-stars-value">
                        <?php echo Lasso_Helper::show_decimal_field_rate($lasso_url->fields->primary_rating->field_value); ?>
                    </span>
                </div>
            <?php } ?>

            <?php if (isset($lasso_url->rating)) { ?>
                <div class="lasso-stars" style="--rating: <?php echo $lasso_url->rating; ?>">
                </div>
            <?php } ?>

            <div class="clear"></div>

            <div style="margin-top: 20px;" class="area-detail">
                <?php if ('' === $lasso_url->apple_btn_url && '' === $lasso_url->google_btn_url) { ?>
                    <a class="lasso-button-1" <?php echo $lasso_url_obj->render_attributes() ?>>
                        <?php echo $lasso_url->display->primary_button_text; ?>
                    </a>
                <?php } ?>

                <?php if ('' !== $lasso_url->apple_btn_url) { ?>
                    <a class="lasso-button-apple lasso-btn-shortcode" title="<?php echo $lasso_url->name ?>" href="<?php echo $lasso_url->apple_btn_url ?>" target="_blank" rel="nofollow noopener sponsored">
                        <?php echo $lasso_url->display->apple_btn_text; ?>
                    </a>
                <?php } ?>

                <?php if ('' !== $lasso_url->google_btn_url) { ?>
                    <a class="lasso-button-google lasso-btn-shortcode" title="<?php echo $lasso_url->name ?>" href="<?php echo $lasso_url->google_btn_url ?>" target="_blank" rel="nofollow noopener sponsored">
                        <?php echo $lasso_url->display->google_btn_text; ?>
                    </a>
                <?php } ?>

                <?php if ('' !== $lasso_url->display->secondary_url) { ?>
                    <a class="lasso-button-6 lasso-btn-shortcode" title="<?php echo $lasso_url->name ?>" href="<?php echo $lasso_url->display->secondary_url ?>" target="_blank" rel="nofollow noopener sponsored">
                        <?php echo $lasso_url->display->secondary_button_text; ?>
                    </a>
                <?php } ?>

                <?php if ('' !== $lasso_url->display->third_url) { ?>
                    <a class="lasso-button-3 lasso-btn-shortcode" title="<?php echo $lasso_url->name ?>" href="<?php echo $lasso_url->third_url ?>" target="_blank" rel="nofollow noopener sponsored">
                        <?php echo $lasso_url->display->third_btn_text; ?>
                    </a>
                <?php } ?>

                <?php if ('' !== $lasso_url->display->fourth_url) { ?>
                    <a class="lasso-button-6 lasso-btn-shortcode" title="<?php echo $lasso_url->name ?>" href="<?php echo $lasso_url->fourth_url ?>" target="_blank" rel="nofollow noopener sponsored">
                        <?php echo $lasso_url->display->fourth_btn_text; ?>
                    </a>
                <?php } ?>
            </div>

        </div>

        <div class="lasso-box-2">
            <a class="lasso-image" <?php echo $lasso_url_obj->render_attributes($lasso_url->title_url); ?>>
                <img src="<?php echo $lasso_url->image_src; ?>" height="120" width="120" <?php echo Lasso_Html_Helper::build_img_lazyload_attributes() ?> alt="<?php echo $image_alt; ?>" style="width: 120px !important; height: 120px !important;">
            </a>
        </div>

        <?php if ($is_show_disclosure) : ?>
            <div class="lasso-box-9">
                <div class="lasso-disclosure">
                    <?php
                    if ($lasso_url->display->show_disclosure) {
                        echo "<span>" . $lasso_url->display->disclosure_text . "</span>";
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="lasso-box-6">
            <div class="lasso-date">
                <?php
                if ($lasso_url->display->show_date && $lasso_url->display->show_price && '' !== $lasso_url->price) {
                    echo $lasso_url->display->last_updated . ' <i class="lasso-amazon-info" data-tooltip="Price and availability are accurate as of the date and time indicated and are subject to change."></i>';
                }
                ?>
            </div>
        </div>
        <?php if (Lasso_Helper::is_show_brag_icon()) { ?>
            <div class="lasso-single-brag">
                <?php echo Lasso_Html_Helper::get_brag_icon(); ?>
            </div>
        <?php } ?>
    </div>
</div>