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
		$route = explode("/", $_GET['page']);

		$controller = isset($route[1]) ? $route[1] : "project";
		$action = isset($_GET['action']) ? $_GET['action'] : "index";

		// Execute the request
		$class = "Controller_{$controller}";
		$class = new $class;
		unset($_GET['page'], $_GET['action']); // Pull of the page and action params...
		$class->set_params($_GET); // Setting additional route params

		$action = "action_".strtolower($_SERVER['REQUEST_METHOD'])."_".$action;

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

	/**
	 * Redirects to another page and exits.
	 *
	 * @param   string   $url    The url to redirect to
	 * @param   int      $code   The http code
	 */
	public static function redirect($url, $code = 302)
	{
		header("Location: {$url}", true, $code);
		exit();
	}

}
