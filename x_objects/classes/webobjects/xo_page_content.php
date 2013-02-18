<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 06/02/13
 * Time: 11:59 AM
 */
class xo_page_content {
    private $key = "home";
    public function __construct($key){
        $this->key = $key;
    }
    public function __toString(){
        try {
            $str = x_object::create($this->key)->html($this);
        } catch (Exception $e){
            $str = "<div style='background-color:#1e1e1e;color: white;width: 800px;height:auto;min-height: 100px;margin: 50px auto 0;padding: 10px;font-size: 25pt'><p>X-Objects says:</p><p>".$e->getMessage()."</p></div>";
        }
        return $str;
    }
}
