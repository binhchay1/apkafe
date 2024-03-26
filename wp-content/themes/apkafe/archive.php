<?php
get_header(); ?>

<div class="main_bar">
    <div class="widget">
        <div class="widget_head">
            <ul id="breadcrumbs" class="bread_crumb">
                <li><a href="https://apkmodget.com/">Home</a></li>
                <li><i class="fa fa-angle-double-right"></i></li>
                <li><a class="active" href="https://apkmodget.com/action/">Action</a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div id="main_list_item" class="main_list_item"><a class="side_list_item" href="https://apkmodget.com/games/fnaf-2-apk-3/">
                <img class="item_icon lazyloaded" width="80" height="80" src="https://apkmodget.com/media/2023/10/_1/80x80/fnaf-2-apk_1c29e.jpg" data-src="https://apkmodget.com/media/2023/10/_1/80x80/fnaf-2-apk_1c29e.jpg" alt="FnaF 2 Apk 2.0 Download Full Version For Android">
                <p class="title">FnaF 2 Apk 2.0 Download Full Version For Android</p>
                <p class="category">v2.0 + MOD: Unlocked</p>
            </a>
        </div>
        <div class="clear mb20"></div>
        <div class="ac" id="main_list_item_next"><a onclick="get_more_cat_items();" class="more_link" href="javascript:void(0);">Load More Action Updates <i class="fa fa-angle-double-down"></i></a></div>
        <script>
            var tsr = 0;

            function get_more_cat_items() {
                tsr++;

                document.getElementById("main_list_item_next").innerHTML = '<p style="text-align:center; font-size:20px; line-height:28px; font-weight:bold; color:blue; margin-top:20px; margin-bottom:20px;">Loading Please wait ...</p>';

                var http = new XMLHttpRequest();
                var url = "https://apkmodget.com/ajax.php";
                var params = "act=get_more_cat_items&cid=akdBYXFtZUtyekIxRlpsbjFFNCtIUT09&asl=ZStrUk9nejlFd0F1VGMzU2o0ZzJSdz09&m=" + tsr;

                http.open("POST", url, true);
                http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                http.onreadystatechange = function() {
                    if (http.readyState == 4 && http.status == 200) {
                        document.getElementById("main_list_item_next").innerHTML = '';

                        var get_data = http.responseText;
                        var obj = JSON.parse(get_data);

                        document.getElementById('main_list_item').innerHTML += obj['data'];


                        if (obj['next'] == 1) {
                            document.getElementById("main_list_item_next").innerHTML = '<a onClick="get_more_cat_items();" class="more_link" href="javascript:void(0);">Load More Action Updates <i class="fa fa-angle-double-down"></i></a>';
                        }
                    }
                }
                http.send(params);
            }
        </script>
    </div>
    <div class="clear mb15"></div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>