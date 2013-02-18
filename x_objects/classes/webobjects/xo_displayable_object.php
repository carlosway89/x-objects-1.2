<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 05/02/13
 * Time: 09:40 AM
 */
class xo_displayable_object {
    protected $view = null;
    protected $object = null;
    public function __toString(){
        $str = "";
        try {
            $str = $this->view? x_object::create($this->view)->xhtml($this->object?$this->object:null):"";
        } catch (Exception $e){
            $str = $e->getMessage();
        }
        return $str;
    }

}
