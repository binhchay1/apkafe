import {ReturnTableTbody} from "./returnTableTbody";
import {ReturnTableHead}  from "./returnTableHead";

const YasrRankingTable = ({error, isLoaded, data, source, rankingParams, tableId}) => {

    if(error) {
        return (
            <tbody>
                <tr>
                    <td>
                        {console.log(error)}
                        Error
                    </td>
                </tr>
            </tbody>
        )
    }

    if (isLoaded === false) {
        return (
            <tbody>
                <tr>
                    <td>
                        {JSON.parse(yasrWindowVar.textLoadRanking)}
                    </td>
                </tr>
            </tbody>
        )
    }

    if(source === 'overall_rating' || source === 'author_multi') {
        return (
            <ReturnTableTbody
                data={data}
                tableId={tableId}
                tBodyId={'overall_'+tableId}
                rankingParams={rankingParams}
                show={'table-row-group'}
                source={source}
            />
        )
    }

    const vvMost      = data.most;
    const vvHighest   = data.highest;
    const display     = 'table-row-group';
    const hide        = 'none';

    let defaultView = 'most';
    let styleMost    = display;
    let styleHighest = hide;

    let params = new URLSearchParams(rankingParams);

    if(params.get('view') !== null) {
        defaultView = params.get('view');
    }

    if(defaultView === 'highest') {
        styleMost    = hide;
        styleHighest = display;
    }

    return (
        <>
            <ReturnTableHead
                tableId={tableId}
                source={source}
                defaultView={defaultView}
            />
            <ReturnTableTbody
                data={vvMost}
                tableId={tableId}
                tBodyId={'most-rated-posts-'+tableId}
                rankingParams={rankingParams}
                show={styleMost}
                source={source}
            />
            <ReturnTableTbody
                data={vvHighest}
                tableId={tableId}
                tBodyId={'highest-rated-posts-'+tableId}
                rankingParams={rankingParams}
                show={styleHighest}
                source={source}
            />
        </>
    )

}

export {YasrRankingTable};
