import {decodeEntities} from "@wordpress/html-entities";

/**
 * Left column for a table, with post link and title
 *
 * @author Dario Curvino <@dudo>
 * @since  3.0.8
 *
 * @param {string} colClass - Column class name*
 * @param link              - post link
 * @param title             - post title
 *
 * @return {JSX.Element} - html <td> element
 */

const ReturnTableColumnLeft = ({colClass, post:{link, title}}) => {
    return (
        <td className={colClass}>
            <a href={link}>{decodeEntities(title)}</a>
        </td>
    )
};

export {ReturnTableColumnLeft};