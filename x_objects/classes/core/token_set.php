<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 05/12/12
 * Time: 12:33 PM
 */
class token_set extends magic_object{
    private $tokens = array();
    public function __construct($string,$separator = ' '){
        $this->tokens = explode($separator,$string);
    }
    public function __get($what){
        switch( $what){
            case 'singles':
                return $this->tokens;
            break;
            case 'doubles':
                $doubles = array();
                for ( $key = 0; $key<count($this->tokens);$key++){
                    $keys = array(
                        "first"=>$key,
                        "second"=>($key+1)%count($this->tokens),

                    );
                    array_push( $doubles,
                        $this->tokens[$keys["first"]]." ".
                            $this->tokens[$keys["second"]]
                    );
                }
                return $doubles;
                break;

            case 'triples':
                $triples = array();
                for ( $key = 0; $key<count($this->tokens);$key++){
                    $keys = array(
                        "first"=>$key,
                        "second"=>($key+1)%count($this->tokens),
                        "third"=>($key+2)%count($this->tokens),

                    );
                    array_push( $triples,
                        $this->tokens[$keys["first"]]." ".
                        $this->tokens[$keys["second"]]. " ".
                        $this->tokens[$keys["third"]]
                    );
                }
                return $triples;
            break;

        }
        return null;
    }
}
