
function addMediaHandle(thisBtn) {
    const button = thisBtn;
    const hiddenField = button.prev();
    const hiddenFieldValue = hiddenField.val();
    const areaReviewSlide = button.prev().prev();
    let hiddenFieldValueSplit = [];

    if (hiddenFieldValue) {
        hiddenFieldValueSplit = hiddenFieldValue.split(',');
    }

    const customUploader = wp.media({
        title: 'Insert images',
        library: {
            type: 'image'
        },
        button: {
            text: 'Use these images'
        },
        multiple: true
    }).on('select', function () {
        let selectedImages = customUploader.state().get('selection').map(item => {
            item.toJSON();
            return item;
        })

        selectedImages.map(image => {
            jQuery(areaReviewSlide).append('<li class="binhchay-li-item" data-id="' + image.id + '"><img src="' + image.attributes.url + '" /><br><button type="button" class="btn binhchay-gallery-remove" onclick="removeMediaHandler(jQuery(this))">Delete</button></li>');
            hiddenFieldValueSplit.push(image.id);
        });

        jQuery('.binhchay-gallery').sortable('refresh');
        hiddenField.val(hiddenFieldValueSplit.join());

    }).open();
}

function removeMediaHandler(thisBtn) {
    const button = thisBtn;
    const imageId = button.parent().data('id');
    const container = button.parent();
    const hiddenField = container.parent().next();
    const hiddenFieldValue = hiddenField.val().split(",");
    let index = -1;


    for (let i = 0; i < hiddenFieldValue.length; i++) {
        if (hiddenFieldValue[i] == imageId) {
            index = i;
        }
    }

    button.parent().remove();

    if (index != -1) {
        hiddenFieldValue.splice(index, 1);
    }

    hiddenField.val(hiddenFieldValue.join());
    container.parent().sortable('refresh');
}

function deleteSlideHandle(thisBtn) {
    const divParent = thisBtn.parent();
    divParent.remove();
}