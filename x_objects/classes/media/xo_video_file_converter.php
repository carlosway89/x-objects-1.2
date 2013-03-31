<?php
/**
 *
 * Web Media Component to Convert video files
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 31/03/13
 * Time: 01:28 PM
 */

class xo_video_file_converter {
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
        $result = false;
        if ( $this->type != 'video')
            $this->error = "$this->filename: Not a video file";
        else {
            // convert from AVI
            if ( $this->extension == 'avi'){
                $this->new_filename = preg_replace('/\.avi/','.mp4',$this->filename);
                $config = $this->container->config->ffmpeg;
                if ( ! $config )
                    $this->error = 'There is no X-Objects configuration for ffmpeg';
                else {
                    $ext = $this->container->platform() == 'win'?".exe":'';
                    $cname = "ffmpeg$ext";
                    $cmd = (string) $config->directory . ''.$cname.' -i '.$this->filename.
                        ' -f mp4 -acodec aac -ac 2 -ar 44100 -b:a 128k -r 25 -b:v
                        512k -s 720x400 -vcodec libx264 -flags +loop+mv4 -cmp 256
                        -partitions +parti4x4+parti8x8+partp4x4+partp8x8+partb8x8
                        -me_method hex -subq 7 -trellis 1 -refs 5 -bf 0 -coder 0
                        -me_range 16 -g 250 -keyint_min 25 -sc_threshold 40 -i_qfactor
                        0.71 -qmin 10 -qmax 51 -qdiff 4 -strict -2 -level 30 -vprofile
                        baseline $this->new_filename';
                    $command = new xo_shell_command($cmd);
                    if ( ! $command->execute())
                        $this->error = 'Could not run server video converter';
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