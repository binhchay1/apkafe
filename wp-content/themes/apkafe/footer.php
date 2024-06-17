<?php
/*
 *     Footer
 */
?>

<footer>
    <div class="container">
        <div class="ftr_link_box">
            <?php if (!empty(ot_get_option('menu_footer'))) {  ?>
                <?php foreach (ot_get_option('menu_footer') as $menu) { ?>
                    <a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <div class="clear mb15"></div>
    <div class="footer_main">
        <div class="container">
            <div class="ftr_social_box">
                <?php if (ot_get_option('share_facebook') != '') { ?>
                    <a class="fsb_facebook" target="_blank" href="<?php echo ot_get_option('share_facebook') ?>"><i class="fa fa-facebook"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_twitter') != '') { ?>
                    <a class="fsb_twitter" target="_blank" href="<?php echo ot_get_option('share_twitter') ?>"><i class="fa fa-twitter"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_linkedin') != '') { ?>
                    <a class="fsb_linkedin" target="_blank" href="<?php echo ot_get_option('share_linkedin') ?>"><i class="fa fa-linkedin"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_youtube') != '') { ?>
                    <a class="fsb_youtube" target="_blank" href="<?php echo ot_get_option('share_youtube') ?>"><i class="fa fa-youtube"></i></a>
                <?php } ?>
                <?php if (!empty(ot_get_option('custom_share'))) { ?>
                    <?php foreach (ot_get_option('custom_share') as $link) { ?>
                        <a class="fsb_custom" target="_blank" href="<?php echo $link['link_share'] ?>"><img src="<?php echo $link['icon'] ?>" width="30" height="30"></i></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="footer_bottom">
        <div class="container" style="display: flex;">
            <div><img src="<?php echo get_template_directory_uri() . '/images/logo-modobom-resize.png' ?>" /></div>
            <div class="ac"><?php echo ot_get_option('copyright') ?></div>
        </div>
    </div>
    <div class="clear"></div>
</footer>

<?php if (ot_get_option('custom_script_footer') != '') {
    echo ot_get_option('custom_script_footer');
} ?>

<?php wp_footer() ?>
</body>
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "WebPage",
        "mainEntityOfPage": {
            "@type": "Webpage",
            "@id": "https://apkafe.com/"
        },
        "headline": "Apkafe",
        "publisher": {
            "@type": "Organization",
            "name": "Apkafe",
            "url": "https://apkafe.com/",
            "logo": {
                "@type": "ImageObject",
                "url": "https://www.allrecipes.com/thmb/Z9lwz1y0B5aX-cemPiTgpn5YB0k=/112x112/filters:no_upscale():max_bytes(150000):strip_icc()/allrecipes_logo_schema-867c69d2999b439a9eba923a445ccfe3.png",
                "width": 112,
                "height": 112
            },
            "brand": "Apkafe",
            "publishingPrinciples": "https://www.allrecipes.com/about-us-6648102#toc-editorial-guidelines",
            "sameAs": [
                "https://www.facebook.com/allrecipes",
                "https://www.instagram.com/allrecipes/",
                "https://www.pinterest.com/allrecipes/",
                "https://www.tiktok.com/@allrecipes",
                "https://www.youtube.com/user/allrecipes/videos",
                "https://twitter.com/Allrecipes",
                "https://flipboard.com/@Allrecipes",
                "https://en.wikipedia.org/wiki/Allrecipes.com",
                "http://linkedin.com/company/allrecipes.com"
            ],
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "225 Liberty Street, 4th Floor",
                "addressLocality": "New York",
                "addressRegion": "NY",
                "postalCode": "10281",
                "addressCountry": "USA"
            },
            "parentOrganization": {
                "url": "https://www.dotdashmeredith.com",
                "brand": "Dotdash Meredith",
                "name": "Dotdash Meredith",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "225 Liberty Street, 4th Floor",
                    "addressLocality": "New York",
                    "addressRegion": "NY",
                    "postalCode": "10281",
                    "addressCountry": "USA"
                },
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://www.allrecipes.com/thmb/UrZUYJQsAwN-jGDeycKgTz1FKPg=/234x60/filters:no_upscale():max_bytes(150000):strip_icc()/dotdash-logo-e9cde67f713a45c68ce5def51d3ca409.jpg",
                    "width": 234,
                    "height": 60
                },
                "sameAs": [
                    "https://en.wikipedia.org/wiki/Dotdash_Meredith",
                    "https://www.instagram.com/dotdashmeredith/",
                    "https://www.linkedin.com/company/dotdashmeredith/",
                    "https://www.facebook.com/dotdashmeredith/"
                ]
            }
        },
        "description": "Everyday recipes with ratings and reviews by home cooks like you. Find easy dinner ideas, healthy recipes, plus helpful cooking tips and techniques."
    }
</script>

</html>