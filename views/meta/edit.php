        <div class="my_meta_control">

        	<label>Author Name <span>(optional)</span></label>

        	<p>
        		<textarea class="tags-input" name="anthologize_meta[author_name]" rows="3" cols="27"><?php echo $author_name ?></textarea>
        	</p>

        	<?php /* Display content for imported feed, if there is any */ ?>
        	<?php if ( $imported_item_meta ) : ?>
        		<dl>
        		<?php foreach ( $imported_item_meta as $key => $value ) : ?>
        			<?php
        				$the_array = array( 'feed_title', 'link', 'created_date' );
        				if ( !in_array( $key, $the_array ) )
        					continue;

						switch ( $key ) {
							case 'feed_title':
								$dt = __( 'Source feed:', 'anthologize' );
								$dd = '<a href="' . $imported_item_meta['feed_permalink'] . '">' . $value . '</a>';
								break;
							case 'link':
								$dt = __( 'Source URL:', 'anthologize' );
								$dd = '<a href="' . $value . '">' . $value . '</a>';
								break;
							/*case 'authors':
								$dt = __( 'Author:', 'anthologize' );
								$ddv = $value[0];
								$dd = $ddv->name;
								break; todo: fixme */
							case 'created_date':
								$dt = __( 'Date created:', 'anthologize' );
								$dd = $value;
								break;
							default:
								continue;
								break;
						}
        			?>


        			<dt><?php echo $dt ?></dt>
        			<dd><?php echo $dd ?></dd>
        		<?php endforeach; ?>
        		</dl>

        	<?php endif; ?>

		<?php if ( isset( $_GET['return_to_project'] ) ) : ?>
			<input type="hidden" name="return_to_project" value="<?php echo $_GET['return_to_project'] ?>" />
		<?php endif; ?>

		<?php if ( isset( $_GET['new_part'] ) ) : ?>
			<input type="hidden" id="new_part" name="new_part" value="1" />
                	<input type="hidden" id="anth_parent_id" name="parent_id" value="<?php echo $_GET['project_id']; ?>" />
		<?php endif; ?>
		
		<input type="hidden" id="menu_order" name="menu_order" value="<?php echo $post->menu_order; ?>">
		<input class="tags-input" type="hidden" id="anthologize_noncename" name="anthologize_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
        </div>