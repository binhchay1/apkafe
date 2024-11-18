function scrollToc(to_id) {
    this.event.preventDefault();
    var element = document.getElementById(to_id);
    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function () { window.location.hash = to_id; }, 100);
}

function share_this(elem) {
    var url = document.getElementById(elem).getAttribute('data-url');
    var title = document.getElementById(elem).getAttribute('data-title');

    var pop_url = '';

    if (elem == 'share_facebook') {
        pop_url = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
    }
    else if (elem == 'share_twitter') {
        pop_url = 'https://twitter.com/intent/tweet?text=' + title + '&url=' + url;
    }
    else if (elem == 'share_reddit') {
        pop_url = 'http://www.reddit.com/submit?url=' + url + '&title=' + title;
    }
    else if (elem == 'share_pinterest') {
        pop_url = 'http://www.pinterest.com/pin/create/button/?url=' + url + '&description=' + title;
    }

    window.open(pop_url, "PopupWindow", "width=500,height=500,scrollbars=yes,resizable=no");
}

var ratings = document.getElementsByClassName('rating');
var scrollId;
const isMobile = {
    Android: function () {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function () {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function () {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function () {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function () {
        return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
    },
    any: function () {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

jQuery(document).ready(function () {
    var owl = jQuery('.owl-carousel');
    owl.owlCarousel({
        items: 1,
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 2000,
        autoplayHoverPause: true
    });

    jQuery('.js-open-write-review').on('click', function () {
        jQuery('#fancybox-container-1').show();
    });
});

jQuery(document).on('mousemove', '.stars-holder.editable-rating', function (e) {
    jQuery(".js-full-stars").css("animation", "none");
    jQuery(".js-animation-full-stars").css("animation", "none");
    jQuery(".js-animation-full-stars").css("display", "none");
    var percent = scorePercent(e, this);
    jQuery(this).find('.full-stars').width(percent + "%");
}).on('click', '.stars-holder.editable-rating', function (e) {
    e.stopPropagation();
    var score = Math.round(scorePercent(e, this) * 10 / 100) / 20 * 10;
    if (jQuery(this).next('input[type="hidden"]').length > 0) {
        jQuery(this).next('input[type="hidden"]').val(score).change();
    }

    if (jQuery(this).closest('.overall-score').find('.score-numbers').length > 0) {
        jQuery(this).closest('.overall-score').find('.score-numbers').text(score);
    }

    if (jQuery(this).closest('#new-user-review').length > 0) {
        var count = 0;
        var total = 0;
        jQuery(this).closest('#new-user-review').find('.stars-holder').next('input[type="hidden"]').each(function () {
            if (jQuery(this).val() > 0) {
                count++;
                total += +jQuery(this).val();
            }
        });
        if (count > 0) {
            jQuery(this).closest('#new-user-review').find('.overall-score .avg-rate').text(Math.round(total / count * 10) / 10);
            var stars_width = 0;
            stars_width = Math.round(total / count * 10) / 10 * 20 + '%';
            stars_width_num = Math.round(total / count * 10) / 10 * 20;
            jQuery(this).closest('#new-user-review').find('.overall-score .full-stars-all').width(stars_width);
        }
    }
}).on('mouseleave', '.stars-holder.editable-rating', function () {
    var width = 0;
    if (jQuery(this).next('input[type="hidden"]').length > 0) {
        width = jQuery(this).next('input[type="hidden"]').val() * 20 + '%';
        width_num = jQuery(this).next('input[type="hidden"]').val() * 20;
    }
    jQuery(this).find('.full-stars').width(width);
});

function handleTouch() {
    if (isMobile.any()) {
        clearInterval(scrollId);
    }
}

function closeBoxReview() {
    jQuery('#fancybox-container-1').hide();
}

function show_menu_mobile() {
    jQuery('.background-nav-new').removeClass('hide-mobile');
    jQuery('#nav_new').removeClass('hide-mobile');
    jQuery('body').addClass('none-scroll');
}

function closeMenu() {
    jQuery('.background-nav-new').addClass('hide-mobile');
    jQuery('#nav_new').addClass('hide-mobile');
    jQuery('body').removeClass('none-scroll');
}

function scorePercent(e, stars_holder) {
    var width = jQuery(stars_holder).width();
    var left = e.pageX - jQuery(stars_holder).offset().left;
    var offset = Math.min(Math.max(0, left), width);
    return Math.round((offset / width * 100 / 20) + 0.49) * 20;
}

function backToHome() {
    window.location.href = '/';
}
