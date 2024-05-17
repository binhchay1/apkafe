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

jQuery(document).ready(function () {
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

    if (isMobile.any()) {
        scrollToBottom();
    }
});

function scrollToBottom(timedelay = 0) {
    var scrollId;
    var height = 0;
    var minScrollHeight = 10;
    scrollId = setInterval(function () {
        if (height <= document.body.scrollHeight) {
            window.scrollBy(0, minScrollHeight);
        }
        else {
            clearInterval(scrollId);
        }
        height += minScrollHeight;
    }, timedelay);
}

