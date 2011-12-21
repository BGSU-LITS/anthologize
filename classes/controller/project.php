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
	public function get_index()
	{
		$this->do_project_query();

		$this->content = Anthologize::render("project/home", array(
			'project_saved' =>  $this->get_param('project_saved', FALSE) !== FALSE ? TRUE : FALSE,
			'posts' => $this->do_project_query(),
		));
	}

/* Taken from Admin class

		if ( isset( $_GET['project_id'] ) )
			$project = get_post( $_GET['project_id'] );

		if ( isset( $_GET['action'] ) ) {
			if ( $_GET['action'] == 'delete' && $project ) {
				wp_delete_post($project->ID);
			}

			if ( $_GET['action'] == 'edit' && $project ) {
				$this->load_project_organizer( $_GET['project_id'] );
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
