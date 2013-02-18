<?php

//! interface to manage changes to an object's values
interface Changeable {

	//! has the given key changed?
	public function hasChanged( $key );
	
	//! set the given key as changed
	public function changed( $key );
	
	//! reset all changes
	public function resetChange();
	
	//! have their been any changes?
	public function noChanges();

}

?>