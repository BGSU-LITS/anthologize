<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Base API Object.
 * 
 * The Project, Part and Posts API objects will use this class.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
abstract class Anthologize_API_Base implements ArrayAccess
{
	/**
	 * @var   array   The data for this object
	 */
	protected $data = array();

	/**
	 * Creates a new api object.
	 *
	 * @param array $data  The project data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Returns all of the data in the object
	 *
	 * @return   array
	 */
	public function data()
	{
		return $this->data;
	}

	/**
	 * Gets the value of an instance variable.
	 *
	 * @param   string   $name     The property name to fetch
	 * @param   mixed    $default  The default value if the key isn't found
	 * @return  mixed
	 */
	public function get($name, $default = null)
	{
		return $this->offsetExists($name) ? $this->offsetGet($name) : $default;
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   type   $name   The name of the property to get
	 * @return  mixed          The value of the property, or null if not found 
	 */
	public function __get($name)
	{
		return $this->get($name, null);
	}

	/**
	 * Whether a offset exists
	 *
	 * @link    http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param   mixed   $offset   An offset to check for.
	 * @return  boolean           Returns true on success or false on failure.
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link    http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param   mixed   $offset   The offset to retrieve.
	 * @return  mixed             Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * Offset to set
	 *
	 * @link    http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param   mixed   $offset    The offset to assign the value to.
	 * @param   mixed   $value     The value to set.
	 * @return  void 
	 */
	public function offsetSet($offset, $value)
	{
		$this->set(array($offset =>  $value));
	}

	/**
	 * Offset to unset
	 * 
	 * @link    http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param   mixed   $offset   The offset to unset.
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

}
