<?php
    /**
     * Start/html/info.tpl
     * Contains the HTML template for the info subsection
     *
     * @author Cory Gehr
     */
?>
<h1>Welcome!</h1>
<p>
<?php
	echo $this->get('message', 'inline');
?>
</p>