<?php
	/**
	 * Object/DataSet.php 
	 * Contains the THINKER_Object_DataSet class
	 *
	 * @author Cory Gehr
	 */
	 
class THINKER_Object_DataSet extends THINKER_Object
{
	private $columns;
	private $filters;
	private $PrimarySchema;
	private $PrimaryTable;
	private $query;
	private $queryParams;
	private $returnedData;

	/**
	 * __construct()
	 * Constructor for the THINKER_Object_DataSet Class
	 *
	 * @author Cory Gehr
	 * @access public
	 * @param $Schema: Primary Schema Object
	 * @param $Table: Primary Table Object
	 * @param $columns: Columns to pull
	 * @param $filters: Filters to use (default: empty array)
	 */
	public function __construct($Schema, $Table, $columns, $filters = array())
	{
		global $_DB;

		// Call parent constructor
		parent::__construct();

		// Set local variables
		$this->columns = $columns;
		$this->filters = $filters;
		$this->PrimarySchema = $Schema;
		$this->PrimaryTable = $Table;

		// Pull data
		$this->compileQuery();

		$statement = $_DB['thinker']->prepare($this->query);
		$statement->execute($this->queryParams);

		$this->returnedData = $statement->fetchAll(PDO::FETCH_ASSOC);

		if(empty($this->returnedData))
		{
			pushMessage("No data was returned for the specified parameters.", 'warning');
		}
	}

	/**
	 * compileQuery()
	 * Compiles a query based on the inputs
	 *
	 * @access private
	 */
	private function compileQuery()
	{
		if($this->columns && isset($this->filters))
		{
			// Compile a list of the columns to be pulled
			$selectCols = "";

			// Compile a list of tables and their schemas to be pulled, starting with the primary table
			$tables = array($this->PrimaryTable->getTableSchema() => array($this->PrimaryTable->getTableName() => null));

			foreach($this->columns as $c)
			{
				$tableSchema = $c['TABLE']->getTableSchema();
				$tableName = $c['TABLE']->getTableName();
				$colSchema = $c['COLUMN']->getColumnSchema();
				$colTable = $c['COLUMN']->getColumnTable();
				$colName = $c['COLUMN']->getColumnName();
				$colFName = $c['COLUMN']->getColumnFriendlyName();
				$Relationship = null;

				if(!empty($c['RELATIONSHIP']))
				{
					$Relationship = $c['RELATIONSHIP'];
					// Add source column friendlyName
					$srcFName = $Relationship->getSourceColumnName();
					$colFName .= " ($srcFName)";
				}

				// Add column to SELECTs
				if(!empty($selectCols))
				{
					$selectCols .= ", $colSchema.$colTable.$colName AS '$colFName'";
				}
				else
				{
					$selectCols = "$colSchema.$colTable.$colName AS '$colFName'";
				}

				// Check if Schema is set
				if(!empty($tables[$tableSchema]))
				{
					// Set table if it isn't set
					if(!isset($tables[$tableSchema][$tableName]))
					{
						$tables[$tableSchema][$tableName] = $Relationship;
					}
				}
				else
				{
					$tables[$tableSchema][$tableName] = $Relationship;
				}
			}

			// Create the FROM string for the query
			$from = "";

			foreach($tables as $schema => $table)
			{
				foreach($table as $name => $val)
				{
					if(!$val && empty($from))
					{
						// Primary table
						$from = "FROM $schema.$name";
					}
					elseif($val)
					{
						// Get relationship data
						$srcSchema = $val->getSourceSchema();
						$srcTable = $val->getSourceTable();
						$srcCol = $val->getSourceColumn();
						$refCol = $val->getReferencedColumn();
						$from .= " JOIN $schema.$name ON $srcSchema.$srcTable.$srcCol = $schema.$name.$refCol";
					}
					else
					{
						// Throw error
						trigger_error('A relationship was expected, but not listed');
						return false;
					}
				}
			}

			$where = "";
			$filterCount = 0;

			// Add filters
			foreach($this->filters as $filter)
			{
				$Column = $filter['COLUMN']['COLUMN'];
				$colSchema = $Column->getColumnSchema();
				$colTable = $Column->getColumnTable();
				$colName = $Column->getColumnName();
				$colFName = $Column->getColumnFriendlyName();
				$filterOption = $filter['OPTION'];
				$filterVal = $filter['VALUE'];
				$filterAndOr = $filter['ANDOR'];

				if(empty($where) && $filterAndOr === 'FIRST')
				{
					// First filter should begin with WHERE
					$filterAndOr = 'WHERE';
				}
				elseif($filterAndOr === 'FIRST')
				{
					// FIRST was specified on a non-first filter
					trigger_error('A filter option was incorrect, please try again');
					return false;
				}

				$filter = "";

				switch($filterOption)
				{
					case 'AFTER':
					case 'GT':
						$filter = "> :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'AFTER_INCL':
					case 'GTE':
						$filter = ">= :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'BEFORE':
					case 'LT':
						$filter = "< :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'BEFORE_INCL':
					case 'LTE':
						$filter = "<= :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'CONTAINS':
						$filter = "LIKE :$filterCount";
						$this->queryParams[":$filterCount"] = "%$filterVal%";
					break;

					case 'ENDS_WITH':
						$filter = "LIKE :$filterCount";
						$this->queryParams[":$filterCount"] = "%$filterVal";
					break;

					case 'EQUALS':
						$filter = "= :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'FALSE':
						$filter = "< :$filterCount";
					break;

					case 'LIKE':
						$filter = "LIKE :$filterCount";
						$this->queryParams[":$filterCount"] = $filterVal;
					break;

					case 'STARTS_WITH':
						$filter = "LIKE :$filterCount";
						$this->queryParams[":$filterCount"] = "$filterVal%";
					break;

					case 'TRUE':
						$filter = "= 1";
					break;

					default:
						trigger_error('An invalid filter option was specified, please try again');
						return false;
					break;
				}

				$where .= "$filterAndOr $colSchema.$colTable.$colName $filter ";

				$filterCount++;
			}

			$this->query = "SELECT " . $selectCols . ' ' . $from . ' ' . $where;
		}
		else
		{
			trigger_error("No columns or invalid filters provided");
			return false;
		}
	}

	/**
	 * getData()
	 * Returns the data provided by the given inputs
	 *
	 * @access public
	 * @return DataSet Values
	 */
	public function getData()
	{
		return $this->returnedData;
	}

	/**
	 * getQuery()
	 * Returns the query used to return the dataset
	 *
	 * @access public
	 * @return DataSet Query
	 */
	public function getQuery()
	{
		return $this->query;
	}
}