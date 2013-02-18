<?php 
/*! Public interface for a datafield, which is a conceptual representation of a single field for a data object, corresponding
to both the database representation, and the object representation.
*/
interface DataField_interface {

	//! get the name of the field
	public function name();
	
	//! get the type of field
	public function type();
	
	//! get the label for the field
	public function label();
	
	//! returns true if the field is read only
	public function readonly();
	
	//! returns true if the field should be hidden from display
	public function hidden();
	
	/*! returns the input field (for editing) for the field
	\param value (mixed|null) set a value for the input field
	*/
	public function input( $value = null );

}

?>