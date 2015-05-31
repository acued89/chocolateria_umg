<?php
require_once("core/global_model.php");
require_once("core/global_controller.php");

class login_model extends global_model{

    private $objcontroller;
    
    function __construct() {
        parent::__construct();
        $this->objcontroller = new global_controller;
    }

    function limpiar_session(){
        $strSesionid = session_id();
        $_SESSION["wild"] = array();
        $_SESSION["wild"]["uid"] = 0;
        $_SESSION["wild"]["CountryName"] = "";
        $_SESSION["wild"]["IdCountry"] = "";
        $_SESSION["wild"]["IdRole"] = "";
        $_SESSION["wild"]["RoleName"] = "";
        $_SESSION["wild"]["UserName"] = "";
        $_SESSION["wild"]["Password"] = "";
        $_SESSION["wild"]["tipo"] = "";
        $_SESSION["wild"]["tipo"] = "*PUBLIC*";
    }

    function llenar_session($strUser,$strPassword ){
        
        $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/UserInformation/json/".$strUser."/".$strPassword));
        $array = get_object_vars($jsonObject);
        $_SESSION["wild"]["CountryName"] = $array['CountryName'];
        $_SESSION["wild"]["IdCountry"] = $array['IdCountry'];
        $_SESSION["wild"]["IdRole"] = $array['IdRole'];
        $_SESSION["wild"]["RoleName"] = $array['RoleName'];
        $_SESSION["wild"]["UserName"] = $strUser;
        $_SESSION["wild"]["Password"] = $strPassword;
        $_SESSION["wild"]["tipo"] = "admin";
        $_SESSION["wild"]["logged"] = true;
        $_SESSION["wild"]["uid"] = 1;
        $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/Permissions/json/".$strUser."/".$strPassword));
        $array = get_object_vars($jsonObject);
        $_SESSION["wild"]["permisos"] = $array;
        
        return true;
        
    }
    
    function llamarServicioWeb($pStrModoConexion, $pStrUser, $pStrPassword){
        
        if ($pStrModoConexion == "validate"){
            $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/Validate/json/".$pStrUser."/".$pStrPassword));
        }else if ($pStrModoConexion == "information"){
            $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/UserInformation/json/".$pStrUser."/".$pStrPassword));
        }else if ($pStrModoConexion == "permissions"){
            $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/Permissions/json/".$pStrUser."/".$pStrPassword));
        }
        return $jsonObject;
    }
	
    function login($strUser, $strPassword, $intUid = false){
        $strSesionid = session_id();
        $strUser = strtolower(trim($strUser));
        if(!preg_match("/^[a-z]+$/",$strUser)){
            return false;
        }
        $boolSetSession = false;
        $jsonObject = json_decode(file_get_contents("http://umgsk8ertux.azurewebsites.net/Services/Credentials.svc/Validate/json/".$strUser."/".$strPassword));
        $array = get_object_vars($jsonObject);
        $isAutenticated = $array['ValidateJsonResult'];
        
        if ($isAutenticated){
            $boolSetSession = true;
            $this->llenar_session($strUser,$strPassword);
            return true;
        }else{
            $this->LogOut();
            return false;
        }
    }
    
    public function LogOut(){
       session_destroy();
       unset($_SESSION["wild"]);
       return true;
    }
    
    public function check_login(){
        if(isset($_SESSION["wild"])){
            if ($this->login($_SESSION["wild"]["UserName"],$_SESSION["wild"]["Password"],$_SESSION["wild"]["uid"])) {
                return true;
            }            
        }
        return false;
    }
}