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
    if (isMobile.any()) {
        scrollToBottom();
    }

    var owl = jQuery('.owl-carousel');
    owl.owlCarousel({
        items: 1,
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 2000,
        autoplayHoverPause: true
    });
});

function scrollToBottom(timedelay = 0) {
    var height = 0;
    var minScrollHeight = 1;
    scrollId = setInterval(function () {
        if (height <= document.body.scrollHeight) {
            window.scrollBy(0, minScrollHeight);

        } else {
            clearInterval(scrollId);
        }

        height += minScrollHeight;
    }, timedelay);
}

function handleTouch() {
    if (isMobile.any()) {
        clearInterval(scrollId);
    }
}

function show_menu_mobile() {
    jQuery('#nav_new').removeClass('hide-mobile');
}

function closeMenu() {
    jQuery('#nav_new').addClass('hide-mobile');
}