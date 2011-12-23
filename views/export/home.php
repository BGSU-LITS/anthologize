<div class="wrap anthologize">

	<div id="blockUISpinner">
		<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
		<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
	</div>

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

	<div id="export-form">

		<form action="<?php echo $action; ?>" method="post">

		<label for="project_id"><?php _e( 'Select a project...', 'anthologize' ) ?></label>
		<select name="project_id" id="project-id-dropdown">
		<?php foreach ( $projects as $proj_id => $project_name ) : ?>
			<option value="<?php echo $proj_id ?>"

			<?php if ( $proj_id == $project_id ) : ?>selected="selected"<?php endif; ?>

			><?php echo $project_name ?></option>
		<?php endforeach; ?>
		</select>

		<h3 id="copyright-information-header"><?php _e( 'Copyright Information', 'anthologize' ) ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Year', 'anthologize' ) ?></th>
				<td><input type="text" id="cyear" name="cyear" value="<?php echo $cdate ?>"/></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Copyright Holder', 'anthologize' ) ?></th>
				<td><input type="text" id="cname" name="cname" value="<?php echo $cname ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Type', 'anthologize' ) ?></th>
				<td>
					<input type="radio" id="ctype" name="ctype" value="c" <?php if ( $ctype == 'c' ) echo 'checked="checked"' ?>/> <?php _e( 'Copyright', 'anthologize' ) ?><br />
					<input type="radio" id="ctype" name="ctype" value="cc" checked="checked" <?php if ( $ctype != 'c' ) echo 'checked="checked"' ?>/> <?php _e( 'Creative Commons', 'anthologize' ) ?>
						<select id="cctype" name="cctype">
							<option value=""><?php _e( 'Select One...', 'anthologize' ) ?></option>
							<option value="by" <?php if ( $cctype == 'by' ) echo 'selected="selected"' ?>><?php _e( 'Attribution', 'anthologize' ) ?></option>
							<option value="by-sa" <?php if ( $cctype == 'by-sa' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Share-Alike', 'anthologize' ) ?></option>
							<option value="by-nd" <?php if ( $cctype == 'by-nd' ) echo 'selected="selected"' ?>><?php _e( 'Attribution No Derivatives', 'anthologize' ) ?></option>
							<option value="by-nc" <?php if ( $cctype == 'by-nc' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial', 'anthologize' ) ?></option>
							<option value="by-nc-sa" <?php if ( $cctype == 'by-nc-sa' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial Share Alike', 'anthologize' ) ?></option>
							<option value="by-nc-nd" <?php if ( $cctype == 'by-nc-nd' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial No Derivatives', 'anthologize' ) ?></option>
						</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Edition', 'anthologize' ) ?></th>
				<td><input type="text" id="edition" name="edition" value="<?php echo $edition ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Add Author(s)', 'anthologize' ) ?></th>
				<td><textarea id="authors" name="authors"><?php echo $authors ?></textarea></td>
			</tr>
		</table>

		<input type="hidden" id="export-step" name="export-step" value="1" />
		<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

		</form>
	</div>
</div>