<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<?php

$taxonomy = 'product_cat';
$orderby = 'name';
$show_count = 0;
$pad_counts = 0;
$dataCategory = [];
$args = array(
    'taxonomy'     => $taxonomy,
    'orderby'      => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'parent' => 0
);

$all_parent_categories = get_categories($args);
$categoriesPost = get_categories();

foreach ($categoriesPost as $category) {
    if ($category->name == 'Tips Android') {
        $idCategoryTips = $category->term_id;
    }
}

foreach ($all_parent_categories as $parent) {
    $category_id = $parent->term_id;

    $argsGetChildCategories = array(
        'taxonomy'     => $taxonomy,
        'orderby'      => $orderby,
        'show_count'   => $show_count,
        'pad_counts'   => $pad_counts,
        'parent'       => $category_id,
        'child_of'     => 0,
    );

    $all_child_categories = get_categories($argsGetChildCategories);
    foreach ($all_child_categories as $category) {
        $dataCategory[$parent->cat_name][] = $category;
    }
}

?>

<head>

    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0">
    <meta name="ahrefs-site-verification" content="6696d4ed9ce3e44a9658b8dfe8ae27d2e90fce3365b23b65c226b8c5a97c1330">

    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

    <?php if (ot_get_option('favicon')) { ?>
        <link rel="shortcut icon" type="ico" href="<?php echo ot_get_option('favicon'); ?>">
    <?php }

    wp_head(); ?>
</head>

