<?php 
/*
 * @Project:	Platform
 * @Module:		classes/HTMLAttributes
 * @extends:	classes/AttributedObject
 * @Created:	Sept 26, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object that can have attributes set or get
 */
class HTMLAttributes extends AttributedObject  {
	
	//constants to reference name value pairs
	const NAME = 0;
	const VALUE = 1;
	
	// constant to replace character for equals sign embedded in javascript
	const JS_EMBEDDED_EQUALS = '%';
	
	// holds local attributes as name/values
	private $Attributes = array();
	
	// set an attribute
	public function set( $Attribute, $Value) { $this->Attributes[$Attribute] = $Value; }
	
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
			$this->set( $NVPair[self::NAME] , $this->replaceJSEmbeddedEquals($NVPair[self::VALUE] ));
		}
	}
	 	
	
	// get an attribute
	public function get( $Attribute ) { return isset( $this->Attributes[$Attribute]) ?  $this->Attributes[$Attribute] : null; }
	
	/*
	 * getAttributesAsString(): returns all attributes as a string of name=value
	 * @returns: the string of name values
	 */
	public function getAttributesAsString() {
		
		$String = '';
		foreach ( $this->Attributes as $Name => $Value )
			$String .= "$Name=\"$Value\" ";
			
		return $String;
		
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
	
}
?>
