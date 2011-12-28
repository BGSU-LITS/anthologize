<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Project API Object.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_API_Project extends Anthologize_API_Content
{
	/**
	 * @var  array      All of the projects "parts" (Anthologize_API_Part objects)
	 */
	protected $parts = null;

	/**
	 * Gets the parts that are associated with this project.
	 *
	 * @return  array (of Anthologize_API_Part's)
	 */
	public function parts()
	{
		// Only fetch the parts once, using lazy loading
		if ($this->parts === null)
		{
			$query = new WP_Query;
			$parts = $query->query(array(
				'post_parent' => $this->ID,
				'post_type' => 'anth_part',
				'orderby' => 'menu_order',
				'order' => "ASC",
			));

			foreach ($parts as $row)
			{
				$this->parts[] = new Anthologize_API_Part((array) $row);
			}
		}

		return $this->parts;
	}

	/**
	 * The person who is exporting the project
	 *
	 * @return string
	 */
	public function anthologizer()
	{
		$anthologizer = wp_get_current_user();
		return $anthologizer->display_name;
	}

}
