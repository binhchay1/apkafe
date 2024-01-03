<?php
if(leaf_get_option('retina_logo')){?>
	@media only screen and (-webkit-min-device-pixel-ratio: 2),(min-resolution: 192dpi) {
		/* Retina Logo */
		.logo{background:url(<?php echo esc_url(leaf_get_option('retina_logo')); ?>) no-repeat center; display:inline-block !important; background-size:contain;}
		.logo img{ opacity:0; visibility:hidden}
		.logo *{display:inline-block}
		.affix-top .logo.sticky,
		.affix .logo{ display:none !important}
		.affix .logo.sticky{ background:transparent !important; display:block !important}
		.affix .logo.sticky img{ opacity:1; visibility: visible;}
	}
<?php }

if(!function_exists('leaf_hex2rgba')){
function leaf_hex2rgba($hex,$opacity) {
   $hex = str_replace("#", "", $hex);
   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $opacity = $opacity/100;
   $rgba = array($r, $g, $b, $opacity);
   return implode(",", $rgba);
}
}

//fonts
if($custom_font_1 = leaf_get_option( 'custom_font_1')){ ?>
	@font-face
    {
    	font-family: 'custom-font-1';
    	src: url('<?php echo esc_url($custom_font_1) ?>');
    }
<?php }
if($custom_font_2 = leaf_get_option( 'custom_font_2')){ ?>
	@font-face
    {
    	font-family: 'custom-font-2';
    	src: url('<?php echo esc_url($custom_font_2) ?>');
    }
<?php }
$main_font = leaf_get_option( 'main_font', false);
$main_font_family = explode(":", $main_font);
$main_font_family = $main_font_family[0];
$heading_font = leaf_get_option( 'heading_font', false);
$heading_font_family = explode(":", $heading_font);
$heading_font_family = $heading_font_family[0];

if($main_font){?>
    body, #main-nav .navbar-nav>li>a .menu-description{
        font-family: "<?php echo esc_attr($main_font_family) ?>",sans-serif;
    }
<?php }
if($main_size = leaf_get_option( 'main_size' )){ ?>
	body {
        font-size: <?php echo esc_attr($main_size) ?>px;
    }
<?php }
if($heading_font){?>
    h1, .h1, h2, .h2, .content-dropcap p:first-child:first-letter, .dropcap, .font-2,
    .mobile-menu > li > a, .media-heading, .widget-title, .item-content .item-title,
    .post-slider-title, .overlay-top h4, h4.wpb_toggle, .wpb_accordion .wpb_accordion_wrapper h3.wpb_accordion_header,
    .content-dropcap p:first-child:first-letter, .dropcap, .related-product h3,
    #main-nav .navbar-nav>li>a{
        font-family: "<?php echo esc_attr($heading_font_family) ?>", Times, serif;
    }
<?php }

//listing mode
$woo_listing_mode = leaf_get_option('woo_listing_mode');
if($woo_listing_mode =='on'){?>
ul li .button.product_type_simple.ia-addtocart,
ul li .button.add_to_cart_button{ display: none}
<?php
}

