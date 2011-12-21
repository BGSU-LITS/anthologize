<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * A Project in the Anthologize plugin.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Project {

	/**
	 * Gets a post.
	 *
	 * @param  int   $id   The project id
	 * @return  Anthologize_Project
	 */
	public static function get($id)
	{
		$project = new Anthologize_Project;
		$project->data((array) get_post($id));
		return $project;
	}

	/**
	 * Changes a projects status.
	 *
	 * @throws   Exception   If the status is not in the allowed list
	 *
	 * @param   int     $project_id   The project id
	 * @param   string  $status       The status to change to
	 */
	public static function change_project_status( $project_id, $status ) {
		$status_types = array('publish', 'draft');

		if ( ! in_array($status_types, $types))
		{
			throw new Exeception("A project may only have the follow status: ".implode(", ", $status_types));
		}

		$args = array(
			'post_status' => $status_types,
			'post_parent' => $project_id,
			'nopaging' => true,
			'post_type' => 'anth_part'
		);

		$parts = get_posts( $args);

		foreach ( $parts as $part ) {
			if ( $part->post_status != $status ) {
				$update_part = array(
					'ID' => $part->ID,
					'post_status' => $status,
				);
				wp_update_post( $update_part );
			}

			$args = array(
				'post_status' => $status_types,
				'post_parent' => $part->ID,
				'nopaging' => true,
				'post_type' => 'anth_library_item'
			);

			$library_items = get_posts( $args );

			foreach( $library_items as $item ) {
				if ( $item->post_status != $status ) {
					$update_item = array(
						'ID' => $item->ID,
						'post_status' => $status,
					);
					wp_update_post( $update_item );
				}
			}
		}
	}
	
	/**
	 * @var   array   Array of post data
	 */
	protected $_data = array();

	/**
	 * Is this a new project?
	 */
	protected $_is_new = false;

	/**
	 * Creates a new Anthologize Project
	 *
	 * @param   array   $data   Any project data
	 */
	public function __construct($data = array())
	{
		$default = array(
			'post_title' => 'Default Title',
			'post_type' => 'anth_project',
			'post_status' => '',
			'post_date' => date( "Y-m-d G:H:i" ),
			'post_date_gmt' => gmdate( "Y-m-d G:H:i" ),
		);

		$this->_data = array_merge($default, $data);
		$this->_is_new = ! empty($data);
	}

	/**
	 * Getter/Setter for the data
	 *
	 * @param   array   $data   The data
	 * @return  array
	 */
	public function data(array $data = array())
	{
		if (empty($data))
		{
			return $this->_data;
		}

		$this->_data = array_merge($this->_data, $data);
	}

	/**
	 * Gets a parameter by name.
	 *
	 * @param   string   $name      The param name
	 * @param   mixed    $default   The default value if the param isn't found
	 * @return  mixed               The param value or the default value
	 */
	public function param($name, $default = null)
	{
		return isset($this->_data[$name]) ? $this->_data[$name] : $default;
	}

	/**
	 * Saves the project
	 */
	public function save()
	{
		if ($this->_is_new)
		{
			$this->create();
		}
		else
		{
			$this->update();
		}
	}

	/**
	 * Creates a new project
	 */
	protected function create()
	{
		wp_insert_post($this->_data);
	}

	/**
	 * Updates an existing project.
	 */
	protected function update()
	{
		$this->update_metadata();

		// @todo Is this needed? (When would the status change?)
		/**
		if ( !empty ($_POST['post_status']) && ($the_project->post_status != $_POST['post_status'] ))
			$this->change_project_status( $_POST['project_id'], $_POST['post_status'] );
		 */

		wp_update_post($this->data());
	}

	/**
	 * Updates a posts metadata
	 */
	protected function update_metadata()
	{
		// Prep the metadata
		$meta = get_post_meta($this->param('ID'), 'anthologize_meta', true);
		if ( ! $meta)
		{
			$meta = array();
		}

		foreach ($this->param('anthologize_meta', array()) as $key => $value)
		{
			$meta[$key] = $value;
		}

		if (empty($meta))
		{
			delete_post_meta($this->param('ID'), 'anthologize_meta');
		}
		else
		{
			update_post_meta($this->param('ID'), 'anthologize_meta', $meta);
		}
	}

}
