<?php
/**
 *
 * Component to get a generic "file type" name for a given filename
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 12/02/13
 * Time: 05:05 PM
 */
class file_type_for {
    private $extension = "";
    private $type = 'unknown';
    private $translations = array(
        'video'=>array('avi','wmv','mov','mpg','mpeg','mp4','flv','ogg','ogv'),
        'document'=>array('pdf','xls','doc','ppt','txt','rtf','xlsx','docx','pptx'),
        'image'=>array('jpg','jpeg','gif','png','bmp','tif','tga'),
        'audio'=>array('mp3','wma','wav','m4a','flac','aiff','aif','snd','wv','ape','aac')
    );
    public function __construct($filename){
        if ( preg_match( '/(.+)\.([a-z|A-Z|0-9|_]+)/',$filename,$hits)){
            $this->extension = strtolower($hits[2]);
            if ( $this->extension){
                foreach( $this->translations as $type =>$extensions){
                    if ( in_array($this->extension,$extensions)){
                        $this->type = $type;
                        break;
                    }
                }
            }
        }
    }
    public function __toString(){
        return $this->type;
    }
}
