<div class="wrap anthologize">

		<div id="blockUISpinner">
			<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
			<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
		</div>

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
		<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>
<?php anthologize_save_project_meta() ?>

			<?php $project_id = $_POST['project_id']; ?>
			<?php $project = get_post( $project_id ); ?>

			<form action="" method="post">

				<?php _e( 'Title', 'anthologize' ) ?> <input type="text" name="post-title" id="post-title" value="<?php echo $project->post_title ?>" size="100"/>

				<div style="clear: both;"> </div><br />

				<div style="width: 400px; float: left;">
					<p><strong><?php _e( 'Dedication', 'anthologize' ) ?></strong></p>
					<textarea id="dedication" name="dedication" cols=35 rows=15><?php echo $dedication ?></textarea>
				</div>

				<div style="width: 400px; float: left;">
					<p><strong><?php _e( 'Acknowledgements', 'anthologize' ) ?></strong></p>
					<textarea id="acknowledgements" name="acknowledgements" cols=35 rows=15><?php echo $acknowledgements ?></textarea>
				</div>
				
				<div style="clear: both;"></div>
				
				<div id="export-format">
					<h4><?php _e( 'Export Format', 'anthologize' ) ?></h4>
					
					<?php $this->export_format_list() ?>
				</div>
				
				<input type="hidden" name="export-step" value="2" />

				<div style="clear: both;"> </div>

				<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>
</div>