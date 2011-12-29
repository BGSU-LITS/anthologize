<?php defined("ANTHOLOGIZE") or die("No direct script access.");
/**
 * Anthologize PDF Renderer
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_Output_PDF implements Anthologize_Output
{
	/**
	 * @var Anthologize_API_Project  The project api class
	 */
	protected $project;

	/**
	 * @var array  Output options
	 */
	protected $options;

	/**
	 * @var TCPDF  The pdf
	 */
	protected $output;

	/**
	 * @var  string   The base height
	 */
	public $baseH = '12';

	/**
	 * @var  string   The height of a part
	 */
	public $partH = '16';

	/**
	 * @var  string   The height of an post heading
	 */
	public $itemH = '12';

	/**
	 * @var  string   The path to the header logo. Should be in anthologize/images/
	 */
	public $headerLogo = "logo-pdf.png";

	/**
	 * @var  string   The header logo width
	 */
	public $headerLogoWidth = '10';

	/**
	 * @var  int   Keep track of how many pages in front so the TOC can be inserted in proper position in finish()
	 */
	public $frontPages = 0;

	/**
	 * @var  string  The path to the font folder.
	 */
	public $anthFontsPath = "";

	/**
	 * @var  string   Use Tidy for html cleanup?
	 */
	public $tidy = false;

	/**
	 * Gets the output for a html file.
	 *
	 * @param  Anthologize_API_Project $project  The anthologize project
	 * @param  array                   $options  Rendering options
	 */
	public function render(Anthologize_API_Project $project, array $options)
	{
		// Shut off the errors...
		error_reporting(0);

		$this->project = $project;
		$this->options = $options;

		// Setup the PDF classes
		$this->output = new Anthologize_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $options['page-size'], true, 'UTF-8', false);
		
		// Taken from the Anthologizer class
		$this->init();
		$this->appendFront();
		$this->appendBody();
		$this->appendBack();
		$this->finish();

		// Send the output
		$this->output->Output($this->project->file_name().".pdf", 'D');
	}

	/**
	 * Init
	 */
	public function init() {
		//set some language-dependent strings
		$this->output->setLanguageArray(array(
			'a_meta_charset' => 'UTF-8',
			'a_meta_dir' => 'ltr',
			'a_meta_language' => 'en',
			'w_page' => '',
		));

		$this->output->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		$this->output->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$this->output->setPrintHeader(false);
		$this->output->setPrintFooter(false);

		$this->output->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->output->setFooterFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));


		// set default monospaced font
		$this->output->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set default font subsetting mode
		$this->output->setFontSubsetting(false);

		$font_family = $this->options['font-face'];
		$this->baseH = $this->options['font-size'];

		$this->anthFontsPath = ANTHOLOGIZE .
			DIRECTORY_SEPARATOR . 'vendor' .
			DIRECTORY_SEPARATOR . 'tcpdf' .
			DIRECTORY_SEPARATOR . 'fonts' .
			DIRECTORY_SEPARATOR ;

		// Do some font checking
		switch($font_family) {
			case 'arialunicid0-ko':
				//arialunicid0.php has a code to uncomment by language
				//since we need to switch on the fly, arialunicid0-XX will include that, then override the
				//uncommented code
				//see arialunicid0.php in TCPDF fonts directory (scroll to the bottom), and pdf/fonts/arialunicid0-ko
				$this->output->AddFont($font_family, '', $this->anthFontsPath . 'arialunicid0-ko.php');
				$this->output->AddFont($font_family, 'B', $this->anthFontsPath . 'arialunicid0-ko.php');
				$this->output->AddFont($font_family, 'I', $this->anthFontsPath . 'arialunicid0-ko.php');
				$this->output->AddFont($font_family, 'BI', $this->anthFontsPath . 'arialunicid0-ko.php');

			break;

			case 'arialunicid0-cj':
				$font_family = 'arialunicid0';
			break;

			default:
				//passthrough without changing font family
			break;

		}

		$this->options['font-face'] = $font_family;

		$this->output->SetFont($this->options['font-face'], '', $this->baseH, '', true);

		$this->output->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->output->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->output->SetFooterMargin(PDF_MARGIN_FOOTER);
		$this->output->setHeaderTemplateAutoreset(true);

		$this->set_header(array('logo'=>$this->headerLogo, 'logo_width'=>$this->headerLogoWidth));

		$this->partH = $this->baseH + 4;
		$this->itemH = $this->baseH + 2;
	}

	/**
	 * Add the front pages. (cover, dedication & acknowledgements)
	 */
	public function appendFront() {
		// Title and author
		$this->output->SetCreator("Anthologize: A One Week | One Tool Production");
		$this->output->SetAuthor($this->project->author());
		$this->output->SetTitle($this->project->title());

		// Cover
		$this->frontPages++;
		$this->output->AddPage();

		$this->output->SetY(80);
		$this->output->Write('', $this->project->title(), '', false, 'C', true );
		$this->output->setFont($this->options['font-face'], '', $this->baseH);

		$this->output->Write('', $this->project->author(), '', false, 'C', true );
		$this->output->SetY(120);
		$this->output->Write('', $this->project->copyright().' -- '.$this->project->year() , '', false, 'C', true );
        $this->output->endPage();


		$dedication = $this->project->dedication(true);
		if ($dedication !== "")
		{
			$this->frontPages++;

			$this->output->AddPage();
			$this->output->setFont('', 'B', $this->partH);
			$this->output->write('', __("Dedication", 'anthologize'), '', false, 'C', true);
			$this->output->setFont($this->options['font-face'], '', $this->baseH);
			$this->output->writeHTML($dedication);
			$this->output->endPage();
		}

		$acknowledgements = $this->project->acknowledgements(true);
		if ($acknowledgements !== "")
		{
			$this->frontPages++;

			$this->output->AddPage();
			$this->output->setFont('', 'B', $this->partH);
			$this->output->write('', __("Acknowledgements", 'anthologize'), '', false, 'C', true);
			$this->output->setFont($this->options['font-face'], '', $this->baseH);
			$this->output->writeHTML($acknowledgements);
			$this->output->endPage();
		}
	}

	/**
	 * Add the meat and potatoes (the parts & posts) into the output.
	 */
	public function appendBody()
	{
		$this->output->startPageGroup();
		$this->output->setPrintHeader(true);

		// Make sure there are parts to add
		if (count($this->project->parts() > 0))
		{
			$num = 0;
			foreach ($this->project->parts() as $part)
			{
				// Always break at the first part no matter what...
				$break = $num == 0 OR isset($this->options['break-parts']);

				$this->appendPart($part, $break);
				$num += 1;
			}
		}
	}

	/**
	 * Appends the back information
	 * 
	 * (not sure what the "back" part consists of... DW
	 */
	public function appendBack()
	{
		$this->output->setPrintHeader(true);
	}

	/**
	 * Adds a project part to the pdf.
	 *
	 * @param Anthologize_API_Part $part  The part to add to the pdf
	 * @param boolean              $break Add a page break?
	 */
	public function appendPart(Anthologize_API_Part $part, $break = true)
	{
		// Sets the header information
		$this->set_header(array(
			'title' => $part->title(),
		));

		if ($break)
		{
			$this->output->AddPage();
		}

		// Set a bookmark
		$this->output->Bookmark($part->title());

		//TCPDF seems to add the footer to prev. page if AddPage hasn't been fired
		$this->output->setPrintFooter(true);

		//add the header info
		$this->appendHeading($part->title(), $this->partH, $this->baseH);

		if (count($part->posts()) > 0)
		{
			foreach ($part->posts() as $post)
			{
				$this->appendItem($post, isset($this->options['break-items']));
			}
		}
	}

	/**
	 * Adds a post to the pdf.
	 *
	 * @param Anthologize_API_Post $post   The post to add to the pdf
	 * @param boolean              $break  Page break?
	 */
	public function appendItem(Anthologize_API_Post $post, $break = false)
	{
        $this->set_header(array(
			'string' => $post->title()
		));

		if($break)
		{
			$this->output->AddPage();
		}

		$this->output->Bookmark($post->title(), 1);

		$this->appendHeading($post->title(), $this->itemH, $this->baseH);
		$this->output->writeHTML($post->content($this->options['do-shortcodes'] === '1'), true, false, true);

		/**
		 * TODO: Add in comments if requested
		 *
		if (isset($this->options['include_comments']))
		{
		}
		*/
	}

	/**
	 * Adds a heading to the output
	 *
	 * @param string $heading   The heading string
	 * @param string $size      The font size
	 * @param string $reset     The font size to reset to after the heading is added
	 */
	public function appendHeading($heading, $size, $reset)
	{
		$this->output->setFont($this->options['font-face'], 'B', $size);
		$this->output->Write('', $heading, '', false, 'C', true );
		$this->output->setFont($this->options['font-face'], '', $reset);
	}

	/**
	 * Finish up the pdf and add the Table of contents
	 */
	public function finish() {
        $this->output->endPage();

		$this->output->setPrintHeader(false);
		$this->output->setPrintFooter(false);
		$this->output->addTOCPage();
		$this->output->Write(0, __('Table of Contents', "anthologize"), '', false, 'C', true);
		
		$this->output->addTOC($this->frontPages + 1, '', '', __('Table of Contents', 'anthologize'));
		$this->output->endTOCPage();
	}

	private function _boldSetting() {
		//some font families fail on trying to make bold
		switch ($this->options['font-face']) {
			case 'arialunicid0-ko':
				return '';
			break;

			default:
				return 'B';
			break;
		}
	}

	/**
	 * Sets data for the header.
	 *
	 * @param array $array 
	 */
	protected function set_header($array)
	{
		$header = $this->output->getHeaderData();

		foreach ($array as $key => $value)
		{
			$header[$key] = $value;
		}

		$this->output->setHeaderData($header['logo'], $header['logo_width'], $header['title'], $header['string']);
	}

}
