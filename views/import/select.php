<div class="wrap anthologize">

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'Import Content', 'anthologize' ) ?></h2>

	<div id="export-form">

		<p><?php _e( 'Select the items you\'d like to import to your Imported Items library and click Import.', 'anthologize' ) ?></p>

		<form action="<?php echo $action; ?>" method="post">

			<h3><?php _e( 'Feed items:', 'anthologize' ) ?></h3>

			<ul class="potential-feed-items">
			<?php foreach ( $items as $key => $item ) : ?>
				<?php
					$author = '';
					foreach ( $item['authors'] as $author ) {
						$author .= $author->name . ' ';
					}
				?>
				<li>
					<label><input name="copyitems[]" type="checkbox" checked="checked" value="<?php echo $key ?>"> <strong><?php echo $item['title'] ?></strong></label>  <?php echo $item['description'] ?>
				</li>
			<?php endforeach; ?>
			</ul>

			<input type="hidden" name="feedurl" value="<?php echo $_POST['feedurl'] ?>" />
			<div class="anthologize-button"><input type="submit" name="submit_items" id="submit" value="<?php _e( 'Import', 'anthologize' ) ?>" /></div>

		</form>

	</div>
</div>
