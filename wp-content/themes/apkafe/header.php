<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">

    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <?php if (ot_get_option('favicon') != '') { ?>
        <link rel="shortcut icon" type="ico" href="<?php echo ot_get_option('favicon') ?>">
    <?php } ?>
    <link rel="shortcut icon" type="ico" href="https://apkafe.com/wp-content/uploads/2019/04/fav_apkafe-2.png">

    <?php if (ot_get_option('custom_script_header') != '') {
        echo ot_get_option('custom_script_header');
    } ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class() ?> id="webpage" ontouchend="handleTouch()">
    <div id="bmain_wrap">
        <header>
            <div class="container">
                <span id="menu_hndlr" class="menu_hndlr"><i onclick="show_menu_mobile();" class="fa fa-bars"></i></span>
                <div class="logo_wrap">
                    <?php if (ot_get_option('logo_image') != '') { ?>
                        <a href="/"><img height="38" width="242" class="logo" src="<?php echo ot_get_option('logo_image') ?>" alt="Apkafe"></a>
                    <?php } ?>
                </div>
                <div id="nav_wrap">
                    <ul class="main_nav">
                        <div class="ftr_link_box" style="display: flex; justify-content: end;">
                            <?php if (!empty(ot_get_option('menu_header'))) {  ?>
                                <?php foreach (ot_get_option('menu_header') as $menu) { ?>
                                    <li style="display: flex; justify-content: end;">
                                        <a href="<?php echo $menu['link'] ?>" class="d-flex align-items-center">
                                            <span class="d-flex align-items-center"><img src="<?php echo $menu['icon'] ?>" width="30" height="30" /></span>
                                            <span style="margin-left: 10px;"><?php echo $menu['title'] ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } ?>

                            <li style="align-content: center;">
                                <?php echo do_shortcode('[google-translator]'); ?>
                            </li>
                        </div>
                    </ul>
                </div>
            </div>
        </header>

        <div class="nav_new hide-mobile" id="nav_new" style="left: 0px;">
            <div class="item close_item">
                <button type="button" onclick="closeMenu()">
                    X
                </button>
            </div>

            <div id="nav_wrap_mobile">
                <ul class="main_nav_mobile">
                    <?php if (!empty(ot_get_option('menu_header'))) {  ?>
                        <?php foreach (ot_get_option('menu_header') as $menu) { ?>
                            <li>
                                <span><img src="<?php echo $menu['icon'] ?>" width="40" height="40" /></span>
                                <span style="margin-bottom: 10px;"><a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a></span>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>

            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'trending_search';
            $resultsTrending = $wpdb->get_results("SELECT * FROM $table_name");
            ?>

            <h2 class="widget_head_mobile">Trending Searches</h2>
            <div class="side_cat_list_wrap">
                <div class="search-box index_r_s">
                    <form action="/apkafe/search/" method="post" class="formsearch">
                        <span class="text-box"><span class="twitter-typeahead" style="position: relative; display: inline-block;">
                                <input class="autocomplete main-autocomplete tt-hint" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" readonly="" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);">
                                <input class="autocomplete main-autocomplete tt-input" autocomplete="off" title="Enter App Name, Package Name, Package ID" name="s" type="text" placeholder="Apkafe" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;">
                            </span>
                        </span>
                        <span class="text-btn d-flex-justify-center" title="Search APK">
                            <button type="submit" style="background: none; border: none; margin-left: 13px;">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </form>
                    <div class="trending-content">
                        <?php foreach ($resultsTrending as $trending) { ?>
                            <a href="<?php echo $trending->url ?>" title="<?php echo $trending->title ?>" class="hot"><?php echo $trending->title ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>