//color
$main_color_1 = leaf_get_option('main_color_1');
if(isset($_GET['color']) && $_GET['color']){
	$main_color_1 = '#'.$_GET['color'];
}
if($main_color_1){ ?>
    .main-color-1, .main-color-1-hover:hover, a:hover, a:focus, .dark-div a:hover,
    header .multi-column > .dropdown-menu>li>a:hover,
    header .multi-column > .dropdown-menu .menu-column>li>a:hover,
    .item-meta a:not(.btn):hover,
    .single-post-navigation-item a:hover h4, .single-post-navigation-item a:hover i,
    .map-link.small-text,
    .single-course-detail .cat-link:hover,
    .related-product .ev-title a:hover,
    .woocommerce-review-link,
    .woocommerce #content div.product p.price,
    .woocommerce-tabs .active,
    .woocommerce p.stars a, .woocommerce-page p.stars a,
    .woocommerce .star-rating:before, .woocommerce-page .star-rating:before, .woocommerce .star-rating span:before, .woocommerce-page .star-rating span:before, .woocommerce ul.products li.product .price, .woocommerce-page ul.products li.product .price,
    .vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-left .vc_tta-tab.vc_active > a,
    .vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-left .vc_tta-tab:hover > a,
    .wpb_wrapper .wpb_content_element .wpb_tabs_nav li.ui-tabs-active, .wpb_wrapper .wpb_content_element .wpb_tabs_nav li:hover,
    .grid-overlay .star-rating span,
    .ia-icon, .light .ia-icon,
    .wpb_wrapper .wpb_accordion .wpb_accordion_wrapper .ui-accordion-header-active, .wpb_wrapper .wpb_accordion .wpb_accordion_wrapper .wpb_accordion_header:hover,
    #content .wpb_wrapper h4.wpb_toggle:hover,
    #content .wpb_wrapper h4.wpb_toggle.wpb_toggle_title_active,
    .underline-style ul li ul li:before, .normal-sidebar .underline-style ul li ul li:before,
    .bbp-topic-meta .bbp-topic-started-by a,
    li.bbp-topic-title .bbp-topic-permalink:hover, #bbpress-forums li.bbp-body ul.topic .bbp-topic-title:hover a, #bbpress-forums li.bbp-body ul.forum .bbp-forum-info:hover .bbp-forum-title,
    #bbpress-forums .bbp-body li.bbp-topic-freshness .bbp-author-name,
    #bbpress-forums .bbp-body li.bbp-forum-freshness .bbp-author-name,
    #bbpress-forums .type-forum p.bbp-topic-meta span a,
    #bbpress-forums #bbp-user-wrapper h2.entry-title,
    #bbpress-forums div.bbp-reply-author .bbp-author-role,
    .bbp-reply-header .bbp-meta a:hover,
    div.bbp-template-notice a.bbp-author-name,
    #bbpress-forums li.bbp-body ul.topic .bbp-topic-title:hover:before, #bbpress-forums li.bbp-body ul.forum .bbp-forum-info:hover:before,
    .vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-top .vc_tta-tab.vc_active > a,
    .vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-top .vc_tta-tab.hover > a,
    .wpb_wrapper .wpb_content_element .wpb_tabs_nav li.ui-tabs-active a, .wpb_wrapper .wpb_content_element .wpb_tabs_nav li:hover a{
        color:<?php echo esc_attr($main_color_1) ?>;
    }
    .ia-icon, .light .ia-icon, .dark-div .ia-icon:hover, .ia-icon-box:hover .ia-icon,
    .main-color-1-border,
    input:not([type]):focus, input[type="color"]:focus, input[type="email"]:focus, input[type="number"]:focus, input[type="password"]:focus, input[type="tel"]:focus, input[type="url"]:focus, input[type="text"]:focus, .form-control:not(select):focus, textarea:focus{
        border-color:<?php echo esc_attr($main_color_1) ?>;
    }
    .related-item .price{color:<?php echo esc_attr($main_color_1) ?> !important;}
    .features-control-item:after,
    .main-color-2-bg,
    .main-color-1-bg, .main-color-1-bg-hover:hover,
    input[type=submit],
    table:not(.shop_table)>thead, table:not(.shop_table)>tbody>tr:hover>td, table:not(.shop_table)>tbody>tr:hover>th,
    header .dropdown-menu>li>a:hover, header .dropdown-menu>li>a:focus,
    .widget-title:before,
    .ia-heading h2:before, .member .member-info p:before, .related-product h3:before,
    .woocommerce-cart .shop_table.cart thead tr,
    .owl-theme .owl-controls .owl-page.active span, .owl-theme .owl-controls.clickable .owl-page:hover span,
    .navbar-inverse .navbar-nav>li>a:after, .navbar-inverse .navbar-nav>li>a:focus:after,
    div.bbp-submit-wrapper .button,
    #bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current,
    header .dropdown-menu>li>a:hover:before, header .dropdown-menu>li>a:focus:before{
        background-color:<?php echo esc_attr($main_color_1) ?>;
    }
    
    .btn-primary,
    .ia-icon:hover,.ia-icon-box:hover .ia-icon,
    .features-control-item.active .ia-icon,
    .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit, .woocommerce #content input.button, .woocommerce-page a.button,
    .woocommerce-page button.button, .woocommerce-page input.button, .woocommerce-page #respond input#submit, .woocommerce-page #content input.button,
    .woocommerce #review_form #respond .form-submit input, .woocommerce-page #review_form #respond .form-submit input,
    .woocommerce ul.products li.product.product-category h3:hover {
    	background-color: <?php echo esc_attr($main_color_1) ?>;
    	border-color: <?php echo esc_attr($main_color_1) ?>;
    }
    .woocommerce span.onsale, .woocommerce-page span.onsale,
    .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit, .woocommerce #content input.button, .woocommerce-page a.button,
    .woocommerce .widget_price_filter .ui-slider .ui-slider-handle, .woocommerce-page .widget_price_filter .ui-slider .ui-slider-handle,
.woocommerce .widget_price_filter .ui-slider-horizontal .ui-slider-range, .woocommerce-page .widget_price_filter .ui-slider-horizontal .ui-slider-range,
    .woocommerce-page button.button, .woocommerce-page input.button, .woocommerce-page #respond input#submit, .woocommerce-page #content input.button,
	.wpb_accordion .wpb_accordion_wrapper .ui-state-active .ui-icon:before, .wpb_accordion .wpb_accordion_wrapper .ui-state-active .ui-icon:after, .wpb_wrapper .wpb_accordion .wpb_accordion_wrapper .wpb_accordion_header:hover .ui-icon:before, .wpb_wrapper .wpb_accordion .wpb_accordion_wrapper .wpb_accordion_header:hover .ui-icon:after,
    .wpb_wrapper .wpb_toggle:hover:before, .wpb_wrapper .wpb_toggle:hover:after,
    .wpb_wrapper h4.wpb_toggle.wpb_toggle_title_active:before, .wpb_wrapper h4.wpb_toggle.wpb_toggle_title_active:after,
    #bbpress-forums li.bbp-header,
    .woocommerce #review_form #respond .form-submit input, .woocommerce-page #review_form #respond .form-submit input{
        background:<?php echo esc_attr($main_color_1) ?>;
    }
    
    .woocommerce-page button.button, .woocommerce-page input.button, .woocommerce-page #respond input#submit, .woocommerce-page #content input.button, .woocommerce a.button.alt,
    .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit, .woocommerce #content input.button, .woocommerce-page a.button,
    .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce #respond input#submit.alt, .woocommerce #content input.button.alt, .woocommerce-page a.button.alt,
    .woocommerce-page button.button.alt, .woocommerce-page input.button.alt, .woocommerce-page #respond input#submit.alt, .woocommerce-page #content input.button.alt, 
    .woocommerce #review_form #respond .form-submit input, .woocommerce-page #review_form #respond .form-submit input{background:<?php echo esc_attr($main_color_1) ?>;}
    .thumbnail-overlay {
    	background: rgba(<?php echo esc_attr(leaf_hex2rgba($main_color_1,80)); ?>);
    }
    a.button.ia-addtocart{background-color: transparent; border-color:rgba(51,51,51,.05)}
    a.button.ia-addtocart:hover{background-color: <?php echo esc_attr($main_color_1) ?>; border-color: <?php echo esc_attr($main_color_1) ?>;}
    .wpb_wrapper .wpb_content_element .wpb_tabs_nav li.ui-tabs-active a, .wpb_wrapper .wpb_content_element .wpb_tabs_nav li:hover a,
    .vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-top .vc_tta-tab.vc_active > a,
    .woocommerce #content div.product .woocommerce-tabs ul.tabs li.active a, .woocommerce div.product .woocommerce-tabs ul.tabs li.active a, .woocommerce-page #content div.product .woocommerce-tabs ul.tabs li.active a, .woocommerce-page div.product .woocommerce-tabs ul.tabs li.active a{box-shadow: inset 0 -3px 0 <?php echo esc_attr($main_color_1) ?>;}
    @media (min-width: 480px){
    	.vc_tta-tabs:not([class*="vc_tta-gap"]):not(.vc_tta-o-no-fill).vc_tta-tabs-position-left .vc_tta-tab.vc_active > a,
        .wpb_wrapper .wpb_tour.wpb_content_element .wpb_tabs_nav li:hover a,
        .wpb_wrapper .wpb_tour.wpb_content_element .wpb_tabs_nav li.ui-tabs-active a{box-shadow: inset -3px 0 0 <?php echo esc_attr($main_color_1) ?>;}
    }

