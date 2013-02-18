<?php
/*
 * PLAtforM.MySQLService: an  base class that provides direct bridged services to access a specific 
 * MySQL database
 * 
 */
class MySQLService2 implements x_service  {

	// local private debugging
	private static $Debug = false;
	
	// database pool singleton
	private static $DBPool = null;

	// sql connection
	private static $SQL = null;
	
	//! the instance
	private static $instance = null;
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	}
	
	//! returns a reference to the singleton instance of the class
    public static function instance() 
    {
		// if the instance hasn't been created yet
        if (!isset(self::$instance)) {
			// use the current classname
            $C = __CLASS__;
			// and create the instance as a new object of that class
            self::$instance = new $C;
        }

		// return a reference to the instance
        return self::$instance;
    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	
	
	/*
	 * The MySQL data field types are used to translate the raw numbers from the database query 
	 * mysqli_getfields() to something understandable by language-dependent beings
	 */
	private $MySQLFieldType = array (
	
		3 => 'Integer',
		253 => 'String',
		254 => 'Enumerated List',
		1 => 'Boolean',
		7 => 'Timestamp',
		10 => 'Date',
		252 => 'Text',
		300 => 'URL',
		301 => 'Password'
		
	);

	// gives the user the ability to know how many rows were last retrieved by certain data functions.
	public $LastNumRows = 0;
		
	//! get the id for a record, given the search
	public static function getId( $search, $source, $key = 'id' ) {
		// set up logging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		if ( $container->debug )
			echo "<span style='color:orange;'>$tag->event_format : Looking for Id of record, and search = $search</span><br>\r\n";
	
		$query = "SELECT `$key` FROM `$source` " . SQLCreator::getWHEREclause( HumanLanguageQuery::create( $search )->conditions() , get_class()."::".__FUNCTION__ );
		if ( $container->debug ) echo "$tag->event_format: query = $query<br>\r\n";
		if ( $res = self::query( $query ) ) {
		
				
			$row = $res->fetch_assoc();
			$id = $row[$key];
			$res->close();
            if ($container->debug )
                echo "$tag->event_format: returned id is $id<br>\r\n";
			return $id;
		} else {
            return "A SQL error occurred fetching id: ".self::getSQLError().".  The query is $query";
        }
	}
	
	// constructor connects to database
	public static function getSQL( ) {
	
		// get singleton database pool
		self::$DBPool = self::$DBPool ? self::$DBPool : DatabasePool::getSingleton();
		
		return self::$SQL ? self::$SQL : self::$DBPool->getConnection();
	}
	
	// destructor closes database connection
	function __destruct() {  }
	
	/*
	 * getIdsNamesAsArray(): fetches Id and Name pairs from a table as an associative array
	 */
	public function getIdsNamesAsArray( $Source ) {
		
		$NamePairs = array();
		
		$Query = 'SELECT Id, Name FROM `' . $Source . '` WHERE 1';
		
		if ( $Result = $this->query( $Query)) {
			
			while ( $Row = $Result->fetch_assoc() )
				$NamePairs[$Row['Id']] = $Row['Name'];	
			
			$Result->close();	
			
			return $NamePairs;
		} else return null;
		
		
	}
	
	
	/*
	 * fetchAllFrom( $TableName or $ServiceName ): fetch all records from a specific Table
	 * Note: assumes object class name is same as table name when constructing
	 */
	function fetchAllFrom( $TableName ) {
	
		$ReturnObjects = array();
	
		// construct select query to obtain Ids
		$Query = 'SELECT Id FROM `' . $TableName."`";
		
		if ( $Result = $this->MySQL->query( $Query )) {
			while ( $Row = $Result->fetch_assoc()) {
				try {
					/*
					 * instantiate a new Object in the return array, using it's Id as the key
					 * The Table name is the class name for the object,
					 * and when creating it, specify that we are passing the Id to load it from the
					 * database
					 */
					$ReturnObjects[$Row['Id']] = new $TableName( 
						$Row['Id'], true );
				} catch (Exception $e ) { throw $e; }
			}
			$Result->close();
		} else {throw new Exception('MySQLService::fetchAllFrom(): Exception thrown: ' . $this->MySQL->error . ' executing query: ' . $Query); }
	
		return $ReturnObjects;
	}
	
	public function fetchFrom( $Table , $Offset = 0 , $Limit = 10 , $OrderBy = 'Id', $Direction = 'ASC' , $Where = '') {
		
		// an array of return objects
		$ReturnObjects = array();
		
		// construct a query
		$Query = 'SELECT Id FROM `' . $Table . '` ';
		if ( $Where <> '')
			$Query .= ' WHERE ' . $Where . ' ';
		$Query .=  ' ORDER BY ' . $OrderBy . ' ' . $Direction;
		
		// run query
		if ( $Result = $this->MySQL->query( $Query )) {
			// go to offset
			$Result->data_seek( $Offset );
			$this->LastNumRows = $Result->num_rows;
			
			// capture results
			$Counter = 0;
			while ( ($Row = $Result->fetch_assoc()) && ($Counter++ < $Limit) ) {
				try {
					$ReturnObjects[$Row['Id']] = new $Table( $Row['Id'], true );
				} catch (Exception $e ) { throw $e; }
			}
			$Result->close();
		} else {throw new Exception('MySQLService::fetchFrom(): ' . $this->MySQL->error . ' ' . $Query); }
	
		return $ReturnObjects;
		
	}
	
	/*
	 * query( String $Query ): run a SQL query and return a mysqli_result 
	 * this function merely mimics mysqli_query() and is added to make this class a logical superset
	 */
	public static function query( $Query, $caller=null ) { 
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( Debugger::enabled() && $container->debug_level > 2) { echo "$tag->event_format : Query $Query called by $caller<br>\r\n"; }
		$Result = self::getSQL()->query( $Query );
		// performance
        if ($container->performance_tracking) {
            $container->performance('number_sql_queries',1);
            $container->performance('sql_queries',$Query);
        }
		return $Result;	
	}
	
	/*
	 * getFields(): grab the fields for a given table
	 * $Table:			Well.. er, we do need to know the name of the table, right?  :-)
	 */
	public static function getFields( $Table ) {
		
		if ( $Result = self::query( 'SELECT * FROM `' . $Table . '` LIMIT 5')) {
			$Fields = $Result->fetch_fields();
			$Result->close();
			return $Fields;
		} else throw new DatabaseException( 'Exception thrown in ' . get_class() . '::' . __FUNCTION__ . '(): unable to fetch fields, error is : ' . MySQLService2::getSQLError());
		return false;
	}
	
	/*
	 * getNameCol(): get the "name" column for this table, which is usually the one right after Id
	 * $Table:		which to search?
	 */
	public function getNameCol( $Table ) {
		$Fields = $this->getFields( $Table );
		return $Fields[1]->name;
	}	
	
	/* 
	 * getNameById(): given a specific Id, translate it into a name
	 * $Id:				the Id to translate
	 * returns:		  string contents of Name field
	 */
	public static function getNameById( $Id, $Table, $Column = 'Name', $idcol = 'Id' ){

		// construct query from arguments
		$query = 'SELECT ' . $Column . ' FROM `' . $Table . '` WHERE ' . $idcol . '=\'' . $Id . '\'';
		
		if ( self::$Debug )
			Debugger::echoMessage( 'MySQLService2::getNameById(): query = ' . $query );
			
		
		if ( $Result = self::query( $query ) ) {
			if ( $Result->num_rows ) {
				$Row = $Result->fetch_assoc();
				$Result->close();
				return $Row[$Column];
			} 	
		}
		return false;
	}
	
/* 
	 * getNamesById(): given a specific Id, translate it into an array of names
	 * $Id:				the Id to translate
	 * returns:		  array of Name field
	 */
	public static function getNamesById( $Id, $Table, $Column = 'Name', $idcol = 'Id' ){
		$array = array();
		// construct query from arguments
		$query = 'SELECT ' . $Column . ' FROM `' . $Table . '` WHERE ' . $idcol . '=\'' . $Id . '\'';
		
		if ( self::$Debug )
			Debugger::echoMessage( 'MySQLService2::getNameById(): query = ' . $query );
			
		
		if ( $Result = self::query( $query ) ) {
			if ( $Result->num_rows ) {
				while ( $row = $Result->fetch_assoc())
					array_push( $array, $row[$Column]);
//				$Row = $Result->fetch_assoc();
				$Result->close();
	//			return $Row[$Column];
			} 	
		}
		return $array;
	}
	
	
	
	/*
	 * isForeignKey(): returns true if the given column is a foreign key, and false otherwise
	 */
	public function isForeignKey( $Column ) {
	
		if ( strpos( $Column, 'Id') && $Column <> 'Id')
			return true;
		return false;
	}
	
	/*
	 * getRandomFrom( $Table): returns an data object of a randomly selected row 
	 * from the specified table
	 * @Table: which table to use?
	 * @Returns: subclass of (DataObject)
	 * 
	 */
	public function getRandomFrom( $Table ) {
	
		// seed the random number generator
		srand( time() );
		
		// select all columns from a random row
		if ( $NumRows = $this->getNumRows( $Table ) )  {
			$Query = 'SELECT Id FROM `' . $Table . '` WHERE Id=' . ( (rand( ) % $NumRows )+1 );
			if (  $Result = $this->query( $Query ) ) {
				// retrieve the results
				$Row = $Result->fetch_assoc();
				$Result->close();
				
				// create a new object to return
				$Object = new $Table( null, $Row['Id'], true);
				
				return $Object;
				
			} else return null;
		} else return null;
	}
	
	//! record_max is a synonym for getNumRows()
	public static function record_max( $table ){
		return self::getNumRows( $table );
	}
	
	/*
	 * getNumRows(): returns the number of rows in a given table
	 * $Table: the table to check
	 */
	public static function getNumRows( $Table ) {
	
		// holder for result
		$ReturnVal = 0;
		
		// select all rows
		$Query = 'SELECT * FROM `' . $Table."`";
		
		// get the result of query
		$Result = self::query( $Query );
		
		if ( $Result ) {
		
			$ReturnVal = $Result->num_rows;
			
			$Result->close();
			
		}
		
		return $ReturnVal;
	
	}
	
	
	
	/*
	 * getFieldType(): this de-abstraction function parses out a field's type
	 * and uses some built in assumptions
	 */
	public static function getFieldType( $Field ) {
	
		/*
		 * This exception looks for bitmask fields based on the presence of the word
		 * Bitmask in the field name
		 */
		if ( strpos( $Field->name , 'Bitmask' ))
			return 'Bitmask';
			
		/*
		 * This exception looks for ImageURL in the field, which indicates that this is an uploadable image
		 */
		if ( strpos ( $Field->name, 'ImageURL'))
			return 'UploadableFile';
			
		/*
		 * This exception looks for URL fields based on the token URL in the field
		 */
		if ( strpos ( $Field->name, 'URL'))
			return 'URL';
			
		/*
		 * Exception for passwords
		 */
		if ( $Field->name == 'Password')
			return 'Password';
			
		/*
		 * In the general case, we just need to send back the translation of type by Id
		 */
		return ( isset( $this->MySQLFieldType[ $Field->type ] ) ?  $this->MySQLFieldType[ $Field->type ] : $Field->type);	
	}
	
	/*
	 * deabstractDataValue(): a really useful function to display the de-abstracted value  of a specific
	 * field.  In most cases, it does nothing, but for certain field types can be very useful! 
	 * 
	 */
	public function deabstractDataValue( $OrigValue , $Field  ){

		switch ( $this->getFieldType( $Field)) {
		// for bitmasks, explode the value
		case 'Bitmask':
			return $this->parseBitmaskValAsString( $OrigValue, substr_replace( $Field->name, '' , strpos( $Field->name, 'Bitmask')) );
		// in the default case, just leave the value alone
		// for boolean, explode to yes/no
		case 'Boolean':
			return ( $OrigValue ? 'yes' : 'no');
		case 'Timestamp':
		case 'Date':
			//return $OrigValue;
			return date ( 'M j, Y' , strtotime( $OrigValue ));
		default:
			return $OrigValue;
		}
	}
	
	/*
	 * parseBitmaskValAsString(): takes a value from a known bitmask, and creates a tokenized string of the
	 * named values
	 */
	public function parseBitMaskValAsString( $Value, $Table) {
	
		// start with nuttin'
		$String = '';
		
		/*
		 * What's the magic here?  Well... it's simple, really.  Just iterate through all the rows of
		 * the table corresponding to the bitmask.  For each row, use the binary exponent value and check
		 * if set in the $Value.  If so, add that table row's named value to the string
		 */
		for ( $Counter = 0; $Counter < $this->getNumRows( $Table ); $Counter++ ) {
		
			// get the binary power of two for this row (1,2,4,8,...)
			$BinValue = pow( 2 , $Counter);
			
			/*
			 * If that value is set in the bitmask, append the corresponding name
			 * to the string
			 */
			if ( $BinValue & $Value)
				$String .= $this->getNameById( $BinValue , $Table) . ' ';
		}
		
		return $String;	
	}

	/*
	 * getEnumValues(): returns an array of the enumerated possible values for an ENUM field
	 */
	
	protected function getEnumValues( $Table , $Fieldname ) {
	
		// construct a query to retrieve the values
		$Query = 'SHOW COLUMNS FROM ' . $Table . ' LIKE \'' . $Fieldname . '\'';

		if ( $Result = $this->query( $Query ) ) {
			$Row = $Result->fetch_row();
			if(stripos(".".$Row[1],"enum(") > 0) $Row[1]=str_replace("enum('","",$Row[1]);
			else $Row[1]=str_replace("set('","",$Row[1]);
			$Row[1]=str_replace("','","\n",$Row[1]);
			$Row[1]=str_replace("')","",$Row[1]);
			$ar = split("\n",$Row[1]);
			for ($i=0;$i<count($ar);$i++) 
				$arOut[str_replace("''","'",$ar[$i])]=str_replace("''","'",$ar[$i]);
			return $arOut ;	
		}
		
		return null;
	
	}
	
	/*
	 * fetchChildrenAsObjects(): this helpful function receives a DataObject and a Class/Table as parameters,
	 * and returns an array of all of the DataObjects of type Class which are "children" of the original object 
	 * in the database.
	 * 
	 * For example, if you have a Hotel object and Room objects, and you instantiate with:
	 * fetchChildrenAsObjects( $Hotel , 'Room' )
	 * the function will return an array of $Room objects whose parent is $Hotel
	 */
	public function fetchChildrenAsObjects( $Parent, $TableorClass , $AutoLoad = true) {
	
		$ReturnObjects = array();
		
		/*
		 * Note that we make some assumptions to make this query easy:
		 * 1) foreign key field is ClassId, where Class is the parent's class
		 */
		$Query = 'Select Id from `' . $TableorClass . '` WHERE ' . get_class ( $Parent ) . 'Id=\'' . $Parent->get('Id') . '\'';
		
		// iterate through the results, creating new Objects
		if ( $Result = $this->query( $Query ) )
			while ( $Row = $Result->fetch_assoc())
				$ReturnObjects[$Row['Id']] = new $TableorClass($Row['Id'], $AutoLoad, true);

		return $ReturnObjects;
		
	}
		/*
	 * getFieldByName ( $Name ) : return an array def of a field given it's name
	 */
	public static function getFieldByName( $Name ) {

		// make sure fields are loaded
		foreach ($this->Fields as $Field)
			if ( $Field->name == $Name)
				return $Field;
		
		return null;
	}
	
	/*
	 * simpleLookup( $Source, $Lookup, $Target): performs a simple lookup, translating from the 
	 * lookup field to its corresponding target
	 * @Source: database table or view
	 * @LookupField: the column/field to lookup
	 * @LookupValue: the matching value
	 * @Target: the field value to pull
	 * @returns: value of @Target
	 */
	public static function simpleLookup( $Source, $LookupField, $LookupValue, $Target) {

		// construct query
		$Query = 'SELECT ' . $Target . ' FROM `'. $Source . '` WHERE ' . $LookupField . '=\'' . $LookupValue .  '\''; 
		
		if ( $Result = self::query( $Query)) {
			
			if ( $Row = $Result->fetch_assoc()) {
			
				$Result->close();
				return $Row[$Target];
				
			}
				
			
		}
		
		return null;
					
	}
	
	/*
	 * getSQLError() : returns last SQL error
	 * 
	 */
	public static function error() { return self::getSQLError(); }
	public static function getSQLError() { return self::getSQL()->error; }
	
	// real escape string
	public static function real_escape_string( $Arg ) {
	
		$RES = self::getSQL()->real_escape_string( $Arg );
		return $RES;	
			
	}
	
	//! gets the insert id from the last action
	public static function insert_id() {
		return self::getSQL()->insert_id;
	}
	
	//! get the values of a field as an array
	public static function get_as_array( $args_str ) {
	
		$args = new args( $args_str );
	
		$query = "SELECT distinct id, $args->field FROM `$args->key` WHERE 1";
		
		$array = array();
		
		$names = array();
		
		if ( $res = self::query( $query ) ) {
		
			while ( $row = $res->fetch_assoc() ) {
			
				// if already exists, skip it
				if ( ! in_array( $row[$args->field] , $names ) ) {
					array_push( $array, $row[$args->field] . '=' . $row['id']  );
				
					// add to names
					array_push( $names, $row[$args->field] );
				}
			}
			
			$res->close();

			return implode( ',' , $array);
			
		} else return "error:" . self::getSQLError();
		
		
	
	}
	
	// truncate a table
	public static function truncate($table){
		if ( $res = self::query("TRUNCATE TABLE $table")) {
			return true;
		} else return false;
	}
	
	/**
	 * call a database function
	 * @param string $name the name of the function
	 * @param array $args the list of function arguments
	 * @return mixed function call result
	 */
	 public function call_database_function( $name, $args){
	 	$argstr = "";
	 	foreach ( $args as $arg)
	 		$argstr .= (!$argstr)? " $arg":" , $arg";
	 	$q = "SELECT $name( $argstr ) as `result` ";
	 	$result = $this->query( $q );
	 	if ( $result){
	 		$row = $result->fetch_assoc();
	 		return $row['result'];
	 	} else{
			return "$name : ". $this->getSQLError();	 	
	 		
	 	}
	 }
	/**
	 * call a database proc
	 * @param string $name the name of the function
	 * @param array $args the list of function arguments
	 * @return mixed function call result
	 */
	 public function call_database_procedure( $name, $args){
	 	$argstr = "";
	 	foreach ( $args as $arg)
	 		$argstr .= (!$argstr)? " $arg":" , $arg";
	 	$q = "CALL $name( $argstr, @result );";
		$rs = $this->getSQL()->query( $q  );
		$rs = $this->getSQL()->query( "SELECT @result"  );
		if($row = $rs->fetch_object())
		{
			$member = "@result";
			return $row->$member;
		 		
	 	} 
	 }

	public function multi_query($q){ return $this->getSQL()->multi_query($q); }
	public function store_result(){ return $this->getSQL()->store_result(); }

}