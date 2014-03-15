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
	private $columnFullType;
	private $columnMaxLength;
	private $columnComment;
	private $columnTable;
	private $columnSchema;

	/**
	 * createFromDB()
	 * Constructor for the THINKER_Object_Column Class that creates objects from the database
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @param $columnName: Column Name
	 * @return THINKER_Object_Column Object
	 */
	public static function createFromDB($schemaName, $tableName, $columnName)
	{
		global $_DB;

		$Object = new THINKER_Object_Column();

		// Pull column info from database
		$query = "SELECT COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE, 
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
		$result = $statement->fetch(PDO::FETCH_ASSOC);

		if($result)
		{
			// Load data into object
			$Object->setColumnSchema($schemaName);
			$Object->setColumnTable($tableName);
			$Object->setColumnName($columnName);
			$Object->setColumnDefaultValue($result['COLUMN_DEFAULT']);

			if($result['IS_NULLABLE'] == 'Yes')
			{
				$Object->setNullable(true);
			}
			else
			{
				$Object->setNullable(false);
			}

			$Object->setColumnType($result['DATA_TYPE']);
			$Object->setColumnFullType($result['COLUMN_TYPE']);

			if($result['CHARACTER_MAXIMUM_LENGTH'])
			{
				$Object->setColumnMaxLength($result['CHARACTER_MAXIMUM_LENGTH']);
			}
			elseif($result['NUMERIC_PRECISION'])
			{
				$Object->setColumnMaxLength($result['NUMERIC_PRECISION']);
			}
			else
			{
				$Object->setColumnMaxLength(null);
			}

			$Object->setColumnComment($result['COLUMN_COMMENT']);

			return $Object;
		}
		else
		{
			return false;
		}
	}

	/**
	 * createFromParams()
	 * Constructor for the THINKER_Object_Column Class that creates objects from the provided values
	 *
	 * @access public
	 * @static
	 * @param $schemaName: Schema Name
	 * @param $tableName: Table Name
	 * @param $columnName: Column Name
	 * @param $defaultValue: Column Default Value
	 * @param $nullable: Column Allows Nulls
	 * @param $dataType: Short Data Type Definition
	 * @param $fullType: Full Data Type Definition
	 * @param $maxLength: Max Length of Column
	 * @param $columnComment: Column Comment
	 * @return THINKER_Object_Column Object
	 */
	public static function createFromParams($schemaName, $tableName, $columnName, $defaultValue, 
											$nullable, $dataType, $fullType, $maxLength, $columnComment)
	{
		$Object = new THINKER_Object_Column();

		// Set values
		$Object->setColumnSchema($schemaName);
		$Object->setColumnTable($tableName);
		$Object->setColumnName($columnName);
		$Object->setColumnDefaultValue($defaultValue);
		$Object->setNullable($nullable);
		$Object->setColumnType($dataType);
		$Object->setColumnFullType($fullType);
		$Object->setColumnMaxLength($maxLength);
		$Object->setColumnComment($columnComment);

		return $Object;
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
	 * getColumnFilters()
	 * Returns an array of the filters that can be used for the column type
	 *
	 * @access public
	 * @static
	 * @param $columnType: Column short type
	 * @return Array of Filters
	 */
	public static function getColumnFilters($columnType)
	{
		$filters = array();

		switch($columnType)
		{
			case 'bigint':
			case 'decimal':
			case 'double':
			case 'float':
			case 'int':
			case 'smallint':
				$filters = array(
					'EQUALS' => '=',
					'GT' => '>',
					'GTE' => '>=',
					'LT' => '<',
					'LTE' => '<='
					);
			break;

			case 'date':
			case 'datetime':
			case 'time':
			case 'timestamp':
				$filters = array(
					'BEFORE' => 'Before',
					'BEFORE_INCL' => 'Before (Inclusive)',
					'AFTER' => 'After',
					'AFTER_INCL' => 'After (Inclusive)',
					'EQUALS' => 'Equals'
					);
			break;

			case 'tinyint':
				$filters = array(
					'FALSE' => 'False',
					'TRUE' => 'True'
					);
			break;

			default:
				// Varchar, Char, Enum Typically
				$filters = array(
					'CONTAINS' => 'Contains',
					'EQUALS' => 'Equals',
					'LIKE' => 'Like',
					'STARTS_WITH' => 'Starts With',
					'ENDS_WITH' => 'Ends With'
					);
			break;
		}

		return $filters;
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
	 * getColumnFullType()
	 * Returns the full data type of the current column
	 *
	 * @access public
	 * @return Column Full Type
	 */
	public function getColumnFullType()
	{
		return $this->columnFullType;
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
	 * getFilter()
	 * Returns the value of the specified filter
	 *
	 * @access public
	 * @static
	 * @param $filter: Filter Name
	 * @param $value: Inputted value
	 * @return Filter Value
	 */
	public static function getFilterValue($filter, $value)
	{
		switch($filter)
		{
			case 'GT':
				return '>';
			break;

			case 'LT':

			break;
		}
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

	/**
	 * setColumnComment()
	 * Sets the comment on the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Comment
	 */
	public function setColumnComment($value)
	{
		$this->columnComment = $value;
		return $this->columnComment;
	}

	/**
	 * setColumnDefaultValue()
	 * Sets the default value of the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Default Value
	 */
	public function setColumnDefaultValue($value)
	{
		$this->columnDefault = $value;
		return $this->columnDefault;
	}

	/**
	 * setColumnFullType()
	 * Sets the full data type of the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Full Type
	 */
	public function setColumnFullType($value)
	{
		$this->columnFullType = $value;
		return $this->columnFullType;
	}

	/**
	 * setColumnMaxLength()
	 * Sets the maximum length of the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Max Column Length
	 */
	public function setColumnMaxLength($value)
	{
		$this->columnMaxLength = $value;
		return $this->columnMaxLength;
	}

	/**
	 * setColumnName()
	 * Sets the name of the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Name
	 */
	public function setColumnName($value)
	{
		$this->columnName = $value;
		return $this->columnName;
	}

	/**
	 * setColumnSchema()
	 * Sets the name of the column's schema
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Schema
	 */
	public function setColumnSchema($value)
	{
		$this->columnSchema = $value;
		return $this->columnSchema;
	}

	/**
	 * setColumnTable()
	 * Sets the name of the column's table
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Table
	 */
	public function setColumnTable($value)
	{
		$this->columnTable = $value;
		return $this->columnTable;
	}

	/**
	 * setColumnType()
	 * Sets the data type of the current column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Type
	 */
	public function setColumnType($value)
	{
		$this->columnType = $value;
		return $this->columnType;
	}

	/**
	 * setNullable()
	 * Sets the nullable status of the column
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Column Nullable Status
	 */
	public function setNullable($value)
	{
		$this->columnNullable = $value;
		return $this->columnNullable;
	}
}