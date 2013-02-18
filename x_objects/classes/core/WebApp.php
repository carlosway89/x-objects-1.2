<?php

 //! an instance of a web application
 
 class WebApp {
 
	//! the xml for this app
	private $appXml = null;
	
	//! app key/name
	private $key = null;
	
	//! construct a new WebApp given its key
	public function __construct( $key ) {
		$this->key = $key;
			
		global $directory_name,$container;

		// app xml now comes from x-objects.xml
		$this->appXml = $container->config->app;
		/*		
		try {

			// load the xml file associated with this app
			$this->appXml = new RealXML( $key );
			*/
		
		// if necessary, set IE7 compatibility mode
		$ie7 = $this->appXml->xpath("/$key/config/ie7-compatible" );
		if ( count( $ie7 )  )
			RealXML::$ie7_compatible = (string) $ie7[0];
		
		/*} catch ( ObjectNotInitializedException $e ) {
			
			throw new ObjectNotInitializedException(
"WebApp::app(): Sorry, I am unable to load the application <strong>$this->key</strong> because I cannot find its associated xml file <strong>$key.xml</strong>.  Please create the file, and save it in <strong>/xml</strong> or <strong>/x_objects/xml</strong>" );
		}
	*/
		
	}
 
	//! get a specific page
	public function page ( $name ) {
		return new WebPage( $name );
	}
	
	//! magic get
	public function __get( $what ) {
		
		// case 1: getting a service
		if ( preg_match( '/([a-zA-Z0-9])+(service){1}/' , strtolower( $what ) ) )
			//return $what::instance();
			return call_user_func( "$what::instance");
			
		// case 2: get the configuration for the current app
		if ( $what == 'configuration' || $what == 'config') {
			$matches = 	$this->appXml->find( "/$this->key/config");
			return $matches[0];			
		}
		
		switch ( $what ) {
			// get managed services
			case 'managed_services':
				$services=array();
				foreach( $this->xml->managed_services->service as $service) {
					$index = (string) $service["name"];				
					$services[ $index ] = new managed_service($service);
				}
				return $services;
			break;
			case 'xml':
			
				return get_class($this->appXml)=== 'SimpleXMLElement'? $this->appXml:$this->appXml->xml();
				
			break;
		
		
			case 'name':
			
				return $this->key;
				
			break;
			
		}
		
	}
	
	//! redirect the app page
	public function redirect( $to ) {
		Utility::redirectTo($to);
	}
	
	//! return a registered or unregistered service bean
	public function service( $key ) {
	
		return call_user_func( "$key::instance" );
	}
}

?>