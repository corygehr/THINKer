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
<form id='filterSelect' method='post' action='<?php echo createUrl('DataPull', 'filterSelect'); ?>'>
	<fieldset>
		<legend>Select Filters</legend>
		<select id='filter_1' class='colFilter' name='filter_1' onchange=''>
			<option>Choose a Data Point:</option>
<?php
	// Get columns and create options for them
	$options = $this->get('columns');

	if($options)
	{
		foreach($options as $o => $val)
		{
			$tableFriendly = $val['TABLE_FRIENDLY'];
			$friendlyName = "[$tableFriendly] " . $val['FRIENDLY_NAME'];

			echo "<option value='$o'>$friendlyName</option>";
		}
	}
?>
		</select>
		<select id='filter_option_1' name='filter_option_1' disabled>
			<option>Filter Type:</option>
		</select>
		<input type='text' id='filter_value_1' name='filter_value_1' placeholder='Filter Value' />
		<br />
		<input type='submit' value='Next >' />
	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/filterSelect.js'></script>