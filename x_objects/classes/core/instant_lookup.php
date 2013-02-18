<?php
class instant_lookup extends magic_object {
	public function __construct($key,$fields,$val,$constraints = null){
		global $container;
		// create a tag
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		if ( $container->debug)
			echo "$tag->event_format : (key,fields,val,constraints) = ($key,$fields,$val,$constraints)<br>\r\n";
		$this->key = $key;
		$this->fields = $fields;
		$fs = explode('.',$fields);
		$model = call_user_func( "$key::model");
		$search = "";
		$val = preg_replace('/,/',' ',$val);
		foreach ( $fs as $f) $search .= ($search)? ",OR:$f LIKE '%$val%' ":" $f LIKE '%$val%' ";
		$search = $val == '*'? "": $search;
		if ( $constraints){
			if ( $container->debug)
					echo "<span style='color:green;'>$tag->event_format: have constraints $constraints</span><br>\r\n";
			$cs = explode(',', $constraints);
			if ( ! count($cs)){
				if ( $container->debug)
					echo "<span style='color:orange;'>$tag->event_format: warning: could not split constraints, not being applied</span><br>\r\n";
				
			}
			foreach( $cs as $c){
				if ( $c){
					$p = explode(':',$c);
					if ( $model->has_field( $p[0]))
						$search .= ($search)? ",".$p[0]."='".$p[1]."'":" ".$p[0]."='".$p[1]."'";
					else {
						if ( $container->debug)
							echo "<span style='color:orange;'>$tag->event_format: $p[0] is not a field for this model, so ignoring constraint</span><br>\r\n";
						
					}
				}
			}
		}
		// limit results
		$search .= ($search)?",LIMIT 20":"LIMIT 20";
		if ( $container->debug)
			echo "$tag->event_format: search = $search<br>\r\n";
	
		$this->records = $model->find_all($search);
	}
	public function __toString(){
		$str = "";
		try { 
		if ( $this->records)
			foreach ( $this->records as $record)
				$str .= $record->xhtml($this->key.'-instant-lookup-view');
		} catch ( Exception $e){
			echo $e->getMessage();
		}
		return $str;
	}
	
}
?>
