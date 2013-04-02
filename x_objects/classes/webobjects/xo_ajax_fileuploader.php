<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 01/11/12
 * Time: 08:28 AM
 */
class xo_ajax_fileuploader extends magic_object {
    public function __construct( $html_id,$server_action,$debug = "no"){
        $this->html_id = $html_id;
        $this->server_action = $server_action;
        $this->debugging = $debug;
    }
    public function __toString(){
        $str = "";
        try {
            $str .= x_object::create( "qq-file-uploader")->xhtml($this);
        } catch( Exception $e){
            $str = $e->getMessage();
        }
        return $str;
    }
}
