<?php

class DataImportRow {

	// debugging
	private $debug = false;

	//! construct with key and row data
	public function __construct( $key, $data , $header ) {
	
		if ( Debugger::enabled() )
			$this->debug = true;
	
		$this->key = $key;
		$this->data = $data;
		$this->header = $header;
		$this->class = $this->key;
		$this->obj = new $this->class();
		// save values
		$this->obj->set_values( $this->values );
		
//		echo $this->data . '<br>';
	}

	public function __set( $what, $val ){
	
		$this->$what = $val;
		
	}
	
	public function __get( $what ) {
	
		switch( $what ) {

			case 'header_mismatch_error':
			
				return null;
				
			break;
			
			case 'save_error':
			
				return $this->obj->insert_error;
				
			break;
		
			case 'values':
			
			
				$values = array();
				
				if ( Debugger::enabled() )
					echo "In DataImportRow, raw data for values = $this->data<br>";
					
				
				$vals = explode( ',', $this->data );
				
				$header_pointer = 0;
				
				foreach ( $vals as $val ) {
				
					if ( Debugger::enabled() )
						echo "In DataImportRow, processing value = $val<br>";
						
					$col = @$this->header->cols[ $header_pointer ];
					if ( $col ) {
						$values [ $col ] = trim( $val );
						$header_pointer++;
					}
				
				}
				if ( Debugger::enabled() )
					echo "In DataImportRow, returning values = " . implode(',', $values) . '<b>';
				
				return $values;
				
			break;
		
			case 'sql':
			
				return $this->obj->save_sql;
			
			break;
			
			default:
			
				return $this->$what;
				
			break;
			
		}
	}

	//! import the row into the database
	public function import() {
	
		
		if ( $this->obj->save() )
			return true;
			
		return false;
	}
	
	//! create a new one
	public static function create( $key, $data, $header ) {
	
		return new DataImportRow( $key, $data , $header );
		
	}

}

?>