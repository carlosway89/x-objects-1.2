<?php 
/* source: http://www.iamcal.com/publish/articles/php/search/
 * RegexSearch: class to input a search term and create a regex search expression from it
 * for use with MySQL and PHP
 */
class RegexSearch {

	private $debug = true;
	
	// member to hold the terms for search
	private $Terms = '';
	private $Parts = array();
	
	public function __construct( $SourceTerms, $cols ) {
	
		// toggle debugging
		$this->debug = ( Debugger::enabled() ) ? true : false;
		
		$this->Terms = $SourceTerms;
		$this->Terms = $this->search_split_terms($this->Terms);
		$terms_db = $this->search_db_escape_terms($this->Terms);
		foreach($terms_db as $term_db){
			foreach ( $cols as $col )
				$this->Parts[] = 'OR:' . $col . " RLIKE '$term_db'";
		}
		
		$this->Parts = implode(',', $this->Parts);
		
		if ( $this->debug ) {
			echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . 
			": sourceterms $SourceTerms, cols $cols<br>";
			echo "terms:<br>";
			print_r( $this->Terms);
			echo "<br>parts:<br>";
			print_r( $this->Parts);
			echo "<br>";

		}
	}
	
	private function search_transform_term($term){
		$term = preg_replace("/(\s)/e", "'{WHITESPACE-'.ord('\$1').'}'", $term);
		$term = preg_replace("/,/", "{COMMA}", $term);
		return $term;
	}
	
	private function search_split_terms($terms){

		$STT = $this->search_transform_term('\$1') ;
		$terms = preg_replace("/\"(.*?)\"/e", "$STT", $terms);
		$terms = preg_split("/\s+|,/", $terms);

		$out = array();

		foreach($terms as $term){

			$term = preg_replace("/\{WHITESPACE-([0-9]+)\}/e", "chr(\$1)", $term);
			$term = preg_replace("/\{COMMA\}/", ",", $term);

			$out[] = $term;
		}

		return $out;
	}
	
	private function search_escape_rlike($string){
		return preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
	}
	
	private function search_db_escape_terms($terms){
		$out = array();
		foreach($terms as $term){
			//$out[] = '.*[[:<:]]'.AddSlashes($this->search_escape_rlike($term)).'[[:>:]].*';
			$out[] = '.*'.AddSlashes($this->search_escape_rlike($term)).'.*';
		}
		return $out;
	}
	
	
	public function asString() { 
		if ( $this->debug ) 
			echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . 
			": parts $this->Parts<br>";
			
		return $this->Parts; 
		
		}

	//! create a new regexsearch
	public static function create( $query, $columns ) {
	
		return new RegexSearch( $query, $columns );
		
	}
}
?>