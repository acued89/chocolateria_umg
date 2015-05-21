<?php
require_once("modulos/usuario/usuario_model.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_controler.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_view.php");

class usuario_registro_model extends usuario_model {
    static $_instance;
    public function __construct() {
        parent::__construct();        
    } 
    
    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {
        
    }

    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getArrayUser() {
        $this->objC = new usuario_registro_controler();
        $strNickname = (!empty($_POST["txt_searchNickname"])) ? $this->sql_real_escape_string($this->objC->user_magic_quotes($_POST["txt_searchNickname"], true)) : "";
        $strNombre = (!empty($_POST["txt_searchNombreReal"])) ? $this->sql_real_escape_string($this->objC->user_magic_quotes($_POST["txt_searchNombreReal"], true)) : "";
        $strFilter = "";
        if (!empty($strNickname))
            $strFilter = "AND nickname LIKE '%{$strNickname}%'";

        if (!empty($strNombre))
            $strFilter = "AND nombreCompleto LIKE '%{$strNombre}%'";
        return $this->getUserInfo($strFilter);
    }
    
    public function getPage(){
        $strNickname = (!empty($_POST["txt_searchNickname"])) ? $this->sql_real_escape_string(usuario_registro_controler::user_magic_quotes($_POST["txt_searchNickname"], true)) : "";
        $strNombre = (!empty($_POST["txt_searchNombreReal"])) ? $this->sql_real_escape_string(usuario_registro_controler::user_magic_quotes($_POST["txt_searchNombreReal"], true)) : "";
        $strFilter = "";
        if (!empty($strNickname))
            $strFilter = "AND nickname LIKE '%{$strNickname}%'";

        if (!empty($strNombre))
            $strFilter = "AND nombreCompleto LIKE '%{$strNombre}%'";
        return $this->getUserInfo($strFilter);
    }
    
    public function getUserID($arrParams){
        $strFilter = "";
        while($arrt = each($arrParams)){
            $strFilter .= (!empty($strFilter))?" AND {$arrt["key"]} = '{$arrt["value"]}'":" {$arrt["key"]} = '{$arrt["value"]}'";
            unset($arrt);
        }
        $strSQL = "SELECT userid FROM usuario WHERE {$strFilter}";
        $intUser = $this->sql_ejecutarKey($strSQL);
        return $intUser;
    }
}