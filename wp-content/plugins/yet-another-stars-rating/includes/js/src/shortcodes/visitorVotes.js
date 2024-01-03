const yasrRaterInDom = document.getElementsByClassName('yasr-rater-stars-vv');

yasrSearchVVInDom(yasrRaterInDom);

export function yasrSearchVVInDom(yasrRaterInDom) {
    if (yasrRaterInDom.length > 0) {
        yasrVisitorVotesFront(yasrRaterInDom);
        if (yasrWindowVar.visitorStatsEnabled === 'yes') {
            let yasrStatsInDom = document.getElementsByClassName('yasr-dashicons-visitor-stats');
            if (yasrStatsInDom) {
                yasrVvStats (yasrStatsInDom);
            }
        }
    }
}

/**
 *
 * @param yasrRaterVVInDom
 */
function yasrVisitorVotesFront (yasrRaterVVInDom) {
    //Check in the object
    for (let i = 0; i < yasrRaterVVInDom.length; i++) {
        (function(i) {
            //yasr-star-rating is the class set by rater.js : so, if already exists,
            //means that rater already run for the element
            if(yasrRaterVVInDom.item(i).classList.contains('yasr-star-rating') !== false) {
                return;
            }

            const elem                       = yasrRaterVVInDom.item(i);
            const postId                     = elem.getAttribute('data-rater-postid');
            const htmlId                     = elem.id;
            const uniqueId                   = htmlId.replace('yasr-visitor-votes-rater-', '');
            const mainContianer              = document.getElementById('yasr_visitor_votes_' + uniqueId);
            const starSize                   = parseInt(elem.getAttribute('data-rater-starsize'));
            const nonce                      = elem.getAttribute('data-rater-nonce');
            const isSingular                 = elem.getAttribute('data-issingular');
            const containerVotesNumberName   = 'yasr-vv-votes-number-container-' + uniqueId;
            const containerAverageNumberName = 'yasr-vv-average-container-' + uniqueId;
            const bottomContainerName        = 'yasr-vv-bottom-container-' + uniqueId;
            const loaderContainerName        = 'yasr-vv-loader-' + uniqueId;
            const containerVotesNumber       = document.getElementById(containerVotesNumberName);
            const containerAverageNumber     = document.getElementById(containerAverageNumberName);
            const bottomContainer            = document.getElementById(bottomContainerName);
            const loaderContainer            = document.getElementById(loaderContainerName);
            let rating                       = elem.getAttribute('data-rating');
            let readonlyShortcode            = elem.getAttribute('data-readonly-attribute');
            let readonly                     = elem.getAttribute('data-rater-readonly');

            if (readonlyShortcode === null) {
                readonlyShortcode = false;
            }

            readonlyShortcode = yasrTrueFalseStringConvertion(readonlyShortcode);
            readonly          = yasrTrueFalseStringConvertion(readonly);

            //if comes from shortcode attribute, and is true, readonly is always true
            if (readonlyShortcode === true) {
                readonly = true;
            }

            if(yasrWindowVar.ajaxEnabled === 'yes') {
                yasrLoader(loaderContainer);

                let data = {
                    action: 'yasr_load_vv',
                    post_id: postId,
                };

                jQuery.get(yasrWindowVar.ajaxurl, data).done(
                    function (response) {
                        let data = yasrValidJson(response);

                        if(data === false) {
                            let jsonError = 'Not a valid Json Element';
                            yasrLoader(loaderContainer, false)
                            yasrInnerHtml(mainContianer, jsonError);
                            return;
                        }

                        let readonly;
                        //if has readonly attribute, it is always true
                        if(readonlyShortcode === true) {
                            readonly = true;
                        } else {
                            readonly = data.yasr_visitor_votes.stars_attributes.read_only;
                        }

                        if (data.yasr_visitor_votes.number_of_votes > 0) {
                            rating = data.yasr_visitor_votes.sum_votes / data.yasr_visitor_votes.number_of_votes;
                        } else {
                            rating = 0;
                        }
                        rating   = rating.toFixed(1);
                        rating   = parseFloat(rating);

                        yasrSetVisitorVotesRater(starSize, rating, postId, readonly, htmlId, uniqueId, nonce, isSingular,
                            containerVotesNumber, containerAverageNumber,  loaderContainer, bottomContainer);

                        //do this only if yasr_visitor_votes has not the readonly attribute
                        if(readonlyShortcode !== true) {
                            yasrInnerHtml(containerVotesNumber, data.yasr_visitor_votes.number_of_votes);
                            yasrInnerHtml(containerAverageNumber, rating);

                            //insert span with text after the average
                            if(data.yasr_visitor_votes.stars_attributes.span_bottom !== false) {
                                let text = data.yasr_visitor_votes.stars_attributes.span_bottom;
                                yasrInnerHtml(bottomContainer, text);
                            }
                        }

                    }).fail(
                    function(e, x, settings, exception) {
                        console.info('YASR ajax call failed. Showing ratings from html');
                        yasrSetVisitorVotesRater(starSize, rating, postId, readonly, htmlId, uniqueId, nonce, isSingular,
                            containerVotesNumber, containerAverageNumber,  loaderContainer, bottomContainer);

                        //Unhide the div below the stars
                        if(readonlyShortcode !== true) {
                            bottomContainer.style.display = '';
                        }
                    });
            }

            //When ajax is not enabled, just set the star rating
            else {
                yasrSetVisitorVotesRater(starSize, rating, postId, readonly, htmlId, uniqueId, nonce, isSingular,
                    containerVotesNumber, containerAverageNumber,  loaderContainer, bottomContainer);
            }

        })(i);
    }//End for

}


