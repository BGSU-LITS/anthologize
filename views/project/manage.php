<div class="wrap anthologize" id="project-<?php echo $_GET['project_id'] ?>">

	<div id="blockUISpinner">
		<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
		<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
	</div>

	<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>

	<h2><?php echo $project->post_title ?>

	<div id="project-actions">
		<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $project->ID ?>"><?php _e( 'Project Details', 'anthologize' ) ?></a> |
		<a target="_blank" href="admin.php?page=anthologize&action=preview&post_type=anth_project&project_id=<?php echo $project->ID; ?>"><?php _e( 'Preview Project', 'anthologize' ) ?></a> |
		<a href="admin.php?page=anthologize&action=delete&noheader=true&project_id=<?php echo $project->ID ?>" class="confirm-delete"><?php _e( 'Delete Project', 'anthologize' ) ?></a>
	</div>

	</h2>

	<?php if ( isset( $_GET['edited'] ) ) : ?>
		<div id="message" class="updated below-h2">
			<p><?php _e( 'Item edited', 'anthologize' ) ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['append_parent'] ) && !isset( $_GET['append_children'] ) ) : ?>
		<div id="message" class="updated below-h2">
			<p><?php _e( 'Select the items you would like to append and click Go.', 'anthologize' ) ?></p>
		</div>
	<?php endif; ?>

	<div id="project-organizer-frame">
		<div id="project-organizer-left-column" class="metabox-holder">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

			<div id="add-custom-links" class="postbox">
			<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div><h3 class="hndle"><span><?php _e( 'Items', 'anthologize' ) ?></span></h3>
			<div class="inside">
				<div class="customlinkdiv" id="customlinkdiv">

						<p id="menu-item-name-wrap">
							<span><?php _e( 'Filter by', 'anthologize' ) ?></span>
							<select name="sortby" id="sortby-dropdown">
								<option value="" selected="selected"><?php _e( 'All posts', 'anthologize' ) ?></option>
								<?php foreach( $filters as $filter => $name ) : ?>
									<option value="<?php echo $filter ?>" <?php if ( $filter == $cfilter ) : ?>selected="selected"<?php endif; ?>><?php echo $name ?></option>
								<?php endforeach; ?>
							</select>
						</p>

						<p id="termfilter">
							<select name="filter" id="filter">
							<option value=""><?php echo $nulltext; ?></option>
							<?php foreach( $terms as $term ) : ?>
								<?php $term_value = ( $cfilter == 'tag' ) ? $term->slug : $term->term_id; ?>
								<option value="<?php echo $term_value ?>" <?php if ( $cterm == $term_value ) : ?>selected="selected"<?php endif; ?>><?php echo $term->name ?></option>
							<?php endforeach; ?>
						</select>
						</p>
						<p id="datefilter">
							<label for="startdate"><?php _e("Start", 'anthologize'); ?></label> <input name="starddate" id="startdate" type="text"/>
				
							<br />
							<label for="enddate"><?php _e("End", 'anthologize'); ?></label> <input name="enddate" id="enddate" type="text" />
							<br />
							<input type="button" id="launch_date_filter" value="Filter" /> 
						</p>

						<h3 class="part-header"><?php _e( 'Posts', 'anthologize' ) ?></h3>
						<div id="posts-scrollbox">
						<?php if ($big_posts->have_posts()): ?>
							<ul id="sidebar-posts">
								<?php while ( $big_posts->have_posts() ) : $big_posts->the_post(); ?>
									<li class="item"><span class="fromNewId">new-<?php the_ID() ?></span><h3 class="part-item"><?php the_title() ?></h3></li>
								<?php endwhile; ?>
							</ul>
						<?php endif; ?>
						</div>

				</div><!-- /.customlinkdiv -->
				</div>
			</div> <!-- /.postbox -->

			</div> <!-- .meta-box-sortables -->
		</div> <!-- .project-organizer-left-column -->

		<div class="metabox-holder" id="project-organizer-right-column">

			<div class="postbox" id="anthologize-parts-box">

			<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div><h3 class="hndle"><span><?php _e( 'Parts', 'anthologize' ) ?></span><div class="part-item-buttons button" id="new-part"><a href="post-new.php?post_type=anth_part&project_id=<?php echo $project->ID; ?>&new_part=1"><?php _e( 'New Part', 'anthologize' ) ?></a></div></h3>

			<div id="partlist">

			<ul class="project-parts">
			<?php
				$parts = $project->get_existing_parts();
				if (have_posts()):
					while(have_posts()):
					the_post();
			?>
				<li class="part" id="part-<?php echo get_the_ID(); ?>">
					<h3 class="part-header"><noscript><a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $project->ID ?>&move_up=<?php echo get_the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $project->ID ?>&move_down=<?php echo get_the_ID() ?>">&darr;</a> </noscript>
					<span class="part-title-header"><?php echo get_the_title(); ?></span>

					<div class="part-buttons">
						<a href="post.php?post=<?php echo get_the_ID() ?>&action=edit&return_to_project=<?php echo $project->ID ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |

						<a target="_blank" href="admin.php?page=anthologize&action=preview&post_id=<?php echo get_the_ID(); ?>&post_type=anth_part" class=""><?php _e( 'Preview', 'anthologize' ) ?></a> |

						<a href="admin.php?page=anthologize&action=remove_post&project_id=<?php echo $project->ID ?>&remove=<?php echo get_the_ID() ?>" class="remove"><?php _e( 'Remove', 'anthologize' ) ?></a> |
						<a href="#collapse" class="collapsepart"> - </a> 
					</div>

					</h3>

					<div class="part-items">
						<ul>
					<?php
						$items = Anthologize_Part::get_part_items(get_the_ID());
						if ($items->have_posts()):
							while($items->have_posts()):
								$items->the_post();
					?>
							<li id="item-<?php the_ID() ?>" class="item">

							<?php
								if ( isset($_GET['append_parent']) ) :
									$append_parent = $_GET['append_parent'];
							?>
								<input type="checkbox" name="append_children[]" value="<?php the_ID() ?>" <?php if ( $append_parent == get_the_ID() ) echo 'checked="checked" disabled=disabled'; ?>/> <?php echo get_the_ID() . " " . $append_parent ?>
							<?php endif; ?>

							<noscript>
								<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $project->ID ?>&move_up=<?php the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $project->ID ?>&move_down=<?php the_ID() ?>">&darr;</a>
							</noscript>

							<h3 class="part-item">
								<span class="part-title"><?php the_title() ?></span>
								<div class="part-item-buttons">
									<a href="post.php?post=<?php the_ID() ?>&action=edit&return_to_project=<?php echo $project->ID ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |

									<?php /* Comments are being pushed to 0.7 */ ?>
									<?php /*
									<a href="#comments" class="comments toggle"><?php printf( __( 'Comments (<span class="included-comment-count">%1$d</span>/%2$d)', 'anthologize' ), $included_comment_count, $original_comment_count ) ?></a><span class="comments-sep toggle-sep"> |</span>
									*/ ?>

									<a href="#append" class="append toggle"><?php _e( 'Append', 'anthologize' ) ?></a><span class="append-sep toggle-sep"> |</span>

									<a target="new" href="admin.php?page=anthologize&action=preview&post_id=<?php echo get_the_ID(); ?>&post_type=anth_library_item" class=""><?php _e( 'Preview', 'anthologize' ) ?></a><span class="toggle-sep"> |</span>

									<?php
									// admin.php?page=anthologize&action=edit&project_id=$this->project_id&append_parent= the_ID()
									?>
									<a href="admin.php?page=anthologize&action=remove_post&project_id=<?php echo $project->ID ?>&remove=<?php the_ID() ?>" class="confirm remove"><?php _e( 'Remove', 'anthologize' ) ?></a>
								</div>
							</h3>
						</li>
					<?php
							endwhile;
						endif; ?>
						</ul>
					</div>

					<?php /* Noscript solution. Removed at the moment to avoid db queries. Todo: refactor ?>
						<?php if ( isset( $_GET['append_parent'] ) && !isset( $_GET['append_children'] ) ) : ?>

							<input type="submit" name="append_submit" value="Go" />
							<input type="hidden" name="append_parent" value="<?php echo $_GET['append_parent']  ?>" />

						<?php else : ?>

							<select name="item_id">
								<?php $this->get_posts_as_option_list( $part_id ) ?>
							</select>
							<input type="submit" name="new_item" value="Add Item" />
							<input type="hidden" name="part_id" value="<?php echo $part_id ?>" />

						<?php endif; ?>

					<?php */ ?>
				</li>

			<?php
					endwhile;
				else:
			?>
				<p><?php echo sprintf( __( 'You haven\'t created any parts yet! Click <a href="%1$s">"New Part"</a> to get started.', 'anthologize' ), 'post-new.php?post_type=anth_part&project_id=' . $project->ID . '&new_part=1' ) ?></p>
			<?php endif; ?>
			</ul>

			<noscript>
				<h3><?php _e( 'New Parts', 'anthologize' ) ?></h3>
				<p><?php _e( 'Wanna create a new part? You know you do.', 'anthologize' ) ?></p>
				<form action="" method="post">
					<input type="text" name="new_part_name" />
					<input type="submit" name="new_part" value="New Part" />
				</form>
			</noscript>

			<!--
				<br /><br />
				<p>See the *actual* project at <a href="http://mynameinklingon.org">mynameinklingon.org</a>. You lucky duck.</p>
			-->

			</div>

			</div> <!-- #anthologize-part-box -->

		<div class="button" id="export-project-button"><a href="admin.php?page=anthologize&action=export&project_id=<?php echo $project->ID ?>" id="export-project"><?php _e( 'Export Project', 'anthologize' ) ?></a></div>

		</div> <!-- #project-organizer-right-column -->


	</div> <!-- #project-organizer-frame -->

</div> <!-- .wrap -->