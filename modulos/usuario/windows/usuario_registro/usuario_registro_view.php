<?php
require_once("modulos/usuario/usuario_view.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_model.php");
require_once("modulos/usuario/windows/usuario_registro/usuario_registro_controller.php");

class usuario_registro_view extends usuario_view{
    static $_instance;
    private $arrBotons = array();
    public function __construct($strAction) {
        parent::__construct($strAction);
    }

    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {

    }

    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance($strAction) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }

    public function usuario_scripts(){
        ?>
        <script type="text/javascript">
            var xhr;

            function fntGrabar(){
                var allFields = new Array();
                allFields["txt_nickname"] = "Ingrese un Usuarios\n";
                if($("#hidd_userid").val() == 0){
                    allFields["txt_password"] = "Ingrese una Contraseña \n";
                }
                allFields["txt_nombres"] = "Ingrese un nombre \n";
                allFields["txt_apellidos"] = "Ingrese los apellidos \n";
                var bValid = checkForm(allFields, false);
                if(bValid){
                    var data = $("#frmUser").serialize();
                    var url = "<?php print "{$this->getStrAction()}&sUser=true" ?>";
                    ajaxJsonData(url, data, false, "fntNuevo()");
                }
            }

            function fntBuscar(){
                $.ajax({
                    type:"POST",
                    url: "<?php print $this->getStrAction(); ?>&gsUser=true",
                    success: function(data){
                        if($(".ui-dialog-titlebar").length)$(".ui-dialog-titlebar").show();
                        $("#divglobal_load").html(data);
                        $("#divglobal_load").dialog({
                            autoOpen:true,
                            show: "blind",
                            hide:"blind",
                            modal: true,
                            closeOnEscape: false,
                            resizable: true,
                            draggable : true,
                            width: 800,
                            height: 410,
                            maxHeight: 725,
                            position: { my: "center middle", at: "center middle", of: window },
                            close: function(){
                                $(this).html("");
                            },
                            buttons:{
                                "Cancelar": function(){
                                    CloseSearch($(this));
                                }
                            }
                        });
                        mostrarDatos();
                    },
                    error: function(){
                        fntBuscar();
                    }
                });
            }

            function CloseSearch(objDiv){
                objDiv.html("");
                objDiv.dialog("close");
            }

            function mostrarDatos(){
                var data = $("#frmBuscaUser").serialize();
                var url = "<?php print "{$this->getStrAction()}&gBusqueda=true"; ?>";
                ajaxSendData(url, data, $("#DivResult"));
            }

            function fntNuevo(){
                $(":input").val("").attr("checked","").show();
                $("#txt_contacto").val("");
                $("#tblWindow").html("");
                $("#div_pass").hide();
            }

            function fntObtener(arrItems, arrAccesos){
                $.each(arrItems, function(i, item){
                    var value = item.split("|");
                    if($("#txt_"+value[0]).length){
                        if(value[0] == "password"){
                            $("#div_pass").show();
                            $("#txt_"+value[0]).hide();
                        }
                        else{
                            $("#txt_"+value[0]).val(value[1])
                        }
                    }
                    if($("#hidd_"+value[0]).length){
                        $("#hidd_"+value[0]).val(value[1])
                    }
                    if($("#slt_"+value[0]).length){
                        $("#slt_"+value[0]).val(value[1])
                    }

                    if(value[0]=="tipo"){
                        $("#slt_tipo").change();
                    }
                    if(value[0]=="active"){
                        var strCheck = (value[1] == "Y")?"checked":false;
                        $("#chk_active").attr("checked",strCheck);
                    }
                });

                if(!arrAccesos) arrAccesos = false;
                if(arrAccesos){
                    $("#tblWindow").html("");
                    $.each(arrAccesos, function(key, value){
                        var strHTML = "<tr>\n\
                                        <td><input type='hidden' name='hddn_ventana_"+key+"' value='"+value["menuid"]+"'>\n\ "+
                                            value["nombre"]+
                                        "</td> \n\
                                    </tr>";
                        $("#tblWindow").append(strHTML);
                    });
                }

                var nombres = $("#txt_nombres").val();
                var apellidos = $("#txt_apellidos").val();
                $("#hddn_nombreCompleto").val(nombres + " " + apellidos);
            }

            $(function(){
               $("#txt_nombres").keyup(function(){
                   var nombres = $(this).val();
                   var apellidos = $("#txt_apellidos").val();
                   $("#hddn_nombreCompleto").val(nombres + " " + apellidos);
               });
               $("#txt_apellidos").keyup(function(){
                   var nombres = $("#txt_nombres").val();
                   var apellidos = $(this).val();
                   $("#hddn_nombreCompleto").val(nombres + " " + apellidos);
               });

               $("#chk_pass").click(function(){
                   if($(this).is(":checked")){
                       $("#txt_password").show();
                   }
                   else{
                       $("#txt_password").hide().val('');
                   }

               });
            });

            function fntAddWindow(){
                if($("#slt_ventanas").val() !=0){
                    var valueSel = $("#slt_ventanas").val();
                    var splitSel = valueSel.split("_");

                    //hago un fix de las filas
                    var i=0;
                    var boolOK = true;
                    $("input[name*=hddn_ventana_]").each(function(){
                        i++;
                        $(this).attr("name","hddn_ventana_"+i);
                        if(splitSel[0] == $(this).val()){
                            boolOK = false;
                        }
                    });
                    if(boolOK){
                        i++;
                        var strHTML = "<tr>\n\
                                            <td><input type='hidden' name='hddn_ventana_"+i+"' value='"+splitSel[0]+"'>\n\ "+
                                                splitSel[1]+
                                            "</td> \n\
                                        </tr>";
                        $("#tblWindow").append(strHTML);
                    }
                    else{
                        alert("Ya ha agregado la ventana.");
                    }
                }
            }

            function fntDelete(){

            }
        </script>
        <?php
    }

    public function drawPage(){
        $this->usuario_scripts();
        $this->getButtons("tblUser", $this->getArrBotones());
        $this->drawContenido();
    }

    public function getArrBotones(){
        $this->arrBotons[0] = array("title" => "Nuevo", "name" => "btnNuevo", "onclick" => "fntNuevo();");
        $this->arrBotons[1] = array("title" => "Grabar", "name" => "btnGrabar", "onclick" => "fntGrabar();");
        $this->arrBotons[3] = array("title" => "Buscar", "name" => "btnBuscar", "onclick" => "fntBuscar();");
        //$this->arrBotons[4] = array("title" => "Eliminar", "name" => "btnBuscar", "onclick" => "fntDelete();");
        return $this->arrBotons;
    }

    public function drawContenido(){
        $this->initForm("frmUser");
        ?>
        <input type="hidden" name="hidd_userid" id="hidd_userid" value="" />
        <table align="center" width="80%" cellpadding="1" cellspacing="3" border='0' class="ui-widget-content ui-corner-all">
            <tr>
                <td>
                    <b>Usuario</b><br/>
                    <input type="text" name="txt_nickname" id="txt_nickname" value="" />
                </td>
                <td>
                    <b>Contraseña</b><br/>
                    <div id="div_pass" style="display:none;">
                        <input type="checkbox" name="chk_pass" id="chk_pass" value="Y">
                        <span>Editar password</span>
                    </div>
                    <input type="password" name="txt_password" id="txt_password" value=""/>
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td>
                    <b>Nombres</b><br/>
                    <input type="text" name="txt_nombres" id="txt_nombres" value="" />
                </td>
                <td>
                    <b>Apellidos</b><br/>
                    <input type="text" name="txt_apellidos" id="txt_apellidos" value="" />
                    <input type="hidden" name="hddn_nombreCompleto" id="hddn_nombreCompleto" value="">
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <b>Tipo</b>
                    <select name="slt_tipo" id="slt_tipo">
                        <option value=""> </option>
                        <option value="cajero">Vendedor</option>
                        <option value="inventario">Inventario</option>
                        <option value="admin">Admin</option>
                    </select>
                </td>
                <td>
                    <b>Activo</b><br>
                    <input type="checkbox" name="chk_active" id="chk_active" value="1" />
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;<br/></td></tr>
            <tr>
                <td colspan="2" class="ui-corner-all ui-state-highlight" style="font-size:18;" align="left">
                    Tiene permiso para las ventanas :
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php
                    $objC = new usuario_registro_controller("");
                    $arrVentana = $objC->getVentanas();
                    ?>
                    <select id="slt_ventanas" name="slt_ventanas">
                        <option value="0">Seleccione Uno</option>
                        <?php
                        if(is_array($arrVentana) && (count($arrVentana) > 0)){
                            while($arrt = each($arrVentana)){
                                ?>
                                <optgroup><?php print $arrt["key"]; ?></optgroup>
                                <?php
                                if(is_array($arrt["value"]) && (count($arrt["value"]) > 0)){
                                    while($arrt2 = each($arrt["value"])){
                                        ?>
                                        <option value="<?php print $arrt2["key"]; ?>_<?php print $arrt2["value"]["nombre"]; ?>"><?php print $arrt2["value"]["nombre"] ?></option>
                                        <?php
                                        unset($arrt2);
                                    }
                                }
                                unset($arrt);
                            }
                        }
                        ?>
                    </select>
                    <img src="images/add.png" alt="emblem-cvs-added" style="cursor: pointer;" onclick="fntAddWindow();"/>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table id="tblWindow" width="100%" cellspacing="0" cellpadding="0" border="0">

                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br/></td>
            </tr>
        </table>
        <?php
        $this->finForm();
    }

    public function getBuscar(){
        ?>
        <script type="text/javascript">
            $(function(){
                $("#txt_searchNickname").keyup(function(){ mostrarDatos(); });
                $("#txt_searchNombreCompleto").keyup(function(){ mostrarDatos(); });
            });
        </script>
        <div id="divBuscaUser" class="floatLeft" title="Buscar Usuario">
            <form action="<?php print $this->getStrAction(); ?>" name="frmBuscaUser" id="frmBuscaUser" method="post">
                <input type="hidden" name="hdBuscarUser" id="hidBuscarUser" value="N">
                <table width="100%" cellpading="0" cellspacing="0" border="0">
                    <tr>
                        <td align="right" width="50%">
                            Nickname
                            <input type="text" name="txt_searchNickname" id="txt_searchNickname" value="" size="20"/>
                        </td>
                        <td align="left" width="50%">
                            Nombre
                            <input type="text" name="txt_searchNombreCompleto" id="txt_searchNombreCompleto" value="" size="20" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" cellpading="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <div id="DivResult" style="overflow-x: hidden; height: 250px;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    public function getResult($arrGet = false){
        if(is_array($arrGet) && (count($arrGet) >0)){
            $rowClass="row1";
            while($arrE = each($arrGet)){
                ?>
                <script type="text/javascript">
                    $(function(){
                       $("#rowS_<?php print $arrE["key"]; ?>").click(function(){
                            var itemsarray = [];
                            var tempo = "userid|<?php print (!empty($arrE["value"]["userid"]))?$arrE["value"]["userid"]:0; ?>";
                            itemsarray.push(tempo);
                            tempo = "nickname|<?php print (!empty($arrE["value"]["nickname"]))?$arrE["value"]["nickname"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "password|<?php print (!empty($arrE["value"]["password"]))?$arrE["value"]["password"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "tipo|<?php print (!empty($arrE["value"]["tipo"]))?$arrE["value"]["tipo"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "nombres|<?php print (!empty($arrE["value"]["nombres"]))?$arrE["value"]["nombres"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "apellidos|<?php print (!empty($arrE["value"]["apellidos"]))?$arrE["value"]["apellidos"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "nombreCompleto|<?php print (!empty($arrE["value"]["nombreCompleto"]))?$arrE["value"]["nombreCompleto"]:""; ?>";
                            itemsarray.push(tempo);
                            tempo = "active|<?php print (!empty($arrE["value"]["active"]))?$arrE["value"]["active"]:""; ?>";
                            itemsarray.push(tempo);

                            var itemAccess = [];
                            <?php
                            if(isset($arrE["value"]["accesos"])){
                                if(is_array($arrE["value"]["accesos"])){
                                    while($arrE2 = each($arrE["value"]["accesos"])){
                                        ?>
                                        var arrTempo = {
                                            "menuid":"<?php print $arrE2["value"]["menu_id"]; ?>",
                                            "nombre":"<?php print $arrE2["value"]["nombre"]; ?>",
                                        }
                                        itemAccess.push(arrTempo);
                                        <?php
                                    }
                                }
                            }

                            ?>
                            fntObtener(itemsarray, itemAccess);
                            CloseSearch($("#divglobal_load"));
                       });
                    });
                </script>
                <div id="rowS_<?php print $arrE["key"]; ?>" class="floatLeft <?php print $rowClass; ?>" onmouseout="$(this).removeClass('ui-state-highlight').addClass('');"
                        onmouseover="$(this).removeClass('').addClass('ui-state-highlight');" style="cursor:pointer">
                    <div class="floatLeft" style="width:30%">
                        <?php print $arrE["value"]["nickname"]; ?>
                    </div>
                    <div class="floatLeft" style="width:50%">
                        <?php print $arrE["value"]["nombreCompleto"]; ?>
                    </div>
                </div>
                <?php
                $rowClass = ($rowClass =="row1")?"row2":"row1";
                unset($arrE);
            }
        }
        else{
            ?>
            <div class="floatLeft">
                <?php
                $this->fntAlerta("Alerta!!!", "No se han encontrado resultados para su busqueda");
                ?>
            </div>
            <?php
        }
    }
}