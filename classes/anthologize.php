<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * The Anthologize class handles a lot of the functionality for the plugin
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @author       Dave Widmer <dwidmer@bgsu.edu>
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

}
