<?php 
//! a managed text area element contains an element to manage the length and display feedback to the user
class ManagedTextAreaElement extends TextAreaElement {

	// default max length
	const DEF_MAX_LEN = 250;

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		parent::__construct($Attributes, $InnerHTML, $displayOnConstruct);	
		
		// calculate the characters remaining
		$remaining = self::DEF_MAX_LEN - strlen( $this->getInnerHTML() );
		
		// set the management to display a counter of remaining characters
		$this->setManaged( new SpanElement('class=managedtextarea,id=managedtextarea_' . $this->get('id') ,$remaining . ' characters remaining'));
		
		
		// add a javascript handler to update that section
		$this->JSHandler->setOnKeyUp( 'HTMLElement.notifyCharsRemaining(\'' . $this->get('id') . '\',\'' . self::DEF_MAX_LEN . '\');' );
		
	}

}
?>
