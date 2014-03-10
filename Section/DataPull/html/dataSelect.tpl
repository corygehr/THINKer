<?php
    /**
     * DataPull/html/info.tpl
     * Contains the HTML template for the dbSelect subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Data Selection</h1>
<div id='steps-container'>
	<ol id='nav-steps'>
		<li><a class='disabled'>1. Pick Data Source</a></li>
		<li class='active'><a href='#'>2. Choose Data Points</a></li>
		<li><a class='disabled'>3. Add Filters</a></li>
		<li><a class='disabled'>4. Review Data</a></li>
	</ol>
</div>
<p>
	Now, choose the data you'd like to pull from '<?php echo $this->get('schemaName', 'inline') ?>':
</p>
<form id='dsSelect' method='post'>
	<fieldset>
		<legend>Select Data Points</legend>
		<label for='source'>Table Name:</label>
		<br />
		<select id='source' name='source'>
			<option>Select One:</option>
<?php
	// Get tables from schema
	$tables = $this->get('tables', 'inline');

	foreach($tables as $t)
	{
		list($name, $comment) = $t;

		if(!$comment)
		{
			$comment = $name;
		}

		echo "<option value='$name'>$comment</option>";
	}
?>
		</select>
		<br />
		<input type='submit' value='Next >' />
	</fieldset>
</form>