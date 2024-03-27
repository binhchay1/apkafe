function lang_toggler() {
    var element = document.getElementById("lang_box_inner");
    element.classList.toggle("show");
}

function show_menu_mob() {
    document.getElementById("nav_wrap").className += 'show_mob_menu';
}

function hide_menu_mob() {
    document.getElementById("nav_wrap").className = '';
}

function on_search() {
    document.getElementById("search_wrap").style.display = "block";
    document.getElementById("kwd").focus();
}

function off_search() {
    document.getElementById("search_wrap").style.display = "none";
}

function scrollToi(id) {
    this.event.preventDefault();
    const yOffset = 0;
    const element = document.getElementById(id);
    const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: 'smooth' });
}

function scrollToc(to_id) {
    this.event.preventDefault();
    var element = document.getElementById(to_id);
    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function () { window.location.hash = to_id; }, 100);
}

function mod_box_toggle() {
    document.getElementById("mod_box_in_wrap").classList.toggle('mod_box_in_hide');
}

function faq_toggle(wrap) {
    document.getElementById(wrap).classList.toggle('faq_hide');
}


function share_this(elem) {
    var url = document.getElementById(elem).getAttribute('data-url');
    var title = document.getElementById(elem).getAttribute('data-title');

    var pop_url = '';

    //console.log(url);

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

if (document.getElementById('apk_rate_wrap')) {
    var apk_rate_wrap = document.getElementById('apk_rate_wrap');
    apk_rate_wrap.addEventListener('rate', function (e) {
        var rval = e.detail;

        var http = new XMLHttpRequest();
        var url = site_base + "ajax.php";
        var params = "act=star_counter&v=" + rval + "&i=cGd1RlgzQUZ4c28zZVhNcmdqbC9nQT09";

        http.open("POST", url, true);
        http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        http.onreadystatechange = function () {
            if (http.readyState == 4 && http.status == 200) {
                var get_data = http.responseText;
                var obj = JSON.parse(get_data);

                if (obj['status'] == '1') {
                    document.getElementById("apk_rate_msg_wrap").innerHTML = '<span class="txt_suc">Rating submitted successfully</span>';
                    document.getElementById("apk_rate_show_wrap").innerHTML = obj['ratings'];

                    var ratings = document.getElementsByClassName('rating');
                    for (var i = 0; i < ratings.length; i++) {
                        var r = new SimpleStarRating(ratings[i]);

                        ratings[i].addEventListener('rate', function (e) {
                            console.log('Rating: ' + e.detail);
                        });
                    }
                }
                else {
                    if (obj['msg'] == 0) {
                        document.getElementById("apk_rate_msg_wrap").innerHTML = "<span class='txt_err'>Something went wrong please try later</span>";
                    }
                    else if (obj['msg'] == 2) {
                        document.getElementById("apk_rate_msg_wrap").innerHTML = "<span class='txt_err'>Rating Already Submitted</span>";
                    }
                    else {
                        document.getElementById("apk_rate_msg_wrap").innerHTML = "<span class='txt_err'>Something went wrong please try later</span>";
                    }
                }
            }
        }
        http.send(params);
    });
}