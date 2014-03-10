<?php
    /**
     * DataPull/html/info.tpl
     * Contains the HTML template for the dbSelect subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Welcome!</h1>
<div id='steps-container'>
	<ol id='nav-steps'>
		<li class='active'><a href='#'>1. Pick Data Source</a></li>
		<li><a class='disabled'>2. Choose Data Points</a></li>
		<li><a class='disabled'>3. Add Filters</a></li>
		<li><a class='disabled'>4. Review Data</a></li>
	</ol>
</div>
<p>
	Welcome to the THINKer! To begin, please choose a data source below:
</p>
<form id='dsSelect' method='post' action='<?php echo createUrl('DataPull', 'dbSelect', array('phase' => 'proceed')); ?>'>
	<fieldset>
		<legend>Select Data Source</legend>
		<label for='source'>Data Source:</label>
		<br />
		<select id='source' name='source'>
			<option>Select One:</option>
<?php
	// Get schemata
	$schemata = $this->get('schemas', 'inline');

	foreach($schemata as $s)
	{
		list($n) = $s;
		echo "<option value='$n'>$n</option>";
	}
?>
		</select>
		<br />
		<input type='submit' value='Next >' />
	</fieldset>
</form>