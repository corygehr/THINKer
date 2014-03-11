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
		<input type='submit' value='Next >' />
	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/filterSelect.js'></script>