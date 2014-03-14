<?php
	/**
	 * DataPull.php
	 * Contains the Class for the DataPull Section
	 *
	 * @author Cory Gehr
	 */

class THINKER_Section_DataPull extends THINKER_Section
{
	/**
	 * dataReview()
	 * Passes data back for the 'dataReview' subsection
	 *
	 * @access public
	 */
	public function dataReview()
	{
		// Check for schema information
		$schema = $this->session->__get('PULL_SCHEMA');
		$table = $this->session->__get('PULL_TABLE');

		if($schema && $table)
		{
			// Get columns
			$columns = $this->session->__get('PULL_COLUMNS');

			if($columns)
			{
				// Get filters
				$filters = $this->session->__get('PULL_FILTERS');
				var_dump($columns);
				if(isset($filters))
				{
					// Generate DataSet
					$DataSet = new THINKER_Object_DataSet($schema, $table, $columns, $filters);

					// Get the data
					$data = $DataSet->getData();

					// Process phase
					$phase = getPageVar('phase', 'str', 'GET', false);

					switch($phase)
					{
						case 'export':

						break;
					}

					// Pass back the data
					$this->set('data', $data);
				}
				else
				{
					redirect('DataPull', 'filterSelect', array('phase' => 'noFilters'));	
				}
			}
			else
			{
				// We have a schema and table, but no columns. Redirect back to dataSelect
				redirect('DataPull', 'dataSelect', array('phase' => 'noCols'));
			}
		}
		else
		{
			// User hasn't started yet. Redirect to start
			redirect('DataPull', 'dsSelect', array('phase' => 'noSchema'));
		}

		return true;
	}

	/**
	 * dataSelect()
	 * Passes data back for the 'dataSelect' subsection
	 *
	 * @access public
	 */
	public function dataSelect()
	{
		// Check for schema information
		$schema = $this->session->__get('PULL_SCHEMA');
		$table = $this->session->__get('PULL_TABLE');

		if($schema && $table)
		{
			// Get table friendly name
			$ParentTable = new THINKER_Object_Table($schema, $table);

			$this->set('schemaName', $schema);
			$this->set('tableName', $ParentTable->getTableFriendlyName());

			// Get the columns for this table, and those it has relationships to
			$columns = array();

			$columns[] = array(
				'SCHEMA' => $schema,
				'TABLE' => $table,
				'TABLE_FRIENDLY' => $ParentTable->getTableFriendlyName(),
				'COLUMNS' => THINKER_Object_Table::getTableColumnNames($schema, $table),
				'REF_COLUMN' => null
				);

			// Discover relationships
			$relationTables = $ParentTable->discoverRelationships();

			if($relationTables)
			{
				// Compile columns from relationship tables
				foreach($relationTables as $t)
				{
					list($refSchema, $refTable, $refTableComment, $columnName, $columnComment, $refColumnName) = $t;

					if(!$refTableComment)
					{
						$refTableComment = $refTable;
					}

					// Add FK Column Comment to Foreign Table
					$refTableComment .= " ($columnComment)";

					$columns[] = array(
						'SCHEMA' => $refSchema,
						'TABLE' => $refTable,
						'TABLE_FRIENDLY' => $refTableComment,
						'COLUMNS' => THINKER_Object_Table::getTableColumnNames($refSchema, $refTable),
						'REF_COLUMN' => $refColumnName
						);
				}
			}

			// Now perform any actions specified, since we can use the columns to pull the selected fields
			$phase = getPageVar('phase', 'str', 'GET');

			switch($phase)
			{
				case 'noCols':
					pushMessage('Invalid or no columns selected!', 'warning');
				break;

				case 'proceed':
					// Gather all possible column selections
					// For each column, create the expected ID string and try to grab its value
					$selectCols = array();

					foreach($columns as $c)
					{
						$schemaName = $c['SCHEMA'];
						$tableName = $c['TABLE'];
						$tableFriendlyName = $c['TABLE_FRIENDLY'];
						$cols = $c['COLUMNS'];
						$refCol = $c['REF_COLUMN'];

						// Loop through cols
						foreach($cols as $col)
						{
							list($colName, $colFriendlyName) = $col;
							$id = $this->createColId($schemaName, $tableName, $colName);

							// Pull data
							$val = getPageVar($id, 'checkbox', 'POST', false);

							if($val)
							{
								// Add to list of columns to pull
								$selectCols[$id] = array(
									'SCHEMA' => $schemaName,
									'TABLE' => $tableName,
									'TABLE_FRIENDLY' => $tableFriendlyName,
									'COLUMN' => $colName,
									'FRIENDLY_NAME' => $colFriendlyName,
									'REF_COLUMN' => $refCol
									);
							}
						}
					}

					if(!empty($selectCols))
					{
						// Add to session and redirect
						$this->session->__set('PULL_COLUMNS', $selectCols);
						redirect('DataPull', 'filterSelect');
					}
					else
					{
						// Try again
						redirect('DataPull', 'dataSelect', array('phase' => 'noCols'));
					}
				break;
			}

			$this->set('columns', $columns);
		}
		else
		{
			// Redirect back to database selection
			redirect('DataPull', 'dbSelect', array('phase' => 'invalidCombo'));
		}

		return true;
	}

