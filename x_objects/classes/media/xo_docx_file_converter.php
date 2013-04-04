<?php
/**
 *
 * Web Media Component to Convert DocX files
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 31/03/13
 * Time: 01:28 PM
 */

class xo_docx_file_converter {
    private $container = null;
    private $filename = '';
    public $new_filename = '';
    private $type = '';
    private $extension = '';
    public $error = '';
    public $output = '';
    public function __construct($filename){
        global $container;
        $this->container = (object)$container;
        $this->filename = $this->new_filename = $filename;
        $this->type = (string) new file_type_for($filename);
        $this->extension = (string) new file_extension_for($filename);
    }
    public function convert(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        $result = false;
        if ( $this->type != 'document')
            $this->error = "$this->filename: Not a document";
        else {
            // convert from DOCX
            if ( in_array( $this->extension,array('doc','docx'))){
                $this->new_filename = preg_replace("/\.$this->extension/",'.pdf',$this->filename);
                $config = $this->container->config->libreoffice;
                if ( ! $config )
                    $this->error = 'There is no X-Objects configuration for libreoffice';
                else {
                    global $webapp_location;
                    $cmd = (string) $config->binary . " --headless --invisible
                    --convert-to pdf $this->filename --outdir ".$webapp_location."/user_images/
                    --nofirststartwizard -display 1 ";
                    if ( $container->debug) echo "$tag->event_format: cmd=$cmd<br>";
                    $command = new xo_command($cmd);
                    if ( ! $command->execute())
                        $this->error = $command->error;
                    else {
                        $result = true;
                        $this->output = $command->output;
                    }
                }
            }
        }
        return $result;
    }
}