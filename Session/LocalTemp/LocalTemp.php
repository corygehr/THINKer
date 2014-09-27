<?php
	/**
	 * Session/Local/LocalTemp.php 
	 * Contains the THINKER_Session_LocalTemp class
	 *
	 * @author Cory Gehr
	 */

class THINKER_Session_LocalTemp extends THINKER_Session
{
	/**
	 * __construct()
	 * Constructor for the THINKER_Session_Local class
	 *
	 * @access protected
	 */
	protected function __construct()
	{
		// Add HttpOnly flag to ensure the cookie can only be accessed by HTTP (Secure is true on Live)
		session_set_cookie_params(0, '/', null, false, true);

		// Call parent constructor
		parent::__construct();

		// Check for authentication
		if(!isset($_SESSION['AUTH_ID']) || $_SESSION['AUTH_ID'] != 'TooManyHeartsOneHome2015')
		{
			// Redirect to login
			header('Location: login.php?phase=noAuth');
			exit();
		}
	}
}