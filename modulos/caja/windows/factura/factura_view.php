<?php
require_once("modulos/caja/caja_view.php");

class factura_view extends caja_view{
    static $_instance;
    private $objController;
    
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
    public function drawPage(){
        $this->get_javascript();
        $this->getButtons("ButtonsFacturacion", $this->getArrBotones(),5);
        $this->drawContenido();        
    }
    public function getArrBotones(){
        $this->arrBotons[0] = array("title" => "Nuevo", "name" => "btnNuevo", "onclick" => "nuevo()");
        $this->arrBotons[1] = array("title" => "Grabar", "name" => "btnGrabar", "onclick" => "grabar();");
        $this->arrBotons[3] = array("title" => "Buscar", "name" => "btnBuscar", "onclick" => "buscar()");
        $this->arrBotons[4] = array("title" => "Eliminar", "name" => "btnEliminar", "onclick" => "eliminar()");
        return $this->arrBotons;
    }   
    public function get_javascript(){
        ?>
        <script type="text/javascript">
            var myWidget = new drawWidgets();
            function nuevo(){
                $(":input").val("").attr("checked","").show();
                $("#frm_marcas_name")
                        .data({
                            "isNew":1,
                            "id":0
                        });
            }
            function grabar(){
                var objMarca = $("#frm_marcas_name");
                
                if(objMarca.val() !== ""){
                    var isnew = objMarca.data("isNew");
                    var link = "<?php print $this->getStrAction(); ?>"
                    if(isnew)
                        link += "&op=new";                    
                    else
                        link += "&op=update";
                    
                    var params = {
                        id: objMarca.data("id")+" ",
                        data: objMarca.val()
                    };                                        
                    
                    link += serializeObj(params);
                    
                    $.get(link,function(data){
                        if(isnew){
                            if(data.NewResult === "true"){
                                myWidget.alertDialog("Marca agregada exitosamente");
                            }
                            else{
                                myWidget.alertDialog("La marca ya ha sido registrada, intente ingresando una nueva marca");
                            }
                            
                        }
                        else{
                            if(data.UpdateResult === "true"){
                                myWidget.alertDialog("La marca fue actualizada");
                            }
                            else{
                                myWidget.alertDialog(data.UpdateResult);
                            }                            
                        }
                    })
                    .fail(function() {
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    });                
                }
                else{
                    myWidget.alertDialog("Ingrese un texto para guardar información")
                }
            }
            function eliminar(){
                var link = "<?php print $this->getStrAction(); ?>&op=delete"
                var objMarca = $("#frm_marcas_name");
                if(objMarca.val() !== ""){
                    var params = {
                        id: objMarca.data("id")+" ",
                        data: objMarca.val()
                    };
                    link += serializeObj(params);
                    $.get(link,function(data){
                        if(data.DeleteResult === "true"){
                            myWidget.alertDialog("Marca eliminada");
                        }
                        else{
                            myWidget.alertDialog("No se puede eliminar el registro")
                        }
                        
                    })
                    .fail(function() {
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    }); 
                }
                else{
                    myWidget.alertDialog("Seleccione una marca para poder eliminarla")
                }
                
            }
            function CloseSearch(objDiv){
                objDiv.html("");
                objDiv.dialog("close");
            }
            function buscar(){
                $("#divglobal_load").dialog({
                    autoOpen:false,
                    show: "blind",
                    hide:"blind",
                    modal: true,
                    closeOnEscape: false,
                    resizable: true,
                    draggable : true,
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
                
                var link = "<?php print $this->getStrAction(); ?>&op=get"
                $.get(link,function(data){
                    myWidget.openLoading();
                })
                .done(function(data){                                        
                    myWidget.closeLoading();
                    table = $("<table width='100%'></table>")
                            .addClass("table table-striped table-bordered table-hover dataTable no-footer");
                    var tr = $("<tr role='row'></tr>");
                    var td = $("<td width='5%'></td>")
                            .html("<b>Correlativo</b>")    
                    tr.append(td);
                    var td = $("<td width='40%'></td>")
                            .html("<b>Descripción</b>")    
                    tr.append(td);
                    var td = $("<td width='25%'></td>")
                            .html("<b>Estado</b>")    
                    tr.append(td);
                    table.append(tr);
                    var classtd = "gradeA odd";
                    $.each(data.GetJsonResult, function(key,value){
                        if(value.IsActive == true){
                            var tr = $("<tr></tr>");
                            var td = $("<td></td>")
                                    .html(value.IdTradeMark)
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.Name)
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.IsActive)
                            
                            tr.append(td)
                                .addClass(classtd)
                                .css({"cursor":"pointer"})
                                .click(function(){
                                    seleccionar(value);
                                    CloseSearch($("#divglobal_load"));
                                });
                                
                            table.append(tr);
                            classtd = (classtd == "gradeA odd")?"gradeA even":"gradeA odd";
                        }
                    });
                    $("#divglobal_load").html(table);
                    $("#divglobal_load").dialog("open");
                })
                .fail(function() {
                    myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                });;
            }
            
            function seleccionar(data){
                $("#frm_marcas_name").val(data.Name);
                $("#frm_marcas_name").data({
                    "isNew":0,
                    "id" : data.IdTradeMark
                });
            }
            
            $(function(){
                $("#frm_marcas_name")
                .data({
                    "isNew":1,
                    "id":0
                });
            })
        </script>
        <?php       
    }
    
