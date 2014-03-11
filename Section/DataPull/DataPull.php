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
	 * dataSelect()
	 * Passes data back for the 'dataSelect' subsection
	 *
	 * @access public
	 */
	public function dataSelect()
	{
		$phase = getPageVar('phase', 'str', 'GET');

		switch($phase)
		{

		}

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
				'COLUMNS' => THINKER_Object_Table::getTableColumnNames($schema, $table)
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
						'COLUMNS' => THINKER_Object_Table::getTableColumnNames($refSchema, $refTable)
						);
				}
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
				pushMessage('An invalid schema/table combination was selected, please try again.', 'error');
			break;

			case 'noSchema':
				pushMessage('An invalid schema was selected, please try again.', 'error');
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
}
?>