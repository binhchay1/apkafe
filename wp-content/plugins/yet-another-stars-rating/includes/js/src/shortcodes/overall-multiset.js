const arrayClasses = ['yasr-rater-stars', 'yasr-multiset-visitors-rater'];

/*** Constant used by yasr
 yasrWindowVar (ajaxurl,  isrtl)
***/

for (let i=0; i<arrayClasses.length; i++) {
    //Search and set all div with class yasr-multiset-visitors-rater
    yasrSearchStarsDom(arrayClasses[i]);
}

/**
 * Search for divs with defined classname
 */
export function yasrSearchStarsDom (starsClass) {
    //At pageload, check if there is some shortcode with class yasr-rater-stars
    const yasrRaterInDom = document.getElementsByClassName(starsClass);
    //If so, call the function to set the rating
    if (yasrRaterInDom.length > 0) {
        //stars class for most shortcodes
        if(starsClass === 'yasr-rater-stars') {
            yasrSetRatingOverall(yasrRaterInDom);
        }

        if (starsClass === 'yasr-multiset-visitors-rater') {
            yasrSetRatingVisitorMulti(yasrRaterInDom)
        }
    }
}

function yasrSetRatingOverall (yasrRatingsInDom) {
    //Check in the object
    for (let i = 0; i < yasrRatingsInDom.length; i++) {
        //yasr-star-rating is the class set by rater.js : so, if already exists,
        //means that rater already run for the element
        if(yasrRatingsInDom.item(i).classList.contains('yasr-star-rating') === false) {
            const element  = yasrRatingsInDom.item(i);
            const htmlId   = element.id;
            const starSize = element.getAttribute('data-rater-starsize');
            yasrSetRaterValue(starSize, htmlId, element);
        }
    }
}

/**
 * Call rater if multisetVisitor in dom is found
 *
 * @param yasrMultiSetVisitorInDom
 */
function yasrSetRatingVisitorMulti (yasrMultiSetVisitorInDom) {
    //will have field id and vote
    let ratingObject = "";

    //an array with all the ratings objects
    let ratingArray = [];

    const hiddenFieldMultiReview = document.getElementById('yasr-pro-multiset-review-rating');

    //Check in the object
    for (let i = 0; i < yasrMultiSetVisitorInDom.length; i++) {
        (function (i) {
            //yasr-star-rating is the class set by rater.js : so, if already exists,
            //means that rater already run for the element
            if(yasrMultiSetVisitorInDom.item(i).classList.contains('yasr-star-rating') !== false) {
                return;
            }

            let elem       = yasrMultiSetVisitorInDom.item(i);
            let htmlId     = elem.id;
            let readonly   = elem.getAttribute('data-rater-readonly');
            let starSize   = elem.getAttribute('data-rater-starsize');
            if(!starSize) {
                starSize = 16;
            }

            readonly = yasrTrueFalseStringConvertion(readonly);

            const rateCallback = function (rating, done) {
                const postId     = elem.getAttribute('data-rater-postid');
                const setId      = elem.getAttribute('data-rater-setid');
                const setIdField = elem.getAttribute('data-rater-set-field-id');

                //Just leave 1 number after the .
                rating = rating.toFixed(1);
                //Be sure is a number and not a string
                const vote = parseInt(rating);

                this.setRating(vote); //set the new rating

                ratingObject = {
                    postid: postId,
                    setid: setId,
                    field: setIdField,
                    rating: vote
                };

                //creating rating array
                ratingArray.push(ratingObject);

                if(hiddenFieldMultiReview) {
                    hiddenFieldMultiReview.value = JSON.stringify(ratingArray);
                }

                done();
            }
            yasrSetRaterValue (starSize, htmlId, elem, 1, readonly, false, rateCallback);
        })(i);
    }

    //add event listener when submit button is clicked
    yasrVisitorMultiSubmitOnClick(ratingArray)
} //End function

/**
 * Add an event listener for each submit button
 *
 * @param ratingArray
 */
function yasrVisitorMultiSubmitOnClick (ratingArray) {
    const visitorMultiSubmitButtons = document.getElementsByClassName('yasr-send-visitor-multiset');

    //add an event listener for each submit button
    for (let i=0; i< visitorMultiSubmitButtons.length; i++) {
        visitorMultiSubmitButtons[i].addEventListener('click', function () {

            const multiSetPostId = this.getAttribute('data-postid');
            const multiSetId     = this.getAttribute('data-setid');
            const nonce          = this.getAttribute('data-nonce');
            const submitButton   = document.getElementById(`yasr-send-visitor-multiset-${multiSetPostId}-${multiSetId}`);
            const loader         = document.getElementById(`yasr-loader-multiset-visitor-${multiSetPostId}-${multiSetId}`)

            submitButton.style.display = 'none';
            loader.style.display       = 'block';

            const data = {
                action: 'yasr_visitor_multiset_field_vote',
                post_id: multiSetPostId,
                rating: JSON.stringify(ratingArray),
                set_id: multiSetId
            };

            const isUserLoggedIn = JSON.parse(yasrWindowVar.isUserLoggedIn);

            if (isUserLoggedIn === true) {
                Object.assign(data, {
                    nonce: nonce
                });
            }

            const body = new URLSearchParams(data).toString();

            yasrPostVisitorsMultiset (body, loader);
        })
    }
}

/**
 * Do the post and save data
 *
 * @param body
 * @param loader
 */
function yasrPostVisitorsMultiset (body, loader) {
    fetch(yasrWindowVar.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: body
    })
    .then(response => {
        if (response.ok === true) {
            return response.json();
        } else {
            throw new Error('Ajax Call Failed.');
        }
    })
    .then(response => {
        //check if response is an object
        if (typeof response === 'object' && !Array.isArray(response) && response !== null) {
            //check if has property "status"
            if(Object.hasOwn(response, 'status')) {
                if(response.status !== 'success') {
                    throw new Error(response.text);
                }
                loader.innerText = response.text;
            }
        } else {
            throw new Error(`The response is not an object, response is: ${response}`);
        }
    })
    .catch(networkError => {
        loader.innerText = 'Ajax Call Failed'
        console.error('Fetch network error', networkError);
    })
    .catch(queryError => {
        loader.innerText = queryError;
        console.error('Error with the Query', queryError);
    })
}