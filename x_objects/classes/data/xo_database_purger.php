<?php
/**
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 17/06/13
 * Time: 10:32 AM
 */

class xo_database_purger {
    const days = 1;
    private $table,$age,$unit,$column;
    public $error = '';
    public function __construct($table,$age,$unit = self::days,$column='created_data'){
        $this->table = $table;
        $this->age = $age;
        $this->unit= $unit;
        $this->column = $column;
    }
    private function unit_token($unit){
        switch($unit){
            case self::days: return 'days'; break;
            default: return ''; break;
        }
    }
    public function purge(){
        global $container;
        $differ = "-$this->age ".$this->unit_token($this->unit);
        $check_date = date('Y-m-d H:i:s', strtotime($differ));
        $sql = "DELETE FROM `$this->table` WHERE `$this->column` < '$check_date'";
        $mysql = $container->services->mysql_service;
        $result =  $mysql->query($sql);
        if ( ! $result) {
            $this->error = $mysql->getSQLError();
            if ( ! $this->error) $result = true;
        }
        return $result;
    }
}