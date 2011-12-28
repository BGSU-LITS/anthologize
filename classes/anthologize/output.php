<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Output interface.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
interface Anthologize_Output
{
	/**
	 * Renders the output
	 *
	 * @param Anthologize_API_Project $project  The Anthologize project to render
	 * @param array                   $options  The output options
	 */
	public function render(Anthologize_API_Project $api, array $options);
}
