<h3><?php _e( 'Anthologize', 'anthologize' ); ?></h3>
		
<table id="menu" class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Allow individual site admins to determine which kinds of users can use Anthologize?', 'anthologize' ); ?></th>
		<td>

		<?php 
		/**
		 * This value is called 'forbid_per_blog_caps' but is worded in
		 * terms of 'allowing'. This is because I wanted the wording to be
		 * in terms of allowing (so that checked = allowed) but for the
		 * default value to be allowed, without needing to initialize
		 * options in the installer.
		 */
		?>
		<label><input type="checkbox" class="tags-input" name="anth_site_settings[forbid_per_blog_caps]" value="1" <?php if ( empty( $site_settings['forbid_per_blog_caps'] ) ) : ?>checked="checked"<?php endif ?>> <?php _e( 'When unchecked, access to Anthologize will be limited to the default role you select below.', 'anthologize' ) ?></label>

		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php _e( 'Default mimimum role for Anthologizers', 'anthologize' ); ?></th>
		<td>

		<label>
			<select class="tags-input" name="anth_site_settings[minimum_cap]">
				<option<?php selected( $minimum_cap, 'manage_network' ) ?> value="manage_network"><?php _e( 'Network Admin', 'anthologize' ) ?></option>

				<option<?php selected( $minimum_cap, 'manage_options' ) ?> value="manage_options"><?php _e( 'Administrator', 'anthologize' ) ?></option>

				<option<?php selected( $minimum_cap, 'delete_others_posts' ) ?> value="delete_others_posts"><?php _e( 'Editor', 'anthologize' ) ?></option>

				<option<?php selected( $minimum_cap, 'publish_posts' ) ?> value="publish_posts"><?php _e( 'Author', 'anthologize' ) ?></option>

				<?php /* Removing these for now */ ?>
				<?php /*
				<option<?php selected( $minimum_cap, 'edit_posts' ) ?> value="edit_posts"><?php _e( 'Contributor', 'anthologize' ) ?></option>

				<option<?php selected( $minimum_cap, 'read' ) ?> value="read"><?php _e( 'Subscriber', 'anthologize' ) ?></option>
				*/ ?>
			</select>
		</label>

		</td>
	</tr>
</table>
