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
		<li class='completed'>1. Pick Data Source</li>
		<li class='active'>2. Choose Data Points</li>
		<li>3. Add Filters</li>
		<li>4. Review Data</li>
	</ol>
</div>
<p>
	Now, choose the data you'd like to pull from '<?php echo $this->get('schemaName', 'inline') . '.' . $this->get('tableName', 'inline'); ?>':
</p>
<form id='dsSelect' method='post'>
	<fieldset>
		<legend>Select Data Points</legend>
		<div id='data-points'>
			<ul>
				<li><a href='#tabs-1'>Table 1</a></li>
				<li><a href='#tabs-2'>Table 2</a></li>
				<li><a href='#tabs-3'>Table 3</a></li>
			</ul>
			<div id='tabs-1'>
				<p>
					Table 1 Data checkboxes go here.
				</p>
			</div>
			<div id='tabs-2'>
				<p>
					Table 2 Data checkboxes go here.
				</p>
			</div>
			<div id='tabs-3'>
				<p>
					Table 3 Data checkboxes go here.
				</p>
			</div>
		</div>
		<input type='submit' value='Next >' />
	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/dataSelect.js'></script>