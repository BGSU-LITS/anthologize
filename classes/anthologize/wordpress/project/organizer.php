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
