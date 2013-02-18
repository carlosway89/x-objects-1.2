<?php
//! an xexception is an exception specific to x-objects
class xexception extends Exception {
	//! exception types
	const exception = 1;
	const unrecognized_tag = 2;
	const uninitialized_object = 3;
	const illegal_argument = 4;
	const database = 5;
	
	//! type
	private $type = self::exception;

	//! construct given type, tag, and data
	public function __construct($type,$tag,$data){
		// save type
		$this->type = $type;
		// construct parent
		parent::__construct( "$tag->exception_format $data");
	}
}
?>