<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Post API Object.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_API_Post extends Anthologize_API_Content
{
	/**
	 * @var  WP_User  The author data
	 */
	protected $author = null;

	/**
	 * @var array  The post tags
	 */
	protected $tags = null;

	/**
	 * @var array  The post categories
	 */
	protected $categories = null;

	/**
	 * @var array  The post comments
	 */
	protected $comments = null;

	/**
	 * Set the author data when createing the object
	 *
	 * @param array $data   Post data
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);

		$author_id = $this->meta('author_id', null);

		if ($author_id !== null)
		{
			$this->author = get_user_by('id', $author_id);
		}
	}

	/**
	 * Gets the post content
	 *
	 * @param  boolean  $do_shortcodes  Run the Wordpress shortcodes
	 * @return string                   The content
	 */
	public function content($do_shortcodes = true)
	{
		$content = $this->get('post_content', "");

		if ( ! $do_shortcodes)
		{
			remove_filter('the_content', 'do_shortcode', 11);
		}

		return apply_filters('the_content', $content);
	}

	/**
	 * Gets the gravatar url for the author of the post.
	 *
	 * @return  string
	 */
	public function gravatar_url()
	{
		$attrs = $this->author ?
			md5(strtolower(trim($this->author->user_email))):
			"?f=y"; // Force the icon

		return "http://www.gravatar.com/avatar/".$attrs;
	}

	/**
	 * Gets the authors name
	 *
	 * This function does an author check in case the author has been changed in edit mode.
	 *
	 * @return string
	 */
	public function author()
	{
		return $this->author ? $this->author->display_name : $this->meta('author_name');
	}

	/**
	 * Gets the tags
	 *
	 * @param array
	 */
	public function tags()
	{
		if ($this->tags === null)
		{
			$tags = get_the_tags($this->meta('original_post_id'));

			if ( ! is_array($tags))
			{
				$tags = array();
			}

			$this->tags = $tags;
		}
		
		return $this->tags;
	}

	/**
	 * Gets the categories
	 *
	 * @return array
	 */
	public function categories()
	{
		if ($this->categories === null)
		{	
			$categories = get_the_category($this->meta('original_post_id'));

			if (! is_array($categories))
			{
				$categories = array();
			}

			$this->categories = $categories;
		}

		return $this->categories;
	}

	/**
	 * Gets the comments
	 *
	 * @return array
	 */
	public function comments()
	{
		if ($this->comments === null)
		{
			$comments = get_comments(array(
				'post_id' => $this->meta('original_post_id')
			));

			$tmp = array();
			
			if (is_array($comments))
			{
				foreach ($comments as $c)
				{
					$tmp[] = new Anthologize_API_Comment((array) $c);
				}
			}

			$this->comments = $tmp;
		}

		return $this->comments;
	}

}
