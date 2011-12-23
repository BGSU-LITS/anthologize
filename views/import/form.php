<div class="wrap anthologize">

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'Import Content', 'anthologize' ) ?></h2>

	<?php if($message): ?>
	<div id="message" class="<?php echo $message['type']; ?> fade">
		<p style="line-height: 150%"><?php echo $message['message']; ?></p>
	</div>
	<?php endif; ?>

	<p><?php _e( 'Want to populate your Anthologize project with content from another web site? Enter the RSS feed address of the site from which you\'d like to import and click Go.', 'anthologize' ) ?></p>

	<p><?php _e( 'Please respect the rights of copyright holders when using this import tool.', 'anthologize' ) ?></p>

	<form action="<?php echo $action; ?>" method="post">

		<h4><?php _e( 'Feed URL:', 'anthologize' ) ?></h4>
		<input type="text" name="feedurl" id="feedurl" size="100" />

		<div class="anthologize-button"><input type="submit" name="submit" id="submit" value="<?php _e( 'Go', 'anthologize' ) ?>" /></div>

	</form>
</div>
