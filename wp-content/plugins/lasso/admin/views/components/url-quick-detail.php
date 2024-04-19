<?php /** @var object $lasso_url */ ?>
<h2 class="product-name"><?php echo $lasso_url->name ?></h2>
<input type="hidden" id="lasso_id" value="<?php echo $lasso_url->lasso_id ?>">
<input type="hidden" id="thumbnail_image_url" value="<?php echo $lasso_url->image_src ?>"/>
<div class="row">
	<div class="col">
		<div class="row">
			<div class="col">
				<div class="form-group mb-4">
					<label for="affiliate_name"><strong>Name</strong></label>
					<input id="affiliate_name" name="affiliate_name" type="text" class="form-control affiliate_name" value="<?php  echo $lasso_url->name ?>" placeholder="URL Name Goes Here">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="form-group mb-4">
					<label for="badge_text"><strong>Badge Text</strong></label>
					<input id="badge_text" name="badge_text" type="text" class="form-control" value="<?php echo $lasso_url->display->badge_text; ?>" placeholder="Our Pick">
				</div>
			</div>
			<div class="col">
				<div class="form-group mb-4">
					<label for="button_text"><strong>Button Text</strong></label>
					<input id="buy_btn_text" name="buy_btn_text" type="text" class="form-control" value="<?php echo $lasso_url->display->primary_button_text; ?>" placeholder="Buy Now">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="form-group mb-4">
					<label for="description"><strong>Description</strong></label>
					<div class="form-control" id="description">
						<?php echo $lasso_url->description; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="row">
			<div class="col">
				<div class="image_wrapper">
					<div class="lasso-image">
						<img id="lasso_render_thumbnail" src="<?php echo $lasso_url->image_src; ?>" class="img-fluid url_image">
						<div class="image_loading d-none"><div class="py-5"><div class="ls-loader"></div></div></div>
						<div class="image_update" onclick="set_thumbnail()"><i class="far fa-camera-alt"></i> Update Image</div>
						<input type="hidden" id="lasso_thumbnail_id" name="lasso_thumbnail_id" value="<?php echo $lasso_url->thumbnail_id; ?>"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col text-center">
		<p class="js-error text-danger my-3 d-none"></p>
	</div>
</div>
<div class="row">
	<div class="col text-right">
		<button class="btn btn-lasso-save-link" data-post-id="<?php echo $lasso_url->lasso_id ?>" data-link-slug="<?php echo $lasso_url->slug ?>">
			<i class="far fa-plus-circle"></i> Add
		</button>
		<button class="btn btn-danger btn-close-save-quick-link">
			Cancel
		</button>
	</div>
</div>
<script>
	function set_thumbnail() {
		let elementor_editor = document.body.classList.contains( 'elementor-page' );

		if ( elementor_editor ) {
			var custom_uploader = parent.wp.media({
				title: 'Select an Image',
				multiple: false,
				library: { type : 'image' },
				button: { text: 'Select Image' }
			});
		} else {
			var custom_uploader = wp.media({
				title: 'Select an Image',
				multiple: false,
				library: { type : 'image' },
				button: { text: 'Select Image' }
				// frame: 'post'
			});
		}

		if(custom_uploader) {
			// When a file is selected, grab the URL
			custom_uploader.on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				jQuery("#lasso_render_thumbnail").attr('src', attachment.url);
				jQuery("#lasso_thumbnail_id").val(attachment.id);
				jQuery("#thumbnail_image_url").val(attachment.url);
			});

			custom_uploader.open();
		}
	}

	jQuery(document).on('click', '.btn-close-save-quick-link', function () {
		jQuery("#url-quick-detail").modal("hide");
	});
</script>
