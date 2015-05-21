<?php
require_once("modulos/usuario/usuario_controller.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_model.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_view.php");

class usuario_registro_controller extends usuario_controller{
    private $objView;
    private $objModel;
    
    public function __construct($strAction = "") {
        parent::__construct($strAction);
        $this->objView =  usuario_registro_view::getInstance($strAction);
        $this->objModel = usuario_registro_model::getInstance();
    }
    
    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {
        
    }

    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance($strAction = "") {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }
    
    public function getPage(){
        $this->saveUser();
        $this->getSearch();
        $this->getDatosBusqueda();
        $this->objView->drawPage();
    }
    
    public function saveUser(){
        if(isset($_GET["sUser"])){
            header('Content-type: application/json');
            $arrResultado = array();
            $arrTMP = array();
            $arrKey = array();
            $arrKey["userid"] = 0;
            if(!empty($_POST["hidd_userid"])){
                $arrKey["userid"] = intval($_POST["hidd_userid"]);
            }
            $arrFields = array();
            $arrFields["nickname"] = isset($_POST["txt_nickname"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["txt_nickname"])):"";
            if($arrKey["userid"] == 0){
                $arrFields["password"] = isset($_POST["txt_password"])?md5($this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["txt_password"]))):"";
            }
            else{
                if(!empty($_POST["chk_pass"])){
                    $arrFields["password"] = isset($_POST["txt_password"])?md5($this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["txt_password"]))):"";
                }                
            }
            
            $arrFields["nombres"] = isset($_POST["txt_nombres"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["txt_nombres"])):"";
            $arrFields["apellidos"] = isset($_POST["txt_apellidos"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["txt_apellidos"])):"";
            $arrFields["nombreCompleto"] = isset($_POST["hddn_nombreCompleto"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["hddn_nombreCompleto"])):"";
            $arrFields["active"] = isset($_POST["chk_active"])?"Y":"N"; //isset($_POST["chk_active"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["chk_active"])):"";
            $arrFields["tipo"] = isset($_POST["slt_tipo"])?$this->objModel->sql_real_escape_string($this->user_magic_quotes($_POST["slt_tipo"])):"";            
            $boolOK = $this->objModel->sql_TableUpdate("usuario", $arrKey, $arrFields);
            $intUid = 0;
            
            
            if($arrKey["userid"] == 0){
                $arrParamU["nickname"] = $arrFields["nickname"];
                $arrParamU["nombres"] = $arrFields["nombres"];
                $arrParamU["apellidos"] = $arrFields["apellidos"];
                $intUid = $this->objModel->getUserID($arrParamU);           
                //usuario_registro_controller::debugPHP($intUid);
            }
            else{
                $intUid = $arrKey["userid"];
            }
            
            $arrTables = array();
            $arrTables["usuario_acceso"]["userid"] = $intUid;
            $this->objModel->sql_TableDelete($arrTables);
            reset($_POST);
            while($arrT = each($_POST)){
                $arrEx = explode("_",$arrT["key"]);
                if($arrEx[0] =="hddn" && $arrEx[1] =="ventana"){
                    $arrKeyAccess["userid"] = $intUid;
                    $arrPermisos = array();
                    $arrPermisos["menu_id"] = $arrT["value"];
                    $boolOK = $this->objModel->sql_TableUpdate("usuario_acceso", $arrKeyAccess, $arrPermisos,false, true);
                }
            }
            if($boolOK){
                $arrTMP["estado"] = "ok";
                $arrTMP["msg"] = "Se ha actualizado exitosamente su informacion";
            }
            else{
                $arrTMP["estado"] = "fail";
                $arrTMP["msg"] = "Error al guardar sus datos, por favor intentelo de nuevo";
            }
            array_push($arrResultado, $arrTMP);
            print json_encode($arrResultado);
            die();
        }
    }
    
    public function getSearch(){
        if(isset($_GET["gsUser"])){
            header("Content-Type: text/html; charset=iso-8859-i");
            $this->objView->getBuscar();
            die();
        }
    }
    
    public function getDatosBusqueda(){
        if(isset($_GET["gBusqueda"])){
            $arrT = $this->objModel->getArrayUser();
            $this->objView->getResult($arrT);
            die();
        }
    }
    
    public function getVentanas(){
        return $this->objModel->getVentanas();
    }
    
}