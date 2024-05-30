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
                        </div>

                        <?php echo do_shortcode('[google-translator]'); ?>
                    </ul>
                </div>
                <!--
                <div class="lang_box">
                    <a onclick="lang_toggler();" href="javascript:void(0);"><img width="20" height="20" id="show_fix_lang_icon" alt="English"><span id="show_fix_lang_short" class="lang_txt">EN</span></a>
                    <div id="lang_box_inner" class="lang_box_inner">
                        <a lang="en-US" hreflang="en-US" href="/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHzSURBVHjaYkxOP8IAB//+Mfz7w8Dwi4HhP5CcJb/n/7evb16/APL/gRFQDiAAw3JuAgAIBEDQ/iswEERjGzBQLEru97ll0g0+3HvqMn1SpqlqGsZMsZsIe0SICA5gt5a/AGIEarCPtFh+6N/ffwxA9OvP/7//QYwff/6fZahmePeB4dNHhi+fGb59Y4zyvHHmCEAAAW3YDzQYaJJ93a+vX79aVf58//69fvEPlpIfnz59+vDhw7t37968efP3b/SXL59OnjwIEEAsDP+YgY53b2b89++/awvLn98MDi2cVxl+/vl6mituCtBghi9f/v/48e/XL86krj9XzwEEEENy8g6gu22rfn78+NGs5Ofr16+ZC58+fvyYwX8rxOxXr169fPny+fPn1//93bJlBUAAsQADZMEBxj9/GBxb2P/9+S/R8u3vzxuyaX8ZHv3j8/YGms3w8ycQARmi2eE37t4ACCDGR4/uSkrKAS35B3TT////wADOgLOBIaXIyjBlwxKAAGKRXjCB0SOEaeu+/y9fMnz4AHQxCP348R/o+l+//sMZQBNLEvif3AcIIMZbty7Ly6t9ZmXl+fXj/38GoHH/UcGfP79//BBiYHjy9+8/oUkNAAHEwt1V/vI/KBY/QSISFqM/GBg+MzB8A6PfYC5EFiDAABqgW776MP0rAAAAAElFTkSuQmCC" title="English" alt="English"><span>English</span></a>
                        <a lang="ja" hreflang="ja" href="/ja/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAE2SURBVHjaYvz69SsDEvj37x+ERGbAwZ9//wACiAUoysXFBST///8P0QOm//+HU0jgxYsXAAHEAlP0H8HYt+//4SP/f//6b2b238sLrpqRkRFoCUAAsaCrXrv2/8KF///8+f/r9//Dh/8/ffI/OQWiAeJCgABigrseJPT27f/Vq////v3/1y8oWrzk/+PHcEv+/PkDEEBMEM/B3fj/40eo0t9g8suX/w8f/odZAVQMEEAsQAzj/2cQFf3PxARWCrYEaBXQLCkpqB/+/wcqBgggJrjxQPX/hYX/+/v///kLqhpIBgf/l5ODhxiQBAggFriToDoTEv5zcf3ftQuk2s7uf0wM3MdAAPQDQAAxvn37lo+PDy4KZUDcycj4/z9CBojv3r0LEEAgG969eweLSBDEBSCWAAQYACaTbJ/kuok9AAAAAElFTkSuQmCC" title="日本語" alt="日本語"><span>日本語</span></a>
                        <a lang="th" hreflang="th" href="/th/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVHjaYvzPgAD/UNlYEUAAisQgBwAQhGGi/pzP8EBvG+BImqbL7pzuUlda9SJ7DMs85NYEBgX5Ir4AYvz/H2QHhIQz/mMDjIyMnz59AgggRkfXjTmZOu/e/fz7D2jH/7///v398+8PkPEHCEHsv3///fn978+/f8JCnGWlWwACiGX/7jOmhiKPHn3+8wck8fvPv9+//wLRr1//wORfOCkvz8fAsAUggIB++AdxJ8iRQNf++f/rF8TZ/4B6fgEZQPIXRAEoLAACCKjhx9+/f/78+f0LaC/YbIjxyGaDSaCFvxgYvgAEEAs3r5qKqhAPLzs4GP4CnQR2G9CMf2A2iPEH7BNJSe5Tp8wAAojx58+fzMzM//79wxU4EACUBYbS27dvAQKI5R87O1NJCQPEjX//MvwGkn8Yf/8GRggCAY0DSgFt2bsXIIAYv6JGJJ44hgCAAAMA8pZimQIezaoAAAAASUVORK5CYII=" title="ไทย" alt="ไทย"><span>ไทย</span></a>
                    </div>
                </div>
                -->
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