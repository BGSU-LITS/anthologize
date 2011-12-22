<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Project Organization for Wordpress.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Wordpress_Project_Organizer {

	var $project_id;

	/**
	 * The project organizer. Git 'er done
	 */
	function anthologize_project_organizer ( $project_id ) {
		$this->project_id = $project_id;

		$project = get_post( $project_id );
		
		if ( !empty( $project->post_title ) )
			$this->project_name = $project->post_title;

	}


	function filter_dropdown() {

		$cterm = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;
		
		$cfilter = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;
		
		$cstartdate = ( isset( $_COOKIE['anth-startdate'] ) ) ? $_COOKIE['anth-startdate'] : false;
				
		$cenddate = ( isset( $_COOKIE['anth-enddate'] ) ) ? $_COOKIE['anth-enddate'] : false;		
	
		switch ( $cfilter ) {
			case 'tag' :
				$terms = get_tags();
				$nulltext = __( 'All tags', 'anthologize' );
				break;
			case 'category' :
				$terms = get_categories();
				$nulltext = __( 'All categories', 'anthologize' );
				break;
			case 'post_type' :
				$types = $this->available_post_types();
				$terms = array();
				foreach ( $types as $type_id => $type_label ) {
					$type_object = null;
					$type_object->term_id = $type_id;
					$type_object->name = $type_label;
					$terms[] = $type_object;
				}
				$nulltext = __( 'All post types', 'anthologize' );
				break;
			default :
				$terms = Array();
				$nulltext = ' - ';
				break;
		}

		?>
			
			
			
		<?php
	}

	function filter_date(){
		?>
		

		<?php
	}
	
	

	function add_item_to_part( $item_id, $part_id ) {
		global $wpdb, $current_user;

		if ( !(int)$last_item = get_post_meta( $part_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;
		$the_item = get_post( $item_id );
		$part = get_post( $part_id );

		$args = array(
		  'menu_order' => $last_item,
		  'comment_status' => $the_item->comment_status,
		  'ping_status' => $the_item->ping_status,
		  'pinged' => $the_item->pinged,
		  'post_author' => $current_user->ID,
		  'post_content' => $the_item->post_content,
		  'post_date' => $the_item->post_date,
		  'post_date_gmt' => $the_item->post_date_gmt,
		  'post_excerpt' => $the_item->post_excerpt,
		  'post_parent' => $part_id,
		  'post_password' => $the_item->post_password,
		  'post_status' => $part->post_status, // post_status is set to the post_status of the parent part
		  'post_title' => $the_item->post_title,
		  'post_type' => 'anth_library_item',
		  'to_ping' => $the_item->to_ping, // todo: tags and categories
		);

        // WordPress will strip these slashes off in wp_insert_post
        $args = add_magic_quotes($args);

		if ( !$imported_item_id = wp_insert_post( $args ) )
			return false;
		
		// Update the parent project's Date Modified field to right now
		$this->update_project_modified_date();

		// Author data
		$user = get_userdata( $the_item->post_author );

		if ( !$author_name = get_post_meta( $item_id, 'author_name', true ) )
			$author_name = $user->display_name;
		$author_name_array = array( $author_name );

		$anthologize_meta = apply_filters( 'anth_add_item_postmeta', array(
			'author_name' => $author_name,
			'author_name_array' => $author_name_array,
			'author_id' => $the_item->post_author,
			'original_post_id' => $item_id
		) );
		
		update_post_meta( $imported_item_id, 'anthologize_meta', $anthologize_meta );
		
		update_post_meta( $imported_item_id, 'author_name', $author_name ); // Deprecated - please use anthologize_meta
		update_post_meta( $imported_item_id, 'author_name_array', $author_name_array ); // Deprecated - please use anthologize_meta

		return $imported_item_id;
	}
	
	function update_project_modified_date() {
		$project_post = get_post( $this->project_id );
		$project_args = array(
			'ID' => $this->project_id,
            'post_modified' => date( "Y-m-d G:H:i" ),
            'post_modified_gmt' => gmdate( "Y-m-d G:H:i" )
		);
		wp_update_post( $project_args );
	}

	function add_new_part( $part_name ) {
		if ( !(int)$last_item = get_post_meta( $this->project_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;

		$project = get_post( $this->project_id );

		$args = array(
		  'post_title' => $part_name,
		  'post_type' => 'anth_part',
		  'post_status' => $project->post_status,
		  'post_parent' => $this->project_id
		);

		if ( !$part_id = wp_insert_post( $args ) )
			return false;

		// Store the menu order of the last item to enable easy moving later on
		update_post_meta( $this->project, 'last_item', $last_item );

		$this->update_project_modified_date();

		return true;
	}

	
	}

	function get_posts_as_option_list( $part_id ) {
		global $wpdb;

		$items = get_post_meta( $part_id, 'items', true );

		$item_query = new WP_Query( 'post_type=items&post_parent=' . $part_id );

		$sql = "SELECT id, post_title FROM wp_posts WHERE post_type = 'page' OR post_type = 'post' OR post_type = 'anth_imported_item'";
		$ids = $wpdb->get_results($sql);

		$counter = 0;
		foreach( $ids as $id ) {
			if ( in_array( $id->id, $items ) || array_key_exists( $id->id, $items ) ) // Todo: adjust so that it references parent stuff
				continue;

			echo '<option value="' . $id->id . '">' . $id->post_title . '</option>';
			$counter++;
		}

		if ( !$counter )
			echo '<option disabled="disabled">Sorry, no content to add</option>';

	}


	function get_part_items( $part_id ) {

		$append_parent = !empty( $_GET['append_parent'] ) ? $_GET['append_parent'] : false;

		$items = get_post_meta( $part_id, 'items', true );

		//echo "<pre>";
		//print_r($items); die();
		//if ( empty( $items ) )
		//	return;

		$args = array(
			'post_parent' => $part_id,
			'post_type' => 'anth_library_item',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);

		$items_query = new WP_Query( $args );

		if ( $items_query->have_posts() ) {

			while ( $items_query->have_posts() ) : $items_query->the_post();

				$this->display_item( $append_parent );

			endwhile;

		}
	}

	function move_up( $id ) {
		global $wpdb;

		$post = get_post( $id );
		$my_menu_order = $post->menu_order;

		$little_brother = 0;
		$minus = 0;

		while ( !$big_brother ) {
			$minus++;

			// Find the big brother
			$big_brother_q = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND menu_order = %d LIMIT 1", $post->post_parent, $my_menu_order-$minus );

			$bb = $wpdb->get_results( $big_brother_q, ARRAY_N );
			$big_brother = $bb[0][0];
		}

		// Downgrade the big brother
		$big_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order, $big_brother ) );

		// Upgrade self
		$little_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order-$minus, $id ) );

		return true;
	}

	function move_down( $id ) {
		global $wpdb;

		$post = get_post( $id );
		$my_menu_order = $post->menu_order;

		$little_brother = 0;
		$plus = 0;

		while ( !$little_brother ) {
			$plus++;

			// Find the little brother
			$little_brother_q = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND menu_order = %d LIMIT 1", $post->post_parent, $my_menu_order+$plus );

			$lb = $wpdb->get_results( $little_brother_q, ARRAY_N );
			$little_brother = $lb[0][0];
		}

		// Upgrade the little brother
		$little_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order, $little_brother ) );

		// Downgrade self
		$big_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order+$plus, $id ) );

		return true;
	}



	function insert_item( $project_id, $post_id, $new_post, $dest_id, $source_id, $dest_seq, $source_seq ) {
		global $wpdb;
		if ( !isset( $project_id ) || !isset( $post_id ) || !isset( $dest_id ) || !isset( $dest_seq ) )
			return false;

		if ( !$new_post ) {
			if ( !isset( $source_id ) || !isset( $source_seq ) )
				return false;
		}

		if ( true === $new_post ) {
			$add_item_result = $this->add_item_to_part( $post_id, $dest_id );
			if (false === $add_item_result) {
				return false;
			}
			$post_id = $add_item_result;
      // $dest_seq[$post_id] = $dest_seq['new_new_new'];
      // unset($dest_seq['new_new_new']);
		} else {
			$post_params = Array('ID' => $post_id,
				'post_parent' => $dest_id);
			$update_item_result = wp_update_post($post_params);
			if (0 === $update_item_result) {
				return false;
			}
			$post_id = $update_item_result;
			$this->rearrange_items( $source_seq );
		}

        // not really any point in checking for errors at this point
        // Since the insert succeeded
        // We should use more detailed Exceptions eventually
        //
		// All items require the destination siblings to be reordered
/*		if ( !$this->rearrange_items( $dest_seq ) )
    return false;*/
		//$this->rearrange_items( $dest_seq );

		return $post_id;
	}

	function rearrange_items( $seq ) {
        global $wpdb;
		foreach ( $seq as $item_id => $pos ) {
			$q = "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d";
			$post_up_query = $wpdb->query( $wpdb->prepare( $q, $pos, $item_id ) );
		}
		
		$this->update_project_modified_date();

		return true;
	}

	function remove_item( $id ) {
		// Git ridda the post
		if ( !wp_delete_post( $id ) )
			return false;
		
		$this->update_project_modified_date();

		return true;
	}

	function append_children( $append_parent, $append_children ) {

		$parent_post = get_post( $append_parent );
		$pp_content = $parent_post->post_content;

		if ( !$author_name = get_post_meta( $append_parent, 'author_name', true ) )
			$author_name = '';

		if ( !$author_name_array = get_post_meta( $append_parent, 'author_name_array', true ) )
			$author_name_array = array();

		foreach( $append_children as $append_child ) {
			$child_post = get_post( $append_child );

			$cp_title = '<h2 class="anthologize-item-header">' . $child_post->post_title . '</h2>
			';

			$cp_content = $child_post->post_content;

			$pp_content .= $cp_title . $cp_content . '
			';

			if ( $author_name != '' )
				$author_name .= ', ';

			$cp_author_name = get_post_meta( $append_child, 'author_name', true );
			$author_name .= $cp_author_name;
			$author_name_array[] = $cp_author_name;

			wp_delete_post( $append_child );
		}

		$args = array(
			'ID' => $append_parent,
			'post_content' => $pp_content,
		);

		if ( !wp_update_post( $args ) )
			return false;

		update_post_meta( $append_parent, 'author_name', $author_name );
		update_post_meta( $append_parent, 'author_name_array', $author_name_array );

		$this->update_project_modified_date();

		return true;
	}

	function display_item( $append_parent ) {
		global $post;
		
		/**
		 * Pull up some comment data to be used in the Comments (x/y) area.
		 * Comments themselves are fetched with AJAX as needed.
		 */
		
		// First, the original post
		$anth_meta = get_post_meta( get_the_ID(), 'anthologize_meta', true );
		
		$original_comment_count = 0;
		if ( !empty( $anth_meta['original_post_id'] ) ) {
			$original_post = get_post( $anth_meta['original_post_id'] );
			$original_comment_count = $original_post->comment_count;
		}
		
		// Then, see how many comments are being brought along to the export
		$included_comment_count = 0;
		if ( !empty( $anth_meta['included_comments'] ) ) {
			$included_comment_count = count( $anth_meta['included_comments'] );
		}

	?>

		<li id="item-<?php the_ID() ?>" class="item">

			<?php if ( $append_parent ) : ?>
				<input type="checkbox" name="append_children[]" value="<?php the_ID() ?>" <?php if ( $append_parent == $post->ID ) echo 'checked="checked" disabled=disabled'; ?>/> <?php echo $post->ID . " " . $append_parent ?>
			<?php endif; ?>

			<noscript>
				<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_up=<?php the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_down=<?php the_ID() ?>">&darr;</a>
			</noscript>

			<h3 class="part-item">
				<span class="part-title"><?php the_title() ?></span>
				<div class="part-item-buttons">
					<a href="post.php?post=<?php the_ID() ?>&action=edit&return_to_project=<?php echo $this->project_id ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |

					<?php /* Comments are being pushed to 0.7 */ ?>
					<?php /*
					<a href="#comments" class="comments toggle"><?php printf( __( 'Comments (<span class="included-comment-count">%1$d</span>/%2$d)', 'anthologize' ), $included_comment_count, $original_comment_count ) ?></a><span class="comments-sep toggle-sep"> |</span>
					*/ ?>

					<a href="#append" class="append toggle"><?php _e( 'Append', 'anthologize' ) ?></a><span class="append-sep toggle-sep"> |</span>
					
					<a target="new" href="<?php echo $this->preview_url( get_the_ID(), 'anth_library_item' ) ?>" class=""><?php _e( 'Preview', 'anthologize' ) ?></a><span class="toggle-sep"> |</span>
					
					<?
					// admin.php?page=anthologize&action=edit&project_id=$this->project_id&append_parent= the_ID()
					?>
					<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&remove=<?php the_ID() ?>" class="confirm"><?php _e( 'Remove', 'anthologize' ) ?></a>
				</div>
			</h3>
		</li>
	<?php
	}
	
}
