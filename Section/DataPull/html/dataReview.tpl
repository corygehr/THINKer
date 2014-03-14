<?php
    /**
     * DataPull/html/dataReview.tpl
     * Contains the HTML template for the dataReview subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Welcome!</h1>
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

	</fieldset>
</form>

<script type='text/javascript' src='Section/DataPull/html/dataReview.js'></script>