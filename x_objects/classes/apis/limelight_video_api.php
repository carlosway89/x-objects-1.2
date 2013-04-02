<?php
/**
 * The Limelight Video API is a specific implementation of the REST API Component.
 * As such, it is by default, Unit-Testable, which makes it well suited for
 * production applications.
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 13/02/13
 * Time: 10:18 AM
 */
class limelight_video_api extends xo_rest_api {
    private $org_id = null;         // my limelight org id
    private $access_key = null;     // my API access key
    private $secret_key = null;     // my API secret key
    private $channels = null;       // all channels
    private $upload_json = array(); // JSON after uploading media (or trying to)
    public function __construct(){
        global $container;
        $config = $container->config->limelight;
        $this->end_point = (string)$config->endpoint;
        $this->org_id = (string)$config->organization_id;
        $this->access_key = (string)$config->access_key;
        $this->secret_key = (string)$config->secret_key;
    }
    // get a list of test methods to call (should NOT include test_self() which is implicit)
    public function get_tests(){
        return array( "get_channels");
    }
    // get all channels
    public function get_channels(){
        $result = true;
        $request = $this->end_point. "organizations/$this->org_id/channels.json";
        $signed_request = LvpAuthUtil::authenticate_request("GET", $request, $this->access_key, $this->secret_key);
        $response = file_get_contents($signed_request);
        $this->channels = $channel_array = json_decode($response);
        return $result;

    }
    // check my config (for unit testing)
    public function test_self(){
        $result = true;
        $required_members = array( "org_id","access_key","secret_key");
        foreach( $required_members as $member)
            if ( ! $this->$member){
            $this->last_error = "No $member found";
            $result = false;
            break;
        }
        return $result;
    }
    // gets the state of the object (required as part of Unit Testable)
    public function get_state(){
        $state = array(
            "num_channels"=>count(@$this->channels->channel_list),
            "channels"=>array()
        );
        foreach( $this->channels->channel_list as $id=>$channel)
            $state['channels'][$id] = new xo_string($channel);
        return $state;
    }
    // upload some media
    public function upload_media($file_path,$title=null){
        $json = array();
        $result = true;
        $file = $file_path;
        $title = $title?$title:'API Upload';
        $description = 'This file was uploaded with the LVP API using PHP and Curl';
        $upload_media_url = $this->end_point. "organizations/$this->org_id/media";

        //authenticate the upload URL
        $signed_url = LvpAuthUtil::authenticate_request("POST", $upload_media_url, $this->access_key, $this->secret_key);
        $json['signed_url'] = $signed_url;
        //arrange the details of the upload in an array
        $post_params = array("title" => $title, "description" => $description, "media_file" => $file);
        $json["POST"] = $post_params;

        //perform the POST using CURL, passing in the array of parameters
        $upload_response = $this->do_post($signed_url, $post_params);
        //display the media ID of the new upload on the screen
        $response_obj = json_decode($upload_response);
        $json['response'] = $response_obj;
        $this->upload_json = $json;
        return $result;
    }

    // get upload json
    public function upload_json() { return $this->upload_json; }

}
