<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Project exportation.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Controller_Export extends Controller
{

	/**
	 * The home screen
	 */
	public function action_get_index()
	{
		$project_id = $this->param('project_id', false);
		
		$data = array(
			'projects' => $this->get_projects(),
			'project_id' => $project_id,
			'action' => "admin.php?page=anthologize/export&noheader=true",
		);

		$this->content = Anthologize::render("export/home", array_merge($data, self::get_metadata($project_id)));
	}

	/**
	 * Posting to the home screen
	 */
	public function action_post_index()
	{
		$project_id = $_POST['project_id'];

		$meta = array_merge(self::get_metadata($project_id), $_POST);
		update_post_meta( $project_id, 'anthologize_meta', $meta );

		Anthologize::redirect("admin.php?page=anthologize/export&action=step2");
	}

	/**
	 * Gets the project metadata
	 *
	 * @param  int  $id   The project id
	 * @return array 
	 */
	public static function get_metadata($id)
	{
		$meta = ($id === false) ? array() : get_post_meta($id, 'anthologize_meta', true );

		$defaults = array(
			'cyear' => date("Y"),
			'cname' => isset($meta['author_name']) ? isset($meta['author_name']) : "",
			'ctype' => "cc",
			'cctype' => "by",
			'edition' => "",
			'authors' => isset($meta['author_name']) ? $meta['author_name'] : "",
			'dedication' => "",
			'acknowledgements' => ""
		);

		return array_merge($defaults, $meta);
	}

	public function action_get_step2()
	{
		
	}

	public function action_get_step3()
	{
		/*
		 * You should never actually get to this point.
		 * Method load_template() in anthologize.php should grab all requests with $_POST['filetype'],
		 * send a file to the user, and die. If someone ends up here, it means
		 * that something has gone awry.
		 */
	}
	
	function export_format_options_title() {
		global $anthologize_formats;
		
		$format = $_SESSION['filetype'];
	
		$title = sprintf( __( '%s Publishing Options', 'anthologize' ), $anthologize_formats[$format]['label'] );
		
		echo $title;
	}

	function save_session() {
		
		if ( $_POST['export-step'] == '2' )
			$_SESSION['outputParams'] = array( 'format' => $_POST['filetype'] );
		
		// outputParams need to be reset at step 3 so that
		// on a refresh null values will overwrite
		if ( $_POST['export-step'] == '3' ) {
			// filetype has been set different ways in different versions
			// This is to be safe
			$filetype = isset( $_SESSION['outputParams']['filetype'] ) ? $_SESSION['outputParams']['filetype'] : $_SESSION['filetype'];
			$_SESSION['outputParams'] = array( 'format' => $filetype );
		}		
		
		
		foreach ( $_POST as $key => $value ) {
			if ( $key == 'submit' || $key == 'export-step' )
				continue;
		
			if ( $key == '' )
				echo "OK";
			
			if ( $_POST['export-step'] == '3' )
				$_SESSION['outputParams'][$key] = stripslashes( $value );
			else
				$_SESSION[$key] = stripslashes( $value );
		
		}
	
	}
	
	function export_format_list() { 
		global $anthologize_formats;
	?>
		<?php foreach( $anthologize_formats as $name => $fdata ) : ?>
		
			<input type="radio" name="filetype" value="<?php echo $name ?>" /> <?php echo $fdata['label'] ?><br />
					
		<?php endforeach; ?>
	
		<?php do_action( 'anthologize_export_format_list' ) ?>

	<?php
	}
	
	function render_format_options() {
		global $anthologize_formats;
		
		$format = $_SESSION['filetype'];
		
		if ( $fdata = $anthologize_formats[$format] ) {
			$return = '';
			foreach( $fdata as $oname => $odata ) {
			
				if ( $oname == 'label' || $oname == 'loader-path' )
					continue;
				
				if ( !$odata )
					continue;
				
				$default = ( isset( $odata['default'] ) ) ? $odata['default'] : false;
				
				$return .= '<div class="export-options-box">'; 
		
				$return .= '<div class="pub-options-title">' . $odata['label'] . '</div>';
				
				switch( $odata['type'] ) {
					case 'checkbox':
						$return .= $this->build_checkbox( $oname, $odata['label'] );
						break;
					
					case 'dropdown':
						$return .= $this->build_dropdown( $oname, $odata['label'], $odata['values'], $default );
						break;
						
					// Default is a textbox
					default:
						$return .= $this->build_textbox( $oname, $odata['label'] );
						break;
				}
				
				$return .= '</div>';
				
			}
		} else {
			$return = __( 'This appears to be an invalid export format. Please try again.', 'anthologize' );
		}
					
		echo $return;
	}

	function build_checkbox( $name, $label ) {
		
		$html = '<input name="' . $name . '" id="' . $name .'" type="checkbox">';
		
		return apply_filters( 'anthologize_build_checkbox', $html, $name, $label );
	}

	function build_dropdown( $name, $label, $options, $default ) {
		// $name is the input name (no spaces, eg 'page-size')
		// $label is the input label (for display, eg 'Page Size'. Should be internationalizable, eg __('Page Size', 'anthologize')
		// $options is associative array where keys are option values and values are the text displayed in the option field.
		// $default is the default option
						
		$html = '<select name="' . $name . '">';
		
		foreach( $options as $ovalue => $olabel ) {
			$html .= '<option value="' . $ovalue . '"';
			
			if ( $default == $ovalue )
				$html .= ' selected="selected"';
						
			$html .= '>' . $olabel . '</option>';
		}	
		
		$html .= '</select>';
		
		return apply_filters( 'anthologize_build_dropdown', $html, $name, $label, $options );
	}
	
	function build_textbox( $name, $label ) {
					
		$html = '<input name="' . $name . '" id="' . $name . '" type="text">';
		
		return apply_filters( 'anthologize_build_textbox', $html, $name, $label );
	}

	/**
	 * Gets a list of Anthologize projects
	 *
	 * @return  array
	 */
	protected function get_projects()
	{
		$projects = array();

		query_posts( 'post_type=anth_project&orderby=title&order=ASC' );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$projects[get_the_ID()] = get_the_title();
			}
		}

		return $projects;
	}
	
}
