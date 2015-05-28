<?php
require_once("modulos/inventario/inventario_view.php");

class inventario_marcas_view extends inventario_view{
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
    public function getArrBotones(){
        $this->arrBotons[0] = array("title" => "Nuevo", "name" => "btnNuevo", "onclick" => "nuevo()");
        $this->arrBotons[1] = array("title" => "Grabar", "name" => "btnGrabar", "onclick" => "grabar();");
        $this->arrBotons[3] = array("title" => "Buscar", "name" => "btnBuscar", "onclick" => "buscar()");
        $this->arrBotons[4] = array("title" => "Eliminar", "name" => "btnEliminar", "onclick" => "eliminar()");
        return $this->arrBotons;
    }
   
    public function drawContenido(){
        $this->initForm("frm_marcas");
        ?>
        <div id="content-marcas">
            <div class="panel panel-primary">
                <div class="panel-heading">Marcas</div>
                <div class="panel-body">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input class="form-control" size="30" name="frm_marcas_name" id="frm_marcas_name">
                        </div>
                    </div>                                        
                </div>
            </div>
        </div>
        <?php
        $this->finForm();
    }
}
