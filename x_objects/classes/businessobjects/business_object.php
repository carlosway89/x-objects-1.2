<?php
/**
 * A BusinessObject is an object representation of data bound to a data store (e.g. MySQL).
 *
 * BusinessObjects have many useful run-time methods for invocation directly, or using the REST API.
 * 
 * To create a business object create a new subclass of this one.
 * @property $last_error string the last error from any operation
 * @property $exists bool Does this object correspond to an actual database record?
 * @property $save_error string an error raised if last save operation failed
 * @property $delete_error string an error raised if last delete op failed
 * @property $as_array array representation of object
 */
abstract class business_object extends data_object 
	implements business_interface, Abstractable {
    // last message from any operation
    public $last_message = '';
    // resources
    public $bo_resources = null;
    // last class error
    public static $last_class_error = "";
	// recordset parity
    public static $recordset_parity = "even";

	public static $called_class = 'undefined called class';
	//! business object key
	private $key = null;
	
	//! xml specification
	protected $xml = null;
	
	//! the selector for this sub-class
	private static $selector = null;
	
	//! the (optional) form validation spec for this object
	private $validation = null;
	// cache available for all business objects
    protected $cache = null;

	//! handle calls to "undefined" methods
	public function __call( $func, $args ) {
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
	
		switch ( $func ) {
            case 'paginator':
                return null;
            break;
			// set the object from another one
			case 'set_from_object':
				$obj = $args[0];
				foreach( $this->source->columns() as $col)
					$this->$col = $obj->$col;
			break;
			case 'as_array':
				$arr = array();
				foreach( $this->source->columns() as $col){
					$arr[ $col]= $this->$col;
				}
				return $arr;
			break;
			// return the business object as JSON
			case 'json':
				// start JSON
				$json="{ ";
				$first=true;
				// walk through each column
				foreach( $this->source->columns() as $col){
					if (!$first) $json .= ",";
					$json .= '"'.$col.'" : "'.$this->$col.'" ';
					$first=false;
				}
				$json .=" }";
				return $json;
				
			break;
		
			// view a business object
			case 'view':
			
				return $this->html( $args[0] );
			
			break;
		
			// archive or unarchive a meeting
			case 'archive':
			case 'unarchive':
			
				$status = ( $func == 'archive' ) ? 1 : 0;
				
				$this->is_archived = $status;
				
				return $this->save() ? "success $func $this->id" : "error $func $this->id";
				
			break;
		
			case 'lock':
			case 'unlock':
			
				if ( $func == 'lock' ) $lock = 1; else $lock = 0;
				$this->is_locked = $lock;
				return $this->save() ? $func . ' ' . $this->id : 'error';
				
			break;
		
		
			case 'recycle':
			case 'restore':
			
				if ( $this->debug )
					echo get_class() . " $func $this->id<br>";
			
				if ( $func == 'recycle' ) $recycle = 1; else $recycle = 0;
				$this->is_deleted = $recycle;
				
				$result = $this->save();
				
				// after delete triggers
				// fire after triggers
				if ( $func =='recycle')
					$this->after_triggers(  'delete',__FUNCTION__."()" );
				
				return $result ? "success $func $this->id" : "error $func $this>id";
				
			break;
		
		
			case 'new':

				// load args as xml string
				$myargs = simplexml_load_string( stripslashes( $args[0] ) );
				
				foreach ( $myargs->children() as $node ) {
					$member = $node->getName();
					$val = (string) $node;
					$this->$member = $val;
				}
				if ( $this->save() ) {
					xevent::log( xevent::notice, 
						$_SERVER["PHP_SELF"].":".__FILE__.":".__LINE__.":".get_class().":".__FUNCTION__.":".
						"successfully created a new BusinessObject of type $this->key id $this->id");
					return "saved";
				}
				else {
					xevent::log( xevent::error, 
						$_SERVER["PHP_SELF"].":".__FILE__.":".__LINE__.":".get_class().":".__FUNCTION__.":".
						"failed creating a new BusinessObject of type $this->key id $this->id");
					return "not saved";
				}
							
			break;
		
			// perform and update
			case 'update':
			
				// first case: get as xml from api
				if ( $args[0] ) {
				
					if ( preg_match( '/<div>/' , $args[0] ) ) {
				
						// load as simple xml
						$xml = simplexml_load_string( stripslashes( $args[0] ) );

						// decode each one and set it
						foreach ( $xml->children() as $node ) {
							$member = (string) $node['id'];
							$val = (string) $node;
							$this->$member = $val;
						}
					} else {
					
						// parse as an arg
						$updates = new args( $args[0] );
						
						$cols = $this->source->columns();
						
						foreach ( $updates->members as $key => $value ) {
							if ( in_array( $key, $cols ))
								$this->$key = $value;
						}
					}
					
				} else {
				// second case, get it all from POST
					$this->get_from_post();
				
				}
				
				if ( @$updates->view ) {
				
					return $this->save() ? "<div>" . $this->html( $updates->view ) . "</div>" : "error update businessobject $this->id";
				
				} else
				return $this->save() ? 'saved ' . $this->id : 'error ' . $this->id;
				
			break;
		
			// exists
			case 'exists':
			
				return parent::__get( 'exists' ) ? 'yes' : 'no';
				
			break;
	
		
			// get the upload dir for a specific field
			case 'upload_dir_for':
			
				$column = $args[0];
				
				if ( Debugger::enabled() )
					echo "getting upload dir for $column<br>";
				
				return (string) $this->xml->xml()->file_upload->$column->target;
			
			break;
		
			// re abstract time
			case 're_abstract_time':
			
				$raw = $args[0];
				
				$result =   sprintf('%02d', $raw / 60 ) . ':' . sprintf( '%02d' , $raw % 60);
				
				echo $result;
				return $result;
				
			break;
		
			// re abstract a value
			case 're_abstract':
			
				$key = $args[0];
				$value = $args[1];
				
				if ( Debugger::enabled() )
					echo "checking re-abstraction for $key<br>";
					
				if ( isset( $this->xml->xml()->data_abstraction->$key ) ) {
				
					if ( Debugger::enabled() )
						echo "$key needs to be re-abstracted<br>";
						
					$method = (string) $this->xml->xml()->data_abstraction->$key->method;
				
					return $this->$method( $value );
				} else return $value;
			
			break;
		
			// is this a file upload?
			case 'is_file_upload':
			
				return isset( $this->xml->xml()->file_upload->$args[0] );
				
			break;
		
			case 'id':
			
				return $this->get('id');
				
			break;
			
			default:
                // just set an error
                $this->last_error = "$func(): I can't find out any information about this method. Are you sure this is defined for ".get_called_class()."?";
                return false;
			break;
		}
	}
	
	//! set a value
	public function set( $name, $value ) {
	
		// de-abstract and set in parent
		//parent::set( $name, $this->re_abstract( $name, $value ) );
		parent::set( $name, $value );
	}
	
	//! construct
	public function __construct( $key, $search = null) {
        // this can help with performance
        static $sources = array();
        // load cache
        $this->cache = new magic_object();


		if ( Debugger::enabled() )
			$this->debug = true;
	
		// save key
		$this->key = 'bo-' . strtolower( $key );
		
		// load configuration
		try {
		
			$obj_or_src = (is_object($this->xml_obj))?$this->xml_obj:$this->key;
			$this->xml = new RealXML( $obj_or_src );
		} catch ( ObjectNotInitializedException $e ) {
			throw new ObjectNotInitializedException( "Unable to create a new BusinessObject2 of type $key because the XML file $this->key.xml cannot be found.");
		}

		/*
		 * construct parent object, passing:
		 * \param search (string) the search to find existing object
		 * \source (object) the data source for the object
		 */
        $sname = (string)$this->xml->xml()->datasource->name;
        if ( ! isset( $sources[$sname]))
            $sources[$sname] = new DataSource2( $this->xml->xml()->datasource);
		parent::__construct( 
			$search, // e.g. "id = '1'"
			$source = $sources[$sname]
        );
			
			// load resources
            $this->bo_resources = new xo_resource_bundle(get_called_class());

    }
	
	//! get magic function
	public function __get( $what ) { 
		static $parity = false;
		static $resources_bundle = null;
		switch( $what ) {
            case 'friendly_last_modified_date': return(string)new human_time($this->last_modified_date?strtotime($this->last_modified_date):0);break;
            case 'friendly_created_date': return(string)new human_time(strtotime($this->created_date));break;
            case 'recordset_parity':
                self::$recordset_parity = self::$recordset_parity == 'even'?'odd':'even';
                return self::$recordset_parity;
            break;
            case 'members':
                return $this->source()->columns();
           break;
			case 'as_comma_separated_list':
				return implode(",", $this->as_array) . "\r\n";
			break;
			case 'parity_class':
				$parity = ($parity)?false:true;
				return ($parity)?"even":"odd";
			break;
						case 'as_array':
				$arr = array();
				foreach( $this->source->columns() as $col){
					$arr[ $col]= $this->$col;
				}
				return $arr;
			break;
			
			break;
			// get the object as JSON
			case 'json':
				$json = "{ ";
				$first = true;
				foreach( $this->source->columns() as $col) {
//				foreach ( $this as $key => $value){
					if ( ! $first) $json .=", ";
					$json .= '"'.$col.'" : "'.$this->$col.'" ';
					$first = false;
				}
				$json .=" }";
				return $json;
			break;
			case 'locked_class':
			
				return $this->is_locked ? 'locked' : 'unlocked';
				
			break;
		
			case 'deleted_class':
			
				return $this->is_deleted ? 'deleted' : '';
				
			break;
		
			case 'key':
			
				return $this->key;
				
			break;
		
			
			// if valid
			case 'valid':
			
				return true;
				
			break;
		
			case 'exists':
			
				return parent::__get( $what );
				
			break;
		
			case 'search_columns':
			
				return ( $this->xml->xml()->search_columns ) ? explode( ',' , (string) $this->xml->xml()->search_columns ) : null;
				
			break;

		
			case 'insert_error':
			case 'save_sql':
				return parent::__get( $what );
			break;
			

			default:
			
				// try matching by regex
				$matched = false;

                // get time in human terms
                if ( preg_match('/human_([a-z|_]+)/',$what,$hits)){
                    $matched = true;
                    $member= $hits[1];
                    return new human_time( strtotime($this->$member));
                }

				/*
				 * sometimes for security and privacy, we need to partially mask the member
				 */
				if ( preg_match( '/masked_([a-z|_|0-9]+)/',$what,$matches)) {
					$matched=true;
					$member = $matches[1];
					// take action based on what is being masked
					switch( $member ){
						// recursively mask each member
						case 'json':
							$json = "{ ";
							$first = true;
							foreach( $this->source->columns() as $col) {
								if ( ! $first) $json .=", ";
								$member = "masked_$col";
								$json .= '"'.$col.'" : "'.$this->$member.'" ';
								$first = false;
							}
							$json .=" }";
							return $json;
						break;
						case 'password':
							return "********";
						break;
						case 'username':
							return 'xxxxxxxxx';
						break;
						case 'phone':
						
							$mask = '';
							// mask all but last 3 characters
							for ( $i = 0; $i < strlen($this->phone); $i++){
								if ( $i < strlen($this->phone)-3)
									$mask .= '#';
								else $mask .= substr($this->phone,$i,1);
							}
							return $mask;
							
						break;
						case 'email':
							// split into two parts
							$parts = explode( "@",$this->email);
							$first = $parts[0];
							$domain = @$parts[1];
							$mask = '';
							// mask all but last 3 characters
							for ( $i = 0; $i < strlen($first); $i++){
								$mask .= '*';
							}
							return "$mask@".$domain;
									
						break;
						// default is to do nothing, since we don't know how to mask it
						default:
							return htmlspecialchars( $this->$member );
						break;
					}
				}
				
				if ( ! $matched) return parent::get( $what ); 
			break;
			
		}
	}

	//! default for de_abstract
	public function de_abstract( $key ) {
		return $this->get( $key );
	}
	
	//! default for abstraction type
	public function abstractTypeof( $key ) {
	
		return null;
	}
	
	
	//! by default, all business objects may be edited... a specific object my overrride
	public function mayEdit() { return true; }
	
	//! set the action target id for action buttons
	public static function setActionTargetId( $id ) { 
		self::$actionTargetId = preg_replace ('/#/' , '' , $id ); 
	}
	
	/* gets the action target id for action buttons
	public static function getActionTargetId() { 
		return self::$actionTargetId; 
	}
	*/
	
	//! save the object, managing business rules
	public function save( $mode = Persistable::SAVE_MODE_DIRTY) {
        // set up logging and debugging
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $reqs = $this->xml->xml()->datasource->required;
        if ( $container->debug) echo "$tag->event_format: required raw = $reqs<br>\r\n";
        $rs = explode(',',$reqs);
        foreach ( $rs as $r){
            if ( $r != "" && ! $this->$r){
                if ( $container->debug) echo  "$tag->event_format: field $r is required by has no value, cannot save record<br>\r\n";
                $e = self::$resources->errors['required']?self::$resources->errors['required']:"this field is required";
                $this->save_error = "$r: $e";
                return false;
            }
        }

        // make sure it passes the save audit
        if ( ! $this->save_audit() ){
            $this->save_error = $this->save_audit_error;
            return false;
        }

        if ( Debugger::enabled() )
			echo "$tag->event_format : saving record of type <span style=\"color: blue; font-weight: bold\">$this->key</span><br>\r\n";

			$result = parent::save();
		
		
		// fire after triggers
		// don't fire for xevent
		if ( $result && ! preg_match('/xevent/' ,$this->key)){
			//$this->after_triggers(  $what, __FUNCTION__."()" );
		
		}
        self::$last_class_error = $this->save_error;
		return $result;
		
	}
	
	//! after triggers are fired after a state change
	private function after_triggers( $what, $caller = "unknown_method()" ) {
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

		$triggers = (string) $this->xml->xml()->triggers->after;
		$event = (string)$this->xml->xml()->triggers->after['event'];
		if ( Debugger::enabled() )
			echo "$tag->event_format : firing after triggers for $what called by $caller<br>\r\n";
		
		if ( is_array( $triggers ) )
			foreach ( $triggers as $trigger ) {
		
				if ( $trigger['event'] == $what ) {
				
					echo "trigger!";
			
					$action = (string) $trigger['action'];
				
					$this->$action();
				}
			
			}
		elseif ( is_object( $triggers ) ) { 
			if ( $triggers['event'] == $what ) {
				
					$action = (string) $triggers['action'];
				
					$this->$action();
				}
		} elseif ( is_string($triggers) && $event == $what) {
			$funcs = explode(",",$triggers);
			foreach ( $funcs as $func)
				$this->$func();
		}
		if ( $container->debug) echo "$tag->event_format: DONE firing after triggers for $what<br>\r\n";
		return;
	}
	
	//! get/set the form validation spec
	public function validation( $spec = null ) {
	
		// if present, set it
		if ( $spec )
			$this->validation = $spec;
		// otherwise get it
		else  
			return $this->validation;
			
	}
	
	//! get or set the selector
	public static function selector( $selector = null ) {
	
		if ( $selector ) {
			if ( ! is_object( $selector ) || ! get_class( $selector ) == 'ObjectSelector' )
				throw new IllegalArgumentException ( 'BusinessObject::selector(): if present, first argument must be an object of type ObjectSelector');
		
			self::$selector = $selector;
		} else return self::$selector;
	}
		
	//! set values of all keys from REQUEST variables
	public function get_from_post( 
		$prefix = '', 	// optional prefix for all fieldnames
		$suffix = '',    // optional suffix for all fieldnames
		$normalize_req = false
	) {
		// set up event logging
		global $container, $config;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
			
		if ( (bool) @$config->event_mgmt->debug_events)
				$container->log( xevent::debug, "$tag->event_format : checking post for " . count($this->source->columns()) . " fields");	
		foreach ($this->source->columns() as $col) {
			$req = ($normalize_req)? strtolower($col[0]).substr($col,1) : $col;
			//if ( (bool) @$config->event_mgmt->debug_events)
				//$container->log( xevent::debug, "$tag->event_format : checking POST variable $req");
			if ( isset( $_REQUEST[$req] ) ) {

			// handle uploaded files
			if ( $this->is_file_upload( $col ) &&
				$_FILES[$col]['name'] != '') {
			
				// get the target directory
				$dir = $this->upload_dir_for( $col );
				$target_path =  PATHROOT . $dir . '/' . basename( $_FILES[$col]['name']);;

				// upload the file
				$this->set( $col, file_upload::upload( $col, $target_path ) ); 

			// password
			} elseif ( $col == 'password' ) {
			
				parent::set( $col, md5( $this->salt . $_REQUEST[$req]));
			
			// handle other cases
			} else {
			
				$Key = $prefix . $req . $suffix;
				
				if ( isset( $_REQUEST[$Key] ) ) {
				
						
					$Value = $_REQUEST[$Key];
					//if ( $container->debug )
						//echo "$tag->event_format : setting $Key = $Value<br>\r\n";
					//$container->log( xevent::debug, "$tag->event_format : setting $Key = $Value");
					parent::set( $col, $Value);
				}
			}
	
		}
		}
		
		// return this, for command chaining
		return $this;
		
	}
	
			//! return as well-formed html
	public function html( $view  ) {
		/*
		 * new!  we need to check if the view is defined as an XML string, instead of a file
		 */
		global $$view;
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( $container->debug)
			echo "$tag->event_format : view = $view<br>\r\n";
		
		if ( is_string( @$$view) && @$$view != ""){ 
			if ( $container->debug)
				echo "$tag->event_format : rendering view as XML string<br>\r\n";
			return x_object::render( $$view)->xhtml($this);
		} else{ 
			if ( $container->debug)
				echo "$tag->event_format : rendering view from XML file<br>\r\n";
			// create as a web snippet and return as html
			return x_object::get( $view )->html( $this );
		}
		
	}

	// synonym
	public function xhtml( $view ) {
	
		return $this->html( $view );
	}
	
	//! magic set
	public function __set( $what, $how ) {
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( $this->debug )
			echo "$tag->event_format : calling parent standard set for $what<br>\r\n";
	
		parent::set( $what, $how );
		
	}

	
	//! get the record max
	public static function record_max_parent( $source, $search = null ) {
	
		$clause = $search ?  SQLCreator::where( HumanLanguageQuery::create( $search )->conditions() ) : "";
	
		// construct query
		$query = " SELECT count( " . $source->keycol() . " ) as record_max FROM " . $source->name() . " $clause " ;
		
		//echo $query;
	
		// run query
		if ( $result = MySQLService2::query( $query ) ) {
		
			$row = $result->fetch_assoc();
			
			$result->close();
			
			return $row['record_max'];
			
		
		} else return 0;
	}
	
	//! set from args
	public function set_from_args ( $args ) {
	
		foreach( $args->members as $name => $val ) {
		
			// get columns
			$cols = $this->source->columns();
			
			if ( in_array( $name, $cols ))
				$this->$name = $val;
		}
	
	}
	
	/**
	 * Set values for the business object's members from json members
	 * @param string $jsonStr undecoded json string
	 * @return int number of members that were set
	 */
	
	public function set_from_json( $jsonStr ) {
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		$num = 0;
        if ( $container->debug)
            echo "$tag->event_format : jsonStr=$jsonStr<br>\r\n";
		$json = is_string( $jsonStr)?json_decode( stripslashes( $jsonStr )):$jsonStr;
		if ( $container->debug)
			echo "$tag->event_format : json=".new xo_string($json)."<br>\r\n";
		foreach ( $json as $name => $val ) {
			if ( $container->debug)
				echo "$tag->event_format : $name = $val<br>\r\n";
			$cols = $this->source->columns();
			if ( $container->debug) { 
				//echo "$tag->event_format : columns<br>\r\n";
				//print_r( $cols);
			}
			if ( in_array( $name, $cols)) {
				// take action based on type of field
				switch( $name ) {
					case 'password':
						$this->salt = ($this->salt)?$this->salt:$container->services->utilities->random_password(4);
						$this->password = md5( $val.$this->salt);
					break;
					default:
						if ( $container->debug)
							echo "$tag->event_format : saving $name = $val<br>\r\n";
						$this->$name = $val;
					break;
				}
				$num++;
			}

		}
		
		// check for missing required fields
		foreach ( $this->source->columns() as $col) { 
			//echo $col;
			if ( $this->source->required( $col) && $this->$col == '' ){ 
				$this->error = "missing required field `$col`";
				return false;
			}
		}
		if ( $num === 0)
			$this->error = "no recognized values in source JSON";
		return $this;
	}
	
	//! truncate == MUST be defined by child class
	public static function truncate(){
		trigger_error(get_class() . "::" . __FUNCTION__ . ": Fatal Error. Child class MUST override static method truncate().  May not be invoked in generic case.",
			E_USER_ERROR);
	}
	
	//! safely delete a record
	public function safe_delete($deletor = 0){
		$this->is_deleted = true;
        $this->deleted_by = $deletor;
        $this->deleted_date = date('Y-m-d H:i:s');
		$res = $this->save();
        if ( ! $res){
            self::$last_class_error = $this->save_error;
        }
        return $res;
	}
	
	// print the object as a string
	public function __toString(){
		$keycol = $this->source->keycol();
		$id = $this->$keycol;
		return get_class(). "{}: $this->key ( $id ), exists = $this->exists";
	}
	
	public static function model(){
		return new xo_model( function_exists('get_called_class')?get_called_class(): self::$called_class);
	}

	public static function create($search){
		$class = get_called_class();
		return new $class($search);
	}

    /**
     * @param $json
     * @return mixed new business object
     */
    public static function create_from_json( $json){
		$class = get_called_class();
		$b = new $class();
        //echo "json=".new xo_string($json);
		foreach ( $json as $name => $val){
		
			if ( ! in_array( $name, array("id","key"))){
					//echo "$name = $val<br>";
					$b->$name = $val;
					
			}
		}
		return $b;
	}

    /**
     * get the columns of this record as json
     * @return JSON the columns as JSON
     */
    public static function columns_json(){
        $class = get_called_class();
        $s = call_user_func($class."::source");
        header("Content-Type: application/json");
        $cols = array();
       // print_r( $class::source()->fields);
        foreach( $s->fields as $c){
            $ac = array();
            if ( ! preg_match('/_id/',$c->name) && $c->name != 'id'){
                $ac['name'] = $c->name;
                $ac['type'] = $s->translate_type($c->type);
                $ac['display_name'] = $s->display_name_for( $c->name );
                array_push( $cols, $ac);
            }
           // echo $c->name . " " .$c->type . " ";
        }
        echo json_encode($cols);
    }

    /**
     * @return bool true if passes save audit, false otherwise
     */
    public function save_audit(){
        // set up logging and debugging
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $audit = $this->xml->xml()->datasource->audit;
        $r = true;
        $a = $this->exists?($this->is_deleted?"d":"u"):"i";
        // audit not required?
        if ( ! (string)$audit->required == 'yes'){

        } else {
            switch ($a){
                // audit checks when inserting a new record
                case 'i':
                    // is the creator set?
                    $c = $audit->creator;
                    if ( ! $this->$c){
                        $this->save_audit_error = "$c: this field is required as part of the save audit";
                        $r = false;
                    }
                break;
                // audit checks when updating a record
                case 'u':
                    $e = $audit->editor;
                    $ed = $audit->edit_date;
                    if ( ! $this->$e){
                        $this->save_audit_error = "$e: this field is required as part of the save audit";
                        $r = false;
                    } elseif( ! $this->$ed){
                        $this->save_audit_error = "$ed: this field is required as part of the save audit";
                        $r = false;
                    }
                break;
                // audit checks when deleting a record
                case 'd':
                    $d = $audit->deletor;
                    $dd = $audit->delete_date;
                    if ( ! $this->$d){
                        $this->save_audit_error = "$d: this field is required as part of the save audit";
                        $r = false;
                    } elseif( ! $this->$dd){
                        $this->save_audit_error = "$dd: this field is required as part of the save audit";
                        $r = false;
                    }
                break;
            }
        }


        return $r;
    }

    /**
     * @param array $a optional array of additional members to set
     * @return business_object resulting BO
     */
    public static function create_from_request($a = null){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $c = get_called_class();
        $u = new $c;
        $r = new REQUEST;
        foreach ( $r->as_array as $n=>$v){
            //if ( $container->app_debug ) echo "$tag->event_format: $n = $v<br>\r\n";
            $u->$n = $v;
        }
        // now optional members
        if ( $a && is_array($a)){
            foreach ($a as $n=>$v){
                //if ( $container->app_debug ) echo "$tag->event_format: $n = $v<br>\r\n";
                $u->$n = $v;

            }
        }
        return $u;
    }

    public static function display( $which_ones ){
        $class = get_called_class();
        $model = call_user_func("$class::model");
        return $model->fetch_as_bundle($which_ones);
    }
}
?>