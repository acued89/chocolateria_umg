<?php
require_once("modulos/caja/caja_controller.php");
require_once("modulos/caja/windows/factura/factura_view.php");


class factura_controller extends caja_controller{
    static $_instance;
    public function __construct($strAction = "") {
        parent::__construct($strAction);
        $this->objView = factura_view::getInstance($strAction);
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
        $data = $this->checkParam("data");
        $id = $this->checkParam("id");
        /*Los servicios de dba no son rest como tal, ya que no utilizan los metodos post, put or delete.
         * Sin embargo podemos llegar a ellos por medio de un get en forma global
        */
        if($op == "new"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/TradeMarks.svc/New/{$data}";
            $exec = RestClient::get($link); 
        }
        else if($op =="update"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/TradeMarks.svc/Update/{$id}/{$data}";
            $exec = RestClient::get($link); 
        }
        elseif($op == "delete"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/TradeMarks.svc/Delete/{$id}";
            $exec = RestClient::get($link);
        }
        else if($op == "getcliente"){
           $link = "http://umgsk8ertux.azurewebsites.net/Services/Customer.svc/Get/json/0/0/null/null";
           $exec = RestClient::get($link);
        }
        else if($op == "getBodega"){
            $link = "http://umgsk8ertux.azurewebsites.net/Services/WareHouses.svc/Get/xml/0/0/null";
            $exec = RestClient::get($link);
        }        
        else{
            $link = "http://umgsk8ertux.azurewebsites.net/Services/TradeMarks.svc/Get/json/0/null";
            $exec = RestClient::get($link);
        }
        
        //Para debuguear
        //debug::drawdebug($exec->getResponse());
        //debug::drawdebug($link);
        $arrReturn = json_decode($exec->getResponse());
        return $arrReturn;
    }
    public function getPage(){
        if($this->checkParam("view") =="bodegas"){
            $this->objView->drawBodegas();
        }
        else if($this->checkParam("view") == "productos"){
            $arrProductos = $this->getProductos();
            $this->objView->drawProductos($arrProductos);
        }
        else if($this->checkParam("view") == "additem"){
            $this->objView->additem();
        }
        else{
            $this->objView->drawPage();
        }        
    }
    
   public function getProductos(){
        $intBodega = $this->checkParam("bodegaID");
        $link = "http://umgsk8ertux.azurewebsites.net/Services/Products.svc/Get/json/0/0/0/null";
        $exec = RestClient::get($link);
        $arrReturn = json_decode($exec->getResponse());

        $link = "http://umgsk8ertux.azurewebsites.net/Services/SizeWightBarcode.svc/Get/json/null/0/null";
        $exec = RestClient::get($link);
        $arrB = json_decode($exec->getResponse());
       
        $arrProductos = array();
        foreach($arrReturn->{"GetJsonResult"} as $key => $value ){
           $arrProductos[$value->{"IdProduct"}]["Description"] = $value->{"Description"};
           $arrProductos[$value->{"IdProduct"}]["IdProduct"] = $value->{"IdProduct"};
           $arrProductos[$value->{"IdProduct"}]["IdTrademark"] = $value->{"IdTrademark"};
           $arrProductos[$value->{"IdProduct"}]["IdType"] = $value->{"IdType"};
           $arrProductos[$value->{"IdProduct"}]["Name"] = $value->{"Name"};
           unset($key);
           unset($value);
       }
       $arrBarcode = array();
       foreach($arrB->{"GetJsonResult"} as $key => $value){
           if(isset($arrProductos[$value->{"IdProduct"}])){
               if(!isset($arrProductos[$value->{"IdProduct"}]["presentaciones"])) $arrProductos[$value->{"IdProduct"}]["presentaciones"] = array();
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["Barcode"] = $value->{"Barcode"};
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["IsActive"] = $value->{"IsActive"};
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["SizeOrWeight"] = $value->{"SizeOrWeight"};
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["IdProduct"] = $value->{"IdProduct"};
               
               $arrBarcode[$value->{"Barcode"}]["Barcode"] = $value->{"Barcode"};
               $arrBarcode[$value->{"Barcode"}]["IdProduct"] = $value->{"IdProduct"};
           }
           else{
               if(!isset($arrProductos[$value->{"IdProduct"}]["presentaciones"])) $arrProductos[$value->{"IdProduct"}]["presentaciones"] = array();
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["Barcode"] = $value->{"Barcode"};
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["IsActive"] = $value->{"IsActive"};
               $arrProductos[$value->{"IdProduct"}]["presentaciones"][$value->{"Barcode"}]["SizeOrWeight"] = $value->{"SizeOrWeight"};
           }                      
           unset($key);
           unset($value);
       }
       
       
        $link = "http://umgsk8ertux.azurewebsites.net/Services/InventoryControl.svc/Get/json/null/0";
        $exec = RestClient::get($link);
        $arrC = json_decode($exec->getResponse());
        $arrInventario = array();        
        
        foreach($arrC->{"GetJsonResult"} AS $key => $value){
            if(isset($arrBarcode[$value->{"Barcode"}])){
                $boolOK = true;
                if(!empty($intBodega)){
                    if($intBodega != $value->{"IdWarehouse"}) $boolOK = false;
                }
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["Barcode"] = $value->{"Barcode"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["CostPrice"] = $value->{"CostPrice"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["DozenPercent"] = $value->{"DozenPercent"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["DozenPrice"] = $value->{"DozenPrice"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["IdWarehouse"] = $value->{"IdWarehouse"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["Price"] = $value->{"Price"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["ProfitAmount"] = $value->{"ProfitAmount"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["ProfitPercent"] = $value->{"ProfitPercent"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["Stock"] = ($boolOK)?$value->{"Stock"}:"";
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["TaxAmount"] = $value->{"TaxAmount"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["TaxPercent"] = $value->{"TaxPercent"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["WholesalePercent"] = $value->{"WholesalePercent"};
                $arrInventario[$arrBarcode[$value->{"Barcode"}]["IdProduct"]][$value->{"Barcode"}]["WholesalePrice"] = $value->{"WholesalePrice"};
            }            
        }
        foreach($arrInventario AS $key => $value){
            foreach($value AS $key2 => $value2){
                if(isset($arrProductos[$key]["presentaciones"][$key2])){
                    $arrProductos[$key]["presentaciones"][$key2]["inventario"] = $value2;
                } 
            }            
        }   
        return $arrProductos;
   }
}

