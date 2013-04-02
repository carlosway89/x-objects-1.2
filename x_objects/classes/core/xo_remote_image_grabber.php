<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 20/12/12
 * Time: 11:21 AM
 */
class xo_remote_image_grabber {
    private $remote_url = "";
    private $local_folder = "";
    private $image_type = "";
    public $local_image = "";
    public function __construct($remote_url,$local_folder,$image_type){
        $this->remote_url = $remote_url;
        $this->local_folder = $local_folder;
        $this->image_type = $image_type;
    }
    public function grab(){
        global $container;
        $new_name = $container->services->utilities->random_password(20);
        $img = $this->local_folder. $new_name.".".$this->image_type;
        file_put_contents($img, file_get_contents($this->remote_url));
        $this->local_image = $new_name.".".$this->image_type;
        return true;
    }
}
