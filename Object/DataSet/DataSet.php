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
	private $primarySchema;
	private $primaryTable;
	private $query;
	private $queryParams;
	private $returnedData;

	/**
	 * __construct()
	 * Constructor for the THINKER_Object_DataSet Class
	 *
	 * @author Cory Gehr
	 * @access public
	 * @param $schema: Primary Schema
	 * @param $table: Primary Table
	 * @param $columns: Columns to pull
	 * @param $filters: Filters to use (default: empty array)
	 */
	public function __construct($schema, $table, $columns, $filters = array())
	{
		global $_DB;

		// Call parent constructor
		parent::__construct();

		// Set local variables
		$this->columns = $columns;
		$this->filters = $filters;
		$this->primarySchema = $schema;
		$this->primaryTable = $table;

		// Pull data
		$this->compileQuery();

		$statement = $_DB->prepare($this->query);
		$statement->execute($this->queryParams);

		$this->returnedData = $statement->fetchAll();

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
			// Start parsing query
			$selectCols = "";
			// FROM string
			$from = "";
			// WHERE, if necessary
			$where = "";
			// Query parameters
			$params = array();

			// Initialize arrays to store database table info
			$schemas = array($this->primarySchema);
			$tables = array(0 => array($this->primaryTable));
			$columns = array();

			// Keep a count of the number of tables (aids with refs)
			$tableCount = 0; // Start at 0 for the primary table -- we increment before we add a new one

			foreach($this->columns as $col)
			{
				$colSchema = $col['SCHEMA'];
				$colTable = $col['TABLE'];
				$colName = $col['COLUMN'];
				$refColName = $col['REF_COL'];

				// Primary Schema & table are always 0
				$schemaRef = 0;
				$tableRef = 0;

				// Schema & Table Check
				if($colSchema != $this->primarySchema)
				{
					// Check for schema in array
					$schemaRef = array_search($colSchema, $schemas);

					if(!$schemaRef)
					{
						// Add to schemas array
						$schemas[] = $colSchema;
						// Get index by looking at the last item in the array
						$schemaRef = end($schemas);
					}

					// We have a schema reference, so now see if the table has already gotten a reference

					// Check for table
					if($colTable != $this->primaryTable)
					{
						// Check for table reference
						$tableRef = array_search($colTable, $tables[$schemaRef]);

						// If no reference exists, add one
						if(!$tableRef)
						{
							// Increment table count
							$tableCount++;
							// Add to schemas array
							$tables[$schemaRef][$tableCount] = $colTable;
							$tableRef = $tableCount;
						}
					}
				}


				// Add column to return
				if($selectCols)
				{
					$selectCols .= ',';
				}

				$selectCols .= " $tableRef.$colName"
			}

			// Create JOINs
			for($i=0; $i<count($schema); $i++)
			{
				if($i == 0)
				{

				}
				else
				{

				}
			}

			// Assign final values to local variables
			$this->query = "SELECT $selectCols $from $where";
			$this->queryParams = $params;

			return true;
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