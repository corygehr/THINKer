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
	private $tableComment;
	private $tableSchema;

	/**
	 * createFromDB()
	 * Constructor for the THINKER_Object_Table Class that gets properties from the database
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @return THINKER_Object_Table Object
	 */
	public static function createFromDB($schemaName, $tableName)
	{
		global $_DB;

		$Object = new THINKER_Object_Table();

		// Query for Table Existence
		$query = "SELECT TABLE_COMMENT
				  FROM INFORMATION_SCHEMA.TABLES 
				  WHERE TABLE_SCHEMA = :schemaName 
				  AND TABLE_NAME = :tableName 
				  LIMIT 1";
		$params = array(':schemaName' => $schemaName, ':tableName' => $tableName);

		$statement = $_DB['thinker']->prepare($query);
		$statement->execute($params);

		// Will only contain one row
		$result = $statement->fetch();

		if($result)
		{
			// Load data into object
			$Object->setTableName($tableName);
			$Object->setTableComment($result['TABLE_COMMENT']);
			$Object->setTableSchema($schemaName);

			return $Object;
		}
		else
		{
			return false;
		}
	}

	/**
	 * createFromParams()
	 * Constructor for the THINKER_Object_Table Class that gets properties from the provided values
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @param $tableComment: Table Comment
	 * @return THINKER_Object_Table Object
	 */
	public static function createFromParams($schemaName, $tableName, $tableComment)
	{
		$Object = new THINKER_Object_Table();

		// Set values
		$Object->setTableSchema($schemaName);
		$Object->setTableName($tableName);
		$Object->setTableComment($tableComment);

		return $Object;
	}

	/**
	 * discoverRelationships()
	 * Returns the information about each Foreign Key relationship with other tables
	 *
	 * @access public
	 * @return Array of THINKER_Object_Relationship Objects
	 */
	public function discoverRelationships()
	{
		global $_DB;

		$query = "SELECT KCU.CONSTRAINT_NAME, KCU.COLUMN_NAME, C.COLUMN_COMMENT, KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, 
				  T.TABLE_COMMENT, KCU.REFERENCED_COLUMN_NAME, C1.COLUMN_COMMENT
				  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU 
				  JOIN INFORMATION_SCHEMA.COLUMNS C 
				  	ON C.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND C.TABLE_NAME = KCU.TABLE_NAME AND C.COLUMN_NAME = KCU.COLUMN_NAME 
				  JOIN INFORMATION_SCHEMA.COLUMNS C1
				    ON C1.TABLE_SCHEMA = KCU.REFERENCED_TABLE_SCHEMA AND C1.TABLE_NAME = KCU.REFERENCED_TABLE_NAME AND C1.COLUMN_NAME = KCU.REFERENCED_COLUMN_NAME 
				  JOIN INFORMATION_SCHEMA.TABLES T
				  	ON T.TABLE_SCHEMA = KCU.REFERENCED_TABLE_SCHEMA AND T.TABLE_NAME = KCU.REFERENCED_TABLE_NAME 
				  WHERE KCU.TABLE_SCHEMA = :schemaName 
				  AND KCU.TABLE_NAME = :tableName 
				  AND KCU.REFERENCED_TABLE_NAME IS NOT NULL 
				  ORDER BY C.ORDINAL_POSITION, KCU.COLUMN_NAME";
		$params = array(':schemaName' => $this->getTableSchema(), ':tableName' => $this->getTableName());

		$statement = $_DB['thinker']->prepare($query);
		$statement->execute($params);

		$results = $statement->fetchAll(PDO::FETCH_NUM);

		$output = array();

		if($results)
		{
			foreach($results as $t)
			{
				list($relationName, $columnName, $columnComment, $refSchema, $refTable, $refTableComment, $refColumnName, $refColumnComment) = $t;

				$output[] = THINKER_Object_Relationship::createFromParams($relationName, $this->getTableSchema(), $this->getTableName(), $this->getTableFriendlyName(), 
					$columnName, $columnComment, $refSchema, $refTable, $refTableComment, $refColumnName, $refColumnComment);
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
		$statement = $_DB['thinker']->prepare($query);
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
			return $this->getTableName();
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

	/**
	 * setTableComment()
	 * Sets the Comment listed for the current Table
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Table Comment
	 */
	public function setTableComment($value)
	{
		$this->tableComment = $value;
		return $this->tableComment;
	}

	/**
	 * setTableName()
	 * Sets the name of the current table
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Table Name
	 */
	public function setTableName($value)
	{
		$this->tableName = $value;
		return $this->tableName;
	}

	/**
	 * setTableSchema()
	 * Sets the name of the current table's schema
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Schema Name
	 */
	public function setTableSchema($value)
	{
		$this->tableSchema = $value;
		return $this->tableSchema;
	}
}