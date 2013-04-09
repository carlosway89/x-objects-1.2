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
    private $save_dir = '';
    public $command = '';
    public function __construct($filename,$save_dir){
        global $container;
        $this->container = (object)$container;
        $this->filename = $this->new_filename = $filename;
        $this->type = (string) new file_type_for($filename);
        $this->extension = (string) new file_extension_for($filename);
        $this->save_dir = $save_dir;
    }
    public function convert(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        $result = false;
        if ( $this->type != 'document')
            $this->error = "$this->filename: Not a document";
        else {
            // convert from MS Office
            if ( in_array( $this->extension,array('doc','docx','xls','xlsx','ppt','pptx'))){
                $this->new_filename = preg_replace("/\.$this->extension/",'.pdf',$this->filename);
                $config = $this->container->config->libreoffice;
                if ( ! $config )
                    $this->error = 'There is no X-Objects configuration for libreoffice';
                else {
                    global $webapp_location;
                    $cmd = "sudo -i ". (string) $config->binary . " --headless --invisible --convert-to pdf $this->filename --outdir $this->save_dir --nofirststartwizard -display 1 2>&1";
                    $this->command = $cmd;
                    if ( $container->debug) echo "$tag->event_format: cmd=$cmd<br>";
                    $command = new xo_shell_command($cmd);
                    if ( ! $command->execute())
                        $this->error = $command->output;
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