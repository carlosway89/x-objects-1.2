<?php
/**
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 17/06/13
 * Time: 10:32 AM
 */

class xo_database_purger {
    const days = 1;
    public function __construct($table,$age,$unit = self::days,$column='created_data'){
        global $container;
        $check_date = date("-$age ".$this->unit_token($unit));
        $sql = "DELETE FROM `$table` WHERE `$column` < $check_date";
        echo $sql;
        //$mysql = $container->services->mysql_service;
        //return $mysql->query($sql);
    }
    private function unit_token($unit){
        switch($unit){
            case self::days: return 'day'; break;
            default: return ''; break;
        }
    }
}