<div class="fancybox-container fancybox-is-modal fancybox-is-open" role="dialog" tabindex="-1" id="fancybox-container-1" style="transition-duration: 366ms;">
    <div class="fancybox-bg"></div>
    <div class="fancybox-inner">
        <div class="fancybox-infobar"><span data-fancybox-index="">1</span>&nbsp;/&nbsp;<span data-fancybox-count="">1</span></div>
        <div class="fancybox-toolbar"><button data-fancybox-zoom="" class="fancybox-button fancybox-button--zoom" title="Zoom" disabled=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18.7 17.3l-3-3a5.9 5.9 0 0 0-.6-7.6 5.9 5.9 0 0 0-8.4 0 5.9 5.9 0 0 0 0 8.4 5.9 5.9 0 0 0 7.7.7l3 3a1 1 0 0 0 1.3 0c.4-.5.4-1 0-1.5zM8.1 13.8a4 4 0 0 1 0-5.7 4 4 0 0 1 5.7 0 4 4 0 0 1 0 5.7 4 4 0 0 1-5.7 0z"></path>
                </svg></button><button data-fancybox-play="" class="fancybox-button fancybox-button--play" title="Start slideshow" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M6.5 5.4v13.2l11-6.6z"></path>
                </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M8.33 5.75h2.2v12.5h-2.2V5.75zm5.15 0h2.2v12.5h-2.2V5.75z"></path>
                </svg></button><button data-fancybox-thumbs="" class="fancybox-button fancybox-button--thumbs" title="Thumbnails" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M14.59 14.59h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76H5.65v-3.76zm8.94-4.47h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76H5.65v-3.76zm8.94-4.47h3.76v3.76h-3.76V5.65zm-4.47 0h3.76v3.76h-3.76V5.65zm-4.47 0h3.76v3.76H5.65V5.65z"></path>
                </svg></button><button data-fancybox-close="" class="fancybox-button fancybox-button--close" title="Close"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 10.6L6.6 5.2 5.2 6.6l5.4 5.4-5.4 5.4 1.4 1.4 5.4-5.4 5.4 5.4 1.4-1.4-5.4-5.4 5.4-5.4-1.4-1.4-5.4 5.4z"></path>
                </svg></button></div>
        <div class="fancybox-navigation"><button data-fancybox-prev="" class="fancybox-button fancybox-button--arrow_left" title="Previous" disabled="">
                <div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M11.28 15.7l-1.34 1.37L5 12l4.94-5.07 1.34 1.38-2.68 2.72H19v1.94H8.6z"></path>
                    </svg></div>
            </button><button data-fancybox-next="" class="fancybox-button fancybox-button--arrow_right" title="Next" disabled="">
                <div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M15.4 12.97l-2.68 2.72 1.34 1.38L19 12l-4.94-5.07-1.34 1.38 2.68 2.72H5v1.94z"></path>
                    </svg></div>
            </button></div>
        <div class="fancybox-stage">
            <div class="fancybox-slide fancybox-slide--html fancybox-slide--current fancybox-slide--complete">
                <div class="box-reviews my-4 fancybox-content" style="max-width: 670px;">
                    <div class="review-box-content"> <img class="avatar-review-modal" width="100" height="100" src="<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>" alt="game avatar">
                        <div class="cursor-pointer position-absolute fw-bold fs-20" style="top: 10px; right: 15px" onclick="closeBoxReview()">✕</div>
                        <div class="py-2 no-hover" style="margin-top: 59px;">
                            <div class="app-item-info d-block">
                                <div class="fs-20 fw-500 d-block mb-1 pt-2 text-center"><?php echo get_the_title(get_the_ID()) ?></div>
                                <div class="text-muted text-center"> Rate this app </div>
                            </div>
                        </div>
                        <form id="form-submit-review" method="POST"> <input type="hidden" value="56" name="review[app_id]">
                            <div class="row">
                                <div class="col-12 flex-center py-3 margin-left-15">
                                    <div class="overall-score">
                                        <div class="stars-holder-big stars-holder editable-rating">
                                            <div class="full-stars js-full-stars" style="width: 0%; animation: auto ease 0s 1 normal none running none;"></div>
                                        </div> <input type="hidden" id="input-hidden-review-score" name="review[score]" value="0">
                                        <div class="score-numbers fw-bold fs-18 ms-2 d-none"> 0 </div>
                                    </div>
                                    <div class="animation-full-stars js-animation-full-stars"></div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="form-group">
                                        <label class="fw-500" for="user_name">Your name</label>
                                        <input id="user_name" type="text" name="review[username]" class="form-control fs-14" value="" placeholder="Your name" required>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="form-group">
                                        <label class="fw-500" for="comment_">Comments</label>
                                        <textarea id="user_comment" class="form-control fs-14" name="review[text]" rows="3" placeholder="Questions or Comments" required=""></textarea>
                                    </div>
                                </div>
                                <input type="hidden" id="post-id-for-review" value="<?php echo get_the_ID() ?>">
                                <div class="col-xl-6 col-lg-6 mx-auto d-flex justify-content-center">
                                    <button type="button" class="btn d-block col-12 btn-primary text-uppercase" id="submit-review">
                                        <svg width="24" height="24" fill="#fff" style="position: relative; top:-1px ;">
                                            <use xlink:href="#icon-send-review"></use>
                                        </svg> Send Comment
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class=" mt-3 p-2 alert alert-warning"> Reviews are public and editable. Past edits are visible to the developer and users unless you delete your review altogether. </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fancybox-caption fancybox-caption--separate">
            <div class="fancybox-caption__body"></div>
        </div>
    </div>
</div>