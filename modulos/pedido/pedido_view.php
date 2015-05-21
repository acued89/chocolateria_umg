<?php
require_once 'core/global_view.php';

class pedido_view extends global_view{
    static $_instance;
    
    function __construct($strAction) {
        parent::__construct($strAction);
    }
    
    private function __clone() { }
    
    public static function getInstance($strAction) {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }
}