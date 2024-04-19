jQuery(function (jQuery) {
	jQuery(document).on("input", '#custom-url [name="affiliate_name"]', function () {
		if (jQuery(this).val() != "") {
			jQuery("#permalink").text(lassoOptionsData.site_url + "/" + (jQuery(this).val()).replace(/\s+/g, '-').toLowerCase());
			jQuery("#basic_link").find(".btn-add-url").removeClass("hidden");
		} else {
			jQuery("#permalink").text("");
			jQuery("#basic_link").find(".btn-add-url").addClass("hidden");
		}
	});

	jQuery(document).on("click", '#custom-url .btn-add-url', function () {
		if (jQuery("#custom-url").find(".display-add").hasClass("hidden")) {
			jQuery("#custom-url").find(".display-add").removeClass("hidden");
			jQuery("#custom-url").find(".display-add").find("[name=uri]").val(jQuery('[name="affiliate_name"]').val().replace(/\s+/g, '-').toLowerCase());
			jQuery(this).addClass("hidden");
		}
	});

	jQuery(document).on("click", '#custom-url button.cancel-add-url', function () {
		jQuery("#custom-url").find(".display-add").addClass("hidden");
		jQuery("#custom-url").find(".btn-add-url").removeClass("hidden");
	});

	jQuery(document).on("click", '#custom-url button.btn-submit-add-url', function () {
		jQuery("#custom-url").find(".display-add").addClass("hidden");
		jQuery("#custom-url").find(".btn-add-url").removeClass("hidden");
		jQuery("#permalink").text(lassoOptionsData.site_url + "/" + (jQuery("[name=uri]").val()));
	});

	//button btn-submit-add-url vertical-middle
	//button cancel-add-url
});
