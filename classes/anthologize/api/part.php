<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Part API Object.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_API_Part extends Anthologize_API_Content
{
	/**
	 * @var  array     All of the posts that are associated with this part (Anthologize_API_Post objects)
	 */
	protected $posts = null;

	/**
	 * Gets the posts for this part
	 *
	 * @param   int   $part_id  The part id to find posts for
	 * @return  array 
	 */
	public function posts()
	{
		if ($this->posts === null)
		{
			$query = new WP_Query;
			$posts = $query->query(array(
				'post_parent' => $this->ID,
				'post_type' => 'anth_library_item',
				'orderby' => 'menu_order',
				'order' => "ASC",
			));

			foreach ($posts as $row)
			{
				$this->posts[] = new Anthologize_API_Post((array) $row);
			}
		}

		return $this->posts;
	}
}
