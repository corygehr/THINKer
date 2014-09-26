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
		$Schema = $this->session->__get('PRIMARY_SCHEMA');
		$Table = $this->session->__get('PRIMARY_TABLE');

		if(!empty($Schema) && !empty($Table))
		{
			// Get columns
			$columns = $this->session->__get('PULL_COLUMNS');

			if($columns)
			{
				// Get filters
				$filters = $this->session->__get('PULL_FILTERS');
				
				if(isset($filters))
				{
					// Generate DataSet
					$DataSet = new THINKER_Object_DataSet($Schema, $Table, $columns, $filters);

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
		$Schema = $this->session->__get('PRIMARY_SCHEMA');
		$Table = $this->session->__get('PRIMARY_TABLE');

		if(!empty($Schema) && !empty($Table))
		{
			$this->set('schemaName', $Schema->getSchemaName());
			$this->set('tableName', $Table->getTableFriendlyName());

			// Get the tables to reference
			$Tables = array();

			$Tables[] = array(
				'TABLE' => $Table,
				'COLUMNS' => THINKER_Object_Table::getTableColumnNames($Schema->getSchemaName(), $Table->getTableName()),
				'RELATIONSHIP' => null
				);

			// Discover relationships
			$Relationships = $Table->discoverRelationships();

			if($Relationships)
			{
				// Compile columns from relationship tables
				foreach($Relationships as $T)
				{
					$Tables[] = array(
						'TABLE' => THINKER_Object_Table::createFromDB($T->getReferencedSchema(), $T->getReferencedTable()),
						'COLUMNS' => THINKER_Object_Table::getTableColumnNames($T->getReferencedSchema(), $T->getReferencedTable()),
						'RELATIONSHIP' => $T
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

					foreach($Tables as $t)
					{
						$ColTable = $t['TABLE'];
						$cols = $t['COLUMNS'];
						$ColRelationship = $t['RELATIONSHIP'];

						// Loop through cols
						foreach($cols as $col)
						{
							list($colName, $colFriendlyName) = $col;

							$srcRelationCol = null;

							if(!empty($ColRelationship))
							{
								$srcRelationCol = $ColRelationship->getSourceColumn();
							}

							$id = $this->createColId($ColTable->getTableSchema(), $ColTable->getTableName(), $colName, $srcRelationCol);

							// Pull data
							$val = getPageVar($id, 'checkbox', 'POST', false);

							if($val)
							{
								// Add to list of columns to pull
								$selectCols[$id] = array(
									'TABLE' => $ColTable,
									'COLUMN' => THINKER_Object_Column::createFromDB($ColTable->getTableSchema(), $ColTable->getTableName(), $colName),
									'RELATIONSHIP' => $ColRelationship
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

			$this->set('tables', $Tables);
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

				// TODO: Verify permissions
				if($schema && $table)
				{
					// Push to session
					$this->session->__set('PRIMARY_SCHEMA', THINKER_Object_Schema::createFromDB($schema));
					$this->session->__set('PRIMARY_TABLE', THINKER_Object_Table::createFromDB($schema, $table));
					
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
		$Schema = $this->session->__get('PRIMARY_SCHEMA');
		$Table = $this->session->__get('PRIMARY_TABLE');

		if(!empty($Schema) && !empty($Table))
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
							$colPartCount = count($colParts);

							// Needs to be three or four parts
							if($colPartCount === 3 || $colPartCount === 4)
							{
								$fkCol = null;

								if($colPartCount === 4)
								{
									$fkCol = $colParts[3];
								}

								// Get the column data type
								$id = $this->createColId($colParts[0], $colParts[1], $colParts[2], $fkCol);

								if(isset($columns[$id]))
								{
									$Column = $columns[$id];
									$colType = $Column['COLUMN']->getColumnType();

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

					case 'invalidColumn':
						pushMessage('An invalid column was specified, please try again.', 'warning');
					break;

					case 'missingInfo':
						pushMessage('A required filter option and/or value were missing, please try again.', 'warning');
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
								// Get Column Object
								if(isset($columns[$column]))
								{
									$FilterCol = $columns[$column];

									// Get the option, andor, and values
									$option = getPageVar($filterOptionName, 'str', 'POST', false);
									$value = getPageVar($filterValueName, 'str', 'POST', false);
									$andOr = getPageVar($filterAndOrName, 'str', 'POST', false);

									if($option && ($value || (!$value && ($option == 'FALSE' || $option == 'TRUE'))))
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
										$filters[] = array('COLUMN' => $FilterCol, 'OPTION' => $option, 'VALUE' => $value, 'ANDOR' => $andOr);

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
									// Throw error
									redirect('DataPull', 'filterSelect', array('phase' => 'invalidColumn'));
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

				$this->set('schemaName', $Schema->getSchemaName());
				$this->set('tableName', $Table->getTableFriendlyName());
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
	 * @access public
	 * @static
	 * @param $schema: Schema Name
	 * @param $table: Table Name
	 * @param $column: Column Name
	 * @param $relationCol: Source Table Column for Relationship (default: null)
	 * @return Column ID
	 */
	public static function createColId($schema, $table, $column, $relationCol = null)
	{
		$id = $schema . '-|-' . $table . '-|-' . $column;

		if($relationCol)
		{
			$id .= '-|-' . $relationCol;
		}

		return $id;
	}
}
?>