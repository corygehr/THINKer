<?php
	/**
	 * html.php
	 * Contains the THINKER_View_json class
	 *
	 * @author Cory Gehr
	 */

class THINKER_View_html extends THINKER_View_Common
{
	/**
	 * display()
	 * Outputs the HTML
	 *
	 * @access public
	 */
	public function display()
	{
		// Start HTML output
?>
<!DOCTYPE html>
<html>
<head>
<?php
		// Include head items
		include_once('View/html/head.inc');
?>
</head>
<body>
<?php
		// Include heading
		include_once('View/html/bodyHeading.inc');

		// Include the HTML for the section/subsection
		print_r($this->section->getData());

		// Include footer
		include_once('View/html/bodyFooter.inc');
?>
</body>
</html>
<?php
	}
}
?>