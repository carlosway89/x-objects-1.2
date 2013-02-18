<?php 

abstract class AttributedObject extends x_class {
	
	//constants to reference name value pairs
	const NAME = 0;
	const VALUE = 1;
	
	// constant to replace character for equals sign embedded in javascript
	const JS_EMBEDDED_EQUALS = '%';
	
	//! constat to replace chars for embedded comma
	const JS_EMBEDDED_COMMA = ':';
	
	// holds local attributes as name/values
	private $attributes = array();
	
	public static $parse_colon = true;
	
	//! returns the attributes
	public function attributes() { return $this->attributes; }
	
	// set an attribute
	public function set( $key, $val) { 
		//echo "setting $key =". (string)$val. "<br>";
		$this->attributes[$key] = $val; 
		}
	
	/*
	 * setAttributes( $Attributes): sets attributes from a string of name,value pairs
	 * @Attributes: (string) name/value pairs
	 * @returns: none
	 */
	 protected function setAttributes ( $Attributes , $Separator) {

		// explode name value pairs
		$Attrs = explode( $Separator ,$Attributes );
		
		// iterate through and set each one
		foreach ( $Attrs as $Attr) {
			$NVPair = explode( '=' ,  $Attr );
			if ( $this->Debug)
				Debugger::echoMessage( get_class() . '::setAttributes(): got pair Name,Value=' . $NVPair[self::NAME] . ',' . $NVPair[self::VALUE]);
			if ( isset( $NVPair[self::VALUE] ) )
				$this->set( $NVPair[self::NAME] , 
					//$this->replaceJSEmbeddedComma( 
					$this->replaceJSEmbeddedEquals($NVPair[self::VALUE] ) 
					//) 
					);
		}
	}
	 	
	
	// get an attribute
	public function get( $key ) { 
		if ( isset( $this->attributes[ $key ] ) )
			return $this->attributes[ $key ];
		else return null;
	}
	
	/*
	 * getAttributesAsString(): returns all attributes as a string of name=value
	 * @returns: the string of name values
	 */
	protected function getAttributesAsString() {
		
		$String = '';
		foreach ( $this->attributes as $Name => $Value )
			$String .= "$Name=\"$Value\" ";
		
		if ( $this->Debug )
			Debugger::echoMessage( get_class() . '::getAttributesAsString() String=' .$String);	
		return $this->replaceSpecialChars( $String );
		
	}
	
	//! replace special characters for a string
	private function replaceSpecialChars( $string ){
		
		return preg_replace( '/[\^]/', ',', $string);
	}
	
	/*
	 * replaceJSEmbeddedEquals( $Attribute ): replaces the default embedded equals symbol
	 * within JS code for an equals sign
	 * @Attribute: the attribute to replace
	 * @returns: same string after regex replace of character
	 */
	 private function replaceJSEmbeddedEquals( $Attribute) {
	 	
	 	return preg_replace( '/[%]/', '=', $Attribute);
	 	
	 }
	 
	 //! replace embedded comma in JS
	 private function replaceJSEmbeddedComma( $string ) {
		
			if ( self::$parse_colon )
				return preg_replace( '/[:]/', ',', $string);
			else return $string;
	}
	
	//! get attribute names
	public function attribute_names() {
	
		return array_keys( $this->attributes );
		
	}
	
		// magic set
	public function __set( $what, $how ) {
	
		$this->set( $what, $how );
	}
	
	// magic get
	public function __get( $what ) {
	
		return $this->get( $what );
	}
	
	/**
	 * convert to a string
	 */
	public function __toString(){
		$str = "{ ";
		foreach ( $this->attributes as $name=>$val) {
			if( is_array( $val))
				$val = new xo_array($val);
			$str .= "'$name' = '$val',";
		}
		//print_r( $this->attributes);
		return "$str } ". count( $this->attributes) . " magic members.";	
	}

}
?>
