<?php

require_once("modulos/inventario/inventario_controller.php");
require_once("modulos/inventario/windows/inventario_barcode/inventario_barcode_view.php");


class inventario_barcode_controller extends inventario_controller{
    static $_instance;
    public function __construct($strAction = "") {
        parent::__construct($strAction);
        $this->objView = inventario_barcode_view::getInstance($strAction);
    }
    private function __clone() { }

    public static function getInstance($strAction = "") {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }
    public function getOperacion(){
        $arrReturn  = false;
        $op = $this->checkParam("op");
        /*Parametros de Daniel */
        $barcode = $this->checkParam("barcode");
       
        $peso = $this->checkParam("peso");
        $url = $this->checkParam("url");
        /* fin de parametros */
        $name = $this->checkParam("name");
        $id = $this->checkParam("id");
        $description = $this->checkParam("description");
        /*Los servicios de dba no son rest como tal, ya que no utilizan los metodos post, put or delete.
         * Sin embargo podemos llegar a ellos por medio de un get en forma global
        */
        if($op == "new"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/SizeWightBarcode.svc/New/{$barcode}/{$id}/{$peso}/{$url}";
            $exec = RestClient::get($link); 
        }
        else if($op =="update"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/SizeWightBarcode.svc/Update/{$id}/{$name}/{$description}";
            $exec = RestClient::get($link); 
        }
        elseif($op == "delete"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/SizeWightBarcode.svc/Delete/{$id}";
            $exec = RestClient::get($link);
        }
        else{
            $link = "http://umgsk8ertux.azurewebsites.net/Services/SizeWightBarcode.svc/Get/json/0/null";
            $exec = RestClient::get($link);
        }
        
        //Para debuguear
        //debug::drawdebug($exec->getResponse());
        //debug::drawdebug($link);
        $arrReturn = json_decode($exec->getResponse());
        return $arrReturn;
    }
    public function getPage(){           
        $this->objView->drawPage();
    }
    
   
}
