<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 03/11/12
 * Time: 06:38 PM
 */
class xo_object_bundle extends magic_object {
    public function as_( $view){
        $html = "";
        foreach ($this->objects as $o)
            $html .= $o->html($view);
        return $html;
    }
}
