<?php
/**
 *
 * Object to encapsulate and run a server Shell Command
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 31/03/13
 * Time: 01:47 PM
 */

class xo_shell_command {

    private $command = '';
    public $output = null;
    public function __construct($command){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $this->command = $command;
        if ($container->debug) echo "$tag->event_format: command is $command<br>";
    }
    public function execute(){
        $output = shell_exec($this->command);
        if ( $output === null)
            return false;
        else {
            $this->output = $output;
            return true;
        }
    }
}