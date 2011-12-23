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
	 * Inserts a post into the part
	 *
	 * @global WPDB  $wpdb         The Wordpress Database instance
	 * @param  int   $project_id   The project id to insert into
	 * @param  int   $post_id      The post id to insert into
	 * @param type $new_post
	 * @param type $dest_id
	 * @param type $source_id
	 * @param type $dest_seq
	 * @param type $source_seq
	 * @return  int                The post id
	 */
	public static function insert_item( $project_id, $post_id, $new_post, $dest_id, $source_id, $dest_seq, $source_seq ) {
		global $wpdb;
		if ( !isset( $project_id ) || !isset( $post_id ) || !isset( $dest_id ) || !isset( $dest_seq ) )
			return false;

		if ( !$new_post ) {
			if ( !isset( $source_id ) || !isset( $source_seq ) )
				return false;
		}

		if ( true === $new_post ) {
			$add_item_result = Anthologize_Part::add_item_to_part( $post_id, $dest_id );
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
			self::rearrange_items( $project_id, $source_seq );
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

	/**
	 * Rearranges items in the part
	 *
	 * @global type $wpdb
	 * @param   int   $project_id   The project id to update
	 * @param type $seq
	 * @return  boolean
	 */
	public static function rearrange_items( $project_id, $seq ) {
        global $wpdb;
		foreach ( $seq as $item_id => $pos ) {
			$q = "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d";
			$post_up_query = $wpdb->query( $wpdb->prepare( $q, $pos, $item_id ) );
		}

		self::update_project_modified_date($project_id);

		return true;
	}

	/**
	 * Updates the modified date on a project
	 *
	 * @param   int   $id     The project id to update
	 */
	public static function update_project_modified_date($id) {
		$project = Anthologize_Project::get($id);
		$project_args = array(
			'ID' => $project->ID,
            'post_modified' => date( "Y-m-d G:H:i" ),
            'post_modified_gmt' => gmdate( "Y-m-d G:H:i" )
		);
		wp_update_post( $project_args );
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
	 * Gets the value of a non-existant property
	 *
	 * @param   string   $name   The name of the property to get
	 * @return  mixed            The value OR null
	 */
	public function __get($name)
	{
		return $this->param($name, null);
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
		$post_id =  wp_insert_post($this->_data);
		$this->_data['ID'] = $post_id;

		$this->update_metadata();
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

	/**
	 * Gets the existing parts for a project
	 *
	 * @return   array   An array of existing parts for the project
	 */
	public function get_existing_parts()
	{
		$args = array(
			'post_type' => 'anth_part',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'post_per_page' => -1,
			'showposts' => -1,
			'post_parent' => $this->ID
		);

		return query_posts($args);
	}

}
