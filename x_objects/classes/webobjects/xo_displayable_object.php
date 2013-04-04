<?php
/**
 * A Displayable Object provides a convenient way to turn any object into
 * one that can be rendered as a string for display purposes.
 *
 * This is done by wrapping the X-Object functionality around the new Object
 *
 * For example:
 *
 * class my_object extends xo_displayable_object{
 *      public function __construct(){
 *          $this->view = "some-view-file";
 *          $this->object = $this;
 *      }
 * }
 *
 * echo (string) new my_object();
 *
 * Provides a simple and elegant solution for using Objects in your Ajax methods
 *
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
    // provide better errors to parent
    public function __get($what){
        trigger_error("$what: not a member of class ".get_called_class(),E_USER_WARNING);
    }
}
