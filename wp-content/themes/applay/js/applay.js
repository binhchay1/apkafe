jQuery(document).ready(function (t) {

    jQuery(".blog-title.title.entry-title").hide(), jQuery(".search-toggle").click(function (t) {
        return jQuery("body").toggleClass("enable-search"), jQuery("body").hasClass("enable-search"), !1
    }), jQuery(".init-carousel").each(function () {
        jQuery(this).attr("id");
        var t = jQuery(this).data("autoplay"),
            a = jQuery(this).data("items"),
            e = jQuery(this).data("navigation");
        jQuery(this).hasClass("single-carousel") ? jQuery(this).owlCarousel({
            singleItem: !0,
            autoHeight: !0,
            autoPlay: t,
            addClassActive: !0,
            stopOnHover: !0,
            slideSpeed: 600,
            navigation: !!e,
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"]
        }) : jQuery(this).owlCarousel({
            autoPlay: t,
            items: a || 4,
            itemsDesktop: !a && 4,
            itemsDesktopSmall: a ? a > 3 && 3 : 3,
            singleItem: 1 == a,
            slideSpeed: 500,
            addClassActive: !0,
            navigation: !!e,
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"]
        })
    }), jQuery(".post-slider-prev").click(function (t) {
        return jQuery(this).closest(".post-slider-carousel").data("owlCarousel").prev(), !1
    }), jQuery(".post-slider-next").click(function (t) {
        return jQuery(this).closest(".post-slider-carousel").data("owlCarousel").next(), !1
    }), 977 > jQuery(window).width() && jQuery(".grid-listing").owlCarousel({
        addClassActive: !0
    }), jQuery("[data-countdown]").each(function () {
        var t = jQuery(this),
            a = jQuery(this).data("countdown"),
            e = t.data("daylabel"),
            s = t.data("hourlabel"),
            i = t.data("minutelabel"),
            n = t.data("secondlabel"),
            o = t.data("showsecond");
        t.countdown(a, {
            elapse: !0
        }).on("update.countdown", function (a) {
            a.elapsed, t.html(a.strftime('<span class="countdown-block"><span class="countdown-number main-color-1-bg dark-div minion">%D</span><span class="countdown-label main-color-1">' + e + '</span></span><span class="countdown-block"><span class="countdown-number main-color-1-bg dark-div minion">%H</span><span class="countdown-label main-color-1">' + s + '</span></span><span class="countdown-block"><span class="countdown-number main-color-1-bg dark-div minion">%M</span><span class="countdown-label main-color-1">' + i + "</span></span>" + (o ? '<span class="countdown-block"><span class="countdown-number main-color-1-bg dark-div minion">%S</span><span class="countdown-label main-color-1">' + n + "</span></span>" : "")))
        })
    }), jQuery(".mobile-menu-toggle").click(function (t) {
        return jQuery("body").toggleClass("enable-mobile-menu"), !1
    }), jQuery(document).mouseup(function (t) {
        var a = jQuery(".mobile-menu-wrap, #off-canvas-search");
        a.is(t.target) || 0 !== a.has(t.target).length || jQuery("body").removeClass("enable-mobile-menu")
    }), jQuery(".mobile-menu li a").on("click", function () {
        jQuery("body").removeClass("enable-mobile-menu")
    }), jQuery('a[href*="#"]:not([href="#"])').click(function () {
        if (jQuery(this).hasClass("featured-tab") || jQuery(this).hasClass("popup-gallery-comment") || jQuery(this).parents("ul").hasClass("tabs") || jQuery(this).hasClass("comment-reply-link") || jQuery(this).hasClass("ui-tabs-anchor") || jQuery(this).data("vc-container") || jQuery(this).parents("div").hasClass("wpb_tour_next_prev_nav")) return !0;
        if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") || location.hostname == this.hostname) {
            var t = jQuery(this.hash);
            if ((t = t.length ? t : jQuery("[name=" + this.hash.slice(1) + "]")).length) return jQuery("html,body,#body-wrap").animate({
                scrollTop: t.offset().top - 50
            }, 660), !0
        }
    });
    var a = jQuery(window);
    jQuery(".pc .ia_paralax .wpb_row, .pc .is-paralax").each(function () {
        var t = jQuery(this),
            e = -((a.scrollTop() - t.offset().top + 30) / 5);
        t.attr("style", "background-position: 50% " + e + "px !important; transition: none;"), jQuery(window).scroll(function () {
            var e = -((a.scrollTop() - t.offset().top + 30) / 5);
            t.attr("style", "background-position: 50% " + e + "px !important; transition: none;")
        })
    }), jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").addClass("buttons_added").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />'), jQuery(".buttons_added #minus1").click(function (t) {
        var a = parseInt(jQuery(this).siblings("input.qty").val()) - 1;
        a >= 0 && (jQuery(this).siblings("input.qty").val(a), jQuery('.woocommerce-cart-form *[name="update_cart"]').prop("disabled", !1))
    }), jQuery(".buttons_added #add1").click(function (t) {
        var a = parseInt(jQuery(this).prev().val()) + 1;
        jQuery(this).prev().val(a), jQuery('.woocommerce-cart-form *[name="update_cart"]').prop("disabled", !1)
    }), jQuery(document.body).on("updated_cart_totals", function () {
        jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").addClass("buttons_added").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />'), jQuery(".buttons_added #minus1").click(function (t) {
            var a = parseInt(jQuery(this).siblings("input.qty").val()) - 1;
            a >= 0 && (jQuery(this).siblings("input.qty").val(a), jQuery('.woocommerce-cart-form *[name="update_cart"]').prop("disabled", !1))
        }), jQuery(".buttons_added #add1").click(function (t) {
            var a = parseInt(jQuery(this).prev().val()) + 1;
            jQuery(this).prev().val(a), jQuery('.woocommerce-cart-form *[name="update_cart"]').prop("disabled", !1)
        })
    }), jQuery(document).mouseup(function (t) {
        var a = jQuery("#off-canvas-search form");
        a.is(t.target) || 0 !== a.has(t.target).length || jQuery("body").removeClass("enable-search")
    }), jQuery(".woocommerce-loop-product__link").attr("aria-label", "Language");

    jQuery("#menu-item-489 a img").attr("width", "16"), jQuery("#menu-item-489 a img").attr("height", "11");
    jQuery(".lang-item-27 a img").attr("width", "16"), jQuery(".lang-item-27 a img").attr("height", "11");
    jQuery(".lang-item-909 a img").attr("width", "16"), jQuery("#menu-item-489 a img").attr("height", "11");
    jQuery("#email").attr("style", "height: 46px !important");

    let k = jQuery('.owl-item .single-gallery-item .colorbox-grid img');
    for (let v = 0; v < k.length; v++) {
        k[v].setAttribute("alt", "owl item");
    }

    jQuery('.cm-text').prepend('<label for="comment">Your Comment*</label>');
    let x = jQuery('.colorbox-grid');
    for (let u = 0; u < x.length; u++) {
        x[u].removeAttribute('href');
    }

    let n = jQuery('.popup-data-content a');
    for (let g = 0; g < n.length; g++) {
        n[g].removeAttribute('href');
    }

    jQuery('#body').attr("style", "margin-top: 0 !important");
    jQuery('.top-sidebar').attr("style", "margin-top: 0 !important");

    jQuery("#download-now").click(function (e) {
        e.preventDefault();
        jQuery('html,body').animate({
            scrollTop: jQuery("#app-store-link").offset().top - 140
        },
            'slow');
    });

    window.addEventListener('click', function (e) {
        if (jQuery('.searching-show').is(":visible")) {
            if (document.getElementById('searching-show').contains(e.target)) {
            } else {
                jQuery('.searching-hide').show();
                jQuery('.search-mask').show();
                jQuery('.searching-show').hide();
            }
        } else {
            if (this.document.getElementById('search-mask').contains(e.target)) {
                jQuery('.searching-hide').hide();
                jQuery('.search-mask').hide();
                jQuery('.searching-show').show();
            }
        }

        if (jQuery('.ll').is(":visible")) {
            if (document.getElementById('form_query_mobile').contains(e.target)) {
            } else {
                jQuery('.ll').hide();
                jQuery('.menu_btn').show();
                jQuery('#btn-search-m').show();
                jQuery('.logo').show();
                jQuery('.m_logo').show();
            }
        } else {
            if (this.document.getElementById('btn-search-m').contains(e.target)) {
                jQuery('.ll').show();
                jQuery('.menu_btn').hide();
                jQuery('#btn-search-m').hide();
                jQuery('.logo').hide();
                jQuery('.m_logo').hide();
            }
        }
    });

    (function () {
        var screenWidth = 996;
        window.goBack = function () {
            if (document.referrer) {
                history.go(-1);
            } else {
                location.href = '/';
            }
        }
        var navPosition = jQuery(".ar_fix").length > 0 ? 'right' : 'left';
        window.closeMenu = function () {
            jQuery('#nav_new').css(navPosition, '-480px');
            jQuery('#shadow').hide();
            jQuery('html').css('overflow-y', 'auto');
            jQuery('body').css('overflow-y', 'auto');
            jQuery('#article_item:not(".selected")').removeClass('open');
            setTimeout(function () {
                jQuery('#header').removeClass('open_menu');
            }, 100);
        }
        window.openMenu = function () {
            location.hash = "menu";

            jQuery('html').css('overflow-y', 'hidden');
            jQuery('body').css('overflow-y', 'hidden');
            jQuery('#nav_new').css(navPosition, '0');
            jQuery('#article_item:not(".selected")').addClass('open selected');
            jQuery('#header').addClass('open_menu');
            jQuery('#shadow').show();
        }
        jQuery(window).on('hashchange', function (event) {
            var isMenuOpen = jQuery('.shadow').css('display') === 'block';
            var e = event.originalEvent;

            if (!e.oldURL) return;

            if (e.oldURL.substr(-5) === '#menu' && isMenuOpen) closeMenu(true);

            if (e.newURL.substr(-5) === '#menu' && !isMenuOpen) openMenu();
        });

        var startX, deltaX;
        jQuery('.nav_container').on('touchstart', function (event) {
            var touches = event.touches;
            if (touches && touches.length) {
                startX = touches[0].pageX;
            }
        });

        jQuery('.nav_container').on('touchmove', function (event) {
            var touches = event.touches;
            if (touches && touches.length) {
                deltaX = startX - touches[0].pageX;
            }
        });

        jQuery('.nav_container').on('touchend', function (event) {
            if (jQuery('.ar_fix').length > 0) {
                if (deltaX < -80) {
                    closeMenu();
                }
            } else {
                if (deltaX > 80) {
                    closeMenu();
                }
            }
            deltaX = 0;
        });

        jQuery('#nav_new').on('click', '.many > a, .many > span', function (event) {
            if (window.innerWidth < screenWidth) {
                var jQuerythat = jQuery(this).parent('.many');
                if (jQuerythat.hasClass('open')) {
                    jQuerythat.removeClass('open')
                } else {
                    jQuerythat.addClass('open');
                }
            }
        });

        jQuery(document).bind("click", function (e) {
            if (window.innerWidth < screenWidth) {
                var current_box = jQuery('.current_box');
                if (jQuery(e.target).closest('.current_box').length > 0) {
                    current_box.hasClass('open') ? current_box.removeClass('open') : current_box.addClass('open');
                } else {
                    current_box.removeClass('open');
                }
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > screenWidth) {
                jQuery('#nav_new .many').removeClass('open');
                jQuery('.current_box').removeClass('open');
                closeMenu();
            }
        });


        window.use_search_new_css = true;

    })();

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        jQuery('.main-body').attr('style', 'display: block; margin-left: 10px; margin-right: 10px');
        jQuery('.normal-sidebar').attr('style', 'margin-left: 10px; margin-top: 10px;');
        jQuery('.search-box').attr('style', 'display: block');
        jQuery('.hide-mobile').attr('style', 'display: none !important;');
    } else {
        jQuery('.main-body').attr('style', 'display: flex');
        jQuery('.search-box-m').attr('style', 'display: none !important;');
    }

}), jQuery(window).scroll(function (t) {
    jQuery(window).width() > 992 ? jQuery(".fixed-effect").each(function (t, a) {
        var e = jQuery(window).height(),
            s = jQuery(this).offset().top,
            i = jQuery(".fixed-effect-inner", this).outerHeight(),
            n = jQuery(document).scrollTop();
        if (n + e >= s) {
            var o = (n + e - s) / i;
            jQuery(".fixed-effect-inner", this).css("opacity", o), jQuery(".fixed-effect-inner", this).css("margin-top", (o - 1) * 300)
        }
    }) : jQuery(".fixed-effect").each(function (t, a) {
        jQuery(".fixed-effect-inner", this).css("opacity", 1), jQuery(".fixed-effect-inner", this).css("margin-bottom", 0)
    })
}), jQuery(window).resize(function (t) {
    977 > jQuery(window).width() ? jQuery(".grid-listing").each(function (t, a) {
        jQuery(this).hasClass("owl-carousel") || jQuery(this).owlCarousel({
            addClassActive: !0
        })
    }) : jQuery(".grid-listing").each(function (t, a) {
        jQuery(this).hasClass("owl-carousel") && jQuery(this).data("owlCarousel").destroy()
    }), jQuery(".fixed-effect").each(function (t, a) {
        var e = jQuery(".fixed-effect-inner", this).outerHeight();
        jQuery(this).css("height", e)
    })
}), jQuery(window).scroll(function (t) {
    if (jQuery(window).width() > 991 && jQuery(".summary.portrait-screenshot").length && jQuery(".images .ias-devide-wrap").length) {
        var a = jQuery(window).scrollTop(),
            e = jQuery(".summary.portrait-screenshot").outerHeight();
        jQuery(".summary.portrait-screenshot").offset().top;
        var s = jQuery(".images .ias-devide-wrap").height(),
            i = jQuery(".images .ias-devide-wrap").offset().top,
            n = 50 + jQuery("#wpadminbar").height();
        i - a - n - 30 <= 0 ? (margin_top = a - i + n + 30) >= s - e && (margin_top = s - e) : margin_top = 0, jQuery(".summary.portrait-screenshot").css("margin-top", margin_top)
    } else jQuery(".summary.portrait-screenshot").css("margin-top", 0)
}), jQuery.each(jQuery.browser, function (t) {
    return jQuery("body").addClass(t), !1
});
var os = ["iphone", "ipad", "windows", "mac", "linux", "android", "mobile"],
    match = navigator.appVersion.toLowerCase().match(RegExp(os.join("|")));
match && jQuery("body").addClass(match[0]), void 0 === match[0] && (match[0] = ""), (-1 != navigator.appVersion.indexOf("Win") || -1 != navigator.appVersion.indexOf("Mac") || -1 != navigator.appVersion.indexOf("X11") || "windows" == match[0] || "mac" == match[0]) && "iphone" != match[0] && "ipad" != match[0] ? jQuery("body").addClass("pc") : jQuery("body").addClass("mobile"), jQuery(window).load(function (t) {
    jQuery("#pageloader").fadeOut(500)
});