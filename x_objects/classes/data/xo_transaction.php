<?php
/**
 * @property array $as_array array representation of transaction
 */
abstract class xo_transaction {
    protected $array_representation;
    abstract public function process();
    abstract public function commit();
    abstract public function rollback();
    public function __get($what){
        switch( $what) {
            case 'as_array':
                return $this->array_representation;
            break;
        }
    }

}
