(function($) {
    "use strict";

    $(document).ready(function() {
        let bdTocContentListLink = $(".bd_toc_content_list_item a"),
            bdTocContainerWithFixed = $(".scroll-to-fixed-fixed"),
            bdTocContainer = $(".bd_toc_container"),
            bdTocHeader = $(".bd_toc_header"),
            bd_toc_wrapper = $(".bd_toc_wrapper"),
            bd_toc_content = $(".bd_toc_content"),
            bd_toc_wrapper_height = bd_toc_wrapper.height(),
            bd_toc_content_height = $(".bd_toc_content").height(),
            bdTocContainerDataVal = bdTocContainer.data("fixedwidth");

        bdTocHeader.click(function() {
            bd_toc_content.slideToggle();

            $('.fit_content').css({
                width: "auto"
            });

            if ($(this).hasClass("active")) {
                $(this).removeClass("active");
                bdTocContainer.addClass("slide_left");
                headerTitleWidthHeight();
            } else {
                $(this).addClass("active");
                bdTocContainer.removeClass("slide_left");
                headerTitleRemoveWidthHeight();
            }
        });


        //add width to header title
        function headerTitleWidthHeight() {
            let headerTitleWidth = $(".bd_toc_header_title").width();
            let bdTocWrapperPadding = bd_toc_wrapper.attr("data-wrapperPadding");
            let bdTocHeaderPadding = bdTocHeader.attr("data-headerPadding");
            let bdTocWrapperPaddingValue = parseInt(bdTocWrapperPadding);
            let bdTocHeaderPaddingValue = parseInt(bdTocHeaderPadding);
            let totleHeaderTitleWidth = parseInt(headerTitleWidth + bdTocWrapperPaddingValue + bdTocHeaderPaddingValue + 3);

            if (bdTocContainer.hasClass("scroll-to-fixed-fixed")) {
                bdTocContainerWithFixed.css({
                    position: "fixed",
                    top: "0px",
                });
            } else {
                bdTocContainer.css(
                    "cssText",
                    "width: " + totleHeaderTitleWidth + "px !important;"
                );
            }
        }

        // remove width and height from header title
        function headerTitleRemoveWidthHeight() {
            bdTocContainer.css("width", 0 + "px");
            // bd_toc_content.css("cssText", "height: " + bd_toc_content_height + "px");
        }

        //collapse button off
        if (handle.initial_view == "0") {
            bdTocHeader.click(function() {
                if (bdTocContainer.hasClass("scroll-to-fixed-fixed")) {
                    bdTocContainerWithFixed.css({
                        position: "fixed",
                        top: "0px",
                    });
                } else {
                    bdTocContainer.css(
                        "cssText",
                        "width: " + 100 + "% !important;",
                        "transition: all 0.5s ease-in-out;"
                    );
                }
                bd_toc_content.css("cssText", "display: block;");

                if (bdTocContainer.hasClass("slide_left")) {
                    headerTitleWidthHeight();
                } else {
                    $(".bd_toc_content_list").css(
                        "cssText",
                        // "height: " + $(".bd_toc_content").height() + "px !important;"
                    );
                }
            });
            if (bdTocHeader.hasClass("active")) {
                bdTocHeader.removeClass("active");
            }
        }

        //collapse button on
        if (handle.initial_view == "1") {
            headerTitleRemoveWidthHeight();
        }

        function slidingTocContainer(width) {
            $(".bd_toc_container.scroll-to-fixed-fixed").css(
                "cssText",
                `z-index: 1000;
                position: fixed;
                transition: .1s;
                top: 0px;
                margin-left: 0px;
                ${sticky_mode_position.sticky_mode_position}: calc( 0% + ${width} ) !important`
            );
        }

        $(document).on(
            "click",
            ".bd_toc_content_list_item ul li .collaps-button",
            function() {
                $(this).parent("li").toggleClass("collapsed");
                $(this).parent("li").find("li").addClass("collapsed");
                bd_toc_content_height = $(".bd_toc_content ul").height();

                toggleContentWrapperHeight(bd_toc_content_height);
            }
        );

        bdTocContentListLink.on("click", function() {
            let bdTocContentListLinkHref = $(this).attr("href");
            location.replace(bdTocContentListLinkHref);
        });

        bdTocContentListLink.on("click", function() {
            if (screen.width < "1024") {
                if (bdTocContainer.hasClass("active")) {
                    slidingTocContainer("-" + bdTocContainerDataVal + "px");
                    bdTocContainer.removeClass("active");
                }
            }
        });

        function toggleContentWrapperHeight(height) {
            $(".bd_toc_container .bd_toc_content_list_item").css("height", height);
        }

        $(".layout_toggle_button").on("click", function() {
            if (sticky_mode_position.sticky_sidebar_collapse_on_off == "1") {
                $('.bd_toc_container.scroll-to-fixed-fixed .bd_toc_wrapper').css('opacity', '1');
                if (bdTocContainer.hasClass("active")) {
                    bdTocContainer.removeClass("active");
                    slidingTocContainer(`0px`);
                } else {
                    bdTocContainer.addClass("active");
                    slidingTocContainer("-" + bdTocContainerDataVal + "px");
                }
            }else {
                if (bdTocContainer.hasClass("active")) {
                    bdTocContainer.removeClass("active");
                    slidingTocContainer("-" + bdTocContainerDataVal + "px");
                } else {
                    bdTocContainer.addClass("active");
                    slidingTocContainer(`0px`);
                }
            }
        });

        //scroll class add form sticky sidebar when content height is greater than windows height 
        if(bd_toc_content.height() > $(window).height()){
            bd_toc_content.addClass("scroll");
        }else{
            bd_toc_content.removeClass("scroll");
        }

        //progress bar
        if(typeof progress_bar_switcher !== 'undefined' && progress_bar_switcher.progress_bar_switcher == 1){
            $(".bd_toc_progress_bar").addClass("progress_bar_open");
            $(document).on("scroll", function(){
                var pixels = $(document).scrollTop();
                var pageHeight = $(document).height() - $(window).height();
                var progress = 100 * pixels / pageHeight;
                
                $(".bd_toc_widget_progress_bar").css("width", progress + "%");
            })
        }
    });

    //floating content show hide option when scrolling
    function widgetFLoatingOpen(){
        $(window).scroll(function() {
            let windowScrollLength = $(window).scrollTop();
            if (windowScrollLength > $(".bd_toc_container").height() + $('.bd_toc_container').offset().top) {
                $(".bd_toc_widget_floating").addClass("widget_floating_open");
            } else {
                $(".bd_toc_widget_floating").removeClass("widget_floating_open");
            }
        });
    }
    
    //default nav scrolling and floating value update
    $(".bd_toc_content_list_item").onePageNav({
        currentClass: "current",
        scrollChange: function($currentListItem) {
            $($currentListItem).parents("li").addClass("active");
            $($currentListItem[0]).addClass("active");

            //floating title text insert when click single content element
            // let current_list_item = $currentListItem.text();
            let current_list_item = $(".bd_toc_content_list_item ul li.current").find(">:first-child").text();
            $(".bd_toc_widget_floating_current_heading .current_list_item").text(current_list_item);

            if(handle.isProActivated && widget_floating_option.widget_floating_option == "1"){
                //floating content show when default content scroll
                widgetFLoatingOpen();
            }
        },
    });

    //Floating Title show and value update when scrolling
    if(handle.isProActivated && widget_floating_option.widget_floating_option == "1"){
        $(".bd_toc_content_floating_list_item").onePageNav({
            currentClass: "current",
            scrollChange: function($currentListItem) {
                // let current_list_item = $currentListItem.text();
                let current_list_item = $(".bd_toc_content_floating_list_item ul li.current").find(">:first-child").text();
                $(".bd_toc_widget_floating_current_heading .current_list_item").text(current_list_item);
                
                //floating content show when scroll floating content scroll 
                widgetFLoatingOpen();
            },
        });
    };

    //Floating option
    if(handle.isProActivated && widget_floating_option.widget_floating_option == 1){
        $(".bd_toc_widget_item").prepend(`
        <div class="current_list_item"></div>
    `)
    }

    //Floating navigation insert
    if(handle.isProActivated && widget_floating_nav.widget_floating_nav == 1){
        $(".bd_toc_widget_item").prepend(`
            <div class="bd_toc_widget_nav_prev">
                <a href="#" class="bd_toc_widget_left_arrow"></a>
            </div>
            <div class="bd_toc_widget_nav_next">
                <a href="#" class="bd_toc_widget_right_arrow"></a>
            </div>
        `)
    }

    //floating content
    if(handle.isProActivated && widget_floating_content.widget_floating_content == 1){
        $(".current_list_item").hover(function(){
            $(this).parent().parent().parent().find(".bd_toc_floating_content").addClass("widget_floating_content_open");
            $(".bd_toc_widget_floating").css({
                "border-radius": "15px",
            });
        });

        //floating content hide when click floating single content
        $(".bd_toc_floating_content ul li a ").on("click", function(){
            $('.bd_toc_floating_content').addClass("floating_content_hide");
        })

        let title_border_radius_top = widget_floating_content.title_border_radius_top + "px";
        let title_border_radius_right = widget_floating_content.title_border_radius_right + "px";
        let title_border_radius_bottom = widget_floating_content.title_border_radius_bottom + "px";
        let title_border_radius_left = widget_floating_content.title_border_radius_left + "px";
    
        $(document).on("mouseleave", ".bd_toc_floating_content", function() {

            if ($(window).width() >= 768) {
                $(this).removeClass("floating_content_hide");
                $(this).removeClass("widget_floating_content_open");
                $(".bd_toc_widget_floating").css(
                    "cssText",
                    `
                    border-top-left-radius: ${title_border_radius_top},
                    border-top-right-radius: ${title_border_radius_right},
                    border-bottom-left-radius: ${title_border_radius_bottom},
                    border-bottom-right-radius: ${title_border_radius_left},
                    `
                );
            }
        });
        // for mobile devices
        if ($(window).width() <= 767) {
            $(".floating_toc_bg_overlay").on("click", function(){
                $(".bd_toc_floating_content").removeClass("widget_floating_content_open");
                $('.bd_toc_widget_floating').removeClass("overlay" );
                $('.bd_toc_widget_floating_current_heading').css({
                    "display": "block"
                })
            })
    
            $(".bd_toc_widget_nav_overlay").on("click", function(){
                $(this).parent().parent().parent().find(".bd_toc_floating_content").addClass("widget_floating_content_open");
                $(".bd_toc_widget_floating").css({
                    "border-radius": "15px"
                });
                $('.bd_toc_widget_floating').addClass("overlay" );
                $('.bd_toc_widget_floating_current_heading').css({
                    "display": "none"
                })
            });
        }
    }

    //floating content hide floating position is bottom 
    if(handle.isProActivated && widget_floating_content.widget_floating_position == "bottom"){
        $(".current_list_item").hover(function(){
            $(this).parent().parent().css("display", "none");
            $(this).parent().parent().parent().find(".bd_toc_floating_content").css("display", "block");
        });

        $(document).on("mouseleave", ".bd_toc_floating_content", function() {
            if ($(window).width() >= 768) {
                $(".bd_toc_widget_floating_current_heading").css("display", "block");
                $(this).css("display", "none");
            }
        });

        if(handle.isProActivated && widget_floating_content.widget_floating_content == 0){
            $(".current_list_item").hover(function(){
                $(this).parent().parent().css("display", "block");
                $(this).parent().parent().parent().find(".bd_toc_floating_content").css("display", "none");
            });
        }
    }

    //floating text update when click single content element
    $(".bd_toc_content_list_item ul li a ").on("click", function(){
        if(handle.isProActivated && widget_floating_option.widget_floating_option == "1"){
            $(".bd_toc_content_floating_list_item").onePageNav({
                currentClass: "current",
                scrollChange: function($currentListItem) {
                    let current_list_item = $(".bd_toc_content_list_item ul li.current").find(">:first-child").text();
                    $(".bd_toc_widget_floating_current_heading .current_list_item").text(current_list_item);
                    
                    //floating content show when scroll floating content scroll 
                    widgetFLoatingOpen();
                },
            });
        }
    });

    if($('.bd_toc_content_floating_list_item').length > 0 ) {
        $('.bd_toc_content_floating_list_item ul:first > li').addClass('root_parent');
    }

    //floating text update when click floating single content element
    $(".bd_toc_content_floating_list_item ul li a ").on("click", function(){
        let current_text = $(".bd_toc_content_floating_list_item ul li.current").find(">:first-child").text();
        $('.bd_toc_widget_floating_current_heading .current_list_item').text(current_text);
    });

    //floating navigation when click arrow left button
    $(".bd_toc_widget_nav_prev").on("click", function(){
        let pre_id = "";
        let current_dom = $(".bd_toc_floating_content .bd_toc_content_floating_list_item ul").find("li.current");
        if(current_dom.prev().children("ul").length > 0){
            pre_id = current_dom.prev().children("ul").find("li").last().find("a").attr("href");

        }else {
            if(current_dom.parent().children("li").hasClass("first last")){
                pre_id = current_dom.parent().parent().find("a").attr("href");
            }else{
                pre_id = current_dom.prev().find("a").attr("href");
            }
            if(pre_id === undefined){
                pre_id = current_dom.parent().parent().find("a").attr("href");
            }
        }
        
        $(".bd_toc_widget_nav_prev a").attr("href", pre_id);
        $('html').css("scroll-behavior", "smooth");
    });

   //floating navigation when click arrow right button
   $(".bd_toc_widget_nav_next").on("click", function(){
        // e.preventDefault();
        let next_id;
        let current_dom = $(".bd_toc_floating_content .bd_toc_content_floating_list_item ul").find("li.current");

        if(current_dom.children("ul").children("li").hasClass("first last")) {
            next_id = current_dom.children("ul").children("li").find("a").attr("href");
        } else {
            let next_element = current_dom.next("li");
            next_id = next_element.find("a").attr("href");
            
            if(current_dom.children("ul").length > 0) {
                next_id = current_dom.children("ul").children("li").find("a").attr("href");
            } else {
                if(next_element.length === 0) {
                    let parent_li = getFirstParentLi(current_dom[0]);
                    next_id = $(parent_li).next().children("a").attr("href");
                } else {
                    next_id = current_dom.next("li").find("a").attr("href");
                }
            }
        }
        
        $(".bd_toc_widget_nav_next a").attr("href", next_id);
        $('html').css("scroll-behavior", "smooth");
    });


    function getFirstParentLi(element) {
        if (!element.parentNode || element.parentNode.className === 'root_parent' || element.parentNode.className === 'first root_parent') {
            return element.parentNode;
        }
        // Recursive case: call the function again with the parent element
        return getFirstParentLi(element.parentNode);
    }

    let count = $(".bd_toc_container").length;

    for (let i = 0; i < count; i++) {
        if (i !== 0) {
            $(".bd_toc_container").eq(1).remove();
        }
    }

})(jQuery);