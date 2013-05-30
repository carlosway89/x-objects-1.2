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

    // flags for conversion types
    const convert_mp4 = 1;
    const convert_ogv = 2;
    const convert_webm = 4;

    private $container = null;
    private $filename = '';
    public $new_filenames = array();
    private $type = '';
    private $extension = '';
    public $error = '';
    public $output = '';
    private $config = null;
    private $extensions = array('flv',  'avi','wmv','mov','mp4');
    public $commands = array();
    private $logger = null;

    /**
     * Create a new Video Converter
     * @param $filename string the name of the file to convert, including full path
     * @param $directory string (deprecated) directory into which to save converted file
     * @param $logger object of type xo_loggable to allow for event logging
     */
    public function __construct($filename,$directory = '',$logger = null){
        global $container;
        $this->logger = $logger;
        $this->container = (object)$container;
        $this->filename = $this->new_filename = $filename;
        $this->type = (string) new file_type_for($filename);
        $this->extension = (string) new file_extension_for($filename);
        $this->config = $this->container->config->ffmpeg;
        if (! $this->config){
            $this->error = 'No ffmpeg configuration';
        }
    }

    /**
     * Convert the specified file to indicated formats
     * @param int $bits indicates which formats to convert
     * @param bool $verbose if true sends verbose output to STDOUT
     * @return bool true if successful for all conversions
     */
    public function convert($bits = 7,$verbose=false){
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $result = true;
        if ( $this->type != 'video')
            $this->error = "$this->filename: Not a video file";
        else {
            // convert from AVI
            if ( in_array(strtolower($this->extension),$this->extensions)){
                if ($verbose) echo "$tag->event_format: $this->filename is a candidate for conversion\r\n";
                // convert to MP4, Ogv and webm
                if ( $bits & self::convert_mp4) $result &= $this->to_mp4($verbose);
                if ( $bits & self::convert_ogv) $result &= $this->to_ogv($verbose);
                if ( $bits & self::convert_webm) $result &= $this->to_webm($verbose);
            }
        }
        $success = $result?'success':'error';
        if ($verbose) echo "$tag->event_format: $success converting $this->filename\r\n";

        return $result;
    }

    /**
     * Convert video to OGV (or to MP4)
     * @return bool true if successfully converted
     */
    private function to_mp4($verbose=false){ return $this->convert_to('mp4',$verbose); }

    /**
     * Convert video to OGV (or to MP4)
     * @return bool true if successfully converted
     */
    private function to_ogv($verbose=false){ return $this->convert_to('ogv',$verbose); }

    /**
     * Convert video to WebM
     * @return bool true if successfully converted
     */
    private function to_webm($verbose=false){ return $this->convert_to('webm',$verbose); }

    /**
     * Convert to one of several formats
     * @param $what string which format to convert to
     * @param $verbose bool if true sends output to STDOUT
     * @return bool true if successful
     */
    private function convert_to($what,$verbose=false){
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $args = array(
            'mp4'=>' -f mp4 -acodec aac -ac 2 -ar 44100 -b:a 128k -r 25 -b:v 512k -s 720x400 -vcodec libx264 -flags +loop+mv4 -cmp 256 -partitions +parti4x4+parti8x8+partp4x4+partp8x8+partb8x8 -me_method hex -subq 7 -trellis 1 -refs 5 -bf 0 -coder 0 -me_range 16 -g 250 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -qmin 10 -qmax 51 -qdiff 4 -strict -2 -level 30 -vprofile baseline ',
            'ogv'=>' ',
            'webm'=>' '
        );
        $result = true;
        // if already MP4 nothing to do for =>MP4 ;)
        if ( $what == 'mp4' && strtolower($this->extension) =='mp4')
            return $result;
        else{
            // extension is conversion type
            $new_extension = ".$what";
            // set new filename
            $this->new_filenames[$what] = preg_replace("/\.$this->extension/",$new_extension,$this->filename);
            // must have a configuration!
            if ( ! $this->config )
                $result = false;
            else {
                // allow to run on various platforms
                $ext = $this->container->platform() == 'win'?".exe":'';
                // get command name
                $cname = "ffmpeg$ext";
                // construct command for this conversion
                $cmd = (string) $this->config->directory . ' '.$cname.' -i '.$this->filename. $args[$what]
                    . $this->new_filenames[$what] . ' 2>&1 ';
                // save it
                $this->commands[$what] = $cmd;
                if ($verbose) echo "$tag->event_format: converting to $what using $cmd\r\n";
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
    }
}