<?php
/**
 *
 * Web component to suss out the controller name at page load time
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 14/02/13
 * Time: 01:00 PM
 */
class xo_controller_key {
    private $key = "";
    public function __construct(){
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $key = null;
        // get the parsed url
        $parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
        if ( @$container->debug ) echo "$tag->event_format : parsed URL is ".new xo_array($parsed_url)."<br>\r\n";
        // now get the parts
        $url_parts = explode('/',$parsed_url['path']);
        // if available, set form part 1
        if ( isset($url_parts[1])){
            if ( $container->debug ) echo "$tag->event_format : key may be found in URL parts<br>\r\n";
            $key = $url_parts[1];
        } else {
            // get the URI
            $uri = new REQUEST_URI;
            if ( $container->debug) print_r($uri);
            // set controller from request
            $key = isset( $_REQUEST['key'] ) && $_REQUEST['key'] != '' ? $_REQUEST['key'] : '';
            // or from URI
            $key = (! $key )? $uri->part(1) : $key;
            $orig_uri = $key;
        }
        // or default to home
        $key = (! $key )? "home":$key;
        // save it
        if ( $container->debug ) echo "$tag->event_format : key = $key<br>\r\n";
        $this->key = $key;
    }
    // print it as object
    public function __toString(){
        return (string)$this->key;
    }

}
