<?php

//! class to manage an online instant google-like search

class RealSearch extends AttributedObject {

	private $debug=false;
	
	public function __construct( $key, $query, $view , $type = 'RLIKE', $filters = null, $page=1, $size=10) {
	
		if (Debugger::enabled()) $this->debug=true;
		
		$this->key = $key;
		$this->query = $query;
		$this->view = $view;
		$this->type = $type;
		$this->filters = $filters;
		$this->page = $page;
		$this->size = $size;
		
		if ( $this->debug ) 
			echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . ": key $this->key query $this->query view $this->view".
				" type $this->type filters $this->filters page $this->page size $this->size<br>";
	}
	
	public function __call( $what, $args ) {
	
		switch( $what ) {
		
			case 'execute':
			
			
				// take into account the search size
				$this->filters .= ( $this->filters ) ? ",LIMIT $this->size" : ",LIMIT $this->size";
				
				// take into account moving to a new page
				if ( $this->page > 1) {
					$offset = ( $this->page -1) * $this->size;
					$this->filters .= ( $this->filters ) ? ",OFFSET $offset" : ",OFFSET $offset";
				}
			
				$this->results = ObjectFactory::search( $this->key , $this->type, $this->query ,$this->filters);
				

				return $this;
			break;
			case 'xhtml':
				$html = '';
				
				// use a wrapper
				if ( isset( $args[0]) && $args[0] )
					$html .= '<div class="realsearch-wrapper">';
					
				foreach( $this->results as $result)
					$html .= $result->html( $this->view );
					
				// optional paginator
				if ( $paginator = $this->paginator( $this->page ) )
					$html .= $paginator->xhtml;
				
				// close wrapper
				if ( isset( $args[0] ) && $args[0] )
					$html .= '</div><!-- end wrapper -->';
					
				if ( ! count( $this->results ) )
					$html = '<div class="realsearch">the search returned no results</div>';
				return $html;
			break;
		}
	
	}
	
	public function __get( $what ) {
	
		return $this->get( $what );
	}
	
	public function __set( $what, $val ) {
	
		$this->set( $what, $val );
	}
	
	//! create a new search
	public static function create( 
		$key, 
		$query, 
		$view , 
		$type = 'RLIKE',
		$filters = null,		// optional filter conditions
		$page = 1,				// optional set starting page
		$size = 10
		) {
	
		return new RealSearch( $key , $query, $view , $type, $filters, $page,$size);
		
	}
	
	//! get a paginator for the search results:
	public function paginator( $page ) { 
	
		// convert search query into conditions
		$obj = new $this->key();
		$conditions = Search::rlike_clause( $this->query, $obj->search_columns );
		$conditions .= $this->filters;
				
		return new paginator( "paginator" ,$this->key,$this->size,
			$conditions,
			$page);
	}

}

?>