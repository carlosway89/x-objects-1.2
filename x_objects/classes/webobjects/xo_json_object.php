<?php
/**
 *
 * An Object representing a bundle of actions, the result of which is captured in
 * an Array.  This is mainly for the purpose of echoing back as JSON to the client
 *
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 01/02/13
 * Time: 04:24 PM
 */
class xo_json_object {
    /**
     * @var $models array of models to save for object state
     */
    private $models = array();

    protected $json_result = array(
        "signature"=>"xo_json_object"
    );
    public function __toString(){
        return (string)json_encode($this->json_result);
    }
    /**
     * Set a JSON member to a specific value
     * @param $member string the JSON member, that is the array key
     * @param $value mixed the value to set
     */
    public function __set($member,$value){
        $this->json_result[$member]=$value;
    }

    /**
     * get a JSON member
     * @param $member string the JSON member, that is the array key
     * @return $value mixed|null the value of the member, or null if not set
     */
    public function __get($member){
        return isset($this->json_result[$member])?$this->json_result[$member]:null;
    }

    /**
     * Queue up a model/business object to save
     * @param $model object business object
     */
    protected function queue($model){
        $this->models[count($this->models)] = $model;
    }


}
