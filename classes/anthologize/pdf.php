<?php defined("ANTHOLOGIZE") or die("No direct script access.");

// Set our own tcpdf config
define('K_TCPDF_EXTERNAL_CONFIG', true);
include Anthologize::find_file('vendor', "tcpdf/classes/config/tcpdf_config_anthologize");

include Anthologize::find_file("vendor", 'tcpdf/classes/tcpdf');

/**
 * Extended PDF class for TCPDF.
 *
 * @package      Anthologize
 * @author       One Week | One Tool
 * @copyright    Copyright (C) 2010 Center for History and New Media, George Mason University
 */
class Anthologize_PDF extends TCPDF{}