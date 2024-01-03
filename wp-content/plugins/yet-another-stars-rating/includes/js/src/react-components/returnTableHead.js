/**
 * Change style attribute for assigned tbody
 *
 * @author Dario Curvino <@dudo>
 * @since  2.5.7
 *
 */
const switchTBody = (props) => (event) => {

    event.preventDefault();
    const linkId        = event.target.id;

    const tableId       = props.tableId;
    const idLinkMost    = 'link-most-rated-posts-'+tableId;
    const idLinkHighest = 'link-highest-rated-posts-'+tableId;
    const bodyIdMost    = 'most-rated-posts-'+tableId;
    const bodyIdHighest = 'highest-rated-posts-'+tableId;

    //change html from a to span and vice versa
    //https://stackoverflow.com/a/13071899/3472877
    let anchor = document.getElementById(linkId);
    let span   = document.createElement("span");

    //Copy innerhtml and id into span element
    span.innerHTML = anchor.innerHTML;
    span.id        = anchor.id;

    //replace <a> with <span>
    anchor.parentNode.replaceChild(span,anchor);

    if(linkId === idLinkMost) {
        //Dispaly body for Most
        document.getElementById(bodyIdHighest).style.display = 'none';
        document.getElementById(bodyIdMost).style.display = '';

        //Here I've to replace <span> with <a>
        span             = document.getElementById(idLinkHighest);
        anchor.innerHTML = span.innerHTML;
        anchor.id        = span.id;
        span.parentNode.replaceChild(anchor,span);
    }
    if(linkId === idLinkHighest) {
        //Dispaly body for Highest
        document.getElementById(bodyIdMost).style.display = 'none';
        document.getElementById(bodyIdHighest).style.display = '';

        //Here I've to replace <span> with <a>
        span             = document.getElementById(idLinkMost);
        anchor.innerHTML = span.innerHTML;
        anchor.id        = span.id;
        span.parentNode.replaceChild(anchor,span);
    }


}

/**
 * Print Thead Ranking Table Head
 *
 * @author Dario Curvino <@dudo>
 * @since  2.5.7
 *
 * @return {JSX.Element} - html <thead> element
 */
const ReturnTableHead = (props) => {
    const {tableId, source, defaultView} = props;

    const idLinkMost    = 'link-most-rated-posts-'+tableId;
    const idLinkHighest = 'link-highest-rated-posts-'+tableId;

    if(source !== 'author_ranking') {
        let containerLink = <span>
                                <span id={idLinkMost}>
                                    {JSON.parse(yasrWindowVar.textMostRated)}
                                </span>&nbsp;|&nbsp;
                                <a href='#' id={idLinkHighest} onClick={switchTBody(props)}>
                                    {JSON.parse(yasrWindowVar.textHighestRated)}
                                </a>
                            </span>

        if(defaultView === 'highest') {
            containerLink = <span>
                                <span id={idLinkHighest} >
                                    {JSON.parse(yasrWindowVar.textHighestRated)}
                                </span>&nbsp;|&nbsp;
                                <a href='#' id={idLinkMost} onClick={switchTBody(props)}>
                                    {JSON.parse(yasrWindowVar.textMostRated)}
                                </a>
                            </span>
        }

        return (
            <thead>
            <tr className='yasr-rankings-td-colored yasr-rankings-heading'>
                <th>{JSON.parse(yasrWindowVar.textLeftColumnHeader)}</th>
                <th>
                    {JSON.parse(yasrWindowVar.textOrderBy)}:&nbsp;&nbsp;
                    {containerLink}
                </th>
            </tr>
            </thead>
        )
    }

    return (<></>)
}

export {ReturnTableHead};