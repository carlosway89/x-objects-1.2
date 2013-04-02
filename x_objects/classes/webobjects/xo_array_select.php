<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 17/12/12
 * Time: 01:19 PM
 */
class xo_array_select {
    private $array = array();
    private $default = null;
    private $cssid = null;
    private $cssclass = null;
    public function __construct($array,$default=null,$cssid=null,$cssclass=null){
        $this->array = $array;
        $this->default = $default;
        $this->cssid = $cssid;
        $this->cssclass = $cssclass;
    }
    public function __toString(){
        $str = "<select id='$this->cssid' class='$this->cssclass'>";
        foreach ( $this->array as $index=>$item){
            $selected = $index === $this->default? 'selected="selected"':"";
            $str .='<option '.$selected.' value="'.$index.'">'.$item.'</option>';
        }
        $str .="</select>";
        return $str;
    }



}
