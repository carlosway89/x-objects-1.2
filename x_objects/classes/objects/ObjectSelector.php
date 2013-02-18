<?php

//! An ObjectSelector is a ui component to choose one of the objects from a pull down list
class ObjectSelector extends SelectElement {

	//! optional link to show for adding a new one
	private $link = null;
	
	public function __construct(
		$hname,						// html entity name/id
		$source,					// data source, e.g. table
		$id = 'id',					// id field name
		$name = 'name',				// name field name
		$link = null,				// optional link for adding
		$conditions = null,			// optional field specifying conditions for searching records
		$selected = null			// optionally indicate selected value
		) {
	
		$this->link = $link;
		
		$options = array( 'Please choose' => 'null');
		
		// special query to obtain client names and ids
		$query = "SELECT $name, $id FROM $source " . SQLCreator::WHERE( $conditions );
		
		if ( ! $result = MySQLService2::query( $query ) )
			throw new DatabaseException( "ObjectSelector::__construct( $query ): A database exception occurred: " . MySQLService2::getSQLError() );
			
		while ( $row = $result->fetch_assoc() )
			$options[ $row[$name] ] = $row[$id];
			
		parent::__construct("class=selector $hname-selector,name=$hname,id=$hname",null, $options, false, $selected);
		
	}
	
	//! override parent method
	public function getAsHTML() {
	
		if ( $this->link )
			if ( is_object( $this->link ) )
				return parent::getAsHTML() . $this->link->getAsHTML();
			else
				return parent::getAsHTML() . $this->link;
		else 
			return parent::getAsHTML();
	}
	
	//! adds an option
	public function add_option( $name, $value ) {
		
		$this->Options[$name] = $value;
		
	}
	
	//! appends text to a specific option name
	public function append_option_name ( $value , $text ) {
	
		$name = array_search ( $value, $this->Options );
		$this->Options[ $name . ' [myself]' ] = $value;
		unset( $this->Options[$name] );
	}
}