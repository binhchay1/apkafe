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
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
        <symbol id="icon-star-rating" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
            <path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path>
        </symbol>

        <symbol id="icon-write-review" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21 21">
            <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" transform="translate(3 3)">
                <path d="m14 1c.8284271.82842712.8284271 2.17157288 0 3l-9.5 9.5-4 1 1-3.9436508 9.5038371-9.55252193c.7829896-.78700064 2.0312313-.82943964 2.864366-.12506788z"></path>
                <path d="m6.5 14.5h8"></path>
                <path d="m12.5 3.5 1 1"></path>
            </g>
        </symbol>

        <symbol id="icon-send-review" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M15.379,19.1403 L12.108,12.5993 L19.467,5.2413 L15.379,19.1403 Z M4.86,8.6213 L18.76,4.5343 L11.401,11.8923 L4.86,8.6213 Z M3.359,8.0213 C2.923,8.1493 2.87,8.7443 3.276,8.9483 L11.128,12.8733 L15.053,20.7243 C15.256,21.1303 15.852,21.0773 15.98,20.6413 L20.98,3.6413 C21.091,3.2623 20.739,2.9093 20.359,3.0213 L3.359,8.0213 Z"></path>
        </symbol>
    </svg>
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
                                    <li class="menu-primary-nav">
                                        <a href="<?php echo $menu['link'] ?>" class="d-flex align-items-center">
                                            <span class="d-flex align-items-center"><img src="<?php echo $menu['icon'] ?>" width="30" height="30" /></span>
                                            <span><?php echo $menu['title'] ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } ?>

                            <li class="dropdown-langue" style="align-content: center;">
                                <?php echo do_shortcode('[google-translator]'); ?>
                            </li>
                        </div>
                    </ul>
                </div>
            </div>
        </header>

        <div class="background-nav-new hide-mobile">
        </div>

        <div class="nav_new hide-mobile" id="nav_new">
            <div class="item close_item">
                <button type="button" onclick="closeMenu()">
                    X
                </button>
            </div>

            <div id="nav_wrap_mobile">
                <ul class="main_nav_mobile">
                    <?php if (!empty(ot_get_option('menu_header'))) {  ?>
                        <?php foreach (ot_get_option('menu_header') as $key => $menu) {
                            if ($key % 2 == 0) { ?>
                                <li>
                                    <span><img src="<?php echo $menu['icon'] ?>" width="40" height="40" /></span>
                                    <span style="margin-bottom: 10px;"><a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a></span>
                                </li>
                            <?php } else { ?>
                                <li class="remove-background-default">
                                    <span><img src="<?php echo $menu['icon'] ?>" width="40" height="40" /></span>
                                    <span style="margin-bottom: 10px;"><a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a></span>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>

                    <li class="dropdown-langue" style="align-content: center;">
                        <?php echo do_shortcode('[google-translator]'); ?>
                    </li>
                </ul>
            </div>

            <div>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'trending_search';
                $resultsTrending = $wpdb->get_results("SELECT * FROM $table_name");
                ?>

                <h2 class="widget_head_mobile">Trending Searches</h2>
                <div class="side_cat_list_wrap">
                    <div class="search-box index_r_s">
                        <form action="/search/" method="post" class="formsearch">
                            <span class="text-box"><span class="twitter-typeahead" style="position: relative; display: inline-block;">
                                    <input class="autocomplete main-autocomplete tt-hint width-search-mobile search-bar-mobile" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" readonly="" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);">
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
        </div>