<body <?php body_class() ?>>

    <a style="height:0; position:absolute; top:0;" id="top"></a>
    <?php if (ot_get_option('pre-loading', 2) == 1 || (ot_get_option('pre-loading', 2) == 2 && (is_front_page() || is_page_template('page-templates/front-page.php')))) { ?>
        <div id="pageloader" class="dark-div" style="position:fixed; top:0; left:0; width:100%; height:100%; z-index:99999; background:<?php echo esc_attr(ot_get_option('loading_bg', '#111')) ?>;">
            <div class="loader loader-2"><i></i><i></i><i></i><i></i></div>
        </div>
        <script>
            setTimeout(function() {
                jQuery('#pageloader').fadeOut();
            }, 30000);
        </script>
    <?php }

    global $page_title;
    $page_title = leaf_global_title();
    ?>
    <div id="body-wrap">
        <div id="wrap">
            <header id="header" data-search-sug-download="1">
                <div class="nav_container">
                    <h1 class="logo">
                        <a title="Apkafe" href="/">
                            <img alt="Apkafe" src="/wp-content/uploads/2019/04/fav_apkafe-2.png" height="65" width="47" class="p_logo" style="height: 65px;">
                            <img alt="Logo" src="/wp-content/uploads/2019/04/fav_apkafe-2.png" height="65" width="30" class="m_logo" style="height: 40px;">
                        </a>
                    </h1>
                    <div class="shadow" id="shadow" onclick="closeMenu()" style="display: none;"></div>
                    <div class="nav_new" id="nav_new">
                        <div class="item close_item">
                            <button type="button" onclick="closeMenu()">
                                <i class="icon icon_close"></i>
                            </button>
                        </div>

                        <div class="item nav_home searching-hide">
                            <a href="/" class="dt_nav_button" title="The best free online game and application store today">
                                <i class="icon icon_home"></i>
                                <span class="dt_menu_text">The best free online game and application store today</span>
                            </a>
                        </div>
                        <div class="item searching-hide">
                            <a class="dt_nav_button nav-g" href="/games-html5/" title="Games html5">
                                <i class="icon icon_game"></i>
                                <span class="dt_menu_text">Games html5</span>
                            </a>
                        </div>

                        <div class="item many searching-hide">
                            <span class="nav-p dt_nav_button dt-nav-parent">
                                <i class="icon icon_product"></i>
                                <span class="dt_menu_text">App</span>
                            </span>
                            <ul class="nav_submenu">
                                <li class="nav_submenu-item">
                                    <div class="menu_list">
                                        <div class="menu_body">
                                            <ul>
                                                <?php foreach ($dataCategory['App'] as $categoryApp) { ?>
                                                    <li><a class="dt_menu_text" href="<?php echo get_category_link($categoryApp->term_id) ?>" title="<?php echo $categoryApp->name ?>"><?php echo $categoryApp->name ?></a></li>
                                                <?php } ?>
                                            </ul>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="item many searching-hide">
                            <span class="nav-p dt_nav_button dt-nav-parent">
                                <i class="icon icon_product"></i>
                                <span class="dt_menu_text">Game</span>
                            </span>
                            <ul class="nav_submenu">
                                <li class="nav_submenu-item">
                                    <div class="menu_list">
                                        <div class="menu_body">
                                            <ul>
                                                <?php foreach ($dataCategory['Game'] as $categoryGame) { ?>
                                                    <li><a class="dt_menu_text" href="<?php echo get_category_link($categoryGame->term_id) ?>" title="<?php echo $categoryGame->name ?>"><?php echo $categoryGame->name ?></a></li>
                                                <?php } ?>
                                            </ul>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="item searching-hide">
                            <a class="dt_nav_button nav-a" href="<?php echo get_category_link($idCategoryTips) ?>" title="Privacy policy">
                                <i class="icon icon_product"></i>
                                <span class="dt_menu_text">Tips android</span>
                            </a>
                        </div>

                        <div class="item many searching-hide">
                            <span class="nav-p dt_nav_button dt-nav-parent">
                                <i class="icon icon_product"></i>
                                <?php
                                $arr_lg = icl_get_languages('skip_missing=0');
                                foreach ($arr_lg as $item) {
                                    if ($item['active']) {
                                        echo '<span class="dt_menu_text"><a href="' . esc_url($item['url']) . '"><img src="' . esc_url($item['country_flag_url']) . '"/></a></span>';
                                    }
                                }
                                ?>
                            </span>
                            <?php $translations = pll_the_languages(array('raw' => 1));
                            ?>
                            <ul class="nav_submenu">
                                <li class="main-menu-item menu-item-depth-0 menu-item menu-item-has-children parent dropdown sub-menu-left">
                                    <?php
                                    pll_the_languages(array('show_flags' => 1, 'show_names' => 1));
                                    ?>
                                </li>
                            </ul>
                        </div>

                        <div class="item search">
                            <form class="formsearch searching-show" method="post" action="/search/" id="searching-show">
                                <div class="search-input">
                                    <span class="twitter-typeahead" style="position: relative; display: inline-block;">
                                        <input class="query autocomplete main-autocomplete tt-hint" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" size="40" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);">
                                        <input value="<?php get_search_query() ?>" name="s" id="s" class="query autocomplete main-autocomplete tt-input" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" size="40" placeholder="Apkafe" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;">
                                        <pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
                                        <div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                                            <div class="tt-dataset tt-dataset-0"></div>
                                        </div>
                                    </span>
                                    <input class="search-btn-icon" type="submit" value="<?php esc_attr__('Search') ?>" id="searchsubmit">
                                    <i class="clear-input"></i>
                                </div>
                            </form>
                            <div class="search-mask" id="search-mask">
                                <i class="search-mask-icon"></i>
                                <span>Apkafe</span>
                            </div>
                        </div>
                        <div class="menu-item item group menu-item-pwa" style="display: none;">
                            <div>
                                <div class="menu-layer">
                                    <span class="menu-icon-pwa"></span>
                                    <span class="menu-text">Add to Home Screen</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="menu_btn" onclick="openMenu()">
                        <i class="icon icon_menu"></i>
                    </button>
                    <a class="search_btn" title="search" href="#search" id="btn-search-m">
                        <i class="icon icon_search"></i>
                    </a>

                    <div class="ll" style="display: none;" id="ll">
                        <input autocomplete="off" value id="form_query_mobile" class="query autocomplete" title="Enter App Name, Package Name, Package ID" name="q" type="search" placeholder="Apkafe" />
                        <input type="hidden" name="t" value required />
                    </div>
                </div>
            </header>