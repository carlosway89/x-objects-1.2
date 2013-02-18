<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Aspire
 * Date: 02/10/12
 * Time: 04:47 PM
 * To change this template use File | Settings | File Templates.
 */
class x_time_picker extends magic_object {

    public function __construct($eid,$class="",$default=""){
        $this->element_id = $eid;
        $this->css_class = $class;
        $this->default_value = $default;
    }
    public function __toString(){
        $s = "";
        try {
            $s =  x_object::create("xo-time-picker")->xhtml($this);
        } catch( Exception $e){
            $s = $e->getMessage();
        }
        return $s;
    }
}
