<?php
require_once("modulos/usuario/usuario_model.php");
require_once("modulos/usuario/windows/usuario_reporte/usuario_reporte_controller.php");
class usuario_reporte_model extends usuario_model {
    static $_instance;
    
    public function __construct(){
        parent::__construct();
    }
    
    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {}
    
    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getUsersSearch(){
        $strNickname = (!empty($_POST["txt_searchNickname"])) ? $this->sql_real_escape_string(usuario_controller::user_magic_quotes($_POST["txt_searchNickname"], true)) : "";
        $strNombre = (!empty($_POST["txt_searchNombreCompleto"])) ? $this->sql_real_escape_string(usuario_controller::user_magic_quotes($_POST["txt_searchNombreCompleto"], true)) : "";
        $strFilter = "";
        if (!empty($strNickname))
            $strFilter = "AND nickname LIKE '%{$strNickname}%'";

        if (!empty($strNombre))
            $strFilter = "AND nombreCompleto LIKE '%{$strNombre}%'";
        
        return $this->getUserInfo($strFilter);
    }
}