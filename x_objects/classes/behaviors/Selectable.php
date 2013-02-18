<?php

//! interface defining behaviors for objects that can be selected in the ui
interface Selectable {

	//! get/set the selector
	public static function selector( $selector = null );
	
}