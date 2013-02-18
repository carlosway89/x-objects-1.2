<?php
class xo_twitter_api extends business_object {
	// save xml objects for reuse
	private static $xml_obj = null;
	private static $xml_src_obj = null;
	
	// constructor
	public function __construct( $search = null) {
		if ( ! self::$xml_obj)
			self::$xml_obj = simplexml_load_string( 
		"<xotwapi>
			<!-- specify the datasource for this business object -->
			<datasource>
				<name>xo_twitter_api</name>
				<type>table</type>
				<alias>xo_twitter_api</alias>
				<key>id</key>
				<read_only></read_only>
			</datasource>
		</xotwapi>"
		);
		$this->xml_obj = self::$xml_obj;
		
		parent::__construct( get_class(), $search );
	}

	//! get datasource in static context
	public static function source() {
		if ( ! self::$xml_src_obj)
			self::$xml_src_obj = simplexml_load_string("<datasource>
				<name>xo_twitter_api</name>
				<type>table</type>
				<alias>xo_twitter_api</alias>
				<key>id</key>
				<read_only></read_only>
			</datasource>"
		); 
		return new DataSource2( self::$xml_src_obj);
	}
	//! magic get
	public function __get( $what ) {
		switch( $what ){
			default:
				return parent::__get( $what );
			break;
		}
	}
	
	public static function create( $search = null ) { return new xo_twitter_api($search); }

}
?>