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

}
