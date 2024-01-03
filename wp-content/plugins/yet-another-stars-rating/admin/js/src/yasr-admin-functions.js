export const copyToClipboard = string => {
    const el = document.createElement('textarea');
    el.value = string;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
};

tippy(
    document.querySelectorAll('.yasr-copy-shortcode'),
    {
        content: 'Copied! Insert into your post!',
        theme: 'yasr',
        arrow: 'true',
        arrowType: 'round',
        trigger: 'click'
    }
);

export const getActiveTab = () => {
    //get active Tab
    let activeTab;
    let tabClass = document.getElementsByClassName('nav-tab-active');

    if(tabClass.length > 0){
        activeTab = document.getElementsByClassName('nav-tab-active')[0].id;
    }

    return activeTab;
}

/**
 * Return an error div
 * @param text
 * @returns {`<div class="notice notice-error" style="padding: 10px">${string}</div>`}
 */
export const yasrReturnErrorDiv = (text) => {
    return `<div class="notice notice-error" style="padding: 10px">${text}</div>`;
}

/**
 * Return a success div
 * @param text
 * @returns {`<div class="notice notice-success" style="padding: 10px">${string}</div>`}
 */
export const yasrReturnSuccessDiv = (text) => {
    return `<div class="notice notice-success" style="padding: 10px">${text}</div>`;
}