/**
 *
 * @param starSize
 * @param rating
 * @param postId
 * @param readonly
 * @param htmlId
 * @param uniqueId
 * @param nonce
 * @param isSingular
 * @param containerVotesNumber
 * @param containerAverageNumber
 * @param loaderContainer
 * @param bottomContainer
 */
function yasrSetVisitorVotesRater (starSize, rating, postId, readonly, htmlId, uniqueId, nonce, isSingular,
                                   containerVotesNumber, containerAverageNumber, loaderContainer, bottomContainer) {

    //Be sure is a number and not a string
    rating = parseFloat(rating);

    //raterjs accepts only boolean for readOnly element
    readonly               = yasrTrueFalseStringConvertion(readonly);

    const elem             = document.getElementById(htmlId);
    const isUserLoggedIn   = JSON.parse(yasrWindowVar.isUserLoggedIn);

    yasrLoader(loaderContainer, false);

    let rateCallback = function (rating, done) {
        //show the loader
        yasrLoader(loaderContainer, true);

        //Creating an object with data to send
        let data = {
            action: 'yasr_send_visitor_rating',
            rating: rating,
            post_id: postId,
            is_singular: isSingular
        };

        if(isUserLoggedIn === true) {
            Object.assign(data, {nonce_visitor: nonce});
        }

        this.setRating(rating);
        this.disable();

        //Send value to the Server
        jQuery.post(yasrWindowVar.ajaxurl, data).done(
            function (response) {
                response = yasrValidJson(response);

                if(response === false) {
                    yasrLoader(loaderContainer, false)
                    yasrInnerHtml(bottomContainer, "<span>Not a valid Json Element, rating can't be saved.</span>");
                    return;
                }

                let text;
                let responseSpanId = `yasr-vote-${response.status}`;

                if (response.status === 'success') {
                    //Update the ratings only if response is success
                    yasrInnerHtml(containerVotesNumber,   response.number_of_votes)
                    yasrInnerHtml(containerAverageNumber, response.average_rating)
                }

                text = `<span class="yasr-small-block-bold" id="${responseSpanId}"> ${response.text} </span>`;

                yasrInnerHtml(bottomContainer, text);

                yasrLoader(loaderContainer, false);

                done();

            }).fail(
            function(e, x, settings, exception) {
                console.error('YASR ajax call failed. Can\'t save data');
                console.log(e);
            });
    }

    yasrSetRaterValue (starSize, htmlId, elem, 1, readonly, rating, rateCallback);

}

function yasrInnerHtml(htmlContainer, htmlChild) {
    if (htmlContainer !== null) {
        htmlContainer.innerHTML = htmlChild;
        //Be sure the bottom container is showed
        htmlContainer.style.display = '';
    }
}

/**
 * Show or hide the laoder
 *
 * since 3.0.4
 * @param loaderContainer
 * @param show
 */
function yasrLoader(loaderContainer, show=true) {
    let loader = '';

    if(show === true) {
       loader = yasrWindowVar.loaderHtml;
    }

    yasrInnerHtml(loaderContainer, loader);
}

/****** Tooltip functions ******/

//used in shortcode page and ajax page
function yasrVvStats (yasrStatsInDom) {
    //htmlcheckid declared false
    let htmlIdChecked = false;

    let txtContainer;  //the container of the text [TOTAL...];  needed to get the text color
    let computedcolor;

    for (let i = 0; i < yasrStatsInDom.length; i++) {
        (function (i) {

            let htmlId = '#'+yasrStatsInDom.item(i).id;
            let postId = yasrStatsInDom.item(i).getAttribute('data-postid');

            //get the font color from yasr-vv-text-container only the first time
            if(i===0) {
                //main container
                txtContainer     = document.getElementsByClassName('yasr-vv-text-container');
                if(txtContainer !== null) {
                    computedcolor = window.getComputedStyle(txtContainer[0], null).getPropertyValue("color");
                }
            }

            //if computed color exists, change the color to the svg
            if(computedcolor) {
                //get the svg element
                let svg = document.getElementById(yasrStatsInDom.item(i).id);
                //fill with the same color of the text
                svg.style.fill=computedcolor;
            }

            let data = {
                action: 'yasr_stats_visitors_votes',
                post_id: postId
            };

            let initialContent = '<span style="color: #0a0a0a">Loading...</span>';

            if (typeof tippy === "function") {
                tippy(htmlId, {
                    allowHTML: true,
                    content: initialContent,
                    theme: 'yasr',
                    arrow: true,
                    arrowType: 'round',

                    //When support for IE will be dropped out, this will become onShow(tip)
                    onShow: function onShow(tip) {
                        if (htmlId !== htmlIdChecked) {
                            //must be post or won't work
                            jQuery.post(yasrWindowVar.ajaxurl, data, function (response) {
                                response = yasrValidJson(response);

                                if(response === false) {
                                    tip.setContent('Error!');
                                    return;
                                }

                                if(response.status === 'error') {
                                    console.error(response.text);
                                    tip.setContent(response.text);
                                    return;
                                }

                                tip.setContent(yasrReturnToolbarStats(response));
                            }).fail(
                                function(e, x, settings, exception) {
                                    let errorText = 'YASR ajax call failed.'
                                    console.log(e);
                                    tip.setContent(errorText);
                            });
                        }
                    },
                    onHidden: function onHidden() {
                        htmlIdChecked = htmlId;
                    }

                });
            }

        })(i);
    }

}

