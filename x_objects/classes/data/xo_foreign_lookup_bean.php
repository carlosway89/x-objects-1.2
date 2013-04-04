<?php
/**
 *
 * Convenient way to save a foreign lookup as its own record,
 * and then emit the resulting id for use
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 04/04/13
 * Time: 10:25 AM
 */

class xo_foreign_lookup_bean {
    public $error = '';
    public $id = 0;
    public function __construct($key,$member,$value,$others = array()){
        global $container,$webroot;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ($container->debug) echo "$tag->event_format: (key,member,value)=($key,$member,$value)<br>";
        if ( ! class_exists($key))
            $this->error = "$key: No such class, model, or business object";
        else {
            $id_field = $key::model()->key_field();
            if ($container->debug) echo "$tag->event_format: id_field=$id_field<br>";
            $object = new $key("$member='$value'");
            if ($object->exists){
                $this->id = $object->$id_field;
                if ($container->debug) echo "$tag->event_format: object already exists and id is $this->id<br>";
            }
            else {
                $object->$member = $value;
                foreach( $others as $n=>$v)
                    $object->$n = $v;
                if (! $object->save())
                    $this->error = $object->save_error;
                else
                    $this->id = $object->$id_field;
            }
        }
    }
}