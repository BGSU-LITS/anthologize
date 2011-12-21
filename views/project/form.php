<div class="wrap anthologize">

	<div id="anthologize-logo">
		<img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" />
	</div>

	<h2><?php echo $title; ?></h2>

	<form action="<?php echo $action; ?>" method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="post_title"><?php _e( 'Project Title', 'anthologize' ) ?></label></th>
				<td><input type="text" name="post_title" value="<?php if ($project) echo $project->post_title; ?>"></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="anthologize_meta[subtitle]"><?php _e( 'Subtitle', 'anthologize' ) ?></label>
				<td><input type="text" name="anthologize_meta[subtitle]" value="<?php if( $project && !empty($meta['subtitle']) ) echo $meta['subtitle']; ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label><?php _e( 'Author Name <span>(optional)</span>', 'anthologize' ) ?></label></th>
				<td><textarea name="anthologize_meta[author_name]" rows="5" cols="50"><?php if( $project && !empty($meta['author_name']) ) echo $meta['author_name']; ?></textarea></td>
			</tr>

			<?php /* Hidden until there is a more straightforward way to display projects on the front end of WP */ ?>
			<?php /*
			<tr valign="top">
				<th scope="row"><label for="post_status"><?php _e( 'Project Status', 'anthologize' ) ?></label></th>
				<td>
					<input type="radio" name="post_status" value="publish" <?php if ( $project->post_status == 'publish' ) : ?>checked="checked"<?php endif; ?> > Published<br />
					<input type="radio" name="post_status" value="draft" <?php if ( $project->post_status != 'publish' ) : ?>checked="checked"<?php endif; ?>> Draft<br />
					<p><small><?php _e( 'Published projects are available via the web. Remember that you can change the status of your project later.', 'anthologize' ) ?></small></p>
				</td>
			</tr>
			*/ ?>

		</table>

		<?php if ($project): ?>
		<input type="hidden" name="project_id" value="<?php echo $project->ID; ?>">
		<?php endif; ?>

		<div class="anthologize-button">
			<input type="submit" value="<?php _e( 'Save Project', 'anthologize' ) ?>">
		</div>
	</form>

</div>
