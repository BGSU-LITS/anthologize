<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Project exportation.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Export extends Controller
{
	/**
	 * The home screen
	 */
	public function action_get_index()
	{
		$project_id = $this->param('project_id', false);
		
		$data = array(
			'projects' => $this->get_projects(),
			'project_id' => $project_id,
			'action' => "admin.php?page=anthologize/export&noheader=true",
		);

		$this->content = Anthologize::render("export/home", array_merge($data, self::get_metadata($project_id)));
	}

	/**
	 * Posting to the home screen
	 */
	public function action_post_index()
	{
		$this->update($_POST, 2);
	}

	/**
	 * Step 2
	 *
	 * Set the project title, dedication, acknolwedgements and output format
	 */
	public function action_get_step2()
	{
		global $anthologize_formats;

		$id = $this->param('project_id');
		$meta = self::get_metadata($id);

		$this->content = Anthologize::render('export/step2', array(
			'project_id' => $id,
			'project' => get_post($id),
			'action' => "admin.php?page=anthologize/export&action=step2&noheader=true",
			'dedication' => $meta['dedication'],
			'acknowledgements' => $meta['acknowledgements'],
			'filetype' => $meta['filetype'],
			'formats' => $anthologize_formats,
		));

		do_action( 'anthologize_export_format_list' );
	}

	/**
	 * Saves the step 2 panel
	 */
	public function action_post_step2()
	{
		$this->update($_POST, 3);
	}

	/**
	 * Step 3
	 *
	 * Sets the final options before outputting the results
	 */
	public function action_get_step3()
	{
		global $anthologize_formats;

		$id = $this->param('project_id');
		$meta = self::get_metadata($id);

		$format = $anthologize_formats[$meta['filetype']];
		$label = $format['label'];

		unset($format['label'], $format['loader-path']);

		$this->content = Anthologize::render('export/step3', array(
			'project_id' => $id,
			'project' => get_post($id),
			'action' => "admin.php?page=anthologize/export&action=step3&noheader=true",
			'format' => $format,
			'format_title' => sprintf( __( '%s Publishing Options', 'anthologize' ), $label),
		));
	}

	/**
	 * Here is where all the magic happens!!
	 */
	public function action_post_step3()
	{
		$id = $_POST['project_id'];
		unset($_POST['project_id']);

		$api = new Anthologize_API($id, $_POST);
		$this->content = $api->render();
	}

	/**
	 * Gets the project metadata
	 *
	 * @param  int  $id   The project id
	 * @return array 
	 */
	public static function get_metadata($id)
	{
		$meta = ($id === false) ? array() : get_post_meta($id, 'anthologize_meta', true );

		$defaults = array(
			'cyear' => date("Y"),
			'cname' => isset($meta['author_name']) ? isset($meta['author_name']) : "",
			'ctype' => "cc",
			'cctype' => "by",
			'edition' => "",
			'authors' => isset($meta['author_name']) ? $meta['author_name'] : "",
			'dedication' => "",
			'acknowledgements' => "",
			'filetype' => 'tei',
			'do-shortcodes' => 1
		);

		return array_merge($defaults, $meta);
	}

	/**
	 * Updates the metadata for a post.
	 *
	 * @param array  $data        Metadata data
	 * @param int    $step        The step to send the user to (If null, then no redirect)
	 */
	protected function update($data, $step = null)
	{
		$project_id = $_POST['project_id'];
		unset($data['project_id']);

		$meta = array_merge(self::get_metadata($project_id), $data);
		update_post_meta( $project_id, 'anthologize_meta', $meta );

		if ($step !== null)
		{
			Anthologize::redirect("admin.php?page=anthologize/export&action=step{$step}&project_id={$project_id}");
		}
	}

	/**
	 * Gets a list of Anthologize projects
	 *
	 * @return  array
	 */
	protected function get_projects()
	{
		$projects = array();

		query_posts( 'post_type=anth_project&orderby=title&order=ASC' );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$projects[get_the_ID()] = get_the_title();
			}
		}

		return $projects;
	}
	
}
