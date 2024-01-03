import {yasrDrawRankings} from "../../../includes/js/src/shortcodes/ranking";
import {copyToClipboard} from "./yasr-admin-functions";
const  { __ } = wp.i18n; // Import __() from wp.i18n

let activeTab;
let tabClass = document.getElementsByClassName('nav-tab-active');

if(tabClass.length > 0){
    activeTab = document.getElementsByClassName('nav-tab-active')[0].id;
}

if (activeTab === 'rankings') {
    let elementsClassParents = '.yasr-builder-elements-parents'; //class shared between all elements
    let elementsClassChilds  = '.yasr-builder-elements-childs'; //class shared between all elements

    jQuery(elementsClassParents).prop('disabled', true);   //disable all parents elements;
    jQuery(elementsClassChilds).prop('disabled', true);    //disable all childs elements;

    jQuery(elementsClassParents).find('input').each(function () {
        jQuery(this).prop('disabled', true);
    });

    //add action in yasr-settings-page
    wp.hooks.doAction('yasrBuilderBegin', elementsClassParents, elementsClassChilds);

    let shortcode;
    let filterShortcodeAttributes = [];

    //initialize shortcode attributes
    let shortcodeAttributes = yasrReturnShortcodeAttributes();

    shortcode = shortcodeAttributes['name'];
    document.getElementById('yasr-builder-shortcode').textContent = '['+shortcode+']';
    document.getElementById('yasr-builder-copy-shortcode').setAttribute('data-shortcode', '['+shortcode+']');

    //Buttons
    const previewButton      = document.getElementById('yasr-builder-button-preview');
    const copyButton         = document.getElementById('yasr-builder-copy-shortcode');
    const previewDiv         = document.getElementById('yasr-builder-preview');

    //This is the select for data Source
    const selectDataSource   = document.getElementById('yasr-ranking-source');
    const selectMultiset     = document.getElementById('yasr-ranking-multiset-select');
    const datePickerStart    = document.getElementById('yasr-builder-datepicker-start');
    const datePickerEnd      = document.getElementById('yasr-builder-datepicker-end');

    //Containers Div
    const divParamsContainer = document.getElementById('yasr-builder-params-container');
    const divView            = document.getElementById('builder-vv-default-view');
    const divVotes           = document.getElementById('builder-vv-required-votes');
    const divSize            = document.getElementById('builder-stars-size');
    const divOverallTxt      = document.getElementById('builder-overall-text');
    const divUsername        = document.getElementById('builder-username-options');
    const divCategory        = document.getElementById('builder-category');
    const divCpt             = document.getElementById('builder-cpt');
    const divMultiSet        = document.getElementById('yasr-ranking-multiset');
    const divDatePicker      = document.getElementById('yasr-builder-datepicker');

    let dataSource     = selectDataSource.value;
    let previewClicked = false;

    //Be sure date picker fields are empty on page load (or reload)
    datePickerStart.value = '';
    datePickerEnd.value   = '';

    //if on page load is selected something else from the default ranking (yasr_most_or_highest_rated_posts)
    //show the proper fields
    if(dataSource === 'yasr_ov_ranking' ) {
        yasrBuilderOvOptions(divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker);
    }
    else if(dataSource === 'yasr_most_active_users' || dataSource === 'yasr_top_reviewers') {
        yasrBuilderNoStarsOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker);
    }
    else if(dataSource === 'yasr_multi_set_ranking') {
        yasrBuilderMultisetOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, false, divDatePicker);
    }
    else if(dataSource === 'yasr_visitor_multi_set_ranking') {
        yasrBuilderMultisetOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, true, divDatePicker);
    }
    else {
        yasrBuilderVvOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker)
    }

    //event listener for all change events
    document.addEventListener('change', event => {
        if(event.target.id === 'yasr-ranking-source') {
            //be sure to reset the class of the container to the default one
            divParamsContainer.className = '';
            divParamsContainer.classList.add('yasr-settings-row-33');

            //when event is on main select, be sure the preview div is empty
            previewDiv.innerHTML = '';

            //Empty date picker fields
            datePickerStart.value = '';
            datePickerEnd.value   = '';

            //reset attributes
            shortcodeAttributes =  yasrReturnShortcodeAttributes();

            if(event.target.value === 'yasr_ov_ranking') {
                yasrBuilderOvOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker);
            }
            else if(event.target.value === 'yasr_most_active_users' || event.target.value === 'yasr_top_reviewers') {
                yasrBuilderNoStarsOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker);
            }
            else if(event.target.value === 'yasr_multi_set_ranking') {
                yasrBuilderMultisetOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, false, divDatePicker);
                //get the value of the first option(array key 0) in the select
                shortcodeAttributes['setid'] =  ' setid='+selectMultiset[0].value;
            }
            else if(event.target.value === 'yasr_visitor_multi_set_ranking') {
                yasrBuilderMultisetOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, true, divDatePicker);
                //get the value of the first option(array key 0) in the select
                shortcodeAttributes['setid'] =  ' setid='+selectMultiset[0].value;
            }
            //By default, show setting for yasr_most_or_highest_rated_posts
            else {
                yasrBuilderVvOptions (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker)
            }

            //be sure shortcodeAttributes is initialized again
            shortcodeAttributes['name'] = event.target.value;
            shortcode = shortcodeAttributes['name']+shortcodeAttributes['setid'];
        }
        else {
            if(event.target.id === 'yasr-ranking-multiset-select') {
                shortcodeAttributes['setid'] = ' setid=' + event.target.value;
            }
            //use this filter to add new attributes
            filterShortcodeAttributes = wp.hooks.applyFilters('yasrBuilderFilterShortcode', shortcodeAttributes);

            //loop the shortcodeAttributes array from 'rows'
            //and if the key is found in filterShortcodeAttributes make the assigment
            for(let i=2; shortcodeAttributes.length; i++) {
                if(filterShortcodeAttributes.hasOwnProperty(shortcodeAttributes[i])) {
                    shortcodeAttributes[i] = filterShortcodeAttributes[i];
                }
            }

            shortcode =
                shortcodeAttributes['name']+
                shortcodeAttributes['setid']+
                shortcodeAttributes['rows']+
                shortcodeAttributes['view']+
                shortcodeAttributes['minvotesmost']+
                shortcodeAttributes['minvoteshg']+
                shortcodeAttributes['size']+
                shortcodeAttributes['txtPosition']+
                shortcodeAttributes['txt']+
                shortcodeAttributes['display']+
                shortcodeAttributes['start_date']+
                shortcodeAttributes['end_date']+
                shortcodeAttributes['category']+
                shortcodeAttributes['cpt'];

        }

        document.getElementById('yasr-builder-shortcode').textContent = '['+shortcode+']';
        document.getElementById('yasr-builder-copy-shortcode').setAttribute('data-shortcode', '['+shortcode+']');

        //If the previewButton was already clicked, and if the event is no the select, make fetch again
        if(previewClicked === true && event.target.id !== 'yasr-ranking-source' && event.target.name !== 'yasr-builder-category-radio') {
            yasrBuilderDoFetch(event);
        }
    });



    //event listener click on button
    copyButton.onclick = function (event) {
        let el = document.getElementById(event.target.id);
        copyToClipboard(el.getAttribute("data-shortcode"));
    }

    previewButton.onclick = function (event) {
        yasrBuilderDoFetch(event);
        previewClicked = true;
    }

    /**
     * This function do the fetch that calls admin-ajax / yasr_rankings_preview_shortcode
     *
     * @param event
     */
    function yasrBuilderDoFetch(event) {
        const shortcode           = selectDataSource.value
        const fullShortcode       = document.getElementById('yasr-builder-shortcode').textContent;

        //in this array we put the shortcode that need yasrDrawRankings() to work
        let shortcodesDrawRankings  = [
            'yasr_ov_ranking',
            'yasr_most_or_highest_rated_posts',
            'yasr_multi_set_ranking',
            'yasr_visitor_multi_set_ranking'
        ];

        //hooke here to add shortcode
        shortcodesDrawRankings = wp.hooks.applyFilters('yasrBuilderDrawRankingsShortcodes', shortcodesDrawRankings);

        fetch(ajaxurl +'?action=yasr_rankings_preview_shortcode&shortcode='+shortcode+'&full_shortcode='+fullShortcode)
            .then(response => {
                if (response.ok === true) {
                    return response.json();
                } else {
                    console.info(__('Ajax Call Failed. Shortcode preview can\'t be done', 'yet-another-stars-rating'));
                    return 'KO';
                }
            })
            .catch((error) => {
                console.info(error);
            })
            .then(response => {
                if (response !== 'KO') {
                    let newDiv = document.createElement('div');
                    newDiv.innerHTML = response;

                    //if a child already exists, replace it
                    if (previewDiv.childNodes.length > 0) {
                        previewDiv.replaceChild(newDiv, previewDiv.childNodes[0]);
                    } else {
                        previewDiv.appendChild(newDiv);
                    }
                }
            })
            .then(response => {
                shortcodesDrawRankings.forEach(element => {
                    //if the shortcode is in the array
                    if(shortcode === element) {
                        yasrDrawRankings();
                    }
                });
            })
    }

    /**
     * This function return the default array for shortcode attributes
     *
     * @return {{txt: string, size: string, txtPosition: string, name: string, rows: string}}
     */
    function yasrReturnShortcodeAttributes() {
        return {
            name: 'yasr_most_or_highest_rated_posts',
            setid:        '',
            rows:         '',
            size:         '',
            view:         '',
            minvotesmost: '',
            minvoteshg:   '',
            txtPosition:  '',
            txt:          '',
            display:      '',
            start_date:   '',
            end_date:     '',
            category:     '',
            cpt:          ''
        };
    }

    /**
     *
     * @param divOverallTxt
     * @param divSize
     * @param divView
     * @param divVotes
     * @param divUsername
     * @param divCategory
     * @param divCpt
     * @param divMultiSet
     * @param divDatePicker
     */
    function yasrBuilderOvOptions
        (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker) {
            //show custom text for overall
            divOverallTxt.style.display = '';
            divSize.style.display       = '';
            divCategory.style.display   = '';
            divDatePicker.style.display = '';
            divView.style.display       = 'none';
            divVotes.style.display      = 'none';
            divUsername.style.display   = 'none';
            if(divCpt !== null) {
                divCpt.style.display        = '';
            }
            if(divMultiSet !== null) {
                divMultiSet.style.display        = 'none';
            }
    }

    /**
     *
     * @param divOverallTxt
     * @param divSize
     * @param divView
     * @param divVotes
     * @param divUsername
     * @param divCategory
     * @param divCpt
     * @param divMultiSet
     * @param divDatePicker
     */
    function yasrBuilderVvOptions
        (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker) {
            divView.style.display       = ''
            divVotes.style.display      = '';
            divSize.style.display       = '';
            divCategory.style.display   = '';
            divDatePicker.style.display = '';
            divOverallTxt.style.display = 'none';
            divUsername.style.display   = 'none';
            if(divCpt !== null) {
                divCpt.style.display        = '';
            }
            if(divMultiSet !== null) {
                divMultiSet.style.display        = 'none';
            }
    }

    /**
     *
     * @param divOverallTxt
     * @param divSize
     * @param divView
     * @param divVotes
     * @param divUsername
     * @param divCategory
     * @param divCpt
     * @param divMultiSet
     * @param divDatePicker
     */
    function yasrBuilderNoStarsOptions
        (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt, divMultiSet, divDatePicker) {
            divUsername.style.display   = '';
            divDatePicker.style.display = 'none';
            divOverallTxt.style.display = 'none';
            divView.style.display       = 'none';
            divVotes.style.display      = 'none';
            divSize.style.display       = 'none';
            divCategory.style.display   = 'none';
            if(divCpt !== null) {
                divCpt.style.display        = 'none';
            }
            if(divMultiSet !== null) {
                divMultiSet.style.display        = 'none';
            }
    }

    /**
     *
     * @param divOverallTxt
     * @param divSize
     * @param divView
     * @param divVotes
     * @param divUsername
     * @param divCategory
     * @param divCpt
     * @param divMultiSet
     * @param visitor
     * @param divDatePicker
     */
    function yasrBuilderMultisetOptions
        (divOverallTxt, divSize, divView, divVotes, divUsername, divCategory, divCpt,
             divMultiSet, visitor=false, divDatePicker
        ) {
            //params for yasr_visitor_multi_set_ranking
            if(visitor === true) {
                //Here the div must not be 33 but 24
                divParamsContainer.className = '';
                divParamsContainer.classList.add('yasr-settings-row-24');
                divView.style.display       = ''
                divVotes.style.display = '';
                divOverallTxt.style.display = 'none';
            }
            //params for yasr_visitor_multi_set_ranking
            else {
                //common params for both
                divView.style.display       = 'none'
                divVotes.style.display      = 'none';
                divOverallTxt.style.display = '';
            }

            //common params for both
            divCategory.style.display   = '';
            divSize.style.display       = '';
            divDatePicker.style.display = '';
            divUsername.style.display   = 'none';

            if(divCpt !== null) {
                divCpt.style.display        = '';
            }
            if(divMultiSet !== null) {
                divMultiSet.style.display        = '';
            }


    }

}