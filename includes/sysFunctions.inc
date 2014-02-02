<?php
	/**
	 * sysFunctions.php
	 * Contains the system critical functions for THINKer
	 *
	 * @author Cory Gehr
	 */
	 
/**
 * __autoload()
 * PHP Function that runs when a selected class cannot be loaded
 * Attempts to locate the class so we don't have to include them everywhere
 *
 * @author Cory Gehr
 * @param $class: Class being loaded
 */
function __autoload($class)
{
	// Get the type of object
	$classTypes = explode('_', $class); // Splits by '_' into array
	
	try
	{
		$type = $classTypes[1]; // Type will ALWAYS be here unless someone doesn't start their class with 'THINKER_'
	}
	catch(Exception $err)
	{
		// Attempt to include non-THINK class
		throw new Exception("Attempted to include non-THINKer Class $class. Please manually specify the path in the file where it is required. 
							 Detailed info: " . $err->getMessage());
	}
	
	// Determine the path
	// More than two items indicates this is a sub-class
	$depth = count($classTypes);

	if($depth > '2')
	{
		if($type == 'Object')
		{
			// Objects are the anomaly here -- they'll all be in 'includes'
			$classFile = 'Object/' . $classTypes[2] . '.php';
		}
		else
		{
			// Build path
			$classFile = '';

			// Start at 1 to skip 'THINK'
			for($i=1; $i<$depth;$i++)
			{
				$classFile .= $classTypes[$i] . '/';
			}

			// Add filename at the end
			$classFile .= $classTypes[$depth-1] . '.php';
		}
	}
	else
	{
		// <= 2 means it will be in includes (THINK_Object, THINK_Model, etc.)
		$classFile = $classTypes[1] . '.php';
	}
	
	// Require the class file
	require_once($classFile);
}

/**
 * timestamp()
 * Gets the current date/time
 *
 * @author Cory Gehr
 * @return Date in Y-m-d H:i:s format
 */
function timestamp()
{
	// Get the current date
	date_default_timezone_set('America/New_York');
	return date('Y-m-d H:i:s');
}
?>