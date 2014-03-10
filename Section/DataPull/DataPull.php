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
			$this->set('schemaName', $schema);
			$this->set('tableName', $table);
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
				$schema = getPageVar('schema', 'str', 'GET', true);

				// TODO: Verify valid schema
				if($schema)
				{
					$this->set('tables', $this->getSchemaTables($schema));
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
		$this->set('schemas', $this->getSchemas());

		return true;
	}

	/**
	 * getSchemas()
	 * Returns a list of schemas on the current database server
	 *
	 * @access private
	 * @return Array of Schemas
	 */
	private function getSchemas()
	{
		global $_DB;

		$query = "SELECT SCHEMA_NAME
				  FROM INFORMATION_SCHEMA.SCHEMATA
				  WHERE SCHEMA_NAME NOT IN ('INFORMATION_SCHEMA', 'PERFORMANCE_SCHEMA', 'mysql', 'thinker', 'phpmyadmin')
				  ORDER BY SCHEMA_NAME";

		// Fetch results
		$statement = $_DB->prepare($query);
		$statement->execute();

		return $statement->fetchAll();
	}

	/**
	 * getSchemaTables()
	 * Returns a list of the tables in the selected schema
	 *
	 * @access private
	 * @param $name: Schema Name
	 * @return Array of Schema Tables
	 */
	private function getSchemaTables($name)
	{
		global $_DB;

		$query = "SELECT TABLE_NAME, TABLE_COMMENT
				  FROM INFORMATION_SCHEMA.TABLES
				  WHERE TABLE_SCHEMA = :schema 
				  ORDER BY TABLE_COMMENT, TABLE_NAME";
		$params = array(':schema' => $name);

		// Fetch results
		$statement = $_DB->prepare($query);
		$statement->execute($params);

		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
}
?>