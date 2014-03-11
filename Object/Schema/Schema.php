<?php
	/**
	 * Object/Schema.php 
	 * Contains the THINKER_Object_Schema class
	 *
	 * @author Cory Gehr
	 */
	 
class THINKER_Object_Schema extends THINKER_Object
{
	private $schemaName;

	/**
	 * __construct()
	 * Constructor for the THINKER_Object_Schema Class
	 *
	 * @author Cory Gehr
	 * @access public
	 * @param $schemaName: Schema Name
	 */
	public function __construct($schemaName)
	{
		global $_DB;

		// Call parent constructor
		parent::__construct();

		// Query for Schema Existence
		$query = "SELECT COUNT(1)
				  FROM INFORMATION_SCHEMA.SCHEMATA 
				  WHERE SCHEMA_NAME = :schemaName 
				  LIMIT 1";
		$params = array(':schemaName' => $schemaName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);

		if($statement->fetchColumn(0) == 1)
		{
			// Load data into object
			$this->schemaName = $schemaName;
		}
		else
		{
			// Throw error
			trigger_error("Schema '$schemaName' does not exist");
		}
	}
	
	/**
	 * fetchSchemaTables()
	 * Returns an array of the tables contained in the specified schema
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Name of the Schema
	 * @return Array of THINKER_Object_Table Objects
	 */
	public static function fetchSchemaTables($schemaName)
	{
		global $_DB;

		// Query for table names
		$query = "SELECT TABLE_NAME
				  FROM INFORMATION_SCHEMA.TABLES
				  WHERE TABLE_SCHEMA = :schemaName 
				  ORDER BY TABLE_NAME";
		$params = array(':schemaName' => $schemaName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);
		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $t)
			{
				list($tableName) = $t;
				// Load new tables and add to the output array
				$output[$tableName] = new THINKER_Object_Table($schemaName, $tableName);
			}
		}

		return $output;
	}

	/**
	 * getLocalSchemata()
	 * Gets all of the local schemata on this server
	 *
	 * @access public
	 * @static
	 * @return Array of Schemata
	 */
	public static function getLocalSchemata()
	{
		global $_DB;

		$query = "SELECT SCHEMA_NAME
				  FROM INFORMATION_SCHEMA.SCHEMATA
				  WHERE SCHEMA_NAME NOT IN ('INFORMATION_SCHEMA', 'PERFORMANCE_SCHEMA', 'mysql', 'thinker', 'phpmyadmin')
				  ORDER BY SCHEMA_NAME";

		// Fetch results
		$statement = $_DB->prepare($query);
		$statement->execute();
		$results = $statement->fetchAll();

		$output = array();

		foreach($results as $s)
		{
			list($schemaName) = $s;
			$output[] = $s;
		}

		return $output;
	}

	/**
	 * getSchemaName()
	 * Gets the name of the current schema
	 *
	 * @access public
	 * @return Schema Name
	 */
	public function getSchemaName()
	{
		return $this->schemaName;
	}

	/**
	 * getSchemaTableNames()
	 * Returns a list of the tables in the selected schema
	 * Cannot load locally, as this causes performance issues
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @return Array of Schema Tables (Format: Array(Table Name, Friendly Name))
	 */
	public static function getSchemaTableNames($schemaName)
	{
		global $_DB;

		$query = "SELECT TABLE_NAME, TABLE_COMMENT
				  FROM INFORMATION_SCHEMA.TABLES
				  WHERE TABLE_SCHEMA = :schemaName 
				  ORDER BY TABLE_COMMENT, TABLE_NAME";
		$params = array(':schemaName' => $schemaName);

		// Fetch results
		$statement = $_DB->prepare($query);
		$statement->execute($params);
		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $t)
			{
				list($tableName, $tableComment) = $t;

				if(!$tableComment)
				{
					$tableComment = $tableName;
				}

				$output[] = array($tableName, $tableComment);
			}
		}

		return $output;
	}
}