<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Abstract controller.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
abstract class Controller {
	/**
	 * @var   array   Additional parameters for the controller
	 */
	protected $_params = array();

	/**
	 * Do things before the action runs.
	 */
	public function before(){}

	/**
	 * Do things after the action runs.
	 */
	public function after(){}

	/**
	 * Sets additional parameters for the controller
	 *
	 * @param   array   $data   Array of additional params for the controller
	 */
	public function set_params(array $data)
	{
		$this->_params = $data;
	}

	/**
	 * Gets a controller parameter or the default value if the named param isn't found
	 *
	 * @param   string   $name      The name of the param to get
	 * @param   mixed    $default   The default value if the param isn't found
	 * @return  mixed               The param value or the default value
	 */
	public function get_param($name, $default = null)
	{
		return isset($this->_param[$name]) ? $this->_param[$name] : $default;
	}

}
