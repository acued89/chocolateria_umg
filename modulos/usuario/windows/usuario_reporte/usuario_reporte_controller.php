<?php
require_once("modulos/usuario/usuario_controller.php");
require_once("modulos/usuario/windows/usuario_reporte/usuario_reporte_model.php");
require_once("modulos/usuario/windows/usuario_reporte/usuario_reporte_view.php");

class usuario_reporte_controller extends usuario_controller{
    private $objView;
    private $objModel;
    
    public function __construct($strAction = "") {
        parent::__construct($strAction);
        $this->objView = new usuario_reporte_view($strAction);
        $this->objModel = new usuario_reporte_model();
        
        $boolPrint = (isset($_GET["print"]))?true:false;
        $this->objView->setBoolPrint($boolPrint);
    }
    
    public function getPage(){
        $this->getSearch();
        $this->objView->drawPage();
    }
    public function getSearch(){
        if(isset($_GET["gBusqueda"])){
            $boolPrint = (isset($_GET["print"]))?true:false;
            $this->objView->setBoolPrint($boolPrint);
            
            if(!$this->objView->getBoolPrint())
                header("Content-Type: text/html; charset=iso-8859-1");
            
            $arrT = $this->objModel->getUsersSearch();
            $this->objView->getResultReport($arrT);
            die();
        }
    }
    
}