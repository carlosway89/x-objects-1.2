<?php 
/*! clss representation of a datafield, which is a conceptual representation of a single field for a data object, corresponding
to both the database representation, and the object representation.
*/
class DataField implements DataField_interface {

	//! the field name
	private $name;
	
	//! the field type
	private $type;
	
	//! the field label
	private $label;
	
	//! indicates if readonly
	private $readonly = false;
	
	//! indicates if hidden
	private $hidden = false;
	
	/*! Create a new DataField
	\param name (string) the name of the field, as in the database or data source
	\param type (int) logical data type
	\param label (string) the user-friendly label for the field
	\param readonly (bool | false) is the field read only?
	\param hidden (bool | false) is the field hidden from display
	*/
	public function __construct(
		$name,
		$type,
		$label,
		$readonly = false,
		$hidden = false
	) {
		$this->name = $name;
		$this->type = $type;
		$this->label = $label;
		$this->readonly = $readonly;
		$this->hidden = $hidden;
	}

	//! get the name of the field
	public function name() { return $this->name; }
	
	//! get the type of field
	public function type() { return $this->type; }
	
	//! get the label for the field
	public function label() { return $this->label; }
	
	//! returns true if the field is read only
	public function readonly() { return $this->readonly; }
	
	//! returns true if the field should be hidden from display
	public function hidden() { return $this->hidden; }
	
	/*! returns the input field (for editing) for the field
	\param value (mixed|null) set a value for the input field
	*/
	public function input( $value = null ) {
		switch ( $this->type ) {
			default:
				return new InputElement('class=datafield,id=' . $this->name . ',name=' . $this->name . ',value=' . $value);
			break;
		}
	}

}

?>