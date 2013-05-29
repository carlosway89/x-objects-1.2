<?php
/**
 *
 * Controller for access to X-Objects via REST URLs (forming a REST API with responses
 * in JSON and xHTML)
 *
 * /xobj/c/<my_model>/xhtml/<my-view>
 * /xobj/c/<my_model>/json
 *
 * Create a new Record (Object), given its model.  Output as specified either JSON or XHTML
 *
 * Parameters: json (encoded JSON string): values to set for new Record
 *
 *
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 06/11/12
 * Time: 04:58 PM
 */
class xobj_controller extends xo_controller {
    public function set_debug(){
        global $webapp_location;
        $xml_file = $webapp_location."/app/xml/x-objects.xml";
        $xml = simplexml_load_file($xml_file); //This line will load the XML file.

        $sxe = new SimpleXMLElement($xml->asXML()); //In this line it create a SimpleXMLElement object with the source of the XML file.
        $token = '';
        switch($this->uri->part(3)){
            case '0': $token ='disabled'; break;
            case '1': $token = 'enabled'; break;
            case '2': $token = 'app'; break;
        }
        $sxe->debugger->status = $token;
        //This next line will overwrite the original XML file with new data added
        $sxe->asXML($xml_file);
        echo json_encode(array('result'=>'success'));
    }
    public function recordset(){
        $j = json_decode($this->req->json);
        echo RecordSet::create($j->key,$j->query,$j->view,$j->none_view)->xhtml($j->wrapper);
    }
    // create something new
    public function c(){
        $key = $this->uri->part(3);
        $output = $this->uri->part(4);
        $obj = null;
        $result = false;
        $error ="";
        if ( class_exists($key)){
            $obj = call_user_func("$key::create_from_json",json_decode($this->req->json))   ;
            $result = $obj->save();
            $error = $obj->save_error;
        }
        if( $output == 'xhtml'){
            if ( ! $result){
                echo "<div>$obj->save_error</div>";
            } else {
                $keycol = $obj->source()->keycol();
                $keyval = $obj->$keycol;
                $obj = new $key("$keycol='$keyval'");
                $view = $this->uri->part(5);
                echo $obj->html($view);
            }
        } else{
            header("Content-Type: application/json");
            if ( ! $obj){
                echo json_encode( array(
                    "result"=>"error",
                    "error"=>"unable to create record; $key is a not valid class"
                ));
            } else {
                // reload it
                $keycol = $obj->source()->keycol();
                $keyval = $obj->$keycol;
                $obj = new $key("$keycol='$keyval'");
                echo json_encode( array(
                    "result"=>$result?"success":"error",
                    "record"=>$obj->as_array,
                    "message"=>"The $key was successfully created",
                    "error"=>$error
                ));

            }
        }
    }


    // record
    public function r(){
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $c = $this->uri->part(3);
        $id = $this->uri->part(4);
        if ( $container->debug && $container->debug_level >1)
            echo "<span style='color:blue;'>$tag->event_format: about to get datasource for $c</span><br>";
        $s = call_user_func("$c::source");
        $k = $s->keycol();
        $a = $this->uri->part(5);
        $o = new $c("$k='$id'");
        $sname = $container->appname;
        $svc = $sname?$container->services->$sname:null;
        $lmb = $svc?$svc->me->id:0;
        $orig_json = stripslashes($this->req->json);
        if ( $container->debug) echo "$tag->event_format: orig json is $orig_json<br>";
        $result = array(
            "api"=>"x_objects",
            "module"=>"record",
            "action"=>$a,
            "id"=>$id,
            "keycol" => $k,
            "key"=>$c,
            "service"=>$container->appname,
            "lmb"=>$lmb,
            "json"=>json_decode($orig_json),

        );
        switch( $a){
            // create a new record
            case 'c':
                $json = json_decode($orig_json);
            break;
            // update a record
            case 'u':
                if ($container->debug )       echo "<span style='color:blue;'>$tag->event_format: API call for Record Update</span><br>";

                // required for UI feedback on successful actions
                $result['message'] = 'The record was updated successfully';
                if (! $id || ! is_numeric($id)){
                    $result['result'] = "error";
                    $result["error"] = "No record id provided";
                } else {
                    $json = json_decode($orig_json);
                    $o->set_from_json($json);
                    $o->last_modified_by = $lmb;
                    $o->last_modified_date = date('Y-m-d H:i:s');
                    $result['result']=$o->save()?"success":"error";
                    $result['error']=$o->save_error;
                    $result['as_array']= $o->as_array;
                }
            break;
        }
        if (! $container->debug && ! $container->app_debug) header("Content-Type: application/json");
        echo json_encode($result);


    }
    //business
    public function b(){
        global $container;
        $c = $this->uri->part(3);
        $id = $this->uri->part(4);

        $s = call_user_func("$c::source");
        $k = $s->keycol();
        if ( preg_match('/\:/',$id)){
            $pair = explode(':',$id);
            $k = $pair[0];
            $id = $pair[1];
        }
        $a = $this->uri->part(5);
        $o = new $c("$k='$id'");
        $result = array(
            "api"=>"x_objects",
            "module"=>"business",
            "action"=>$a,
            "id"=>$id,
            "keycol" => $k,
            "key"=>$c
        );
        if ( ! $o->exists){
            $result['result'] = "error";
            $result['error'] = "no such object";

        } else {
            switch( $a){
                case 'xhtml':
                    header("Content-Type: text/html; charset=utf-8");
                    $view = $this->uri->part(6);
                    echo $o->html($view);
                    return true;
                break;
                case 'json':
                    $result['json']= $o->as_array;
                    $result['result']= "success";
                break;
                case 'safe_delete':
                    $deletor = $this->uri->part(6)?(int)$this->uri->part(6):0;
                    $result['result'] = $o->safe_delete($deletor)?"success":"error";
                    $result['error'] = $o->save_error;
                    $result['message'] = "The record has been successfully deleted";
                break;
                case 'delete':
                    $tokens = explode('_',$c);
                    array_walk($tokens,function(&$item,$index){
                       $item = ucfirst($item);
                    });
                    $result['message'] = 'The '.implode(' ',$tokens). ' has been deleted';
                    $result['result'] = $o->delete()?"success":"error";
                    $result['error'] = $o->delete_error;
                    break;
                default:
                    /**
                     * making more flexible, so method can return types
                     * other than bool
                     */
                    $results = $o->$a();
                    if ( is_array($results)){
                        $result['results'] = $results;
                    } else {
                        $result['result'] = $results?"success":"error";
                        $result['error'] = $o->last_error;
                        $result['message'] = $o->last_message;
                    }
                    break;
            }

        }
        if ( ! $container->debug ) header("Content-Type: application/json");
        echo json_encode($result);
        return true;
    }

}
