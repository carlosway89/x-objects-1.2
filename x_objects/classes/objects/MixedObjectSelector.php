<?php

//! An MixedObjectSelector is a ui component to choose one of the objects from a pull down list of mixed object types
class MixedObjectSelector extends SelectElement {

	//! optional link to show for adding a new one
	private $link = null;
	
	public function __construct(
		$hname,						// html entity name/id
		$sources,					// data sources as array
		$nvpairs					// name-id pairs for the sources
		) {

		// prime the options
		$options = array( 'Please choose' => '');
		
		// construct queries
		foreach ( $sources as $sname => $conditions ) {
			
			$name = $nvpairs[$sname][0];
			$id = $nvpairs[$sname][1];
			
			// construct a custom query
			$query = "SELECT $name, $id FROM $sname " . SQLCreator::WHERE( $conditions );
			
			// if we didn't get a result
			if ( ! $result = MySQLService2::query( $query ) )
				throw new DatabaseException( "MixedObjectSelector::__construct(): A database exception occurred executing '$query': " . MySQLService2::getSQLError() );
			
			// grab the results and add them to the selector
			while ( $row = $result->fetch_assoc() )
				$options[ $row[$name] ] = $row[$id];
		}	
		
		// construct html SELECT element
		parent::__construct("class=mixed-selector $hname-selector,name=$hname,id=$hname",null, $options, false);
		
		
	}
	
	//! override parent method
	public function getAsHTML() {
	
		if ( $this->link )
			return parent::getAsHTML() . $this->link->getAsHTML();
		else 
			return parent::getAsHTML();
	}
}