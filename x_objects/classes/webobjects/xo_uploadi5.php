<?php
    /*
    UploadiFive
    Copyright (c) 2012 Reactive Apps, Ronnie Garcia
    */
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 22/02/13
 * Time: 10:20 AM
 */
class xo_uploadi5 {
    public static $last_file = "";
    private $config = null;
    private $result = "";
    public function __construct($image_only = false){
        global $container;
        $this->config = $container->config->uploadi5;
        $req = new REQUEST();
        $ses = new SESSION();
        // Set the uplaod directory
        $uploadDir = (string)$this->config->upload_dir;
        // Set the allowed file extensions
        $member = $image_only ?"image_file_types":"file_types";
        $fileTypes = explode(',',(string)$this->config->$member); // Allowed file extensions
        $verifyToken = md5('uploadi6' . $req->timestamp);
        if (!empty($_FILES) /*&& $req->token == $verifyToken*/) {
            $tempFile   = $_FILES['Filedata']['tmp_name'];
            $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
            $extension = (string)new file_extension_for($_FILES['Filedata']['name']);
            $new_name = (string)new random_password(20);
            $targetFile = $uploadDir . $new_name.".".$extension;
            // Validate the filetype
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
                // Save the file
                move_uploaded_file($tempFile, $targetFile);
                $ses->uploadi5_last = $new_name.".".$extension;
                $this->result = 1;
            } else {
                // The file type wasn't allowed
                $this->result = 'Invalid file type.';
            }
        }
    }
    public function __toString(){
        return (string)$this->result;
    }
}
