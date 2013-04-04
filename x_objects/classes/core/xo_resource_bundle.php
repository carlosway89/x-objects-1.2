<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 16/10/12
 * Time: 09:01 AM
 */
class xo_resource_bundle extends magic_object {
    private $resources = array();
    public function __construct($key){
        $debug = false;
        $this->key = $key;
        global $xobjects_location,$webapp_location;
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $f = $xobjects_location . "resources/".$container->lang."/$key.ini";
        if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource file expected is $f<br>\r\n";
        if ( file_exists($f)){
            if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource file $f exists and will be loaded<br>\r\n";
            $this->resources = parse_ini_file($f,true);
            if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource bundle is ".new xo_array($this->resources)."<br>\r\n";
            if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: original resource bundle is ".new xo_array($this->resources)."<br>\r\n";
        } else {
            $f = $webapp_location . "/app/resources/$container->app_lang/$key.ini";
            if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource file expected is $f<br>\r\n";
            $this->f2 = $f;
            if ( file_exists($f)){
                if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource file $f exists and will be loaded<br>\r\n";
                $this->resources = parse_ini_file($f,true);
                if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: resource bundle is ".new xo_array($this->resources)."<br>\r\n";
                if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: original resource bundle is ".new xo_array($resources)."<br>\r\n";
            }
        }
    }
    public function __get( $what ){
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: searching bundle for $what<br>\r\n";
        switch($what){

            default:
                if ( isset($this->resources[$what]))
                    return $this->resources[$what];
                else {
                    if ( ($container->debug || $debug) && $container->debug_level >= 2 ) echo "$tag->event_format: $what not found in bundle so punting to parent<br>\r\n";

                    return parent::__get($what);
                }
            break;
        }
    }

}
