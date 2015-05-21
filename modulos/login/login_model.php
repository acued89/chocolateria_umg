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
        $_SESSION["wild"]["name"] = "";
        $_SESSION["wild"]["pswd"] = "";
        $_SESSION["wild"]["logged"] = false;
        $_SESSION["wild"]["tipo"] = "*PUBLIC*";
    }

    function llenar_session($intUsuario){
        $boolReturn = false;
        $intUsuario = intval($intUsuario);
        $strQuery = "SELECT Auser.userid,Auser.nickname,Auser.password,Auser.tipo
         FROM usuario Auser
         WHERE userid= {$intUsuario} "; 
        $stmt = $this->sql_ejecutar($strQuery);
        $resp = $this->sql_fetch_assoc($stmt);       
        
        if($this->sql_num_rows($stmt) > 0){
            $_SESSION["wild"]["uid"] = $intUsuario;
            $_SESSION["wild"]["name"] = $resp["nickname"];
            $_SESSION["wild"]["pswd"] = $resp["password"];
            $_SESSION["wild"]["logged"] = true;
            $_SESSION["wild"]["tipo"] = $resp["tipo"];
            
            $boolReturn = true;
        }    
        return $boolReturn;
    }
	
    function login($strUser, $strPassword, $intUid = false){
        $strSesionid = session_id();
        
        $strUser = strtolower(trim($strUser));
        if(!preg_match("/^[a-z]+$/",$strUser)){
            return false;
        }
        
        $boolSetSession = false;
        if($intUid === false){
            $strPassword = md5($strPassword);
            $sql = $this->sql_ejecutarKey("SELECT  * FROM usuario Auser 
                                        WHERE   nickname = '{$strUser}' AND 
                                                password = '{$strPassword}' AND 
                                                active = 'Y'");
            $boolSetSession = true;
        }
        else{
            if(!preg_match("/^[0-9]+$/",$intUid)){
                $intUid = 0;
            }
            $strPassword = $this->sql_real_escape_string($strPassword);
            $sql = $this->sql_ejecutarKey("SELECT * FROM usuario Auser
                                        WHERE   nickname = '{$strUser}' AND 
                                                password = '{$strPassword}' AND 
                                                active = 'Y'");
        }

        if($sql){
            if($boolSetSession){
                $this->llenar_session($sql["userid"]);
            }
            return true;
        }
        $this->LogOut();
        return FALSE;       
    }
    
    public function LogOut(){
       session_destroy();
       unset($_SESSION["wild"]);
       return true;
    }
    
    public function check_login(){
        if(isset($_SESSION["wild"])){
            if ($this->login($_SESSION["wild"]["name"],$_SESSION["wild"]["pswd"],$_SESSION["wild"]["uid"])) {
                return true;
            }            
        }
        return false;
    }
}