<div class="container p-5 domain-opportunities-box">
	<div class="row p-4 border rounded">
		<div class="col-lg-4 purple-bg p-4 rounded">
			<img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/domain-report.png" class="img-fluid">
		</div>

		<div class="col-lg p-4 pt-5">
			<h3 class="mb-3">Find Site-Wide Monetization Opportunities</h3>
			<p class="mb-3">You’ll be shocked how many times you linked to a domain you didn’t know had an affiliate program. Sign up for that program, add your new affiliate URL to Lasso, and instantly monetize all those links.</p>
			<!--
			<strong><a href="http://support.getlasso.co/en/articles/4029174-how-to-use-opportunities" target="_blank" class="purple"><i class="far fa-play-circle"></i> See How To Monetize What You Already Link To</a></strong>
			-->
		</div>

		<div class="col-1">
			<a class="close close-domain-opportunities" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</a>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function() {
		var opportunities_box = jQuery('.domain-opportunities-box');
		opportunities_box.find('a.close').click(function() {
			opportunities_box.hide();
		});
	});
</script>
