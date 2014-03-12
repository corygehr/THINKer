<?php
	/**
	 * Object/Table.php 
	 * Contains the THINKER_Object_Table class
	 *
	 * @author Cory Gehr
	 */
	 
class THINKER_Object_Table extends THINKER_Object
{
	private $tableName;
	private $tableEngine;
	private $tableComment;
	private $tableSchema;

	/**
	 * __construct()
	 * Constructor for the THINKER_Object_Table Class
	 *
	 * @author Cory Gehr
	 * @access public
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 */
	public function __construct($schemaName, $tableName)
	{
		global $_DB;

		// Call parent constructor
		parent::__construct();

		// Query for Table Existence
		$query = "SELECT ENGINE, TABLE_COMMENT
				  FROM INFORMATION_SCHEMA.TABLES 
				  WHERE TABLE_SCHEMA = :schemaName 
				  AND TABLE_NAME = :tableName 
				  LIMIT 1";
		$params = array(':schemaName' => $schemaName, ':tableName' => $tableName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);

		// Will only contain one row
		$result = $statement->fetch();

		if($result)
		{
			// Load data into object
			$this->tableName = $tableName;
			$this->tableEngine = $result['ENGINE'];
			$this->tableComment = $result['TABLE_COMMENT'];
			$this->tableSchema = $schemaName;
		}
		else
		{
			// Throw error
			trigger_error("Table '$tableName' in Schema '$schemaName' does not exist");
		}
	}

	/**
	 * discoverRelationships()
	 * Returns the name of each table this table has Foreign Key relationships with
	 *
	 * @access public
	 * @return Array of Table Names (Format: Array(Foreign Schema, Foreign Table Name, Foreign Table Friendly Name, Key Column Name, Key Column Friendly Name, Referenced Column Name))
	 */
	public function discoverRelationships()
	{
		global $_DB;

		$query = "SELECT KCU.COLUMN_NAME, C.COLUMN_COMMENT, KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, T.TABLE_COMMENT, 
				  KCU.REFERENCED_COLUMN_NAME, 
				  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU 
				  JOIN INFORMATION_SCHEMA.COLUMNS C 
				  	ON C.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND C.TABLE_NAME = KCU.TABLE_NAME AND C.COLUMN_NAME = KCU.COLUMN_NAME 
				  JOIN INFORMATION_SCHEMA.TABLES T
				  	ON T.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND T.TABLE_NAME = KCU.REFERENCED_TABLE_NAME 
				  WHERE KCU.TABLE_SCHEMA = :schemaName 
				  AND KCU.TABLE_NAME = :tableName 
				  AND KCU.REFERENCED_TABLE_NAME IS NOT NULL 
				  ORDER BY C.ORDINAL_POSITION, KCU.COLUMN_NAME";
		$params = array(':schemaName' => $this->getTableSchema(), ':tableName' => $this->getTableName());

		$statement = $_DB->prepare($query);
		$statement->execute($params);

		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $t)
			{
				list($columnName, $columnComment, $refSchema, $refTable, $refTableComment, $refColumnName) = $t;

				$output[] = array(
					$refSchema, $refTable, $refTableComment, $columnName, $columnComment, $refColumnName
					);
			}
		}

		return $output;
	}

	/**
	 * fetchTableColumns()
	 * Returns an array of the columns contained in the specified table
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Name of the Schema
	 * @param $tableName: Name of the Table
	 * @return Array of THINKER_Object_Column Objects
	 */
	public static function fetchTableColumns($schemaName, $tableName)
	{
		global $_DB;

		// Query for table names
		$query = "SELECT COLUMN_NAME 
				  FROM INFORMATION_SCHEMA.COLUMNS 
				  WHERE TABLE_SCHEMA = :schemaName 
				  AND TABLE_NAME = :tableName 
				  ORDER BY TABLE_NAME";
		$params = array(':schemaName' => $schemaName, ':tableName' => $tableName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);
		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $c)
			{
				list($columnName) = $c;
				// Load new tables and add to the output array
				$output[$columnName] = new THINKER_Object_Column($schemaName, $tableName, $columnName);
			}
		}

		return $output;
	}

	/**
	 * getTableColumnNames()
	 * Returns an array of this table's column names
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @return Array of Column Names (Name, Friendly Name)
	 */
	public static function getTableColumnNames($schemaName, $tableName)
	{
		global $_DB;

		$query = "SELECT COLUMN_NAME, COLUMN_COMMENT 
				  FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE TABLE_SCHEMA = :schemaName 
				  AND TABLE_NAME = :tableName 
				  ORDER BY ORDINAL_POSITION, COLUMN_COMMENT, COLUMN_NAME";
		$params = array(':schemaName' => $schemaName, ':tableName' => $tableName);

		// Fetch results
		$statement = $_DB->prepare($query);
		$statement->execute($params);
		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $c)
			{
				list($columnName, $columnComment) = $c;

				if(!$columnComment)
				{
					$columnComment = $columnName;
				}

				$output[] = array($columnName, $columnComment);
			}
		}

		return $output;
	}

	/**
	 * getTableComment()
	 * Gets the Comment listed for the current Table
	 *
	 * @access public
	 * @return Table Comment
	 */
	public function getTableComment()
	{
		return $this->tableComment;
	}

	/**
	 * getTableEngine()
	 * Gets the Engine of the current Table
	 *
	 * @access public
	 * @return Table Engine
	 */
	public function getTableEngine()
	{
		return $this->tableEngine;
	}

	/**
	 * getTableFriendlyName()
	 * Returns the Table Comment if Available, or else returns the Table Name
	 *
	 * @access public
	 * @return Table Friendly Name
	 */
	public function getTableFriendlyName()
	{
		if($this->tableComment)
		{
			return $this->tableComment;
		}
		else
		{
			return $this->tableName;
		}
	}

	/**
	 * getTableName()
	 * Gets the name of the current table
	 *
	 * @access public
	 * @return Table Name
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * getTableSchema()
	 * Gets the name of the current table's schema
	 *
	 * @access public
	 * @return Schema Name
	 */
	public function getTableSchema()
	{
		return $this->tableSchema;
	}
}