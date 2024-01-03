import {SetInnerHtml} from "./setInnerHtml";

/**
 * @author Dario Curvino <@dudo>
 * @since  3.0.8
 *
 * @param number_of_votes
 * @param rating
 * @param text
 * @returns {JSX.Element}
 * @constructor
 */
const TextAfterStars = ({post:{number_of_votes, rating}, text}) => {
    //If number_of_votes exists
    if(typeof number_of_votes !== "undefined") {
        let text   = JSON.parse(yasrWindowVar.textAfterVr);
        text       = text.replace('%total_count%', number_of_votes);
        text       = text.replace('%average%', rating);
        return (
            <div className='yasr-most-rated-text'>
                <SetInnerHtml html={text} />
            </div>
        )
    }

    return (
        <span>
            {text} {rating}
        </span>
    );

};

export {TextAfterStars};
