<?php
//! Objects implementing DataAbstractable have data values that can be (de)abstracted for presentation
interface Abstractable {


	//! public method to get a deabstracted value
	public function de_abstract( $key );
	
	//! public method to get the abstraction type for a given key
	public function abstractTypeof( $key );
	
}

?>