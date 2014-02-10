<?php
	/**
	 * Start.php
	 * Contains the Class for the Start Section
	 *
	 * @author Cory Gehr
	 */

class THINKER_Section_Start extends THINKER_Section
{
	/**
	 * info()
	 * Passes data back for the 'info' subsection
	 *
	 * @access public
	 */
	public function info()
	{
		$message = "Welcome to the THINKer. How can I assist you today?";

		$this->set('message', $message);

		return true;
	}
}
?>