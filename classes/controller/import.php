<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Import Wizard.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Import extends Controller {

	/**
	 * @var string   A error message for the form
	 */
	protected $message = "";
	
	/**
	 * Importing a project
	 */
	public function action_get_index()
	{
		$this->content = Anthologize::render("import/form", array(
			'action' => get_admin_url()."admin.php?page=anthologize&controller=import",
			'message' => $this->message
		));
	}

	/**
	 * Processes the feed.
	 */
	public function action_post_index()
	{
		include_once( ABSPATH . 'wp-includes/class-simplepie.php' );
		$feedurl = $_POST['feedurl'];

		if ($feedurl === "")
		{
			$this->set_message("Sorry, no items were found. Please try another feed address.");
		}

		$rss = fetch_feed( trim( $feedurl ) );

		if ( !empty( $rss->errors ) )
		{
			$this->set_message('There was an unknown error');
		}

		if ( !$maxitems = $rss->get_item_quantity() )
		{
			$this->set_message("There are no items in the feed");
		}

		$this->content = Anthologize::render("import/select", array(
			'items' => $this->fetch_items($rss),
			'action' => get_admin_url()."admin.php?page=anthologize&controller=import&action=import",
		));
	}

	/**
	 * Import away!!!
	 */
	public function action_post_import()
	{
		if ( ! empty($_POST['copyitems']))
		{
			include_once( ABSPATH . 'wp-includes/class-simplepie.php' );
			$rss = fetch_feed( trim( $_POST['feedurl'] ) );

			$saved = $this->save_items($_POST['copyitems'], $this->fetch_items($rss));

			$this->set_message($saved." imported posts.", "updated");
		}
	}

	/**
	 * Gets all of the feed items.
	 *
	 * @param  SimplePie  $rss   The rss object
	 * @return array 
	 */
	protected function fetch_items($rss)
	{
		$feed_title = $rss->get_title();
		$feed_permalink = $rss->get_permalink();

		$rss_items = $rss->get_items();

		$items_data = array( 'feed_title' => $feed_title, 'feed_permalink' => $feed_permalink );

		$items = array();
		foreach ($rss_items as $rss_item ) {
			$item_data = array(
				'link' => $rss_item->get_link(),
				'title' => $rss_item->get_title(),
				'authors' => $rss_item->get_authors(),
				'created_date' => $rss_item->get_date(),
				'categories' => $rss_item->get_categories(),
				'contributors' => $rss_item->get_contributors(),
				'copyright' => $rss_item->get_copyright(),
				'description' => $rss_item->get_description(),
				'content' => $rss_item->get_content(),
				'permalink' => $rss_item->get_permalink(),
			);

			$items[] = array_merge($items_data, $item_data);
		}

		return $items;
	}

	/**
	 * Saves the items selected to be imported to the database
	 *
	 * @global $current_user
	 * @param  array  $copy   A list of items to import
	 * @param  array  $items  A full list of feed items
	 * @return int            The number of inserted posts
	 */
	protected function save_items($copy, $items)
	{
		global $current_user;

		$saved = 0;
		$tags = array();

		foreach ($copy as $index)
		{
			$item = $items[$index];

			foreach( $item['categories'] as $cat ) {
				if ( $cat->term )
					$tags[] = $cat->term;
			}

			$args = array(
				'post_status' => 'draft',
				'post_type' => 'anth_imported_item',
				'post_author' => $current_user->ID,
				'guid' => $item['permalink'],
				'post_content' => $item['content'],
				'post_excerpt' => $item['description'],
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_title' => $item['title'],
				'tags_input' => $tags
			);

			if ( isset( $item['created_date'] ) ) {
				$original_post_date = date( "Y-m-d H:i:s", strtotime( $item['created_date'] ) );
				$args['post_date'] = $original_post_date;
				$args['post_date_gmt'] = $original_post_date;
			}

			$post_id = wp_insert_post( $args );
			if ( $post_id !== 0 AND  ! $post_id instanceof WP_Error)
			{
				$author_name = $item['authors'][0]->name;
				update_post_meta( $post_id, 'author_name', $author_name );
				update_post_meta( $post_id, 'imported_item_meta', $item );

				$saved += 1;
			}
		}

		return $saved;
	}

	/**
	 * Sets a message and stops the request execution.
	 *
	 * @param   string   $msg   The message to set
	 * @param   string   $type  the type of message (error or updated)
	 */
	protected function set_message($msg, $type = "error")
	{
		$this->message = array(
			'message' => __($msg, "anthologize"),
			'type' => $type,
		);
		$this->action_get_index();
		$this->after();
		exit;
	}

}
