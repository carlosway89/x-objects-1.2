<?php
/**
 *
 * library of core/low-level functions
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 27/02/13
 * Time: 10:04 AM
 */

/**
 * Is the given filename of an image file?
 * @param $filename string the name of the file to check
 * @return bool true if the file is an image
 */
function is_image($filename){
    $images = array('jpg','jpeg','gif','bmp','tif','nef','tiff','png','psd','ico');
    return in_array( (string) new file_extension_for(strtolower($filename)),$images);
}

function canonical_url($url){
    $result = null;
    if (preg_match('/((ftp|http|https):\/\/)?((\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/',strtolower($url),$hits)){
        $result = $hits[3];
    }
    return $result;
}

?>