<?php
}//main color 1

if($nav_bg = leaf_get_option('nav_bg')){
$nav_bg = leaf_hex2rgba($nav_bg,leaf_get_option('nav_opacity',100));
?>
    #main-nav .navbar, #main-nav.light-nav .navbar {
    	background: rgba(<?php echo esc_attr($nav_bg); ?>);
    }
<?php
}//footer_bg
if($footer_bg = leaf_get_option('footer_bg','#2b2b2b')){ ?>
    footer.main-color-2-bg, .main-color-2-bg.back-to-top{
        background-color:<?php echo esc_attr($footer_bg) ?>;
    }
<?php
}//footer_bg


if($loading_spin_color = leaf_get_option( 'loading_spin_color', false)){ ?>
.loader-2 i {
	background:<?php echo esc_attr($loading_spin_color); ?>
}
<?php }
for($i=1; $i<11; $i++){?>
@media (min-width: 992px){
    .ia-post-grid-<?php echo esc_attr($i); ?> .grid-item {
        width: <?php echo esc_attr(100/$i) ?>%;
    }
    .ia-post-grid-<?php echo esc_attr($i); ?>.has-featured-item .grid-item:first-child{
    	width: <?php echo esc_attr(200/$i) ?>%;
    }
}
<?php }
//custom CSS
echo ot_get_option('custom_css','');