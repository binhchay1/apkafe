<?php 
use Lasso\Models\Fields;
?>

<a href="edit.php?post_type=lasso-urls&page=field-urls&post_id=${ element.id }&urls=${ element.count }" class="d-block p-2 text-break black hover-gray js-add-field-to-product" 
    data-field-id="${ element.id }" 
    data-field-type="${ element.field_type }" 
    data-product-id="<?php echo $_GET['post_id'] ?? 0; ?>" >
    <div class="row align-items-center">
        <!-- NAME -->
        <div class="col-lg pb-lg-0 pb-2 text-lg-left text-center">
            <strong>${ element.field_name }</strong><br/>
            <small>${ element.field_description }</small> <!-- Can also be added to tables -->
        </div>
        <div class="col-lg-1 pl-4 text-center">
            ${ element.id == `<?php echo Fields::PRODUCT_NAME_FIELD_ID ?>` ? `<i class="far fa-text"></i>` : `` } 
            ${ element.id == `<?php echo Fields::IMAGE_FIELD_ID ?>` ? `<i class="far fa-image"></i>` : `` } 
            ${ element.id == `<?php echo Fields::PRICE_ID ?>` ? `<i class="far fa-dollar-sign"></i>` : `` } 
            ${ element.id == `<?php echo Fields::DESCRIPTION_FIELD_ID ?>` ? `<i class="far fa-paragraph"></i>` : `` } 
            ${ element.field_type == `<?php echo Fields::FIELD_TYPE_BUTTON ?>` ? `<i class="far fa-rectangle-wide"></i>` : `` } 
            ${ element.field_type == `<?php echo Fields::FIELD_TYPE_TEXT ?>` ? `<i class="far fa-text"></i>` : `` } 
            ${ element.field_type == `<?php echo Fields::FIELD_TYPE_TEXT_AREA ?>` ? `<i class="far fa-paragraph"></i>` : `` } 
            ${ element.field_type == `<?php echo Fields::FIELD_TYPE_NUMBER ?>` ? `<i class="far fa-hashtag"></i>` : `` } 
            ${ element.field_type == `<?php echo Fields::FIELD_TYPE_RATING ?>` ? `<i class="far fa-star"></i>` : `` } 
        </div>
        <div class="col-lg-1 pr-2 text-center">
            ${ element.count }
        </div>
    </div>
</a>