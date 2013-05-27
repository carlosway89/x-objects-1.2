<?php
/**
 *
 * An object representation of an image from a source file, allowing
 * for inspection of validity and certain other properties
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 11/03/13
 * Time: 06:06 PM
 */
class xo_image {
    private $is_valid = false;
    private $image = null;
    private $image_info = array();
    public $error = '';
    public function __construct($image_path){
        if (! file_exists($image_path) || is_dir($image_path))
            $this->error = "$image_path: no such image file found";
        else {
            $this->image_info = getimagesize($image_path);
            $this->image_type = $this->image_info[2];
            switch($this->image_type){
                case IMAGETYPE_JPEG:
                    $this->image = imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_GIF:
                    $this->image = imagecreatefromgif($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $this->image = imagecreatefrompng($image_path);
                    break;
            }
            if ( $this->image !== false) $this->is_valid = true;
        }
    }
    public function is_valid(){
        return $this->is_valid;
    }
    public function width() { return imagesx($this->image); }
    public function height() { return imagesy($this->image); }

}
