<?php
	/**
	 * Object/Relationship.php 
	 * Contains the THINKER_Object_Relationship class
	 *
	 * @author Cory Gehr
	 */
	 
class THINKER_Object_Relationship extends THINKER_Object
{
	private $relationName;
	private $sourceSchema;
	private $sourceTable;
	private $sourceTableName;
	private $sourceColumn;
	private $sourceColumnName;
	private $refSchema;
	private $refTable;
	private $refTableName;
	private $refColumn;
	private $refColumnName;

	/**
	 * createFromDB()
	 * Constructor for the THINKER_Object_Relationship Class that uses the database to create an object
	 *
	 * @access public
	 * @static
	 * @param $sourceSchema: Source Schema Name
	 * @param $sourceTable: Source Table Name
	 * @param $relationshipName: Relationship Name
	 * @return THINKER_Object_Relationship Object
	 */
	public static function createFromDB($sourceSchema, $sourceTable, $relationshipName)
	{
		global $_DB;

		$Object = new THINKER_Object_Relationship();

		// Get values from the database
		$query = "SELECT T.TABLE_COMMENT, KCU.COLUMN_NAME, C.COLUMN_COMMENT, KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, 
				  T1.TABLE_COMMENT AS 'REFFERENCED_TABLE_COMMENT', KCU.REFERENCED_COLUMN_NAME, C1.COLUMN_COMMENT AS 'REFERENCED_COLUMN_COMMENT'
				  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU 
				  JOIN INFORMATION_SCHEMA.COLUMNS C 
				  	ON C.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND C.TABLE_NAME = KCU.TABLE_NAME AND C.COLUMN_NAME = KCU.COLUMN_NAME 
				  JOIN INFORMATION_SCHEMA.COLUMNS C1
				    ON C1.TABLE_SCHEMA = KCU.REFERENCED_TABLE_SCHEMA AND C1.TABLE_NAME = KCU.REFERENCED_TABLE_NAME AND C1.COLUMN_NAME = KCU.REFERENCED_COLUMN_NAME 
				  JOIN INFORMATION_SCHEMA.TABLES T
				  	ON T.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND T.TABLE_NAME = KCU.REFERENCED_TABLE_NAME 
				  JOIN INFORMATION_SCHEMA.TABLES T1
				  	ON T1.TABLE_SCHEMA = KCU.REFERENCED_TABLE_SCHEMA AND T1.TABLE_NAME = KCU.REFERENCED_TABLE_NAME 
				  WHERE KCU.TABLE_SCHEMA = :schemaName 
				  AND KCU.CONSTRAINT_NAME = :relationName 
				  AND KCU.TABLE_NAME = :tableName 
				  LIMIT 1";

		$params = array(':schemaName' => $sourceSchema, ':tableName' => $sourceTable, ':relationName' => $relationshipName);

		$statement = $_DB->prepare($query);
		$statement->execute($params);

		$result = $statement->fetch(PDO::FETCH_ASSOC);

		if($result)
		{
			// Set object properties
			$Object->setRelationshipName($relationshipName);
			$Object->setSourceSchema($sourceSchema);
			$Object->setSourceTable($sourceTable);
			$Object->setSourceTableName($result['TABLE_NAME']);
			$Object->setSourceColumn($result['COLUMN_NAME']);
			$Object->setSourceColumnName($result['COLUMN_COMMENT']);
			$Object->setReferencedSchema($result['REFERENCED_TABLE_SCHEMA']);
			$Object->setReferencedTable($result['REFERENCED_TABLE_NAME']);
			$Object->setReferencedTableName($result['REFFERENCED_TABLE_COMMENT']);
			$Object->setReferencedColumn($result['REFERENCED_COLUMN_NAME']);
			$Object->setReferencedColumnName($result['REFFERENCED_TABLE_COMMENT']);

			return $Object;
		}
		else
		{
			return false;
		}
	}

	/**
	 * createFromParams()
	 * Constructor for the THINKER_Object_Relationship Class that uses parameters for creation
	 *
	 * @author Cory Gehr
	 * @access public
	 * @static
	 * @param $relationName: Name of the Relationship
	 * @param $sourceSchema: Source Schema
	 * @param $sourceTable: Source Table
	 * @param $sourceTableName: Source Table Friendly Name
	 * @param $sourceColumn: Source Column
	 * @param $sourceColumnName: Source Column Friendly Name
	 * @param $refSchema: Referenced Schema
	 * @param $refTable: Referenced Table
	 * @param $refTableName: Referenced Table Friendly Name
	 * @param $refColumn: Referenced Column
	 * @param $refColumnName: Referenced Column Friendly Name
	 * @return THINKER_Object_Relationship Object
	 */
	public static function createFromParams($relationName, $sourceSchema, $sourceTable, $sourceTableName, $sourceColumn, 
								$sourceColumnName, $refSchema, $refTable, $refTableName, $refColumn, $refColumnName)
	{
		$Object = new THINKER_Object_Relationship();

		// Set values
		$Object->setRelationshipName($relationName);
		$Object->setSourceSchema($sourceSchema);
		$Object->setSourceTable($sourceTable);
		$Object->setSourceTableName($sourceTableName);
		$Object->setSourceColumn($sourceColumn);
		$Object->setSourceColumnName($sourceColumnName);
		$Object->setReferencedSchema($refSchema);
		$Object->setReferencedTable($refTable);
		$Object->setReferencedTableName($refTableName);
		$Object->setReferencedColumn($refColumn);
		$Object->setReferencedColumnName($refColumnName);

		return $Object;
	}

