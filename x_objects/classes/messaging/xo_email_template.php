<?php
/**
 * Allows you to quickly templatize some HTML or text (from the database),
 * together with a Business Object, thus accessing its members, and passing
 * along the resulting Object as a parsed string
 *
 * Best used passed as an argument to xo_email_message
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 20/11/12
 * Time: 06:11 PM
 */
class xo_email_template extends magic_object{
    /**
     * @param $key string lookup key in email_templates db table
     * @param $bo business_object to use for fetching member values
     */
    public function __construct($key,$bo){
        $this->template = new bo_email_template("key='$key'");
        $this->text = $this->template->text;
        if ( preg_match_all("/#([a-z|A-Z|0-9|_]+)/",$this->text,$hits)){
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
