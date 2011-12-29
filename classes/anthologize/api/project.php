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
	 * @var  string    The copyright text
	 */
	protected $copyright = null;

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

	/**
	 * The project authors
	 *
	 * @return string
	 */
	public function author()
	{
		return $this->meta('authors', "");
	}

	/**
	 * Get the copyright info
	 */
	public function copyright()
	{
		if ($this->copyright === null)
		{
			$type = $this->meta('ctype');

			if ($type === "c")
			{
				$this->copyright = __("Copyright", 'anthologize')." - ".$this->meta('cname', "");
			}
			else
			{
				$this->copyright = __("Creative Commons", 'anthologize')." - ".strtoupper($this->meta('cctype', ""));
			}
		}

		return $this->copyright;
	}

	/**
	 * Gets the copyright year
	 *
	 * @return int
	 */
	public function year()
	{
		return (int) $this->meta('cyear');
	}

	/**
	 * The dedication
	 *
	 * @param  boolean  $run   Run the content throught the Wordpress content filter?
	 * @return string          The dedication
	 */
	public function dedication($run = false)
	{
		$str = $this->meta('dedication', "");
		return $run ? apply_filters('the_content', $str) : $str;
	}

	/**
	 * The acknowledgements
	 *
	 * @param  boolean  $run   Run the content throught the Wordpress content filter?
	 * @return string          The acknowledgements
	 */
	public function acknowledgements($run = false)
	{
		$str = $this->meta('acknowledgements', "");
		return $run ? apply_filters('the_content', $str) : $str;
	}

	/**
	 * Gets the filename.
	 *
	 * @return  string   The filename to save
	 */
	public function file_name()
	{
		return str_replace(" ", '_', $this->title());
	}

}
