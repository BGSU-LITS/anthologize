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
	 * @var Anthologize_API_Project  The project object
	 */
	protected $project;

	/**
	 * @var array  The output options
	 */
	protected $options;

	/**
	 * Creates a new API instance
	 *
	 * @param   int   $id       The project id
	 * @param   array $options  Output options.
	 */
	public function __construct($id, array $options)
	{
		// Get the project
		$query = new WP_Query;
		$project = $query->query(array(
			'p' => $id,
			'post_type' => 'anth_project'
		));

		$this->project = new Anthologize_API_Project((array) $project[0]);
		$this->options = $options;
	}

	/**
	 * Gets the project.
	 *
	 * @return   Anthologize_API_Project
	 */
	public function project()
	{
		return $this->project;
	}

	/**
	 * Output renderer.
	 *
	 * @param  array  $options  The output options
	 */
	public function render()
	{
		$class = "Anthologize_Output_".$this->options['filetype'];
		$render = new $class;
		return $render->render($this->project, $this->options);
	}

}