<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 21/10/12
 * Time: 08:50 PM
 */
class xo_performance_tracker extends magic_object {
    public function __construct(){
        // initialize some stats
        $this->number_sql_queries = 0;
        $this->duplicate_sql_queries = 0;
        $this->sql_queries = array();
        $this->unique_sql_queries = array();
        $this->sql_queries_count = array();
        $this->number_unique_sql_queries = 0;
    }
    public function performance($stat,$value){
        switch($stat){
            case 'number_sql_queries':
                $this->$stat += (int)$value;
            break;
            case 'sql_queries':
                $a = $this->sql_queries;
                $u = $this->unique_sql_queries;
                $c = $this->sql_queries_count;
                // if we haven't seen this query before
                if ( ! in_array($value,$u)){
                    // add it to unique queries
                    array_push($u,$value);
                    // add a counter for # of calls
                    $key = array_search($value,$u);
                    // add a count
                    $c[$key] = 1;
                // keep tabs of count
                } else {
                    $key = array_search($value,$u);
                    $c[$key]++;
                }
                if ( in_array($value,$a))
                    $this->duplicate_sql_queries++;
                array_push( $a,$value);
                $this->sql_queries = $a;
                $this->unique_sql_queries = $u;
                $this->number_unique_sql_queries = count($u);
                $this->sql_queries_count = $c;
            break;
        }
    }
    public function __toString(){
        $str = "<br>\r\n<br>\r\n<br>\r\nBEGIN PERFORMANCE STATISTICS<br>\r\n<br>\r\n<br>\r\n";
        $str .= "$this->number_sql_queries SQL queries performed<br>\r\n";
        $str .= "$this->duplicate_sql_queries DUPLICATE SQL queries performed<br>\r\n";
        $str .= "$this->number_unique_sql_queries UNIQUE SQL queries performed<br><br>\r\n";
        // show unique queries
        /*
        $str .= "<br>Below are the unique queries for the page:<br>\r\n";
        foreach( $this->unique_sql_queries as $q){
            $str .= "$q<br>\r\n";
        }
        */
        // sql queries count
        // show unique queries
        $str .= "<br>Below are the # of calls for each query:<br>\r\n";
        foreach( $this->sql_queries_count as $key => $count){
            $str .= $count . " calls to ". $this->unique_sql_queries[$key] . "<br>\r\n";
        }
        $str .= "<br>\r\n<br>\r\n<br>\r\nEND PERFORMANCE STATISTICS<br>\r\n<br>\r\n<br>\r\n";
        return $str;
    }
}
