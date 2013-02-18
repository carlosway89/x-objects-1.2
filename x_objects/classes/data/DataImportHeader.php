<?php

class DataImportHeader {

	private $debug = false;

	//! construct with xml and header row
	public function __construct ( $xml, $row ) {
	
		if ( Debugger::enabled() )
			$this->debug = true;
	
		$this->xml = $xml;
		$this->row = $row;
		
	}

	//! magic set
	public function __set( $what, $val ) {
	
		$this->$what = $val;
		
	}
	
	//! magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			case 'key':
			
				$key = (string) $this->xml->key;
			
				if ( Debugger::enabled() )
					echo get_class() . ": key = $key<br>";
			
				return $key;
				
			break;

			case 'cols':
				//echo $this->row . "<br>";
				
				$orig = explode( ',' , $this->row );
				
				if ( $this->debug )
					print_r( $orig);
				
				$new = array();
				
				foreach( $orig as $id => $col ) {
					
					if ( $this->debug)
						echo get_class() . " $id => $col <br>";
					$new[ $id ] = (string) $this->xml->header_mapping->$col;
					//echo $new[ $id ];
				}
				//echo implode( ',' , $orig ) . ' ';
				
				//print_r( $new );
				
				return $new;
				
			break;
			
			default:
				return null;
			break;
	
		}
	
	}

}

?>