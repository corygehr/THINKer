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