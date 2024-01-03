<?php
$nav_style = leaf_get_option('nav_style', false);
$nav_des = leaf_get_option('nav_des', 'on');
?>
<div id="main-nav" class="<?php if (leaf_get_option('nav_schema', false)) { ?> light-nav <?php } else { ?> dark-div <?php } ?> <?php if ($nav_des == 'off') { ?> disable-description <?php } ?>" <?php if (leaf_get_option('nav_sticky', 'on') == 'on') { ?>data-spy="affix" data-offset-top="280" <?php } ?>>
    <nav class="navbar navbar-inverse <?php if ($nav_style) { ?> style-off-canvas <?php } ?>" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <?php if (leaf_get_option('logo_image') == '') : ?>
                    <a class="logo" href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri() ?>/images/web-logo.png" alt="logo"></a>
                <?php else : ?>
                    <a class="logo" href="<?php echo get_home_url(); ?>" title="<?php wp_title('|', true, 'right'); ?>"><img src="<?php echo esc_url(leaf_get_option('logo_image')); ?>" alt="<?php wp_title('|', true, 'right'); ?>" /></a>
                <?php endif; ?>

                <?php if (leaf_get_option('sticky_logo_image') != '' && leaf_get_option('nav_sticky', 'on') == 'on') : ?>
                    <style type="text/css">
                        .navbar-header .logo.sticky {
                            display: none
                        }

                        #main-nav.affix .navbar-header .logo {
                            display: none
                        }

                        #main-nav.affix .navbar-header .logo.sticky {
                            display: inline-block
                        }

                        #main-nav.affix .style-off-canvas .navbar-header .logo.sticky {
                            display: block
                        }
                    </style>
                    <a class="logo sticky" href="<?php echo get_home_url(); ?>" title="<?php wp_title('|', true, 'right'); ?>"><img src="<?php echo esc_url(leaf_get_option('sticky_logo_image')); ?>" alt="<?php wp_title('|', true, 'right'); ?>" /></a>
                <?php endif; ?>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="main-menu hidden-xs <?php if ($nav_style) { ?> hidden <?php } ?>">
                <?php
                if (function_exists('pll_the_languages')) { ?>
                    <ul class="wmpl-lang nav navbar-nav navbar-right">
                        <li class="main-menu-item menu-item-depth-0 menu-item menu-item-has-children parent dropdown sub-menu-left">
                            <?php
                            $translations = pll_the_languages(array('raw' => 1));
                            foreach ($translations as $item) {
                                if ($item['current_lang']) {
                                    echo '<a href="' . esc_url($item['url']) . '"><img src="' . esc_url($item['flag']) . '"/></a>';
                                }
                            }
                            ?>
                            <ul class="dropdown-menu menu-depth-1">
                                <?php pll_the_languages(array('show_flags' => 1, 'show_names' => 1)); ?>
                            </ul>
                        </li>
                    </ul>
                    <?php } elseif (function_exists('icl_get_languages')) {
                    $arr_lg = icl_get_languages('skip_missing=0');
                    if (!empty($arr_lg)) { ?>
                        <ul class="wmpl-lang nav navbar-nav navbar-right">
                            <li class="main-menu-item menu-item-depth-0 menu-item menu-item-has-children parent dropdown sub-menu-left">
                                <?php
                                $lang_html = '';
                                foreach ($arr_lg as $item) {
                                    if ($item['active']) {
                                        echo '<a href="' . esc_url($item['url']) . '"><img src="' . esc_url($item['country_flag_url']) . '"/></a>';
                                    }
                                    $lang_html .= '<li class=""><a title="' . esc_attr($item['translated_name']) . '" href="' . esc_url($item['url']) . '"><img title="' . esc_attr($item['translated_name']) . '" src="' . esc_url($item['country_flag_url']) . '"/></a></li>';
                                }
                                if ($lang_html) {
                                    echo '<ul class="dropdown-menu menu-depth-1">' . $lang_html . '</ul>';
                                }
                                ?>
                            </li>
                        </ul>
                <?php
                    }
                }
                ?>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if (has_nav_menu('primary-menus')) {
                        wp_nav_menu(array(
                            'theme_location'  => 'primary-menus',
                            'container' => false,
                            'items_wrap' => '%3$s',
                            'walker' => new custom_walker_nav_menu()
                        ));
                    } else { ?>
                        <li><a href="<?php echo home_url(); ?>"><?php _e('Home', 'leafcolor') ?> <span class="menu-description"><?php _e('Home page', 'leafcolor') ?></span></a></li>
                        <?php wp_list_pages('depth=1&number=4&title_li='); ?>
                    <?php } ?>
                </ul>
            </div><!-- /.navbar-collapse -->
            <button type="button" class="mobile-menu-toggle <?php if ($nav_style) { ?> <?php } else { ?> visible-xs <?php } ?>">
                <span class="sr-only"><?php _e('Menu', 'leafcolor') ?></span>
                <i class="fa fa-bars"></i>
            </button>
        </div>
    </nav>
</div><!-- #main-nav -->