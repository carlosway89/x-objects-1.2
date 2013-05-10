<?php 
/**
 * A Data Object is a low level Object tied closely to the database,
 * which allows for access to data abstraction layer, and is typically the
 * parent class for a Business Object.
 *
 * @property string $save_error the error occurring from last save operation
 */
abstract class data_object extends AttributedObject
	implements Iterator,	// allows iteration through the object
		Changeable					// manage state changes
		{				

    // resources
    protected static $resources = null;
	//! string holding error message from last failed operation
	public $error = '';
	public $delete_error = '';
	
	// the data object as an array of columns
	private $object = null;
	
	public $is_new = false;

	// data source
	protected $source = null;

	// has the object been loaded from the database
	public $isLoaded = false;
	
	//! manage changes to fields (dirty)
	private $dirty = array();
	
	//! manage changes to the record's keys
	private $change = array();
	
	//! data abstraction rules
	private $abstraction = null;
	
	//! iterator position
	private static $itPosition = 0,$itKeys;

    protected $save_type = '';
    private $search = null;

	//! construct a new object, where the child class specifies the datasource, etc.
	public function __construct( $search = null , $datasource) {
        $this->search = new business_object_search($search);
        // load any resources
        if ( ! self::$resources) self::$resources = new xo_resource_bundle(get_class());
        // set up logging and debugging
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// save data source
		$this->source = $datasource;
		// if we have a search string
		if ( $search ) {
            if ( $container->debug) echo "$tag->event_format: search is $search<br>\r\n";
			$id = MySQLService2::getId($search, $this->source->name(), $this->source->keycol() );
			
			if ( $container->debug && $container->debug_level >2) {
			
				echo "$tag->event_format: search = $search, source name = " . $this->source->name() . ",keycol = " . $this->source->keycol() . ",id = $id<br>\r\n";
			}
			if ( $id ) {
			// load the object from that search
				if ( $this->debug ) echo get_class() . " YES loading because id=".$id."<br>";
				$this->load( $id );	
			} else {
				if ( $this->debug ) echo get_class() . " not loading because id=".$id."<br>";
			}

				
		}
		
	}
		
		
	//! magic get
	public function __get( $what ) {
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		switch( $what ) {
		
			case 'exists':
			
				if ( Debugger::enabled() || Debugger::enabled() )
					$container->debug ( $tag, "has the object ($this->key) been loaded?". (bool) $this->isLoaded);
//					Debugger::echoMessage("In DO2, checking for exists by checking if loaded, value=".$this->isLoaded);
			
				return $this->isLoaded;
				
			break;
		
			case 'save_sql':
			
				if ( $container->debug )
					echo "$tag->event_format : getting save SQL now<br>\r\n";
			
				if ( $this->isLoaded ) {

					if ( $container->debug ) echo "$tag->event_format : record is already loaded, and will be updated<br>\r\n";				
					$flag = 0;
			
					//begin constructing query
					$query = 'UPDATE ' . $this->source->destination . ' SET ';
			
					// go through all columns
					foreach ($this->source->columns() as $value) {
					
						if ( $container->debug && $container->debug_level >= 2) echo "$tag->event_format : checking column $value<br>\r\n";
						if ( ! $this->source()->is_read_only( $value)
							&&
							$this->isDirty( $value )
							&&
							$value != 'id') {
								//$value = (is_array($value))?implode(',',$value):$value;
							$query .= ($flag++ ? ', ' : ' ' ) . $this->source->write_col_for( $value ) . '=\'' . MySQLService2::real_escape_string($this->get( $value )) . '\' ';
							$fields = true;
						} else {
							if ( $container->debug && $container->debug_level >=3) echo "$tag->event_format : column $value will not be included in SQL, either because it is read only, is not dirty, or is an ID<br>\r\n";
						}
					}
			
					$query .= ' WHERE `' . $this->source->write_keycol  . '`=\'' . $this->get( $this->source->keycol())  . '\'';
				
					

						
					// if the key column is being updated, note it
					if ( $this->isDirty( $this->source->keycol() )){	
					
						$updateKeyCol = true;
					}
					
					// if nothing is dirty, set query to null
					if ( $this->noDirty() ) {
						if ( $this->debug )
							echo get_class() .  " Nothing dirty in DO2, setting query to null<br>";
						$query = null;
					} else {
					
						if ( $container->debug ) {
							
							echo "$tag->event_format : at least one field is dirty, and will be updated in the next record save.  Below is the array of dirty fields<br>\r\n";
							print_r ( $this->dirty );
							echo "<br>\r\n";
						}
					}
					if ( $this->debug )
						echo get_class() . "save_query = $query<br>";

                    if ( preg_match('/SET\s+WHERE/',$query)){
                        if ( $container->debug) echo "$tag->event_format: nulling out save_sql because there were no members to set<br>\r\n";
                        $query = null;
                    }

					return $query;
				}
						
				else {
					if ( $container->debug )
						echo "$tag->event_format : preparing INSERT for new record<br>\r\n";
					// construct query from database table and column names, given at time of construction
					$query = 'INSERT INTO `' . $this->source->destination . '` (';
		
					// loop through each column and add it to the statement
					$i = 0;
					if ( $container->debug )
						echo "$tag->event_format : checking " . count($this->source->columns()) ." columns for changes<br>\r\n";
			
					foreach ( $this->source->columns() as $col) { 
						// exclude read-only columns
						if ( ! $this->source->is_read_only( $col)
							&& $this->isDirty( $col)
							&& $col != 'id' // exclude id col
							) {
							if ( $container->debug )
								echo "$tag->event_format : column $col IS eligible for inclusion in INSERT statement<br>\r\n";
		
							if ( $i > 0 && $i < $this->source->numcols()  ) 
								$query = $query . ",";	
							

							$query .= ' `' . MySQLService2::real_escape_string( $this->source->write_col_for( $col ) ) . '` ';
						$i++;		
						} else {
							if ( $container->debug && $container->debug_level > 2)
								echo "$tag->event_format : column $col NOT eligible for inclusion in INSERT statement, either because it is read only, is the id, or doesn't have a value<br>\r\n";
			
						}
						
					}
					$query .= ") VALUES('";
					$i = 0;

					foreach ( $this->source->columns() as $col) {
						// exclude read only columns
						if ( ! $this->source->is_read_only( $col)
						&& $this->isDirty( $col)
							&& $col != 'id' // exclude id
							){
				
							if ( $i > 0 && $i < ( $this->source->numcols())) 
								$query .= ",'";
				
							$query .=  MySQLService2::real_escape_string( @$this->object[$col] ) . "' ";
						$i++;	
						}
						
					}
					$query .= ")";
					
					if ( $this->noDirty() )
						$query = null;
                    else {
                        if ( $this->debug) echo "$tag->event_format: at least one field is dirty so we need to insert<br>\r\n";
                        if ( $this->debug ) print_r( $this->dirty);
                    }
					if ( $this->debug ) 
						echo "$tag->event_format : insert prepared query = $query<br>\r\n";
				
					return $query;
				}
					
			break;
			
			default:
			
				if ( $this->debug )
					echo get_class() . ": calling standard get instead of magic for $what<br>";
			
				return $this->get( $what );
				
			break;
		}
	}
	
	//! for iterator, is it valid?
	public function valid() {
	
//		self::$itKeys = $this->source->columns();
		self::$itKeys = get_object_vars($this);
		print_r(get_object_vars($this));
		return isset( self::$itKeys[self::$itPosition] );
	}
	
	//! for iterator, get current
	public function current() {
	
		//self::$itKeys = $this->source->columns();
		self::$itKeys = get_object_vars( $this);
		$member = self::$itKeys[self::$itPosition];
		return $this->$member;
		//return $this->object[self::$itKeys[self::$itPosition]];
	}
	
	//! for iterator, move to next
	public function next() {
		self::$itPosition++;
	}
	
	//! for iterator, rewind
	public function rewind() {
		self::$itPosition = 0;
	}
	
	//! for iterator, return current key
	public function key() {
		self::$itKeys = get_object_vars($this);
		return self::$itKeys[self::$itPosition];
	}
	
	//! is the given error lookup key valid for this type of object?
	public static function isDataError( $key ) {
		return ( $key >= 100 && $key <= 120 ) ? true : false;
	}
	
	//! lookup an error and translate it 
	public static function lookupError( $key ) {
		return self::$errorLookup[$key];
	}

	/*
	 * load(): load a record from the table with a given Id and inject into DBObject for further use
	 * $Id: optionally pass the unique db Id to force-load with it
	 */
	public function load( $id ) {
		global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        // save id
		$this->set( $this->source->keycol() , $id );
		// construct a query to select all columns, although in theory we only
		// need the ones specified in the datasource
		$query = "SELECT `" . implode( '`,`' , $this->source->columns() ) . "` FROM `" .
			$this->source->name() . "` WHERE `" . $this->source->keycol() ."` ='$id'";

        if ( $container->debug && $container->debug_level > 1 ) echo "$tag->event_format: id= $id , query = $query";


		// try to run the query
		if ( $result = MySQLService2::query( $query ) ) {
				
			// if we got nothing, just return false, no need to throw an exception
			if ( $result->num_rows == 0 ) { 
				$result->close(); 
				return false; 
			}
				
			// otherwise save the result as an associative array
			$this->object = $result->fetch_assoc();
			if ( $container->debug && $container->debug_level > 1)
                echo "$tag->event_format got back associative array ".new xo_array( $this->object ). "<br>\r\n";

			// close the result set
			$result->close();
				
			// set the return value as true, for success
			$returnValue = true;
				
			//echo 'oops!';
			
			// specify that the object has been loaded
			if ( Debugger::enabled() && $container->debug_level > 2)	
				Debugger::echoMessage('setting object as loaded');
			$this->isLoaded = true;
			if ( Debugger::enabled() && $container->debug_level > 2 )	
				Debugger::echoMessage('already set object as loaded');
				
			// clean all fields
			$this->resetChange();
			$this->cleanAll();
		} else throw new
			DatabaseException("A fatal error occurred attempting to load the object with<p><strong>$query</strong></p> " . MySQLService2::getSQLError());
			
			
		
		return $returnValue;
		
	}
	
	//! has the object been successfully loaded from the database?
	public function isLoaded() { return $this->isLoaded; }
	
	// set a bunch of values as an array
	public function set_values( $arr ) {
	
		foreach( $arr as $key => $val )
			$this->set( $key, $val );
	}
	
	//

    /**
     * set a member directly, so it can be updated in the database
     * @param $key the member 'name'
     * @param $value the actual value to save
     * @return void no return value
     */
    public function set( $key, $value ) {
	    global $container;
        $t = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// only change if the actual value is different
		if ( $this->get( $key ) !=  $value )  {
			$this->object[ $key  ] = $value;
			// set the field as dirty
			$this->setDirty( $key );;
		} else {
            if ( $container->debug ) echo "<span style='color:orange;'>$t->event_format: $key was not changed because the value provided is identical to the current value of the member</span><br>\r\n";
		}
		
	}
	
	//! get a key value
	public function get( $key ) { 
		return isset($this->object[ $key ] ) ? $this->object[ $key ] : null; 
	}
	
	public function equals( $Key, $Value ) { return ( $this->object["$Key"] == $Value ? true : false); }
	
	

	/*
	 * putToPOST(): this function is essential the inverse of getAllFromPOST().  It puts
	 * all of the current values into POST by putting them as hidden field declarations.
	 */
	public function putToPOST() {
	
		foreach ( $this->Columns as $Column)
			echo '<input type="hidden" name="' . $Column . '" id="' . $Column . '" value="' . $this->get($Column) . '"></input>' . "\r\n";
	
	}
	
	//! save the object
	public function save( $mode = Persistable::SAVE_MODE_DIRTY ) {
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	
		if ( $this->isLoaded ){
            $this->save_type = 'update';
            return $this->commit('update', $mode);
        }
		else {
	        if ($container->debug ) echo "$tag->event_format : Need to insert new record!<br>\r\n";
            $this->save_type = 'insert';
			return $this->commit('insert');
		}
	}
	
	/*
	 * commit(): save (new or existing) object data to the database
	 * returns: true if no errors
	 * throws: Exception if the data could not be written or read
	 */
	private function commit( $action = 'update', // by default, we are updating an existing record
		$mode = Persistable::SAVE_MODE_DIRTY 	// by default only save what has changed
	) {

		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		// field updates
		$fields = false;

		// the return value for this function
		$returnVal = true;

		// indicates whether we are updating key column
		$updateKeyCol = false;
		
		$query = $this->save_sql;
		if ( $container->debug ) echo "$tag->event_format: query (obtained from this->save_sql is $query<br>\r\n";		
		
		switch ( $action ) {
		
			// indicate we need to update fields
			case 'insert': $fields = true; break;
		
			// update an existing record
			case 'update': $fields = true; $flag = 0; break;
		
		}
		if ( $container->debug) echo "$tag->event_format : query = $query for key $this->key<br>\r\n";

        if ( $query == null){
            $this->save_error = "No members have changed since the last record save, so not saving anything.";
            return true;
        }

		if (  ! @$fields )
			return true;
			
			
		// check for a problem with the query because we got no columns to update
		if ( preg_match( '/SET\s+WHERE/',$query)){
			$this->insert_error = "no values to insert";
			//$container->warn( "$tag->event_format : The insert statement is incomplete, because no matching values were found to insert ( $query ).");
		}
		
		// check for same problem on insert
		if ( preg_match( '/\(\)/',$query)){
			$this->insert_error = "no values to insert";
			//$container->warn( "$tag->event_format : The insert statement is incomplete, because no matching values were found to insert ( $query ).");
		}
		
		
		// return true if the insert/update succeeded
		if ( $result = MySQLService2::query( $query) ) {
		
		
			// if inserting, grab the Id
			if ( $action == 'insert'  ) {
				$this->is_new = true;
				$this->isLoaded = true;
				$this->set ( $this->source->keycol() , MySQLService2::insert_id() );
			}
		} else {
			/*
			 * this is where a SQL error occurred, we should log it, and
			 * send a warning event to the log
			 */
			// save error for future access
			$this->insert_error = MySQLService2::getSQLError();

	        if ( preg_match('/Duplicate entry \'(.+)\' for key \'(.+)\'/',$this->insert_error,$hits)){
                $this->insert_error = "A record already exists with $hits[2] = '$hits[1]'";
            }

			// set the error
			$this->save_error = $this->insert_error;
//			$container->log(xevent::warning, "$tag->event_format : a SQL error occurred saving changes ( $this->save_error )");
				
			if ( $container->debug ) { 
				echo "$tag->event_format : a SQL error occurred committing changes ( $this->save_error )<br>\r\n";
			}
			return false;
	
		} 
		// reset dirty flags
		$this->cleanAll();
		
		// if we're updating the key column, reset it to new value
		if ( $updateKeyCol ) {
			//Debugger::echoMessage( 'DataObject::commit(): the key column has changed!');
			$this->keycol = $this->get( $this->getDataSource()->keycol() );
			$this->changed( $this->getDataSource()->keycol() );
		}
		
		if ( $this->debug ) {
			echo "$tag->event_format : result is $returnVal<br>\r\n";
			//if ( $returnVal ) echo "success!";
		}
			
		return $returnVal;
	}
	
	public function getAsString() {

		$Columns = $this->getDataSource()->columns();
				
		$retString = 'Id=' . $this->get('Id');
		for ($i=0; $i<count($Columns); $i++) {
			$retString .= $Columns[$i] . '=' . $this->get($Columns[$i]);
		}
		return $retString;
		
	}
	
	/* set the actions for the object
	protected function setActions( $actions) { $this->actions = $actions; }
	
	//! get the actions for this object
	public function getActions() { return $this->actions; }
	 */
	public function getColsAsString() {
		$retString = '';
		for ($i=0; $i<count($this->DBCols); $i++) 
			$retString .= $this->DBCols[$i];
		return $retString;
	
	}
	
	/*
	 * loadTable(): load selected columns, all rows from table
	 */	
	public function loadTable() {
	
		// construct a SELECT query for all rows and selected cols on this table
		$Query = 'SELECT ';
		for ( $i=0; $i<count($this->Columns); $i++) { 
			$Query .= $this->Columns[$i];
			if ( $i < count($this->Columns)-1) $Query .= ', ';		
		}
		$Query .= ' FROM `' . $this->Table. "`";
		if ( DEBUG )
			echo $Query;

		// try to retrieve all rows and save them
		if ($Result = $this->MySQL->query( $Query ) ) {
			for ($i=0; $i< $Result->num_rows; $i++)
				$this->object[$i] = $Result->fetch_assoc();
			$Result->close();
		} else throw new Exception('DataObject::loadTable(): ' . $this->MySQL->error . ' ' . $Query);
	}
	
	/*
	 * delete(): delete this record from the database, and purge everything
	 */
	public function delete() {
	
		$keycol = $this->source->keycol();
		$id = $this->$keycol;
		//echo "keycol=$keycol,id=".$id;
		
		// construct a DELETE query
		$Query = 'DELETE FROM ' . $this->source->destination . ' WHERE ' . $this->source()->keycol() . '=\'' . $id . '\'';
		
		// if the query was executed successfully
		if ( $Result = MySQLService2::query( $Query )) {
		
			return true;	
		} else return false; 
	}
	
	
	/*
	 * getRelationships(): returns an array of relationship fields and their values, for master-detail views
	 */
	public function getRelationships() {
		$Rels = array();
		// for each managed database column
		foreach ($this->DBCols as $Value)
			// if it's an Id
			if ( strpos( $Value, 'Id'))
				// add it to the return array,with the field's current value
				$Rels[$Value] = $this->DBObject[$Value];
		// return results as an array
		return $Rels;	
	}
	
	/*
	 * isReadOnly() is a convienient helper function to find out if a field is read-only
	 */
	private function isReadOnly( $Fieldname ) { return isset( $this->ReadOnlyColumns[$Fieldname] ) ? true : false; }
	
	//! is a given key "dirty"?  has it been changed since the last save?
	public function isDirty( $key ) { 
	
		$isDirty = isset( $this->dirty[$key] ) ? true : false;
		return $isDirty;
		
	}
	
	//! make a given key "dirty"
	public function setDirty( $key ) { 
        global $container;
        $t = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        // exclude these
        $exclude = array( 'xml_obj','save_error');
        if ( ! in_array( $key, $exclude )){
            if ( $container->debug && $container->debug_level > 3) echo "$t->event_format: setting $key as dirty<br>\r\n";
            $this->dirty[$key] = true;
           }

		
		
	}
	
	//! clean all dirty flags
	public function cleanAll() { $this->dirty = array(); }
	
	//! has a given key changed?
	public function hasChanged( $key ) {
		return isset( $this->change[ $key ] );
	}
	
	//! set a given key as changed
	public function changed( $key ) {
		$this->change[ $key ] = true;
	}
	
	//! reset all changes
	public function resetChange() {
		$this->change = array();
	}
	
	//! have there been any changes?
	public function noChanges() {
		return ( count( $this->change ) == 0 );
	}
	
	//! are there no dirty flags?
	public function noDirty() { return ( count( $this->dirty ) == 0 ); }
	
	//! update the object with an array
	public function update_with ( $arr ) {
	
		// set all values
		foreach ( $arr as $name => $val )
			$this->set( $name, $val );
			
		return $this;
	}
}
