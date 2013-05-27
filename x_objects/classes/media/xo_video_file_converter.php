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
    public $new_filenames = array();
    private $type = '';
    private $extension = '';
    public $error = '';
    public $output = '';
    private $config = null;
    private $extensions = array('avi','wmv','mov','mp4');
    public $commands = array();
    public function __construct($filename){
        global $container;
        $this->container = (object)$container;
        $this->filename = $this->new_filename = $filename;
        $this->type = (string) new file_type_for($filename);
        $this->extension = (string) new file_extension_for($filename);
        $this->config = $this->container->config->ffmpeg;
        if (! $this->config){
            $this->error = 'No ffmpeg configuration';
        }
    }
    public function convert(){
        $result = true;
        if ( $this->type != 'video')
            $this->error = "$this->filename: Not a video file";
        else {
            // convert from AVI
            if ( in_array($this->extension,$this->extensions)){
                // convert to Ogv and webm
                $result &= $this->to_ogv();
                $result &= $this->to_webm();
            }
        }
        return $result;
    }

    /**
     * Convert video to OGV (or to MP4)
     * @return bool true if successfully converted
     */
    private function to_ogv(){
        global $container;

        $result = true;
        // support to convert MP4 to Ogg
        $new_extension = $this->extension == 'mp4'?'.ogv':'.mp4';
        $this->new_filenames['ogv'] = preg_replace("/\.$this->extension/",$new_extension,$this->filename);
        if ( ! $this->config )
            $result = false;
        else {
            $ext = $this->container->platform() == 'win'?".exe":'';
            $cname = $this->extension == 'mp4'? 'ffmpeg2theora': "ffmpeg$ext";
            $cmd = (string) $this->config->directory . ''.$cname.' -i '.$this->filename.
                ' -f mp4 -acodec aac -ac 2 -ar 44100 -b:a 128k -r 25 -b:v 512k -s 720x400 -vcodec libx264 -flags +loop+mv4 -cmp 256 -partitions +parti4x4+parti8x8+partp4x4+partp8x8+partb8x8 -me_method hex -subq 7 -trellis 1 -refs 5 -bf 0 -coder 0 -me_range 16 -g 250 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -qmin 10 -qmax 51 -qdiff 4 -strict -2 -level 30 -vprofile baseline '. $this->new_filenames['ogv'] . ' 2>&1 ';
            // if ogg...
            if ($this->extension == 'mp4'){
                $new_filenames['ogv'] = preg_replace('/\.mp4/','.ogv',$this->filename);
                $cmd = (string) $this->config->directory . ''."$cname $this->filename $new_filename 2>&1";
            }
            $this->commands['ogv'] = $cmd;
            $command = new xo_shell_command($cmd);
            if ( ! $command->execute())
                $this->error = $cname.'Could not run server video converter';
            else {
                $result = true;
                $this->output = $command->output;
            }
        }
        return $result;
    }

    /**
     * Convert video to WebM
     * @return bool true if successfully converted
     */
    private function to_webm(){
        $result = true;
        // support to convert MP4 to Ogg
        $new_extension = '.webm';
        $this->new_filenames['webm'] = preg_replace("/\.$this->extension/",$new_extension,$this->filename);
        if ( ! $this->config )
            $result = false;
        else {
            $ext = $this->container->platform() == 'win'?".exe":'';
            $cname = "ffmpeg$ext";
            $cmd = (string) $this->config->directory . ''.$cname.' -i '.$this->filename.
                '  '. $this->new_filenames['ogv'] . ' 2>&1 ';
            $this->commands['webm'] = $cmd;
            $command = new xo_shell_command($cmd);
            if ( ! $command->execute())
                $this->error = 'Could not run server video converter';
            else {
                $result = true;
                $this->output .= "\r\n\r\n".$command->output;
            }
        }
        return $result;

    }
}