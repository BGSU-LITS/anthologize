<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * The Anthologize class handles a lot of the functionality for the plugin
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize
{
	const VERSION = "0.6.2";

	/**
	 * Autoloads resources for the preservation plugin
	 *
	 * @param   string   $file   The name of the class to load
	 * @return  boolean          Was the file loaded?
	 */
	public static function autoload($file)
	{
		$found = false;

		$path = "classes".DIRECTORY_SEPARATOR.str_replace("_", DIRECTORY_SEPARATOR, strtolower($file)).".php";
		if (is_file(ANTHOLOGIZE.$path))
		{
			include ANTHOLOGIZE.$path;
		}

		return $found;
	}

	/**
	 * Simple page routing
	 */
	public static function router()
	{
		$defaults = array(
			'page' => "anthologize",
			'controller' => "project",
			'action' => "index"
		);

		$route = array_merge($defaults, array_intersect_key($_GET, $defaults));

		// Execute the request
		$class = "Controller_{$route['controller']}";
		$class = new $class;
		$class->set_params(array_diff_assoc($_GET, $route)); // Setting additional route params

		$action = strtolower($_SERVER['REQUEST_METHOD'])."_".$route['action'];

		$class->before();
		$class->$action();
		$class->after();
	}

	/**
	 * Tries to find a file contained within the plugin
	 *
	 * @param   string   $dir    The directory name
	 * @param   string   $file   The file name (without extension)
	 * @param   string   $ext    The file extension
	 * @return  mixed            string filename if found or boolean false if not found
	 */
	public static function find_file($dir, $file, $ext = "php")
	{
		$found = false;

		$path = ANTHOLOGIZE.$dir.DIRECTORY_SEPARATOR.$file.".".$ext;
		if (is_file($path))
		{
			$found = $path;
		}

		return $found;
	}

	/**
	 * A simple view rendering function.
	 *
	 * Taken from the Kohana Framework.
	 *
	 * @param   string  $view    The view file to render
	 * @param   array   $data    Data to include in the view
	 * @return  string           Rendered output
	 */
	public static function render($view, array $data = array())
	{
		// Import the view variables to local namespace
		extract($data, EXTR_SKIP);

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include Anthologize::find_file("views", $view);
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

}
