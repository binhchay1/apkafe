

function get_more_cat_items() {
    var tsr = 0;
    tsr++;

    document.getElementById("main_list_item_next").innerHTML = '<p style="text-align:center; font-size:20px; line-height:28px; font-weight:bold; color:blue; margin-top:20px; margin-bottom:20px;">Loading Please wait ...</p>';

    var http = new XMLHttpRequest();
    var url = "https://apkmodget.com/ajax.php";
    var params = "act=get_more_cat_items&cid=akdBYXFtZUtyekIxRlpsbjFFNCtIUT09&asl=ZStrUk9nejlFd0F1VGMzU2o0ZzJSdz09&m=" + tsr;

    http.open("POST", url, true);
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function () {
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

function get_more_latest_items() {
    var tsr = 0;
    tsr++;

    document.getElementById("main_list_item_next").innerHTML = '<p style="text-align:center; font-size:20px; line-height:28px; font-weight:bold; color:blue; margin-top:20px; margin-bottom:20px;">Loading Please wait ... ...</p>';

    var http = new XMLHttpRequest();
    var url = "https://apkmodget.com/ajax.php";
    var params = "act=get_more_latest_items&asl=ZStrUk9nejlFd0F1VGMzU2o0ZzJSdz09&m=" + tsr;

    http.open("POST", url, true);
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function () {
        if (http.readyState == 4 && http.status == 200) {
            document.getElementById("main_list_item_next").innerHTML = '';

            var get_data = http.responseText;
            var obj = JSON.parse(get_data);

            document.getElementById('main_list_item').innerHTML += obj['data'];


            if (obj['next'] == 1) {
                document.getElementById("main_list_item_next").innerHTML = '<a onClick="get_more_latest_items();" class="more_link" href="javascript:void(0);">Load More Latest Updates </a>';
            }
        }
    }
    http.send(params);
}