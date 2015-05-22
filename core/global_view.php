<?php
include_once("core/global_controller.php");

class global_view{
        /*es para saber de donde viene la pagina
    * @access private
    */
    private $strAction;

    /*Contine el nombre de la pagina
    * @access private
    */
    private $strNamePage;

    static $_instance;

    function __construct($strAction){
        $this->setStrAction($strAction);
    }

    /*Evitamos el clonaje del objeto. Patrï¿½n Singleton*/
    private function __clone(){ }

    /*Funciï¿½n encargada de crear, si es necesario, el objeto. Esta es la funciï¿½n que debemos llamar desde fuera de la clase para instanciar el objeto, y asï¿½, poder utilizar sus mï¿½todos*/
    public static function getInstance(){
            if (!(self::$_instance instanceof self)){
                self::$_instance=new self();
        }
        return self::$_instance;
    }

    /*Getter y setter*/
    public function getStrAction(){
        if(empty($this->strAction)) $this->strAction = "";
        return $this->strAction;
    }
    public function setStrAction($strTMP){
        $this->strAction = $strTMP;
    }
    public function getStrNamePage(){
        if(empty($this->strNamePage)) $this->strNamePage = "";
        return $this->strNamePage;
    }
    public function setStrNamePage($strName){
        $this->strNamePage = $strName;
    }

    public function getCabecera($strTittle = "",$includeMenu = true){
        $this->setStrNamePage($strTittle);
        $strSRC = "images/user_Male.jpg";
        $boolLogged = (!empty($_SESSION["wild"]["logged"]));
        ?>
        <!DOCTYPE>
        <html lang="es">
            <head>
                <meta http-equiv="content-type" content="text/html; charset=iso-8859-1;" />
                <title><?php print $this->getStrNamePage(); ?></title>
                <meta http-equiv="content-type" content="text/html; charset=iso-8859-1;">
                <meta name="robots" content="index, follow">
                <?php
                $this->global_scripts();
                ?>
            </head>           
            <script type="text/javascript">
                $boolDesplegate = false;
                <?php
                if($boolLogged){
                    ?>
                    function showOptionsUser(){
                        if($boolDesplegate === false){
                            $("#miniMenu").animate({
                                right: "0px"
                            }, "fast");
                            setTimeout(function(){
                                $boolDesplegate = true;
                            },10)
                        }
                        else{
                            $("#miniMenu").animate({
                                right: '-250px'
                            }, "fast");
                            $boolDesplegate = false;
                        }
                    }

                    function showPanelManuals(){        
                        $("#miniMenu").animate({
                            right: '-250px'
                        }, "fast");
                        $boolDesplegate = false;
                        showManuals();
                    }
                    <?php
                }
                ?>
                
                $(document).ready(function(){ 
                    $("#bodyT").click(function(){   
                        if($boolDesplegate === true){
                            $("#miniMenu").animate({
                                right: '-250px'
                            }, "fast");
                            $boolDesplegate = false;
                        }
                    });       

                    var myHeight = 0;
                    var actualHeight = document.documentElement.clientHeight;
                    //var actualHeight = screen.height;
                    myHeight = (actualHeight * 1)-100;
                    $("#contentPage").css("min-height",myHeight);        
                });
                
            </script>
            <body id="PageBody" onLoad="">
            <?php 
            if($boolLogged){
                ?>
                <div id="miniMenu" class="miniMenu">
                    <div id="titleMini" style="border-bottom: 1px solid #F4F4F4;">
                        <b><?php print $strTittle; ?></b>
                    </div>
                    <div id="titleMini">
                        <b>Opciones</b>
                    </div>
                    <div id="option" onclick="document.location.href='index.php?logout=true'"><b>Salir</b></div>
                </div>
                <?php
            }            
            ?> 
                <div id="bodyT">
                <div class="pageHeader">
                    <div class="pageLogo">
                        <img src="<?php print "images/POS_logo.png"; ?>" title="Tigo Pos" onclick="document.location.href='index.php'" style="cursor: pointer;">
                    </div>
                    <?php 
                    if($boolLogged){
                        ?>
                        <div class="pageLogin" onclick="<?php print ($boolLogged)?"showOptionsUser();":"";?>">
                            <div class="optionsUser">
                                <label><?php print (isset($_SESSION["wild"]["name"]))?$_SESSION["wild"]["name"]:""; ?></label>                
                            </div>
                            <div class="photoUser">                    
                                <img src="<?php print $strSRC; ?>" title="Opciones" class="circular">
                            </div>            
                        </div>
                        <div class="loginShort">
                            <img id="showMenu" src="<?php print $strSRC; ?>" title="<?php print $strName; ?>" onclick="<?php print ($boolLogged)?"showOptionsUser();":"";?>" class="circular">            
                        </div>
                        <div class="menuT">
                            <?php
                            $objCont = new global_controller();
                            $arrMenus = $objCont->get_arrayMenu();
                            $varMenu = new menu($arrMenus);
                            $varMenu->admin_navigation();
                            ?>
                        </div>
                        <?php 
                    }
                    ?>
                </div>          
                <div id="divglobal_load" style="display:none; background-color: white; color: black;">&nbsp;</div>
                <div style="height: 50px;width: 100%"></div>
                <div id="contentPage" class="Content"> 
                
        <?php
        
    }

