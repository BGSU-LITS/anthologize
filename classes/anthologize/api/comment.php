<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Comment Object.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_API_Comment extends Anthologize_API_Base
{
	/**
	 * @var string  The comment content
	 */
	protected $content = null;

	/**
	 * The comment author
	 *
	 * @return string
	 */
	public function author()
	{
		return $this->get('comment_author', "");
	}

	/**
	 * Gets the posted date of the comment.
	 *
	 * @param  string $format The date format
	 * @return string
	 */
	public function posted_date($format = 'F jS, Y \a\t g:ia')
	{
		return date($format, strtotime($this->get("comment_date", "")));
	}

	/**
	 * Gets the content
	 *
	 * @return string
	 */
	public function content()
	{
		if ($this->content === null)
		{
			$this->content = apply_filters('the_content', $this->get('comment_content', ""));
		}

		return $this->content;
	}

}
