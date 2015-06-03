<?php
require_once("modulos/caja/caja_view.php");

class factura_view extends caja_view {

    static $_instance;
    private $objController;
    private $params = array();
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

    public function drawPage() {
        $this->get_javascript();
        $this->drawContenido();
    }

    public function getArrBotones() {
        $this->arrBotons[0] = array("title" => "Nuevo", "name" => "btnNuevo", "onclick" => "");
        $this->arrBotons[1] = array("title" => "Grabar", "name" => "btnGrabar", "onclick" => "");
        return $this->arrBotons;
    }

    public function get_javascript() {
        ?>        
        <script type="text/javascript">
            var myWidget = new drawWidgets();
            function buscarCliente() {
                var divLoad = $("#divglobal_load");
                divLoad.dialog({
                    autoOpen: false,
                    show: "blind",
                    hide: "blind",
                    modal: true,
                    closeOnEscape: false,
                    resizable: true,
                    draggable: true,
                    width: 800,
                    height: 410,
                    maxHeight: 725,
                    position: {my: "center middle", at: "center middle", of: window},
                    close: function () {
                        $(this).html("");
                    },
                    buttons: {
                        "Cancelar": function () {
                            CloseSearch($(this));
                        }
                    }
                });

                var link = "<?php print $this->getStrAction(); ?>&op=getcliente"
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: link,
                    cache: false,
                    async: false,
                    beforeSend: function () {
                        myWidget.openLoading();
                    },
                    success: function (data) {
                        myWidget.closeLoading();

                        myWidget.closeLoading();
                        table = $("<table width='100%'></table>")
                                .addClass("table table-striped table-bordered table-hover dataTable no-footer");
                        var tr = $("<tr role='row'></tr>")                                
                        var td = $("<td width='5%'></td>")
                                .html("<b>Codigo</b>")
                        tr.append(td);
                        var td = $("<td width='45%'></td>")
                                .html("<b>Nombre completo</b>")
                        tr.append(td);
                        var td = $("<td width='50%'></td>")
                                .html("<b>Tax number</b>")
                        tr.append(td)                            
                        table.append(tr);
                        var classtd = "gradeA odd";
                        $.each(data.GetJsonResult, function (key, value) {
                            var tr = $("<tr role='row'></tr>");
                            var td = $("<td width='5%'></td>")
                                    .html(value.IdCustomer)
                            tr.append(td);
                            var td = $("<td width='45%'></td>")
                                    .html(value.CompleteName);
                            tr.append(td);
                            var td = $("<td width='50%'></td>")
                                    .html(value.TaxNumber);
                            tr.append(td);
                            tr.append(td)
                                    .addClass(classtd)
                                    .css({"cursor": "pointer"})
                                    .click(function () {
                                        selectCliente(value.IdCustomer, value.CompleteName); 
                                        CloseSearch($("#divglobal_load"));
                                    });

                            table.append(tr);
                            classtd = (classtd == "gradeA odd") ? "gradeA even" : "gradeA odd";
                        });

                        divLoad.html(table);
                        divLoad.dialog("open");
                    },
                    error: function () {
                        myWidget.closeLoading();
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    }
                });
            }
            function buscarBodega() {                
                var divLoad = $("#divglobal_load");
                divLoad.dialog({
                    autoOpen: false,
                    show: "blind",
                    hide: "blind",
                    modal: true,
                    closeOnEscape: false,
                    resizable: true,
                    draggable: true,
                    width: 800,
                    height: 410,
                    maxHeight: 725,
                    position: {my: "center middle", at: "center middle", of: window},
                    close: function () {
                        $(this).html("");
                    },
                    buttons: {
                        "Cancelar": function () {
                            CloseSearch($(this));
                        }
                    }
                });                
                
                var link = "<?php print $this->getStrAction(); ?>&view=bodegas"
                $.ajax({
                    type: "GET",
                    url: link,
                    cache: false,
                    async: false,
                    beforeSend: function () {
                        myWidget.openLoading();
                    },
                    success: function (data) {
                        myWidget.closeLoading();
                        divLoad.html(data).dialog("open");
                    },
                    error: function () {
                        myWidget.closeLoading();
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    }
                });
            }
            function CloseSearch(objDiv) {
                objDiv.html("");
                objDiv.dialog("close");
            }
            function selectBodega(idBranch, idWarehouse, strName){
                var divLoad = $("#divglobal_load");
                $("#frmPedido_idbodega").val(idWarehouse);
                $("#frmPedido_idbranch").val(idBranch);
                $("#label_bodega").html(strName);
                CloseSearch(divLoad);
            }
            function selectCliente(idCliente, strName){
                $("#frmPedido_idcliente").val(idCliente);
                $("#label_cliente").html(strName);
                
            }
            function getProductos(){
                var link = "<?php print $this->getStrAction(); ?>&view=productos"
                $.ajax({
                    type: "GET",
                    url: link,
                    cache: false,
                    async: false,
                    beforeSend: function () {
                        myWidget.openLoading();
                    },
                    success: function (data) {
                        myWidget.closeLoading();
                        $("#tbody-productos").html(data);
                    },
                    error: function () {
                        myWidget.closeLoading();
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    }
                });
            }
            $(document).ready(function () {
                $("#search_cliente").click(function () {
                    buscarCliente();
                });
                $("#search_bodega").click(function () {
                    buscarBodega();
                });
                
                getProductos();
            });
            function addItem(obj){
                var divLoad = $("#divglobal_load");
                divLoad.dialog({
                    autoOpen: false,
                    show: "blind",
                    hide: "blind",
                    modal: true,
                    closeOnEscape: false,
                    resizable: true,
                    draggable: true,
                    width: "auto",
                    height: "auto",
                    maxHeight: 725,
                    position: {my: "center middle", at: "center middle", of: window},
                    close: function () {
                        $(this).html("");
                    },
                    buttons: {
                        "Agregar": function(){
                            var param = {
                                "cantidad":$("#txtCantidad").val(),
                                "precio":$("#hddnprecio").val(),
                                "barcode": $("#hddnbarcode").val()
                            }
                            appendItem(param);
                        },
                        "Cancelar": function () {
                            CloseSearch($(this));
                        }
                    }
                });
                var param = serializeObj(obj);
                var link = "<?php print $this->getStrAction(); ?>&view=additem"
                $.ajax({
                    type: "GET",
                    url: link,
                    data: param,
                    cache: false,
                    async: false,
                    beforeSend: function () {
                        myWidget.openLoading();
                    },
                    success: function (data) {
                        myWidget.closeLoading();
                        divLoad.html(data);
                        divLoad.dialog("open");
                    },
                    error: function () {
                        myWidget.closeLoading();
                        myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                    }
                });                
            }
            
            function appendItem(obj){
                var tbody = $("#tbody-items-add");
                var tr = $("<tr></tr>");
                var td = $("<td></td>")
                        .append("<input type='text'  >");
                
                tr.append(td);
            }
        </script>
        <?php
    }
    public function additem(){
        ?>
        <div class="row">
            <div class="col-md-3">
                Cantidad
            </div>
            <div class="col-md-5">
                <input type="text" name="txtCantidad" id="txtCantidad" value="">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                Precio
            </div>
            <div class="col-md-5">
                <?php 
                print "Q";
                ?>
            </div>
        </div>
        <?php
    }
    public function drawContenido() {
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
                                <button type="button" id="search_cliente" onclick="buscarCliente" class="btn btn-info btn-circle"><i class="fa fa-search" ></i></button>
                                <input type="hidden" name="<?php print $strForm ?>_idcliente" id="<?php print $strForm ?>_idcliente" value="">
                                <span id='label_cliente' class= '' style=''></span><br>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bodega</label>
                                </div>                            
                            </div>
                            <div class="col-md-8">
                                <button type="button" id="search_bodega" class="btn btn-info btn-circle"><i class="fa fa-search"></i></button>
                                <input type="hidden" name="<?php print $strForm ?>_idbodega" id="<?php print $strForm ?>_idbodega" value="">
                                <input type="hidden" name="<?php print $strForm ?>_idbranch" id="<?php print $strForm ?>_idbranch" value="">
                                <span id='label_bodega' class= '' style=''></span><br>
                            </div>
                        </div>
                    </div>                                        
                </div>
            </div>
            <br/>
            <div id="td-pedido-contenido" class="row">
                <div id="td-pedido-contenido-solicitud" class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Productos agregados</div>
                        <div class="panel-body">
                            <div style="height: 500px; overflow-y: auto;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Cantidad</th>
                                                <th>Barcode</th>
                                                <th>Precio</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-items-add">

                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <?php
                                    $this->getButtons("ButtonsFacturacion", $this->getArrBotones(), 5);
                                    ?>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>
                <div id="td-pedido-contenido-productos" class="col-md-8">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Productos</div>
                        <div class="panel-body">
                            <div  style="height: 500px; overflow-y: auto;">
                                <div class="table-responsive">
                                
                                    
                                    <div class="table-responsive">
                                        <div id="dataTables-example_wrapper" class="dataTables_wrapper form-inline" role="grid">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="dataTables_filter" id="dataTables-example_filter">
                                                        <label>Search:
                                                            <input aria-controls="dataTables-example" class="form-control input-sm" type="search">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <table aria-describedby="dataTables-example_info" class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-example">
                                                <thead>
                                                    <tr role="row">
                                                        <th aria-label="" aria-sort="ascending"  colspan="1" rowspan="1" aria-controls="dataTables-example" tabindex="0" class="sorting_asc">Productos</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-productos">


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
        </div>
        <?php
        $this->finForm();
    }

    public function drawBodegas() {        
            ?>
            <table aria-describedby="dataTables-example_info" class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-example">
                <thead>
                    <tr role="row">
                        <th aria-label="Nombre" aria-sort="ascending" style="width: 185px;" colspan="1" rowspan="1" aria-controls="dataTables-example" tabindex="0" class="sorting_asc">Nombre</th>
                        <th aria-label="Ubicacion" aria-sort="ascending" style="width: 185px;" colspan="1" rowspan="1" aria-controls="dataTables-example" tabindex="0" class="sorting_asc">Ubicación</th>
                </thead>
                <tbody>
                    <?php
                    $strClass = "even";
                    foreach ($_SESSION["wild"]["bodegas"] AS $key => $value) {
                        ?>
                    <tr class="gradeA <?php print $strClass; ?>" onclick="selectBodega('<?php print $value["IdBranch"] ?>','<?php print $value["IdWareHouse"] ?>','<?php print $value["Name"] ?>')" style="cursor:pointer;">
                            <td class="center"><?php print $value["Name"]; ?></td>
                            <td class="center"><?php print $value["Location"]; ?></td>
                        </tr>
                        <?php
                        $strClass = ($strClass)?"even":"odd";
                        unset($key);unset($value);
                    }
                    ?>
                </tbody>
            </table>
            <?php        
    }
    
    public function drawProductos($arrProductos){
        //debug::drawdebug($arrProductos);
        $strClass = "even";
        foreach($arrProductos as $key => $value){
            ?>
            <tr>
                <td class="<?php print $strClass; ?>" align="center">
                    <?php 
                    print "<b>".$value["Name"] . "</b>"; 
                    if(isset($value["presentaciones"])){
                        print "<br><b>Presentaciones: </b>";
                        foreach($value["presentaciones"] as $key2 => $value2){
                            if(isset($value2["inventario"])){
                                $arrSend["barcode"] = $value2["Barcode"];
                                $arrSend["presentacion"] = $value2["SizeOrWeight"];
                                $arrSend["costo"] = $value2["inventario"]["CostPrice"];
                                $arrSend["stock"] = $value2["inventario"]["Stock"];
                                $param = json_encode($arrSend);
                                ?>
                                <script type="text/javascript">
                                    $("#p-item-<?php print $value2["Barcode"] ?>").click(function(){
                                        addItem(<?php print $param; ?>)
                                    })
                                </script>
                                <p align="left" id="p-item-<?php print $value2["Barcode"] ?>" style="cursor:pointer;" onmouseout="$(this).addClass('even').removeClass('odd')" onmouseover="$(this).addClass('odd').removeClass('even')">
                                    <?php                                 
                                    print $value2["SizeOrWeight"]; 
                                    print "<br>";
                                    print "Barcode: ". $value2["Barcode"];                                                                                                         
                                        print "<br>";
                                        print "Costo: " . number_format($value2["inventario"]["CostPrice"],2);
                                        print "<br>";
                                        print "Existencias: " . $value2["inventario"]["Stock"];

                                    ?>                                
                                </p>
                                <hr>
                                <?php
                            }
                            else{
                                print "<p align='left'>";
                                print $value2["SizeOrWeight"]; 
                                print "<br>";
                                print "No hay existencias";
                                print "</p>";
                            }
                        }
                    }                    
                    ?>                    
                </td>
            </tr>
            <?php
            //$strClass = ($strClass)?"even":"odd";
        }        
    }

}
