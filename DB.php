<?php
	/**
	 * DB.inc
	 * Contains the DB class (extends the PHP PDO class)
	 *
	 * @author Cory Gehr
	 */
	
class DB extends PDO
{
	// Private Class Variables
	private $engine; 
	private $host; 
	private $database; 
	private $user; 
	private $pass;

	/* __construct()
	 * Constructor for the DB Class
	 */
	public function __construct()
	{ 
		$this->engine = 'mysql'; 
		$this->host = 'localhost'; 
		$this->database = 'thinker'; 
		$this->user = 'root'; 
		$this->pass = '';
		$ds = $this->engine . ":dbname=" . $this->database . ";host=" . $this->host;
		
		// Construct the DB Object using PDO's __construct() function
		try
		{
			// '@' supresses the error this would throw -- we want to use our own error message
			@parent::__construct($ds, $this->user, $this->pass);
		}
		catch(PDOException $err)
		{
			die("Unable to connect to the database server: " . $err->getMessage());
		}
	}
}
?>
