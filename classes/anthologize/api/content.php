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
abstract class Anthologize_API_Content extends Anthologize_API_Base
{
	/**
	 * @var   array   The post metadata
	 */
	protected $meta = array();

	/**
	 * Creates a new api object.
	 *
	 * @param array $data  The project data
	 */
	public function __construct(array $data)
	{
		$this->meta = get_post_meta($data['ID'], 'anthologize_meta', true );
		parent::__construct($data);
	}

	/**
	 * The project title
	 *
	 * @return string
	 */
	public function title()
	{
		return $this->get('post_title', "");
	}

	/**
	 * Gets the metadata array or a property by name.
	 *
	 * @param  string   $name     The metadata name to get
	 * @param  mixed    $default  THe default value (if value not found)
	 * @return mixed
	 */
	public function meta($name = null, $default = null)
	{
		if ($name === null)
		{
			return $this->meta;
		}

		return isset($this->meta[$name]) ? $this->meta[$name] : $default;
	}

}
