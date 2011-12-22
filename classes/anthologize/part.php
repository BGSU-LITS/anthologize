<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * A Part in the Anthologize plugin.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Part
{
	/**
	 * Gets all of the posts for a given part.
	 *
	 * @param   int   $part_id   The part id
	 * @return  array            The post items 
	 */
	public static function get_part_items($part_id)
	{
		$append_parent = !empty( $_GET['append_parent'] ) ? $_GET['append_parent'] : false;

		$items = get_post_meta( $part_id, 'items', true );

		$args = array(
			'post_parent' => $part_id,
			'post_type' => 'anth_library_item',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);

		return new WP_Query($args);
	}

	/**
	 * Adds an item to a part
	 *
	 * @global type $wpdb
	 * @global type $current_user
	 * @param type $item_id
	 * @param type $part_id
	 * @return  int          The inserted item's id
	 */
	public static function add_item_to_part( $item_id, $part_id ) {
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
		// This is done in the Anthologize_Project::rearrange_items function
		//$this->update_project_modified_date();

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

}
