import {copyToClipboard} from "./yasr-admin-functions";
import {v4 as uuidv4} from "uuid";

/**
 * Used in settings page, add a new criteria when creating a new set
 *
 * Handle the "Add new Criteria" button
 *
 */
export const addMultisetCriteria = () => {

    //This is the container of all criteria input
    const parentDiv        = document.getElementById('new-set-criteria-container');

    const newElementButton = document.getElementById('new-criteria-button');

    newElementButton.onclick = (event) => {
        event.preventDefault();

        //array with all div values
        let rows         = returnArrayElementsValues('removable-criteria');

        //find if there is a missing number in array
        let missingNumber = returnArrayMissingNumber(rows);

        let newRowNumber;

        //the first element to insert must be a missingNumber, otherwise array.lenght+1
        if(missingNumber !== false) {
            newRowNumber   = missingNumber;
        } else {
            newRowNumber   = rows.length + 1;
        }

        //Row number must be >= 3 and < 9
        if(newRowNumber < 3 || newRowNumber > 9 ) {
            return;
        }

        //Create the div
        const newDiv     = createNewCriteria(
            newRowNumber,
            'removable-criteria',
            'criteria-row-container-',
            'multi-set-name-element-',
            'multi-set-name-element-',
            'new-form'
        );

        //insert (or append) the new div
        insertNewCriteria(missingNumber, newRowNumber, parentDiv, newDiv);
    }
}

/**
 *  Used in the edit form, manage the "Add element" button
 *
 */
export const editFormAddElement = () => {
    const addElement = document.getElementById('yasr-add-field-edit-multiset');
    let   counter    = document.getElementById('yasr-edit-form-number-elements');

    if(!!counter !== true) {
        return;
    }
    counter = counter.value;
    counter++;

    const buttonDeleteThirdElement  = document.getElementById('edit-form-remove-3');
    const buttonDeleteFourthElement = document.getElementById('edit-form-remove-4');

    //decrease counter if third element is removed
    if(!!buttonDeleteThirdElement === true) {
        buttonDeleteThirdElement.addEventListener('click', (event) => {
            counter--;
            document.getElementById('yasr-element-limit').style.display = 'none';
            document.getElementById('yasr-add-field-edit-multiset').style.display = ''
        });
    }

    //decrease counter if fourth element is removed
    if(!!buttonDeleteFourthElement === true) {
        buttonDeleteFourthElement.addEventListener('click', (event) => {
            counter--;
            document.getElementById('yasr-element-limit').style.display = 'none';
            document.getElementById('yasr-add-field-edit-multiset').style.display = ''
        });
    }

    addElement.addEventListener('click', (event) => {
        event.preventDefault();

        //@todo increase number of element that can be stored
        if (counter > 9) {
            document.getElementById('yasr-element-limit').style.display = '';
            document.getElementById('yasr-add-field-edit-multiset').style.display = 'none'
            return false;
        }

        const parent = document.getElementById('edit-set-criteria-container');
        const newDiv = createNewCriteria(
            counter,
            'edit-form-removable-criteria',
            'edit-form-criteria-row-container-',
            'edit-multi-set-element-',
            'edit-form-multi-set-name-element-',
            'edit-form',
            false
        );

        insertNewCriteria(false, counter, parent, newDiv);

        counter++;
    });
}

/**
 * Return an array of int with all the "value" attribute of an element.
 *
 * This function can't be used if the element doesn't have the "value" attribute!!
 *
 * @param className
 * @returns {number[]}
 */
const returnArrayElementsValues = (className) => {
    return [...document.getElementsByClassName(className)]
        .map(
            el => parseInt(el.dataset.row)
        );
}

/**
 * Return the first number missing in an array, and if none is found, just array.length +1
 *
 * @param array
 * @returns {boolean}
 */
const returnArrayMissingNumber = (array) => {
    let missingNumber = false

    //be sure array is sorted
    array.sort()

    //find the first missing number in array
    for (let i = 1; i <= array.length; i++) {
        if (array.indexOf(i) === -1) {
            missingNumber = i;
            break; //break at first missing number
        }
    }

    return missingNumber;
}

/**
 * Return an empty row with new criteria and delete button
 *
 * @param newRowNumber
 * @param secClass
 * @param containerId
 * @param inputName
 * @param inputId
 * @param context
 * @param removable
 * @returns {*}
 */
