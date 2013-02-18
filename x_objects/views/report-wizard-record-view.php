<?php
    global $business_object, $html,$report_wizard_columns;
    $cols = explode(',',$report_wizard_columns);
    $html = "<tr class=\"report-wizard-record-view\" id=\"report_record_$business_object->id\">";
    foreach ( $business_object->members as $m) {
        if ( in_array( $m,$cols)){
            if ( preg_match('/_date/',$m) || in_array($m,array("last_modified_by")))
                $m = "friendly_$m";
            $html .= "<td class=\"wizard-member member-$m\">".$business_object->$m."</td>";

        }
    }
    $html .= "</tr>";
?>