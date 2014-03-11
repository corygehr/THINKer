<?php
	/**
	 * Object/Column.php 
	 * Contains the THINKER_Object_Column class
	 *
	 * @author Cory Gehr
	 */
	 
class THINKER_Object_Column extends THINKER_Object
{
	private $columnName;
	private $columnDefault;
	private $columnNullable;
	private $columnType;
	private $columnMaxLength;
	private $columnComment;
	private $columnTable;
	private $columnSchema;
	
	/**
	 * __construct()
	 * Constructor for the THINKER_Object_Column Class
	 *
	 * @author Cory Gehr
	 * @access public
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @param $columnName: Column Name
	 */
	public function __construct($schemaName, $tableName, $columnName)
	{
		global $_DB;

		// Call parent constructor
		parent::__construct();

		// Query for Table Existence
		$query = "SELECT COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, 
				  CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, 
				  COLUMN_COMMENT
				  FROM INFORMATION_SCHEMA.COLUMNS 
				  WHERE TABLE_SCHEMA = :schemaName 
				  AND TABLE_NAME = :tableName 
				  AND COLUMN_NAME = :columnName 
				  LIMIT 1";
		$params = array(':schemaName' => $schemaName, ':tableName' => $tableName, ':columnName' => $columnName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);

		// Will only contain one row
		$result = $statement->fetch();

		if($result)
		{
			// Load data into object
			$this->columnSchema = $schemaName;
			$this->columnTable = $tableName;
			$this->columnName = $columnName;
			$this->columnDefault = $result['COLUMN_DEFAULT'];

			if($result['IS_NULLABLE'] == 'Yes')
			{
				$this->columnNullable = true;
			}
			else
			{
				$this->columnNullable = false;
			}

			$this->columnType = $result['DATA_TYPE'];

			if($result['CHARACTER_MAXIMUM_LENGTH'])
			{
				$this->columnMaxLength = $result['CHARACTER_MAXIMUM_LENGTH'];
			}
			elseif($result['NUMERIC_PRECISION'])
			{
				$this->columnMaxLength = $result['NUMERIC_PRECISION'];
			}
			else
			{
				$this->columnMaxLength = null;
			}

			$this->columnComment = $result['COLUMN_COMMENT'];
		}
		else
		{
			// Throw error
			trigger_error("Column '$columnName' in Table '$tableName' in Schema '$schemaName' does not exist");
		}
	}

	/**
	 * getColumnComment()
	 * Returns the comment on the current column
	 *
	 * @access public
	 * @return Column Comment
	 */
	public function getColumnComment()
	{
		return $this->columnComment;
	}

	/**
	 * getColumnDefaultValue()
	 * Returns the default value of the current column
	 *
	 * @access public
	 * @return Column Default Value
	 */
	public function getColumnDefaultValue()
	{
		return $this->columnDefault;
	}

	/**
	 * getColumnFriendlyName()
	 * Returns the column comment if available, or else returns the column name
	 *
	 * @access public
	 * @return Column Friendly Name
	 */
	public function getColumnFriendlyName()
	{
		if($this->columnComment)
		{
			return $this->columnComment;
		}
		else
		{
			return $this->columnName;
		}
	}

	/**
	 * getColumnMaxLength()
	 * Returns the maximum length of the current column
	 *
	 * @access public
	 * @return Max Column Length
	 */
	public function getColumnMaxLength()
	{
		return $this->columnMaxLength;
	}

	/**
	 * getColumnName()
	 * Returns the name of the current column
	 *
	 * @access public
	 * @return Column Name
	 */
	public function getColumnName()
	{
		return $this->columnName;
	}

	/**
	 * getColumnSchema()
	 * Returns the name of the column's schema
	 *
	 * @access public
	 * @return Column Schema
	 */
	public function getColumnSchema()
	{
		return $this->columnSchema;
	}

	/**
	 * getColumnTable()
	 * Returns the name of the column's table
	 *
	 * @access public
	 * @return Column Table
	 */
	public function getColumnTable()
	{
		return $this->columnTable;
	}

	/**
	 * getColumnType()
	 * Returns the data type of the current column
	 *
	 * @access public
	 * @return Column Type
	 */
	public function getColumnType()
	{
		return $this->columnType;
	}

	/**
	 * isNullable()
	 * Returns the nullable status of the column
	 *
	 * @access public
	 * @return Column Nullable Status
	 */
	public function isNullable()
	{
		return $this->columnNullable;
	}
}