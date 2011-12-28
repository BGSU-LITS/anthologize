<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize HTML Output Renderer
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Output_HTML implements Anthologize_Output
{
	/**
	 * Gets the output for a html file.
	 *
	 * @param  Anthologize_API_Project $project  The anthologize project
	 * @param  array                   $options  Rendering options
	 * @return string                            The html output
	 */
	public function render(Anthologize_API_Project $project, array $options)
	{
		$output = Anthologize::render("output/html", array(
			'project' => $project,
			'dedication' => $project->meta('dedication', ""),
			'acknowledgements' => $project->meta('acknowledgements', ""),
			'font_size' => $options['font-size'],
			'do_shortcodes' => $options['do-shortcodes'] === "1" ? true : false,
		));

		if (isset($options['download']))
		{
			$file = str_replace(" ", "_", $project->title());

			header("Content-type: text/html");
			header("Content-Disposition: attachment; filename={$file}.html");
			echo $output;
			die;
		}

		return $output;
	}
}
