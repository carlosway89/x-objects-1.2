<?php
//! the DataSecurity class contains methods to better keep the data secure and free from corruption
class DataSecurity {

	//! constant for form submissions
	const FORMSUBMITTED = 1;
	
	//! has the prior form already been submitted?
	public static function formAlreadySubmitted() {
		@session_start();
		return isset( $_SESSION[self::FORMSUBMITTED] ) ? true : false;
		
	}
	
	//! set the form as submitted
	public static function setFormAsSubmitted() {
		@session_start();
		$_SESSION[self::FORMSUBMITTED] = true;
	}
	
	//! clear last form submission
	public static function clearFormSubmission() {
		@session_start();
		unset( $_SESSION[self::FORMSUBMITTED] );
	}
}

?>