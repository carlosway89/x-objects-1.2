<?php

// a magic object has magic methods and is super easy to manage!

class magic_object extends AttributedObject {
	// magic get
	public function __get( $what ) {
	
		return parent::get( $what );
	
	}
	
	// magic set
	public function __set( $what, $how ) {
	//	echo "$what = $how<br>";
	
		parent::set( $what, $how );
		
	}
	
	//! magic call
	public function __call( $what, $args){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	 
	 	switch ( $what) {
			default:
				$container->warn( "$tag->event_format : call to unknown method $what, possibly from child class");	
			break;
		}
	}

}