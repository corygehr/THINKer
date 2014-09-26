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
	 * createFromDB()
	 * Constructor for the THINKER_Object_Schema Class that gets properties from the database
	 * Currently useless, except for validating schema actually exists
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @return THINKER_Object_Schema Object
	 */
	public static function createFromDB($schemaName)
	{
		global $_DB;

		$Object = new THINKER_Object_Schema();

		// Query for Schema
		$query = "SELECT COUNT(1)
				  FROM INFORMATION_SCHEMA.SCHEMATA 
				  WHERE SCHEMA_NAME = :schemaName 
				  LIMIT 1";
		$params = array(':schemaName' => $schemaName);

		$statement = $_DB['thinker']->prepare($query);
		$statement->execute($params);

		if($statement->fetchColumn(0) == 1)
		{
			// Load data into object
			$Object->schemaName = $schemaName;

			return $Object;
		}
		else
		{
			return false;
		}
	}

	/**
	 * createFromParams()
	 * Constructor for the THINKER_Object_Schema Class that gets properties from the specified values
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @return THINKER_Object_Schema Object
	 */
	public static function createFromParams($schemaName)
	{
		$Object = new THINKER_Object_Schema();

		// Set values
		$Object->schemaName = $schemaName;

		return $Object;
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
		$statement = $_DB['thinker']->prepare($query);
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
		$statement = $_DB['thinker']->prepare($query);
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

	/**
	 * setSchemaName()
	 * Sets the name of the current schema
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Property Value
	 */
	public function setSchemaName($value)
	{
		$this->schemaName = $value;
		return $this->schemaName();
	}
}