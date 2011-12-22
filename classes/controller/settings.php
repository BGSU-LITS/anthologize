<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Controller to handle the Anthologize settings.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Settings extends Controller {
	/**
	 * @var   array    The blog settings
	 */
	protected $settings;

	/**
	 * @var   array    Settings in a multi-site environment
	 */
	protected $site_settings;

	/**
	 * Get the site and blog settings
	 */
	public function before()
	{
		$this->settings = $this->get_settings();
		$this->site_settings = $this->get_site_settings();
	}

	/**
	 * The settings.
	 */
	public function action_get_index()
	{
		$this->content = Anthologize::render("settings", array(
			'action' => get_admin_url() . "admin.php?page=anthologize&controller=settings&noheader=true",
			'forbid_local_caps' => $this->forbid_local_caps(),
			'minimum_cap' => $this->minimum_cap(),
			'message' => $this->param('saved', false)
		));
	}

	/**
	 * The settings have been posted
	 */
	public function action_post_index()
	{
		check_admin_referer( 'anth_settings' );

		$anth_settings = !empty( $_POST['anth_settings'] ) ? $_POST['anth_settings'] : array();
		update_option( 'anth_settings', $anth_settings );

		Anthologize::redirect(get_admin_url().'admin.php?page=anthologize&controller=settings&saved=1');
	}

	/**
	 * Loads the settings for the blog
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	protected function get_settings() {
		return get_option( 'anth_settings' );
	}
	
	/**
	 * Loads the settings for the blog
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	protected function get_site_settings() {
		$site_settings = array();
		
		if ( is_multisite() )
			$site_settings = get_site_option( 'anth_site_settings' );
			
		return apply_filters( 'anth_site_settings', $site_settings );
	}

	/**
	 * Determine whether the network admin has forbidden the setting of local caps
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	protected function forbid_local_caps() {
		$forbid_local_caps = false;
		
		if ( !empty( $this->site_settings['forbid_per_blog_caps'] ) )
			$forbid_local_caps = true;
			
		return apply_filters( 'anth_forbid_local_caps', $forbid_local_caps );
	}

	/**
	 * Gets the minimum cap for the plugin
	 *
	 * @return   string
	 */
	function minimum_cap() {
		$default_cap = 'manage_options';
	
		if ( is_multisite() ) {
			// On multisite, the network admin is able to override the local admin's
			// settings
			$forbid_local_caps = !empty( $this->site_settings['forbid_per_blog_caps'] ) ? true : false;
			
			if ( $this->forbid_local_caps ) {
				$minimum_cap = !empty( $this->site_settings['minimum_cap'] ) ? $this->site_settings['minimum_cap'] : 'manage_options';
			} else {
				// If the network admin has not forbidden local caps, we still must
				// check whether there's a network default
				$default_cap = !empty( $this->site_settings['minimum_cap'] ) ? $this->site_settings['minimum_cap'] : 'manage_options';
				$minimum_cap = !empty( $this->settings['minimum_cap'] ) ? $this->settings['minimum_cap'] : $default_cap;
			}
		} else {		
			// On non-MS, we can check the local settings directly
			$minimum_cap = !empty( $this->settings['minimum_cap'] ) ? $this->settings['minimum_cap'] : $default_cap;
		}
		
		return apply_filters( 'anth_settings_minimum_cap', $minimum_cap );
	}

}
