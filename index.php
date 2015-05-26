<?php 
date_default_timezone_set("America/Guatemala");
session_start();
$strAction = basename(__FILE__);
error_reporting(0);

if((isset($_GET["act"])) && ($_GET["act"] == "lnk")){
    require_once("core/global_controller.php");
    $strPage = $_GET["page"];
    $strTitle = isset($_GET["title"])?$_GET["title"]:"";
    $strMod = isset($_GET["mod"])?$_GET["mod"]:"";
    $boolInclude = (isset($_GET["ijs"]))?true:false;    
    $controller = new global_controller($strAction);
    if($boolInclude){
        $controller->getObjViewScripts();
    }
    $controller->getAjaxContent($strPage,$strMod,"","", ($boolInclude)?false:true );
    
    die();
}

require_once("core/global_controller.php");
require_once("modulos/login/login_model.php");


$login = new login_model();
if(isset($_GET["logout"])){
    $login->LogOut();
    header("location: index.php");
    exit();
}


if(isset($_POST["login_name"]) && isset($_POST["login_passwd"])){
    if(!$login->login($_POST["login_name"], $_POST["login_passwd"])){
       echo "<div class='error' width='100%' align='center'>
                <b>El usuario y password son incorrectos</b>
             </div>";
    }    
}

$objcontroller = new global_controller($strAction);
$objcontroller->setStrTitle("Web POS");


if($login->check_login()){ 
    $objcontroller->principal_struct();
}
else{
    $objView = new global_view($strAction); 
    $objView->getCabecera("Farmacias Lux",false);
    
    include_once("modulos/login/login_view.php");
    
    $objView->getPiePagina();
}