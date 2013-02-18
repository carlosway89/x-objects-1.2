<?php
    global $business_object, $html;
    $html = '<select class="'.$business_object->css_class.'" id="'.$business_object->element_id.'" >
        <option value="">Seleccionar</option>';

    for ($s = strtotime("0:00:00"),$incr=0; $incr<=48;$incr++,$s = $s+30*60){

        $v = date('H:i:s', $s);
        $selected = $v == $business_object->default_value? 'selected="selected"':'';
        $html .='<option '.$selected.' value="'. $v .'">'.date('g:i A',$s).'</option>';
    }
    $html.='</select>';
?>