const createNewCriteria = (newRowNumber, secClass, containerId, inputName, inputId, context, removable=true) => {

    const divClass        = `criteria-row ${secClass}`;

    //Create the div
    const newCriteria     = document.createElement('div');

    newCriteria.className = divClass;
    newCriteria.id        = `${containerId}${newRowNumber}`;
    newCriteria.setAttribute('data-row', `${newRowNumber}`);

    let removeButtonId;
    if(context === 'edit-form') {
        removeButtonId = `edit-form-remove-${newRowNumber}`
    } else {
        removeButtonId = `new-form-remove-${newRowNumber}`
    }

    newCriteria.innerHTML = `
        <label>
            <input type="text"
                name="${inputName}${newRowNumber}"
                id="${inputId}${newRowNumber}"
                class="input-text-multi-set"
                placeholder="New Criteria"
            />
        </label> `;

    if(removable !== false) {
        newCriteria.innerHTML += ` <span
            class="dashicons dashicons-remove yasr-multiset-info-delete criteria-delete"
            id="${removeButtonId}"
            data-id-criteria="${newCriteria.id}"
            onclick="document.getElementById('${newCriteria.id}').remove();"
            >
        </span>`;
    }

    return newCriteria;
}

/**
 * insert (or append) the new div when creating the new set
 *
 * @param missingNumber
 * @param newRowNumber
 * @param parentDiv
 * @param newDiv
 */
const insertNewCriteria = (missingNumber, newRowNumber, parentDiv, newDiv) => {
    if(missingNumber !== false) {
        //value to increase newRowNumber
        let j = 1;

        for(let i=3; i<9; i++) {
            let nextId     = newRowNumber + j;
            let idNextNode = `criteria-row-container-${nextId}`;

            //if idNextNode exists, insert the new div before
            if(!!document.getElementById(idNextNode) === true) {
                let nextDiv = document.getElementById(idNextNode);
                parentDiv.insertBefore(newDiv, nextDiv);

                //job done, break the loop
                break;
            }

            //otherwise increase J
            j++;
        }
    }
    //just do appendChild if we're adding and no field was removed
    else {
        parentDiv.appendChild(newDiv);
    }
}


/**
 *  Used in edit page, print the new set in the box below the editor
 */

export const yasrMultiCriteriaEditPage = () => {

    let divContainer                 = document.getElementById('yasr-editor-multiset-container');
    let nMultiSet                  = parseInt(divContainer.getAttribute('data-nmultiset'));
    let setId                      = parseInt(divContainer.getAttribute('data-setid'));
    let postId                     = parseInt(divContainer.getAttribute('data-postid'));
    //do not use parseInt here, or an empty value will be converted to 0
    const yasrProReviewSetid         = document.getElementById('yasr-pro-review-setid');

    let yasrProReviewSetidPostMeta = null;
    if(!!document.getElementById('yasr-pro-review-setid-postmeta-value') === true) {
        yasrProReviewSetidPostMeta = document.getElementById('yasr-pro-review-setid-postmeta-value').value;
    }

    const copyRoMultiset             = document.getElementById('yasr-editor-copy-readonly-multiset');
    const copyVisitorMultiset        = document.getElementById('yasr-editor-copy-visitor-multiset');
    const copyAverageMultiSet        = document.getElementById('yasr-editor-copy-average-multiset');
    const copyAverageVVMultiSet      = document.getElementById('yasr-editor-copy-average-vvmultiset');
    const copyAverageCommentsMulti   = document.getElementById('yasr-editor-copy-comments-multiset');
    const reviewsEnabled             = document.getElementById('yasr-pro-comments-enabled-yes');
    const multiSetinReview           = document.getElementById('yasr-pro-multiset-review-switcher');
    const nonceSetId                 = document.getElementById('yasr-send-id-nameset-nonce').value;

    if(yasrProReviewSetidPostMeta !== null && yasrProReviewSetidPostMeta !== '') {
        //when page loads, if the current selected set is the same for the current post, enable the switcher
        //Insert this Multi Set in the comment form?
        //keep == instead of ===
        if (setId == yasrProReviewSetidPostMeta) {
            multiSetinReview.checked = true;
        } else {
            multiSetinReview.checked = false;
        }
    }

    yasrManageMultiSelectEditPage(setId, postId, nMultiSet, null, null, nonceSetId);

    copyRoMultiset.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.textContent.trim());
    }

    copyVisitorMultiset.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.textContent.trim());
    }

    copyAverageMultiSet.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.textContent.trim());
    }

    copyAverageVVMultiSet.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.textContent.trim());
    }

    copyAverageCommentsMulti.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.textContent.trim());
    }

    //add event listener to synchronize switchers
    sincronizeEditorSwitchers (multiSetinReview, reviewsEnabled, yasrProReviewSetid, setId, nMultiSet);

    //show a select if more than 1 multiset is used
    selectMultiset(nMultiSet, postId, yasrProReviewSetid, multiSetinReview);

}

