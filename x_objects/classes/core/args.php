<?php
/**
 * args() is a helper class that encapsules a set of args passed as name-value pairs in a REST string
 * for example:
 * http://www.mysite.com/api/business/user/create/firstname:david,lastname:owen
 *
 * This is then passed to the API as:
 * &args=firstname:david,lastname:owen
 *
 * The API then creates a new args() object to give access to the nv pairs either individually or as an array":
 * 
 * # returns "david"
 * $args->firstname
 */
class args  {

	/**
	 * @property array $members the array of all members as name-value pairs
	 */
	public $members = array();

	public function __construct( $args_str) {
	
		// split by commas and save each one
		foreach ( explode( ',' , $args_str ) as $arg_str ) {
			$pair = explode( ':', $arg_str );
			$this->$pair[0] = $pair[1];
			//echo "$pair[0] = $pair[1]<br>";
		}
	
	}
	/** returns the value of a member by name
	 * @param string $what the name of the member
	 * @return mixed the value of the member
	 */
	public function __get( $what ) {
	
		return ( isset( $this->members[$what ])) ? $this->members[$what] : null;
	}
	/**
	 * private method to set members, invoked by constructor
	 * @param string $what the name of the member to set
	 * @param mixed $value the value of the member to set
	 */
	private function __set( $what, $val ) {
	
		$this->members[$what] = $val ;
	}
	

}

?>