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
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
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
                        <div class="ftr_link_box">
                            <?php if (!empty(ot_get_option('menu_header'))) {  ?>
                                <?php foreach (ot_get_option('menu_header') as $menu) { ?>
                                    <li><a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a></li>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </ul>
                </div>
                <div class="lang_box">
                    <a onclick="lang_toggler();" href="javascript:void(0);"><img width="20" height="20" id="show_fix_lang_icon" src="https://apkmodget.com/images/lang-en.png" alt="English"><span id="show_fix_lang_short" class="lang_txt">EN</span></a>
                    <div id="lang_box_inner" class="lang_box_inner">
                        <a href="https://apkmodget.com/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/images/lang-en.png" alt="English"><span>English</span></a><a href="https://apkmodget.com/br/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/brazilflag_08f7f.png" alt="Português"><span>Português</span></a><a href="https://apkmodget.com/id/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/indonesian_984f9.png" alt="indonesia"><span>indonesia</span></a><a href="https://apkmodget.com/es/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/spainflag_6cae4.png" alt="Español"><span>Español</span></a><a href="https://apkmodget.com/ru/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/russia_37ab2.png" alt="Russia"><span>Russia</span></a><a href="https://apkmodget.com/sa/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/saudi-arabia_36164.png" alt="Arabic"><span>Arabic</span></a><a href="https://apkmodget.com/in/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/india_7b7d8.png" alt="India"><span>India</span></a><a href="https://apkmodget.com/it/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/italy_2a9cb.png" alt="Italy"><span>Italy</span></a><a href="https://apkmodget.com/jp/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/japan_447bf.png" alt="Japan"><span>Japan</span></a><a href="https://apkmodget.com/pl/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/poland_6b9a7.png" alt="Poland"><span>Poland</span></a><a href="https://apkmodget.com/de/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/germany_1bc24.png" alt="Germany"><span>Germany</span></a><a href="https://apkmodget.com/fr/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/france_6326f.png" alt="France"><span>France</span></a><a href="https://apkmodget.com/tr/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/turkey_4df3c.png" alt="Turkey"><span>Turkey</span></a><a href="https://apkmodget.com/kp/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/korea_889df.png" alt="Korea"><span>Korea</span></a><a href="https://apkmodget.com/my/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/malaysia_c0fef.png" alt="Malaysia"><span>Malaysia</span></a><a href="https://apkmodget.com/th/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/thailand_e63f0.png" alt="Thailand"><span>Thailand</span></a><a href="https://apkmodget.com/vn/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/vietnam_95466.png" alt="Vietnam"><span>Vietnam</span></a><a href="https://apkmodget.com/cn/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/chinese-simplified_186a4.png" alt="Chinese (Simplified)"><span>Chinese (Simplified)</span></a><a href="https://apkmodget.com/tw/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/chinese-traditional_7d2de.png" alt="Chinese (Traditional)"><span>Chinese (Traditional)</span></a><a href="https://apkmodget.com/ne/" class="lang_item"><img width="20" height="20" src="https://apkmodget.com/media/2022/02/_1/20x20/125px-flagofthenetherlandssvg_99fd9.png" alt="Nederland  "><span>Nederland </span></a>
                    </div>
                </div>
                <a onclick="on_search();" class="btnsearch" href="javascript:void(0);"><i class="fa fa-search"></i></a>
            </div>
        </header>