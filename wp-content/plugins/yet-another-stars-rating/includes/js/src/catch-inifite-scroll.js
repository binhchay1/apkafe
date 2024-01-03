/**
 * This file is for add support to catch infinite scroll
 * @since 2.9.3
*/

import {yasrSearchStarsDom} from "./shortcodes/overall-multiset";
import {yasrSearchVVInDom}  from "./shortcodes/visitorVotes";
import {yasrDrawRankings}   from "./shortcodes/ranking";

jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
    let acceptedUrl = yasrWindowVar.siteUrl + '/page/';
    if(settings.url.includes(acceptedUrl)) {
        yasrCisOvMulti();
        yasrCisVV();
        yasrDrawRankings()
    }
});

function yasrCisOvMulti () {
    const arrayClasses = ['yasr-rater-stars', 'yasr-multiset-visitors-rater'];

    for (let i=0; i<arrayClasses.length; i++) {
        //Search and set all div with class yasr-multiset-visitors-rater
        yasrSearchStarsDom(arrayClasses[i]);
    }
}

function yasrCisVV () {
    const yasrRaterInDom = document.getElementsByClassName('yasr-rater-stars-vv');

    yasrSearchVVInDom (yasrRaterInDom);
}