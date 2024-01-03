import {ReturnTableColumnLeft} from "./returnTableColumnLeft";
import {ReturnTableColumnRight} from "./returnTableColumnRight";

/**
 * Print row for Ranking Table
 *
 * @author Dario Curvino <@dudo>
 * @since  3.0.8
 *
 * @param props
 * @param {string} props.source   - Source of data
 * @param {Object} props.post     - Object with post attributes
 *
 * @return {JSX.Element} - html <tr> element
 */

const ReturnTableRow = (props) => {

    const columnLeftAttr = {
        colClass: props.leftClass,
        post: props.post
    }

    const columnRightAttr = {
        rankingParams: props.rankingParams,
        tableId: props.tableId,
        colClass: props.rightClass,
        post: props.post
    }

    return (
        <tr className={props.trClass}>
            <ReturnTableColumnLeft {...columnLeftAttr} />
            <ReturnTableColumnRight {...columnRightAttr} />
        </tr>
    )
};

export {ReturnTableRow};
