<?php
class xo_model extends magic_object {
	public function __construct($key){
		$this->key = $key;
	}
    public function find_first($query="all"){
        $arr = object_factory::create($this->key,null,1,null,null,$query);
        $array_keys = array_keys($arr);
        $key = $array_keys[0];
        return $arr[$key];
    }
	public function find_all($query = "all"){
		return object_factory::create($this->key,null,null,null,null,$query);
	}
    public function find_all_assoc($query = "all"){
        return object_factory::create_assoc($this->key,null,null,null,null,$query);
    }

    public function fetch_as_bundle($query="all"){
        $bundle = new xo_object_bundle();
        $bundle->objects = $this->find_all($query);
        return $bundle;
    }
	public function count_all( $query = "all"){
		return object_factory::count( $this->key, $query);
	}
	public function has_field($f){
		$o = new $this->key;
		return in_array( $f, $o->source()->columns());
	}
    public function fetch_all_as_array($query="all"){
        $os = $this->find_all($query);
        $a = array();
        if ( $os )
            foreach( $os as $o)
                array_push( $a, $o->as_array);
        return $a;
    }
    public function fetch_all_from_ids($a){
       return object_factory::create_from_ids($this->key,$a);
    }

    /**
     * @param $json object representation of values to update
     * @param $keys array keys indicating search for update
     * @param $map array map of json members to db fields
     * @return bool true if operation successful
     * errors logged in class's last class error
     */
    public function upsert_from_json($json,$keys,$map = array()){
        $class = $this->key;
        $conditions = "";
        foreach ( $keys as $field=>$member)
            $conditions .= ($conditions)?",$field='".$json->$member."'":"$field='".$json->$member."'";
        $object = new $class($conditions);
        foreach ( $json as $member=>$value){
            $mem = isset($map[$member])?$map[$member]:$member;
            $object->$mem = $value;
        }
        $result = $object->save();
        $class::$last_class_error = $object->save_error;
        return $result;
    }

    /**
     * Find all instances of a specific column (field) for given Model
     * and return them as an array
     * @param string $member the member name to fetch for each row
     * @param string $search an optional limiter for search
     * @param bool $compact_array optional to compact the array after fetching
     * @return array the results
     */
    public function find_all_values_as_array(
        $member,
        $search="all",
        $compact_array = false
    ){
        global $container;
        $class = $this->key;
        $query = "SELECT `$member` FROM `".$class::source()->name."` ".SQLCreator::getWHEREclause(HumanLanguageQuery::create($search)->conditions());
        $result = $container->services->mysql_service->query( $query );
        $values = array();
        if ( $result){
            if ( method_exists($result,"fetch_all"))
                $values = $result->fetch_all(MYSQLI_NUM);
            else {
                while ( $row = $result->fetch_assoc())
                    array_push( $values ,$row[$member]);
            }
            $result->close();
        }
        if ( $compact_array)
            array_walk($values,function(&$item,$index){ $item = $item[0]; });
        return $values;
    }

    /**
     * get all importable columns
     */
    public function importable_columns(){
        // first get all of them
        global $container;
        $class = $this->key;
        $source = $class::source();
        return $source->import_columns();
    }

}
?>
