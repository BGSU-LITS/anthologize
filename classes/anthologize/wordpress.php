<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * This class does what is need to get the plugin up and running on Wordpress
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Wordpress
{
	/**
	 * And away we go!!
	 */
	public static function bootstrap()
	{
		// Start the session if not already up and running.
		if ( ! isset($_SESSION))
		{
			session_start();
		}

		// If the PHP version is less than 5, print a message and stop the plugin from loading
		if (version_compare( phpversion(), '5', '<' )) {
			add_action('admin_notices', array("Anthologize_Wordpress", 'phpversion_nag'));
			return;
		}

		// Give me something to believe in
		add_action( 'plugins_loaded', array ( "Anthologize_Wordpress", 'loaded' ) );

		add_action( 'init', array ( "Anthologize_Wordpress", 'init' ) );

		// Load constants
		add_action( 'anthologize_init',  array ( "Anthologize_Wordpress", 'load_constants' ) );

		// Check for an ajax request
		add_action( 'anthologize_init', array("Anthologize_Wordpress", 'check_ajax'));

		// Load the post types
		add_action( 'anthologize_init', array ( "Anthologize_Wordpress", 'register_post_types' ) );

		// Load the custom feed
		add_action( 'do_feed_customfeed', array ( "Anthologize_Wordpress", 'register_custom_feed' ) );

		// Include the necessary files
		add_action( 'anthologize_loaded', array ( "Anthologize_Wordpress", 'includes' ) );

		// Attach textdomain for localization
		add_action( 'anthologize_init', array ( "Anthologize_Wordpress", 'textdomain' ) );

		add_action( 'anthologize_init', array ( "Anthologize_Wordpress", 'load_template' ), 999 );

		// Register the built-in export formats
		add_action( 'anthologize_init', array( "Anthologize_Wordpress", 'default_export_formats' ) );

		add_filter( 'custom_menu_order', array( "Anthologize_Wordpress", 'custom_menu_order_function' ) );

		add_filter( 'menu_order', array( "Anthologize_Wordpress", 'menu_order_my_function' ) );

		// activation sequence
		register_activation_hook( __FILE__, array( "Anthologize_Wordpress", 'activation' ) );

		// deactivation sequence
		register_deactivation_hook( __FILE__, array( "Anthologize_Wordpress", 'deactivation' ) );
	}

	/**
	 * Gets the parts associated with a project
	 *
	 * @package Anthologize
	 * @since 0.3
	 *
	 * @param int $project_id The id for the project being loaded
	 * @return array $parts The project's parts
	 */
	public static function get_project_parts($project_id = null)
	{
		global $post;
	
		if ( !$project_id ) {
		    $project_id = $post->ID;
		}
	
		$args = array(
			'post_parent' => $project_id,
			'post_type' => 'anth_part',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
	
		$parts_query = new WP_Query( $args );
	
		if ( $parts = $parts_query->posts ) {
			return $parts;
		} else {
			return false;
		}
	}

	/**
	 * Gets the items associated with a project
	 *
	 * @package Anthologize
	 * @since 0.3
	 *
	 * @param int $project_id The id for the project being loaded
	 * @return array $items The project's items
	 */
	public static function get_project_items($project_id = null) {
		global $post;
	
		if (!$project_id) {
			$project_id = $post->ID;
		}
	
		$parts = self::get_project_parts($project_id);

		$items = array();
		if ( $parts ) {
			foreach ($parts as $part) {
				$args = array(
					'post_parent' => $part->ID,
					'post_type' => 'anth_library_item',
					'posts_per_page' => -1,
					'orderby' => 'menu_order',
					'order' => 'ASC'
				);
				
				$items_query = new WP_Query( $args );
				
				// May need optimization
				if ( $child_posts = $items_query->posts ) {
					foreach( $child_posts as $child_post ) {
						$items[] = $child_post;
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Gets a list of all available post types (excluding the anthologize types)
	 *
	 * @return   array      Array of available post types
	 */
	public static function available_post_types()
	{
		$all_post_types = get_post_types( false, false );

		$excluded_post_types = apply_filters( 'anth_excluded_post_types', array(
			'anth_library_item',
			'anth_part',
			'anth_project',
			'attachment',
			'revision',
			'nav_menu_item'
		) );

		$types = array();
		foreach( $all_post_types as $name => $post_type ) {
			if ( !in_array( $name, $excluded_post_types ) )
				$types[$name] = isset( $post_type->labels->name ) ? $post_type->labels->name : $name;
		}

		return apply_filters( 'anth_available_post_types', $types );
	}

	/**
	 * Sets up anthologize constants
	 */
	public function load_constants() {
		if ( !defined( 'ANTHOLOGIZE_INSTALL_PATH' ) )
			define( 'ANTHOLOGIZE_INSTALL_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR );
		
		if ( !defined( 'ANTHOLOGIZE_INCLUDES_PATH' ) )
			define( 'ANTHOLOGIZE_INCLUDES_PATH', ANTHOLOGIZE_INSTALL_PATH . 'includes' . DIRECTORY_SEPARATOR );

		if ( !defined( 'ANTHOLOGIZE_TEIDOM_PATH' ) )
			define( 'ANTHOLOGIZE_TEIDOM_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php' );

		if ( !defined( 'ANTHOLOGIZE_TEIDOMAPI_PATH' ) )
			define( 'ANTHOLOGIZE_TEIDOMAPI_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php' );
			
		if ( !defined('ANTHOLOGIZE_CREATORS_ALL'))
		    define('ANTHOLOGIZE_CREATORS_ALL', 1);
		if ( !defined('ANTHOLOGIZE_CREATORS_ASSERTED'))
		    define('ANTHOLOGIZE_CREATORS_ASSERTED', 2);
	}

	/**
	 * Checks to see if this is an ajax request.
	 *
	 * If so we don't need the whole plugin loop, just output the reponse and
	 * be done with the request
	 *
	 * @see http://davidwalsh.name/detect-ajax
	 */
	public function check_ajax()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			$ajax = new Controller_Ajax_WP;
		}
	}

	/**
	 * Let plugins know that we're initializing
	 */
	function init() {
		do_action( 'anthologize_init' );
	}

	/**
	 * Allow this plugin to be translated by specifying text domain.
	 *
	 * @todo Make the logic a bit more complex to allow for custom text within a given language
	 */
	public function textdomain() {
		$locale = get_locale();

		// First look in wp-content/anthologize-files/languages, where custom language files will not be overwritten by Anthologize upgrades. Then check the packaged language file directory.
		$mofile_custom = WP_CONTENT_DIR . "/anthologize-files/languages/anthologize-$locale.mo";
		$mofile_packaged = WP_PLUGIN_DIR . "/anthologize/languages/anthologize-$locale.mo";

    	if ( file_exists( $mofile_custom ) ) {
      		load_textdomain( 'anthologize', $mofile_custom );
      	} else if ( file_exists( $mofile_packaged ) ) {
      		load_textdomain( 'anthologize', $mofile_packaged );
      	}
	}

	/**
	 * The next two functions are a hack to make WordPress hide the menu items
	 * for Parts and Library Items
	 *
	 * @todo Remove with proper routing
	 */
	public function custom_menu_order_function(){
		return true;
	}

	/**
	 * @todo Remove with proper routing
	 */
	public function menu_order_my_function( $menu_order ){
		global $menu;

		foreach ( $menu as $mkey => $m ) {

			$key = array_search( 'edit.php?post_type=anth_part', $m, true );
			$keyb = array_search( 'edit.php?post_type=anth_library_item', $m, true );

			if ( $key || $keyb ) {
				unset( $menu[$mkey] );
			}
		}

		return $menu_order;
	}


	/**
	 * Custom post types - Oh, Oh, Oh, It's Magic
	 */
	public function register_post_types() {
		register_post_type( 'anth_project', array(
			'label' => __( 'Projects', 'anthologize' ),
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "project"), // Permalinks format
		));

		 $parts_labels = array(
			'name' => _x('Parts', 'post type general name'),
			'singular_name' => _x('Part', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Part'),
			'edit_item' => __('Edit Part'),
			'new_item' => __('New Part'),
			'view_item' => __('View Part'),
			'search_items' => __('Search Parts'),
			'not_found' =>  __('No parts found'),
			'not_found_in_trash' => __('No parts found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'anth_part', array(
			'label' => __( 'Parts', 'anthologize' ),
			'labels' => $parts_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title'),
			'rewrite' => array("slug" => "part"), // Permalinks format
		));

		 $library_items_labels = array(
			'name' => _x('Library Items', 'post type general name'),
			'singular_name' => _x('Library Item', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Library Item'),
			'edit_item' => __('Edit Anthologize Library Item'),
			'new_item' => __('New Anthologize Library Item'),
			'view_item' => __('View Anthologize Library Item'),
			'search_items' => __('Search Library Items'),
			'not_found' =>  __('No library items found'),
			'not_found_in_trash' => __('No library items found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'anth_library_item', array(
			'label' => __('Library Items', 'anthologize' ),
			'labels' => $library_items_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true,
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions', 'comments'),
			'rewrite' => array("slug" => "library_item"), // Permalinks format
		));

		 $imported_items_labels = array(
			'name' => _x('Imported Items', 'post type general name'),
			'singular_name' => _x('Imported Item', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Imported Item'),
			'edit_item' => __('Edit Imported Item'),
			'new_item' => __('New Imported Item'),
			'view_item' => __('View Imported Item'),
			'search_items' => __('Search Imported Items'),
			'not_found' =>  __('No imported items found'),
			'not_found_in_trash' => __('No imported items found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'anth_imported_item', array(
			'label' => __('Imported Items', 'anthologize' ),
			'labels' => $imported_items_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "imported_item"), // Permalinks format
		));
	}

	/**
	 * Sets the default export format options
	 */
	public function default_export_formats() {

		// Defining the default options for export formats
		$d_page_size = array(
				'letter' => __( 'Letter', 'anthologize' ),
				'a4' => __( 'A4', 'anthologize' )
		);

		$d_font_size = array(
			'9' => __( '9 pt', 'anthologize' ),
			'10' => __( '10 pt', 'anthologize' ),
			'11' => __( '11 pt', 'anthologize' ),
			'12' => __( '12 pt', 'anthologize' ),
			'13' => __( '13 pt', 'anthologize' ),
			'14' => __( '14 pt', 'anthologize' )
		);

		$d_font_face = array(
			'times' => __( 'Times New Roman', 'anthologize' ),
			'helvetica' => __( 'Helvetica', 'anthologize' ),
			'courier' => __( 'Courier', 'anthologize' )
		);

		$d_font_face_pdf = array(
			'times' => __( 'Times New Roman', 'anthologize' ),
			'helvetica' => __( 'Helvetica', 'anthologize' ),
			'courier' => __( 'Courier', 'anthologize' ),
			'dejavusans' => __( 'Deja Vu Sans', 'anthologize' ),
			'arialunicid0-cj' => __( 'Chinese and Japanese', 'anthologize' ),
			'arialunicid0-ko' => __( 'Korean', 'anthologize' )
		);

		$d_font_face_epub = array(
			'Times New Roman' => __( 'Times New Roman', 'anthologize' ),
			'Helvetica' => __( 'Helvetica', 'anthologize' ),
			'Courier' => __( 'Courier', 'anthologize' )
		);
		// Register PDF + options
		anthologize_register_format( 'pdf', __( 'PDF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/pdf/base.php' );

		anthologize_register_format_option( 'pdf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );

		anthologize_register_format_option( 'pdf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

		anthologize_register_format_option( 'pdf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face_pdf, 'Times New Roman' );

		anthologize_register_format_option( 'pdf', 'break-parts', __( 'Page break before parts?', 'anthologize' ), 'checkbox' );

		anthologize_register_format_option( 'pdf', 'break-items', __( 'Page break before items?', 'anthologize' ), 'checkbox' );
		anthologize_register_format_option( 'pdf', 'include_comments', __("Include Comments?", 'anthologize'), 'checkbox', 'true', 'include_comments');

		anthologize_register_format_option( 'pdf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

		// Register RTF + options
		anthologize_register_format( 'rtf', __( 'RTF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/rtf/base.php' );
		anthologize_register_format_option( 'rtf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );
		anthologize_register_format_option( 'rtf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );
		anthologize_register_format_option( 'rtf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face_pdf, 'Times New Roman' );
		anthologize_register_format_option( 'rtf', 'break-parts', __( 'Page break before parts?', 'anthologize' ), 'checkbox' );
		anthologize_register_format_option( 'rtf', 'break-items', __( 'Page break before items?', 'anthologize' ), 'checkbox' );
		anthologize_register_format_option( 'rtf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

		// Register ePub.
		anthologize_register_format( 'epub', __( 'ePub', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/epub/index.php' );

		anthologize_register_format_option( 'epub', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

		anthologize_register_format_option( 'epub', 'font-family', __( 'Font Family', 'anthologize' ), 'dropdown', $d_font_face_epub, 'Times New Roman' );
		
		anthologize_register_format_option( 'epub', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

		//build the covers list for selection
		$coversArray = array();
		$coversArray['none'] = 'None';
		//scan the covers directory and return the array
		$filesArray = scandir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' .
			 DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub' . DIRECTORY_SEPARATOR . 'covers');
		foreach($filesArray as $file) {
			if(! is_dir($file)) {
				$coversArray[$file] = $file;
			}
		}

		anthologize_register_format_option( 'epub', 'cover', __( 'Cover Image', 'anthologize' ), 'dropdown', $coversArray);

		//epub colophon commented out until we get the XSLTs working for it
		//anthologize_register_format_option( 'epub', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

		// Register HTML

		anthologize_register_format( 'html', __( 'HTML', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/html/output.php' );

		$htmlFontSizes = array('48pt'=>'48 pt', '36pt'=>'36 pt', '18pt'=>'18 pt', '14'=>'14 pt', '12'=>'12 pt');

		anthologize_register_format_option( 'html', 'font-size', __( 'Font Size', 'anthologize' ), 'dropdown', $htmlFontSizes, '14pt' );

		anthologize_register_format_option( 'html', 'download', __('Download HTML?', 'anthologize'), 'checkbox', 'true', 'download');
		anthologize_register_format_option( 'html', 'include_comments', __("Include Comments?", 'anthologize'), 'checkbox', 'true', 'include_comments');

		// Register TEI. No options for this one
		anthologize_register_format( 'tei', __( 'Anthologize TEI', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/tei/base.php' );
	}

	/**
	 * Including some needed files
	 *
	 * @todo  Remove this and do proper autoloading
	 */
	public function includes() {

		if ( is_admin() ) {
			$admin = new Anthologize_Wordpress_Admin;
		}

		require_once Anthologize::find_file('includes', 'class-format-api');
		require_once Anthologize::find_file('includes', 'functions');
	}

	/**
	 * Let plugins know that we're done loading
	 */
	public function loaded() {
		do_action( 'anthologize_loaded' );
	}

	/**
	 * Catches a preview request and jumps out of the wordpress template.
	 */
	function load_template() {
		if ( isset($_GET['action']) AND $_GET['action'] === "preview" ) {
			load_template( ANTHOLOGIZE . 'templates/html_preview/preview.php' );
			die();
		}
	}

	/**
	 * Wordpress Plugin activation
	 */
	function activation() {
		require_once Anthologize::find_file("includes", "class-activation");
		$activation = new Anthologize_Activation();
	}

	/**
	 * Wordpress plugin deactivation
	 */
	function deactivation() {}

	/**
	 * Prints a warning to the screen when the PHP version is insufficient
	 *
	 * Anthologize requires at least PHP version 5.0. If the currently running version of PHP is
	 * less than 5.0, this function will warn the user to upgrade.
	 *
	 * @since 0.6
	 */
	public static function phpversion_nag()
	{
		include Anthologize::find_file("views", "phpnag");
	}

}
