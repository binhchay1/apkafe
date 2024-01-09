<?php
$paths = explode('/', $_SERVER['REQUEST_URI']);
$url = home_url();
$listLang = get_template_directory() . '/languages/en.php';
$pos = strpos($url, '/ja');
if ($pos > 0) {
    $listLang = get_template_directory() . '/languages/ja.php';
}

$pos = strpos($url, '/th');
if ($pos > 0) {
    $listLang = get_template_directory() . '/languages/th.php';
}

require $listLang;
if (in_array('404', $paths)) { ?>
    <style>
        #notfound {
            position: relative;
            height: 100vh
        }

        #notfound .notfound {
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%)
        }

        .notfound {
            max-width: 767px;
            width: 100%;
            line-height: 1.4;
            padding: 0 15px
        }

        .notfound .notfound-404 {
            position: relative;
            height: 150px;
            line-height: 150px;
            margin-bottom: 25px
        }

        .notfound .notfound-404 h1 {
            font-family: titillium web, sans-serif;
            font-size: 186px;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            background: url(../img/text.png);
            background-size: cover;
            background-position: center
        }

        .notfound h2 {
            font-family: titillium web, sans-serif;
            font-size: 26px;
            font-weight: 700;
            margin-top: 60px !important;
        }

        .notfound p {
            font-family: montserrat, sans-serif;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 0;
            text-transform: uppercase
        }

        .notfound a {
            font-family: titillium web, sans-serif;
            display: inline-block;
            text-transform: uppercase;
            color: #fff;
            text-decoration: none;
            border: none;
            background: #5c91fe;
            padding: 10px 40px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 1px;
            margin-top: 15px;
            -webkit-transition: .2s all;
            transition: .2s all
        }

        .notfound a:hover {
            opacity: .8
        }

        @media only screen and (max-width:767px) {
            .notfound .notfound-404 {
                height: 110px;
                line-height: 110px
            }

            .notfound .notfound-404 h1 {
                font-size: 120px
            }
        }
    </style>
    <div class="notfound">
        <div class="notfound-404">
            <h1>404</h1>
        </div>
        <h2><?php echo $lang['Oops! This Page Could Not Be Found'] ?></h2>
        <p><?php echo $lang['Sorry but the page you are looking for does not exist, have been removed. name changed or is temporarily unavailable'] ?></p>
        <a href="/"><?php echo $lang['Go To Homepage'] ?></a>
    </div>
<?php } else { ?>
    <div class="single-content-none content-pad">
        <div class="row">
            <div class="col-md-4 col-md-offset-4" role="main">
                <a id="ia-icon-box-999" class="media ia-icon-box search-toggle" title="<?php echo esc_attr(__('Search', 'leafcolor')) ?>">
                    <div class="text-center icon-center-loop-none">
                        <div class="ia-icon">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                    <div class="media-body text-center">
                        <h4 class="media-heading"><?php echo $lang['No results found'] ?></h4>
                    </div>
                    <div class="clearfix"></div>
                </a>
            </div>
        </div>
    </div>
<?php } ?>