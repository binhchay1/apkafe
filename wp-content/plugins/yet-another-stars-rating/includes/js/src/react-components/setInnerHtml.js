import striptags from "striptags";

/**
 * Strip a string to only allow <strong> and <p> tag (no XSS possible), and return it inside a div
 *
 * @returns {JSX.Element}
 * @param   html //destructured props
 */
const SetInnerHtml = ({html}) => {
    return (
        <div dangerouslySetInnerHTML={{__html: striptags(html, '<strong><p>')} }></div>
    );
};

export {SetInnerHtml};
