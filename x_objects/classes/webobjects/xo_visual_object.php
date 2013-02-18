<?php
/**
 *
 * A visual object is a specific kind of magic object that is designed
 * specifically to be visualized as a web component
 *
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 02/11/12
 * Time: 07:42 PM
 */
abstract class xo_visual_object extends magic_object implements visualizable {
    // cache included
    protected $cache = null;
    public function __construct(){
    }
    public static function create(){
        $class = get_called_class();
        return new $class();
    }
}
