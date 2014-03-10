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
	private $Columns;

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
			$this->Columns = $this->fetchTableColumns($schemaName, $tableName);
		}
		else
		{
			// Throw error
			trigger_error("Table '$tableName' in Schema '$schemaName' does not exist");
		}
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
	 * getColumn()
	 * Returns a specific instance of a table column object 
	 *
	 * @access public
	 * @param $columnName: Name of the Column
	 * @return THINKER_Object_Column Object
	 */
	public function getColumn($columnName)
	{
		if(isset($this->Columns[$columnName]))
		{
			return $this->Columns[$columnName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * getTableColumnNames()
	 * Returns an array of this table's column names
	 *
	 * @access public
	 * @return Array of Column Names
	 */
	public function getTableColumnNames()
	{
		$output = array();

		// Names are contained in each index
		foreach($this->Columns as $index => $object)
		{
			$output[] = $index;
		}

		return $output;
	}

	/**
	 * getTableColumns()
	 * Returns an array of all the table columns
	 *
	 * @access public
	 * @return Array of Table Columns
	 */
	public function getTableColumns()
	{
		return $this->Columns;
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
}