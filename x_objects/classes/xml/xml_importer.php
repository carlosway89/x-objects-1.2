<?php
/**
 * template for creating a new service class
 */
class xml_importer extends magic_object implements x_service {

	//! the instance
	private static $instance = null;
	
	//! array of current objects
	private $current_record = array();
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	
	}
	
	public function __destruct(){
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
    
    // import an XML file into the database
    public function import( $file, $config = "xml_import" ){
    	//echo $file;
    	global $pathroot,$container,$webapp_location;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        // set verbose
        $this->verbose = ( $this->settings->verbose != "") ? true:false;
        // see if the report already exists
        $this->report = new xml_report("filename='$file'");
        if ( $this->report->exists){
            echo "<span style='color: blue;font-weight:bold;'>$tag->event_format : $file already exists, so resuming </span>\r\n";
            $this->report->last_import_start = date('Y-m-d H:i:s');
            $this->report->save();
        } else {
            $this->report->filename = $file;
            $this->report->last_import_start = date('Y-m-d H:i:s');
            $this->report->nodes_processed = 0;
            $this->report->save();
            echo "<span style='color: blue;font-weight:bold;'>$tag->event_format : $file has never been processed before</span>\r\n";

        }

    	$this->xml = new XMLReader;
    	$this->xml->open($file);
    	$this->settings = simplexml_load_file( "$webapp_location/app/xml/$config.xml" );   	
	  	// set update mode
	   	$this->update = ( $this->settings->update != "")?true:false;
	   	echo "<span style='color: blue;font-weight:bold;'>$tag->event_format : beginning import process... file=$file</span>\r\n";
	   	@ob_flush();  
		$count = 1;
		while ( $this->xml->read()){
            if ( $count < $this->report->nodes_processed){
                if ( $count % 10000 === 0){
                    echo "$tag->event_format: skipping node $count because already processed\r\n";
                    @ob_flush();
                    $count++;
                    continue;
                }
            }
			switch ($this->xml->nodeType) {
        		case (XMLREADER::ELEMENT):
        		if ($this->xml->localName == "MedlineCitation") {
        			//echo $this->xml->localName . "";
        			//exit;
        			
            		$node = $this->xml->expand();
                	$dom = new DomDocument();
                	$n = $dom->importNode($node,true);
                	$dom->appendChild($n);
                	$node = simplexml_import_dom( $dom);
                	$xp = new DomXpath($dom);
                	$n = $dom = $xp = null;
                	$this->process_node( $node);
                	$node = $n = $dom = $xp = null;
                	@ob_flush();
        			
            	}
            	break;
            	default: //echo "nope";
            	break;
        	}
        	if ( $count % 10000 === 0){
        		$this->report->nodes_processed = $count;
        		$this->report->save();
        		echo "$tag->event_format: processed $count nodes\r\n";
        		@ob_flush();
        	}
        	$count++;
    	}
    	echo "$tag->event_format : done processing $file\r\n";
    	$this->report->last_import_done = date('Y-m-d H:i:s');
    	$this->report->save();
    }
		
    
    
    // process a node recursively
    public function process_node( $node){
    	global $pathroot,$container;
    	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
    		
    	$name = $node->getName();
	
		// recurse?
		$recurse = true;
		
		//if ( $this->verbose ) echo "$tag->event_format : found a new node $name\r\n";
		// find a rule for it
		if ( ! isset( $this->settings->rules->$name)) { 
			//if ( $this->verbose ) echo "<span style=\"color:orange;\"> $tag->event_format : no rules found for node $name</span>\r\n";
		} else {
			//if ( $this->verbose ) echo "$tag->event_format : found rules for node $name\r\n";
				$rule = $this->settings->rules->$name;
		
				$recurse = $rule["norecurse"] != "yes";			
				// process rules for this node
				$this->process_rules_for_node( $node, $rule);
	
				
		}
		// now go through all the children, but only if not to be ignored
		if ($recurse )
			foreach ( $node->children() as $child)				
				$this->process_node( $child);
			$node = null;
		@ob_flush();
	}
	
	//! set the master record
	public function set_master_record( $object ){
	 	global $pathroot,$container;
    	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
   							
   		//if ( $this->verbose ) echo "<span style='font-weight:bold;'>$tag->event_format : setting master record as $object</span>\r\n";
		$this->master_record = $object;	
	}
	
	//! process rules for a node
	private function process_rules_for_node( $node, $rule){
		global $pathroot,$container;
    	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
  		
  		// get node name		
  		$name = (string)$node->getName();
        //echo "<span style='color:green;'>$tag->event_format : found node $name</span>\r\n";

        // skip it?
		if ( $rule->skip){
			//if ( $this->verbose ) echo "<span style='color:green;'>$tag->event_format : skipping node $name</span>\r\n";
			return;	
		}

		// is this a master record field?		
		if ( $rule->master_record_field ) { 
			//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : $name is a master record field, and processing accordingly,node=$node,rule=$rule</span>\r\n";
			$this->process_record_field($node,$rule);
		}
		// is it a current record field?
		if ( $rule->current_record_field ){ 
			//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : $name is a current record field, and processing accordingly</span>\r\n";
			$this->process_record_field($node,$rule, "current");
		}		
	
		// check for a business object			
		$bo_class = (string)$rule->bo;
		//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : the business object class is <span style=\"color:blue;font-weight:bold;\">$bo_class</span></span>\r\n";
		
		
		$bo_key = (string)$rule->key;
		//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : the business object lookup key for $bo_class is $bo_key</span>\r\n";
		
		
		$key_field = ( $rule->key['translate'])?$rule->key['translate']:$bo_key;
		$id = (string)$node->$bo_key;
		if ( $rule->key["master"] == "yes"){ 
			$mid = (string)$rule->key;
			$id = $this->master_record->$mid;
			if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : we need to do a lookup against the master record <strong>$mid</strong> id <strong>$id</strong></span>\r\n";
			
		} elseif ( $rule->key["magic"] == "yes"){
			// take first 10 letters of value, lowercase, removing spaces
			$id = preg_replace( '/[^a-zA-Z0-9]/' ,"" ,  strtolower( substr( (string)$node , 0, 10)));
			// now add lowercase name of node
			$id .= strtolower( preg_replace( '/[\s\,\'\)]+/',"" ,$name));
			// finally add the master record id
			$mrid = $this->master_record->source()->keycol();
			$id .= $this->master_record->$mrid;
		//echo "<span style='color:black;'>$tag->event_format : $bo_class requires a magic key <span style='color:violet;'>$id</span></span>\r\n";
			
		}
		
		// try to create it
		if ( $bo_class) { 
			// is it a class
			if ( ! class_exists($bo_class)){
				if ( $this->verbose ) echo "<span style='color:orange;'>$tag->event_format : $bo_class is a not a valid classname please confirm</span>\r\n";
			} else { 
				
				$lookup = (string)$rule->bo["alwaysinsert"] == "yes"?false:true;
				
				if ( ! $lookup){
					//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : always insert records of type $bo_class, so skipping lookup</span>\r\n";
					$object = new $bo_class();
					if ( (string)$rule->bo["autokey"]){
						$object->autokey = $container->services->utilities->random_password( 15);
					}
					$object->save();
				} else { 
				
					// try to look it up
					
					$object = new $bo_class("$key_field='$id'");
					//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : just looked for $bo_class{} where $key_field is $id</span>\r\n";
	
					if ( $object->exists && $this->update ){
						$this->current_record[ $bo_class ] = $object;
						//if ( $this->verbose ) echo "<span style='color:green;'>$tag->event_format : a record exists for $bo_class key $bo_key value $id and update is enabled</span>\r\n";
					} else {
						//if ( $this->verbose ) echo "$tag->event_format : a record does NOT exist for $bo_class key $bo_key ($key_field) value $id\r\n";
						// create a new one
						if ( (string)$rule->bo["autokey"]){
							$object->autokey = $container->services->utilities->random_password( 15);
						} else $object->$key_field = $id;
						if ( $object->save()){
							//if ( $this->verbose ) echo "<span style='color:green;'>$tag->event_format : record saved for $bo_class key $bo_key value $id and update is enabled</span>\r\n";
							$this->current_record[ $bo_class] = $object;
						} else {
							if ( $this->verbose ) echo "<span style='color:red;'>$tag->event_format : error saving record for $bo_class key $bo_key value $id : $object->save_error</span>\r\n";
						}
					}
				}
					
				// add value as a field
				if ( (string)$rule->value){
					// field
					$field = (string)$rule->value;
					$object->$field = (string)$node;
				}
				
				// add attributes
				if ( (string)$rule->attributes == "lowercase"){
					//if ( $this->verbose ) echo "<span style='color:black;'>$tag->event_format : saving attributes as lowercase for $name</span>\r\n";
					foreach ( $node->attributes() as $attr){
						$field = strtolower( $attr );						
						$object->$field = $node[$attr];
					}
				}
				
				// reference parent?
				if ( $rule->reference_parent_as){
					$pid = (string)$rule->reference_parent_as;
					$parent_bo = (string)$rule->reference_parent_as["bo"];
					$parent = @$this->current_record[$parent_bo];
					$object->$pid = @$parent->id;
				}
				$object->save();
						
				/*						
				 * is this the master record?  if so we need to cache a copy
				 */
				if ( $object->exists && $rule->is_master_record != "") 
					$this->set_master_record( $object);
							
				// is there a callback to master record
				$callback = ( isset( $rule->master_callback))? (string)$rule->master_callback : null;
				if ( $callback && $this->master_record && $object->exists){
					//if ( $this->verbose) echo "$tag->event_format: master callback $rule->master_callback for".get_class($object)."\r\n";
					$this->master_record->$callback( $object);
					
				}
				// is there a callback to parent record
				$callback = ( isset( $rule->parent_callback))? (string)$rule->master_callback : null;
				if ( $callback && $this->current_record[ $rule->parent_callback["bo"]] && $object->exists)
					$this->current_record[$rule->parent_callback["bo"]]->$callback( $object);
				
			}
		}
	}
	
	
	//! process a master record field
	private function process_record_field( $node, $rule, $type = "master")		{ 
			global $pathroot,$container;
    	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
  		// get node name		
  		$name = $node->getName();
  		
  		// seet member
  		$member = "$type" ."_record_field";
  		$fname = (string)$rule->$member;
  		
		$val = (string)$node;
		
		
		// is it a composite type
		$field_type = ($type == "master")?  $rule->master_record_field["type"]: $rule->current_record_field["type"];

       // if ( $this->verbose ) echo "<span style='color:blue;'>$tag->event_format : node=$node,rule=$rule,type=$type,name=$name,member=$member,fname=$fname,val=$val,filed_type=$field_type</span>\r\n";

        // composite text
		if ( $field_type == "composite_text"){
			//if ( $this->verbose ) echo "$tag->event_format : node $name is a composite text type value ($val) for $fname in $type record\r\n";
			$raw = ($type == "master")? $rule->master_record_field["components"]: $rule->current_record_field["components"];
			$components = explode( ";", $raw);
			$val = "";
			foreach ( $components as $component){
				$val .= ($val != "")? ( " ". $node->$component):$node->$component;		
			}
			//if ( $this->verbose ) echo "$tag->event_format : node $name is value ($val) for $fname in $type record\r\n";
			
		}
		if ( $field_type == "composite_date"){
			//if ( $this->verbose ) echo "$tag->event_format : node $name is a composite date type value ($val) for $fname in $type record\r\n";
			$raw = ($type == "master")? $rule->master_record_field["components"]: $rule->current_record_field["components"];
			$components = explode( ";", $raw);
			foreach ( $components as $component){
				$pieces = explode( ":", $component);
				switch ( $pieces[0]){
					case 'y':
						$year_src = $pieces[1];
					break;
					case 'm':
						$month_src = $pieces[1];
					break;
					case 'd':
						$day_src = $pieces[1];
					break;
				}
					
			}
			$val = (string)$node->$year_src . "-". (string)$node->$month_src . "-". (string)$node->$day_src;
			//if ( $this->verbose ) echo "$tag->event_format : node $name is value ($val) for $fname in $type record\r\n";
			
		}		
		if ( $type == "current") { 
			$ref = (string)$rule->current_record_field["bo"];
			//if ( $this->verbose ) echo "<span style=\"color:black;\"> $tag->event_format : current record type (class, or key) is $ref</span>\r\n";
			if ( ! isset( $this->current_record[ $ref ]) ) echo "<span style=\"color:red;\"> $tag->event_format : FATAL current record type (class, or key) is $ref but no such current record</span>\r\n";			

		}
		$record = ($type == "master")? $this->master_record: $this->current_record[  $ref ];							
		if ( $fname != "PMID"){
		$record->$fname = $val;
		if ( $record->save()){
			//if ( $this->verbose ) echo "<span style=\"color:green;\"> $tag->event_format : the $type record was successfully updated by node $name</span>\r\n";
		} else {
            if ( preg_match('/No members have changed/',$record->save_error)){
//                if ( $this->verbose ) echo "<span style=\"color:orange;\"> $tag->event_format : a warning occurred updating $type record using node $name: $record->save_error</span>\r\n";

            } else {
                if ( $this->verbose ) echo "<span style=\"color:red;\"> $tag->event_format : an error occurred updating $type record using node $name: $record->save_error</span>\r\n";

            }

		}
		}
	}
	
}


?>