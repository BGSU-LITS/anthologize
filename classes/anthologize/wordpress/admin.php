<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize Administration in Wordpress
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Wordpress_Admin {
	/**
	 * @var   string   The minimum level for the user to access the pages.
	 */
	protected $minimum_cap = null;

	/**
	 * Setup the admin interface
	 */
	public function __construct()
	{
		$this->minimum_cap = $this->minimum_cap();

		add_action( 'admin_init', array ( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'dashboard_hooks' ), 990 );
		add_action( 'admin_notices', array( $this, 'version_nag' ) );
		
		if ( is_multisite() ) {
			add_action( 'wpmu_options', array( $this, 'ms_settings' ) );
			add_action( 'update_wpmu_options', array( $this, 'save_ms_settings' ) );
		}
	}

	/**
	 * Initilization function for the admin panel
	 */
	public function init() {
		foreach ( array('anth_project', 'anth_part', 'anth_library_item', 'anth_imported_item') as $type ) {
			add_meta_box('anthologize', __( 'Anthologize', 'anthologize' ), array($this,'item_meta_box'), $type, 'side', 'high');
			add_meta_box('anthologize-save', __( 'Save', 'anthologize' ), array($this,'meta_save_box'), $type, 'side', 'high');
			remove_meta_box( 'submitdiv' , $type , 'normal' );
		}

		add_action('save_post',array( $this, 'item_meta_save' ));

		do_action( 'anthologize_admin_init' );
	}

	/**
	 * Loads the minimum user capability for displaying the Anthologize menus
	 *
	 * When running Multisite, this function first checks to see whether the super admin has
	 * allowed per-blog settings.
	 *
	 * For now, Anthologize pages are all-or-nothing. In the future, finer-grained access is
	 * planned. In the meantime, feel free to filter this value in your own plugin.
	 *
	 * @package Anthologize
	 * @since 0.6
	 * @return   mixed    The minimum cap for user actions.
	 */
	public function minimum_cap() {
		if ($this->minimum_cap !== null)
		{
			return $this->minimum_cap;
		}

		// If the super admin hasn't set a default, it'll fall back to manage_options, i.e. Administrators-only
		// Get the default cap
		if ( is_multisite() ) {
			$site_settings = get_site_option( 'anth_site_settings' );
	
			$default_cap = !empty( $site_settings['minimum_cap'] ) ? $site_settings['minimum_cap'] : 'manage_options';
		} else {
			$default_cap = 'manage_options';
		}

		// Then use the default to set the minimum cap for this blog
		if ( !is_multisite() || empty( $site_settings['forbid_per_blog_caps'] ) ) {
			$blog_settings = get_option( 'anth_settings' );
			$cap = !empty( $blog_settings['minimum_cap'] ) ? $blog_settings['minimum_cap'] : $default_cap;
		} else {
			$cap = $default_cap; 
		}

		return apply_filters( 'anth_minimum_cap', $cap );
	}

	/**
	 * Adds Anthologize's plugin pages to the Dashboard
	 *
	 * Uses a somewhat hackish method, borrowed from BuddyPress, to get things in a nice order
	 *
	 * @package Anthologize
	 * @since 0.3
	 */
	public function dashboard_hooks() {
		global $menu;
		
		// The default location of the Anthologize menu item. Anthologize needs an empty
		// space before and after it in order to display, so it might have to poke around
		// a bit to find room for itself
		$default_index = apply_filters( 'anth_default_menu_position', 55 ); 
		
		while ( !empty( $menu[$default_index - 1] ) || !empty( $menu[$default_index ] ) || !empty( $menu[$default_index + 1] ) ) {
			$default_index++;
		}
		
		$separator = array(
			0 => '',
			1 => 'read',
			2 => 'separator-anthologize',
			3 => '',
			4 => 'wp-menu-separator'
		);
		$menu[$default_index - 1] = $separator;
		$menu[$default_index + 1] = $separator;
		
		$plugin_pages = array();
		
		// Adds the top-level Anthologize Dashboard menu button
		$this->add_admin_menu_page( array(
			'menu_title' => __( 'Anthologize', 'anthologize' ),
			'page_title' => __( 'Anthologize', 'anthologize' ),
			'access_level' => $this->minimum_cap, 'file' => 'anthologize',
			'function' => array( "Anthologize", 'render' ),
			'position' => $default_index
		) );
		
		// Creates the submenu items
		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'My Projects', 'anthologize' ), __( 'My Projects','anthologize' ), $this->minimum_cap, 'anthologize', array ("Anthologize", 'router') );
		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'New Project','anthologize' ), __('New Project','anthologize'), $this->minimum_cap, 'anthologize&action=create', array ("Anthologize", 'router'));
		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Export Project', 'anthologize' ), __( 'Export Project', 'anthologize' ), $this->minimum_cap, 'anthologize&action=export', array ("Anthologize", 'router') );
		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Import Content', 'anthologize' ), __( 'Import Content', 'anthologize' ), $this->minimum_cap, 'anthologize&controller=import', array ("Anthologize", 'router') );
		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Settings', 'anthologize' ), __( 'Settings', 'anthologize' ), $this->minimum_cap, 'anthologize&controller=settings', array ("Anthologize", 'router'));
		
		foreach ( $plugin_pages as $plugin_page ) {
			add_action( "admin_print_styles", array( $this, 'load_styles' ) );
			add_action( "admin_print_scripts", array( $this, 'load_scripts' ) );
		}

	}

	/**
	 * Borrowed, with much love, from BuddyPress. Allows us to put Anthologize way up top.
	 */
	public function add_admin_menu_page( $args = '' ) {
		global $menu, $admin_page_hooks, $_registered_pages;

		$defaults = array(
			'page_title' => '',
			'menu_title' => '',
			'access_level' => 2,
			'file' => false,
			'function' => false,
			'icon_url' => false,
			'position' => 100
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$file = plugin_basename( $file );

		$admin_page_hooks[$file] = sanitize_title( $menu_title );

		$hookname = get_plugin_page_hookname( $file, '' );
		if (!empty ( $function ) && !empty ( $hookname ))
			add_action( $hookname, $function );

		if ( empty($icon_url) )
			$icon_url = 'images/generic.png';
		elseif ( is_ssl() && 0 === strpos($icon_url, 'http://') )
			$icon_url = 'https://' . substr($icon_url, 7);

		do {
			$position++;
		} while ( !empty( $menu[$position] ) );

		$menu[$position] = array ( $menu_title, $access_level, $file, $page_title, 'menu-top ' . $hookname, $hookname, $icon_url );
		unset( $menu[$position][5] );

		$_registered_pages[$hookname] = true;

		return $hookname;
	}

	/**
	 * Loads Anthologize's JS
	 *
	 * This needs a massive amount of cleanup
	 *
	 * @package Anthologize
	 * @since 0.3
	 */
	public function load_scripts() {
		wp_enqueue_script( 'anthologize-js', WP_PLUGIN_URL . '/anthologize/js/project-organizer.js' );
		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-sortable');
		wp_enqueue_script( 'jquery-ui-draggable');
		wp_enqueue_script( 'jquery-ui-datepicker', WP_PLUGIN_URL . '/anthologize/js/jquery-ui-datepicker.js');
		wp_enqueue_script( 'jquery-cookie', WP_PLUGIN_URL . '/anthologize/js/jquery-cookie.js' );
		wp_enqueue_script( 'blockUI-js', WP_PLUGIN_URL . '/anthologize/js/jquery.blockUI.js' );
		wp_enqueue_script( 'anthologize_admin-js', WP_PLUGIN_URL . '/anthologize/js/anthologize_admin.js' );
		wp_enqueue_script( 'anthologize-sortlist-js', WP_PLUGIN_URL . '/anthologize/js/anthologize-sortlist.js' );
		
		wp_localize_script( 'anthologize-sortlist-js', 'anth_strings', array(
			'append'		=> __( 'Append', 'anthologize' ),
			'cancel'		=> __( 'Cancel', 'anthologize' ),
			'commenter'		=> __( 'Commenter', 'anthologize' ),
			'comment_content'	=> __( 'Comment Content', 'anthologize' ),
			'comments'		=> __( 'Comments', 'anthologize' ),
			'comments_explain'	=> __( 'Check the comments from the original post that you would like to include in your project.', 'anthologize' ),
			'done'			=> __( 'Done', 'anthologize' ),
			'edit'			=> __( 'Edit', 'anthologize' ),
			'less'			=> __( 'less', 'anthologize' ),
			'more'			=> __( 'more', 'anthologize' ),
			'no_comments'		=> __( 'This post has no comments associated with it.', 'anthologize' ),
			'preview'		=> __( 'Preview', 'anthologize' ),
			'posted'		=> __( 'Posted', 'anthologize' ),
			'remove'		=> __( 'Remove', 'anthologize' ),
			'save'			=> __( 'Save', 'anthologize' ),
			'select_all'		=> __( 'Select all', 'anthologize' ),
			'select_none'		=> __( 'Select none', 'anthologize' ),
		) );
	}
	
	/**
	 * Loads Anthologize's styles
	 *
	 * This should be optimized to load CSS only on Anthologize pages
	 *
	 * @package Anthologize
	 * @since 0.3
	 */
	public function load_styles() {
		wp_enqueue_style( 'anthologize-css', WP_PLUGIN_URL . '/anthologize/css/project-organizer.css' );
		wp_enqueue_style( 'jquery-ui-datepicker-css', WP_PLUGIN_URL . '/anthologize/css/jquery-ui-1.7.3.custom.css');
	}

    /**
     * Deletes an item. Fun!
     */
     public function item_delete($post_id)
     {

     }

	/**
	 * The Save box display
	 *
	 * @param  int  $post_id  The post id
	 */ 
	function meta_save_box( $post_id ) {
		echo Anthologize::render('meta/save_box', array('post_id' => $post_id));
	}

	/**
	 * item_meta_save
	 *
	 * Processes post save from the item_meta_box function. Saves
	 * custom post metadata. Also responsible for correctly
	 * redirecting to Anthologize pages after saving.
	 */
	public function item_meta_save( $post_id ) {
		// make sure data came from our meta box. Only save when nonce is present
		if ( empty( $_POST['anthologize_noncename'] ) || !wp_verify_nonce( $_POST['anthologize_noncename'],__FILE__ ) )
			return $post_id;
		
		// Check user permissions.
		if ( !$this->user_can_edit() ) 
			return $post_id;
		
		if ( empty( $_POST['anthologize_meta'] ) || !$new_data = $_POST['anthologize_meta'] )
			$new_data = array();
		
		if ( !$anthologize_meta = get_post_meta( $post_id, 'anthologize_meta', true ) )
			$anthologize_meta = array();
		
		foreach( $new_data as $key => $value ) {
			$anthologize_meta[$key] = maybe_unserialize( $value );
		}
		
		update_post_meta( $post_id,'anthologize_meta', $anthologize_meta );
		update_post_meta( $post_id, 'author_name', $new_data['author_name'] );
		
		// We need to filter the redirect location when Anthologize items are saved
		add_filter( 'redirect_post_location', array( $this, 'item_meta_redirect' ) );
		
		return $post_id;
	}

	/**
	 * Provides a redirect location for after a post is saved
	 *
	 * @package Anthologize
	 * @since 0.3
	 *
	 * @param str $location
	 * @retur str $location
	 */
	public function item_meta_redirect($location) {
		if ( isset( $_POST['post_parent'] ) ) {
			$post_parent_id = $_POST['post_parent'];
		} else {
			$post = get_post( $_POST['ID'] );
			$post_parent_id = $post->post_parent;
		}

    	$post_parent = get_post( $post_parent_id );

		$arg = isset($_POST['new_part']) ?
			$_POST['parent_id'] :
			$post_parent->post_parent;

		return add_query_arg( array(
			'page'	     => 'anthologize',
			'action'     => 'manage',
			'project_id' => $arg
		), admin_url( 'admin.php' ) );
    }

    /**
     * item_meta_box
     *
     * Displays form for editing item metadata associated with
     * Anthologize. Includes hidden fields for post_parent and
     * menu_order because WP sets those values to 0 if those
     * fields are not present on the form.
     **/
    function item_meta_box() {
        global $post;

		echo Anthologize::render("meta/edit", array(
			'post' => $post,
			'meta' => get_post_meta( $post->ID, 'anthologize_meta', TRUE ),
			'imported_item_meta' => get_post_meta( $post->ID, 'imported_item_meta', true ),
			'author_name' => get_post_meta( $post->ID, 'author_name', true )
		));
    }


	/**
	 * Checks whether a user has permission to edit the item in question
	 *
	 * @package Anthologize
	 * @since 0.6
	 *
	 * @param int $post_id Optional The post to check. Defaults to current post
	 * @param int $user_id Optional The user to check. Defaults to logged-in user
	 * @return bool $user_can_edit Returns true when the user can edit, false if not
	 */
	public function user_can_edit( $post_id = false, $user_id = false ) {
		global $post, $current_user;
		
		$user_can_edit = false;
		
		if ( is_super_admin() ) {
			// When the user is a super admin (network admin on MS, Administrator on
			// single WP) there is no need to check anything else
			$user_can_edit = true;
		} else {				
			if ( !$user_id )
				$user_id = $current_user->ID;
			
			if ( $post_id ) {
				$post = get_post( $post_id );
			}
			
			// Is the user the author of the post in question?
			if ( $user_id == $post->post_author )
				$user_can_edit = true;
		}		
		
		return apply_filters( 'anth_user_can_edit', $user_can_edit, $post_id, $user_id );
	}

	/**
	 * Displays a message to let the user know that the version of wordpress isn't compatible.
	 *
	 * @global type $wp_version 
	 */
	function version_nag() {
		global $wp_version;

		if ( version_compare( $wp_version, '3.0', '<' ) ){
			Anthologize::render("notice/wordpress_nag", array(
				'version' => $wp_version
			));
		}
	}
	
	/**
	 * Adds Anthologize settings to the ms-options.php panel of an MS dashboard
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	public function ms_settings() {
		$site_settings = get_site_option( 'anth_site_settings' );

		Anthologize::render("multisite/settings", array(
			'minimum_cap' => ! empty( $site_settings['minimum_cap'] ) ? $site_settings['minimum_cap'] : 'manage_options',
		));		
	}
	
	/**
	 * Saves the settings created in ms_settings()
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	public function save_ms_settings() {
		$forbid_per_blog_caps = empty( $_POST['anth_site_settings']['forbid_per_blog_caps'] ) ? 1 : 0;
		$minimum_cap = empty( $_POST['anth_site_settings']['minimum_cap'] ) ? 'manage_options' : $_POST['anth_site_settings']['minimum_cap'];
		
		//print_r( $_POST['anth_site_settings']['minimum_cap'] );
		$anth_site_settings = array(
			'forbid_per_blog_caps' => $forbid_per_blog_caps,
			'minimum_cap' => $minimum_cap
		);
		
		update_site_option( 'anth_site_settings', $anth_site_settings );
	}
}
