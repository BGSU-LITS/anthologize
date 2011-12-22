<div class="wrap anthologize">

	<div id="blockUISpinner">
		<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
		<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
	</div>

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'Settings', 'anthologize' ) ?></h2>

<?php if ($message): ?>
	<div id="message" class="updated fade">
		<p style="line-height: 150%"><?php
		_e("Settings saved!", 'anthologize');
		?></p>
	</div>
<?php endif; ?>
	
	<form action="<?php echo $action; ?>" method="post" id="bp-admin-form">

	<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php _e( 'Minimum role for creating and editing Anthologize projects', 'anthologize' ) ?>:</th>
			<td>
				<select name="anth_settings[minimum_cap]" <?php if ( $forbid_local_caps ) : ?>disabled="disabled"<?php endif ?>>
				<?php if ( is_multisite() ) : ?>
					<option<?php selected( $minimum_cap, 'manage_network' ) ?> value="manage_network"><?php _e( 'Network Admin', 'anthologize' ) ?></option>
				<?php endif ?>

					<option<?php selected( $minimum_cap, 'manage_options' ) ?> value="manage_options"><?php _e( 'Administrator', 'anthologize' ) ?></option>

					<option<?php selected( $minimum_cap, 'delete_others_posts' ) ?> value="delete_others_posts"><?php _e( 'Editor', 'anthologize' ) ?></option>

					<option<?php selected( $minimum_cap, 'publish_posts' ) ?> value="publish_posts"><?php _e( 'Author', 'anthologize' ) ?></option>

					<?php
					/* I think it doesn't make sense for these to be available
					<option<?php selected( $this->minimum_cap, 'edit_posts' ) ?> value="edit_posts"><?php _e( 'Contributor', 'anthologize' ) ?></option>

					<option<?php selected( $this->minimum_cap, 'read' ) ?> value="read"><?php _e( 'Subscriber', 'anthologize' ) ?></option>
					*/ ?>
				</select>
				<?php if ( $forbid_local_caps ) : ?>
					<label for="anth_settings[minimum_cap]"><?php _e( 'Your network administrator has disabled this setting.', 'anthologize' ) ?></label>
				<?php endif ?>
			</td>
		</tr>
	</tbody>
	</table>

	<p class="submit">
		<input class="button-primary" type="submit" name="anth_settings_submit" value="<?php _e( 'Save Settings', 'anthologize' ) ?>"/>
	</p>

	<?php wp_nonce_field( 'anth_settings' ) ?>


	</form>

</div>