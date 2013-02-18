<?php

class lightweight_business_object {
	
	private $property_list = array();
	
	public function __construct( $key, $search = null){
		
		$this->key = $key;
		
		$this->search = $search;
		
		if ( $this->search )
			$this->load();
	}

	public function __get( $what ){
		switch( $what ){
			case 'exists':
			break;
			default:
				echo get_class(). "::$what doesn't exist<br>\r\n";
		}
	}

}
?>