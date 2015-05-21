<?php
include_once("core/db_mysql.php");
include_once("core/global_controller.php");

class global_model extends db_mysql{

    private $objC;

    //Constructor
    function __constructor(){
        parent::__construct();
        $this->objC = global_controller::getInstance();
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

    public static function clearTerm($strTMP, $boolUTFDecode = false){
        if(!empty($strTMP)) 
            return self::sql_real_escape_string( global_controller::user_magic_quotes($strTMP, $boolUTFDecode) );
        else return "";
    }

    /**
    * Funcion que construye y ejecuta los queries para actualizar o insertar data.
    *
    * @param string $strTable Nombre de la tabla a afectar
    * @param array $arrKey Array con las llaves de la tabla campo=>value
    * @param array $arrFields Array con los datos a actualizar campo=>value
    * @param mixed $arrExtraInsertFields Array con campos extras a agregar si es un insert (dateregistered, por ejemplo)
    * @param boolean $boolForceReplace obliga a hacer un replace into
    */
    public function sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields = false, $boolForceReplace = false) {
        if(!$boolForceReplace){
            $strWhere = "1";
            while ($arrField = each($arrKey)) {
                $strValue =  $this->sql_real_escape_string($arrField["value"]);
                $strWhere .= " AND {$arrField["key"]} = '{$strValue}'";
            }
            // Primero veo si el dato ya existe
            $strQuery = "SELECT COUNT(*) AS conteo
                         FROM {$strTable}
                         WHERE {$strWhere}";
            $intNumRows = $this->sql_ejecutarKey($strQuery);
        }
        else {
            $intNumRows = 0;
        }

        if ($intNumRows <= 0) {
            // Insert
            $arrAllFields = array_merge($arrKey, $arrFields);
            if (is_array($arrExtraInsertFields)) {
                $arrAllFields = array_merge($arrAllFields, $arrExtraInsertFields);
            }
            $strFields = "";
            $strValues = "";
            while ($arrField = each($arrAllFields)) {
                $strValue = $this->sql_real_escape_string($arrField["value"]);
                $strFields .= ", {$arrField["key"]}";
                if($strValue == "NULL"){
                    $strValues .= ", {$strValue}";
                }
                else{
                    $strValues .= ", '{$strValue}'";
                }
            }
            $strFields = substr($strFields, 2);
            $strValues = substr($strValues, 2);
            $strCommand = ($boolForceReplace)?"REPLACE":"INSERT";
            $strQuery = "{$strCommand} INTO {$strTable}
                         ({$strFields})
                         VALUES
                         ({$strValues})";
            $this->sql_ejecutar($strQuery);
            return true;
        }
        else if ($intNumRows == 1) {
            // Update
            $strSet = "";
            while ($arrField = each($arrFields)) {
                $strValue = $this->sql_real_escape_string($arrField["value"]);
                if($strValue == "NULL"){
                    $strSet .= ", {$arrField["key"]} = {$strValue}";
                }
                else{
                    $strSet .= ", {$arrField["key"]} = '{$strValue}'";
                }
            }
            $strSet = substr($strSet, 2);

            $strQuery = "UPDATE {$strTable}
                         SET {$strSet}
                         WHERE {$strWhere}";
            $this->sql_ejecutar($strQuery);
            return true;
        }
    }

    /* Funcion para eliminar datos de las tablas
    * @param array() $arrTables["nombreTabla"]["key_tabla"] = valor
    */
    public function sql_TableDelete($arrTables){
        if(is_array($arrTables) && count($arrTables) > 0){
            while($arrEachT = each($arrTables)){
                $strWhere = "";
                if(is_array($arrEachT["value"]) && count($arrEachT["value"])){
                    while($arrTMP2 = each($arrEachT["value"])){
                        $strWhere .= (empty($strWhere))?"{$arrTMP2["key"]}='{$arrTMP2["value"]}'": " AND {$arrTMP2["key"]}='{$arrTMP2["value"]}'";
                    }
                    $strQuery = "DELETE FROM {$arrEachT["key"]} WHERE {$strWhere}";
                    $this->sql_ejecutar($strQuery);
                }
            }
        }

    }

    /*Funcion para debuguear un query*/
    public static function sql_queryDebug($sql, $boolShowQueryString = true, $arrFilter = false, $boolExplain = false, $objConnection = false){
        $t = new db_mysql();
        $boolFilter = is_array($arrFilter);
        if ($boolExplain)
            $sql = "EXPLAIN\n" . $sql;
        $qTMP = $t->sql_ejecutar($sql);
        ?>
        <div  style="position:relative; z-index:20; background-color:white; color:black;">
        <?php
        if ($boolShowQueryString)
            print_r("<hr>" . nl2br($sql) . "<br><br>");
        ?>
        <table border="1" cellspacing="0" cellpadding="2" align="center">
        <?php
        $boolFirstRow = true;
        $listFields = $t->sql_get_fields($qTMP);
        if ($rTMP = $t->sql_fetch_assoc($qTMP)) {
            do {
                if ($boolFirstRow) {
                    $strRow = "<tr>";
                    reset($listFields);
                    foreach ($listFields as $key => $entry) {
                        $strRow.="<th>{$key}</th>";
                    }
                    $strRow.= "</tr>\n";
                    echo $strRow;
                    $boolFirstRow = false;
                    reset($rTMP);
                }
                if ($boolFilter) {
                    $boolOK = true;
                    while ($arrFItem = each($arrFilter)) {
                        if ($rTMP[$arrFItem["key"]] != $arrFItem["value"])
                            $boolOK = false;
                    }
                    reset($arrFilter);
                    if (!$boolOK)
                        continue;
                }
                $strRow = "<tr>";
                reset($listFields);
                foreach ($listFields as $key => $entry) {
                    $strValue = $rTMP[$key];
                    if (strlen($rTMP[$key]) == 0) {
                        $strValue = "&nbsp;";
                    }
                    $strRow.="<td>{$strValue}</td>";
                }
                $strRow.= "</tr>\n";
                echo $strRow;
            }
            while($rTMP=$t->sql_fetch_assoc($qTMP));
        }
        ?>
        </table><br><?php print $t->sql_num_rows($qTMP); ?> rows<hr>
        </div>
        <?php
        $t->sql_free_result($qTMP);
    }

