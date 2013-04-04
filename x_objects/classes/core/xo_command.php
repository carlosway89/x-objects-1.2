<?php
/**
 *
 * Object to encapsulate and run a server Command
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 31/03/13
 * Time: 01:47 PM
 */

class xo_command {

    private $command = '';
    public $output = null;
    public $error = null;
    public function __construct($command){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $this->command = $command;
        if ($container->debug) echo "$tag->event_format: command is $command<br>";
    }
    public function execute(){
        global $container;
        $result = false;
        $aOutput = array();
        $output = exec($this->command,$aOutput,$result);
        if ($container->debug) {
            print_r($aOutput);
            echo "<br>output is $output<br>";
        }

        if ( ! $result){
            $this->error = $output;
            return false;
        }
        else {
            $this->output = $output;
            return true;
        }
    }
}