	/**
	 * getRelationshipName()
	 * Returns the name of the relationship
	 *
	 * @access public
	 * @return Relationship Name
	 */
	public function getRelationshipName()
	{
		return $this->relationName;
	}

	/**
	 * getReferencedColumn()
	 * Returns the name of the referenced column
	 *
	 * @access public
	 * @return Referenced Column
	 */
	public function getReferencedColumn()
	{
		return $this->refColumn;
	}

	/**
	 * getReferencedColumnName()
	 * Returns the friendly name of the referenced column
	 *
	 * @access public
	 * @return Referenced Column Friendly Name
	 */
	public function getReferencedColumnName()
	{
		if($this->refColumnName)
		{
			return $this->refColumnName;
		}
		else
		{
			return $this->getReferencedColumn();
		}
	}

	/**
	 * getReferencedSchema()
	 * Returns the name of the referenced table's schema
	 *
	 * @access public
	 * @return Referenced Schema
	 */
	public function getReferencedSchema()
	{
		return $this->refSchema;
	}

	/**
	 * getReferencedTable()
	 * Returns the name of the referenced table
	 *
	 * @access public
	 * @return Referenced Table
	 */
	public function getReferencedTable()
	{
		return $this->refTable;
	}

	/**
	 * getReferencedTableName()
	 * Returns the friendly name of the referenced table
	 *
	 * @access public
	 * @return Referenced Table Friendly Name
	 */
	public function getReferencedTableName()
	{
		if($this->refTableName)
		{
			return $this->refTableName;
		}
		else
		{
			return $this->getReferencedTable();
		}
	}

	/**
	 * getSourceColumn()
	 * Returns the name of the source column
	 *
	 * @access public
	 * @return Source Column
	 */
	public function getSourceColumn()
	{
		return $this->sourceColumn;
	}

	/**
	 * getSourceColumnName()
	 * Returns the friendly name of the source column
	 *
	 * @access public
	 * @return Source Column Friendly Name
	 */
	public function getSourceColumnName()
	{
		if($this->sourceColumnName)
		{
			return $this->sourceColumnName;
		}
		else
		{
			return $this->getSourceColumn();
		}
	}

	/**
	 * getSourceSchema()
	 * Returns the name of the source table's schema
	 *
	 * @access public
	 * @return Source Schema
	 */
	public function getSourceSchema()
	{
		return $this->sourceSchema;
	}

	/**
	 * getSourceTable()
	 * Returns the name of the source table
	 *
	 * @access public
	 * @return Source Table
	 */
	public function getSourceTable()
	{
		return $this->sourceTable;
	}

	/**
	 * getSourceTableName()
	 * Returns the friendly name of the source table, if it exists. Otherwise, returns the source table name.
	 *
	 * @access public
	 * @return Source Table Friendly Name
	 */
	public function getSourceTableName()
	{
		if($this->sourceTableName)
		{
			return $this->sourceTableName;
		}
		else
		{
			return $this->getSourceTable();
		}
	}

	/**
	 * setReferencedColumn()
	 * Sets the name of the referenced column
	 *
	 * @access public
 	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setReferencedColumn($value)
	{
		$this->refColumn = $value;
		return $this->refColumn;
	}

	/**
	 * setReferencedColumnName()
	 * Sets the friendly name of the referenced column
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setReferencedColumnName($value)
	{
		$this->refColumnName = $value;
		return $this->refColumnName;
	}

	/**
	 * setReferencedSchema()
	 * Sets the name of the referenced table's schema
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setReferencedSchema($value)
	{
		$this->refSchema = $value;
		return $this->refSchema;
	}

	/**
	 * setReferencedTable()
	 * Sets the name of the referenced table
	 *
	 * @access public
 	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setReferencedTable($value)
	{
		$this->refTable = $value;
		return $this->refTable;
	}

	/**
	 * setReferencedTableName()
	 * Sets the friendly name of the referenced table
	 *
	 * @access public
 	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setReferencedTableName($value)
	{
		$this->refTableName = $value;
		return $this->refTableName;
	}

	/**
	 * setRelationshipName()
	 * Sets the name of the relationship
	 *
	 * @access public
	 * @param $value: New Property Value
	 * @return Property Value
	 */
	public function setRelationshipName($value)
	{
		$this->relationName = $value;
		return $this->relationName;
	}

	/**
	 * setSourceColumn()
	 * Sets the name of the source column
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setSourceColumn($value)
	{
		$this->sourceColumn = $value;
		return $this->sourceColumn;
	}

	/**
	 * setSourceColumnName()
	 * Sets the friendly name of the source column
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setSourceColumnName($value)
	{
		$this->sourceColumnName = $value;
		return $this->sourceColumnName;
	}

	/**
	 * setSourceSchema()
	 * Sets the name of the source table's schema
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setSourceSchema($value)
	{
		$this->sourceSchema = $value;
		return $this->sourceSchema;
	}

	/**
	 * setSourceTable()
	 * Sets the name of the source table
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setSourceTable($value)
	{
		$this->sourceTable = $value;
		return $this->sourceTable;
	}

	/**
	 * setSourceTableName()
	 * Sets the friendly name of the source table
	 *
	 * @access public
	 * @param $value: New Property Value
 	 * @return Property Value
	 */
	public function setSourceTableName($value)
	{
		$this->sourceTableName = $value;
		return $this->sourceTableName;
	}
}