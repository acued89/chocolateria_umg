<?php
require_once 'core/global_controller.php';
require_once 'core/global_model.php';

class usuario_model extends global_model{
    static $_instance;
    function __construct() {
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
    
    public function getUserInfo($strFilter = ''){
        $arrReturn = false;
        $sql = "SELECT  U.*, UA.type_priv, UA.menu_id, M.nombre AS nombreMenu
                FROM    usuario AS U
                        LEFT JOIN usuario_acceso AS UA
                            ON UA.userid = U.userid
                                LEFT JOIN menu AS M
                                    ON M.menu_id = UA.menu_id
                WHERE   1 {$strFilter}";
        $qtmp = $this->sql_ejecutar($sql);
        while($stmp = $this->sql_fetch_assoc($qtmp)){
            $arrReturn[$stmp["userid"]]["userid"] = $stmp["userid"];
            $arrReturn[$stmp["userid"]]["nickname"] = $stmp["nickname"];
            $arrReturn[$stmp["userid"]]["tipo"] = $stmp["tipo"];
            $arrReturn[$stmp["userid"]]["nombres"] = $stmp["nombres"];
            $arrReturn[$stmp["userid"]]["apellidos"] = $stmp["apellidos"];
            $arrReturn[$stmp["userid"]]["nombreCompleto"] = $stmp["nombreCompleto"];
            $arrReturn[$stmp["userid"]]["active"] = $stmp["active"];
            if(!empty($stmp["menu_id"])){
                if(!isset($arrReturn[$stmp["userid"]]["accesos"])) $arrReturn[$stmp["userid"]]["accesos"] = array();
                $arrReturn[$stmp["userid"]]["accesos"][$stmp["menu_id"]]["menu_id"] = $stmp["menu_id"];
                $arrReturn[$stmp["userid"]]["accesos"][$stmp["menu_id"]]["nombre"] = $stmp["nombreMenu"];
            }
        }
        return $arrReturn;
    }
}