<div class="wrap">
	<h1>CloudArcade Sync</h1>
	<form method="post" action="">
		<?php wp_nonce_field('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce'); ?>
		<input type="hidden" name="cloudarcade_wp_sync_games" value="1">
		<input type="submit" value="Sync Games" class="button button-primary button-large">
	</form>
	<br>
	<form method="post" action="">
		<?php wp_nonce_field('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce'); ?>
		<input type="hidden" name="cloudarcade_wp_sync_category" value="1">
		<input type="submit" value="Sync Categories" class="button button-primary button-large">
	</form>
	<br>
	<form method="post" action="">
		<?php wp_nonce_field('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce'); ?>
		<input type="hidden" name="cloudarcade_wp_delete_all" value="1">
		<input type="submit" value="Delete all games" class="button button-primary button-large">
	</form>
</div>