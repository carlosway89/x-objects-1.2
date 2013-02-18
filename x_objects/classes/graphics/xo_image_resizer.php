<?php
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class xo_image_resizer {

    var $image;
    var $image_type;
    private $image_directory = "/";

    public function set_directory($dir){
        $this->image_directory = $dir;
    }

function load($filename) {
    $image_path = $this->image_directory.$filename;

$image_info = getimagesize($image_path);
$this->image_type = $image_info[2];
if( $this->image_type == IMAGETYPE_JPEG ) {

$this->image = imagecreatefromjpeg($image_path);
} elseif( $this->image_type == IMAGETYPE_GIF ) {

$this->image = imagecreatefromgif($image_path);
} elseif( $this->image_type == IMAGETYPE_PNG ) {

$this->image = imagecreatefrompng($image_path);
}
}
    function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
        //echo $filename;
        $image_url = $this->image_directory.$filename;
        //echo $image_url;
if( $image_type == IMAGETYPE_JPEG ) {
imagejpeg($this->image/*,$image_url,$compression*/);
    $i = ob_get_clean();
    // Save file
    $fp = fopen ($image_url,'w');
    fwrite ($fp, $i);
    fclose ($fp);
} elseif( $image_type == IMAGETYPE_GIF ) {

imagegif($this->image,$image_url);
} elseif( $image_type == IMAGETYPE_PNG ) {

imagepng($this->image,$image_url);
}
if( $permissions != null) {

chmod($image_url,$permissions);
}
}
function output($image_type=IMAGETYPE_JPEG) {

if( $image_type == IMAGETYPE_JPEG ) {
imagejpeg($this->image);
} elseif( $image_type == IMAGETYPE_GIF ) {

imagegif($this->image);
} elseif( $image_type == IMAGETYPE_PNG ) {

imagepng($this->image);
}
}
function getWidth() {

return imagesx($this->image);
}
function getHeight() {

return imagesy($this->image);
}
function resizeToHeight($height) {

$ratio = $height / $this->getHeight();
$width = $this->getWidth() * $ratio;
$this->resize($width,$height);
}

function resizeToWidth($width) {
$ratio = $width / $this->getWidth();
$height = $this->getheight() * $ratio;
$this->resize($width,$height);
}

function scale($scale) {
$width = $this->getWidth() * $scale/100;
$height = $this->getheight() * $scale/100;
$this->resize($width,$height);
}

function resize($width,$height) {
$new_image = imagecreatetruecolor($width, $height);
imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
$this->image = $new_image;
}

    public function resize_auto($width,$height){
        if ( $this->getWidth() > $this->getHeight())
            $this->resizeToWidth($width);
        else $this->resizeToHeight($height);
    }

    public function thumb_filename($filename){
        if ( preg_match( '/(.+)\.([a-z|A-Z]{3,4})/',$filename,$hits)){
            return $hits[1]."_thumb.".$hits[2];
        } else return $filename;
    }
}
?>