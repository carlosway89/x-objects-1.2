<?php
/**
 *
 * Abstract representation of a text filter
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 28/04/13
 * Time: 09:39 AM
 */

abstract class xo_text_filter {
    protected $text = null;
    public function __toString(){
        //if ( $this->text === null) user_error(get_called_class().": text was not set by child class",E_USER_WARNING);
        return $this->text;
    }
}