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
	Now, choose the data you'd like to pull from <strong><?php echo $this->get('schemaName', 'inline') . ".'" . $this->get('tableName', 'inline') . "'"; ?></strong>:
</p>
<form id='dataSelect' method='post' action='<?php echo createUrl('DataPull', 'dataSelect', array('phase' => 'proceed')); ?>'>
	<fieldset>
		<legend>Select Data Points</legend>
		<div id='data-points'>
<?php
	// Get column data
	$colData = $this->get('columns');

	$titles = '';
	$divs = '';

	// Echo titles
	for($i=0;$i<count($colData);$i++)
	{
		$curData = $colData[$i];

		// First grab title
		$titles .= "<li><a href='#tabs-$i'>" . $curData['TABLE_FRIENDLY'] . "</a></li>";

		// Now generate checkboxes
		$divs .= "<div id='tabs-$i'>";

		// Loop through columns
		foreach($curData['COLUMNS'] as $c)
		{
			list($colName, $friendlyName) = $c;
			$chkId = $curData['SCHEMA'] . '-|-' . $curData['TABLE'] . '-|-' . $colName;
			$divs .= "<input type='checkbox' id='$chkId' name='$chkId' /><label for='$chkId'>$friendlyName</label><br />";
		}

		// Close div
		$divs .= "</div>";
	}

	echo "<ul>$titles</ul>";
	echo $divs;
?>
		</div>
		<input type='submit' value='Next >' />
	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/dataSelect.js'></script>