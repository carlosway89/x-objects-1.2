<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 12/11/12
 * Time: 07:16 PM
 */
class xo_google_shopping_api extends magic_object {
    const api_url = "https://www.googleapis.com/shopping/search/v1/public/products?";
    public function __construct(){
        global $container;
        $this->key = $container->config->google_apis->key;
        $this->country = $container->config->google_apis->country;
    }
    public function search($term,$verbose = false){
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $term = preg_replace('/\s+/',"+",$term);
        $request = self::api_url . "key=".$this->key."&country=".$this->country."&q=".$term."&alt=json";
        if ( $verbose) echo "$tag->event_format: request = $request\r\n";
        return json_decode(file_get_contents( $request));
    }
}