/**
 * when multiset multiSetinReview is enabled, enable also reviews in comment multiSetinReview
 *
 * @param multiSetinReview     | Switcher for question  Insert this Multi Set in the comment form?
 * @param reviewsEnabled       | Switcher to enable or disable reviews in comments
 * @param yasrProReviewSetid   | dom element of hidden field with id yasrProReviewSetid
 * @param setId                | the set id at page load, to be used only if one multiset is used
 * @param nMultiSet            | total nuber of set ids
 */
const sincronizeEditorSwitchers = (multiSetinReview, reviewsEnabled, yasrProReviewSetid, setId, nMultiSet) => {
    //add event listener to synchronize switchers
    if(multiSetinReview !== null) {

        //this only works in classic editor
        if (reviewsEnabled !== null) {
            //when reviews in comment are disabled, disable also multiset multiSetinReview
            reviewsEnabled.addEventListener('change', (event) => {
                if (!event.currentTarget.checked) {
                    multiSetinReview.checked = false;
                }
            })
        }

        //click on "Insert this Multi Set in the comment form?"
        multiSetinReview.addEventListener('change', (event) => {
            //when switcher is enabled
            if (event.currentTarget.checked === true) {
                //if it is classic editor, check reviewsEnabled on true
                if (reviewsEnabled !== null) {
                    reviewsEnabled.checked = true;
                } else {
                    //if this is gutenberg, use document.getElementById on change to get the current state and check it
                    //document.getElementById('yasr-comment-reviews-disabled-switch').checked = true;
                }

                //update the hidden field, if only one multiset is used
                if (yasrProReviewSetid !== null && nMultiSet === 1) {
                    yasrProReviewSetid.value = setId;
                } else {
                    //get the selected set
                    const selectMulti = document.getElementById("yasr_select_set");
                    yasrProReviewSetid.value = selectMulti.value;
                }
            } else {
                //update the hidden field
                if (yasrProReviewSetid !== null) {
                    yasrProReviewSetid.value = '';
                }
            }
        });
    }
}

/**
 * Show the select if more than one multiset is used
 *
 * @param nMultiSet
 * @param postId  bool | int
 * @param yasrProReviewSetid
 * @param multiSetinReview
 * @param selectID
 */
export const selectMultiset = (
    nMultiSet = false,
    postId= false,
    yasrProReviewSetid = null,
    multiSetinReview = null,
    selectID = 'yasr_select_set'
) => {

    const select = document.getElementById(selectID);
    let   nonce  = false;
    //If more than 1 set is used...
    if(!!select === true) {
        select.addEventListener('change', function () {
            //get the set id on change
            const setId = select.value;

            //here I'm in edit page
            if (postId !== false) {
                //show the loader
                document.getElementById('yasr-loader-select-multi-set').style.display='';

                nonce = document.getElementById('yasr_nonce_change_set-id').value;
                yasrManageMultiSelectEditPage(setId, postId, nMultiSet, yasrProReviewSetid, multiSetinReview, nonce);
            }
            //here I'm in the setting page
            else {
                nonce = document.getElementById('nonce-settings-edit-form-id').value;
                yasrManageMultiSelectSettingsPage(setId, nonce);
            }
            return false; // prevent default click action from happening!
        });
    }
}

/**
 * First do the ajax call, then print the multiset with rating for the post
 *
 * @param setId
 * @param postid
 * @param nMultiSet
 * @param yasrProReviewSetid
 * @param multiSetinReview
 * @param nonce
 * @returns {boolean}
 */
const yasrManageMultiSelectEditPage = (
    setId,
    postid,
    nMultiSet,
    yasrProReviewSetid=null,
    multiSetinReview = null,
    nonce
) => {

    const data_id = {
        action: 'yasr_send_id_nameset',
        set_id:  setId,
        post_id: postid,
        yasr_send_id_nameset_nonce: nonce
    };

    jQuery.post(ajaxurl, data_id, function (response) {
        //Hide the loader near the select only if more multiset are used
        if (nMultiSet > 1) {
            document.getElementById('yasr-loader-select-multi-set').style.display = 'none';
        }

        let yasrMultiSetValue   = JSON.parse(response);
        let tableAuthorMulti    = document.getElementById('yasr-table-multi-set-admin');
        let tableAuthorVisitor  = document.getElementById('yasr-table-multi-set-admin-visitor');

        yasrReturnTableMultiset(yasrMultiSetValue, tableAuthorMulti);
        yasrReturnTableMultiset(yasrMultiSetValue, tableAuthorVisitor, false);

        //Set rater for divs
        yasrSetRaterAdminMulti();
        yasrSetRaterAdminMulti(false);

        let spanWithSetID = document.getElementsByClassName('yasr-editor-multiset-id');

        for (let i = 0; i < spanWithSetID.length; i++) {
            spanWithSetID[i].innerText = setId;
        }

    });

    //update hidden field
    document.getElementById('yasr-multiset-id').value = setId;

    if(yasrProReviewSetid !== null && yasrProReviewSetid !== '' && multiSetinReview !== null) {
        //update hidden field
        yasrProReviewSetid.value = setId;

        let yasrProReviewSetidPostMeta = null;
        if(!!document.getElementById('yasr-pro-review-setid-postmeta-value') === true) {
            yasrProReviewSetidPostMeta = document.getElementById('yasr-pro-review-setid-postmeta-value').value;
        }

        if(yasrProReviewSetidPostMeta !== null && yasrProReviewSetidPostMeta !== '') {

            //if the current seleced set is the same for the current post, enable the switcher
            if (setId === yasrProReviewSetidPostMeta) {
                multiSetinReview.checked = true;
            } else {
                multiSetinReview.checked = false;
            }
        }
    }

    return false; // prevent default click action from happening!
}

