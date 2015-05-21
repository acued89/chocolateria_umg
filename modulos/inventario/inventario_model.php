<?php
require_once 'core/global_view.php';
require_once 'core/global_model.php';

class inventario_model extends global_model{
    static $_instance;

    function __construct() {
        parent::__construct();
    }

    private function __clone() {}

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
}
