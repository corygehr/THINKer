<?php
	/**
	 * Session/Cosign/Cosign.php 
	 * Contains the THINKER_Session_Cosign class
	 *
	 * @author Cory Gehr
	 */

class THINKER_Session_Cosign extends THINKER_Session
{
	/**
	 * __construct()
	 * Constructor for the THINKER_Session_Cosign class
	 *
	 * @access protected
	 */
	protected function __construct()
	{
		global $_DB;

		// Add HttpOnly flag to ensure the cookie can only be accessed by HTTP (Secure is true on Live)
		session_set_cookie_params(0, '/', null, false, true);

		// Call parent constructor
		parent::__construct();

		// Grab username from WebAccess
		if(isset($_SERVER['REMOTE_USER']))
		{
			// Ensure proper AUTH TYPE
			if($_SERVER['AUTH_TYPE'] == 'Cosign')
			{
				// See if current user is allowed to user THINKer
				$query = "SELECT COUNT(1)
						  FROM users_access 
						  WHERE username = :username 
						  LIMIT 1";
				$params = array(':username' => $_SERVER['REMOTE_USER']);

				if(!$_DB['thinker']->doQueryAns($query, $params))
				{
					// 403
					die("<html><head><title>Unauthorized | THINKer</title></head><body><h1>403: Unauthorized</h1><p>You do not have permission to access this website. If you feel this message is in error, please contact the Technology Director.</p></body>");
				}
				// Good to go past this point
			}
			else
			{
				// Fail
				die("<html><head><title>THINKer</title></head><body><h1>Authentication Failure</h1><p>The method of authentication used is invalid. Please contact the Technology Director.</p></body></html>");
			}
		}
	}
}