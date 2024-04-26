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
    <meta name="robots" content="noindex, nofollow">

    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <?php if (ot_get_option('favicon') != '') { ?>
        <link rel="shortcut icon" type="ico" href="<?php echo ot_get_option('favicon') ?>">
    <?php } ?>
    <link rel="shortcut icon" type="ico" href="//apkafe.com/wp-content/uploads/2019/04/fav_apkafe-2.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

    <?php if (ot_get_option('custom_script_header') != '') {
        echo ot_get_option('custom_script_header');
    } ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class() ?> id="webpage">
    <div id="bmain_wrap">
        <header>
            <div class="container">
                <span id="menu_hndlr" class="menu_hndlr"><i onclick="show_menu_mob();" class="fa fa-bars"></i></span>
                <div class="logo_wrap">
                    <?php if (ot_get_option('logo_image') != '') { ?>
                        <a href="/"><img height="38" width="242" class="logo hwa" src="<?php echo ot_get_option('logo_image') ?>" alt="Apkafe"></a>
                    <?php } ?>
                </div>
                <div id="nav_wrap">
                    <p class="mob_menu_close"><i onclick="hide_menu_mob();" class="fa fa-times"></i></p>
                    <ul class="main_nav">
                        <div class="ftr_link_box" style="display: flex; justify-content: end;">
                            <?php if (!empty(ot_get_option('menu_header'))) {  ?>
                                <?php foreach (ot_get_option('menu_header') as $menu) { ?>
                                    <li style="display: flex; justify-content: end;">
                                        <span style="margin-top: 5px;"><img src="<?php echo $menu['icon'] ?>" width="40" height="40" /></span>
                                        <span><a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a></span>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </ul>
                </div>
                <div class="lang_box">
                    <a onclick="lang_toggler();" href="javascript:void(0);"><img width="20" height="20" id="show_fix_lang_icon" src="https://apkmodget.com/images/lang-en.png" alt="English"><span id="show_fix_lang_short" class="lang_txt">EN</span></a>
                    <div id="lang_box_inner" class="lang_box_inner">
                        <a lang="en-US" hreflang="en-US" href="/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHzSURBVHjaYkxOP8IAB//+Mfz7w8Dwi4HhP5CcJb/n/7evb16/APL/gRFQDiAAw3JuAgAIBEDQ/iswEERjGzBQLEru97ll0g0+3HvqMn1SpqlqGsZMsZsIe0SICA5gt5a/AGIEarCPtFh+6N/ffwxA9OvP/7//QYwff/6fZahmePeB4dNHhi+fGb59Y4zyvHHmCEAAAW3YDzQYaJJ93a+vX79aVf58//69fvEPlpIfnz59+vDhw7t37968efP3b/SXL59OnjwIEEAsDP+YgY53b2b89++/awvLn98MDi2cVxl+/vl6mituCtBghi9f/v/48e/XL86krj9XzwEEEENy8g6gu22rfn78+NGs5Ofr16+ZC58+fvyYwX8rxOxXr169fPny+fPn1//93bJlBUAAsQADZMEBxj9/GBxb2P/9+S/R8u3vzxuyaX8ZHv3j8/YGms3w8ycQARmi2eE37t4ACCDGR4/uSkrKAS35B3TT////wADOgLOBIaXIyjBlwxKAAGKRXjCB0SOEaeu+/y9fMnz4AHQxCP348R/o+l+//sMZQBNLEvif3AcIIMZbty7Ly6t9ZmXl+fXj/38GoHH/UcGfP79//BBiYHjy9+8/oUkNAAHEwt1V/vI/KBY/QSISFqM/GBg+MzB8A6PfYC5EFiDAABqgW776MP0rAAAAAElFTkSuQmCC" title="English" alt="English"><span>English</span></a>
                        <a lang="ja" hreflang="ja" href="/ja/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAE2SURBVHjaYvz69SsDEvj37x+ERGbAwZ9//wACiAUoysXFBST///8P0QOm//+HU0jgxYsXAAHEAlP0H8HYt+//4SP/f//6b2b238sLrpqRkRFoCUAAsaCrXrv2/8KF///8+f/r9//Dh/8/ffI/OQWiAeJCgABigrseJPT27f/Vq////v3/1y8oWrzk/+PHcEv+/PkDEEBMEM/B3fj/40eo0t9g8suX/w8f/odZAVQMEEAsQAzj/2cQFf3PxARWCrYEaBXQLCkpqB/+/wcqBgggJrjxQPX/hYX/+/v///kLqhpIBgf/l5ODhxiQBAggFriToDoTEv5zcf3ftQuk2s7uf0wM3MdAAPQDQAAxvn37lo+PDy4KZUDcycj4/z9CBojv3r0LEEAgG969eweLSBDEBSCWAAQYACaTbJ/kuok9AAAAAElFTkSuQmCC" title="日本語" alt="日本語"><span>日本語</span></a>
                        <a lang="th" hreflang="th" href="/th/" class="lang_item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAIAAAD5gJpuAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVHjaYvzPgAD/UNlYEUAAisQgBwAQhGGi/pzP8EBvG+BImqbL7pzuUlda9SJ7DMs85NYEBgX5Ir4AYvz/H2QHhIQz/mMDjIyMnz59AgggRkfXjTmZOu/e/fz7D2jH/7///v398+8PkPEHCEHsv3///fn978+/f8JCnGWlWwACiGX/7jOmhiKPHn3+8wck8fvPv9+//wLRr1//wORfOCkvz8fAsAUggIB++AdxJ8iRQNf++f/rF8TZ/4B6fgEZQPIXRAEoLAACCKjhx9+/f/78+f0LaC/YbIjxyGaDSaCFvxgYvgAEEAs3r5qKqhAPLzs4GP4CnQR2G9CMf2A2iPEH7BNJSe5Tp8wAAojx58+fzMzM//79wxU4EACUBYbS27dvAQKI5R87O1NJCQPEjX//MvwGkn8Yf/8GRggCAY0DSgFt2bsXIIAYv6JGJJ44hgCAAAMA8pZimQIezaoAAAAASUVORK5CYII=" title="ไทย" alt="ไทย"><span>ไทย</span></a>
                    </div>
                </div>
                <a onclick="on_search();" class="btnsearch" href="javascript:void(0);"><i class="fa fa-search"></i></a>
            </div>
        </header>