<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 21/01/13
 * Time: 09:08 PM
 */
class xo_file {
    private $path = null;
    private $resource = null;
    public function __construct($path){
        $this->path = $path;
    }
    public function __destruct(){
        if ( $this->resource)
            fclose($this->resource);
    }
    public function next_line(){
        if ( ! $this->resource){
            $this->resource = fopen($this->path,"r");
        }
        return fgets($this->resource);
    }

}
