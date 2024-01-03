const yasrPaginationButtonsUser  = document.getElementById('yasr-user-log-page-navigation-buttons');
const yasrPaginationButtonsAdmin = document.getElementById('yasr-admin-log-page-navigation-buttons');

if(yasrPaginationButtonsUser) {
    yasrLogWidget();
}
if(yasrPaginationButtonsAdmin) {
    yasrLogWidget('yasr-admin');
}

function yasrLogWidget(prefix='yasr-user') {
    let isAdminWidget = false;
    if (prefix === 'yasr-admin') {
        isAdminWidget = true;
    }

    const totalPages = document.getElementById(`${prefix}-log-total-pages`).dataset.yasrLogTotalPages;

    const ajaxAction = `${prefix}_change_log_page`;
    const nonce      = document.getElementById(`${prefix}-log-nonce-page`).value;

    let rowContainer = []; //array containing all the DOM containers of the rows
    let spanVote     = [];
    let rowTitle     = []; //array containing all the DOM containers for the title
    let rowDate      = []; //array containing all the DOM containers for the dates

    let userNameSpan = false;
    let avatar       = false;
    let ipSpan       = [];
    if(isAdminWidget === true) {
        userNameSpan = [];
        avatar       = [];
    }

    for (let i = 0; i < 8; i++) {
        rowContainer[i] = document.getElementById(`${prefix}-log-div-child-${i}`);
        spanVote[i]     = document.getElementById(`${prefix}-log-vote-${i}`);
        rowTitle[i]     = document.getElementById(`${prefix}-log-post-${i}`);
        rowDate[i]      = document.getElementById(`${prefix}-log-date-${i}`);

        if(isAdminWidget === true) {
            userNameSpan[i] = document.getElementById(`${prefix}-log-user-${i}`);
            avatar[i]       = document.getElementById(`${prefix}-log-avatar-${i}`);
            ipSpan[i]       = document.getElementById(`${prefix}-log-ip-${i}`) || false;
        }
    }

    yasrLogWidgetOnClick(rowContainer, spanVote, rowTitle, rowDate, totalPages, userNameSpan, avatar, prefix, ipSpan, ajaxAction, nonce);
}

/**
 *
 * Action to do on click
 *
 * @param rowContainer
 * @param spanVote
 * @param rowTitle
 * @param rowDate
 * @param totalPages
 * @param userNameSpan
 * @param avatar
 * @param prefix
 * @param ipSpan
 * @param ajaxAction
 * @param nonce
 */
function yasrLogWidgetOnClick(rowContainer, spanVote, rowTitle, rowDate, totalPages, userNameSpan,
                              avatar, prefix, ipSpan, ajaxAction, nonce) {
    const pageNumbers = document.querySelectorAll(`.${prefix}-log-page-num`);
    pageNumbers.forEach(function(pageNumber) {
        pageNumber.addEventListener('click', function() {
            const pagenum = parseInt(this.value);
            yasrUpdateLogUsersPagination(pagenum, totalPages, prefix);
            yasrPostDataLogUsers(pagenum, rowContainer, spanVote, rowTitle, rowDate, ipSpan, totalPages,
                userNameSpan, avatar, prefix, ajaxAction, nonce);
        });
    });
}


/**
 * Update the pagination
 * @param pagenum
 * @param totalPages
 * @param prefix
 * @returns {string}
 */
function yasrUpdateLogUsersPagination (pagenum, totalPages, prefix) {
    //cast to int
    pagenum = parseInt(pagenum);

    let newPagination = '';
    if (pagenum >= 3 && totalPages > 3) {
        newPagination += `<button class="${prefix}-log-page-num" value="1">
            &laquo; First </button>&nbsp;&nbsp;...&nbsp;&nbsp;`
    }

    let startFor = pagenum - 1;

    if (startFor <= 0) {
        startFor = 1;
    }

    let endFor = pagenum + 1;

    if (endFor >= totalPages) {
        endFor = totalPages;
    }

    for (let i = startFor; i <= endFor; i++) {
        if (i === pagenum) {
            newPagination += `<button class="button-primary" value="${i}">${i}</button>&nbsp;&nbsp;`;
        } else {
            newPagination += `<button class="${prefix}-log-page-num" value="${i}">${i}</button>&nbsp;&nbsp;`;
        }
    }

    if (totalPages > 3 && pagenum < totalPages) {
        newPagination += `...&nbsp;&nbsp;
            <button class="${prefix}-log-page-num" value="${totalPages}"> Last &raquo;</button>&nbsp;&nbsp;`;
    }

    if(prefix === 'yasr-admin') {
        return yasrPaginationButtonsAdmin.innerHTML = newPagination;
    }

    return yasrPaginationButtonsUser.innerHTML = newPagination;
}

/**
 *
 * Show / hide the loader, and call the ajax action
 *
 * @param pagenum
 * @param rowContainer
 * @param spanVote
 * @param rowTitle
 * @param rowDate
 * @param ipSpan
 * @param totalPages
 * @param userNameSpan
 * @param avatar
 * @param prefix
 * @param ajaxAction
 * @param nonce
 */
function yasrPostDataLogUsers(pagenum, rowContainer, spanVote, rowTitle, rowDate, ipSpan, totalPages, userNameSpan, avatar,
                              prefix, ajaxAction, nonce) {

    const loader = document.getElementById(`${prefix}-log-loader-metabox`);

    //show the loader
    loader.style.display = 'inline';

    const data = {
        action: ajaxAction,
        pagenum: pagenum,
        totalpages: totalPages,
        yasr_user_log_nonce: nonce
    };

    // noinspection JSUnusedLocalSymbols
    fetch(yasrWindowVar.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data).toString(),
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
                    throw new Error(response.message);
                }
                updateTableUserRateHistory(response)
                yasrLogWidgetOnClick(rowContainer, spanVote, rowTitle, rowDate, totalPages, userNameSpan, avatar, prefix, ipSpan, ajaxAction, nonce);

            }
        } else {
            throw new Error(`The response is not an object, response is: ${response}`);
        }
    })
    .catch(networkError => {
        console.error('Fetch network error', networkError);
    })
    .catch(queryError => {
        console.error('Error with the Query', queryError);
    })
    .finally(() => {
        loader.style.display = 'none';
    });

    /**
     * Update the table
     *
     * @param response
     */
    const updateTableUserRateHistory = (response) => {
        let title;
        for (let i = 0; i < 8; i++) {
            if (response.data[i]) {
                rowContainer[i].style.display = 'block';
                spanVote[i].innerText = parseInt(response.data[i].vote);

                if (Array.isArray(userNameSpan)) {
                    userNameSpan[i].innerText = response.data[i].user_nicename;
                }
                if (Array.isArray(avatar)) {
                    avatar[i].src = response.data[i].avatar_url;
                }

                if (i in ipSpan) {
                    ipSpan[i].innerText = response.data[i].ip;
                }

                title = `<a href="${response.data[i].permalink}">${response.data[i].post_title}</a>`

                //update the title
                rowTitle[i].innerHTML = title;
                //update the date
                rowDate[i].innerText = response.data[i].date
            } else {
                rowContainer[i].style.display = 'none';
            }
        }
    }
}
