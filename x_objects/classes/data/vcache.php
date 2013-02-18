<?php

//! template for a class with magic methods and create
class vcache extends AttributedObject {

	public function __construct( $raw_cond ) {
	
		// get the key for the vcache entry
		if ( preg_match ( '/offset from vcache:([a-zA-Z0-9_-]+)/' , $raw_cond, $matches ))
			$this->key = $matches[1];
			
		// get vcache configuration
		$this->config = RealObjects::instance()->vcache_config;
	}
	
	
	public function __get( $what ) {
	
		switch ( $what ) {
		
			case 'offset':
			
				$query = "SELECT day_one FROM " . $this->config->table. " WHERE vcache_key = '" . $this->key . "'";
				
				if ( $result = MySQLService2::query( $query ) ) {
				
					$row = $result->fetch_assoc();
					
					
					$date = strtotime( $row['day_one'] );
					
					$diff = time() - $date;
					
					$days = round ( $diff / 60 / 60 / 24 );
					
					return $days;
					
					$result->close();
				} else throw new DatabaseException( $query . ' ' . MySQLService2::error() );
				
		
				
			break;
			
			default:
			
				return $this->get( $what );
				
			break;
		
		}
	
	}
	
	//! reset day one for the current vcache pointer
	public function reset_day_one() {
	
		$query = "UPDATE " . $this->config->table . " SET day_one = '" . date( 'm/j/Y') . "' WHERE vcache_key = '" . strtolower( $this->key ). "'";
		if ( $result = MySQLService2::query( $query ) ) {
				
		} else throw new DatabaseException( $query . ' ' . MySQLService2::error() );
				
	
	}
	
	public function __set( $what, $val ) {
	
		$this->set( $what, $val );
	
	}
	
	public static function create( $raw_cond ) {
	
		return new vcache( $raw_cond );
		
	}

}

?>