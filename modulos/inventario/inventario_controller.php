<?php
require_once 'core/global_view.php';
require_once 'core/global_model.php';

class inventario_controller extends global_controller{
    static $_instance;
    
    function __construct($strAction) {
        parent::__construct($strAction);
    }
    
    /*Evitemos el clonaje del objeto. Patron singleton*/
    private function __clone() { }
    
    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance($strAction) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }
        }
