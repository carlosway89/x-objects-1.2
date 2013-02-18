<?php

class paginator extends magic_object  {

	//! construct
	public function __construct(
		$class = "paginator",		// css class for elements
		$key,						// business object lookup key
		$size,						// page record size
		$query,						// pass a search term to lookup records
		$page = 1					// current page
	) {
		
		if ( Debugger::enabled() )
			$this->debug = true;
			
		if ( $this->debug )
			echo get_class() . "::__construct( $class, $key, $size, $query, $page)<br>";
		
		// save local vars
		$this->class = $class;
		$this->key = $key;
		$this->size = $size;
		$this->query = $query;
		$this->page = $page;
	
	}
	
	//! magic get 
	public function __get( $what ) {
		switch( $what ) {
		
			// the current group
			case 'current_group':
			
				return ceil( $this->page / 8 );
				
			break;
		
			// the total # of page groups
			case 'total_groups':
			
				return ceil( $this->last_page / 8);
				
			break;
			
			// does this paginator have a previous group?
			case 'has_prev_pages':
			
				return ( $this->current_group > 1 ) ? "" : "hidden";
				
			break;
		
			// does this paginator have a next group of pages?
			case 'has_next_pages':
			
				// true if the number of groups is greater than the current group
				return ( $this->total_groups > $this->current_group ) ? "" : "hidden";
			
			break;
		
			// offset
			case 'offset':
				return ($this->page -1 ) * $this->size + 1;
			break;
			// number of records
			case 'num_records':
				return ObjectFactory::count( $this->key, $this->query );
			break;
			// last page
			case 'last_page':
				return ceil( $this->num_records / $this->size );
			break;
			// next class
			case 'next_class':
			case 'fforward_class':
				return ( $this->page == $this->last_page ) ? "hidden" : "";
			break;
			// show/hide buttons
			case 'rewind_class':
			case 'back_class':
				return ( $this->page == 1) ? "hidden" : "";
			break;
			// get paginator as xHTML
			case 'xhtml':
				return x_object::create( "paginator" )->xhtml( $this );
			break;
			default: 
				// the current class for a page
				if ( preg_match( '/current([0-9]+)/',$what,$matches)) {
					return( ($this->page == (int)$matches[1])?"current":"");
				}
			
				// the group classes determine which group of page numbers to show
				if ( preg_match( '/group([0-9]+)_class/' , $what, $matches)) {
				
					$group = $matches[1];
					$page1 = $group * 8 - 7;
					$page2 = $group * 8;
					// hidden if page is not in this group
					return ( $this->page <= $page2 && $this->page >= $page1) ? "" : "hidden";
				
				}
			
				// the page classes determine which page numbers are shown/hidden
				elseif ( preg_match( '/page([0-9]+)_class/', $what, $matches ))
					return ( $matches[1] > $this->last_page ) ? "hidden" : '';
				else
					return parent::__get( $what );
			break;
		}
	
	}

	//! create a new paginator
	public static function create($class,$key,$size,$query,$page) { 
		return new paginator( $class,$key,$size,$query,$page);
	}

}

?>