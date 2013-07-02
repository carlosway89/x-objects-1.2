<?php 
/*! singleton to provide access to a pool of database connections as mysqli objects or mysql handles.
 */
class DatabasePool {

	// use this referance for the main connection
	const DEFAULT_CONN = 1;
	// this is for the legacy (mysql lib) connection
	const LEGACY_CONN = 2;
	// Since this class is a Singleton, we must hold an instance
	private static $Instance;
	private $Connections = array ( self::DEFAULT_CONN => null , self::LEGACY_CONN => null );
	//! connection xml
	private $xml = null;
	
	// private constructor, so the class may not be instantiated
	private function __construct() {
		// load connection data
		global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		$this->xml = $container->config->database;
        if ( $container->debug && $container->debug_level >=2 ) echo "$tag->event_format: xml config is ".$this->xml->asXML(). "<br>";
	}
	
	/*
	 * getSingleton: return single instance of this class
	 */
    public static function getSingleton() 
    {
    	//echo 'getSingleton() ';
        if (!isset(self::$Instance)) {
            $C = __CLASS__;
            self::$Instance = new $C;
        }

        return self::$Instance;
    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
	/*
	 * getConnection(): return a connection from the pool
	 */
	public function getConnection() {
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug && $container->debug_level >2)
            echo "$tag->event_format: about to get connection<br>\r\n";
        if ( ! $this->Connections[self::DEFAULT_CONN]) {
            if ( ! class_exists('mysqli',false)){
                throw new ApplicationException("$tag->event_format: The mysqli library for connectivity to the database is not installed.  For this reason, the application cannot run.");
            }
			$this->Connections[self::DEFAULT_CONN] =
				new mysqli( 
					(string)$this->xml->host, 
					(string)$this->xml->username,
					(string)$this->xml->password,
					(string)$this->xml->database,
					(int) $this->xml->port,
					(string)$this->xml->socket
				);
		
			// set the charset to UTF-8 for best international support
			$this->Connections[self::DEFAULT_CONN]->set_charset('utf8');
					
			// throw an exception if unable to connect
			if ( $this->Connections[self::DEFAULT_CONN]->connect_error)
				throw new DatabaseException('DatabasePool::getConnection(): An error occurred while connecting to the database server: ' . $this->Connections[self::DEFAULT_CONN]->connect_error);
		
		}
        if ( $container->debug && $container->debug_level >2)
            echo "$tag->event_format: done getting connection, returning it<br>\r\n";

        return $this->Connections[self::DEFAULT_CONN];
	
	}

	/*
	 * getLegacyConn(): return a legacy connection, which is the older mysql() lib
	 * in PHP
	 * @returns: connection handle
	 */
	 public function getLegacyConn() {

		if ( ! $this->Connections[self::LEGACY_CONN]) {
		
			$this->Connections[self::LEGACY_CONN] = mysql_connect( DB_HOST, DB_USERNAME, DB_PASS, DB_NAME);
		
			// set the charset to UTF-8 for best international support
			// doesn't work: mysql_set_charset('utf8' , $this->Connections[self::LEGACY_CONN]);
					
			// throw an exception if unable to connect
			if ( mysql_error(  $this->Connections[self::LEGACY_CONN]) )
				throw new DatabaseException('DatabasePool::getConnection(): An error occurred while connecting to the database server: ' . mysql_error( $this->Connections[self::LEGACY_CONN] ));
		
		}
		return $this->Connections[self::LEGACY_CONN]; 
	 	
	 	
	 }
	

}

?>
