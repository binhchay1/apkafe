window.yasrTrueFalseStringConvertion = function (string) {
    if (typeof string === 'undefined' || string === null || string === '') {
        string = false;
    }

    //Convert string to boolean
    if (string === 'true' || string === '1') {
        string = true;
    }
    if (string === 'false' || string === '0') {
        string = false;
    }

    return string;
}

//from https://stackoverflow.com/questions/3710204/how-to-check-if-a-string-is-a-valid-json-string

/**
 * If you don't care about primitives and only objects then this function
 * is for you, otherwise look elsewhere.
 * This function will return `false` for any valid json primitive.
 * EG, 'true' -> false
 *     '123' -> false
 *     'null' -> false
 *     '"I'm a string"' -> false
 */
window.yasrValidJson = function(jsonString) {
    try {
        const o = JSON.parse(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns null, and typeof null === "object",
        // so we must check for that, too. Thankfully, null is falsey, so this suffices:
        if (o && typeof o === "object") {
            return o;
        }
    }
    catch (e) {
        console.error('Not a valid Json Element');
        console.log(e)
    }

    return false;
}

/**
 *
 * @param {number} starSize
 * @param {string} htmlId
 * @param {boolean | HTMLElement} element
 * @param {number} step
 * @param {boolean} readonly
 * @param {boolean | number} rating
 * @param {boolean | callback} rateCallback
 */
window.yasrSetRaterValue = function (starSize,
                                     htmlId,
                                     element=false,
                                     step=0.1,
                                     readonly=true,
                                     rating=false,
                                     rateCallback=false
) {
    let domElement;
    if(element) {
        domElement = element;
    } else {
        domElement = document.getElementById(htmlId)
    }

    //convert to be a number
    starSize = parseInt(starSize);

    raterJs({
        starSize: starSize,
        showToolTip: false,
        element: domElement,
        step: step,
        readOnly: readonly,
        rating: rating,
        rateCallback: rateCallback
    });

}