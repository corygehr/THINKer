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

		if($schema)
		{
			// Pass back all tables in the schema
			$this->set('tables', $this->getSchemaTables($schema));
			$this->set('schemaName', $schema);
		}
		else
		{
			// Redirect back to database selection
			redirect('DataPull', 'dbSelect', array('phase' => 'noSchema'));
		}

		return true;
	}

	/**
	 * dbSelect()
	 * Passes data back for the 'dbSelect' subsection
	 *
	 * @access public
	 */
	public function dbSelect()
	{
		$phase = getPageVar('phase', 'str', 'GET');

		switch($phase)
		{
			case 'noSchema':
				pushMessage('An invalid schema was selected, please try again.', 'error');
			break;

			case 'proceed':
				// Grab selected schema
				$schema = getPageVar('source', 'str', 'POST', true);

				// TODO: Verify permissions and name of schema
				if($schema)
				{
					// Push to session
					$this->session->__set('PULL_SCHEMA', $schema);
					
					// Redirect to next step
					redirect('DataPull', 'dataSelect');
				}
				else
				{
					redirect('DataPull', 'dbSelect', array('phase' => 'noSchema'));
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
				  WHERE SCHEMA_NAME NOT IN ('INFORMATION_SCHEMA', 'PERFORMANCE_SCHEMA')
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

		return $statement->fetchAll();
	}
}
?>