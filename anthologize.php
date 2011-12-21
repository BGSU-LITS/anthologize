<?php
/*
Plugin Name: Anthologize
Plugin URI: http://anthologize.org
Description: Use the power of WordPress to transform your content into a book.
Version: 0.6.2-alpha
Author: One Week | One Tool
Author URI: http://oneweekonetool.org
*/

/*
Copyright (C) 2010 Center for History and New Media, George Mason University

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.

Anthologize includes TCPDF, which is released under the LGPL Use and
modifications of TDPDF must comply with its license.
*/

// Set the anthologize path
define('ANTHOLOGIZE', dirname(__FILE__).DIRECTORY_SEPARATOR);

include ANTHOLOGIZE."classes/anthologize.php";
spl_autoload_register(array('Anthologize', 'autoload'));

// We are in Wordpress, so boot that bad boy up
Anthologize_Wordpress::bootstrap();
