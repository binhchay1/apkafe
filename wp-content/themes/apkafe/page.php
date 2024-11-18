<?php
$custom_section = ot_get_option('custom_section');
$get_post = new WP_Query(array(
    'posts_per_page' => 24,
    'orderby'     => 'modified',
    'order'       => 'DESC',
    'post_type' => 'product',
));

global $wpdb;

$checkCategoryBlog = category_exists('Blog');
$checkCategoryTipsAndroid = category_exists('Tips Android');
$checkCategoryNewsTech = category_exists('News Tech');

get_header();
?>

<div class="container">
    <div class="main_bar">
        <div class="cnt_box">
            <h1><?php echo ot_get_option('homepage_title_short_description') ?></h1>
            <p><?php echo ot_get_option('homepage_short_description') ?></p>
        </div>
        <div class="widget">
            <h2 class="widget_head">Latest Update</h2>
            <div id="main_list_item" class="main_list_item">
                <?php foreach ($get_post->posts as $post) {
                    $_product = wc_get_product($post->ID);
                    $_product_title = $_product->get_title();
                    $rating = '';
                    $developer = '';

                    $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'lasso-urls'", $_product_title));
                    foreach ($res as $postRes) {
                        $rating = get_post_meta($postRes->ID, 'rating');
                        $developer = get_post_meta($postRes->ID, 'developer');

                        break;
                    } 
                    
                    ?>

                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                        <?php echo get_the_post_thumbnail($post->ID); ?>
                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                        <div class="infor-rating-and-developer">
                            <?php if (!empty($rating)) { ?>
                                <span class="infor-rating" style="--rating:<?php echo $rating[0] ?>;"></span>
                            <?php } ?>

                            <?php if (!empty($developer)) { ?>
                                <span><?php echo $developer[0] ?></span>
                            <?php } ?>
                        </div>
                    </a>
                <?php } ?>
            </div>
            <div class="clear mb10"></div>
        </div>
        <div class="clear mb10"></div>

        <?php if (!empty($custom_section)) { ?>
            <?php foreach ($custom_section as $section) { ?>
                <div class="widget">
                    <h2 class="widget_head"><?php echo $section['title'] ?></h2>
                    <div class="main_list_item">
                        <?php $isParent = false; ?>
                        <?php foreach ($section['post_select'] as $post_id) { ?>
                            <?php $terms = get_the_terms($post_id, 'product_cat');
                            $_product = wc_get_product($post_id);
                            if ($_product) {
                                $_product_title = $_product->get_title();

                                $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'lasso-urls'", $_product_title));

                                foreach ($res as $postRes) {
                                    $rating = get_post_meta($postRes->ID, 'rating', false);
                                    $developer = get_post_meta($postRes->ID, 'developer', false);
                                    break;
                                }

                                foreach ($terms as $itemTerm) {
                                    if ($itemTerm->parent != 0) {
                                        continue;
                                    }

                                    $isParent = true;
                                    $finalTermID = $itemTerm->term_id;
                                }
                            ?>

                                <a class="side_list_item" href="<?php echo get_permalink($post_id) ?>">
                                    <?php echo get_the_post_thumbnail($post_id) ?>
                                    <p class="title"><?php echo get_the_title($post_id) ?></p>
                                    <div class="infor-rating-and-developer">
                                        <?php if (!empty($rating)) { ?>
                                            <span class="infor-rating" style="--rating:<?php echo $rating[0] ?>;"></span>
                                        <?php } ?>
                                        <?php if (!empty($developer)) { ?>
                                            <span><?php echo $developer[0] ?></span>
                                        <?php } ?>
                                    </div>

                                </a>
                                <div class="clear mb10"></div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="d-flex justify-content-center" style="margin-top: 15px; margin-bottom: 15px">
                        <a href="<?php echo get_term_link($finalTermID) ?>" class="btn-back-to-home" style="color: #000">Read more</a>
                    </div>
                </div>
                <div class="clear mb10"></div>
            <?php } ?>
        <?php } ?>

        <div class="cnt_box pad10">
            <h2><strong><span class="s4"><?php echo ot_get_option('homepage_title_description') ?></span></strong></h2>
            <?php echo ot_get_option('homepage_description') ?>
        </div>
        <div class="clear"></div>

        <?php if ($checkCategoryBlog != '') { ?>
            <?php $getSectionBlog = ot_get_option('section_blog');
            $listSectionBlog = explode(',', $getSectionBlog);
            ?>
            <div class="widget">
                <div class="secbox sbhomeblogs box-space">
                    <div class="sbheader box-header">
                        <div class="bg">
                            <div class="sbtitle">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="26.926" height="26.924" viewBox="0 0 26.926 26.924">
                                    <g transform="translate(0 0)">
                                        <path d="M26.5,206.733a.953.953,0,0,0-.084-.05l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.506,206.683a.952.952,0,0,0-.395,1.287.962.962,0,0,0,.05.084l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,206.733Z" transform="translate(0 -194.066)" fill="#fff"></path>
                                        <path d="M26.823,309.472a.961.961,0,0,0-.389-.389l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.52,309.083a.962.962,0,0,0,0,1.692l12.5,6.731a.962.962,0,0,0,.912,0l12.5-6.731A.962.962,0,0,0,26.823,309.472Z" transform="translate(-0.014 -290.697)" fill="#fff"></path>
                                        <path d="M26.5,6.905a.952.952,0,0,0-.084-.05L13.918.124a.962.962,0,0,0-.912,0L.506,6.855A.952.952,0,0,0,.161,8.226l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,6.905Z" transform="translate(0 -0.009)" fill="#fff"></path>
                                    </g>
                                </svg>
                                <span>Blogs</span>
                            </div>
                        </div>
                    </div>
                    <div class="sbbody">
                        <ul class="blogs w3">
                            <?php foreach ($listSectionBlog as $sectionBlog) { ?>
                                <?php $postIDBlog = url_to_postid($sectionBlog);
                                $getPostBlog = get_post($postIDBlog);
                                $postThumbnailBlogUrl = get_the_post_thumbnail_url($postIDBlog);
                                $shortDescriptionBlog = get_post_meta($postIDBlog, '_yoast_wpseo_metadesc');
                                ?>
                                <li>
                                    <a class="blog" href="<?php echo $sectionBlog ?>" title="<?php echo $getPostBlog->post_title ?>">
                                        <figure>
                                            <img class="thumb" src="<?php echo $postThumbnailBlogUrl ?>" alt="<?php echo $getPostBlog->post_title ?>">
                                        </figure>
                                        <div class="info">
                                            <div class="title"><?php echo $getPostBlog->post_title ?></div>
                                            <div class="description">
                                                <?php if (!empty($shortDescriptionBlog)) {
                                                    echo $shortDescriptionBlog[0];
                                                } ?>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>

        <?php } ?>

        <?php if ($checkCategoryTipsAndroid != '') { ?>
            <?php $getSectionTipsAndroid = ot_get_option('section_tips_and_android');

            $listSectionTipsAndroid = explode(',', $getSectionTipsAndroid);
            ?>
            <div class="widget">
                <div class="secbox sbhomeblogs box-space">
                    <div class="sbheader box-header">
                        <div class="bg">
                            <div class="sbtitle">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="26.926" height="26.924" viewBox="0 0 26.926 26.924">
                                    <g transform="translate(0 0)">
                                        <path d="M26.5,206.733a.953.953,0,0,0-.084-.05l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.506,206.683a.952.952,0,0,0-.395,1.287.962.962,0,0,0,.05.084l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,206.733Z" transform="translate(0 -194.066)" fill="#fff"></path>
                                        <path d="M26.823,309.472a.961.961,0,0,0-.389-.389l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.52,309.083a.962.962,0,0,0,0,1.692l12.5,6.731a.962.962,0,0,0,.912,0l12.5-6.731A.962.962,0,0,0,26.823,309.472Z" transform="translate(-0.014 -290.697)" fill="#fff"></path>
                                        <path d="M26.5,6.905a.952.952,0,0,0-.084-.05L13.918.124a.962.962,0,0,0-.912,0L.506,6.855A.952.952,0,0,0,.161,8.226l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,6.905Z" transform="translate(0 -0.009)" fill="#fff"></path>
                                    </g>
                                </svg>
                                <span>Tips Android</span>
                            </div>
                        </div>
                    </div>
                    <div class="sbbody">
                        <ul class="blogs w3">
                            <?php foreach ($listSectionTipsAndroid as $sectionTipsAndroid) { ?>
                                <?php $postIDTipsAndroid = url_to_postid($sectionTipsAndroid);
                                $getPostTipsAndroid = get_post($postIDTipsAndroid);
                                $postThumbnailTipsAndroidUrl = get_the_post_thumbnail_url($postIDTipsAndroid);
                                $shortDescriptionTipsAndroid = get_post_meta($postIDTipsAndroid, '_yoast_wpseo_metadesc');
                                ?>
                                <li>
                                    <a class="blog" href="<?php echo $sectionTipsAndroid ?>" title="<?php echo $getPostTipsAndroid->post_title ?>">
                                        <figure>
                                            <img class="thumb" src="<?php echo $postThumbnailTipsAndroidUrl ?>" alt="<?php echo $getPostTipsAndroid->post_title ?>">
                                        </figure>
                                        <div class="info">
                                            <div class="title"><?php echo $getPostTipsAndroid->post_title ?></div>
                                            <div class="description">
                                                <?php if (!empty($shortDescriptionTipsAndroid)) {
                                                    echo $shortDescriptionTipsAndroid[0];
                                                } ?>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ($checkCategoryNewsTech != '') { ?>
            <?php $getSectionNewsTech = ot_get_option('section_news_tech');
            $listSectionNewsTech = explode(',', $getSectionNewsTech);
            ?>
            <div class="widget">
                <div class="secbox sbhomeblogs box-space">
                    <div class="sbheader box-header">
                        <div class="bg">
                            <div class="sbtitle">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="26.926" height="26.924" viewBox="0 0 26.926 26.924">
                                    <g transform="translate(0 0)">
                                        <path d="M26.5,206.733a.953.953,0,0,0-.084-.05l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.506,206.683a.952.952,0,0,0-.395,1.287.962.962,0,0,0,.05.084l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,206.733Z" transform="translate(0 -194.066)" fill="#fff"></path>
                                        <path d="M26.823,309.472a.961.961,0,0,0-.389-.389l-1.911-1.029-10.6,5.61a.962.962,0,0,1-.9,0l-10.6-5.61L.52,309.083a.962.962,0,0,0,0,1.692l12.5,6.731a.962.962,0,0,0,.912,0l12.5-6.731A.962.962,0,0,0,26.823,309.472Z" transform="translate(-0.014 -290.697)" fill="#fff"></path>
                                        <path d="M26.5,6.905a.952.952,0,0,0-.084-.05L13.918.124a.962.962,0,0,0-.912,0L.506,6.855A.952.952,0,0,0,.161,8.226l13.3,7.042,13.3-7.042A.952.952,0,0,0,26.5,6.905Z" transform="translate(0 -0.009)" fill="#fff"></path>
                                    </g>
                                </svg>
                                <span>News Tech</span>
                            </div>
                        </div>
                    </div>
                    <div class="sbbody">
                        <ul class="blogs w3">
                            <?php foreach ($listSectionNewsTech as $sectionNewsTech) { ?>
                                <?php $postIDNewsTech = url_to_postid($sectionNewsTech);
                                $getPostNewsTech = get_post($postIDNewsTech);
                                $postThumbnailNewsTechUrl = get_the_post_thumbnail_url($postIDNewsTech);
                                $shortDescriptionNewsTech = get_post_meta($postIDNewsTech, '_yoast_wpseo_metadesc');
                                ?>
                                <li>
                                    <a class="blog" href="<?php echo $sectionNewsTech ?>" title="<?php echo $getPostNewsTech->post_title ?>">
                                        <figure>
                                            <img class="thumb" src="<?php echo $postThumbnailNewsTechUrl ?>" alt="<?php echo $getPostNewsTech->post_title ?>">
                                        </figure>
                                        <div class="info">
                                            <div class="title"><?php echo $getPostNewsTech->post_title ?></div>
                                            <div class="description">
                                                <?php if (!empty($shortDescriptionNewsTech)) {
                                                    echo $shortDescriptionNewsTech[0];
                                                } ?>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>

        <?php } ?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>