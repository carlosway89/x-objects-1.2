<?php
/**
 *
 * library of core/low-level functions
 *
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
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

?>