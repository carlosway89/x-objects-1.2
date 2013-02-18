<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Aspire
 * Date: 23/09/12
 * Time: 04:41 PM
 * To change this template use File | Settings | File Templates.
 */
class xo_report_wizard_result extends magic_object {

    public function __construct($key,$query,$view,$none_view,$cols,$tconstraint){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug)
            echo "$tag->event_format: key=$key,query=$query,view=$view,none_view=$none_view,cols=$cols,tconstraint=$tconstraint<br>\r\n";
        $this->key = $key;
        $this->query = $query;
        $this->view = $view;
        $this->none_view = $none_view;
        $this->cols = $cols;
        $this->tconstraint = $tconstraint;
    }

    public function __toString(){
        global $container;
        /*
         * if we have a time constraint, we need to translate it into a SQL condition
         * but we can leverage the human language query system to do the work for us
         */
        $q = $this->query;
        if ( $this->tconstraint){
            $p = explode(':',$this->tconstraint);
            $q = $q? $q.",$p[0]='$p[1]'":"$p[0]='$p[1]'" ;
        }
        $str = '';
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( ! $this->key )
            $str =  "$tag->event_format: no key specified";
        elseif ( ! $this->query )
            $str =  "$tag->event_format: no query specified";
        elseif ( ! $this->view )
            $str =  "$tag->event_format: no view specified";
        elseif ( ! $this->none_view)
            $str =  "$tag->event_format: no none-view specified";
        else{
            $str =  "<div class=\"report-wizard-result-set\"><table><tr>";
            $o = new $this->key;
            foreach( $o->members as $m)
                if ( in_array( $m, explode(',',$this->cols)))
                    $str .=  '<th class="report-wizard-column-header">'.$o->source()->display_name_for($m).'</th>';
            $str .= "</tr>";
            try {
                $str .=  RecordSet::create($this->key,$q,$this->view,$this->none_view)->xhtml(false);
            } catch ( Exception $e){
                $str .= $e->getMessage();
            }
            $str .=  "</table></div>";
            return $str;
        }

    }
}
