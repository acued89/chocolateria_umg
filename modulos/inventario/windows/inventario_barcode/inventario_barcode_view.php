<?php
require_once("modulos/inventario/inventario_view.php");

class inventario_barcode_view extends inventario_view{
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
        $this->getButtons("ButtonsMarcas", $this->getArrBotones(),5);
        $this->drawContenido();        
    }
    public function get_javascript(){
        ?>
        <script type="text/javascript">
            var myWidget = new drawWidgets();
            function nuevo(){
                $(":input").val("").attr("checked","").show();
                $("#frm_tipos_name")
                        .data({
                            "isNew":1,
                            "id":0
                        });
            }
            function grabar(){
                var objMarca = $("#frm_tipos_barcode");
                var objDescription = $("#frm_tipos_peso");
                var objUrl = $("#frm_tipos_url");
                
                if(objMarca.val() !== ""){
                    var isnew = objMarca.data("isNew");
                    var link = "<?php print $this->getStrAction(); ?>"
                    if(isnew)
                        link += "&op=new";                    
                    else
                        link += "&op=update";
                    
                    var params = {
                        id: objMarca.data("id")+" ",
                        barcode: objMarca.val(),
                        url: objUrl.val(),
                        peso: objDescription.val()
                        
                    };                                        
                    
                    link += serializeObj(params);
                    
                    $.get(link,function(data){
                        if(isnew){
                            if(data.NewResult === "true"){
                                myWidget.alertDialog("Tipo agregada exitosamente");
                            }
                            else{
                                myWidget.alertDialog("El tipo ya ha sido registrada, intente ingresando un nuevo tipo");
                            }
                            
                        }
                        else{
                            if(data.UpdateResult === "true"){
                                myWidget.alertDialog("El tipo fue actualizada");
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
                var objMarca = $("#frm_tipos_name");
                if(objMarca.val() !== ""){
                    console.log(objMarca.data("id"))
                    var params = {
                        id: objMarca.data("id")+" ",
                        data: objMarca.val()
                    };
                    link += serializeObj(params);
                    $.get(link,function(data){
                        if(data.DeleteResult === "true"){
                            myWidget.alertDialog("Tipo eliminado");
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
                    myWidget.alertDialog("Seleccione un tipo para poder eliminarla")
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
                                    .html(value.IdType);
                            tr.append(td);
                            var td = $("<td></td>")
                                    .html(value.Name + " - " + value.Description);
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
                $("#frm_tipos_name").val(data.Name);
                $("#frm_tipos_name").data({
                    "isNew":0,
                    "id" : data.IdType
                });
                $("#frm_tipos_description").val(data.Description);
            }
            
            $(function(){
                $("#frm_tipos_name")
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
        $this->initForm("frm_tipos");
        ?>
        <div id="content-tipos">
            <div class="panel panel-primary">
                <div class="panel-heading">Size Wight Barcode</div>
                <div class="panel-body">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Codigo de Barras</label>
                            <input class="form-control" size="30" name="frm_tipos_barcode" id="frm_tipos_barcode">
                        </div>
                        <div class="form-group">
                            <label>Peso</label>
                            <input class="form-control" size="30" name="frm_tipos_peso" id="frm_tipos_peso">
                        </div>
                        <div class="form-group">
                            <label>URL</label>
                            <input class="form-control" size="30" name="frm_tipos_url" id="frm_tipos_url">
                        </div>
                    </div>                                        
                </div>
            </div>
        </div>
        <?php
        $this->finForm();
    }
}
