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
	private $Tables;

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
			// Load schema tables
			$this->schemaName = $schemaName;
			$this->Tables = $this->fetchSchemaTables($schemaName);
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
	 * Returns an array of all table names in the current schema
	 *
	 * @access public
	 * @return Array of Table Names (Format: Array(Table Name, Friendly Name))
	 */
	public function getSchemaTableNames()
	{
		$output = array();

		// Names are contained in each index
		foreach($this->Tables as $index => $Object)
		{
			$output[] = array($index, $Object->getTableFriendlyName());
		}

		return $output;
	}

	/**
	 * getSchemaTables()
	 * Returns an array of the tables contained in the current schema
	 *
	 * @access public
	 * @return Array of THINKER_Object_Table Objects
	 */
	public function getSchemaTables()
	{
		return $this->Tables;
	}

	/**
	 * getTable()
	 * Returns a specific instance of a table object
	 *
	 * @access public
	 * @param $tableName: Name of the Table
	 * @return THINKER_Object_Table Object
	 */
	public function getTable($tableName)
	{
		if(isset($this->Tables[$tableName]))
		{
			return $this->Tables[$tableName];
		}
		else
		{
			return null;
		}
	}
}