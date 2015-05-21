<?php
require_once("modulos/usuario/usuario_view.php");
require_once("modulos/usuario/windows/usuario_reporte/usuario_reporte_model.php");
require_once("modulos/usuario/windows/usuario_reporte/usuario_reporte_controller.php");

class usuario_reporte_view extends usuario_view {
    private $boolPrint;
    private $arrBotons = array();
    static $_instance;

    public function __construct($strAction) {
        parent::__construct($strAction);
    }

    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {}

    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance($strAction) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }

    public function getBoolPrint(){
        if(empty($this->boolPrint)) $this->boolPrint = false;
        return $this->boolPrint;
    }

    public function setBoolPrint($boolTMP){
        if(is_bool($boolTMP)) $this->boolPrint = $boolTMP;
    }

    public function drawPage() {
        if(!$this->getBoolPrint()){
            $this->getButtons("tblUserReport", $this->getArrBotones(),2);
        }

        $this->drawContenido();
        $this->getScripts();
    }
    public function getArrBotones() {
        $this->arrBotons[0] = array("title" => "Ver todos", "name" => "btnVer", "onclick" => "fntviewAll();");
        $this->arrBotons[1] = array("title" => "Imprimir", "name" => "btnImprimir", "onclick" => "fntPrint();");
        return $this->arrBotons;
    }
    public function getScripts(){
        ?>
        <script type="text/javascript">
            var xhr;
            function fntBuscar(){
                var data = $("#frmUserReport").serialize();
                var url = "<?php print "{$this->getStrAction()}&gBusqueda=true"; ?>";
                xhr=ajaxSendData(url, data, $("#ReportResult"), xhr);
            }
            function fntviewAll(){
                if($("#txt_searchNombreCompleto").length) $("#txt_searchNombreCompleto").val("");
                if($("#txt_searchNickname").length) $("#txt_searchNickname").val("");
                fntBuscar();
            }
            function fntPrint(){
                $("#frmUserReport").submit();
            }
            $(document).ready(function(){
                <?php
                if(!$this->getBoolPrint()){
                    ?>
                    fntviewAll();
                    <?php
                }
                ?>
                if($("#txt_searchNombreCompleto").length){
                    $("#txt_searchNombreCompleto").keyup(function(){
                        fntBuscar();
                    });
                }
                if($("#txt_searchNickname").length){
                    $("#txt_searchNickname").keyup(function(){
                        fntBuscar();
                    });
                }
            });
        </script>
        <?php
    }

    public function drawContenido(){
        $strAct = $this->getStrAction();
        $strAct .="&gBusqueda=true&ijs=true&print=true";
        $this->initForm("frmUserReport", true ,$strAct);
        ?>
        <table width="100%" cellspacing="0" cellpadding="0" border="0" >
            <?php
            if(!$this->getBoolPrint()){
                ?>
                <tr class="ui-widget-content">
                    <td width="10%" >
                        <b>Filtrar por:</b>
                    </td>
                    <td align="left" width="20%">
                        Nombre
                        <input type="text" name="txt_searchNombreCompleto" id="txt_searchNombreCompleto" value="" size="20" />
                    </td>
                    <td align="left" width="60%">
                        Nickname
                        <input type="text" name="txt_searchNickname" id="txt_searchNickname" value="" size="20"/>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="3">&nbsp;<br/></td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="ReportResult">

                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public function getResultReport($arr = false) {
        //usuario_reporte_controller::debugPHP($arr);
        ?>
        <table width="100%" cellspacing="0" cellpadding='0' border="0">
            <tr>
                <td class="ui-widget-header ui-corner-all" align="center">
                    Usuarios Registrados
                </td>
            </tr>
            <tr>
                <td>&nbsp<br/></td>
            </tr>
            <tr>
                <td>
                    <?php
                    if (is_array($arr) && count($arr) > 0) {
                        ?>
                        <table width="100%" cellspacing="0" cellpadding='0' border="0">
                            <tr>
                                <th class="ui-widget-content ui-corner-all" align="left">Nombre Completo</th>
                                <th class="ui-widget-content ui-corner-all" align="left">Usuario</th>
                                <th class="ui-widget-content ui-corner-all" align="left">Tipo</th>
                                <th class="ui-widget-content ui-corner-all" align="center">Activo</th>
                            </tr>
                            <?php
                            $strClase = "row1";
                            while ($arrT = each($arr)) {
                                ?>
                                <tr>
                                    <td class="<?php print $strClase; ?>" align="left"><?php print ($arrT["value"]["nombreCompleto"]) ? $arrT["value"]["nombreCompleto"] : "&nbsp;"; ?> </td>
                                    <td class="<?php print $strClase; ?>" align="left"><?php print ($arrT["value"]["nickname"]) ? $arrT["value"]["nickname"] : "&nbsp;"; ?> </td>
                                    <td class="<?php print $strClase; ?>" align="left"><?php print ($arrT["value"]["tipo"]) ? $arrT["value"]["tipo"] : "&nbsp;"; ?> </td>
                                    <td class="<?php print $strClase; ?>" align="center"><?php print ($arrT["value"]["active"]) ? $arrT["value"]["active"] : "&nbsp;"; ?> </td>
                                </tr>
                                <?php
                                $strClase = ($strClase == "row1") ? "row2" : "row1";
                            }
                            ?>
                        </table>
                        <?php
                    } else {
                        $this->fntAlerta("", "No se ha encontrado resultados para su busqueda");
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }
}