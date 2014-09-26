<?php
    /**
     * DataPull/html/dataReview.tpl
     * Contains the HTML template for the dataReview subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Results</h1>
<div id='steps-container'>
	<ol id='nav-steps'>
		<li class='completed'>1. Pick Data Source</li>
		<li class='completed'>2. Choose Data Points</li>
		<li class='completed'>3. Add Filters</li>
		<li class='active'>4. Review Data</li>
	</ol>
</div>
<p>
	The data you have selected is below.
</p>
<form id='dataReview' method='post' action='<?php echo createUrl('DataPull', 'dataReview', array('phase' => 'proceed')); ?>'>
	<fieldset>
		<legend>Review Data</legend>
<?php
	$retData = $this->get('data');

	if($retData)
	{
		$headings = "";
		$rows = "";

		// Create headings
		$keys = array_keys($retData[0]);

		for($i=0;$i<count($keys); $i++)
		{
			$key = $keys[$i];
			$headings .= "<th>$key</th>";
		}

		$rowCount = 0;

		foreach($retData as $d)
		{
			$rows .= "<tr>";

			foreach($d as $k => $val)
			{
				$rows .= "<td>$val</td>";
			}
			$rows .= "</tr>";
			$rowCount++;
		}

		echo "<p><strong>$rowCount</strong> Results<p>";
		echo "<p><a href='" . createUrl('DataPull', 'dataReview', array('set' => 'data', 'view' => 'csv')) . "'>Export to CSV</a></p>";
		// Output table
		echo "<table><thead>$headings</thead><tbody>$rows</tbody></table>";
	}
	else
	{
		echo "<p><em>An error occurred while loading the results.</p>";
	}
?>
	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/dataReview.js'></script>