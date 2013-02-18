<?php
//! interface for objects that can/must be validated when changing state
interface Validateable {

	//! is the object valid?
	public function isValid();
	
}
?>