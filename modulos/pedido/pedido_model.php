<?php
require_once("core/global_model.php");
require_once 'core/global_controller.php';

class pedido_model extends global_model{
    static $_instance;

    public function __construct() {
        parent::__construct();
    }

    private function __clone() { }

    public static function getInstance() {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getArrProductos($arrTerms = false, $arrHaving = false, $status='', $intRows = 0,$includeUF8 = false){
        $arrReturn = false;
        $strfilter = "";
        if($arrTerms){
            while($arrT = each($arrTerms)){
                if(!empty($arrT["value"])){
                    $strfilter .=  !(empty($strfilter))?" OR ":" AND ";
                    $strfilter .= " P.{$arrT["key"]} LIKE '%{$arrT["value"]}%'";
                }
            }
        }
        $strHaving = "";
        if($arrHaving){
            while($arrT = each($arrHaving)){
                if(!empty($arrT["value"])){
                    $strHaving .=  !(empty($strfilter))?" OR ":" HAVING ";
                    $strHaving .= " {$arrT["key"]} LIKE '%{$arrT["value"]}%'";
                }
            }
        }
        $strLimit = "";
        $strTypeJoin = "INNER";
        if(empty($status) || ($status == "solicitado"))
            $strTypeJoin = "LEFT";

        if(!empty($status)) $strfilter .= " AND P.status = '{$status}'";
        if(!empty($intRows)) $strLimit = "LIMIT 0,{$intRows}";

        $sql = "SELECT  P.idproducto, P.codigo, P.nombre, P.etiqueta,
                        P.descripcion, P.precio_venta, P.ofertado, P.precio_oferta,
                        P.disponibles, GROUP_CONCAT(DISTINCT L.nombre SEPARATOR ', ') laboratorio
                FROM    ((producto P
                            {$strTypeJoin} JOIN inventario_master IM
                                ON IM.idproducto = P.idproducto)
                                    LEFT JOIN laboratorio L
                                        ON L.idlaboratorio = IM.idlaboratorio)
                WHERE   1
                        {$strfilter}
                GROUP BY P.idproducto
                        {$strHaving}
                        {$strLimit}";
        //$this->sql_queryDebug($sql);
        $stmt = $this->sql_ejecutar($sql);
        $arrDetalle = array();
        if($this->sql_num_rows($stmt)){
            $objInventario = inventario_model::getInstance();
            while($rtmp = $this->sql_fetch_assoc($stmt)){
                $arrDetalle[$rtmp["idproducto"]]["idproducto"] = $rtmp["idproducto"];
                $arrDetalle[$rtmp["idproducto"]]["codigo"] = $rtmp["codigo"];
                $arrDetalle[$rtmp["idproducto"]]["nombre"] = global_controller::user_magic_quotes($rtmp["nombre"],$includeUF8);
                $arrDetalle[$rtmp["idproducto"]]["etiqueta"] = global_controller::user_magic_quotes($rtmp["etiqueta"],$includeUF8);
                $arrDetalle[$rtmp["idproducto"]]["descripcion"] = $rtmp["descripcion"];
                $arrDetalle[$rtmp["idproducto"]]["precio_venta"] = $rtmp["precio_venta"];
                $arrDetalle[$rtmp["idproducto"]]["ofertado"] = $rtmp["ofertado"];
                $arrDetalle[$rtmp["idproducto"]]["precio_oferta"] = $rtmp["precio_oferta"];
                $arrDetalle[$rtmp["idproducto"]]["disponibles"] = $rtmp["disponibles"];
                $arrDetalle[$rtmp["idproducto"]]["laboratorio"] = $rtmp["laboratorio"];
                $arrDetalle[$rtmp["idproducto"]]["codigo_lote"] = 0; 
                $arrPresentaciones = $objInventario->getListadoPresentaxProducto($rtmp["idproducto"],$rtmp["disponibles"]);
                $arrDetalle[$rtmp["idproducto"]]["presenta"] = $arrPresentaciones;

                unset($rtmp);
            }
        }
        if($arrDetalle){
            $arrReturn["status"] = "ok";
            $arrReturn["msj"] = "Resultados devueltos.";
            $arrReturn["detalle"] = $arrDetalle;
            //$arrReturn["query"] = $sql;
        }
        else{
            $arrReturn["status"] = "fail";
            $arrReturn["msj"] = "No se encontraron productos para su busqueda.";
        }
        return $arrReturn;
    }
}