<?php

/*! The ObjectFactory provides a few convenient static methods for retrieving large arrays
 * of objects, with as few parameters as possible.
 *  
 * Project:			Platform
 * Module:			classes/ObjectFactory
 * 
 * Purpose:			Factory methods to create objects of different types, especially
 * 					those bound by data
 * 
 * Created by:		David Owen Greenberg <david.o.greenberg@gmail.com>
 * On:				20 Oct 2010
 */
 class ObjectFactory {
	
	//! a flag for turning on debugging for the factory.
	private static $Debug = false;
	
	//! a pointer to the database pool singleton
	private static $DBPool;

	//! a pointer to the database connection
	private static $SQL;
	
	//! vcache pointer status
	public static $vcache_pointer_status = 'fresh';
	
	//! using vcache pointer
	public static $using_vcache_pointer = false;
	
	
	//! sort a group of objects with usort and a given function
	public static function sort( $objects , $function = null // degrades gracefully when no function provided
	) {
		
		
		// only sort if a callback was provided
		if ( $function ) {
			// set up usort parameters
			$keys = array_keys( $objects );
			$class = get_class ( $objects[$keys[0]] );
			usort( $objects , array( $class, $function ) );
		}
		
		return $objects;
	}
	
	//! synonym for createAll()
	public static function create_all( $classname ) { return self::createAll( $classname ); }
	
	/*! createAll( $Classname ): create new objects from 
	 * all rows in given data source
	 * \param Classname: (type: String): data source, as name of data object subclass
	 * \returns: (array of subclass of DataObject) array of newly created objects
	 */
	public static function createAll( $ClassName ) {

		return self::create( $ClassName, null,10000,null,null,null,null);
	}
	
	//! count the number of objects
	public static function count( $key, $query) {
	    global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug)
            echo "$tag->event_format: key=$key , query=$query<br>\r\n";
		// set the return class
		$source = call_user_func( x_objects::instance()->key_to_class( $key) . "::source");
		// construct a sql query
		$query = "SELECT count(`$source->id_column`) as `total` FROM `$source->name`  " .
			SQLCreator::WHERE( HumanLanguageQuery::create( $query)->conditions );
        if ( $container->debug)
            echo "$tag->event_format: SQL query=$query<br>\r\n";
		if ( $result = MySQLService2::query( $query, get_class()." ".__FUNCTION__ )) {
		
			$row = $result->fetch_assoc();
            if ( $container->debug)
                echo "$tag->event_format: SQL returned assoc row is ".new xo_array($row)."<br>\r\n";

			$result->close();
			return (int)$row['total'];
		
		} else throw new DatabaseException( MySQLService2::getSQLError() . " SQL: $query" );
	}

     public static function create_assoc(
             $classname, // the name of the class for objects to be created
             $offset = null, // where to begin the array from the total set
             $limit = null, 	// limit how many to call
             $sortBy = null, // what field to sort by
             $direction = 'ASC', // what direction to sort
             $conditions = null
         ){
         return self::create(
             $classname, // the name of the class for objects to be created
             $offset, // where to begin the array from the total set
             $limit, 	// limit how many to call
             $sortBy, // what field to sort by
             $direction, // what direction to sort
             $conditions,
             true
         );
     }
	/*
	 * createFrom( $Source ,$Offset, $Limit, $Sortby, $Direction): create new objects from 
	 * specific rows in given data source
	 * @Source: (DataSource): data source, as table, view or query
	 * @Offset: begin receiving at this offset
	 * @Limit: limit the results to the given number of records
	 * @Sortby: sort by this column
	 * @Direction: sort in this direction
	 * @returns: (DataObject) array of newly created objects
	 */
	public static function create( 
		$classname, // the name of the class for objects to be created
		$offset = null, // where to begin the array from the total set
		$limit = null, 	// limit how many to call
		$sortBy = null, // what field to sort by
		$direction = 'ASC', // what direction to sort
		$conditions = null,
        $associative = false
	) {
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// convert as HLQ
		$conditions = HumanLanguageQuery::create($conditions)->conditions();

        //echo "$tag->event_format: sortBy=$sortBy<br>\r\n";
		if ( Debugger::enabled() )
			self::$Debug = true;
			
		// keep a limit flag to check for a vcache update
		$vcache_limit_check = null;
		
		if ( $container->debug )
			echo "<span style='color:#8b008b;'>$tag->event_format : classname = $classname, conditions = $conditions</span> <br>\r\n";
		// set the return class
		
		$class = x_objects::instance()->key_to_class( $classname);
		if ( ! class_exists( $class ))
			throw new Exception( get_class() . "::create(): Oops!  Looks like the calling function was trying to manufacture objects of type <strong>$classname</strong> (<strong>$class</strong>), but I cannot find any such class!");
		$source = call_user_func( "$class::source");
		
		$objects = array();
	
		// construct select query to obtain Id
		$query = 'SELECT ' . $source->keycol() . ' FROM `' . $source->name()."`";
		if ( is_object( $conditions ) ) {
			if ( self::$Debug )
				echo "asString()";
			
			$query .= $conditions->asString();
		}
		else 
			$query .= SQLCreator::getWHEREClause( $conditions );

			if ( self::$Debug )
				echo "$tag->event_format : query = $query<br>\r\n";
			
		// if sortby enabled specify it
		if ( $sortBy )
			$query .= ' ORDER BY ' . $sortBy . ' ' . $direction;

		// check for embedded group by
		if ( preg_match( '/GROUP BY ([a-zA-Z0-9_]+){1}/' , $conditions , $matches) )
			$query .= " GROUP BY $matches[1] ";
			
		// check for embedded group by
        /*
		if ( preg_match( '/ORDER BY ([a-zA-Z0-9_]+){1} ([A-Z]+)/' , $conditions , $matches) )
			$query .= " ORDER BY $matches[1] $matches[2]";
		if ( preg_match( '/ORDER BY `([a-zA-Z0-9_]+){1}` ([A-Z]+)/' , $conditions , $matches) )
			$query .= " ORDER BY `$matches[1]` $matches[2]";
		*/
			
		// add limit if any
		if ( $limit ) {
			
				$query .= ' LIMIT '. $limit . ' ';
				$vcache_limit_check = $limit;
		}
		
		// check for embedded limit
		if ( preg_match( '/LIMIT ([0-9]+){1}/' , $conditions , $matches) ) {
				$query .= " LIMIT $matches[1] ";
				$vcache_limit_check = $matches[1];
		}

        // if we had an offset include it
       //if ( preg_match('/offset ([0-9]+)/',$conditions,$hits))
         // $query .= " LIMIT 10000 OFFSET $hits[1]";


        // check for embedded offset
		if ( preg_match( '/OFFSET ([0-9]+){1}/' , $conditions , $matches) )
			$query .= " OFFSET $matches[1] ";
			
		if ( self::$Debug && $container->debug_level > 2)
			Debugger::echoMessage('ObjectFactory::createFrom(): Query=' . $query);
		//$container->log( xevent::debug, "$tag->event_format : query=$query");
		
		if ( $result = MySQLService2::query( $query, get_class()." ".__FUNCTION__ )) {
			if ( $result->num_rows < $vcache_limit_check ) 
					self::$vcache_pointer_status = 'stale';
				
			if ( self::$Debug ) {
				Debugger::echoMessage( 'ObjectFactory::createFrom(): query successful, row=' . $result->num_rows);
				
			}
			// if an offset is given, move to it
			if ( $offset ) {
				if ( self::$Debug )
					echo get_class() . ":setting offset $offset<br>";
					
				$result->seek( $offset );
			}
			
			$row = null;
			while ( $row = $result->fetch_assoc()) {
			
				if ( self::$Debug )
					print_r ( $row );
				
				try {
					/*
					 * instantiate a new Object in the return array, using it's Id as the key
					 * The Table name is the class name for the object,
					 * and when creating it, specify that we are passing the Id to load it from the
					 * database
					 */
					$keycol = $source->keycol();
					
					if ( self::$Debug ) {
						echo get_class() . ": keycol = $keycol<br>";
						echo get_class() . ": row[keycol] = $row[$keycol]<br>";
						
					}

					$id = $row[$keycol];
					$search = "$keycol = '$id'";
					//echo $search;
                    $insert_key = $associative?"id$id":$id;
					$objects[$insert_key] = new $classname($search);
					if ($container->debug && $container->debug_level > 1)
						echo "$tag->event_format: Created new $classname with Id=" . $objects[$row[$keycol]]->get( $keycol )."<br>\r\n";
				} catch (Exception $e ) { throw $e; }
			}
			$result->close();
		} else {throw new Exception('ObjectFactory::create(): a SQL error occurred: ' . MySQLService2::getSQLError() . ' executing query: <p><strong>'  . $query .  '</strong></p> ( ' . MySQLService2::getSQLError() . ' )'); }
	    if ( $container->debug) echo "<span style='font-weight: bold;'>$tag->event_format: returning ".count($objects)." objects of type $classname</span><br>\r\n";
		return $objects;
	}
	
	//! obtain an array of the unique database Ids for a set of DataObjects
	public static function getUniqueIdsOf ( $objArray , $idField = 'Id') {

		if ( ! is_array( $objArray ) )
			throw new IllegalArgumentException( 'ObjectFactory::getUniqueIdsOf(): argument must be an array of DataObjects');
	
		$ids = array();
		foreach ( $objArray as $object )
			$ids[$object->get( $idField )] = $object->get( $idField );
			
		return $ids;
	}

	//! search for objects
	public static function search( $what, $how , $query , $filters) {

		$debug = (Debugger::enabled()) ? true : false;
		
		if ( $debug ) 
			echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . 
				": what $what, how $how, query $query filters $filters<br>";
				
		switch ( strtolower( $how ) ) {
		
			// use an inline operator
			case 'inline-operator':
			
				$conditions = SQLCreator::convert_inline_ops( $query);
				if ( Debugger::enabled() )
					echo __LINE__ . " " . get_class() . "::search(): conditions=$conditions<br>";
			
			break;
		
			case 'rlike': 
			
				$obj = new $what();
				$search_cols = $obj->search_columns;
						
				
						
				if ( ! $search_cols  )
					throw new ObjectNotInitializedException( get_class() . "::search: $what doesn't have any search columns defined...");
				
				$conditions = Search::rlike_clause( $query, $search_cols ); 
				
				// if we have filters
				if ( $filters )
					$conditions .="$filters";
					
				if ( $debug ) 
					echo $_SERVER["PHP_SELF"] . " " . __LINE__ . " " . get_class() . " " . __FUNCTION__ . 
					": search_cols $search_cols, conditions $conditions<br>";
			break;
			
		}
		return self::create( ucfirst( $what ) , null, null, null, null, $conditions );
	}
	
	//! walk an array of objects, and call a specific method on each
	public static function walk( $objs, $method) {
		foreach( $objs as $obj ) $obj->$method();
	}
	
	//! delete a list of objects from an array of IDS
	public static function delete_from_ids($key,$ids) {
		// get container
		global $container;
		// taggig
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// set debugging
		$debug = $container->debug;

		$count = 0;
		foreach( $ids as $id){
			$obj = new $key("id='$id'");
			if ( $obj->exists){ 
				$obj->delete();
				$count++;
			}
		}
		//$container->log( xevent::notice, "$tag->event_format : deleted $count objects from ids");
	}
	
	//! create a list of objects from an array of IDS
	public static function create_from_ids($key,$ids) {
		// get container
		global $container;
		// taggig
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// set debugging
		$debug = $container->debug;
		$objs = array();
		$ref = new $key();
		$id_col = $ref->source()->keycol();
		$count = count( $ids);
		if ($debug)
			echo "$tag->event_format : id_col = $id_col creating $key objects from $count ids<br>\r\n";
		foreach ( $ids as $id) {
			$search = "`$id_col`='$id'";
			//echo $search;
			$obj = new $key($search);
			if ( $obj->exists)
				array_push($objs,$obj);
		}
			
		return $objs;	
	}
	
	//! create ids for select objects
	public static function create_ids( $key, $conditions, $keycol = null){
		// get container
		global $container;
		// taggig
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// set debugging
		$debug = $container->debug;
		// save ids
		$ids = array();
		// reference object
		$ref = new $key();
		// get id col
		$id_col = ($keycol)? $keycol : $ref->source()->keycol();
		// get table
		$src = $ref->source()->name;
		// get where clause
		$where = SQLCreator::WHERE( HumanLanguageQuery::create( $conditions)->conditions());
		// run query
		$query = "SELECT `$id_col` FROM `$src` $where";
		if ( $debug )
			echo "$tag->event_format : query = $query<br>\r\n";
		
		$result = mysql_service::query($query );
		if ( $result ){
			while ( $row =$result->fetch_assoc())
				array_push( $ids, $row[$id_col]);
			$result->close();
		}
		$count = count($ids);
		if ( $debug )
			echo "$tag->event_format : returning $count ids<br>\r\n";
		return $ids;
	}
	
	// display a collection of objects using the same view
	public static function display( $objs, $view){
		$html = "";
		foreach ( $objs as $obj)
			$html .= $obj->xhtml( $view);
		echo $html;		
	}
	
	// uniquify by a specific field
	public static function uniquify_by( $member, $objs){
		$arr = array();
		foreach ( $objs as $obj)
			if ( ! in_array( $obj->$member, array_keys($arr)))
				$arr[ $obj->$member] = $obj;
		sort( $arr);
		return $arr;
	}
}