    public function getPiePagina(){
        ?>
                </div>
                <div class="bottomT">
                    <div style="float: left;">
                        
                    </div>
                    <div style="width: 10%;margin-left: 32%;float: left;">
                        
                    </div>
                    <div style="float: right;">
                        
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function getLeft(){

    }
    public function getRight(){

    }
    public function global_scripts(){
        ?>
        <link type="text/css" rel="stylesheet" href="vistas/css/menu.css" />
        <link type="text/css" rel="stylesheet" href="vistas/css/estilos.css" />
        <link type="text/css" rel="stylesheet" href="vistas/css/form.css" />
        <script type="text/javascript" src="librarys/js/jquery.js"></script>
        <!--<script type="text/javascript" src="librarys/js/spritely/jq.spritely.js"></script>-->
        <script type="text/javascript" src="librarys/js/ui/jquery.ui.js"></script>
        <link rel="stylesheet" type="text/css" href="librarys/js/ui/jquery.ui.css">
        <script type="text/javascript" src="librarys/js/notice/jquery.notice.js"></script>
        <link rel="stylesheet" type="text/css" href="librarys/js/notice/jquery.notice.css">
        <script type="text/javascript" src="librarys/js/bootstrap/js/bootstrap.js"></script>
        <link rel="stylesheet" type="text/css" href="librarys/js/bootstrap/css/bootstrap.css">
        <?php //para el menu ?>
        <!--<script type="text/javascript" src="librarys/js/mb/_inc/jquery.hoverIntent.min.js" ></script>
        <script type="text/javascript" src="librarys/js/mb/_inc/jquery.metadata.js" ></script>
        <script type="text/javascript" src="librarys/js/mb/menu/jq.mb.menu.js" ></script>
        <link rel="stylesheet" type="text/css" href="librarys/js/mb/menu/jq.mb.menu.css" >-->
        <script type="text/javascript" src="librarys/js/library.js"></script>
        <script type="text/javascript">
            var globalWidget = new drawWidgets();
            function fntGetPage(strLink){
                $.ajax({
                    type:   "GET",
                    url :   '<?php print $this->getStrAction(); ?>?act=lnk&' +strLink,
                    beforeSend: function(){
                        if($(".ui-dialog").length) $(".ui-dialog").remove();
                        globalWidget.openLoading();
                    },
                    success: function (data){
                        globalWidget.closeLoading();
                        $("#contentPage").html(data);
                    },
                    error: function(){
                        globalWidget.closeLoading();
                    }
                });
            }

            <?php
            if(isset($_GET["print"])){
                ?>
                window.print();
                <?php
            }
            ?>
        </script>
        <?php
    }

    public function drawTema($strTittle = ""){
        $this->getCabecera($strTittle);
        $this->getPiePagina();
    }

    public function fntWindowUnable(){
        ?>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td align="center" valign="center">
                    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em; min-height: 40px; vertical-align: middle;">
                        <p style="vertical-align: middle;">
                            <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                            <strong>Alerta: </strong>
                            Ventana no definida!!!
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public function fntAlerta($strTitle, $strTexto = ""){
         ?>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td align="center" valign="center">
                    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em; min-height: 40px; vertical-align: middle;">
                        <p style="vertical-align: middle;">
                            <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                            <strong><?php print $strTitle; ?> </strong>
                            <?php print $strTexto; ?>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public function initForm($strName, $boolTargetBlank = false, $strAction = ""){
        if(empty($strAction)){
            $strAction = $this->getStrAction();
        }
        ?>
        <form action="<?php print $strAction; ?>" name="<?php print $strName?>" id="<?php print $strName?>" method="post" enctype="multipar/form-data" <?php print ($boolTargetBlank)?"target='_blank'":""; ?> >
            <input type="hidden" name="<?php print $strName; ?>_hddn" id="<?php print $strName; ?>_hddn" value="1">
        <?php
    }

    public function finForm(){
        ?>
        </form>
        <?php
    }

    public function getButtons($strName, $arrButtons, $boolDragable = true){
        ?>
        <script type="text/javascript">
            var intWidth = 100;
            $(function(){
               if($.browser.msie){
                   $("#<?php  print $strName; ?>").addClass("IEMenuButton");
               }
               else{

               }
               $("#<?php print $strName; ?>").addClass("MenuButton");
               
               <?php 
               if($boolDragable){
                    ?>
                    $("#<?php $strName; ?>").draggable();
                    <?php
               }
               ?>
            });
        </script>
        <div id="<?php print $strName; ?>">
            <p >
                <?php
                $count = 0;
                $intNum = 0;
                $boolShow = false;
                if(is_array($arrButtons) && count($arrButtons)){
                    while($arrT = each($arrButtons)){
                        $count++;
                        $boolShow = true;
                        ?>
                        <button type="button" class="btn btn-primary btn-lg butimg<?php print $arrT["key"]; ?>" name="<?php print $arrT["value"]["name"]; ?>" id="<?php print $arrT["value"]["name"]; ?>" onclick="<?php print $arrT["value"]["onclick"]; ?>" >                        
                            <?php print $arrT["value"]["title"]; ?>
                        </button>
                        <?php
                        $intNum++;

                        $arrT = false;
                    }
                }

                ?>
            </p>
        </div>
        <?php
    }

    public function draw_headlines($strTitle = ""){
        ?>
	<table width="100%" cellspacing="0" cellspacing="0" >
		<tr> <td class="ui-widget-header ui-corner-all" align="center"> <?php print $strTitle; ?> </td></tr>
	</table>
        <?php
    }
}

class menu{
    private $arrLinks = array();
    private $strIdMenu = "menu";
    function __construct($arrMenus) {
        $this->arrLinks = $arrMenus;
    }
    
    function draw_skyrim(){
        ?>
        <ul id="<?php print $this->strIdMenu; ?>">
        <?php 
        foreach($this->arrLinks AS $value){
            ?>
            <li>
                <a href="#"><?php print $value["modulo"] ?></a>
                <?php 
                if(count($value["detalle"])>0){
                    print "<ul>";
                    foreach($value["detalle"] AS $value2){
                        ?>                        
                        <li>
                            <a href="<?php print $value2["link"] ?>"><?php print $value2["name"] ?></a>
                        </li>                       
                        <?php
                    }
                    print "</ul>";
                }
                ?>
            </li>
            <?php
        }
        
        ?>
        </ul>
        <?php
    }
    
    function admin_navigation(){
        ?>       
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                      <span class="sr-only">Toggle navigation</span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Menú</a>
                </div>                
                <div style="height: 1px;" aria-expanded="false" id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <?php 
                        foreach($this->arrLinks AS $value){
                            ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-list-alt"></span><?php print $value["modulo"];  ?> <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php                             
                                        foreach($value["detalle"] AS $link){
                                            ?>
                                            <li><a style="cursor:pointer;" onclick="fntGetPage('<?php print $link["link"] ?>')"><?php print $link["name"]; ?></a></li> 
                                            <?php   
                                        }                                
                                    ?>                            
                                </ul>
                            </li>

                            <?php
                            unset($value);
                        }
                        ?>  
                    </ul>    
                </div>
            </div>
        </nav>
        <?php
    }
    
    
}