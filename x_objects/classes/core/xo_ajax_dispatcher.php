<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 03/11/12
 * Time: 12:06 PM
 */
abstract class xo_ajax_dispatcher extends magic_object {
    abstract public function as_array();
    abstract protected function do_process($arr);
}
