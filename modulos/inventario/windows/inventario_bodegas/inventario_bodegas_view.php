<?php
require_once("modulos/inventario/inventario_view.php");

class inventario_bodegas_view extends inventario_view{
    static $_instance;
    private $objView;
    
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
        $this->getButtons("ButtonsBodegas", $this->getArrBotones(),5);
        $this->drawContenido();        
    }
    public function get_javascript(){
        ?>
        <script type="text/javascript">
            var myWidget = new drawWidgets();
            function nuevo(){
                $(":input").val("").attr("checked","").show();
                $("#frm_bodegas_name")
                        .data({
                            "isNew":1,
                            "id":0
                        });
            }
           
             function grabar(){
                var objBodega = $("#frm_bodegas_name");
                var objIdbranch = $("#frm_bodegas_idbranch");
                var objLocation = $("#frm_bodegas_location");
                
                if(objBodega.val() !== ""){
                    var isnew = objBodega.data("isNew");
                    var link = "<?php print $this->getStrAction(); ?>"
                    if(isnew)
                        link += "&op=new";                    
                    else
                        link += "&op=update";
                    
                    var params = {
                        id: objBodega.data("id")+" ",
                        name: objBodega.val(),
                        idbranch: objIdbranch.val(),
                        location: objLocation.val()
                        
                    };                                        
                    
                    link += serializeObj(params);
                    
                    $.get(link,function(data){
                        if(isnew){
                            if(data.NewResult === "true"){
                                myWidget.alertDialog("Bodega agregada exitosamente");
                            }
                            else{
                                myWidget.alertDialog("La bodega ya ha sido registrada, intente ingresando una nueva bodega");
                            }
                            
                        }
                        else{
                            if(data.UpdateResult === "true"){
                                myWidget.alertDialog("La bodega fue actualizada");
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
                var objBodega = $("#frm_bodegas_name");
                if(objBodega.val() !== ""){
                    console.log(objBodega.data("id"))
                    var params = {
                        id: objBodega.data("id")+" ",
                        name: objBodega.val()
                    };
                    link += serializeObj(params);
                    $.get(link,function(data){
                        if(data.DeleteResult === "true"){
                            myWidget.alertDialog("Bodega eliminada");
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
                    myWidget.alertDialog("Seleccione una bodega para poder eliminarla")
                }
                
            }
            function CloseSearch(objDiv){
                objDiv.html("");
                objDiv.dialog("close");
            }
            function buscar(){
                var divLoad = $("#divglobal_load");
                divLoad.dialog({
                    autoOpen:false,
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
                            .html("<b>Codigo</b>")    
                    tr.append(td);
                    var td = $("<td width='15%'></td>")
                            .html("<b>Id Sucursal</b>")
                    tr.append(td);
                    var td = $("<td width='40%'></td>")
                            .html("<b>Bodega</b>")    
                    tr.append(td);
                    var td = $("<td width='40%'></td>")
                            .html("<b>Ubicacion</b>")
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
                                    .html(value.IdWareHouse);
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.IdBranch);
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.Name);
                            tr.append(td);
                            var td =$("<td></td>")
                                    .html(value.Location);
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.IsActive);

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
                    divLoad.html(table);
                    divLoad.dialog("open");
                })
                .fail(function() {
                    myWidget.alertDialog("Ha ocurrido un error, por favor intentelo de nuevo")
                });;
            }
            
            function seleccionar(data){
                console.log(data)
                $("#frm_bodegas_name").val(data.Name);
                $("#frm_bodegas_name").data({
                    "isNew":0,
                    "id" : data.IdWareHouse
                });
                $("#frm_bodegas_location").val(data.Location);
                $("#frm_bodegas_idbranch").val(data.IdBranch);
            }
            
            $(function(){
                $("#frm_bodegas_name")
                .data({
                    "isNew":1,
                    "id":0
                });
            })
        </script>
        <?php       
    }
    public function getArrBotones(){
        $this->arrBotons[0] = array("title" => "Nuevo", "name" => "btnNuevo", "onclick" => "nuevo()");
        $this->arrBotons[1] = array("title" => "Grabar", "name" => "btnGrabar", "onclick" => "grabar();");
        $this->arrBotons[3] = array("title" => "Buscar", "name" => "btnBuscar", "onclick" => "buscar()");
        $this->arrBotons[4] = array("title" => "Eliminar", "name" => "btnEliminar", "onclick" => "eliminar()");
        return $this->arrBotons;
    }
   
    public function drawContenido(){
        $this->initForm("frm_bodegas");
        ?>
        <div id="content-bodegas">
            <div class="panel panel-primary">
                <div class="panel-heading">Bodegas</div>
                <div class="panel-body">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Nombre Bodega</label>
                            <input class="form-control" size="30" name="frm_bodegas_name" id="frm_bodegas_name">
                            
                        </div>
                        <div class="form-group">
                            <label>Id Sucursal</label>
                            <input class="form-control" size="30" name="frm_bodegas_idbranch" id="frm_bodegas_idbranch">
                            
                        </div>
                        <div class="form-group">
                            <label>Ubicacion</label>
                            <input class="form-control" size="30" name="frm_bodegas_location" id="frm_bodegas_location">
                        </div>
                    </div>                                        
                </div>
            </div>
        </div>
        <?php
        $this->finForm();
    }
}
