import {v4 as uuidv4}   from "uuid";
import {TextAfterStars} from "./textAfterStars";
import {InvokeRater}    from "./invokeRater";

/**
 * @author Dario Curvino <@dudo>
 * @since  3.0.8
 *
 * @param rankingParams
 * @param tableId
 * @param colClass
 * @param post
 * @returns {JSX.Element}
 * @constructor
 */
const ReturnTableColumnRight = ({rankingParams, tableId, colClass, post}) => {
    let txtPosition = 'after';
    let cstText    = JSON.parse(yasrWindowVar.textRating)

    let params = new URLSearchParams(rankingParams);

    if(params.get('text_position') !== null) {
        txtPosition = params.get('text_position');
    }
    if(params.get('custom_txt') !== null) {
        cstText = params.get('custom_txt');
    }

    const starsAttributes = {
        rating: post.rating,
        htmlId: 'yasr-ranking-element-' + uuidv4(),
        size: document.getElementById(tableId).dataset.rankingSize
    }

    if (txtPosition === 'before') {
        return (
            <td className={colClass}>
                <TextAfterStars post={post} text={cstText}/>
                <InvokeRater {...starsAttributes} />
            </td>
        )
    }

    return (
        <td className={colClass}>
            <InvokeRater {...starsAttributes} />
            <TextAfterStars post={post} text={cstText} />
        </td>
    )
};

export {ReturnTableColumnRight};
