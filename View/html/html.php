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
		global $_MESSAGES, $_SECTION, $_SUBSECTION;

		// Get the filename for the section's action template
		$tplFile = 'Section/' . $_SECTION . '/html/' . $_SUBSECTION . '.tpl';

		// Ensure file exists
		if(!file_exists($tplFile))
		{
			// Redirect to error
			redirect('Error', 'info', array('no' => 1000));
		}

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

		// Output messages
		if(!empty($_MESSAGES))
		{
			foreach($_MESSAGES as $msg)
			{
				list($text, $level) = $msg;

				$output = "<div class='notification ";

				switch($level)
				{
					case 'error':
						$output .= "error'><strong>Error:</strong> $text</div>";
					break;

					case 'success':
						$output .= "success'><strong>Success:</strong> $text</div>";
					break;

					case 'warning':
						$output .= "warning'><strong>Warning:</strong> $text</div>";
					break;

					default:
						$output .= "info'><strong>Info:</strong> $text</div>";
					break;
				}
			}
		}

		// Include body data
		include_once($tplFile);

		// Include footer
		include_once('View/html/bodyFooter.inc');
?>
</body>
</html>
<?php
	}

	/**
	 * get()
	 * Gets an item from the $data array, if it exists
	 *
	 * @access private
	 * @param $name: Index Name
	 * @param $nullBehavior: Error display behavior if item doesn't exist (default: 'suppress')
	 * @param $customError: Custom error to display if item doesn't exist (default: null)
	 * @return Data item if value exists, Error if not
	 */
	private function get($name, $nullBehavior = 'suppress', $customError = null)
	{
		// Check if item exists in $data
		$val = $this->section->getVal($name);

		if($val != null)
		{
			return $val;
		}
		else
		{
			// Error message
			if(!$customError)
			{
				$message = "['$name' is unspecified]";
			}
			else
			{
				$message = $customError;
			}

			// Throw error, based on specified behavior
			switch($nullBehavior)
			{
				case 'suppress':
					return null;
				break;

				case 'inline':
					return $message;
				break;
			}
		}
	}
}
?>