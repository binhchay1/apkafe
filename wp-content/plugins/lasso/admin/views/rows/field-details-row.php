<div class="row shadow url-details-field-box is-dismissable ${ element.field_id != `1` ? `cursor-move` : `static` }" 
    ${ element.field_id != `1` ? `` : `id="static-0"` }
    data-field-id="${ element.field_id }" 
    data-lasso-id="${ element.lasso_id }">
    
    ${ element.field_id != `1` ? `
    <div class="grip">
        <i class="far fa-grip-vertical dark-gray"></i>
    </div>
    ` : `` } 

    <div class="col">
        <div>
            <label class="mb-3" data-tooltip="${ element.field_description }">
                <strong>${ element.field_name }</strong>
            </label>

            <label class="toggle m-0 mb-3 float-right" data-tooltip="Show this field in your display by default.">
                <input id="fieldvisible_${ element.field_id }" class="field_visible" type="checkbox" 
                ${ element.field_visible == `1` ? `checked` : `` }>
                <span class="slider"></span>
            </label>
            ${ element.field_id == `1` ? `
                <label class="toggle m-0 mb-3 mr-1 float-right" data-tooltip="Show field name in your display.">
                    <input id="show_field_name_${ element.field_id }" class="field_visible" type="checkbox" 
                    ${ element.show_field_name == `true` ? `checked` : `` }>
                    <span class="slider"></span>
                </label>
            ` : `` }

            ${ element.field_type == `text` ? `
                <input type="text" class="form-control field_value" id="field_${ element.field_id }" value="${ element.field_value }" 
                placeholder="${ element.field_description }">
            ` : `` } 
            ${ element.field_type == `textarea` || element.field_type == `bulleted_list` || element.field_type == `numbered_list` ? `
                <textarea class="form-control field_value" id="field_${ element.field_id }" rows="3"
                placeholder="${ element.field_description }">${ element.field_value }</textarea>
            ` : `` } 
            ${ element.field_type == `number` ? `
                <input type="number" class="form-control field_value" id="field_${ element.field_id }" value="${ element.field_value }" 
                placeholder="${ element.field_description }">
            ` : `` } 
            ${ element.field_type == `rating` ? `
                <div class="rating-container">
                    <span class="lasso-stars" style="--rating: ${ element.field_value == `` ? `3.5` : parseFloat(element.field_value).toFixed(1) };" aria-label="Rating of this product is 3.5 out of 5."></span>
                    <input type="number" class="form-control field_value star_value float-left" id="field_${ element.field_id }" value="${ element.field_value == `` ? `3.5` : parseFloat(element.field_value).toFixed(1) }"
                    placeholder="3.5" min="1" max="5" step="0.1">
                </div>
                
            ` : `` } 

        </div>
    </div>

    <div class="opp-dismiss">
        <a href="#" class="js-remove-field" 
            data-toggle="modal" 
            data-target="#field-delete" 
            data-field-id="${ element.field_id }" 
            data-field-name="${ element.field_name }" 
            data-lasso-id="${ element.lasso_id }">
            <i class="far fa-times-circle"></i>
        </a>
    </div>
</div>