<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 13/02/13
 * Time: 10:18 AM
 */
abstract class xo_rest_api implements unit_testable {
    protected $last_error = "";    // last error from any operation
    protected $end_point = "";  // end point for API calls
    // run all required tests
    public function test_all(){
        $result = true;
        if ( ! $this->end_point){
            $this->last_error = "An end point must be defined";
            $result = false;
        } else {
            // test self
            $result = $this->test_self();
            if ( $result)
                foreach ( $this->get_tests() as $method){
                    if ( ! method_exists($this,$method)){
                        $this->last_error = "Test method $method doesn't exist";
                        return false;
                    } else {
                        $result &= $this->$method();
                        if ( !$result) break;
                    }
                }
        }
        return $result;
    }
    public function get_error(){ return $this->last_error; }

    public function do_post($url, $params=array()) {
        //this function will perform the POST using curl

        // get the curl session object
        $session = curl_init($url);

        // set the POST options.
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $params);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        // do the POST and then close the session
        $response = curl_exec($session);
        curl_close($session);
        return $response;
    }

}
