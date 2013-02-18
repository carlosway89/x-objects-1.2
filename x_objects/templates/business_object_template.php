<?php
class business_object_template extends business_object {
	
	// data source specification
	const datasource = 		
		"<datasource>
			<name>table_name</name>
			<type>table</type>
			<alias>table_name</alias>
			<key>id</key>
			<read_only>created_date</read_only>
			<required>name</required>
		</datasource>";
	
	// save xml objects for reuse
	private static $xml_obj = null;
	private static $xml_src_obj = null;
	
	//! magic set
	public function __set( $what, $how ){
		switch( $what){
			default:
				parent::__set( $what, $how);
			break;
		}
	}
	
	// constructor
	public function __construct( $search = null) {
		if ( ! self::$xml_obj)
			self::$xml_obj = simplexml_load_string( 
		"<bo-business_object_template>".self::datasource."</bo-business_object_template>"
		);
		$this->xml_obj = self::$xml_obj;
		parent::__construct( get_class(), $search );
		
	}

	//! get datasource in static context
	public static function source() {
		if ( ! self::$xml_src_obj)
			self::$xml_src_obj = simplexml_load_string( self::datasource );
		return new DataSource2( self::$xml_src_obj); 
	}
	
	//! magic get
	public function __get( $what ) {
		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		switch( $what ){
			default:
				return parent::__get( $what );
			break;
		}
	}
	
}
?>