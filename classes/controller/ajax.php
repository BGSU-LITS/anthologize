<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Handles the ajax requests in Wordpress.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Ajax extends Controller
{
	/**
	 * @var   string   The controller action to run
	 */
	protected $action = "";
	
	/**
	 * Ajax is done really funky in wp....
	 */
	public function __construct()
	{
		$this->action = $_POST['action'];
		unset($_POST['action']);
		$this->set_params($_POST);
	}

	/**
	 * We have to setup a run loop in the ajax controller for WP
	 */
	public function run()
	{
		$this->before();
		$this->{$this->action}();
		$this->after();
	}

	/**
	 * Echos out the content (json encoded of course)
	 */
	public function after()
	{
		echo $this->content !== null ?
			json_encode($this->content) :
			"";
	}

}
