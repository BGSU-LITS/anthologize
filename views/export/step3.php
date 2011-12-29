<div class="wrap anthologize">

	<div id="blockUISpinner">
		<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
		<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
	</div>

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

	<form action="<?php echo $action; ?>" method="post">

			<h3><?php echo $format_title ?></h3>
			<div id="publishing-options">

		<?php foreach($format as $oname => $odata): ?>
				<div class="export-options-box">
					<div class="pub-options-title"><?php _e($odata['label']); ?></div>

					<?php if ($odata['type'] === "checkbox"): ?>
						<input name="<?php echo $oname; ?>" id="<?php echo $oname; ?>" type="checkbox" value="<?php echo $odata['values']; ?>" />
					<?php elseif ($odata['type'] === "dropdown"): ?>
						<select name="<?php echo $oname; ?>">
						<?php foreach( $odata['values'] as $key => $value ): ?>
							<option value="<?php echo $key; ?>"<?php
								if($odata['default'] == $key){
									echo ' selected="selected"';
								}
							?>><?php echo $value; ?></option>
						<?php endforeach; ?>
						</select>
					<?php else: ?>
						<input name="<?php echo $oname; ?>" id="<?php echo $oname; ?>" type="text" />
					<?php endif; ?>
				</div>
		<?php endforeach; ?>

				<div class="export-options-box">
					<div class="pub-options-title"><?php _e( 'Shortcodes', 'anthologize' ) ?></div>
					<p><small><?php _e( 'WordPress shortcodes (such as [caption]) can sometimes cause problems with output formats. If shortcode content shows up incorrectly in your output, choose "Disable" to keep Anthologize from processing them.', 'anthologize' ) ?></small></p>
					<select name="do-shortcodes">
						<option value="1" checked="checked"><?php _e( 'Enable', 'anthologize' ) ?></option>
						<option value="0"><?php _e( 'Disable', 'anthologize' ) ?></option>
					</select>
				</div>
			</div>

			<input type="hidden" name="export-step" value="3" />

			<div style="clear: both;"> </div>
			<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
			<input type="hidden" name="filetype" value="<?php echo $filetype; ?>" />

			<div class="anthologize-button" id="export-next"><input type="submit" id="submit" value="<?php _e( 'Export', 'anthologize' ) ?>" /></div>

	</form>

</div>