<?php

//! ids names array
class ids_names_array extends AttributedObject {

	//! the array
	private $the_array = array();

	//! construct with key, values, names
	public function __construct( $key, $id, $name, $query = null ) {
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( Debugger::enabled() )
			$this->debug = true;
	
		// handle query
		$where = " WHERE 1";
		if ( $query )
			$where = SQLCreator::WHERE( HumanLanguageQuery::create( $query )->conditions() );
			
		// handle multiple ids
		$ids = array();
		if ( preg_match( '/\./',$id)) {
			$ids = explode('.',$id);
			$first = true;
			$id = '';
			foreach( $ids as $anId) {
				if ( ! $first )
					$id .= ', ';
				$id .= $anId;
				$first = false;
			}
		}
	
		$query = " SELECT $id, $name FROM $key  $where ORDER BY $name ASC";
		if ( $container->debug)
            echo "$tag->event_format: query = $query<br>\r\n";

		if ( $result = MySQLService2::query( $query, get_class()."::".__FUNCTION__ ) ) {
		
			while ( $row = $result->fetch_assoc() ) {

                if ( $container->debug)
                    echo "$tag->event_format: found an assoc arrray". new xo_array( $row). "<br>\r\n";
			
				$index = '';
				if ( ! count( $ids ))
					$index = $row[ (string)$id ];
				else foreach( $ids as $anId )
					$index .= $row[ (string)$anId]. ",";
				$value = '';
				if ( preg_match( '/,/', (string)$name)) {
				
					$fields = explode( ',',$name);
					foreach( $fields as $field)
						$value .= $row[$field] . " ";
						
				} else $value = $row[ (string)$name ];
				$this->the_array[ $index ] = $value;
			
			}
		
		
		} else $container->exception( xexception::uninitialized_object, $tag, "Failed to load data ( $query ) because: " . MySQLService2::getSQLError() );
	
	}
	
	public function __set( $what, $val ) { $this->set( $what, $val ); }
	
	public function __get( $what ) {
	
		switch ( $what ) {
			
			case 'the_array':
			
				return $this->the_array;
				
			break;
		
			default:
				return $this->get( $what );
			break;
		}
	}
	
	//! create a new one
	public static function create( $key, $id, $name, $query = null ) {
	
		return new ids_names_array( $key, $id, $name , $query );
	}
	
}

?>