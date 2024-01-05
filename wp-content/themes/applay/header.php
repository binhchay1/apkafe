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

$url = home_url();
$listLang = get_template_directory() . '/languages/en.php';
$pos = strpos($url, '/ja');
if ($pos > 0) {
    $listLang = get_template_directory() . '/languages/ja.php';
}

$pos = strpos($url, '/th');
if ($pos > 0) {
    $listLang = get_template_directory() . '/languages/th.php';
}

require $listLang;
?>

<head>

    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0">
    <meta name="ahrefs-site-verification" content="6696d4ed9ce3e44a9658b8dfe8ae27d2e90fce3365b23b65c226b8c5a97c1330">

    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

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

                        <?php wp_nav_menu(array(
                            'theme_location'  => 'primary-menus',
                            'container' => false,
                            'items_wrap' => '%3$s',
                            'walker' => new custom_walker_nav_menu,
                        )) ?>

                        <?php
                        if (function_exists('pll_the_languages')) { ?>
                            <ul class="wmpl-lang nav navbar-nav navbar-right searching-hide">
                                <li class="main-menu-item menu-item-depth-0 menu-item menu-item-has-children parent dropdown sub-menu-left">
                                    <?php
                                    $translations = pll_the_languages(array('raw' => 1));
                                    foreach ($translations as $item) {
                                        if ($item['current_lang']) {
                                            echo '<a href="' . esc_url($item['url']) . '"><img src="' . esc_url($item['flag']) . '"/></a>';
                                        }
                                    }
                                    ?>
                                    <ul class="dropdown-menu menu-depth-1" id="ul-list-lang">
                                        <li class="lang-item lang-item-16 lang-item-en lang-item-first current-lang"><a lang="en-US" hreflang="en-US" href="/"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHzSURBVHjaYkxOP8IAB//+Mfz7w8Dwi4HhP5CcJb/n/7evb16/APL/gRFQDiAAw3JuAgAIBEDQ/iswEERjGzBQLEru97ll0g0+3HvqMn1SpqlqGsZMsZsIe0SICA5gt5a/AGIEarCPtFh+6N/ffwxA9OvP/7//QYwff/6fZahmePeB4dNHhi+fGb59Y4zyvHHmCEAAAW3YDzQYaJJ93a+vX79aVf58//69fvEPlpIfnz59+vDhw7t37968efP3b/SXL59OnjwIEEAsDP+YgY53b2b89++/awvLn98MDi2cVxl+/vl6mituCtBghi9f/v/48e/XL86krj9XzwEEEENy8g6gu22rfn78+NGs5Ofr16+ZC58+fvyYwX8rxOxXr169fPny+fPn1//93bJlBUAAsQADZMEBxj9/GBxb2P/9+S/R8u3vzxuyaX8ZHv3j8/YGms3w8ycQARmi2eE37t4ACCDGR4/uSkrKAS35B3TT////wADOgLOBIaXIyjBlwxKAAGKRXjCB0SOEaeu+/y9fMnz4AHQxCP348R/o+l+//sMZQBNLEvif3AcIIMZbty7Ly6t9ZmXl+fXj/38GoHH/UcGfP79//BBiYHjy9+8/oUkNAAHEwt1V/vI/KBY/QSISFqM/GBg+MzB8A6PfYC5EFiDAABqgW776MP0rAAAAAElFTkSuQmCC" title="English" alt="English"><span style="margin-left:0.3em;">English</span></a></li>
                                        <li class="lang-item lang-item-27 lang-item-ja"><a lang="ja" hreflang="ja" href="/ja/"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAE2SURBVHjaYvz69SsDEvj37x+ERGbAwZ9//wACiAUoysXFBST///8P0QOm//+HU0jgxYsXAAHEAlP0H8HYt+//4SP/f//6b2b238sLrpqRkRFoCUAAsaCrXrv2/8KF///8+f/r9//Dh/8/ffI/OQWiAeJCgABigrseJPT27f/Vq////v3/1y8oWrzk/+PHcEv+/PkDEEBMEM/B3fj/40eo0t9g8suX/w8f/odZAVQMEEAsQAzj/2cQFf3PxARWCrYEaBXQLCkpqB/+/wcqBgggJrjxQPX/hYX/+/v///kLqhpIBgf/l5ODhxiQBAggFriToDoTEv5zcf3ftQuk2s7uf0wM3MdAAPQDQAAxvn37lo+PDy4KZUDcycj4/z9CBojv3r0LEEAgG969eweLSBDEBSCWAAQYACaTbJ/kuok9AAAAAElFTkSuQmCC" title="日本語" alt="日本語" width="16" height="11"><span style="margin-left:0.3em;">日本語</span></a></li>
                                        <li class="lang-item lang-item-909 lang-item-th"><a lang="th" hreflang="th" href="/th/"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVHjaYvzPgAD/UNlYEUAAisQgBwAQhGGi/pzP8EBvG+BImqbL7pzuUlda9SJ7DMs85NYEBgX5Ir4AYvz/H2QHhIQz/mMDjIyMnz59AgggRkfXjTmZOu/e/fz7D2jH/7///v398+8PkPEHCEHsv3///fn978+/f8JCnGWlWwACiGX/7jOmhiKPHn3+8wck8fvPv9+//wLRr1//wORfOCkvz8fAsAUggIB++AdxJ8iRQNf++f/rF8TZ/4B6fgEZQPIXRAEoLAACCKjhx9+/f/78+f0LaC/YbIjxyGaDSaCFvxgYvgAEEAs3r5qKqhAPLzs4GP4CnQR2G9CMf2A2iPEH7BNJSe5Tp8wAAojx58+fzMzM//79wxU4EACUBYbS27dvAQKI5R87O1NJCQPEjX//MvwGkn8Yf/8GRggCAY0DSgFt2bsXIIAYv6JGJJ44hgCAAAMA8pZimQIezaoAAAAASUVORK5CYII=" title="ไทย" alt="ไทย" width="16"><span style="margin-left:0.3em;">ไทย</span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        <?php } ?>

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
                                <i class="fa fa-search"></i>
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
                        <i class="fa fa-search"></i>
                    </a>

                    <div class="ll" style="display: none;" id="ll">
                        <input autocomplete="off" value id="form_query_mobile" class="query autocomplete" title="Enter App Name, Package Name, Package ID" name="q" type="search" placeholder="Apkafe" />
                        <input type="hidden" name="t" value required />
                    </div>
                </div>
            </header>