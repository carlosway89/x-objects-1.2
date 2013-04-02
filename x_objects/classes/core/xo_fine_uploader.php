<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 12/02/13
 * Time: 01:09 PM
 */
class xo_fine_uploader {
    private $result = null;
    public function __construct($filename = null){
        global $webapp_location,$platform;
        $uploader = new qqFileUploader2();
        // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $uploader->allowedExtensions = array();
        // Specify max file size in bytes.
        $uploader->sizeLimit = 10 * 1024 * 1024;
        // Specify the input name set in the javascript.
        $uploader->inputName = 'qqfile';
        // If you want to use resume feature for uploader, specify the folder to save parts.
        $uploader->chunksFolder = 'chunks';
        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $upload_dir = $webapp_location."/user_images/";
        $filename = $filename?$filename.".". (string) new file_extension_for($uploader->getName()):null;
        $result = $uploader->handleUpload($upload_dir,$filename);
        // To save the upload with a specified name, set the second parameter.
        // $result = $uploader->handleUpload('uploads/', md5(mt_rand()).'_'.$uploader->getName());
        // To return a name used for uploaded file you can use the following line.
        $result['uploadName'] = $uploader->getUploadName();
        $result['upload_dir'] = $upload_dir;
        $this->result = $result;

    }
    public function __toString(){
        return (string) json_encode($this->result);
    }
}
