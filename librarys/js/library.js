var userAgent = navigator.userAgent.toLowerCase();
jQuery.browser = {
    version: (userAgent.match( /.+(?:rv|it|ra|ie|me)[\/: ]([\d.]+)/ ) || [])[1],
    chrome: /chrome/.test( userAgent ),
    safari: /webkit/.test( userAgent ) && !/chrome/.test( userAgent ),
    opera: /opera/.test( userAgent ),
    msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
    mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
};

function getElement(strName, objDoc ){
    var p,i,x= false;

    if(!objDoc) objDoc=document;

    if(objDoc[strName]) {
        x=objDoc[strName];
        if (!x.tagName) x = false;
    }

    if (!x && objDoc.all) x=objDoc.all[strName];
    for (i=0;!x && i<objDoc.forms.length; i++) x=objDoc.forms[i][strName];
    if (!x && objDoc.getElementById) x=objDoc.getElementById(strName);
    for (i=0;!x && objDoc.layers && i<objDoc.layers.length; i++) x=getDocumentLayer(strName,objDoc.layers[i].document);

    return x;

}

function jDialog(Texto, Titulo, BoolRecarga, strFun){
    var objDiv = $("<div></div>");
    if(!strFun) strFun = false;
    if(!BoolRecarga) BoolRecarga = false;
    objDiv.html("");
    objDiv.html(Texto);
    $(".ui-dialog-titlebar").show();
    objDiv.dialog({
        modal: true,
        title: Titulo,
        width : 300,
        height : 200,
        position: { my: "center middle", at: "center middle", of: window },
        close: function(){
            if(BoolRecarga) location.reload();
            objDiv.remove();
        },
        buttons: {
            ok: function(){
                if(strFun) eval(strFun);
                $(this).dialog("close");
            }
        }
    });
    objDiv.dialog("widget").find( ".ui-dialog-titlebar-close" ).hide();
    return objDiv;
}

function ajaxJsonData(url, data, strFunBef, strFunSuccess, strFunError){
    if(!strFunBef) strFunBef = false;
    if(!strFunSuccess) strFunSuccess = false;
    if(!strFunError) strFunError = false;
    var _resp;
    $.ajax({
        type:        "POST",
        dataType:    "json",
        data:        data,
        url:         url,
        cache:       false,
        async:       false,
        beforeSend: function(){
            if(strFunBef) eval(strFunBef)
        },
        success: function(data){
            _resp = data;
            $.each(data, function(i, item){
                if(item.estado =="ok"){
                    if(!item.msg) jDialog("Su informacion ha sido ejecutada satisfactoriamente","Mensaje del sistema",false,strSuccess);
                    else jDialog(item.msg, "Mensaje del sistema",false, strFunSuccess);
                }
                else{
                    if(!item.error){
                        if(!item.msg) jDialog("Ha ocurrido un error, por favor intentelo de nuevo <br/> si el error persiste contacte a su administrador", "ERROR AL EJECUTAR", true, strFunError);
                        else jDialog(item.msg, "Mensaje del sistema", false, strFunError);
                    }
                    else{
                        if(!item.msg) jDialog(item.error, "Mensaje del sistema", false, strFunError);
                        else{
                            var tempo = item.msg + "<br>" + item.error;
                            jDiaglog(tempo, "Mensaje del sistema", false, strFunError);
                        }
                    }
                }
            });
            return _resp;
        },
        error: function(){
            _resp = {
                "return":"0",
                "msg": "Ha ocurrido un error en la ejecucion"
            }
            jDialog("Hemos perdido conexción con internet. <br/> Presione OK para recargar la página.","Mensaje del sistema",true );
            return _resp;
        }
    });
}

function ajaxSendData(link, data, objInterface, objXHR ,boolLoading){
    boolError = false;
    if(!objXHR) boolForceAbort = false;
    else boolForceAbort = true;
    if(!boolLoading) boolLoading = false;
    else boolError = true;
    objInterface.html("");
    if(boolForceAbort){
        if(objXHR) objXHR.abort();
    }
    objXHR = $.ajax({
        type:   "POST",
        data : data ,
        url :   link,
        beforeSend: function(){
            if(boolLoading) fntabrirCargando();
        },
        success: function (data){
            if(boolLoading) fntcerrarCargand();
            if(objInterface.length) objInterface.html(data);

            xhr = null;
        },
        error: function(){
            if(boolLoading) fntcerrarCargand();
        }
    });
    return objXHR;
}

function fntabrirCargando(){
    $("#divglobal_load").dialog({
        resizable: false,
        modal: true,
        closeOnEscape: false,
        position: { my: "center middle", at: "center middle", of: window },
        open: function(event,ui){
            var objImg = $("#img-divglobal-load");
            var objDialog = $("#divglobal_load");
            var intDialogWidth = objImg.outerWidth(false);
            var intDialogHeight = objImg.outerHeight(false);
            $(".ui-dialog-titlebar").hide();
            if( $.browser.msie ) {
                objDialog.dialog( "option", "width", intDialogWidth );
                objDialog.dialog( "option", "height", intDialogHeight );
            }
            else {
                intDialogHeight += 15;
                objDialog.dialog( "option", "width", intDialogWidth );
                objDialog.dialog( "option", "height", intDialogHeight );
            }
        }
    });
}

function fntcerrarCargand(){
    $("#divglobal_load").dialog("close");
}

function checkLength( o, oa, n, min, max, boolError ) {
    if(!boolError) boolError = false;
    var boolMax = (max > 0)?true:false;
    o.removeClass( "ui-state-error" );
    if(boolMax){
        if (max == 0 || (o.val().length > max || o.val().length < min) || boolError) {
            o.addClass( "ui-state-error" );
            updateTips(oa, " El tamaño del campo " + n + " tiene que ser entre  " +
                min + " y " + max + "." );
            return false;
        } else {
            return true;
        }
    }
    else{
        if((o.val().length < min) || boolError){
            o.addClass( "ui-state-error" );
            updateTips(oa, n);
            return false;
        }
        else {
            return true;
        }
    }
}

function updateTips(oa, t ) {
    oa.append(t).addClass( "ui-state-highlight" );
    setTimeout(function() {
        oa.removeClass( "ui-state-highlight", 1500 ).addClass("ui-state-error");
    }, 500 );
}

function clearTips(){
    $("#divglobal_load").hide().html("");
    $(".ui-state-error").removeClass("ui-state-error");
}

function checkRegexp( o, regexp, n ) {
    if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips(o, n );
        return false;
    } else {
        return true;
    }
}

function checkForm(arrCamposRequerido, boolError){
    var bookExiste = true;
    var arrNotExist = new Array();
    if(!boolError) objError = false;
    else{
        objError = $("#divglobal_load");
        objError.html("").hide();
    }
    strError = "";
    var boolOK = true;
    for( var i in arrCamposRequerido){
        if($("input[name="+i+"]").length){
            $("input[name="+i+"]").each(function(){
                if(!objError){
                    if($(this).val() == "" || $(this).val() == 0 || $(this).val().length == 0){
                        strError += arrCamposRequerido[i];
                    }
                }
                else{
                    objError.show();
                    boolForceUpTip = false;
                    if($(this).val() == "" || $(this).val() == 0 || $(this).val().length == 0){
                        boolOK = false;
                        boolForceUpTip = true;
                    }
                    boolTMP = checkLength( $(this), objError, arrCamposRequerido[i], 1, 0, boolForceUpTip);
                    boolOK = boolOK && boolTMP;
                }
            });
        }
        else if($("select[name="+i+"]").length){
            $("select[name="+i+"]").each(function(){
                if(!objError){
                    if($(this).val() == "" || $(this).val() == 0){
                        strError += arrCamposRequerido[i];
                    }
                }
                else{
                    if($(this).val() == "" || $(this).val() == 0){
                        boolOK = false;
                    }
                }
            });
        }
        else{
            strError += arrCamposRequerido[i];
            boolOK = false;
            bookExiste = false;
            arrNotExist[i] = arrCamposRequerido[i];
        }
    }

    if(!objError){
        if(strError == ""){
            return true;
        }
        else {
            alert(strError)
            return false;
        }
    }
    else{
        if(boolOK) return true
        else{
            if(!bookExiste){
                var strNotExistmsg = "No existen algunos elementos que intenta validar.";
                var count = 0;
                for( var j in arrNotExist){
                    strNotExistmsg += "\t\n "+ j;
                    count++;
                }
                if(count > 0){
                    alert(strNotExistmsg);
                }
            }
            return false;
        }
    }
}