    public function drawContenido(){
        $strForm = "frmPedido";
        $this->initForm($strForm);
        $strDate = date("Y-m-d");
        $strFormatDate = factura_controller::formatDate($strDate);
        ?>
        <div id="content-factura">
            <a onclick="fntGetPage('page=factura&mod=caja');">Recargar ventana</a>
            <div class="panel panel-primary">
                <div class="panel-heading">Factura</div>
                <div class="panel-body">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha</label>
                                </div>                            
                            </div>
                            <div class="col-md-8">
                                <?php print $strFormatDate ?>
                                <input type="hidden" name="<?php print $strForm ?>_fecha" id="<?php print $strForm ?>_fecha" value="<?php print $strDate ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cliente</label>
                                </div>                            
                            </div>
                            <div class="col-md-8">
                                <button type="button" class="btn btn-info btn-circle"><i class="fa fa-search"></i>
                            </button>
                                <input type="hidden" name="<?php print $strForm ?>_idcliente" id="<?php print $strForm ?>_idcliente" value="">
                                <span id='label_cliente' class= '' style='display:none;'></span><br>
                            </div>
                        </div>
                    </div>                                        
                </div>
            </div>
            <br/>
            <div id="td-pedido-contenido" class="row">
                <div id="td-pedido-contenido-solicitud" class="col-md-4">
                    
                </div>
                <div id="td-pedido-contenido-productos" class="col-md-8">
                    
                </div>
            </div>
            <script type="text/javascript">
            var pedido = function(customSettings){
                var self = this;
                var intPedido = <?php print $intPedidoID ?>;
                var arrProducto = "";
                var objTableAgregados = "";
                var objTdTotal = "";
                var objTdDescuento = "";
                var arrProductosGlobal = "";
                var strForm = "<?php print $strForm ?>";

                var globalAccion = new getstrLink();
                var ObjWidgets = new drawWidgets();
                var objXHR;
                var objTimeRepeat;
                var boolTimeRecently = false;

                var objProductos;
                var objSolicitud;
                var defaults = {
                    idCliente : 0,
                    objProductos : "",
                    objSolicitud : ""
                }

                customSettings || ( customSettings = {} );
                var settings = $.extend({}, defaults, customSettings);

                objProductos = (settings.objProductos.length == 0)?$("<div></div>"):$("#"+settings.objProductos);
                objSolicitud = (settings.objSolicitud.length == 0)?$("<div></div>"):$("#"+settings.objSolicitud);

                function getstrLink(){
                    this.link = "<?php print $this->getStrAction() ?>&op=true";
                }

                this.getInterfaceProductos = function(){
                    //alert("here")
                    objProductos.html("");

                    objTable = $("<table id ='table-grid-productos'></table>").attr("width","100%")
                                .css({ "border":"1px solid black"}).addClass("ui-corner-all");
                    objProductos.append(objTable);
                    objTr = $("<tr></tr>");
                    objTd = $("<td></td>").html("<b style='font-size:10pt;'>Productos</b>")
                                          .attr("align","center")
                                          .addClass("ui-widget-header ui-corner-all");
                    objTr.append(objTd);
                    objTable.append(objTr);
                    objTr = $("<tr></tr>");
                    objTd =$("<td></td>");
                    objTr.append(objTd);
                    objTable.append(objTr);

                    objTrGrid = $("<tr></tr>");
                    objTdGrid =$("<td></td>").html("").attr("valign","top")
                                .css({"height": "360px", "vertical-align":"top"});
                    objTrGrid.append(objTdGrid);
                    objTable.append(objTrGrid);

                    var inputValue = "";
                    inputSearch = $("<input>")
                                    .attr({
                                        "placeholder":"Buscar producto",
                                        id: strForm+"_buscarProducto",
                                        name:strForm+"_buscarProducto",
                                        size:"40"
                                    })
                                    .keyup(function(){
                                        if(inputValue != $(this).val()){
                                            inputValue = $(this).val();
                                            objTdGrid.html("");
                                            getProductos(inputSearch, objTdGrid);
                                            boolTimeRecently = true;
                                            //setTimeout(function(){
                                                boolTimeRecently = false
                                            //}, 150000);
                                        }
                                        inputSearch.focus();
                                    });
                    objTd.append(inputSearch);

                    getProductos(inputSearch, objTdGrid);
                    //objTimeRepeat = setInterval(function(){
                        //if(!boolTimeRecently){
                            getProductos(inputSearch, objTdGrid);
                        //}
                    //},10000);
                }

                var getProductos = function(inputSearch, objInterface){
                    objInterface.hide("slow").html("");
                    if(!inputSearch) inputSearch = $("<input>");
                    var data = "&term="+inputSearch.val();
                    var strLink = globalAccion.link+"&gProductos=true";

                    if(objXHR) objXHR.abort();
                    objXHR = $.ajax({
                        type:"POST",
                        data: data,
                        url: strLink,
                        beforeSend : function(){

                        },
                        success: function(data){
                            if(typeof arrProductosGlobal != "undefined"){
                                if(arrProductosGlobal.length == 0) arrProductosGlobal = data.detalle;    
                            }                            
                            drawGrid(data, objInterface);
                            objInterface.show("slow");
                            objXHR = null;
                        },
                        error: function(){

                        }
                    });

                    //objXHR
                }

                var drawGrid = function(arrData, objInterface) {
                    objInterface.html("");
                    objInterface.append("<br/><br/>");
                    objTable = $("<table id ='table-grid'></table>").attr({"width":"100%", "valign":"top"})
                                .css({"min-height": "156px", "vertical-align":"top"});
                    objInterface.html(objTable);

                    objTr = $("<tr></tr>");
                    objTable.append(objTr);
                    if(typeof arrData != "undefined"){
                        if(arrData.status =="ok"){
                            var i=0;
                            var j=1;
                            var intMaximoVista = 9;
                            var intMaximoXFila = 3;
                            $.each(arrData.detalle, function(key, value){
                                if(j <= intMaximoVista){
                                    if(i == intMaximoXFila){
                                        i=0;
                                        objTr = $("<tr></tr>");
                                        objTable.append(objTr);
                                    }

                                    intPrecio = (value.ofertado =="Y")? value.precio_oferta : value.precio_venta;

                                    objTd = $("<td></td>")
                                            .attr({ "align":"center", "width":"33%" })
                                            .css({ "border":"1px solid black", "cursor":"pointer" })
                                            .hover(function(){
                                                    $(this).addClass("ui-state-focus")
                                                           .css({
                                                                "color":"white"
                                                            })
                                                },
                                                function(){
                                                    $(this).removeClass("ui-state-focus")
                                                            .css({
                                                                "color":"black"
                                                            })
                                                }
                                            )
                                            .append("<b>"+value.codigo+"</b><br>")
                                            .append("<b class='ui-corner-all ui-state-highlight' >"+value.nombre+"</b><br>")
                                            .append("<b >"+value.etiqueta+"</b><br>")
                                            .click(function(){
                                                arrProducto = value;
                                                addProducto(value);
                                            })
                                            .addClass("ui-corner-all");
                                    if(typeof value.presenta != "undefined"){
                                        var objPresenta = $("<div style='background-color:#DDDDDD'>Disponible:<br></div>")
                                        $.each(value.presenta, function(ikey, item){

                                            objPresenta.append("<b>"+item.disponibles +" " + item.descripcion+": Q."+item.precio_venta+"</b><br>")

                                        });
                                        objTd.append(objPresenta);
                                    }
                                    objTr.append(objTd);
                                }
                                i++;
                                j++;
                            })
                        }
                        else{
                            objTd = $("<td></td>")
                                    .attr({ "align":"center", "valign":"top"})
                                    .css({ "border":"1px solid black", "cursor":"pointer","min-height": "156px" })
                                    .hover(function(){
                                            $(this).addClass("ui-state-focus")
                                        },
                                        function(){
                                            $(this).removeClass("ui-state-focus")
                                        }
                                    )
                                    .html("<b>"+ arrData.msj +"</b>")
                            objTr.append(objTd);
                        }
                    }

                }

                this.getInterfaceSolicitud = function(){
                    objSolicitud.html("").attr("valign","top").css({"vertical-align":"top", "border":"1px solid black"}).addClass("ui-corner-all ui-widget-content");
                    objTable = $("<table id ='table-solicitud-productos'></table>").attr("width","100%");
                    objSolicitud.append(objTable);
                    objTd = $("<td></td>").html("<b style='font-size:10pt;'>Productos agregados</b>")
                                          .attr("align","center")
                                          .addClass("ui-widget-header ui-corner-all");
                    objTr = $("<tr></tr>");
                    objTr.append(objTd);
                    objTable.append(objTr);

                    objTr = $("<tr></tr>");
                    objTd = $("<td></td>")
                                    .css({"height":"100px"})
                    objTableAgregados = $("<table id ='table-solicitud-agregados'></table>").attr("width","100%");
                    objTd.append(objTableAgregados)
                    objTr.append(objTd)
                    objTable.append(objTr);

                    objTr = $("<tr></tr>");
                    objTd = $("<td></td>").html("<b>Cantidad</b>");
                    objTr.append(objTd)
                    objTableAgregados.append(objTr);

                    objTd = $("<td></td>").html("<b>Presentacion</b>");
                    objTr.append(objTd)

                    objTd = $("<td></td>").html("<b>Producto</b>");
                    objTr.append(objTd)

                    objTd = $("<td></td>").html("<b>Precio</b>");
                    objTr.append(objTd)

                    objTr = $("<tr></tr>");

                    var inputTotal = $("<input></input>")
                                    .attr({
                                        "type":"hidden",
                                        "name":strForm+"_total",
                                        "id":strForm+"_total",
                                        "value":""
                                    });
                    objTdTotal = $("<span></span>")
                                .html("<b>Total: </b>");

                    var inputDescuento = $("<input/>")
                                    .attr({
                                        "type":"hidden",
                                        "id": strForm+"_descuento",
                                        "name":strForm+"_descuento",
                                        "value":"0"
                                    });
                    objTdDescuento = $("<span class='info'></span>")
                                .html("<b>Descuento: </b>");

                    objTd = $("<td></td>")
                                .append(objTdDescuento)
                                .append(inputDescuento)
                                .append("<br>")
                                .append(objTdTotal)
                                .append(inputTotal)
                    objTr.append(objTd)
                    objTable.append(objTr);

                    objTr = $("<tr></tr>");
                    objTd = $("<td></td>").attr("align","center")
                    objTr.append(objTd)
                    objTable.append(objTr);

                    var btnPedido = $('<input type="button" />')
                            .attr({"id":"butonPedido", "class":"button"})
                            .val("Facturar")
                            .click( function(){
                                fntHacerPedido();
                            })
                    var btnClearPedido = $("<input type='button' />")
                                        .attr({"id":"btnClearPedido","class":"button"})
                                        .val("Limpiar pedido")
                                        .click(function(){
                                            $("[id*=imgDelete_]").each(function(){
                                                $(this).click()
                                            })
                                            inputDescuento.val("");
                                            inputTotal.val("");
                                            objTdTotal.html("<span class='info'>Total: <p class='ui-state-highlight info' style='font-size:14pt;'> Q. 0.00</p></span>");
                                            objTdDescuento.html("<span class='info'>Descuento: Q 0.00</span>");
                                        })

                    var btnDescuentoGral = $("<input type='button' />")
                                            .attr({ "id":"btnDescGral","class":"button"})
                                            .val("Descuento")
                                            .click(function(){
                                                var ObjInputs=({
                                                    "html": "Agregar % de descuento" ,
                                                    "inputs":{
                                                        txtAddDescuento:{
                                                            "attrs":{
                                                                "value":"",
                                                                "type":"text",
                                                                "placeholder":"eje: 2, 3, etc."
                                                            }
                                                        }
                                                    }
                                                })
                                                ObjWidgets.promptDialog(ObjInputs, "Agregar descuento", addDescuento, true);
                                            })
                    objTd.append(btnPedido)
                         .append("&nbsp;&nbsp;&nbsp;&nbsp;")
                         .append(btnClearPedido)
                         .append("&nbsp;&nbsp;&nbsp;&nbsp;")
                         .append(btnDescuentoGral)
                }

                var addDescuento = function(){
                    var sinDesc = $("#txtAddDescuento").val();
                    if(validar_entero(sinDesc)){
                        sinDesc = validar_entero(sinDesc) *1;
                        var sinTotal = ($("#"+strForm+"_total").val() *1);
                        var sinDescuento = ($("#"+strForm+"_descuento").val() *1);
                        sinTotal = (sinTotal >= 0)?sinTotal:0;
                        var pctDesc = (sinTotal * sinDesc)/100;
                        sinTotal = sinTotal - pctDesc;
                        var sinMeroDesc =  format_number((sinDescuento + pctDesc),2);

                        $("#"+strForm+"_descuento").val( sinMeroDesc );
                        $("#"+strForm+"_total").val(sinTotal);
                        objTdTotal.html("<span class='info'>Total: <p class='ui-state-highlight info' style='font-size:14pt;'> Q. "+format_number(sinTotal,2)+"</p></span>");
                        objTdDescuento.html("<span class='info'>Descuento: Q "+format_number(sinMeroDesc,2)+"</span>");
                        return true;
                    }
                    else{
                        var strMSj ="El valor debe ser numérico o mayor que cero";
                        jQuery.noticeAdd({
                            text: strMSj , type:"error", stay:false
                        });
                        return false;
                    }
                }

                var fntHacerPedido = function(){
                    var strError = "";
                    var boolconsumidoF = false;
                    if($("#frmPedido_idcliente").val().length <= 0){
                        boolconsumidoF = true;
                    }                                                   
                    
                    if($("[name*=frmPedido_agregado_cantidad_]").length) strError = "";
                    else strError = "Al menos agregue un producto a su pedido";
                    
                    var boolContinuar = true;
                    if(strError != "") boolContinuar = false;
                    if(boolconsumidoF){
                        boolContinuar = confirm("¿Crear el pedido a consumidor final?")
                    }

                    if(boolContinuar){
                        var arrpar = {
                            strParams : $("#"+strForm).serialize(),
                            strUrl : globalAccion.link+"&sPedido=true",
                            strDataTypeAjax : "json"
                        }
                        arrdata = new fntSendData(arrpar);
                        if(typeof arrdata.status != "undefined"){
                            if(arrdata.status == "ok"){
                                self.getInterfaceProductos();
                                self.getInterfaceSolicitud();

                                $("#"+strForm+"_idcliente").val(0);
                                $("#label_cliente").html("").show();
                                $("#edit_cliente").html("").show();
                                fntGetPage('page=ingresar_pedido&mod=pedido');
                            }
                        }
                    }
                    else{
                        if(strError.length){
                            jQuery.noticeAdd({
                                text: strError , type:"error", stay:false
                            });
                        }
                    }
                }

                var addDetail = function(){
                    var i =0;
                    var boolOK = true;
                    var intAgregados = 0;
                    $("input[name*="+strForm+"_agregado_producto_]").each(function(){
                        i++;
                        var arrName = $(this).attr("name").split("_");
                        if(arrProducto.idproducto == $(this).val()){
                            intAgregados = (intAgregados + ($("input[name*="+strForm+"_agregado_cantidadReal_"+arrName[3]+"]").val() *1));
                        }
                    });

                    //info compra de producto
                    var intCantidad = $("#txtCantidad").val();
                    intCantidad = (validar_entero(intCantidad) *1);
                    var sinPrecioVenta = $("input[name=txtPresenta]:checked").attr("precio");
                    var sinDescuento = $("#txtDescuento").val();
                    sinDescuento = (validar_entero(sinDescuento) *1);
                    var strEtiqueta = $("input[name=txtPresenta]:checked").attr("etiqueta");

                    //para las presentaciones
                    var intPresentacion = $("input[name=txtPresenta]:checked").val();
                    intPresentacion = (intPresentacion*1);
                    var DatosPresentacion = arrProducto.presenta[intPresentacion];
                    var intCantidadReal = (intCantidad * DatosPresentacion.totalUnidades);
                    var boolMayorCanti = true;
                    var intCantidadTotalAdd = (intCantidadReal+intAgregados);

                    if(intCantidadTotalAdd > (arrProducto.disponibles)) boolMayorCanti = false;

                    if(boolOK && boolMayorCanti && (intCantidad >0) && (intPresentacion>0) && (sinPrecioVenta > 0) ){
                        objTr = $("<tr id='tr-add-"+i+"'></tr>")
                        objTableAgregados.append(objTr);
                        var inputCantidad = $("<input/>")
                                    .attr({
                                        "type":"hidden",
                                        "id":strForm+"_agregado_cantidad_"+i,
                                        "name":strForm+"_agregado_cantidad_"+i,
                                        "value": intCantidad
                                    })

                        var inputCantidadReal = $("<input/>")
                                                .attr({
                                                    "type":"hidden",
                                                    "id":strForm+"_agregado_cantidadReal_"+i,
                                                    "name":strForm+"_agregado_cantidadReal_"+i,
                                                    "value": intCantidadReal
                                                });
                                                
                        var inputCodigoLote = $("<input/>")
                                                .attr({
                                                    "type":"hidden",
                                                    "id": strForm+"_agregado_codigoLote_"+i,
                                                    "name": strForm+"_agregado_codigoLote_"+i,
                                                    "value": arrProducto.codigo_lote,
                                                })

                        var objSpanCantidad = $("<span id='span"+ i+"'></span>")
                                            .html(intCantidad);

                        var precio = (arrProducto.ofertado == "Y")?arrProducto.precio_oferta:arrProducto.precio_venta;
                        var precio = (sinPrecioVenta*1)

                        var sinTotal = (intCantidad * precio);

                        var inputPrecio = $("<input/>")
                                            .attr({
                                                "type":"hidden",
                                                "id":strForm+"_agregado_precio_"+i,
                                                "name":strForm+"_agregado_precio_"+i,
                                                "value": sinTotal
                                            })

                        var imgMenos = $("<img/>")
                                    .attr({
                                        "src":"images/go-bottom.png",
                                        "width":"20px",
                                        "height":"20px"
                                    })
                                    .css({
                                        "cursor":"pointer"
                                    })
                                    .click(function(){
                                        var intTMP = (inputCantidad.val() * 1);
                                        if(intTMP > 0){
                                            intTMP -= 1;
                                            objSpanCantidad.html(intTMP);
                                            inputCantidad.val(intTMP);

                                            var sinTotal = (intTMP * precio);

                                            inputPrecio.val(sinTotal);
                                            $("#td-precio-total-"+i).html(format_number(sinTotal,2))
                                            calcularTotal();
                                        }
                                        else{
                                            $("#tr-add-"+i).remove();
                                            calcularTotal();
                                        }
                                    })
                        var imgMas = $("<img/>")
                                    .attr({
                                        "src":"images/go-top.png",
                                        "width":"20px",
                                        "height":"20px"
                                    })
                                    .css({
                                        "cursor":"pointer"
                                    })
                                    .click(function(){
                                        var intTMP = (inputCantidad.val() * 1);

                                        if(intTMP > 0){
                                            if(intTMP < arrProducto.disponibles){
                                                intTMP += 1;
                                                var sinTotal = (intTMP * precio);
                                                inputPrecio.val(sinTotal);
                                                $("#td-precio-total-"+i).html(format_number(sinTotal,2))
                                                calcularTotal();
                                            }
                                            else{
                                                jQuery.noticeAdd({
                                                    text: "Ya no hay mas productos disponibles" , type:"error", stay:false
                                                });
                                            }
                                        }
                                        objSpanCantidad.html(intTMP);
                                        inputCantidad.val(intTMP);
                                    })

                        objTd = $("<td></td>")
                                //.append(imgMenos)
                                .append( objSpanCantidad)
                                .append(inputCantidad)
                                .append(inputCantidadReal)
                                .append(inputCodigoLote)
                                //.append(imgMas);
                        objTr.append(objTd);

                        var inputPresentacion = $("<input/>")
                                                .attr({
                                                    type:"hidden",
                                                    id: strForm+"_agregado_presentacion_"+i,
                                                    name: strForm+"_agregado_presentacion_"+i,
                                                    value: DatosPresentacion.id
                                                })
                        objTd = $("<td></td>")
                                .append(DatosPresentacion.descripcion )
                                .append(inputPresentacion)
                        objTr.append(objTd);


                        inputProducto = $("<input/>")
                                    .attr({
                                        "type":"hidden",
                                        "id":strForm+"_agregado_producto_"+i,
                                        "name":strForm+"_agregado_producto_"+i,
                                        "value":arrProducto.idproducto
                                    })
                        objTd = $("<td></td>")
                                .append(arrProducto.nombre + " - " +strEtiqueta)
                                .append(inputProducto);
                        objTr.append(objTd);


                        var spanPrecio = $("<span id='td-precio-total-"+i+"'></span>")
                                        .html(format_number(sinTotal,2))

                        var sinTotalDescuento = ((sinDescuento * sinTotal)/100);

                        var sinMeroDesc = $("#"+strForm+"_descuento").val();
                        sinMeroDesc = (sinMeroDesc*1);

                        sinTotalDescuento = sinMeroDesc+ sinTotalDescuento;
                        sinTotalDescuento = format_number(sinTotalDescuento,2);
                        $("input[name="+strForm+"_descuento]").val(sinTotalDescuento);

                        objTd = $("<td ></td>")
                                .append(spanPrecio)
                                .append(inputPrecio);
                        objTr.append(objTd);

                        imgDelete= $("<img>")
                                    .attr({
                                        "id":"imgDelete_"+i,
                                        "src":"images/dialog-error.png",
                                        "width":15,
                                        "height":15
                                    })
                                    .css({
                                        "cursor":"pointer"
                                    })
                                    .click(function(){
                                        $("#tr-add-"+i).remove();
                                        calcularTotal();
                                    });
                        objTd = $("<td></td>")
                            .append(imgDelete);
                        objTr.append(objTd);

                        calcularTotal();
                        return true
                    }
                    else{
                        if(!boolOK){
                            var strMSj ="El producto ya ha sido agregado";
                            jQuery.noticeAdd({
                                text: strMSj , type:"error", stay:false
                            });
                            return false;
                        }
                        else if(!boolMayorCanti){
                             strMSj = "El numero debe ser menor o igual a la cantidad disponibles de productos";
                             jQuery.noticeAdd({
                                text: strMSj , type:"error", stay:false
                            });
                             return false;
                        }
                        else{
                            var strMSj ="El valor debe ser numérico o mayor que cero";
                            jQuery.noticeAdd({
                                text: strMSj , type:"error", stay:false
                            });
                            return false;
                        }
                    }
                }

                var calcularTotal = function(){
                    var sinTotalon =0;
                    $("input[name*="+strForm+"_agregado_precio_]").each(function(){
                        sinTotalon += ($(this).val() *1);
                    });
                    var sinDescuento = $("input[name="+strForm+"_descuento]").val();
                    sinDescuento = (sinDescuento*1);
                    sinTotalon = (sinTotalon - sinDescuento);
                    objTdTotal.html("<span class='info'>Total: <p class='ui-state-highlight info' style='font-size:14pt;'> Q. "+format_number(sinTotalon,2)+"</p></span>");
                    objTdDescuento.html("<span class='info'>Descuento: Q "+sinDescuento+"</span>");

                    if($("#"+strForm+"_total") ) $("#"+strForm+"_total").val(format_number_scomas(sinTotalon,2));
                }

                var addProducto = function(datos){
                    if(typeof datos.presenta != "undefined"){
                        var strHTML = "<b>Precios:</b>\n\
                                      <ul class='ui-corner-all ui-widget-content' >";
                        var strClass = "row1";
                        $.each(datos.presenta, function(key, value){
                            if(typeof(value.presenta) === 'object'){
                                $.each(value.presenta, function(key2,value2){
                                    strHTML += (strHTML.length)?"":"";
                                    strHTML += "<li class='"+strClass+"'><input type='radio' name='txtPresenta' id='txtPresenta' precio='"+value2.precio_venta+"' etiqueta='"+value2.descripcion+"' value='"+value2.presentacion_tipo_id+"'><b>"+value2.descripcion+":</b> --> "+value2.precio_venta+" </li>";
                                });
                            }
                            else{
                                strHTML += (strHTML.length)?"":"";
                                strHTML += "<li class='"+strClass+"'><input type='radio' name='txtPresenta' id='txtPresenta' precio='"+value.precio_venta+"' etiqueta='"+value.descripcion+"' value='"+value.presentacion_tipo_id+"'><b>"+value.descripcion+":</b> --> "+value.precio_venta+" </li>";
                            }
                            strClass = (strClass =="row1")?"row2":"row1";
                        });
                        strHTML     += "</ul><br>";
                    }

                    var ObjInputs=({
                        "html": strHTML ,
                        "inputs":{
                            txtCantidad :{
                                "title":'Cantidad de productos',
                                 "attrs":{
                                    "value":"",
                                    "type":"text"
                                 }
                            },
                            txtDescuento:{
                                "title": "Agregar % descuento",
                                "attrs":{
                                    "value":"",
                                    "type":"text",
                                    "placeholder":"valores en porcentaje, eje: 2, 3, etc."
                                }
                            }
                        }
                    })

                    ObjWidgets.promptDialog(ObjInputs, "Agregar Producto", addDetail, true);
                }

            }

            objPedido = new pedido({
                objProductos: "td-pedido-contenido-productos",
                objSolicitud: "td-pedido-contenido-solicitud"
            });
            function getstrLink(){
                this.link = "<?php print $this->getStrAction(); ?>&op=true";
            }
            var globalAccion = new getstrLink();
            $(document).ready(function(){
                var strForm = "<?php print $strForm ?>";
                objPedido.getInterfaceProductos();
                objPedido.getInterfaceSolicitud();

                $("#"+strForm+"_txtSearchCliente").autocomplete({
                    source: globalAccion.link+"&getCliente=true",
                    minLength: 2,
                    select: function( event, ui ) {
                        $("#label_cliente").html("").hide();
                        $("#edit_cliente").html("").hide();
                        if(typeof ui.item.id != "undefined"){
                            $("#label_cliente").html("");
                            $("#edit_cliente").html("");
                            if(ui.item.id != 0){
                                $("#"+strForm+"_idcliente").val(ui.item.id);

                                var editCliente = $("<a class='button'></a>")
                                                    .html("Editar cliente")
                                                    .click(function(){
                                                        addCliente(ui.item);
                                                    });

                                $("#label_cliente").html("<b>"+ui.item.value+"</b>")
                                                   .show();
                                $("#edit_cliente")
                                                  .append(editCliente)
                                                  .show();
                            }

                        }
                        $(this).val("");
                    },
                    open: function() {
                        $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );

                    },
                    close: function() {
                        $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
                        $(this).val("");
                    }
                });
            });