/**
 * Do the ajax call when the multi select is changed in setting page
 */
const yasrManageMultiSelectSettingsPage = (setId, nonce) => {
    const data = {
        action: 'yasr_get_multi_set',
        set_id: setId,
        nonce: nonce
    };

    jQuery.post(ajaxurl, data, function (response) {
        jQuery('#yasr-table-form-edit-multi-set').html(response);
    });

    jQuery(document).ajaxComplete(function () {
        editFormAddElement();
    });
}

/**
 *
 * @param yasrMultiSetValue
 * @param table
 * @param authorMultiset
 */
const yasrReturnTableMultiset = (yasrMultiSetValue, table, authorMultiset=true) => {

    let content = '';
    let divClass   = 'yasr-multiset-admin'

    for (let i = 0; i < yasrMultiSetValue.length; i++) {
        let valueName = yasrMultiSetValue[i]['name'];
        let valueRating = 0;
        let readonly    = true;

        if(authorMultiset !== false) {
            valueRating = yasrMultiSetValue[i]['average_rating'];
            readonly    = false;
            divClass    = 'yasr-multiset-admin-author'
        }

        let valueID = yasrMultiSetValue[i]['id'];

        content += '<tr>';
        content += `<td>${valueName}</td>`;
        content += `<td><div class="${divClass}" id="yasr-multiset-admin-${uuidv4()}" data-rating="${valueRating}"
                        data-multi-idfield="${valueID}" data-readonly="${readonly}"></div>`;
        content += '</td>';
        content += '</tr>';
    }

    if(authorMultiset === false) {
        let button = '<tr><td colspan="2"><input type="submit" class="button button-primary" value="Submit!" disabled></td></tr>';
        content += button;
    }

    table.innerHTML = content;

}

/**
 *
 * @param authorMultiset
 */
const yasrSetRaterAdminMulti = (authorMultiset=true) => {

    let divClass;
    if(authorMultiset !== false) {
        divClass = 'yasr-multiset-admin-author';
    } else {
        divClass = 'yasr-multiset-admin';
    }

    const yasrMultiSetAdmin = document.getElementsByClassName(divClass);

    //an array with all the ratings objects
    let ratingArray = [];
    let ratingValue = false;

    for (let i = 0; i < yasrMultiSetAdmin.length; i++) {

        (function (i) {

            let htmlId = yasrMultiSetAdmin.item(i).id;
            let elem = document.getElementById(htmlId);

            let setIdField       = parseInt(elem.getAttribute('data-multi-idfield'));
            let ratingOnLoad     = parseInt(elem.getAttribute('data-rating'));
            //convert into boolean https://stackoverflow.com/a/264037/3472877
            let readOnly         = (elem.getAttribute('data-readonly') === 'true');

            let ratingObjectOnLoad = {
                field: setIdField,
                rating: ratingOnLoad
            };

            //creating rating array
            ratingArray.push(ratingObjectOnLoad);

            const rateCallback =  function (rating, done) {
                rating = rating.toFixed(1);
                //Be sure is a number and not a string
                rating = parseFloat(rating);
                this.setRating(rating); //Set the rating

                //loop the array with existing rates
                for (let j = 0; j < ratingArray.length; j++) {
                    //if the field of the array is the same of the rated field, get the rating
                    if(ratingArray[j].field === setIdField) {
                        //the selected rating overwrite the existing one
                        ratingArray[j].rating = rating;
                    }
                }

                ratingValue = JSON.stringify(ratingArray);

                //update hidden field
                document.getElementById('yasr-multiset-author-votes').value = ratingValue;

                done();
            }

            yasrSetRaterValue(32, htmlId, false, 0.5, readOnly, false, rateCallback);

        })(i);

    } //End for

}//End function