function ucWords(string){
    if(string != null){
        var arrayWords;
        var returnString = "";
        var len;
        arrayWords = string.split(" ");
        len = arrayWords.length;
        for(i=0;i < len ;i++){
            if(i != (len-1)){
                returnString = returnString+ucFirst(arrayWords[i])+" ";
            }
            else{
                returnString = returnString+ucFirst(arrayWords[i]);
            }
        }
        return returnString;
    }
}
function ucFirst(string){
    return string.substr(0,1).toUpperCase()+string.substr(1,string.length).toLowerCase();
}

function array_flip(trans){
    var key, tmp_ar = {};
    if (trans && typeof trans=== 'object' && trans.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
        return trans.flip();
    }
    for (key in trans) {
        if (!trans.hasOwnProperty(key)) {continue;}
        tmp_ar[trans[key]] = key;
    }
    return tmp_ar;
}

function debugJs($MyVar,$strName){
    if (!$MyVar) var $MyVar;
    if (!$strName)
        var $strName = "VarType "+typeof $MyVar;
    else
        $strName = 'Var "'+$strName+'" '+"Type "+typeof $MyVar;
    console.log($strName);
    console.log($MyVar);
}

function isLeapYear(intYear) {
    if (intYear % 4 == 0) {
        if (intYear % 100 == 0) {
            if (intYear % 400 == 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }
    else {
        return false;
    }
}

function boolCheckDate(intYear, intMonth, intDay) {
    boolReturn = true;
    if(!intYear) intYear = "";
    if(!intMonth) intMonth = "";
    if(!intDay) intDay = "";

    arrMeses = new Array();
    arrMeses[1] = 31;
    arrMeses[2] = (isLeapYear(intYear))?29:28;
    arrMeses[3] = 31;
    arrMeses[4] = 30;
    arrMeses[5] = 31;
    arrMeses[6] = 30;
    arrMeses[7] = 31;
    arrMeses[8] = 31;
    arrMeses[9] = 30;
    arrMeses[10] = 31;
    arrMeses[11] = 30;
    arrMeses[12] = 31;

    intYear = validarEntero(intYear);
    intMonth = validarEntero(intMonth);
    intDay = validarEntero(intDay);

    if(intYear.length == 0) boolReturn = false;
    else if(intMonth.length == 0) boolReturn = false;
    else if(intDay.length == 0) boolReturn = false;

    if(intMonth > 12 || intMonth < 1) {
        boolReturn = false;
    }
    else {
        if(intDay > arrMeses[intMonth * 1] || intDay < 1) {
            boolReturn = false;
        }
    }

    return boolReturn;
}

/*Ejemplo de uso de la clase
    //Parametro opcional
*   objTEST = new drawWidgets({ objDialogAlert: "dialog" });
    var arrWidgets = new Array();
        arrWidgets['title']='Localidad aun no configurada:&nbsp;';
        arrWidgets['txt']='La localidad <i>jdjdjd</i>, aun no se a configurado.';


    //objTEST.alertDialog("test","test tittle",false);
    objTEST.drawMesaggeWidget(arrWidgets,{"width":"350px;"});
*/
var drawWidgets = function(customSettings){
    var self = this;
    var elementLoading;
    var elemsntDialogMesagge;
    var elementDialogAlert;
    var defaults = {
        objLoading: "",
        objDialogMesagge: "",
        objDialogAlert: ""
    }

    customSettings || ( customSettings = {} );
    var settings = $.extend({}, defaults, customSettings);

    this.setOptions = function(customSettings){
        customSettings || ( customSettings = {} );
        settings = $.extend({}, defaults, customSettings);
    }

    this.getOptions = function(){
        return settings;
    }

    this.openLoading = function(boolModal){
        if(!boolModal) boolModal = false;
        $(function(){
            elementLoading = (settings.objLoading.length ==0)?$("<div></div>"):$("#"+settings.objLoading);
            elementLoading.css({"text-align":"center"})
            if(elementLoading){
                elementLoading.dialog({
                    resizable: false,
                    modal: boolModal,
                    closeOnEscape: false,
                    position: { my: "center middle", at: "center middle", of: window },
                    close: function(){
                        if(settings.objLoading.length ==0) elementLoading.remove();
                        $("#ObjOpenLoading-overlay").remove();
                    },
                    open: function(event,ui){
                        var objImg = $("<img/>");
                        objImg.attr({"src" :"images/ajax_medium.gif"});
                        elementLoading.html(objImg);
                        var intDialogWidth = objImg.outerWidth(true);
                        var intDialogHeight = objImg.outerHeight(true);
                        $(".ui-dialog-titlebar").hide();
                        if( $.browser.msie ) {
                            elementLoading.dialog( "option", "width", intDialogWidth );
                            elementLoading.dialog( "option", "height", intDialogHeight );
                        }
                        else {
                            intDialogHeight += 15;
                            elementLoading.dialog( "option", "width", intDialogWidth );
                            elementLoading.dialog( "option", "height", intDialogHeight );
                        }
                    }
                });
            }
        })
    }

    this.closeLoading= function(){
        $(function(){
            if(typeof elementLoading != "undefined" ){
                elementLoading.dialog("close");
                $(".ui-dialog-titlebar").show();
            }
        });
    }

    this.drawMesaggeWidget = function(arrContent,arrDimentions, strIDobjReturn){
        $(function(){
            ObjReturn = getDocumentLayer(settings.objDialogMesagge);
            if(!strIDobjReturn) strIDobjReturn=false;

            if(strIDobjReturn){
                ObjReturn = getDocumentLayer(strIDobjReturn);
            }

            if(!arrContent) arrContent = new Array();
            arrContent['txt']=((!arrContent['txt'])?((arrContent['msj'])?arrContent['msj']:'Hello World!'):arrContent['txt']);
            arrContent['title']=((!arrContent['title'])?"":"<i style='color:#003C77'>"+arrContent['title']+"</i>");


            if(!arrDimentions) arrDimentions = new Array();
            if(!arrDimentions['width'])arrDimentions['width']=(ObjReturn)?'350px':'auto';
            if(!arrDimentions['height'])arrDimentions['height']='auto';

            var ObjTr = $('<tr></tr>');
            var ObjTd = $('<td></td>');
            var ObjTh = $('<th></th>');

            var ObjWidgetTitleTd = $('<th align="left"></th>');
                ObjWidgetTitleTd.append('<span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>');
                ObjWidgetTitleTd.append(arrContent['title']);

            var ObjWidgetTitleTr = $('<tr></tr>');;
                ObjWidgetTitleTr.append(ObjWidgetTitleTd);

            var ObjWidgetTitle = $('<thead></thead>');
                ObjWidgetTitle.append(ObjWidgetTitleTr);

            var ObjWidgetTxtTd = $('<td></td>');
                ObjWidgetTxtTd.append(arrContent['txt']).css({'padding-left':'20px','padding-right':'20px'});

            var ObjWidgetTxtTr = $('<tr></tr>');
                ObjWidgetTxtTr.append(ObjWidgetTxtTd);

            var ObjWidgetTxt = $('<tbody></tbody>');
                ObjWidgetTxt.append(ObjWidgetTxtTr);

            var ObjWidget = $('<table class="ui-widget ui-state-default uid-corner-all"></table>');
                ObjWidget.append(ObjWidgetTitle);
                ObjWidget.append(ObjWidgetTxt);
                ObjWidget.css({"width":arrDimentions['width'],"height":arrDimentions['height']}).attr({"width":arrDimentions['width']});

            if(!ObjReturn)
                self.alertDialog(ObjWidget,arrContent['title'],false);
            else
                $(ObjReturn).html(ObjWidget);
            return ObjWidget;
        })
    }

    this.alertDialog = function(strText, strTitle, boolReload, fncBtnOk, waitFncBtnOk, intWidth){
        elementDialogAlert = (settings.objDialogAlert.length ==0)?$("<div></div>"):$("#"+settings.objDialogAlert);
        elementDialogAlert.html(strText);
        $(function() {
            if(!fncBtnOk)
                fncBtnOk = function (){ return true; };
            if(!waitFncBtnOk)
                waitFncBtnOk = false;
            if(!boolReload)
                boolReload = false;
            if(!intWidth)
                intWidth = 400;
            if(!strTitle) strTitle = "MENSAJE DEL SISTEMA";

            $(".ui-dialog-titlebar").show();

            elementDialogAlert.dialog({
                modal: true,
                title: strTitle,
                width: intWidth,
                closeOnEscape : false,
                position: { my: "center top", at: "center top", of: window },
                close: function(){
                    if(boolReload){
                        location.reload();
                    }
                    if (waitFncBtnOk){
                        if(fncBtnOk())
                            elementDialogAlert.dialog("close");
                    }
                    else{
                        fncBtnOk();
                        elementDialogAlert.dialog("close");
                    }
                    if(settings.objDialogAlert.length ==0) elementDialogAlert.remove();
                },
                buttons: {
                    Ok: function() {
                        if (waitFncBtnOk){
                            if(fncBtnOk())
                                elementDialogAlert.dialog("close");
                        }
                        else{
                            fncBtnOk();
                            elementDialogAlert.dialog("close");
                        }
                    }
                }
            });
        });
        return elementDialogAlert;
    }

    this.promptDialog=function (ObjTmp,title,fncBtnAccept,waitFncBtnAccept,fncBtnCancel, intColumns){
        if(!intColumns) intColumns = 2;
        var arrInputs = ( ObjTmp['inputs'] ) ? ObjTmp['inputs'] : false;
        var strTitle = ( ObjTmp['html'] ) ? "<b>" + ObjTmp['html'] + "</b>" : false;

        if(ObjTmp && (!arrInputs && !strTitle)){
            if(typeof(ObjTmp) == 'string')
                strTitle = ObjTmp;
            else
                arrInputs = ObjTmp;
        }

        if(!fncBtnAccept)
            fncBtnAccept = function (){ return true;}
        if(!fncBtnCancel)
            fncBtnCancel = function (){ return true;}
        if(!waitFncBtnAccept)
            waitFncBtnAccept = false;

        var ObjFrm = $('<table id="tblpromptDialog" align="center"></table>'),ObjFrmHead = $('<tr></tr>'),ObjFrmBody = $('<tr></tr>');

        if(strTitle){
            var ObjFrmTd = $("<td></td>");
                ObjFrmTd.html(strTitle);
            ObjFrmHead.html(ObjFrmTd);
            ObjFrm.append(ObjFrmHead);
            ObjFrmHead = $('<tr></tr>');
        }

        if(arrInputs){
            var i = 0;
            $.each(arrInputs,function (key,value){
                if(key){

                    if(!value['textarea'])
                        value['textarea']=false;
                    if(!value['attrs'])
                        value['attrs']={};
                    if(!value['title'])
                        value['title']="&nbsp;";

                    var ObjFrmTd = $("<td></td>");

                    ObjFrmTd.append(value['title']);
                    if(i == intColumns){
                        ObjFrmHead = $('<tr></tr>');
                    }

                    ObjFrmHead.append(ObjFrmTd);

                    var ObjFrmTd = $("<td></td>"), strInput = ( value['textarea'] ) ? "<textarea></textarea>" : "<input />";
                    var ObjInput = $(strInput);

                    ObjInput.attr({
                        "name":key,
                        "id":key
                    });


                    ObjInput.attr(value['attrs']);

                    ObjFrmTd.append(ObjInput);

                    if(i == intColumns){
                        ObjFrmBody = $('<tr></tr>')
                        i = 0;
                    }

                    ObjFrmBody.append(ObjFrmTd);
                    ObjFrm.append(ObjFrmHead).append(ObjFrmBody);
                    i++;
                }
            });
            //ObjFrm.append(ObjFrmHead).append(ObjFrmBody);
        }

        ObjFrm.dialog({
            modal: true,
            closeOnEscape : false,
            title: title,
            width:"auto",
            position: { my: "center top", at: "center top", of: window },
            close: function (){
                $(this).remove();
            },
            buttons: {
                Aceptar: function() {
                        if (waitFncBtnAccept){
                            if(fncBtnAccept())
                                $( this ).dialog("close");
                        }
                        else{
                            fncBtnAccept();
                            $( this ).dialog("close");
                        }
                },
                Cancelar: function (){
                    fncBtnCancel();
                    $( this ).dialog("close");
                }
            }

        });
        $(".ui-dialog-titlebar-close").click(function(){
            fncBtnCancel();
        })
    }

    this.drawSelectFromObject = function(strIdObjHtml, objJson, addOpctionEmpty, tags, value){

        if(!strIdObjHtml) strIdObjHtml = "select_javascript";
        if(!objJson)
            objJson = false;
        if(!addOpctionEmpty) addOpctionEmpty = false;
        if(!tags) tags = {};

        var arrValues = {};

        if(!value)
            arrValues[0] = true;
        else if(typeof(value) === 'object')
            arrValues = value;
        else if (typeof(value) !== 'boolean')
            arrValues[value] = true;

        var ObjHtmlSelect = $("<select class='field_selectbox'></select>").attr({"id":strIdObjHtml,"name":strIdObjHtml});
            ObjHtmlSelect.attr(tags)
                        .addClass("field_selectbox");


        if(addOpctionEmpty){
            var ObjOption = $("<option></option>");
            ObjOption.val(0);
            ObjOption.html("Seleccionar uno");
            ObjHtmlSelect.append(ObjOption);
        }
        if(objJson){
            $.each(objJson,function (key,value){
                if(typeof(value) === 'object'){
                    $.each(value, function(key2,value2){
                        var ObjOption = $("<option></option>");
                            ObjOption.val(key2);
                            ObjOption.html(value2);
                        if(arrValues[key])
                            ObjOption.attr({"selected":"selected"});
                        ObjHtmlSelect.append(ObjOption);
                    });
                }
                else{
                    var ObjOption = $("<option></option>");
                        ObjOption.val(key);
                        ObjOption.html(value);
                    if(arrValues[key])
                        ObjOption.attr({"selected":"selected"});
                    ObjHtmlSelect.append(ObjOption);
                }
            });
        }
        return ObjHtmlSelect;
    }

    this.createDatePicker = function(customParams){
        var dt = new Date();
        /*Se le suma 1 por que la funcion getMonth trae valores de 0 a 11*/
        var month = dt.getMonth() +1;
        var day = dt.getDate();
        var year = dt.getFullYear()
        newdate = year + "-" + month + "-" + day;

        var defaultsParams = {
            "id":"datepickerInput",
            "object":false,
            "contenedor":"",
            "fntCallback":function (){ return true;},
            "value":newdate,
            "datepicker":{
            },
            "blockPicker":false
        }
        customParams || ( customParams = {} );
        var params = $.extend({}, defaultsParams, customParams);

        var arrDateValue = Array();
        arrDateValue[0] = "";
        arrDateValue[1] = "";
        arrDateValue[2] = "";
        if(params.value != ""){
            arrDateValue = params.value.split("-");
        }

        var inputAnio = $("<input/>")
                    .attr({
                        "id":params.id+"_anio",
                        "name": params.id+"_anio",
                        "size":4,
                        "maxlength":4,
                        "type":"text",
                        "value":arrDateValue[0]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate = inputAnio.val() + "-" + arrSplit[1] + "-" + arrSplit[2];
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });

        if(params.blockPicker){
            inputAnio.attr("readonly","readonly");
        }
        var inputMes = $("<input/>")
                    .attr({
                        "id":params.id+"_mes",
                        "name": params.id+"_mes",
                        "size":2,
                        "maxlength":2,
                        "type":"text",
                        "value":arrDateValue[1]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate = arrSplit[0] + "-" + inputMes.val() + "-" + arrSplit[2];
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });
        if(params.blockPicker){
            inputMes.attr("readonly","readonly");
        }
        var inputdia = $("<input/>")
                    .attr({
                        "id":params.id+"_dia",
                        "name": params.id+"_dia",
                        "size":2,
                        "maxlength":2,
                        "type":"text",
                        "value":arrDateValue[2]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate =  arrSplit[0] + "-" + arrSplit[1] + "-" + inputdia.val();
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });
        if(params.blockPicker){
            inputdia.attr("readonly","readonly");
        }

        var inputHidden = typeof(params.object) == "object" ? params.object : $("<input/>");
        inputHidden.attr({
            id:params.id+"_hidDatepicker",
            name:params.id+"_hidDatepicker",
            "value":params.value/*,
            type:"hidden" //esto no fuinciona por ello lo de abajo*/
        })
        .css({
            "visibility":"hidden",
            "width":"0px"
        });
        
        inputHidden.setDate = function(strDate){
            var arrSplit = strDate.split("-");
                if(boolCheckDate(arrSplit[0], arrSplit[1], arrSplit[2] )){
                    getDocumentLayer(inputAnio.attr("id")).value = arrSplit[0];
                    getDocumentLayer(inputMes.attr("id")).value = arrSplit[1];
                    getDocumentLayer(inputdia.attr("id")).value = arrSplit[2];
                    getDocumentLayer(params.id+"_hidDatepicker").value = strDate;        
                }
        }

        var objDiv = ( (typeof(params.contenedor) != "object") ? ( (params.contenedor) ? $("#"+params.contenedor) : ( ( typeof(params.object) == "object" ) ? inputHidden.parent() : $("<div></div>") ) ) : params.contenedor );
        objDiv.append(inputdia);
        objDiv.append(" - ");
        objDiv.append(inputMes);
        objDiv.append(" - ");
        objDiv.append(inputAnio);
        objDiv.append(inputHidden);

        $(function(){
            inputHidden.datepicker({
                showOn: "button",
                buttonImage: "images/office-calendar.png",
                buttonImageOnly: true,
                dateFormat: 'yy-mm-dd',
                showAnim: "drop",
                dayNames: ["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"],
                dayNamesMin: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"],
                dayNamesShort: ["Dom","Lun","Mar","Mie","Jue","Vie","Sab"],
                monthNamesShort: ["Ene","Feb","Mar","Abr", "May","Jun","Jul","Ago", "Sep","Oct","Nov","Dic"],
                monthNames: ["Enero","Febrero","Marzo","Abril", "Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
                onSelect: function(dateText,inst){
                    var arrSplit = dateText.split("-");
                    inputAnio.val(arrSplit[0]);
                    inputMes.val(arrSplit[1]);
                    inputdia.val(arrSplit[2]);
                    if(params.fntCallback) params.fntCallback();
                }
            });

            $.each(params.datepicker, function(key, value){
                $( "#"+params.id+"_hidDatepicker").datepicker( "option", key, value);
                inputHidden.datepicker( "option", key, value);
            });

            if(params.value != ""){
                inputHidden.datepicker( "setDate", params.value);
            }


            var currentDate = (!inputHidden.datepicker("getDate"))?newdate:inputHidden.datepicker("getDate");
            if(params.blockPicker){
                inputHidden.datepicker( "option", "maxDate", currentDate );
                inputHidden.datepicker( "option", "minDate", currentDate );
            }

        });
        return inputHidden
    }
};


/*Para el uso correcto de la function incluir el plugin de noticeAdd de jquery
* o = Objeto de jquery eje: $("#tuobjeto")
* min = minimo de caracteres
* max = maximo de caracteres
* n = algun titulo para el objeto
* mostrar o no el error
*/
var checkLength = function( o, min, max, n, boolError ) {
    if(!n) n = false;
    if(!boolError) boolError = false;
    var boolMax = (max > 0)?true:false;
    var strTitle = "";
        if(n) var strTitle = n;
    if(boolMax){
        if (max == 0 || (o.val().length > max || o.val().length < min)) {
            if(boolError){
                jQuery.noticeAdd({text: "<b>El tamaño del campo " + strTitle + " tiene que ser entre "+min+" y "+max+". !</b><br><br>",type:"warning",stay:false});
            }
            return false;
        }
        else{
            return true;
        }
    }
    else{
        if((o.val().length < min)){
            if(boolError){
                jQuery.noticeAdd({text: "<b>El tamaño minimo del campo " + strTitle + " tiene que ser "+min+" !</b><br><br>",type:"warning",stay:false});
            }
            return false;
        }
        else {
            return true;
        }
    }
}

var outInts=function(number, boolAddComma){
    if (number.length <= 3)
        return (number == '' ? '0' : number);
    else {
        var mod = number.length%3;
        var output = (mod == 0 ? '' : (number.substring(0,mod)));
        for (i=0 ; i < Math.floor(number.length/3) ; i++) {
            if (((mod ==0) && (i ==0)) || !boolAddComma)
                output+= number.substring(mod+3*i,mod+3*i+3);
            else
                output+= ',' + number.substring(mod+3*i,mod+3*i+3);
        }
        return (output);
    }
}

var  outCents = function(amount,intDec) {
    if (!intDec)
            intDec = 2;
    var intTenExp =    Math.pow(10,intDec);
    amount = Math.round( ( (amount) - Math.floor(amount) ) *intTenExp);
    var strZeros = "";
    for (i=1;i<=intDec;i++){
            if (amount < Math.pow(10,i-1))
                    strZeros+="0";
    }
    if (amount==0)
        return "."+strZeros;
    else
        return "."+strZeros+amount;
}

var format_number = function(monto, decimales){
    var comas  =/,/ig;
    var strTotal = JavaScriptTextTrim(monto) + '';

    if (!decimales) decimales = 0;
    strTotal = strTotal.replace(comas,'');
    strTotal = strTotal * 1;

    var intTenExp =    Math.pow(10,decimales);
    strTotal = Math.round(strTotal * intTenExp)/intTenExp;
    var addMinus = false;
    if (strTotal < 0 ){
            strTotal = Math.abs(strTotal);
            addMinus = true;
    }
    return ((addMinus ? '-':'')+(outInts(Math.floor(strTotal-0) + '', true) + outCents(strTotal - 0,decimales)));
}

var format_number_scomas = function(monto, intDec){
    var strTotal = "";
    var comas  =/,/ig;
    if (!intDec) intDec = 2;
    var intTenExp =    Math.pow(10,intDec);

    monto = JavaScriptTextTrim(monto);
    monto = monto.replace(comas,'');
    monto = Math.round(monto * intTenExp)/intTenExp;

    strTotal = monto + "";
    strTotal = strTotal.replace(comas,'');

    return outInts(Math.floor(strTotal-0) + '', false) + outCents(strTotal - 0, intDec);
}

function JavaScriptTextTrim(str) {
	var whitespace = new String(" \t\n\r");
	var s = new String(str);

	if (whitespace.indexOf(s.charAt(0)) != -1) {
		var j=0, i = s.length;
		while (j < i && whitespace.indexOf(s.charAt(j)) != -1) j++;
		s = s.substring(j, i);
	}
	if (whitespace.indexOf(s.charAt(s.length-1)) != -1) {
		var i = s.length - 1;
		while (i >= 0 && whitespace.indexOf(s.charAt(i)) != -1) i--;
		s = s.substring(0, i+1);
	}
	return s;
}

var validar_entero = function(intvalue){
    if(!intvalue) intvalue = '';
    var RegExPattern = /^(?:\+|-)?\d+$/;
    if ((intvalue.match(RegExPattern)) && (intvalue !='')) {
        return intvalue;
    } else {
         return "";
    }
}

function validarEntero(intvalue){
    var RegExPattern = /^(?:\+|-)?\d+$/;
    if ((intvalue.match(RegExPattern)) && (intvalue !='')) {
        return intvalue;
    } else {
         return "";
    }

}

/*
* @description Envia un ajax y espera a que devuelva los datos, si ocurre un error o si el ajax devuelve la posición "status" diferente de "ok" entonces devuelve un false, de lo contrario devuelve la data del ajax.
 * @important Es necesario Jquery y la libreria JqueryUi para que funcione de lo contrario dara errores.
 * @returns object
 */
var MD5 = function (string) {
/**
*
*  MD5 (Message-Digest Algorithm)
*  http://www.webtoolkit.info/
*
**/
    function RotateLeft(lValue, iShiftBits) {
        return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
    }

    function AddUnsigned(lX,lY) {
        var lX4,lY4,lX8,lY8,lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
        if (lX4 & lY4) {
            return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
        }
        if (lX4 | lY4) {
            if (lResult & 0x40000000) {
                return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
            } else {
                return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
            }
        } else {
            return (lResult ^ lX8 ^ lY8);
        }
     }

     function F(x,y,z) { return (x & y) | ((~x) & z); }
     function G(x,y,z) { return (x & z) | (y & (~z)); }
     function H(x,y,z) { return (x ^ y ^ z); }
    function I(x,y,z) { return (y ^ (x | (~z))); }

    function FF(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    function GG(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    function HH(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    function II(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    function ConvertToWordArray(string) {
        var lWordCount;
        var lMessageLength = string.length;
        var lNumberOfWords_temp1=lMessageLength + 8;
        var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
        var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
        var lWordArray=Array(lNumberOfWords-1);
        var lBytePosition = 0;
        var lByteCount = 0;
        while ( lByteCount < lMessageLength ) {
            lWordCount = (lByteCount-(lByteCount % 4))/4;
            lBytePosition = (lByteCount % 4)*8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount-(lByteCount % 4))/4;
        lBytePosition = (lByteCount % 4)*8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
        lWordArray[lNumberOfWords-2] = lMessageLength<<3;
        lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
        return lWordArray;
    };

    function WordToHex(lValue) {
        var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
        for (lCount = 0;lCount<=3;lCount++) {
            lByte = (lValue>>>(lCount*8)) & 255;
            WordToHexValue_temp = "0" + lByte.toString(16);
            WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
        }
        return WordToHexValue;
    };

    function Utf8Encode(string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    };

    var x=Array();
    var k,AA,BB,CC,DD,a,b,c,d;
    var S11=7, S12=12, S13=17, S14=22;
    var S21=5, S22=9 , S23=14, S24=20;
    var S31=4, S32=11, S33=16, S34=23;
    var S41=6, S42=10, S43=15, S44=21;

    string = Utf8Encode(string);

    x = ConvertToWordArray(string);

    a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

    for (k=0;k<x.length;k+=16) {
        AA=a; BB=b; CC=c; DD=d;
        a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
        d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
        c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
        b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
        a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
        d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
        c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
        b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
        a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
        d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
        c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
        b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
        a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
        d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
        c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
        b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
        a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
        d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
        c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
        b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
        a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
        d=GG(d,a,b,c,x[k+10],S22,0x2441453);
        c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
        b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
        a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
        d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
        c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
        b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
        a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
        d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
        c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
        b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
        a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
        d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
        c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
        b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
        a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
        d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
        c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
        b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
        a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
        d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
        c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
        b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
        a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
        d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
        c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
        b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
        a=II(a,b,c,d,x[k+0], S41,0xF4292244);
        d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
        c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
        b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
        a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
        d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
        c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
        b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
        a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
        d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
        c=II(c,d,a,b,x[k+6], S43,0xA3014314);
        b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
        a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
        d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
        c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
        b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
        a=AddUnsigned(a,AA);
        b=AddUnsigned(b,BB);
        c=AddUnsigned(c,CC);
        d=AddUnsigned(d,DD);
    }

    var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

    return temp.toLowerCase();
}

jQuery.fn.jNumber = function(intDecimal){
    if(!intDecimal) intDecimal = 2;
    $(this).change(function(){
        var value = format_number($(this).val(),intDecimal);
        value = value.replace(",","");
        var arrVal = value.split(".");
        var boolOk = true;
        for( var i in arrVal){
            var intvalue = (parseInt(arrVal[i] * 1));
            if (isNaN(intvalue)){
                boolOk = false;
            }
        }
        if(boolOk){
            if(value<0) $(this).val(value).css({"color":"red"});
            else $(this).val(value).css({"color":"black"});
        }
        else{
            $(this).val("");
            $(this).focus();
            $(this).attr("palceholder","Ingrese un valor numerico");
        }
    });
}

function serializeObj (ObjTmp){
    var _RETURN = "";

    if(!ObjTmp)
        ObjTmp = false;

    var NewObj = jQuery.extend({}, ObjTmp);   

    $.each(NewObj,function (key,value){
        if(typeof(value) == "string")
            _RETURN  += "&" + key + "=" + value;
    });

    return _RETURN;
}

/*Ejemplo de uso de la clase
    //Parametro opcional
*   objTEST = new drawWidgets({ objDialogAlert: "dialog" });
    var arrWidgets = new Array();
        arrWidgets['title']='Localidad aun no configurada:&nbsp;';
        arrWidgets['txt']='La localidad <i>jdjdjd</i>, aun no se a configurado.';


    //objTEST.alertDialog("test","test tittle",false);
    objTEST.drawMesaggeWidget(arrWidgets,{"width":"350px;"});
*/
var drawWidgets = function(customSettings){
    var self = this;
    var elementLoading;
    var elementDialogMesagge;
    var elementDialogAlert;
    var defaults = {
        objLoading: "",
        objDialogMesagge: "",
        objDialogAlert: ""
    }

    customSettings || ( customSettings = {} );
    var settings = $.extend({}, defaults, customSettings);

    this.setOptions = function(customSettings){
        customSettings || ( customSettings = {} );
        settings = $.extend({}, defaults, customSettings);
    }

    this.getOptions = function(){
        return settings;
    }

    this.openLoading = function(boolModal){
        if(!boolModal) boolModal = false;
        var objDialog = self.dialogoCargando(false,boolModal);
        return objDialog;
    } 
    
    this.openProgressBar = function(boolModal){
        if(!boolModal) boolModal = false;
        var objDialog = self.dialogoProgressBar(false,boolModal);
        return objDialog;
    }

    this.dialogoCargando = function (boolCerrar,boolModal){
        if(!boolModal)boolModal=true;
        if(!boolCerrar)boolCerrar=false;
        var objCargando = document.getElementById("div-cargando");
        if (boolCerrar && !objCargando)
            return false;
        else if(!objCargando)
            objCargando = $("<div id='div-cargando' style='text-align:center;'></div>");
        else
            objCargando = $(objCargando);

        if(boolCerrar){
            objCargando.dialog("close");
            return objCargando;
        }

        var MyInterval;
        var objImg = $("<img src=\"images/loading.gif\" width='100px' heigth='98px' style='vertical-align: middle; display: table-cell; text-align:center; margin:auto auto;'/>")
        objCargando
        .html(objImg)
        .append("<br><span id='global_label_loading' style='position:absolute;left:55px;font-size:13px;text-align:center;' ><b>Cargando</b></span>");
        
        objCargando
        .hide()
        .dialog({
            autoOpen: false,
            closeOnEscape : false,
            dialogClass: "no-close",
            modal: boolModal,
            height: 180,
            width:180,
            resizable: false,
            close: function( event, ui ) {
                $(this).removeClass("no-close");
                $(this).hide("fade");
                clearInterval(MyInterval);
                objCargando.dialog( "destroy" );
                objCargando.remove();
            },
            open: function( event, ui ) {
                var objBarClose = $(this).prev();
                    objBarClose.remove();
                $(this).show("fade");
            }
        });        
        objCargando.dialog("open");
        var i = 1;
        MyInterval = setInterval(function(){
            console.log(i)
            if(i>3){
                i=1;
            }
            var stringLoad = "<b>Cargando";
            for(var j=1;j<=i;j++){
                stringLoad += ".";
            }
            stringLoad += "</b>";
            $("#global_label_loading").html(stringLoad);                        
            i++;
        }, 750);

        return objCargando;
    }
    
    this.dialogoProgressBar = function (boolCerrar,boolModal){
        var objCargando = self.dialogoCargando(boolCerrar,boolModal);
        return objCargando;
    }

    this.closeLoading= function(){
        var objDialog = self.dialogoCargando(true);
        return objDialog;
    }
    
    this.closeProgressBar= function(){
        var objDialog = self.dialogoProgressBar(true);
        return objDialog;
    }

    this.drawMesaggeWidget = function(arrContent,arrDimentions, strIDobjReturn){
        $(function(){
            ObjReturn = getDocumentLayer(settings.objDialogMesagge);
            if(!strIDobjReturn) strIDobjReturn=false;

            if(strIDobjReturn){
                ObjReturn = getDocumentLayer(strIDobjReturn);
            }

            if(!arrContent) arrContent = new Array();
            arrContent['txt']=((!arrContent['txt'])?((arrContent['msj'])?arrContent['msj']:'Hello World!'):arrContent['txt']);
            arrContent['title']=((!arrContent['title'])?"":"<i style='color:#003C77'>"+arrContent['title']+"</i>");


            if(!arrDimentions) arrDimentions = new Array();
            if(!arrDimentions['width'])arrDimentions['width']=(ObjReturn)?'350px':'auto';
            if(!arrDimentions['height'])arrDimentions['height']='auto';

            var ObjTr = $('<tr></tr>');
            var ObjTd = $('<td></td>');
            var ObjTh = $('<th></th>');

            var ObjWidgetTitleTd = $('<th align="left"></th>');
                ObjWidgetTitleTd.append('<span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>');
                ObjWidgetTitleTd.append(arrContent['title']);

            var ObjWidgetTitleTr = $('<tr></tr>');;
                ObjWidgetTitleTr.append(ObjWidgetTitleTd);

            var ObjWidgetTitle = $('<thead></thead>');
                ObjWidgetTitle.append(ObjWidgetTitleTr);

            var ObjWidgetTxtTd = $('<td></td>');
                ObjWidgetTxtTd.append(arrContent['txt']).css({'padding-left':'20px','padding-right':'20px'});

            var ObjWidgetTxtTr = $('<tr></tr>');
                ObjWidgetTxtTr.append(ObjWidgetTxtTd);

            var ObjWidgetTxt = $('<tbody></tbody>');
                ObjWidgetTxt.append(ObjWidgetTxtTr);

            var ObjWidget = $('<table class="ui-widget ui-state-default uid-corner-all"></table>');
                ObjWidget.append(ObjWidgetTitle);
                ObjWidget.append(ObjWidgetTxt);
                ObjWidget.css({"width":arrDimentions['width'],"height":arrDimentions['height']}).attr({"width":arrDimentions['width']});

            if(!ObjReturn)
                self.alertDialog(ObjWidget,arrContent['title'],false);
            else
                $(ObjReturn).html(ObjWidget);
            return ObjWidget;
        })
    }

    this.alertDialog = function(strText, strTitle, boolReload, fncBtnOk, waitFncBtnOk, intWidth, intHeight, boolResizable){
        $(function() {
            elementDialogAlert = (settings.objDialogAlert.length ==0)?$("<div></div>"):$("#"+settings.objDialogAlert);
            if(!fncBtnOk)
                fncBtnOk = function (){ return true; };
            if(!waitFncBtnOk)
                waitFncBtnOk = false;
            if(!boolReload)
                boolReload = false;
            if(!intWidth)
                intWidth = "auto";
            if(!intHeight)
                intHeight = "auto";
            if(!strTitle) strTitle = "MENSAJE DEL SISTEMA";

            $(".ui-dialog-titlebar").show();
            elementDialogAlert.html(strText);
            elementDialogAlert.dialog({
                modal: true,
                title: strTitle,
                width: intWidth,
                height: intHeight,
                resizable: boolResizable,
                closeOnEscape : false,
                close: function(){
                    if(boolReload){
                        location.reload();
                    }
                        if (waitFncBtnOk){
                            fncBtnOk();
                        }
                        else{
                            fncBtnOk();
                        }
                    if(settings.objDialogAlert.length ==0) elementDialogAlert.remove();
                },
                buttons: {
                    Ok: function() {
                        if (waitFncBtnOk){
                                elementDialogAlert.dialog("close");
                        }
                        else{
                            elementDialogAlert.dialog("close");
                        }
                    }
                }
            });

        });
    }

    this.promptDialog=function (ObjTmp,title,fncBtnAccept,waitFncBtnAccept,fncBtnCancel, intColumns){
        if(!intColumns) intColumns = 2;
        var arrInputs = ( ObjTmp['inputs'] ) ? ObjTmp['inputs'] : false;
        var strTitle = ( ObjTmp['html'] ) ? "<b>" + ObjTmp['html'] + "</b>" : false;

        if(ObjTmp && (!arrInputs && !strTitle)){
            if(typeof(ObjTmp) == 'string')
                strTitle = ObjTmp;
            else
                arrInputs = ObjTmp;
        }

        if(!fncBtnAccept)
            fncBtnAccept = function (objJsonObjs){ return true;}
        if(!fncBtnCancel)
            fncBtnCancel = function (){ return true;}
        if(!waitFncBtnAccept)
            waitFncBtnAccept = false;


        var objJsonObjs = {};

        var ObjFrm = $('<table id="tblpromptDialog" align="center"></table>'),ObjFrmHead = $('<tr></tr>'),ObjFrmBody = $('<tr></tr>');

        if(strTitle){
            var ObjFrmTd = $("<td></td>");
                ObjFrmTd.html(strTitle).attr("colspan",intColumns);
            ObjFrmHead.html(ObjFrmTd);
            ObjFrm.append(ObjFrmHead);
            ObjFrmHead = $('<tr></tr>');
        }

        if(arrInputs){
            var i = 0;
            var intWidth = (100/intColumns);
            $.each(arrInputs,function (key,value){
                if(key){

                    if(!value['textarea'])
                        value['textarea']=false;
                    if(!value['attrs'])
                        value['attrs']={};
                    if(!value['title'])
                        value['title']="&nbsp;";

                    var ObjFrmTd = $("<td></td>").attr("width",intWidth+"%");

                    ObjFrmTd.append(value['title']);
                    if(i == intColumns){
                        ObjFrmHead = $('<tr></tr>');
                    }

                    ObjFrmHead.append(ObjFrmTd);

                    var ObjFrmTd = $("<td></td>"), strInput = ( value['textarea'] ) ? "<textarea></textarea>" : "<input />";
                    var ObjInput = $(strInput);

                    ObjInput.attr({
                        "name":key,
                        "id":key
                    });

                    objJsonObjs[key] = ObjInput;

                    ObjInput.attr(value['attrs']);

                    ObjFrmTd.append(ObjInput).attr("width",intWidth+"%");

                    if(i == intColumns){
                        ObjFrmBody = $('<tr></tr>')
                        i = 0;
                    }

                    ObjFrmBody.append(ObjFrmTd);
                    ObjFrm.append(ObjFrmHead).append(ObjFrmBody);
                    i++;
                }
            });
            //ObjFrm.append(ObjFrmHead).append(ObjFrmBody);
        }

        ObjFrm.dialog({
            modal: true,
            closeOnEscape : false,
            title: title,
            width:"auto",
            position: 'center',
            close: function (){
                $(this).remove();
            },
            buttons: {
                Aceptar: function() {
                        if (waitFncBtnAccept){
                            if(fncBtnAccept(objJsonObjs))
                                $( this ).dialog("close");
                        }
                        else{
                            fncBtnAccept(objJsonObjs);
                            $( this ).dialog("close");
                        }
                },
                Cancelar: function (){
                    fncBtnCancel();
                    $( this ).dialog("close");
                }
            }

        });
        $(".ui-dialog-titlebar-close").click(function(){
            fncBtnCancel();
        });
        
        return objJsonObjs;
    }

    this.drawSelectFromObject = function(strIdObjHtml, objJson, addOpctionEmpty, tags, value, strLabelEmpty){

        if(!strIdObjHtml) strIdObjHtml = "select_javascript";
        if(!objJson)
            objJson = false;
        if(!addOpctionEmpty) addOpctionEmpty = false;
        if(!tags) tags = {};

        var arrValues = {};

        if(!value)
            arrValues[0] = true;
        else if(typeof(value) === 'object')
            arrValues = value;
        else if (typeof(value) !== 'boolean')
            arrValues[value] = true;
        
        if(typeof(strLabelEmpty) === "undefined"){
            strLabelEmpty = "Seleccionar uno";
        }
        
        var ObjHtmlSelect = $("<select class='field_selectbox'></select>").attr({"id":strIdObjHtml,"name":strIdObjHtml});
            ObjHtmlSelect.attr(tags)
                        .addClass("field_selectbox");


        if(addOpctionEmpty){
            var ObjOption = $("<option></option>");
            ObjOption.val(0);
            ObjOption.html(strLabelEmpty);
            ObjHtmlSelect.append(ObjOption);
        }
        if(objJson){
            $.each(objJson,function (key,value){
                var ObjOption = $("<option></option>");
                    ObjOption.val(key);
                    ObjOption.html(value);
                if(arrValues[key])
                    ObjOption.attr({"selected":"selected"});
                ObjHtmlSelect.append(ObjOption);
            });
        }
        return ObjHtmlSelect;
    }

    this.createDatePicker = function(customParams){
        var dt = new Date();
        /*Se le suma 1 por que la funcion getMonth trae valores de 0 a 11*/
        var month = dt.getMonth() +1;
        var day = dt.getDate();
        var year = dt.getFullYear()
        newdate = year + "-" + month + "-" + day;

        var defaultsParams = {
            "id":"datepickerInput",
            "object":false,
            "contenedor":"",
            "fntCallback":function (){ return true;},
            "value":newdate,
            "datepicker":{
            },
            "blockPicker":false
        }
        customParams || ( customParams = {} );
        var params = $.extend({}, defaultsParams, customParams);

        var arrDateValue = Array();
        arrDateValue[0] = "";
        arrDateValue[1] = "";
        arrDateValue[2] = "";
        if(params.value != ""){
            arrDateValue = params.value.split("-");
        }

        var inputAnio = $("<input/>")
                    .attr({
                        "id":params.id+"_anio",
                        "name": params.id+"_anio",
                        "size":4,
                        "maxlength":4,
                        "type":"text",
                        "value":arrDateValue[0]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate = inputAnio.val() + "-" + arrSplit[1] + "-" + arrSplit[2];
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });

        if(params.blockPicker){
            inputAnio.attr("readonly","readonly");
        }
        var inputMes = $("<input/>")
                    .attr({
                        "id":params.id+"_mes",
                        "name": params.id+"_mes",
                        "size":2,
                        "maxlength":2,
                        "type":"text",
                        "value":arrDateValue[1]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate = arrSplit[0] + "-" + inputMes.val() + "-" + arrSplit[2];
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });
        if(params.blockPicker){
            inputMes.attr("readonly","readonly");
        }
        var inputdia = $("<input/>")
                    .attr({
                        "id":params.id+"_dia",
                        "name": params.id+"_dia",
                        "size":2,
                        "maxlength":2,
                        "type":"text",
                        "value":arrDateValue[2]
                    })
                    .addClass("field_textbox")
                    .change(function(){
                        var arrSplit = inputHidden.val().split("-");
                        var strDate =  arrSplit[0] + "-" + arrSplit[1] + "-" + inputdia.val();
                        inputHidden.val(strDate);
                        if(params.fntCallback) params.fntCallback();
                    });
        if(params.blockPicker){
            inputdia.attr("readonly","readonly");
        }

        var inputHidden = typeof(params.object) == "object" ? params.object : $("<input/>");
        inputHidden.attr({
            id:params.id+"_hidDatepicker",
            name:params.id+"_hidDatepicker",
            "value":params.value/*,
            type:"hidden" //esto no fuinciona por ello lo de abajo*/
        })
        .css({
            "visibility":"hidden",
            "width":"0px"
        });
        
        inputHidden.setDate = function(strDate){
            
            if($.trim(strDate).length > 0){
                
                var arrSplit = strDate.split("-");
                
                if(boolCheckDate(arrSplit[0], arrSplit[1], arrSplit[2] )){
                    getDocumentLayer(inputAnio.attr("id")).value = arrSplit[0];
                    getDocumentLayer(inputMes.attr("id")).value = arrSplit[1];
                    getDocumentLayer(inputdia.attr("id")).value = arrSplit[2];
                    getDocumentLayer(params.id+"_hidDatepicker").value = strDate;        
                }
                
            }
            
        }

        var objDiv = ( (typeof(params.contenedor) != "object") ? ( (params.contenedor) ? $("#"+params.contenedor) : ( ( typeof(params.object) == "object" ) ? inputHidden.parent() : $("<div></div>") ) ) : params.contenedor );
        objDiv.append(inputdia);
        objDiv.append(" - ");
        objDiv.append(inputMes);
        objDiv.append(" - ");
        objDiv.append(inputAnio);
        objDiv.append(inputHidden);

        $(function(){
            inputHidden.datepicker({
                showOn: "button",
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true,
                dateFormat: 'yy-mm-dd',
                showAnim: "drop",
                onSelect: function(dateText,inst){
                    var arrSplit = dateText.split("-");
                    inputAnio.val(arrSplit[0]);
                    inputMes.val(arrSplit[1]);
                    inputdia.val(arrSplit[2]);
                    if(params.fntCallback) params.fntCallback();
                }
            });

            $.each(params.datepicker, function(key, value){
                $( "#"+params.id+"_hidDatepicker").datepicker( "option", key, value);
                inputHidden.datepicker( "option", key, value);
            });

            if(params.value != ""){
                inputHidden.datepicker( "setDate", params.value);
            }


            var currentDate = (!inputHidden.datepicker("getDate"))?newdate:inputHidden.datepicker("getDate");
            if(params.blockPicker){
                inputHidden.datepicker( "option", "maxDate", currentDate );
                inputHidden.datepicker( "option", "minDate", currentDate );
            }

        });
        return inputHidden
    }
    
    this.drawTabsPanel = function(arrCustomParams){
        
        var arrDefaultsParams = {
            "id":"tabs",
            "title_delete":"Eliminar",
            "contenedor":"",
            "boolDeleteTabs":false
        }
        
        arrCustomParams || ( arrCustomParams = {} );
        var arrParams = $.extend({}, arrDefaultsParams, arrCustomParams);
        
        var objDivContainer = ( (typeof(arrParams.contenedor) != "object") ? ( (arrParams.contenedor) ? $("#"+arrParams.contenedor) : $("<div></div>") ) : arrParams.contenedor );
        
        var objUlPanel = $("<ul id='menu_ul_"+arrParams.id+"' ></ul>");
        var objTabsPanel = $("<div id='menu_"+arrParams.id+"' class='h-tab'></div>").append(objUlPanel);
        objDivContainer.append(objTabsPanel);
        objTabsPanel.tabs();
        
        var intTotalTabs = 0;
        var intCounterTabs = 1;
        
        if(arrParams.boolDeleteTabs){
            objTabsPanel.delegate( "span.ui-icon-close", "click", function() {
                var strIdSpan = (this.id).replace("span_#","");
                var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
                $( "#" + panelId ).remove();
                $("#"+strIdSpan).remove();
                objTabsPanel.tabs("destroy");
                objTabsPanel.tabs();
                intTotalTabs--;
            });
        }
        
        objTabsPanel.addPannelTab = function(objContentTab, arrCustomParamsTab){
            
            var strDivDeleteTab = (arrParams.boolDeleteTabs)?"<div><span class='ui-icon ui-icon-close' id='span_#{href}' role='presentation' title='"+arrParams.title_delete+"'></span></div>":"";
            var tabTemplate = "<li style='padding-right:15px;' ><a href='#{href}' id='#{tabid}'>#{label}</a>"+strDivDeleteTab+"</li>";
            
            var arrDefaultsParamsTab = {
                "name":"tab_"+arrParams.id+"_"+intCounterTabs
            }
            
            arrCustomParamsTab || ( arrCustomParamsTab = {} );
            var arrParamsTab = $.extend({}, arrDefaultsParamsTab, arrCustomParamsTab);
            
            var tabid = "tabid-"+arrParams.id+"_"+intCounterTabs;
            var label = arrParamsTab.name;
            var id = "tabs-"+arrParams.id+"_"+intCounterTabs;
            var li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ).replace(/#\{tabid\}/g, tabid) );
            
            //find(".ui-tabs-nav")
            objUlPanel.append(li);
            
            var objDiv = $("<div id='" + id + "'></div>").append(objContentTab);
            objTabsPanel.append(objDiv);
            
            objTabsPanel.tabs("destroy");
            objTabsPanel.tabs();
            
            intCounterTabs++;
            intTotalTabs++;
            
            return objDiv;
        }
        
        return objTabsPanel;       
    }
    
    this.createDynamicTable = function(arrCustomParams){
        
        var mySelf = this;
        
        var intMyKey = (typeof mySelf.intKeyDynamicTable == "undefined") ? mySelf.intKeyDynamicTable = 0 : mySelf.intKeyDynamicTable += 1;
        
        var arrDefaultsParams = {
            "id":"table_dynamic_"+intMyKey,
            "contenedor":false,
            "columnas":4,
            "attr":{},
            "css":{}
        }
        
        arrCustomParams || ( arrCustomParams = {} );
        var arrParams = $.extend({}, arrDefaultsParams, arrCustomParams);
        
        var objMyTable = $('<table cellspacing="2" cellpadding="2" border="0" align="left" valing="top"></table>');
            objMyTable.css(arrParams.css);
            objMyTable.attr(arrParams.attr);
            objMyTable.attr({"id":arrParams.id});
        
        var objMyLastTr = false;
        var intActualFields = 0;
        
        var boolMyLastTrOk = function (){ return (objMyLastTr /*|| ( intActualFields != 0 && intActualFields != arrDefaultsParams.columnas);*/)}
        
        var f = function(o){var t = this;var s = $(t);s.b = s.bind;$.each(o,function(k,e) {if(typeof e == typeof f){s.b(k,e)};});};
        $.fn.addEvents = f;
        
        /*(function (j){
            
        })($);*/
        
        this.addField = function(arrFieldParams){
            if(!boolMyLastTrOk()){
                objMyLastTr = mySelf.addRow();
            }
            var objMyTd = $("<td></td>");
            if(arrFieldParams){
                arrFieldParams.key || (arrFieldParams.key = false);
                arrFieldParams.attr || (arrFieldParams.attr = {}) ;
                arrFieldParams.css || (arrFieldParams.css = {}) ;
                arrFieldParams.events || (arrFieldParams.events = {});
                arrFieldParams.title || (arrFieldParams.title = "");
                arrFieldParams.object || (arrFieldParams.object = false);
                arrFieldParams.attr.dynamicTableType = "field";
                arrFieldParams.attr.dynamicTableId = arrFieldParams.key;
                objMyTd.css(arrFieldParams.css);
                objMyTd.attr(arrFieldParams.attr);
                objMyTd.html(arrFieldParams.title);
                objMyTd.get(0).dynamicTable = {
                    pTd : objMyTd,
                    pTr : objMyLastTr
                };
                if(arrFieldParams.events){
                    objMyTd.addEvents(arrFieldParams.events);
                }
                if(arrFieldParams.object){
                    arrFieldParams.object.tag || (arrFieldParams.object.tag = "<input />");
                    arrFieldParams.object.attr || (arrFieldParams.object.attr = {});
                    arrFieldParams.object.css || (arrFieldParams.object.css = {});
                    arrFieldParams.object.events || (arrFieldParams.object.events = false);
                    arrFieldParams.object.addlabel || (arrFieldParams.object.addlabel = false);
                    arrFieldParams.object.attr.dynamicTableId = arrFieldParams.object.attr.id;
                    arrFieldParams.object.attr.dynamicTableType = "object";
                    
                    var objTag = $(arrFieldParams.object.tag);
                    var objHtml = objTag;
                    if(arrFieldParams.object.addlabel){
                        var objLabel = $("<label></label>");
                        objLabel.html(arrFieldParams.title);
                        objLabel.append(objTag);
                        objHtml = objLabel;
                        objMyTd.html("");
                    }
                    objTag.css(arrFieldParams.object.css);
                    objTag.attr(arrFieldParams.object.attr);
                    objTag.get(0).dynamicTable = {
                        pTd : objMyTd,
                        pTr : objMyLastTr
                    };
                    if(arrFieldParams.object.events){
                        objTag.addEvents(arrFieldParams.object.events);
                    }
                    objMyTd.append(objHtml);
                }
            }
            objMyLastTr.append(objMyTd);
            return objMyTd;
        }
        
        this.addRow = function(arrRowParams){
            var objMyTr = objMyLastTr;
            if(!boolMyLastTrOk()){
                objMyTr = $("<tr></tr>");
                objMyTr.get(0).dynamicTable = {
                    pTr : objMyTr
                };
                objMyLastTr = objMyTr;
            }
            if(arrRowParams){
                arrRowParams.key || (arrRowParams.key = false);
                arrRowParams.attr || (arrRowParams.attr = {}) ;
                arrRowParams.css || (arrRowParams.css = {}) ;
                arrRowParams.events || (arrRowParams.events = false);
                arrRowParams.fields || (arrRowParams.fields = false);
                arrRowParams.boolRowDelete || (arrRowParams.boolRowDelete = false);
                arrRowParams.attr.dynamicTableType = "row";
                arrRowParams.attr.dynamicTableId = arrRowParams.key;
                objMyTr.attr(arrRowParams.attr);
                objMyTr.css(arrRowParams.css);
                if(arrRowParams.events)
                    objMyTr.addEvents(arrRowParams.events);
                arrRowParams.events
                if(arrRowParams.fields){
                    $.each(arrRowParams.fields,function (key,arrFieldParams) {
                        arrFieldParams.key || (arrFieldParams.key = key);
                        var objMyTd = mySelf.addField(arrFieldParams);
                    });
                    
                    if(arrRowParams.boolRowDelete){
                        var opOnDelete = function(){return true;};
                        if(typeof arrRowParams.boolRowDelete == "function")
                            opOnDelete = arrRowParams.boolRowDelete;
                        var arrFieldParams = {};
                            arrFieldParams.object = {};
                            arrFieldParams.object.tag = "<img />"
                            arrFieldParams.object.attr = {
                                "src":"images/delete.png"
                            }
                            arrFieldParams.object.events = {};
                            arrFieldParams.object.events.click = function(){
                                mySelf.delRow($(this));
                                opOnDelete();
                            };
                        mySelf.addField(arrFieldParams);
                    }
                }
            }
            objMyLastTr = false;
            objMyTable.append(objMyTr);
            return objMyTr;
        }
        
        this.addRows = function(arrRowsParams){
            if(!arrRowsParams)
                return false;
            $.each(arrRowParams.rows,function (key,arrRowParams) {
                arrRowParams.key || (arrRowParams.key = key);
                var objMyTr = mySelf.addRow(arrRowParams);
            });
            return objMyTable;
        }
        
        this.delRow = function(objHtml){
            if(!objHtml)
                return false;
            var objRow = false;
            if(objHtml.get(0).dynamicTable){
                if(objHtml.get(0).dynamicTable.pTr){
                    objRow = objHtml.get(0).dynamicTable.pTr;
                }
            }
            if(!objRow){
                return false;
            }
            else{
                if(objRow.remove)
                    objRow.remove();
            }
        }
        
        if(arrParams.contenedor){
            if(typeof arrParams.contenedor == "string"){
                if($(arrParams.contenedor).length != 0)
                    $(arrParams.contenedor).append(objMyTable);
            }
            else if(typeof arrParams.contenedor == "object"){
                if(arrParams.contenedor.jquery)
                    arrParams.contenedor.append(objMyTable);
                else if($(arrParams.contenedor).length != 0)
                    $(arrParams.contenedor).append(objMyTable);
            }
        }
        return $.extend({}, mySelf, objMyTable);
    }
    
};
