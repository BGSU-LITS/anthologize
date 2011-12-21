<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Controller to handle all of the actions that deal with the Anthologize projects.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Project extends Controller {
	/**
	 * The main project screen.
	 */
	public function action_get_index()
	{
		$this->do_project_query();

		$this->content = Anthologize::render("project/home", array(
			'project_saved' =>  $this->param('project_saved', FALSE) !== FALSE ? TRUE : FALSE,
		));
	}

	/**
	 * Creates a new project
	 */
	public function action_get_create()
	{
		$this->content = Anthologize::render("project/form", array(
			'project' => NULL,
			'title' => __( 'Add New Project', 'anthologize' ),
			'action' => get_admin_url() . "admin.php?page=anthologize&action=create&noheader=true"
		));
	}

	/**
	 * Actually create the post
	 */
	public function action_post_create()
	{
		$project = new Anthologize_Project($_POST);
		$project->save();

		wp_redirect( get_admin_url() . 'admin.php?page=anthologize&project_saved=1' );
	}

	/**
	 * Pulls up a project to be edited.
	 */
	public function action_get_edit()
	{
		$id = $this->param('project_id', false);

		if ($id === false)
		{
			include Anthologize::find_file("views", "notice/no_project_id");
			return;
		}

		$this->content = Anthologize::render("project/form", array(
			'project' => get_post($id),
			'meta' => get_post_meta($id, 'anthologize_meta', true),
			'title' => __( 'Edit Project', 'anthologize' ),
			'action' => get_admin_url() . "admin.php?page=anthologize&action=edit&noheader=true&project_id=".$id
		));
	}

	/**
	 * Posted the edit project form.
	 */
	public function action_post_edit()
	{
		$id = $this->param('project_id', false);

		if ($id === false)
		{
			include Anthologize::find_file("views", "notice/no_project_id");
			return;
		}

		$project = Anthologize_Project::get($id);
		$project->data($_POST);
		$project->save();

		Anthologize::redirect(get_admin_url().'admin.php?page=anthologize&project_saved=1');
	}

	/**
	 * Deletes a project
	 */
	public function action_get_delete()
	{
		$id = $this->param("project_id", false);

		if ($id === false)
		{
			include Anthologize::find_file("views", 'notice/no_project_id');
		}
		else
		{
			wp_delete_post($id);
		}

		Anthologize::redirect(get_admin_url().'admin.php?page=anthologize');
	}

/* Taken from Admin class

		if ( isset( $_GET['project_id'] ) )
			$project = get_post( $_GET['project_id'] );

		if ( isset( $_GET['action'] ) ) {
			if ( $_GET['action'] == 'delete' && $project ) {
				wp_delete_post($project->ID);
			}

			if ( $_GET['action'] == 'edit' && $project ) {
				
			}
		}

		if (
			!isset( $_GET['action'] ) ||
			$_GET['action'] == 'list-projects' ||
			( $_GET['action'] == 'edit' && !$project ) ||
			( $_GET['action'] == 'delete')

		) {
*/

	/**
	 * Pulls up the projects that the logged-in user is allowed to edit
	 *
	 * @package Anthologize
	 * @since 0.3
	 * @return   array   List of posts
	 */
	protected function do_project_query() {
		global $current_user;

		// Set up the default arguments
		$args = array(
			'post_type' => 'anth_project'
		);

		// Anyone less than an Editor should only see their own posts
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			$args['author'] = $current_user->ID;
		}

		// Do that thang
		return query_posts( $args );
	}
}
