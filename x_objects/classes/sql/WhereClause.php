<?php
//! object representation of a SQL where clause

class WhereClause extends SQLClause {

	//! holds the conditions as an array
	private $conditions = array();
	
	//! construct with optional clause condition
	public function __construct( $conditions ) {
	
		$pairs = explode( 'AND', $conditions);
	
		$this->conditions = $pairs;
	
	}
	
	//! implementation of asString() from interface Stringable
	public function asString() {
	
		$string = ' WHERE ';
		
		foreach ( $this->conditions as $condition ) {
			$string .= ! strcmp( $string, ' WHERE ') ? '' : ' AND ';
			$string .= $condition;
		}
			
		return $string;
	}

}
?>