            var addCliente = function(arrInfoCliente){
                if(!arrInfoCliente){
                    arrInfoCliente ={
                        "nombre":"",
                        "nit" : "",
                        "direccion" : "",
                        "telefono" : "",
                        "idcliente" : ""
                    }
                }

                function getstrLink(){
                    this.link = "<?php print $this->getStrAction(); ?>&op=true";
                }

                var globalAccion = new getstrLink();
                var formCliente = $("<form></form>")
                                  .attr({
                                    id : "frm_cliente",
                                    name: "frm_cliente"
                                    });
                var inputClienteID = $("<input/>")
                                     .attr({
                                        "id":"frm_cliente_id",
                                        "name":"frm_cliente_id",
                                        "type":"hidden",
                                        "value":arrInfoCliente.idcliente
                                     });
                var objDialog = $("<div id='div-cliente'></div>")
                                .append(formCliente)
                var inputnombre = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_nombre",
                                    "name":"frm_cliente_nombre",
                                    "placeholder":"Nombre del cliente",
                                    "value":arrInfoCliente.nombre
                                   });

                var inputNit = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_nit",
                                    "name":"frm_cliente_nit",
                                    "placeholder":"NIT cliente",
                                    "value":arrInfoCliente.nit
                                   });

                var inputDireccion = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_direccion",
                                    "name":"frm_cliente_direccion",
                                    "placeholder":"Direccion cliente",
                                    "value":arrInfoCliente.direccion
                                   });

                var inputTelefono = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_telefono",
                                    "name":"frm_cliente_telefono",
                                    "placeholder":"Telefono cliente",
                                    "value":arrInfoCliente.telefono
                                   });

                formCliente.append(inputnombre)
                           .append(inputClienteID)
                           .append(inputNit)
                           .append(inputDireccion)
                           .append(inputTelefono)
                objDialog.dialog({
                    title : "Crear cliente",
                    modal: true,
                    position: { my: "center top", at: "center top", of: window },
                    close: function(){
                        objDialog.dialog("destroy");
                        $("#div-cliente").remove();
                        $(".ui-dialog").remove();
                        $("#frm_cliente").remove();
                    },
                    buttons: {
                        "Grabar": function(){
                            var arrData = new fntSendData({
                                strParams: $("#frm_cliente").serialize(),
                                strUrl: globalAccion.link+"&svCliente=true",
                                strDataTypeAjax : "json"
                            });

                            if(typeof arrData.status != "undefined"){
                                $("#edit_cliente").html("").hide();
                                $("#label_cliente").html("").hide();
                                if(arrData.status == "ok"){
                                    var strForm = "<?php print $strForm; ?>"
                                    objDialog.dialog("close");
                                    $("#"+strForm+"_idcliente").val(arrData.detalle.idcliente);
                                    $("#label_cliente").html(arrData.detalle.nombre + " - " + arrData.detalle.nit).show();

                                    var editCliente = $("<a class='button'></a>")
                                                    .html("Editar cliente")
                                                    .click(function(){
                                                        addCliente(arrData.detalle);
                                                    });

                                    $("#edit_cliente")
                                                      .append(editCliente)
                                                      .show();
                                }
                            }
                        },
                        Cancelar:function(){
                            $("#frm_cliente").remove();
                            $(".ui-dialog").remove();
                            $("#edit_cliente").html("").hide();
                            $("#label_cliente").html("").hide();
                        }
                    }
                });
            }
            var Producto = function(arrDatosProducto){
                if(!arrDatosProducto){
                    arrDatosProducto ={
                        "idproducto":"",
                        "descripcion":"",
                        "etiqueta":"",
                        "nombre":""
                    }
                }

                var formProducto = $("<form></form>")
                                  .attr({
                                    id : "frm_producto",
                                    name: "frm_producto"
                                  });
                var objDialog = $("<div id='div-producto'></div>")
                                .append(formProducto)


                var inputProdID = $("<input/>")
                                .attr({
                                   "id":"frm_producto_id",
                                   "name":"frm_producto_id",
                                   "type":"hidden",
                                   "size":"40",
                                   "value": arrDatosProducto.id
                                });

                var inputNombre = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_nombre",
                                    "name":"frm_cliente_nombre",
                                    "placeholder":"Nombre del producto",
                                    "size":"40",
                                    "value":arrDatosProducto.nombre
                                   });
                var inputEtiqueta = $("<input>")
                                  .attr({
                                    "id":"frm_cliente_etiqueta",
                                    "name":"frm_cliente_etiqueta",
                                    "placeholder":"Nombre generico del producto",
                                    "size":"40",
                                    "value":arrDatosProducto.etiqueta
                                   });
                var inputDescrip = $("<textarea></textarea>")
                                  .attr({
                                    "id":"frm_cliente_descripcion",
                                    "name":"frm_cliente_descripcion",
                                    "placeholder":"Descripcion del producto",
                                    "cols":"38",
                                    "value":arrDatosProducto.descripcion
                                   });

                formProducto.append(inputProdID)
                            .append(inputNombre)
                            .append(inputEtiqueta)
                            .append(inputDescrip);

                objDialog.dialog({
                    title : "Crear producto",
                    modal: true,
                    width: 375,
                    position: { my: "center top", at: "center top", of: window },
                    close: function(){
                        objDialog.dialog("destroy");
                        $("#div-producto").remove();
                        $(".ui-dialog").remove();
                        $("#frm_producto").remove();
                    },
                    buttons: {
                        "Grabar": function(){
                            var arrData = new fntSendData({
                                strParams: $("#frm_producto").serialize(),
                                strUrl: "index.php?act=lnk&page=inventario_ingreso&mod=inventario&saveProd=true",
                                strDataTypeAjax : "json"
                            });

                            if(typeof arrData.status != "undefined"){
                                $("#div-GuardarProducto").html("");
                                $("#td-etiquetaProducto").html("");
                                if(arrData.status == "ok"){
                                    objDialog.dialog("close");
                                }
                            }
                        },
                        "Cancelar":function(){
                            $(this).dialog("close");
                        }
                    }
                });
            }
        </script>
        <?php
        $this->finForm();
    }
}

