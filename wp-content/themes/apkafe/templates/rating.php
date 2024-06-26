<?php
global $wpdb;

$result = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM wp_user_review WHERE post_id = '%d'",
        (int) get_the_ID()
    )
);

$total5 = 0;
$total4 = 0;
$total3 = 0;
$total2 = 0;
$total1 = 0;

foreach ($result as $review) {
    if ($review->score == 5) {
        $total5 += 1;
    }

    if ($review->score == 4) {
        $total4 += 1;
    }

    if ($review->score == 3) {
        $total3 += 1;
    }

    if ($review->score == 2) {
        $total2 += 1;
    }

    if ($review->score == 1) {
        $total1 += 1;
    }
}

if (count($result) == 0) {
    $width5 = 0;
    $width4 = 0;
    $width3 = 0;
    $width2 = 0;
    $width1 = 0;
} else {
    $width5 = $total5 / count($result) * 100;
    $width4 = $total4 / count($result) * 100;
    $width3 = $total3 / count($result) * 100;
    $width2 = $total2 / count($result) * 100;
    $width1 = $total1 / count($result) * 100;
}

?>
<div class="mb-4">
    <div class="d-flex align-items-center mb-3 ">
        <h2 class="atitle">User Reviews</h2>
        <div class="ms-auto btn btn-outline-primary js-open-write-review d-flex align-items-center">
            <svg style="margin-right: 10px;" class="me-1" width="24" height="24" fill="#0d6efd">
                <use xlink:href="#icon-write-review"></use>
            </svg>
            Write a Review
        </div>
    </div>
    <div class="rating row mb-4 ">
        <div class="col-5 ">
            <div class="rating-left d-flex flex-column align-items-center ">
                <div class="rating-score">
                    <?php echo count($result) ?> </div>
                <div class="star-container"><svg style="color:#ffc107" width="30" height="30" class="mb-2 mt-1">
                        <use xlink:href="#icon-star-rating"></use>
                    </svg></div>

                <div class="d-flex align-items-center ">
                    <?php echo count($result) ?> user reviews </div>
            </div>
        </div>
        <div class="col-7 ">
            <div class="rating-right ">
                <div class="rating-item d-flex align-items-center ">
                    <div class="flex-shrink-0 me-2 d-flex align-items-center justify-content-between fs-16">5 <svg style="color:#cacaca" height="14" width="14" class="ms-1">
                            <use xlink:href="#icon-star-rating"></use>
                        </svg>
                    </div>
                    <div class="rating-bg">
                        <div class="rating-5" style="width: <?php echo $width5 ?>%; "></div>
                    </div>
                </div>
                <div class="rating-item d-flex align-items-center ">
                    <div class="flex-shrink-0 me-2 d-flex align-items-center justify-content-between fs-16">4 <svg style="color:#cacaca" height="14" width="14" class="ms-1">
                            <use xlink:href="#icon-star-rating"></use>
                        </svg>
                    </div>
                    <div class="rating-bg">
                        <div class="rating-4" style="width: <?php echo $width4 ?>%; "></div>
                    </div>
                </div>
                <div class="rating-item d-flex align-items-center ">
                    <div class="flex-shrink-0 me-2 d-flex align-items-center justify-content-between fs-16">3 <svg style="color:#cacaca" height="14" width="14" class="ms-1">
                            <use xlink:href="#icon-star-rating"></use>
                        </svg>
                    </div>
                    <div class="rating-bg">
                        <div class="rating-3" style="width: <?php echo $width3 ?>%; "></div>
                    </div>
                </div>
                <div class="rating-item d-flex align-items-center ">
                    <div class="flex-shrink-0 me-2 d-flex align-items-center justify-content-between fs-16">2 <svg style="color:#cacaca" height="14" width="14" class="ms-1">
                            <use xlink:href="#icon-star-rating"></use>
                        </svg>
                    </div>
                    <div class="rating-bg">
                        <div class="rating-2" style="width: <?php echo $width2 ?>%; "></div>
                    </div>
                </div>
                <div class="rating-item d-flex align-items-center ">
                    <div class="flex-shrink-0 me-2 d-flex align-items-center justify-content-between fs-16">1 <svg style="color:#cacaca" height="14" width="14" class="ms-1">
                            <use xlink:href="#icon-star-rating"></use>
                        </svg>
                    </div>
                    <div class="rating-bg">
                        <div class="rating-1" style="width: <?php echo $width1 ?>%; "></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class=" mb-4 w-100 py-3">
        <form id="filter-review-options">
            <div class="comment-action d-flex align-items-center flex-wrap justify-content-end">
                <div class="sort-comment me-4 ">
                    <select class="text-primary" id="sort-review-1">
                        <option value="newest">
                            Newest </option>
                        <option value="rating">
                            Rating </option>
                    </select>
                </div>
                <div class="sort-comment ">
                    <select class="text-primary sort-comment-list" id="sort-review-2">
                        <option value="0">
                            &nbsp;All Star </option>
                        <option value="1">
                            ★ 1-star </option>
                        <option value="2">
                            ★ 2-star </option>
                        <option value="3">
                            ★ 3-star </option>
                        <option value="4">
                            ★ 4-star </option>
                        <option value="5">
                            ★ 5-star </option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <?php if (count($result) == 0) { ?>
        <div id="review-container">
            <div class="comment">
                <div class="search-empty d-flex align-items-center justify-content-center flex-column">
                    <img class="lazyloaded" width="180" height="100%" src="https://apkmoday.com/assets/img/page/not-found.png" data-src="https://apkmoday.com/assets/img/page/not-found.png" alt="" referrerpolicy="no-referrer">
                    <div class="fs-20 fw-300">Sorry, no results found.</div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="d-flex" style="flex-direction: row;" id="review-user-area">
            <?php foreach ($result as $user_review) { ?>
                <div class="lasso-container flex-3">
                    <div class="lasso-display">
                        <div class="lasso-box-1">
                            <div class="updated-on">
                                <span class="title-detail"><?php echo $user_review->user_name ?></span>
                            </div>

                            <div class="lasso-stars" style="--rating: <?php echo $user_review->score ?>">
                            </div>

                            <div class="clear"></div>
                            <div class="lasso-description">
                                <?php echo $user_review->user_comment ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<?php get_template_part('templates/modal-rating', 'page'); ?>

<script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

    jQuery(document).ready(function() {
        jQuery('#sort-review-2').on('change', function() {
            filterReviews();
        });

        jQuery('#sort-review-1').on('change', function() {
            filterReviews();
        });
    });

    function filterReviews() {
        let option_newsest = jQuery('#sort-review-1').val();
        let option_star = jQuery('#sort-review-2').val();
        let post_id = jQuery('#post-id-for-review').val();

        jQuery.ajax({
                method: 'POST',
                dataType: 'json',
                url: ajaxurl,
                data: {
                    option_newsest: option_newsest,
                    option_start: option_start,
                    post_id: post_id,
                    action: "filter_review_handler"
                }
            })
            .done(
                function(data) {
                    jQuery('#review-user-area').empty();
                    jQuery.each(data.result, function(key, value) {
                        jQuery('#review-user-area').append(`
                        <div class="lasso-container flex-3">
                            <div class="lasso-display">
                                <div class="lasso-box-1">
                                    <div class="updated-on">
                                        <span class="title-detail">` + value.user_name + `</span>
                                    </div>

                                <div class="lasso-stars" style="--rating: ` + value.score + `">
                                 </div>

                            <div class="clear"></div>
                            <div class="lasso-description">
                            ` + value.user_comment + `
                            </div>
                        </div>
                        </div>
                        </div>
                        `);
                    });
                }
            );
    }

    jQuery('#submit-review').on('click', function(event) {
        event.preventDefault();
        var score = jQuery('#input-hidden-review-score').val();
        var user_name = jQuery('#user_name').val();
        var user_comment = jQuery('#user_comment').val();
        var post_id = jQuery('#post-id-for-review').val();

        if (score != 0 && jQuery.trim(user_name) != '' && jQuery.trim(user_comment)) {
            jQuery('#fancybox-container-1').hide();
            jQuery.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: {
                        score: score,
                        user_name: user_name,
                        user_comment: user_comment,
                        post_id: post_id,
                        action: "submit_review_handler"
                    }
                })
                .done(
                    function(data) {
                        if (data.result == 0) {
                            var x = document.getElementById("snackbar");
                            x.className = "show";
                            x.innerHTML = "You have already review.";
                            setTimeout(function() {
                                x.className = x.className.replace("show", "");
                            }, 3000);
                        } else if (data.result == 1) {
                            var x = document.getElementById("snackbar");
                            x.className = "show-green";
                            x.innerHTML = "Your review has store.";
                            setTimeout(function() {
                                x.className = x.className.replace("show-green", "");
                            }, 3000);
                        } else if (data.result == 4) {
                            var x = document.getElementById("snackbar");
                            x.className = "show";
                            x.innerHTML = "This review has been duplicated.";
                            setTimeout(function() {
                                x.className = x.className.replace("show", "");
                            }, 3000);
                        } else {
                            var x = document.getElementById("snackbar");
                            x.className = "show";
                            x.innerHTML = "Your comment was not accepted with forbidden words ( " + data.character + " ).";
                            setTimeout(function() {
                                x.className = x.className.replace("show", "");
                            }, 3000);
                        }

                    }
                );
        } else {
            if (score == 0) {
                var x = document.getElementById("snackbar");
                x.className = "show";
                x.innerHTML = "Empty Score";
                setTimeout(function() {
                    x.className = x.className.replace("show", "");
                }, 3000);

                return;
            }

            if (user_name == 0) {
                var x = document.getElementById("snackbar");
                x.className = "show";
                x.innerHTML = "Empty name";
                setTimeout(function() {
                    x.className = x.className.replace("show", "");
                }, 3000);

                return;
            }

            if (user_comment == 0) {
                var x = document.getElementById("snackbar");
                x.className = "show";
                x.innerHTML = "Empty comment";
                setTimeout(function() {
                    x.className = x.className.replace("show", "");
                }, 3000);
            }
        }
    });
</script>