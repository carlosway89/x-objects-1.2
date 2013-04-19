<?php
/**
 *
 * The debug manager handles various aspects of Debug state and information
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 14/04/13
 * Time: 01:35 PM
 */

class xo_debug_manager {
    const debug = 1;
    const app_debug = 2;
    const performance = 4;
    private $config = null;
    private $state = 0;
    public function __construct($xml){
        $this->config = $xml;
        $token = (string)$this->config->status;
        // get from database
        if ( preg_match('/database\:([a-z|_]+)\.([a-z|_]+)/',$token,$hits))
            $token = $this->state_from_database($hits[1],$hits[2]);
        $this->state = $this->state | (preg_match('/app/', $token)?self::app_debug:0);
        $this->state = $this->state | (preg_match('/enabled/',$token)?self::debug:0);
        $this->state = $this->state | (preg_match('/performance/',$token )?self::performance:0);
        //echo "state is $this->state<br>";
    }
    public function _is($state){
        return $this->state & $state;
    }
    private function state_from_database($table,$member){
        $query = "SELECT `value` FROM `$table` WHERE `name`='$member'";
        $mysql = MySQLService2::instance();
        $result = $mysql->query($query);
        if ( $result){
            $row = $result->fetch_assoc();
            return (string)$row['value'];
        } else return '';
    }
    public function status(){
        return $this->state;
    }
}