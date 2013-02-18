<?php
/**
 * web component to quickly convert a date or timestamp to human readable,
 * with reference only to the day (ignores the time)
 */
class human_day {
    private $date = null;
    public function __construct($when = "now"){
        switch ($when){
            case 'now':
                $this->date = date('Y-m-d');
            break;
            default:
                if ( preg_match('/\+([0-9]+) day(s?)/',$when,$hits)){
                    $this->date = date("Y-m-d",strtotime($hits[0]));
                }
            break;
        }
    }
    public function __toString(){
        $str = "unknown";
        if ( $this->date == date('Y-m-d')){
            $str = "today";
        } else {
            // future
            if ( $this->date > date('Y-m-d')){
                // calculate days diff
                $days_diff = ceil((strtotime($this->date) - time())/86400);
                switch( $days_diff){
                    case 1: $str = "tomorrow"; break;
                    case 2: $str = "day after tomorrow"; break;
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        $str = "this coming ".date('D',strtotime("+ $days_diff day"));break;
                    default:
                        if ( $days_diff > 7)
                            $str = date('D M jS',strtotime("+ $days_diff day"));
                    break;

                }
            }
        }

        return $str;
    }
}
