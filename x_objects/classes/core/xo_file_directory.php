<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 21/01/13
 * Time: 09:01 PM
 */
class xo_file_directory {
    private $path = null;
    private $dir = null;
    public function __construct($path){
        $this->path = $path;
        $this->dir = dir($this->path);
    }
    public function __destruct(){
        if ( $this->dir )
            $this->dir->close();
    }

    /**
     * @param $ext
     * @return null|xo_file
     */
    public function find_file_by_type($ext){
        $file = null;
        $ext = strtolower($ext);
        while (false !== ($entry = $this->dir->read())) {
            if ( preg_match( "/\.$ext$/",$entry,$hits)){
                $file = new xo_file($this->path."/".$entry);
                break;
            }
        }
        return $file;
    }

    /**
     * @param $skip_dots bool if true do not return "." or ".."
     * @return string the next entry in the directory
     */

    public function next($skip_dots = false){
        $dir = $this->dir?$this->dir->read():null;
        // skip dots
        if ($skip_dots && $dir){
            while (in_array($dir,array('.','..')))
                $dir = $this->dir->read();
        }
        return $dir;
    }
}
