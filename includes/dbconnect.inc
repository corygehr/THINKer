<?php
	/**
	 * dbconnect.inc
	 * Contains the connection information for connecting to the database
	 *
	 * @author Cory Gehr
	 */

class DBConnection extends PDO 
{
	/**
	 * __construct()
	 * Constructor for the database connection class
	 *
	 * @access public
	 */
	public function __construct()
	{
		// Create connection based on global configuration variables
		$connectParams = parse_ini_file('includes/dbconfig.ini', true);

		// Get desired configuration options
		$user = $connectParams['db_credentials']['user'];
		$pass = $connectParams['db_credentials']['password'];
		$type = $connectParams['db_credentials']['driver'];

		// DSN Properties
		$host = $connectParams['db_dsn']['host'];
		$port = $connectParams['db_dsn']['port'];
		$schema = $connectParams['db_dsn']['schema'];

		// PDO Options
		$options = $connectParams['db_pdo_options'];

		// Data Source String
		$dsn = $type . ":dbname=$schema;host=$host;port=$port";

		// Attempt to make connection
		try
		{
			// '@' suppresses errors from this function. We output our own message if there's a problem
			@parent::__construct($dsn, $user, $pass, $options);
		}
		catch(PDOException $err)
		{
			die("Unable to connect to the database server. Details: " . $err->getMessage() . ".");
		}
	}
}
?>