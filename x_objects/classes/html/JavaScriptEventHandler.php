<?php

/*
 * Project:			Platform
 * Module:			classes/JavaScriptEventHandler
 * @extends:		classes/AttributedObject
 * Purpose:			Abstraction of managing javascript event handlers for elements
 * 
 * Created By:		David Owen Greenberg <david.o.greenberg@gmail.com>
 * On:				18 Oct 2010
 */
 
 class JavaScriptEventHandler extends AttributedObject {
 	
 	// debugging
 	private $Debug = false;
 	
 	//! set the onMouseOver() event handler with the specified code
 	public function setOnMouseover( $Code ) { $this->set('onmouseover', $Code); }
 	
 	//! set the onMouseOut() event handler with the specified code
 	public function setOnMouseOut( $Code ) { $this->set('onmouseout' , $Code);}
 	
 	/*
 	 * setOnChange( $Code ): set the onchange js event handler with the given code
 	 */
 	public function setOnChange( $Code) { $this->set( 'onchange' , $Code); }
	
	//! set onkeyup event
	public function setOnKeyup( $Code) { $this->set( 'onkeyup' , $Code); }
	
 	
 	/*
 	 * setOnClick( $Code ): set the onclick js event handler with the given code
 	 */
 	public function setOnClick( $Code) {
 		
 		if ( $this->Debug )
 			Debugger::echoMessage( 'JavaScriptEventHandler::setOnClick: code=' . $Code); 
 		$this->set( 'onclick' , $Code); 
 	}
 	
 	
 	/*
 	 * getHandlersAsString(): returns all JS attributes
 	 */
 	public function getHandlersAsString () { return $this->getAttributesAsString(); }
 	
 }

?>