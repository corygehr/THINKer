<?php
	/**
	 * index.php
	 * Primary Controller for THINKer
	 * This page handles most of the page load logic
	 * The rest is contains in the individual Model files
	 * 
	 * @author Cory Gehr <gehrc621@gmail.com>
	 */

// Include required files
require_once('includes/globals.inc');
require_once('includes/coreFunctions.inc');
require_once('includes/dbconnect.inc');

// Initialize system globals
$config = parse_ini_file('includes/sysconfig.ini', true);

// Set globals
define('BASE_URL', ($config['thinker_general']['use_ssl'] == true ? 'https://' : 'http://') . $config['thinker_general']['base_url'] . '/');
define('ENVIRONMENT', $config['thinker_general']['environment']);
define('CLASS_PREFIX', $config['thinker_general']['class_prefix']);
define('DEFAULT_VIEW', $config['thinker_general']['default_view']);

// Set error handler
set_error_handler('thinkerErrorHandler');

// Connect to database
$_DB = new DBConnection();

/******************
  Controller Start  
*******************/

// Load specified section
if(isset($_GET['section']))
{
	$_SECTION = getPageVar('section', 'str', 'GET', true);
}
else
{
	$_SECTION = $config['thinker_general']['default_section'];
}

// Assign section class
$_SECTION_CLASS = CLASS_PREFIX . 'Section_' . $_SECTION;

// Get path to section
$sectionPath = getPath($_SECTION_CLASS);
$sectionFile = $sectionPath . "$_SECTION.php";

// Check if the section specified exists
if(file_exists($sectionFile))
{
	require_once($sectionFile);

	// Create instance of section
	$instance = new $_SECTION_CLASS();

	// Load section configuration
	$sectionConfig = parse_ini_file($sectionPath . 'config.ini', true);

	// Check for sub-section
	if(isset($_GET['subsection']))
	{
		$_SUBSECTION = getPageVar('subsection', 'str', 'GET', true);
	}
	else
	{
		// Load subsection from config
		if(isset($sectionConfig['defaults']['default_subsection']))
		{
			$_SUBSECTION = $sectionConfig['defaults']['default_subsection'];
		}
		else
		{
			trigger_error("Could not determine the subsection to load.", E_USER_ERROR);
		}
	}

	// Attempt to load subsection
	if(method_exists($_SECTION_CLASS, $_SUBSECTION))
	{
		// Call method
		$result = $instance->$_SUBSECTION();
	}
	else
	{
		trigger_error("Subsection does not exist.", E_USER_ERROR);
	}

	// Compile info array
	$_INFO['environment'] = $config['thinker_general']['environment'];
	$_INFO['section'] = $_SECTION;
	$_INFO['subsection'] = $_SUBSECTION;

	if($result)
	{
		// Create a view
		$view = THINKER_View::factory($instance->view, $instance);

		if($view)
		{
			$view->display();
		}
		else
		{
			trigger_error("Failed to generate the View.", E_USER_ERROR);
		}
	}
	else
	{
		trigger_error("Subsection did not load successfully.", E_USER_ERROR);
	}
}
else
{
	// Throw error
	trigger_error("Page not found.", E_USER_ERROR);
}

// Close database connection
$_DB = null;

?>