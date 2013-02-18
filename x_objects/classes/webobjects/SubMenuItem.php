<?php
/*! Object representation of a navigation menu item at the level below the main one.
 * 
 */

class SubMenuItem extends MenuItem {

	public function __construct(  $Name, $Label, $URL, $Attributes = null) {
		
		parent::__construct(  $Name, $Label, $URL, $Attributes);
	}
	
}

?>

