<?php
/**
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 17/06/13
 * Time: 10:32 AM
 */

class xo_database_purger {
    const days = 1;
    private $table,$age,$unit,$column;
    public function __construct($table,$age,$unit = self::days,$column='created_data'){
        $this->table = $table;
        $this->age = $age;
        $this->unit= $unit;
        $this->column = $column;
    }
    private function unit_token($unit){
        switch($unit){
            case self::days: return 'day'; break;
            default: return ''; break;
        }
    }
    public function purge(){
        global $container;
        $check_date = date("-$this->age ".$this->unit_token($this->unit));
        $sql = "DELETE FROM `$this->table` WHERE `$this->column` < $check_date";
        echo $sql;
        //$mysql = $container->services->mysql_service;
        //return $mysql->query($sql);
    }
}