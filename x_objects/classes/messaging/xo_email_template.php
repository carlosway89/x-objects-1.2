<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 20/11/12
 * Time: 06:11 PM
 */
class xo_email_template extends magic_object{
    public function __construct($key,$bo){
        $this->template = new bo_email_template("key='$key'");
        $this->text = $this->template->text;
        if ( preg_match_all("/#([a-z|_]+)/",$this->text,$hits)){
           // print_r($hits);
            foreach ($hits[1] as $member){
                $this->text= preg_replace("/#$member/",$bo->$member,$this->text);
            }
        }
    }
    public function __toString(){
        return $this->text?$this->text:(string)"";
    }
}
