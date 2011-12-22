<div class="wrap anthologize">
	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
	<h2><?php _e( 'My Projects', 'anthologize' ) ?> <a href="admin.php?page=anthologize&action=create" class="button add-new-h2"><?php _e( 'Add New', 'anthologize' ) ?></a></h2>

<?php if ($project_saved) : ?>
	<div id="message" class="updated fade">
		<p><?php _e( 'Project Saved', 'anthologize' ) ?></p>
	</div>
<?php endif; ?>

<?php if (have_posts()): ?>
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num" id="group-dir-count">
			</span>

			<span class="page-numbers" id="group-dir-pag">
			</span>

		</div>
	</div>

	<table cellpadding="0" cellspacing="0" class="widefat">

	<thead>
		<tr>
			<th scope="col" class="check-column"></th>
			<th scope="col"><?php _e( 'Project Title', 'anthologize' ) ?></th>
			<th scope="col"><?php _e( 'Created By', 'anthologize' ) ?></th>
			<th scope="col"><?php _e( 'Number of Parts', 'anthologize' ) ?></th>
			<th scope="col"><?php _e( 'Number of Items', 'anthologize' ) ?></th>
			<th scope="col"><?php _e( 'Date Created', 'anthologize' ) ?></th>
			<th scope="col"><?php _e( 'Date Modified', 'anthologize' ) ?></th>
		</tr>
	</thead>
	
	<tbody>
	<?php while ( have_posts() ) : the_post(); ?>

			<tr>
				<th scope="row" class="check-column"></th>

				<th scope="row"  class="post-title">
					<a href="admin.php?page=anthologize&amp;action=manage&amp;project_id=<?php the_ID() ?>" class="row-title"><?php the_title(); ?></a>

					<br />

					<?php
					$controlActions	= array();
					$controlActions[]	= '<a href="admin.php?page=anthologize&action=edit&project_id=' . get_the_ID() .'">' . __('Project Details', 'anthologize') . '</a>';
					$controlActions[]   = '<a href="admin.php?page=anthologize&action=manage&project_id=' . get_the_ID() .'">'.__('Manage Parts', 'anthologize') . '</a>';
					$controlActions[]   = '<a href="admin.php?page=anthologize&action=delete&noheader=true&project_id=' . get_the_ID() .'" class="confirm-delete">'.__('Delete Project', 'anthologize') . '</a>';
					?>

					<?php if (count($controlActions)) : ?>
						<div class="row-actions">
							<?php echo implode(' | ', $controlActions); ?>
						</div>
					<?php endif; ?>


				</th>


				<td scope="row anthologize-created-by">
					<?php the_author(); ?>
				</td>

				<td scope="row anthologize-number-parts">
				<?php $parts = Anthologize_Wordpress::get_project_parts(get_the_ID());  echo (is_array($parts) ? count($parts) : '0'); ?>
				</td>

				<td scope="row anthologize-number-items">
					<?php $items = Anthologize_Wordpress::get_project_items();  echo count($items); ?>
				</td>

				<td scope="row anthologize-date-created">
					<?php global $post; echo date( "F j, Y", strtotime( $post->post_date ) ) ?>
				</td>

				<td scope="row anthologize-date-modified">
					<?php the_modified_date(); ?>
				</td>

				<?php do_action( 'anthologize_project_column_data' ); ?>

			</tr>

		<?php endwhile; ?>

	</tbody>

	</table>

<?php else: ?>
		<p><?php _e( 'You haven\'t created any projects yet.', 'anthologize' ) ?></p>
		<p><a href="admin.php?page=anthologize&action=create"><?php _e( 'Start a new project.', 'anthologize' ) ?></a></p>

<?php endif; ?>


</div>