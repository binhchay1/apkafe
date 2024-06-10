import { useBlockProps } from '@wordpress/block-editor';

//import Assets
import './assets/scss/edit.scss';
import Icon from './assets/img/icon.svg';

const Edit = () => {
    const blockProps = useBlockProps();
    return (
        <div {...blockProps}>
            <div className='bd_toc_block_wrapper'>
                <img src={Icon} />
                <h2>Top Table of Contents | <b>Boomdevs</b></h2>
            </div>
        </div>
    )
}

export default Edit