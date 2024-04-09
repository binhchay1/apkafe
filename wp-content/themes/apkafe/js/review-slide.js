jQuery('.misha-upload-button').click(function (event) {
    console.log(1);
    event.preventDefault();
    const button = jQuery(this)
    const hiddenField = button.prev()
    const hiddenFieldValue = hiddenField.val().split(',')

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
            jQuery('.misha-gallery').append('<li data-id="' + image.id + '"><span style="background-image:url(' + image.attributes.url + ')"></span><a href="#" class="misha-gallery-remove">×</a></li>');
            hiddenFieldValue.push(image.id)
        });

        jQuery('.misha-gallery').sortable('refresh');
        hiddenField.val(hiddenFieldValue.join());

    }).open();
});


jQuery('.misha-gallery-remove').click(function (event) {

    event.preventDefault();

    const button = jQuery(this)
    const imageId = button.parent().data('id')
    const container = button.parent().parent()
    const hiddenField = container.parent().next()
    const hiddenFieldValue = hiddenField.val().split(",")
    const i = hiddenFieldValue.indexOf(imageId)

    button.parent().remove();

    if (i != -1) {
        hiddenFieldValue.splice(i, 1);
    }

    hiddenField.val(hiddenFieldValue.join());
    container.sortable('refresh');

});

jQuery('.misha-gallery').sortable({
    items: 'li',
    cursor: '-webkit-grabbing',
    scrollSensitivity: 40,

    stop: function (event, ui) {
        ui.item.removeAttr('style');

        let sort = new Array()
        const container = jQuery(this)

        container.find('li').each(function (index) {
            sort.push(jQuery(this).attr('data-id'));
        });

        container.parent().next().val(sort.join());
    }
});