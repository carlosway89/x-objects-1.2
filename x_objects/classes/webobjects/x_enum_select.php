<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Aspire
 * Date: 06/10/12
 * Time: 10:49 AM
 * To change this template use File | Settings | File Templates.
 */
class x_enum_select extends magic_object {
    public function __construct($key,$default,$class,$cssid = null){
        $this->key = explode('.',$key);
        $this->default = $default;
        $this->class = $class;
        $this->cssid = $cssid;
    }
    public function __toString(){
        $id = $this->cssid? ' id="'.$this->cssid.'" ':'';
        $name = $this->cssid? ' name="'.$this->cssid.'" ':'';

        $s = "<select $id $name class='$this->class'>";
        foreach ( $this->get_enum_values($this->key[0],$this->key[1]) as $v){
            $se = ($v == $this->default)?'selected="selected"':"";
            $s.= "<option ".$se." value='$v'>$v</option>";
        }
        $s .= "</select>";
        return $s;
    }
    public function get_enum_values( $table, $field ) {
        global $container;
        $sql = $container->services->mysql_service;
        $enum = array();
        if ( $r = $sql->query( "SHOW COLUMNS FROM `$table`  WHERE Field = '$field'" )){
            $row = $r->fetch_row();
            $type = $row[1];
            $r->close();
            preg_match('/^enum\((.*)\)$/', $type, $matches);
            foreach( explode(',', $matches[1]) as $value ) {
                //echo $value. "<br>\r\n";
                $enum[] = trim( $value, "'" );
            }
       } else {
            $enum[0] = $sql->getSQLError();
        }
       return $enum;
    }
}
