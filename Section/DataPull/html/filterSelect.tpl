<?php
    /**
     * DataPull/html/filterSelect.tpl
     * Contains the HTML template for the filterSelect subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Filter Selection</h1>
<div id='steps-container'>
	<ol id='nav-steps'>
		<li class='completed'>1. Pick Data Source</li>
		<li class='completed'>2. Choose Data Points</li>
		<li class='active'>3. Add Filters</li>
		<li>4. Review Data</li>
	</ol>
</div>
<p>
	Add filters to constrain the data from <strong><?php echo $this->get('schemaName', 'inline') . ".'" . $this->get('tableName', 'inline') . "'"; ?></strong>:
</p>
<form id='filterSelect' method='post' action='<?php echo createUrl('DataPull', 'filterSelect', array('phase' => 'proceed')); ?>'>
	<fieldset>
		<legend>Select Filters</legend>
		<div id='1'>
			<select id='filter-col_1' class='colFilter' name='filter-col_1'>
				<option>Choose a Data Point:</option>
<?php
	// Get columns and create options for them
	$options = $this->get('columns');

	if($options)
	{
		foreach($options as $o => $val)
		{
			$tableFriendly = $val['TABLE']->getTableFriendlyName();

			if(!empty($val['RELATIONSHIP']))
			{
				$tableFriendly .= " (" . $val['RELATIONSHIP']->getSourceColumnName() . ")";
			}

			$friendlyName = "[$tableFriendly] " . $val['COLUMN']->getColumnFriendlyName();

			echo "<option value='$o'>$friendlyName</option>";
		}
	}
?>
			</select>
			<select id='filter-option_1' name='filter-option_1' disabled>
				<option>Filter Type:</option>
			</select>
			<input type='text' id='filter-value_1' name='filter-value_1' placeholder='Filter Value' />
			<img id='addButton' class='add clickable' src='View/html/images/add.png' width='20' height='20' /> 
		</div>
		<input type='submit' value='Next >' />
	</fieldset>
</form>
<script type='text/javascript' src='Section/DataPull/html/filterSelect.js'></script>