/**
 * Return the HTML with toolbars
 *
 * @param ratings MUST be an object
 * with 6 params: 1 int and 5 suboject
 * int:       medium_rating
 * subobject: each one must have:
 *     - progressbar
 *     - n_of_votes
 *     - votes (optional)
 *     e.g.
 {
          "1": {
            "progressbar": "17.14%",
            "n_of_votes": 6,
            "vote": "1"
          },
          "2": {
            "progressbar": "25.71%",
            "n_of_votes": 9,
            "vote": "2"
          },
          "3": {
            "progressbar": "8.57%",
            "n_of_votes": 3,
            "vote": "3"
          },
          "4": {
            "progressbar": "0%",
            "n_of_votes": 0,
            "vote": 4
          },
          "5": {
            "progressbar": "48.57%",
            "n_of_votes": 17,
            "vote": "5"
          }
        }
 *
 */
function yasrReturnToolbarStats (ratings) {
    //Get the medium rating
    const mediumRating        = ratings.medium_rating;

    //remove medium rating from the object, so I can loop only the ratings later
    delete ratings['medium_rating'];

    let highestNumberOfVotes = 0;
    //loop a first time the array to get the highest number of votes
    for (let i = 1;  i <= 5; i++) {
        if(i === 1) {
            highestNumberOfVotes = ratings[i].n_of_votes;
        } else {
            if (ratings[i].n_of_votes > highestNumberOfVotes) {
                highestNumberOfVotes = ratings[i].n_of_votes;
            }
        }
    }
    //Later, I need to get the number of digits of the highest number
    let lengthHighestNumberOfVotes = Math.log(highestNumberOfVotes) * Math.LOG10E + 1 | 0;

    //Later, I've to calculate the flexbasis based on the length of the number
    //default flexbasis is 5%
    let flexbasis = '5%'

    //if the length of the number is less or equal of 3 digits (999)
    //flexbasis is 5%
    if(lengthHighestNumberOfVotes <= 3) {
        flexbasis = '5%';
    }
    //if the length of the number is major of 3 digits and less or equal of 5 digits (99999)
    //flexbasis is 10%
    if(lengthHighestNumberOfVotes > 3 && lengthHighestNumberOfVotes <= 5) {
        flexbasis = '10%';
    }
    //if highest of 5 digits, flexbasis is 15 % (note that this will break into a newline when 8 digits are
    //reached, but I think that a number of 9,999,999 is enough
    if(lengthHighestNumberOfVotes > 5) {
        flexbasis = '15%';
    }

    //prepare the html to return
    let html_to_return  = '<div class="yasr-visitors-stats-tooltip">';
    html_to_return      += '<span id="yasr-medium-rating-tooltip">'
        + mediumRating
        + ' '
        + JSON.parse(yasrWindowVar.textVvStats)
        + '</span>';
    html_to_return      += '<div class="yasr-progress-bars-container">';

    let stars_text = JSON.parse(yasrWindowVar.starsPluralForm)     //default is plural
    let progressbar = 0; //default value for progressbar
    let n_votes     = 0; //default n_votes

    //Do a for with 5 rows
    for (let i = 5;  i > 0; i--) {
        if (i === 1) {
            stars_text = JSON.parse(yasrWindowVar.starSingleForm) //single form
        }

        //should never happen, just to be sage
        if(typeof ratings[i] !== 'undefined') {
            progressbar = ratings[i].progressbar;
            n_votes     = ratings[i].n_of_votes;
        }

        html_to_return += `<div class='yasr-progress-bar-row-container yasr-w3-container'>
                               <div class='yasr-progress-bar-name'>${i} ${stars_text}</div> 
                               <div class='yasr-single-progress-bar-container'> 
                                   <div class='yasr-w3-border'> 
                                       <div class='yasr-w3-amber' style='height:17px;width:${progressbar}'></div> 
                                   </div>
                               </div> 
                               <div class='yasr-progress-bar-votes-count' style="flex-basis:${flexbasis} ">${n_votes}</div>
                           </div>`;

    } //End foreach

    html_to_return += '</div></div>';

    return html_to_return;
}

/****** End tooltipfunction ******/