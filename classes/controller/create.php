<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Creates a new project (needed for the correct highlighting in the admin menu).
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Create extends Controller {
	/**
	 * Creates a new project
	 */
	public function action_get_index()
	{
		$this->content = Anthologize::render("project/form", array(
			'project' => NULL,
			'title' => __( 'Add New Project', 'anthologize' ),
			'action' => get_admin_url() . "admin.php?page=anthologize/create&noheader=true"
		));
	}

	/**
	 * Actually create the post
	 */
	public function action_post_index()
	{
		$project = new Anthologize_Project($_POST);
		$project->save();

		wp_redirect( get_admin_url() . 'admin.php?page=anthologize&project_saved=1' );
	}

}
