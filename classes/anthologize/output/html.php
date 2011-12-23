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
	 * @param  Anthologize_API $api  The anthologize project api
	 * @return string                The html output
	 */
	public function render(Anthologize_API $api)
	{
		return Anthologize::render("output/html", array(
			'api' => $api
		));
	}
}
