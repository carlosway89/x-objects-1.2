<?php 
/*
 * Project:			platformphp
 * Module:			Search
 * Version:			1.0
 * Author(s):		David Greenberg
 * Descr:			Provides Regex Search for database
 */
class Search {
	private $debug=false;
	
	//! string or array of strings representing sources
	private $sources = null;
	
	private $IdField = 'id';

	//! results
	private $results = array();
	
	/*! construct a new Search
	 *  \param $sources string or array of strings of sources to search
	 *  \returns new Search object
	 */
	function __construct( 
		$sources,
		$IdField = 'id') {

		if (Debugger::enabled()) $this->debug=true;
		
		// save sources for use
		$this->sources = $sources;
		
	}
	
	public function search( ) {
	
		// loop through all the sources
		foreach ( $this->sources as $source ) {
		
			// get the datasource (workaround since PHP does not support static methods for variable classnames)
			$object = new $source();
			$dataSource = $object->getDataSource();

			// pass through each field in the source and construct a search term
			$fields = $dataSource->fields();
			if ( ! is_array( $fields ))
				echo 'ops!';
			foreach ( $fields as $field ) {
			
				// construct the regex
				$reg = new RegexSearch( $this->searchTerm(), $field->name );
		
				// get the search string as regex
				$searchExpr = $reg->asString();
				
				// add results
				$results->add( ObjectFactory::create( $source, null, null,null,null,
					new WhereClause( $searchExpr) ));
				
			}
		}
		
		return $results;
	}
	
	//! return an rlike clause as a string
	public static function rlike_clause ( $query, $cols ) {
		$debug = ( Debugger::enabled() ) ? true : false;
		if ( $debug ) {
			echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . 
				": query $query<br>";
			print_r( $cols);
			echo "<br>";
		}
		return RegexSearch::create( $query , $cols )->asString();
	
	}
	
	//! return the current search object as HTML
	public function getAsHTML() {
		
		// if no search results, get a search form
		if ( ! $this->hasResults ) {
			
			$search = new DivElement( 'class=search',
				// div wraps a form to submit search
				new FormElement( 'class=searchform,method=post,action=search.php', 
					array(
						// form consists of an input field and a button
						new InputElement('id=searchterm,name=searchterm,class=searchinput,onclick=this.value%\'\',value=' . LANG_SEARCHBAR_HINT ),
						new ButtonElement('class=searchbutton,type=submit,' , LANG_SEARCHBUTTON_SUBMIT)
					) // end array
				) // end FormElement constructor
			); // end DivElement constructor
			
			return $search->getAsHTML();
		}
	}
	
	//! returns current search object as a string
	public function getAsString() { return $this->getAsHTML(); }
	
	//! displays current search object
	public function display() { echo $this->getAsHTML(); } 
	
	//! returns the current searchterm, if set
	public function searchTerm() { return isset( $_REQUEST['searchterm'] ) ? $_REQUEST['searchterm'] : false; }
	
	//! sets the display mode
	public static function setDisplayMode ( $mode ) { self::$displayMode = $mode; }
	
	//! gets the display mode
	public static function getDisplayMode() { return self::$displayMode; }
}
?>