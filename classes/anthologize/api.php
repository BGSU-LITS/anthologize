<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize API.
 * 
 * The API is used by all output formats to give access to all of the needed data
 * to the project in a nice and neat fashion.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_API
{
	/**
	 * @var  array    The project metadata
	 */
	protected $_meta;

	/**
	 * @var int   The project id
	 */
	protected $_project_id;

	/**
	 * @var  stdClass   The project object
	 */
	protected $_project;

	/**
	 * @var  array      All of the projects "parts"
	 */
	protected $_parts;

	/**
	 * @var  array     All of the posts that are associated with this project (in some form...)
	 */
	protected $_posts;

	/**
	 * Creates a new API instance
	 *
	 * @param   int   $id   The project id
	 */
	public function __construct($id)
	{
		$this->_project_id = $id;

		$this->_meta = get_post_meta($id, 'anthologize_meta', true );

		// Get the project
		$query = new WP_Query;
		$project = $query->query(array(
			'p' => $id,
			'post_type' => 'anth_project'
		));

		$this->_project = $project[0]; // Only 1 project...

		// Now we need the parts
		$this->_parts = $query->query(array(
			'post_parent' => $id,
			'post_type' => 'anth_part',
		));

		// And finally the posts...
		foreach ($this->_parts as $part)
		{
			$this->_posts[$part->ID] = $query->query(array(
				'post_parent' => $part->ID,
				'post_type' => "anth_library_item"
			));
		}
	}

	/**
	 * Gets the metadata array or a property by name.
	 *
	 * @param  string   $name     The metadata name to get
	 * @param  mixed    $default  THe default value (if value not found)
	 * @return mixed
	 */
	public function meta($name = null, $default = null)
	{
		if ($name === null)
		{
			return $this->_meta;
		}

		return isset($this->_meta[$name]) ? $this->_meta[$name] : $default;
	}

	/**
	 * Gets the posts for this project with the given id.
	 *
	 * @throws  Exception       If the part id is supplied and not found, then an exception is thrown
	 * @param   int   $part_id  The part id to find posts for
	 * @return  array 
	 */
	public function posts($part_id)
	{
		if ( ! isset($this->_posts[$part_id]))
		{
			throw new Exeption("The part id supplied is not included in this project");
		}

		return $this->_posts[$part_id];
	}

	/**
	 * Output renderer
	 *
	 * @return  mixed    The output
	 */
	public function render()
	{
		$class = "Anthologize_Output_".$this->meta('filetype');
		$render = new $class;
		return $render->render($this);
	}

	/**
	 * Gets the value of a non-existant property
	 *
	 * @param   string   $name   The name of the property to get
	 * @return  mixed            The value OR null
	 */
	public function __get($name)
	{
		$under = "_".$name;
		return isset($this->$under) ? $this->$under : null;
	}

}