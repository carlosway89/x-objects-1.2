<?php

// an object representation of a service, managed by the application and container
class managed_service extends magic_object {
	const def_page_max = 10;

	//! construct one using its xml descriptor
	public function __construct( $xml ){
		global $container;
		$this->xml = $xml;
	}
	
	//! magic get
	public function __get( $what ){
		switch( $what ){
			// class for enable/disable empty
			case 'empty_enabled_class':
				return ((string)$this->xml["empty"] == "yes")? "empty-enabled":"empty-disabled";
			break;
			// get display name
			case 'display_name':
				return ucfirst( $this->name );
			break;
			// get the key
			case 'key':
				return (string) $this->xml["key"];
			break;
			// get page max
			case 'page_max':
				return ( $this->record_max < self::def_page_max ) ? $this->record_max : self::def_page_max;
			break;
			// get max number of records of this type
			case 'record_max':
				return mysql_service::record_max( (string) $this->xml["source"]);
			break;
			// return the name of the service
			case "name":
				return (string) $this->xml["name"];
			break;
			default:
				return parent::__get($what);
			break;
		}
	}

}

?>