<?php
	/**
	 * Object.php 
	 * Contains the THINKER_Object class
	 *
	 * @author Cory Gehr
	 */
	 
abstract class THINKER_Object
{
	protected $classInfo;	// Contains information about the class (ReflectionClass)
	
	/**
	 * __construct()
	 * Constructor for the THINKER_Object Class
	 *
	 * @author Cory Gehr
	 * @access public
	 */
	public function __construct()
	{
		// Initialize classInfo with information about the target class
		$this->classInfo = new ReflectionClass($this);
	}
	
	/**
	 * toArray()
	 * Returns an object's variables as an associative array
	 * 
	 * @author Joe Stump <joe@joestump.net>
	 * @access public
	 * @return Array of Variables (VarName => Value)
	 */
	public function toArray()
	{
		$defaults = $this->classInfo->getDefaultProperties();
		$return = array();
		
		foreach($defaults as $var => $val)
		{
			if($this->$var instanceof THINK_Object)
			{
				$return[$var] = $this->$var->toArray();
			}
			else
			{
				$return[$var] = $this->$var;
			}
		}
		
		return $return;
	}
}