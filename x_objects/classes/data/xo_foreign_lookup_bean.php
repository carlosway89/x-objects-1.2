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
    public function __construct($key,$member,$value){
        if ( ! class_exists($key))
            $this->error = "$key: No such class, model, or business object";
        else {
            $id_field = $key::model()->key_field();
            $object = new $key("$member='$value'");
            if ($object->exists)
                $this->id = $object->$id_field;
            else {
                $object->$member = $value;
                if (! $object->save())
                    $this->error = $object->save_error;
                else
                    $this->id = $object->$id_field;
            }
        }
    }
}