    /*Funcion para traer consultas por key
    * @param string $strSQL
    * @param boolean $boolFalseOnEmpty
    * @param boolean $boolForceArray
    */
    public function sql_ejecutarKey($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false){
        $return = false;
        $qList = $this->sql_ejecutar($strSQL . " LIMIT 0,1 ");
        $listFields = $this->sql_get_fields($qList);
        if ($rList = $this->sql_fetch_array($qList)) {
            if ($this->sql_num_fields($qList) == 1 && !$boolForceArray) {
                $return = $rList[0];
                if($boolFalseOnEmpty){
                    $strTMP = html_entity_decode($return);
                    $strTMP = strip_tags($strTMP);
                    $strTMP = str_replace(" ", "", $strTMP);
                    $strTMP = trim($strTMP);
                    $strTMP = str_replace(" ", "", $strTMP);
                    $strTMP = trim($strTMP);

                    if (empty($return) || empty($strTMP)) $return = false;
                }
            }
            else{
                $return = array();
                foreach ($listFields as $field) {
                    $return[$field['name']] = $rList[$field['name']];
                }
            }
        }
        $this->sql_free_result($qList);
        return $return;
    }

    public function getVentanas($strFilter = ""){
        $arrRetun = false;
        $sql = "SELECT * FROM menu WHERE 1 {$strFilter}";
        $stmp = $this->sql_ejecutar($sql);
        while($rtmp = $this->sql_fetch_assoc($stmp)){
            $arrRetun[$rtmp["modulo"]][$rtmp["menu_id"]] = $rtmp;
        }
        return $arrRetun;
    }

    public function getArrayMenuForUser(){
        $arrRet = false;
        $intUsuario = intval($_SESSION["wild"]["uid"]);
        if((!empty($_SESSION["wild"]["tipo"])) && $_SESSION["wild"]["tipo"] == "admin" ){
            $sql = "SELECT  Amenu.menu_id, Amenu.page, Amenu.nombre, Amenu.image, Amenu.modulo,
                            Amenu_categoria.id AS categoria, Amenu_categoria.nombre AS categoriaNombre,
                            Amenu_categoria.imagen AS imagenCatego
                    FROM menu AS Amenu
                        LEFT JOIN menu_categoria Amenu_categoria
                            ON Amenu_categoria.id = Amenu.categoria_id
                    WHERE   1";
        }
        else{
            $sql = "SELECT  Amenu.menu_id, Amenu.page, Amenu.nombre, Amenu.image, Amenu.modulo,
                            Amenu_categoria.id AS categoria, Amenu_categoria.nombre AS categoriaNombre,
                            Amenu_categoria.imagen AS imagenCatego
                    FROM    usuario Auser, usuario_acceso Auser_access, menu Amenu
                        LEFT JOIN menu_categoria Amenu_categoria
                            ON Amenu_categoria.id = Amenu.categoria_id
                    WHERE   Auser.userid = Auser_access.userid AND
                            Auser_access.menu_id = Amenu.menu_id AND
                            Auser.userid = {$intUsuario}";
        }
        $qtmp = $this->sql_ejecutar($sql);
        if($this->sql_num_rows($qtmp) >0){
            while($rtmp = $this->sql_fetch_assoc($qtmp)){
                if(!isset($arrRet[$rtmp["categoria"]]["modulo"])){
                    $arrRet[$rtmp["categoria"]]["modulo"] = $rtmp["categoriaNombre"];
                    $arrRet[$rtmp["categoria"]]["img"] = $rtmp["imagenCatego"];
                    $arrRet[$rtmp["categoria"]]["detalle"] = array();
                }

                $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["name"] = $rtmp["nombre"];
                $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["img"] = $rtmp["image"];
                $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["link"] = "page={$rtmp["page"]}&mod={$rtmp["modulo"]}";
            }
        }

        return $arrRet;
    }

    public function sql_getArray($strSQL, $inArray = false, $strKeyName="", $strValueName=""){
        $return = false;
        $qList = $this->sql_ejecutar($strSQL);
        $listFields = $this->sql_get_fields($qList);
        $strFieldName = ($strValueName!="")?$strValueName:0;
        $strKeyName = ($strKeyName != "")?$strKeyName:0;
        if($this->sql_num_rows($qList) == 0){
            $return = false;
        }
        elseif($this->sql_num_rows($qList) == 1){
            $rList = $this->sql_fetch_array($qList);
            if ($inArray)
                if ($strKeyName != "") {
                        $return = array($rList[$strKeyName] => $rList[$strFieldName]);
                }
                else {
                        $return = array($rList[$strFieldName]);
                }
            else {
                $return = $rList[$strFieldName];
            }
        }
        else{
            $rList = $this->sql_fetch_array($qList);
            $return = array();
            $boolFirst = true;
            do{
                if($strKeyName != ""){
                    $return[$rList[$strKeyName]] = $rList[$strFieldName];
                }
                else{
                    $return[] = $rList[$strFieldName];
                }
            }
            while($rList = $this->sql_fetch_array($qList));

        }
        $this->sql_free_result($qList);
        return $return;
    }

}