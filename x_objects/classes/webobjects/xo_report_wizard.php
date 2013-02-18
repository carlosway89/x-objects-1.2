<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Aspire
 * Date: 23/09/12
 * Time: 11:57 AM
 * To change this template use File | Settings | File Templates.
 */
class xo_report_wizard extends magic_object {
    /**
     * print the wizard, useful when invoking from Ajax
     */
    public function __toString(){
        try {
            return x_object::create('report-wizard')->xhtml();
        } catch ( Exception $e){
            return  $e->getMessage();
        }
    }
}
