<?php
$category = get_the_terms($post->ID, 'product_cat');
$getMeta = get_post_meta($post->ID);
$related = get_posts(array('category__in' => wp_get_post_categories($post->ID), 'numberposts' => 6, 'post__not_in' => array($post->ID)));

$size = [];
$version = [];
$mod_infor = [];
$faq = [];
$sapo = [];
$h1_sapo = [];

if (array_key_exists('size', $getMeta)) {
    $size = $getMeta['size'];
}

if (array_key_exists('latest_version', $getMeta)) {
    $version = $getMeta['latest_version'];
}

if (array_key_exists('mod_infor', $getMeta)) {
    $mod_infor = $getMeta['mod_infor'];
}

if (array_key_exists('size', $getMeta)) {
    $size = $getMeta['size'];
}

if (array_key_exists('_faq', $getMeta)) {
    $faq = $getMeta['_faq'];
}

if (array_key_exists('sapo_default', $getMeta)) {
    $sapo = $getMeta['sapo_default'];
}

if (array_key_exists('h1_sapo', $getMeta)) {
    $h1_sapo = $getMeta['h1_sapo'];
}

?>
<style>
    tr:first-child {
        color: black;
    }

    #content {
        background: none;
    }

    
</style>
<div class="main_bar">
    <div id="article" class="widget">
        <div class="widget_head">
            <ul id="breadcrumbs" class="bread_crumb">
                <li><a href="<?php echo home_url() ?>">Home</a></li>
                <li class="btn-fa-angle"> > </li>
                <li><a href="<?php echo get_category_link($category[0]->term_id) ?>"><?php echo $category[0]->name ?></a></li>
                <li class="btn-fa-angle"> > </li>
                <li><a class="active" href="<?php echo get_permalink(get_the_ID()) ?>"><?php echo the_title() ?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="pad10">
            <h1 class="main_head ac"><?php !empty($h1_sapo) ? print_r($h1_sapo[0]) : '' ?></h1>
            <div class="sapo-review-default">
                <p><?php !empty($sapo) ? print_r($sapo[0]) : '' ?></p>
            </div>
            <div class="main_box_wrap">
                <div class="main_img_wrap">
                    <img id="primaryimage" width="180" height="180" src="<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>" alt="<?php echo the_title() ?>">
                </div>
                <div class="main_spec_wrap">
                    <table class="spec_table">
                        <tbody>
                            <tr>
                                <th>Genre</th>
                                <td><a href="<?php echo get_category_link($category) ?>"><?php echo $category[0]->name ?></a></td>
                            </tr>
                            <tr>
                                <th>Size</th>
                                <td><?php !empty($size) ? print_r($size[0]) : '' ?> MB</td>
                            </tr>
                            <tr>
                                <th>Latest Version</th>
                                <td><?php !empty($version) ? print_r($version[0]) : '' ?></td>
                            </tr>
                            <tr>
                                <th>MOD Info</th>
                                <td><?php !empty($mod_infor) ? print_r($mod_infor[0]) : '' ?></td>
                            </tr>
                            <tr>
                                <th>Update</th>
                                <td><?php echo get_the_modified_date('F j, Y', get_the_ID()) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="clear mb10"></div>
            <div class="ac">
                <a class="btn_download" href="<?php  ?>">
                    Download APK
                    <?php if (!empty($size)) { ?>
                        (<?php print_r($size[0]) ?> MB)
                    <?php } ?>
                </a>
            </div>

            <div class="clear mb20"></div>
            <?php echo the_content() ?>
            <div class="clear"></div>
            <div class="clear mb20"></div>

            <div class="ac mb15 mt15">
                <div id="apk_rate_show_wrap"><span class="rating" id="apk_rate_wrap" data-default-rating="3.92" style="display: inline-block;"><span class="star active"><span class="star active"><span class="star active"><span class="star half active"><span class="star"></span></span></span></span></span></span> <span>3.92 / 5 ( 12 votes )</span></div>
                <div id="apk_rate_msg_wrap"></div>
            </div>

            <div class="fs-19">
                <div id="faq">
                    <h1>FAQ</h1>
                    <ul>
                        <?php if (isset($faq) && !empty($faq)) { ?>
                            <?php $faq = json_decode($faq[0], true) ?>
                            <?php if ($faq != null) { ?>
                                <?php foreach ($faq as $key => $value) { ?>
                                    <?php if ($key == '' || $value == '') {
                                        continue;
                                    } ?>
                                    <li>
                                        <input type="checkbox" checked>
                                        <i></i>
                                        <h2><?php echo $key ?></h2>
                                        <p><?php echo $value ?></p>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="social_sharer">
                <a id="share_facebook" onclick="share_this('share_facebook')" class="facebook" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-facebook"></i> <span>Facebook</span></a>
                <a id="share_twitter" onclick="share_this('share_twitter')" class="twitter" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-twitter"></i><span>Twitter</span></a>
                <a id="share_reddit" onclick="share_this('share_reddit')" class="reddit" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-reddit"></i><span>Reddit</span></a>
                <a id="share_pinterest" onclick="share_this('share_pinterest')" class="pinterest" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-pinterest"></i><span>Pinterest</span></a>
            </div>
        </div>
    </div>
    <div class="clear mb20"></div>
    <div class="clear mb20"></div>
    <div class="widget">
        <h2 class="widget_head">Recommended for you</h2>
        <div class="main_list_item">
            <?php foreach ($related as $post) { ?>
                <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                    <?php echo get_the_post_thumbnail($post->ID) ?>
                    <p class="title"><?php echo get_the_title($post->ID) ?></p>
                    <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    <div id="respond">
        <div id="comments_wrap" class="widget">
            <h2 class="widget_head">Leave a Comment</h2>
            <div class="pad10">
                <form method="post" name="cmnt_form" id="cmnt_form">
                    <div id="cmnt_form_err"></div>
                    <div class="clear mb15"></div>
                    <input type="text" maxlength="50" name="cmnt_name" id="cmnt_name" placeholder="Your Name">
                    <span class="txt_err" id="cmnt_name_err"></span>
                    <div class="clear mb15"></div>
                    <input type="text" maxlength="50" name="cmnt_email" id="cmnt_email" placeholder="Your Email">
                    <span class="txt_err" id="cmnt_email_err"></span>
                    <div class="clear mb15"></div>
                    <div class="ac-textarea">
                        <textarea name="cmnt_text" id="cmnt_text" cols="70" rows="10"></textarea>
                        <span class="txt_err" id="cmnt_text_err"></span>
                    </div>
                    <div class="clear mb15"></div>
                    <div class="ac-submit clearfix">
                        <input type="hidden" name="cmnt_art_id" id="cmnt_art_id" value="cGd1RlgzQUZ4c28zZVhNcmdqbC9nQT09">
                        <input type="hidden" name="cmnt_type" id="cmnt_type" value="0">
                        <input type="hidden" name="cmnt_reply_id" id="cmnt_reply_id" value="0">
                        <input type="hidden" name="cmnt_slang_id" id="cmnt_slang_id" value="">
                        <button onclick="manage_cmnt();" name="submit" type="button">Submit</button>
                    </div>
                </form>
                <ol class="comments-tree-list" id="comments_list_items">
                </ol>
            </div>
        </div>
    </div>
</div>