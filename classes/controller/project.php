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
	 * Manage a project (add parts and arrange them)
	 */
	public function action_get_manage()
	{
		$id = $this->param('project_id', false);

		if ($id === false)
		{
			include Anthologize::find_file("views", "notice/no_project_id");
			return;
		}

		$terms = $this->get_terms();

		$this->content = Anthologize::render("project/manage", array(
			'project' => Anthologize_Project::get($id),
			'filters' => array(
				'tag' => __( 'Tag', 'anthologize' ),
				'category' => __( 'Category', 'anthologize' ),
				'date' => __( 'Date Range', 'anthologize' ),
				'post_type' => __( 'Post Type', 'anthologize' )
			),
			'cfilter' => isset( $_COOKIE['anth-filter'] ) ? $_COOKIE['anth-filter'] : '',
			'terms' => $terms['terms'],
			'nulltext' => $terms['nulltext'],
			'cterm' => isset( $_COOKIE['anth-term'] ) ? $_COOKIE['anth-term'] : false,
			'big_posts' => $this->get_sidebar_posts()
		));
	}

	/**
	 * Handles when the mange form has been posted
	 *
	 * @todo  finish this (but only when scripts are turned off...)
	 */
	public function action_post_manage()
	{
		/**
		 * 
		 * if ( isset( $_POST['new_item'] ) )
			$this->add_item_to_part( $_POST['item_id'], $_POST['part_id'] );

		if ( isset( $_POST['new_part'] ) )
			$this->add_new_part( $_POST['new_part_name'] );

		if ( isset( $_GET['move_up'] ) )
			$this->move_up( $_GET['move_up'] );

		if ( isset( $_GET['move_down'] ) )
			$this->move_down( $_GET['move_down'] );

		if ( isset( $_GET['remove'] ) )
			$this->remove_item( $_GET['remove'] );

		if ( isset( $_POST['append_children'] ) ) {
			$this->append_children( $_POST['append_parent'], $_POST['append_children'] );
		}
		 */
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

	/**
	 * Project exporting
	 */
	public function action_get_export()
	{
		$this->content = "Exportation!";
	}

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

	/**
	 * Get the terms to sort on.
	 *
	 * @return   array    Array with terms and the null text.
	 */
	protected function get_terms()
	{
		$cterm = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;

		$cfilter = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;

		$cstartdate = ( isset( $_COOKIE['anth-startdate'] ) ) ? $_COOKIE['anth-startdate'] : false;

		$cenddate = ( isset( $_COOKIE['anth-enddate'] ) ) ? $_COOKIE['anth-enddate'] : false;

		switch ( $cfilter ) {
			case 'tag' :
				$terms = get_tags();
				$nulltext = __( 'All tags', 'anthologize' );
				break;
			case 'category' :
				$terms = get_categories();
				$nulltext = __( 'All categories', 'anthologize' );
				break;
			case 'post_type' :
				$types = $this->available_post_types();
				$terms = array();
				foreach ( $types as $type_id => $type_label ) {
					$type_object = null;
					$type_object->term_id = $type_id;
					$type_object->name = $type_label;
					$terms[] = $type_object;
				}
				$nulltext = __( 'All post types', 'anthologize' );
				break;
			default :
				$terms = Array();
				$nulltext = ' - ';
				break;
		}

		return array('terms' => $terms, 'nulltext' => $nulltext);
	}

	/**
	 * Provide a list of post types available as a filter on the project organizer screen.
	 *
	 * @package Anthologize
	 * @subpackage Project Organizer
	 * @since 0.5
	 *
	 * @return array A list of post type labels, keyed by name
	 */
	protected function available_post_types() {
		$all_post_types = get_post_types( false, false );

		$excluded_post_types = apply_filters( 'anth_excluded_post_types', array(
			'anth_library_item',
			'anth_part',
			'anth_project',
			'attachment',
			'revision',
			'nav_menu_item'
		) );

		$types = array();
		foreach( $all_post_types as $name => $post_type ) {
			if ( !in_array( $name, $excluded_post_types ) )
				$types[$name] = isset( $post_type->labels->name ) ? $post_type->labels->name : $name;
		}

		return apply_filters( 'anth_available_post_types', $types );
	}

	/**
	 * Gets the posts that can be managed
	 *
	 * @global   WPDB  $wpdb
	 */
	function get_sidebar_posts() {
		global $wpdb;

		$args = array(
			'post_type' => array('post', 'page', 'anth_imported_item' ),
			'posts_per_page' => -1,
			'orderby' => 'post_title',
			'order' => 'DESC'
		);

		$cfilter = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;

		if ( $cfilter == 'date' ) {
			$startdate = mysql_real_escape_string($_COOKIE['anth-startdate']);
			$enddate = mysql_real_escape_string($_COOKIE['anth-enddate']);

			$date_range_where = '';
			if (strlen($startdate) > 0){
				$date_range_where = " AND post_date >= '".$startdate."'";
			}
			if (strlen($enddate) > 0){
				$date_range_where .= " AND post_date <= '".$enddate."'";
			}

			$where_func = '$where .= "'.$date_range_where.'"; return $where;';
			$filter_where = create_function('$where', $where_func);
			add_filter('posts_where', $filter_where);
		} else {

			$cterm = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;

			if ( $cterm ) {
				if ( $cfilter ) {
					switch( $cfilter ) {
						case 'tag' :
							$filtertype = 'tag';
							break;
						case 'category' :
							$filtertype = 'cat';
							break;
						case 'post_type' :
							$filtertype = 'post_type';
							break;
					}

					$args[$filtertype] = $cterm;
				}
			}

		}

		$big_posts = new WP_Query( $args );

        if ( $cfilter == 'date' ) {
            remove_filter('posts_where', $filter_where);
        }

		return $big_posts;
	}

}