	/**
	 * dsSelect()
	 * Passes data back for the 'dsSelect' subsection
	 *
	 * @access public
	 */
	public function dsSelect()
	{
		$phase = getPageVar('phase', 'str', 'GET');

		switch($phase)
		{
			case 'fetchTables':
				$schemaName = getPageVar('schema', 'str', 'GET', true);

				if($schemaName)
				{
					$this->set('tables', THINKER_Object_Schema::getSchemaTableNames($schemaName));
				}

				// This is all we need for this phase -- exit
				return true;
			break;

			case 'invalidCombo':
				pushMessage('An invalid schema/table combination was selected, please try again.', 'warning');
			break;

			case 'noSchema':
				pushMessage('An invalid schema was selected, please try again.', 'warning');
			break;

			case 'proceed':
				// Grab selected schema
				$schema = getPageVar('schema', 'str', 'POST', true);
				$table = getPageVar('table', 'str', 'POST', true);

				// TODO: Verify permissions and name of schema
				if($schema && $table)
				{
					// Push to session
					$this->session->__set('PULL_SCHEMA', $schema);
					$this->session->__set('PULL_TABLE', $table);
					
					// Redirect to next step
					redirect('DataPull', 'dataSelect');
				}
				else
				{
					redirect('DataPull', 'dsSelect', array('phase' => 'noSchema'));
				}
			break;
		}

		// Pass back the list of schemas in the current database
		$this->set('schemas', THINKER_Object_Schema::getLocalSchemata());

		return true;
	}

	/**
	 * filterSelect()
	 * Passes data back for the 'filterSelect' subsection
	 *
	 * @access public
	 */
	public function filterSelect()
	{
		// Check for schema information
		$schema = $this->session->__get('PULL_SCHEMA');
		$table = $this->session->__get('PULL_TABLE');

		if($schema && $table)
		{
			// Get columns
			$columns = $this->session->__get('PULL_COLUMNS');

			if($columns)
			{
				// Process changes
				$phase = getPageVar('phase', 'str', 'GET');

				switch($phase)
				{
					case 'fetchFilterTypes':
						// Get the name of the column
						$column = getPageVar('column', 'str', 'GET');

						if($column)
						{
							// Parse column provided
							$colParts = explode('-|-', $column);

							// Needs to be three parts
							if(count($colParts) === 3)
							{
								// Get the column data type
								$Column = new THINKER_Object_Column($colParts[0], $colParts[1], $colParts[2]);

								if(!empty($Column))
								{
									$colType = $Column->getColumnType();

									$filters = array();

									switch($colType)
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

									$this->set('filters', $filters);
								}
							}
						}

						// This is all we need here
						return true;
					break;

					case 'proceed':
						// Get inputs, as long as they're provided
						$filters = array();
						$process = true;
						$count = 1;

						while($process)
						{
							$filterAndOrName = "filter-andor_$count";
							$filterColName = "filter-col_$count";
							$filterOptionName = "filter-option_$count";
							$filterValueName = "filter-value_$count";

							$column = getPageVar($filterColName, 'str', 'POST', false);

							if(!empty($column))
							{
								// Get the option, andor, and values
								$option = getPageVar($filterOptionName, 'str', 'POST', false);
								$value = getPageVar($filterValueName, 'str', 'POST', false);
								$andOr = getPageVar($filterAndOrName, 'str', 'POST', false);

								if($option && $value)
								{
									// If no AndOr, then assume AND
									if(!$andOr && $count == 1)
									{
										$andOr = 'FIRST';
									}
									else
									{
										$andOr = 'AND';
									}

									// Store values in array
									$filters[] = array('COLUMN' => $column, 'OPTION' => $option, 'VALUE' => $value, 'ANDOR' => $andOr);

									// Continue
									$count++;
								}
								else
								{
									// Throw error
									redirect('DataPull', 'filterSelect', array('phase' => 'missingInfo'));
								}
							}
							else
							{
								// No column value, so we're done
								$process = false;
							}
						}

						// Store filters in session and redirect
						$this->session->__set('PULL_FILTERS', $filters);

						redirect('DataPull', 'dataReview');
					break;
				}

				// Get table friendly name
				$ParentTable = new THINKER_Object_Table($schema, $table);

				$this->set('schemaName', $schema);
				$this->set('tableName', $ParentTable->getTableFriendlyName());
				$this->set('columns', $columns);
			}
			else
			{
				// We have a schema and table, but no columns. Redirect back to dataSelect
				redirect('DataPull', 'dataSelect', array('phase' => 'noCols'));
			}
		}
		else
		{
			// User hasn't started yet. Redirect to start
			redirect('DataPull', 'dsSelect', array('phase' => 'noSchema'));
		}

		return true;
	}

	/**
	 * createColId()
	 * Returns the HTML 'id' for a column
	 *
	 * @access private
	 * @param $schema: Schema Name
	 * @param $table: Table Name
	 * @param $column: Column Name
	 * @return Column ID
	 */
	private function createColId($schema, $table, $column)
	{
		return $schema . '-|-' . $table . '-|-' . $column;
	}
}
?>