<?php
    /**
	 * json.php
	 * Contains the THINKER_View_json class
	 *
	 * @author Cory Gehr
	 * @version 0.1
	 */

class THINKER_View_json extends THINKER_View_Common
{
    /**
	 * display()
	 * Outputs the data
	 *
	 * @access public
     */
    public function display()
    {
	    // Display the header
        header('Content-type: application/json; charset=utf-8');
		
		// Ensure the json_encode() function exists
        if (!function_exists('json_encode'))
		{
            echo '{"msg":"Function json_encode() does not exist.","info":"php-json is probably not installed."}';
        }
		else
		{
		    // Return the encoded data
            echo json_encode($this->model->getData());
        }
    }
}

?>
