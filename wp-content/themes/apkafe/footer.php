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
                "url": "https://apkafe.com/wp-content/uploads/242x38.png",
                "width": 242,
                "height": 38
            },
            "brand": "Apkafe",
            "publishingPrinciples": "https://apkafe.com/about-us/",
            "sameAs": [
                "https://www.facebook.com/apkafedotcom/",
                "https://twitter.com/Apkafeappstore/",
                "https://www.youtube.com/@apkafe2947",
                "https://www.instagram.com/apkafedotcom/",
                "https://linktr.ee/apkafebeststore",
                "https://www.tumblr.com/apkafe",
                "https://www.behance.net/apkafebeststore",
                "https://apkafe.weebly.com/",
                "https://www.linkedin.com/company/apkafe/"
            ],
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "City ５, Room 409, 3-5-10, Mukaihara",
                "addressLocality": "Tokyo",
                "addressRegion": "JP-13",
                "postalCode": "〒173-0036",
                "addressCountry": "JP"
            },
            "parentOrganization": {
                "url": "https://modobom.com/",
                "brand": "Modobom International Company",
                "name": "Modobom International Company",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "D' Capital, Vincom, Building C3 D, 119 D. Tran Duy Hung, Trung Hoa, Cau Giay,",
                    "addressLocality": "Ha Noi",
                    "addressRegion": "HN",
                    "postalCode": "100000",
                    "addressCountry": "VN"
                },
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://modobom.com/wp-content/uploads/2021/10/logo-modobom-resize.png",
                    "width": 198,
                    "height": 41
                },
                "sameAs": [
                    "https://www.facebook.com/profile.php?id=61559827227892",
                    "https://x.com/Modobomcompany",
                    "https://www.linkedin.com/company/modobom-co-ltd/mycompany/",
                    "https://www.youtube.com/@ModobomInternationalCompany",
                    "https://www.instagram.com/modobomcompany/",
                    "https://linktr.ee/modobomcompanyinternational",
                    "https://www.tumblr.com/modobomcompany",
                    "https://www.behance.net/modobomcompany"
                ]
            }
        },
        "description": "Hunt for hot games and best apps. Apkafe.com is home to a variety of apps and games to download with descriptions and detailed instructions for each application."
    }
</script>

</html>