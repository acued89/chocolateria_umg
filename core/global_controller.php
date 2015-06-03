<?php

global $intUid;
include_once("core/global_view.php");
include_once("core/global_model.php");

/**
 * global controller
 * Clases para la gestion del view
 */
class global_controller {

    static $_instance;

    /* Variable para usar el action en cada controller
     * @param string
     * @access private
     */
    private $strAction;
    private $strTitle;
    private $objView;
    private $objMod;
    protected $arrParam;

    //Constructor de la clase
    function __construct($strAction = "") {
        //inicializacion de procesos o variables, si se deseara
        $this->setStrAction($strAction);
        $this->objView = new global_view($strAction);
        $this->objMod = global_model::getInstance();
    }

    /* Evitamos el clonaje del objeto. Patrón Singleton */
    private function __clone() {

    }

    /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
    public static function getInstance($strAction = "") {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($strAction);
        }
        return self::$_instance;
    }

    public function setArrParam($arrP){
        $this->arrParam = $arrP;
    }

    public function getObjViewScripts(){
        $this->objView->global_scripts();
    }

    //Funciones de variables
    public function setStrAction($string) {
        $this->strAction = $string;
    }

    public function getStrAction() {
        return $this->strAction;
    }

    public function setStrTitle($string) {
        $this->strTitle = $string;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    /**
     * Metodo imprime el html en la intercase de usuario
     *
     * @param $html codigo html
     */
    private function print_html($html) {
        print $html;
    }

    function getAjaxContent($strClass, $strModule = "", $strPageId = "", $strTitle = "", $boolincludeHead = true) {
        if ($boolincludeHead)
            header("Content-Type: text/html; charset=iso-8859-1");
        $strClass = strtolower($strClass);
        $classname = "{$strClass}_controller";
        $strTarget = "modulos/{$strModule}/windows/{$strClass}/{$classname}.php";
        $objView = new global_view($this->getStrAction());

        $varTMP = $this->validateClass($strTarget, $classname, $strClass, $strModule, $strTitle);
        if (!$varTMP) {
            $strTarget = "modulos/{$strModule}/{$classname}.php";
            $varTMP = $this->validateClass($strTarget, $classname, $strClass, $strModule, $strTitle);
            if (!$varTMP) {
                $objView->fntWindowUnable();
            }
        }
        die();
    }

    function validateClass($strTarget, $classname, $strClass, $strModule, $strTitle) {
        $boolRetun = false;
        if (file_exists($strTarget)) {
            include_once($strTarget);
            $this->setArrParam($_REQUEST);
            if (!class_exists($classname)){
                $boolReturn = false;
            }
            else {
                $strAction = "index.php?act=lnk&page={$strClass}&mod={$strModule}"; //basename(__FILE__);
                $var = new $classname($strAction);
                $var->arrParam = $this->arrParam;                
                if(isset($this->arrParam["op"]) && method_exists($var, "getOperacion")){
                    header('Content-type: application/json');
                    $arrReturn = $var->getOperacion();
                    print json_encode($arrReturn);
                    die();
                }
                else{
                    if(method_exists($var, "getPage")) {
                        $var->getPage($strTitle);
                        return true;
                    }
                }
            }
        }
        return $boolRetun;
    }

    /**
     * Elimina los slashes de un user input segun la configuracion de magic_quotes_gpc.  DEBE ser utilizada en TODOS los inputs.
     *
     * @param string $strInput
     * @param boolean $boolUTF8Decode
     * @return string
     */
    public static function user_magic_quotes($strInput, $boolUTF8Decode = false) {
        //htmlspecialchars_decode
        //html_entity_decode
        $strInput = trim($strInput);
        if (get_magic_quotes_gpc()) {
            $strInput = stripslashes($strInput);
        }
        //Esto arruina los gets... pero sirve con los posts de ajax...
        if ($boolUTF8Decode && mb_detect_encoding($strInput) == "UTF-8") {
            $strInput = utf8_decode($strInput);
        }
        return $strInput;
    }

    public function principal_struct($strTarget = "") {

        $this->objView->drawTema($this->getStrTitle());
    }

    public function get_arrayMenu() {
        $arrMenu = $this->objMod->getArrayMenuForUser();
        return $arrMenu;
    }

    public static function clearTerm($strTMP, $boolUTFDecode = false){
        return global_model::clearTerm($strTMP, $boolUTFDecode);
    }

    public function formatDate($strDate){
        $arrDate = explode("-",$strDate);
        $arrMeses[1] = "enero";
        $arrMeses[2] = "febrero";
        $arrMeses[3] = "marzo";
        $arrMeses[4] = "abril";
        $arrMeses[5] = "mayo";
        $arrMeses[6] = "junio";
        $arrMeses[7] = "julio";
        $arrMeses[8] = "agosto";
        $arrMeses[9] = "septiembre";
        $arrMeses[10] = "octubre";
        $arrMeses[11] = "noviembre";
        $arrMeses[12] = "diciembre";

        $arrDate[1] = intval($arrDate[1]);

        return "{$arrDate[2]} de {$arrMeses[$arrDate[1]]} del {$arrDate[0]}";
    }
    
     public function checkParam($strTerm){
        if(!empty($this->arrParam[$strTerm])){
            if(is_int($this->arrParam[$strTerm])) $this->arrParam[$strTerm] = intval($this->arrParam[$strTerm]);
            else if(is_float($this->arrParam[$strTerm])) $this->arrParam[$strTerm] = floatval($this->arrParam[$strTerm]);
            else if(is_string($this->arrParam[$strTerm])) $this->arrParam[$strTerm] = global_controller::user_magic_quotes($this->arrParam[$strTerm]);
            else $this->arrParam[$strTerm] = global_controller::user_magic_quotes($this->arrParam[$strTerm]);
                
            return $this->arrParam[$strTerm];
        }
        return "";
    }

}

#handling debugs
/**
 * This class conteins the logical to handling points of debug
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.2
 */
class debug{
    static $_instance;
    private $DEBUG_STR = "";
    private $CLASSREF;
    
    function __construct($objClass = false) {
        $this->CLASSREF = $objClass;
        if(!$this->CLASSREF) $this->CLASSREF = $this;
    }
    public static function getInstance($objClass = false) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($objClass);
        }
        return self::$_instance;
    }
    public static function drawdebug($ThisVar, $VariableName = "", $ShowWhat=0, $boolForceShow = false){
        $strType = gettype($ThisVar);
        $strPreOpen = "";
        $strPreClose = "";
        if (!is_string($ThisVar)) {
            $strPreOpen = "<pre>";
            $strPreClose = "</pre>";
        }

        echo "\n<hr>";
        if (!empty($VariableName))
            echo "<b><i> $VariableName</b></i> ";
        echo "Var  Type of var = <b>" . $strType . "</b><br><br>\n{$strPreOpen}";
        if ($ShowWhat == 0) {
            if (is_bool($ThisVar))
                print_r(($ThisVar) ? "true" : "false");
            else
                print_r($ThisVar);
        }

        else if ($ShowWhat == 1) {
            print_r(array_values($ThisVar));
        }
        else if ($ShowWhat == 2) {
            print_r(array_keys($ThisVar));
        }
        print_r("<hr>{$strPreClose}\n");
    }
    #Function that append point to debug
    function addDebug($strDebugString){        
        $this->DEBUG_STR .= (empty($this->DEBUG_STR))?"":", ";
        $this->DEBUG_STR .= $this->getmicrotime().' '.get_class($this->CLASSREF).": {$strDebugString}\n<br>";
    }
    #Function that clear the debug's variable
    function clearDebug(){
        return $this->DEBUG_STR;
    }
    #Fucntion that return all debugs in string
    function getDebug(){
        return $this->DEBUG_STR;
    }
    #for times
    function getmicrotime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
}
#handling errors
/**
 * This class contains the logical to handling error
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.3
 */
class error{    
    /**
     * Array that conteins all errors descriptions
     *
     * @var array -> array that conteins all errors
     * @access protected
     */
    private $arrErrorMsgs = false;    
    /**
     * Method thad added an errer into array's error 
     *
     * @param string $strMsg
     * @access protected
     */
    public function addError($strMsg, $strKey = "") {
        if(!empty($strKey)){
            $this->arrErrorMsgs[$strKey][] = $strMsg;
        }
        else $this->arrErrorMsgs[] = $strMsg;

    }
    /**
     * Method that ordering the error's array
     */
    public function sortErrorsByText() {
        if ($this->hasError()) {
            sort($this->arrErrorMsgs);
        }
    }
    /**
     * Method that indicating if it has errors
     *
     * @access public
     * @return boolean
     */
    public function hasError($strKey = "") {
        if(!empty($strKey)){
            return (isset($this->arrErrorMsgs[$strKey]) && is_array($this->arrErrorMsgs[$strKey]) && (count($this->arrErrorMsgs[$strKey]) > 0));
        }
        else return (is_array($this->arrErrorMsgs) && (count($this->arrErrorMsgs) > 0));
    }
    /**
     * Method that return the error's array, support array view or string view
     *
     * @param string $strMode modes that return message array|string
     * @param mixed $varModeHelper indicates which is the glue if a string
     * @return mixed
     */
    public function getErrors($strMode = "array", $varModeHelper = false, $strKey = "") {
        if(!empty($strKey)){
            if (!$this->hasError($strKey))
                return false;
            if ($strMode == "string") {
                if ($varModeHelper == false)
                    $varModeHelper = ", ";
                return implode($varModeHelper, $this->arrErrorMsgs[$strKey]);
            }
            else {
                return $this->arrErrorMsgs[$strKey];
            }
        }
        else{
            if (!$this->hasError())
                return false;
            if ($strMode == "string") {
                if ($varModeHelper == false)
                    $varModeHelper = ", ";

                $strVar = "";
                foreach ($this->arrErrorMsgs as $element) {
                    if(is_array($element) && (count($element)>0)){
                        foreach($element as $element2){
                            $strVar .=  (empty($strVar))? $element2 : $varModeHelper . $element2;
                        }
                    }
                    else{
                        $strVar .= (empty($strVar))? $element : $varModeHelper . $element;    
                    }                    
                }
                return $strVar;                                               
            }
            else {
                return $this->arrErrorMsgs;
            }
        }
    }
}
#Class for logical operations
/**
 * This class contains a logical operation to binary level
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.1
 */
class logical_operation{
    static $_instance;
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     *  Realiza una operacion OR a nivel de bits entre dos variantes
     * @param type $int1
     * @param type $int2
     * @return type decimal
     */
    function disjunction($int1, $int2){
        $var1 = decbin($int1);
        $var2 = decbin($int2);
        $len1 = strlen($var1);
        $len2 = strlen($var2);
        $cont = 0;
        if($len1 > $len2){
            $var2 = str_pad($var2, $len1,"0",STR_PAD_LEFT);
            $cont = $len1;
        }
        else{
            $var1 = str_pad($var1, $len2,"0",STR_PAD_LEFT);
            $cont = $len2;
        }
        $strNewValorBin = "";
        for($i=0;$i<$cont;$i++){

            if($var1[$i] == "0" && $var2[$i] =="0"){
                $strNewValorBin .= "0";
            }
            elseif($var1[$i] == "0" && $var2[$i] =="1"){
                $strNewValorBin .= "1";
            }
            elseif($var1[$i] == "1" && $var2[$i] =="0"){
                $strNewValorBin .= "1";
            }
            elseif($var1[$i] == "1" && $var2[$i] =="1"){
                $strNewValorBin .= "1";
            }        
        }
        $strNewValor = bindec($strNewValorBin);
        return $strNewValor;
    }
    /**
     * Realiza una operación AND a nivel de bits entre dos variantes
     * @param type $int1
     * @param type $int2
     * @return type decimal
     */
    function conjunction($int1, $int2){
        $var1 = decbin($int1);
        $var2 = decbin($int2);    
        $len1 = strlen($var1);
        $len2 = strlen($var2);
        $cont = 0;
        if($len1 > $len2){
            $var2 = str_pad($var2, $len1,"0",STR_PAD_LEFT);
            $cont = $len1;
        }
        else{
            $var1 = str_pad($var1, $len2,"0",STR_PAD_LEFT);
            $cont = $len2;
        }
        $strNewValorBin = "";
        for($i=0;$i<$cont;$i++){

            if($var1[$i] == "0" && $var2[$i] =="0"){
                $strNewValorBin .= "0";
            }
            elseif($var1[$i] == "0" && $var2[$i] =="1"){
                $strNewValorBin .= "0";
            }
            elseif($var1[$i] == "1" && $var2[$i] =="0"){
                $strNewValorBin .= "0";
            }
            elseif($var1[$i] == "1" && $var2[$i] =="1"){
                $strNewValorBin .= "1";
            }        
        }
        $strNewValor = bindec($strNewValorBin);
        return $strNewValor;
    }
}

class do_request{
    static $_instance;
    function __construct() {

    }
    public static function getInstance( ) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function do_postOnly_request($strUrl, $arrGetData, $intTimeOut = 5) {

        if (is_array($arrGetData)) {
            $strGetData = http_build_query($arrGetData);
            $arrParams = array("http" => array("method" => "GET", "timeout" => $intTimeOut));
            $objContext = stream_context_create($arrParams);
            $strResponse = file_get_contents("{$strUrl}?{$strGetData}", false, $objContext);
            
            debug::drawdebug($strResponse);
            
            return $strResponse;
        }
        else if (is_string($arrGetData)) {
            $arrParams = array("http" => array("method" => "GET", "timeout" => $intTimeOut));
            $objContext = stream_context_create($arrParams);
            $strResponse = file_get_contents("{$strUrl}?{$arrGetData}", false, $objContext);
            return $strResponse;
        }
        else {
            return false;
        }
    }

}

/** 
 * Class RestClient 
 * Wraps HTTP calls using cURL, aimed for accessing and testing RESTful webservice. 
 * By Diogo Souza da Silva <manifesto@manifesto.blog.br> 
 */ 
class RestClient { 

     private $curl ; 
     private $url ; 
     private $response =""; 
     private $headers = array(); 

     private $method="GET"; 
     private $params=null; 
     private $contentType = null; 
     private $file =null; 

     /** 
      * Private Constructor, sets default options 
      */ 
     private function __construct() { 
         $this->curl = curl_init(); 
         curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true); 
         curl_setopt($this->curl,CURLOPT_AUTOREFERER,true); // This make sure will follow redirects 
         curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,true); // This too 
         curl_setopt($this->curl,CURLOPT_HEADER,true); // THis verbose option for extracting the headers 
     } 

     /** 
      * Execute the call to the webservice 
      * @return RestClient 
      */ 
     public function execute() { 
         if($this->method === "POST") { 
             curl_setopt($this->curl,CURLOPT_POST,true); 
             curl_setopt($this->curl,CURLOPT_POSTFIELDS,$this->params); 
         } else if($this->method == "GET"){ 
             curl_setopt($this->curl,CURLOPT_HTTPGET,true); 
             $this->treatURL(); 
         } else if($this->method === "PUT") { 
             curl_setopt($this->curl,CURLOPT_PUT,true); 
             $this->treatURL(); 
             $this->file = tmpFile(); 
             
             fwrite($this->file,$this->params); 
             fseek($this->file,0); 
             curl_setopt($this->curl,CURLOPT_INFILE,$this->file); 
             curl_setopt($this->curl,CURLOPT_INFILESIZE,strlen($this->params)); 
         } else { 
             curl_setopt($this->curl,CURLOPT_CUSTOMREQUEST,$this->method); 
         } 
         if($this->contentType != null) { 
             curl_setopt($this->curl,CURLOPT_HTTPHEADER,array("Content-Type: ".$this->contentType)); 
         } 
         curl_setopt($this->curl,CURLOPT_URL,$this->url); 
         $r = curl_exec($this->curl); 
         
         //debug::drawdebug($this->curl);
         //debug::drawdebug($r);
         
         $this->treatResponse($r); // Extract the headers and response 
         return $this ; 
     } 

     /** 
      * Treats URL 
      */ 
     private function treatURL(){ 
         if(is_array($this->params) && count($this->params) >= 1) { // Transform parameters in key/value pars in URL 
             if(!strpos($this->url,'?')) 
                 $this->url .= '?' ; 
             foreach($this->params as $k=>$v) { 
                 $this->url .= "&".urlencode($k)."=".urlencode($v); 
             } 
         } 
        return $this->url; 
     } 

     /* 
      * Treats the Response for extracting the Headers and Response 
      */ 
     private function treatResponse($r) { 
        if($r == null or strlen($r) < 1) { 
            return; 
        } 
        $parts = explode("\n\r",$r); // HTTP packets define that Headers end in a blank line (\n\r) where starts the body 
        while(preg_match('@HTTP/1.[0-1] 100 Continue@',$parts[0]) or preg_match("@Moved@",$parts[0])) { 
            // Continue header must be bypass 
            for($i=1;$i<count($parts);$i++) { 
                $parts[$i - 1] = trim($parts[$i]); 
            } 
            unset($parts[count($parts) - 1]); 
        } 
        preg_match("@Content-Type: ([a-zA-Z0-9-]+/?[a-zA-Z0-9-]*)@",$parts[0],$reg);// This extract the content type 
        $this->headers['content-type'] = $reg[1]; 
        preg_match("@HTTP/1.[0-1] ([0-9]{3}) ([a-zA-Z ]+)@",$parts[0],$reg); // This extracts the response header Code and Message 
        $this->headers['code'] = $reg[1]; 
        $this->headers['message'] = $reg[2]; 
        $this->response = ""; 
        for($i=1;$i<count($parts);$i++) {//This make sure that exploded response get back togheter 
            if($i > 1) { 
                $this->response .= "\n\r"; 
            } 
            $this->response .= $parts[$i]; 
        } 
     } 

     /* 
      * @return array 
      */ 
     public function getHeaders() { 
        return $this->headers; 
     } 

     /* 
      * @return string 
      */ 
     public function getResponse() { 
         return $this->response ; 
     } 

     /* 
      * HTTP response code (404,401,200,etc) 
      * @return int 
      */ 
     public function getResponseCode() { 
         return (int) $this->headers['code']; 
     } 
     
     /* 
      * HTTP response message (Not Found, Continue, etc ) 
      * @return string 
      */ 
     public function getResponseMessage() { 
         return $this->headers['message']; 
     } 

     /* 
      * Content-Type (text/plain, application/xml, etc) 
      * @return string 
      */ 
     public function getResponseContentType() { 
         return $this->headers['content-type']; 
     } 

     /** 
      * This sets that will not follow redirects 
      * @return RestClient 
      */ 
     public function setNoFollow() { 
         curl_setopt($this->curl,CURLOPT_AUTOREFERER,false); 
         curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,false); 
         return $this; 
     } 

     /** 
      * This closes the connection and release resources 
      * @return RestClient 
      */ 
     public function close() { 
         curl_close($this->curl); 
         $this->curl = null ; 
         if($this->file !=null) { 
             fclose($this->file); 
         } 
         return $this ; 
     } 

     /** 
      * Sets the URL to be Called 
      * @return RestClient 
      */ 
     public function setUrl($url) { 
         $this->url = $url; 
         return $this; 
     } 

     /** 
      * Set the Content-Type of the request to be send 
      * Format like "application/xml" or "text/plain" or other 
      * @param string $contentType 
      * @return RestClient 
      */ 
     public function setContentType($contentType) { 
         $this->contentType = $contentType; 
         return $this; 
     } 

     /** 
      * Set the Credentials for BASIC Authentication 
      * @param string $user 
      * @param string $pass 
      * @return RestClient 
      */ 
     public function setCredentials($user,$pass) { 
         if($user != null) { 
             curl_setopt($this->curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC); 
             curl_setopt($this->curl,CURLOPT_USERPWD,"{$user}:{$pass}"); 
         } 
         return $this; 
     } 

     /** 
      * Set the Request HTTP Method 
      * For now, only accepts GET and POST 
      * @param string $method 
      * @return RestClient 
      */ 
     public function setMethod($method) { 
         $this->method=$method; 
         return $this; 
     } 

     /** 
      * Set Parameters to be send on the request 
      * It can be both a key/value par array (as in array("key"=>"value")) 
      * or a string containing the body of the request, like a XML, JSON or other 
      * Proper content-type should be set for the body if not a array 
      * @param mixed $params 
      * @return RestClient 
      */ 
     public function setParameters($params) { 
         $this->params=$params; 
         return $this; 
     } 

     /** 
      * Creates the RESTClient 
      * @param string $url=null [optional] 
      * @return RestClient 
      */ 
     public static function createClient($url=null) { 
         $client = new RestClient ; 
         if($url != null) { 
             $client->setUrl($url); 
         } 
         return $client; 
     } 

     /** 
      * Convenience method wrapping a commom POST call 
      * @param string $url 
      * @param mixed params 
      * @param string $user=null [optional] 
      * @param string $password=null [optional] 
      * @param string $contentType="multpary/form-data" [optional] commom post (multipart/form-data) as default 
      * @return RestClient 
      */ 
     public static function post($url,$params=null,$user=null,$pwd=null,$contentType="multipart/form-data") { 
         return self::call("POST",$url,$params,$user,$pwd,$contentType); 
     } 

     /** 
      * Convenience method wrapping a commom PUT call 
      * @param string $url 
      * @param string $body 
      * @param string $user=null [optional] 
      * @param string $password=null [optional] 
      * @param string $contentType=null [optional] 
      * @return RestClient 
      */ 
     public static function put($url,$body,$user=null,$pwd=null,$contentType=null) { 
         return self::call("PUT",$url,$body,$user,$pwd,$contentType); 
     } 

     /** 
      * Convenience method wrapping a commom GET call 
      * @param string $url 
      * @param array params 
      * @param string $user=null [optional] 
      * @param string $password=null [optional] 
      * @return RestClient 
      */ 
     public static function get($url,array $params=null,$user=null,$pwd=null) { 
         return self::call("GET",$url,$params,$user,$pwd); 
     } 

     /** 
      * Convenience method wrapping a commom delete call 
      * @param string $url 
      * @param array params 
      * @param string $user=null [optional] 
      * @param string $password=null [optional] 
      * @return RestClient 
      */ 
     public static function delete($url,array $params=null,$user=null,$pwd=null) { 
         return self::call("DELETE",$url,$params,$user,$pwd); 
     } 

     /** 
      * Convenience method wrapping a commom custom call 
      * @param string $method 
      * @param string $url 
      * @param string $body 
      * @param string $user=null [optional] 
      * @param string $password=null [optional] 
      * @param string $contentType=null [optional] 
      * @return RestClient 
      */ 
     public static function call($method,$url,$body,$user=null,$pwd=null,$contentType=null) { 
         return self::createClient($url) 
             ->setParameters($body) 
             ->setMethod($method) 
             ->setCredentials($user,$pwd) 
             ->setContentType($contentType) 
             ->execute() 
             ->close(); 
     } 
} 

// Package: VFXP - Very Fast XML Parser 
// Author: Claudio Castelpietra 
// Email: claudio@wopweb.com 
// Web site: http://www.wopweb.com/ 
// License: "LGPL" 
// Release history: 
// 18/11/2005 - v.1.0 - First release. 
// 24/01/2006 - v.1.1 - Some API added. Some php compatibility issues fixed. 
// Description: 
// Very Fast XML Parser for PHP. 
// Completely standalone: no need for any PHP extension library. 
// Very fast processing, using iterative single-cycle parsing, instead of recursive or multi-step parsing. 
// Very useful for just loading simple application configuration parameters. 
// Strongly simplified and intuitive DOM API, with respect to the DOM Core Level 1 
// Works with PHP >= 3 
// Limitations in version 1.0: 
// XML version and encoding declaration tags are ignored. 
// DOCTYPE declaration tags are ignored. 
// Notes: 
// I developed this class because the faster extension-independent PHP XML parser I've found took about 500 milliseconds to parse my configuration file, and this is not acceptable for a web page generation. 
// This class takes about 20 times less than the previous one. 
// Anyway, this it a PHP-interpreted class, so its performance cannot be compared to a C-compiled class. 
// 


// The Document Class 
// 
class VFXP_Document { 
    
    // Parses XML from a file 
    // 
    function parseFromFile($filename) { 
        
        $fp = fopen($filename, 'rb'); 
        $this->_docContentLength = filesize($filename); 
        $this->_docContent = fread($fp, $this->_docContentLength); 
        fclose($fp); 
        
        $this->_parseContent(); 
        
        unset($this->_docContent); 
    } 

    // Parses XML from a string 
    // 
    function parseFromString($content) { 
        $this->_docContent = $content; 
        
        $this->_parseContent(); 
        
        unset($this->_docContent); 
    } 
    
    // Returns a reference to the root Element 
    // 
    function & rootElement() { 
        return $this->_docRoot; 
    } 
    
    
    // Private variables and methods: 
    
    var $_docContent; // The XML content 
    var $_docContentLength; 
    var $_docRoot; // Reference to the root element 
    
    // Iterative method for fast XML parsing 
    // 
    function _parseContent() { 
        
        // Removing some tags we don't handle 
        $this->_docContent = eregi_replace('<\?xml [^>]+\?>|<!doctype [^\\[>]+(\\[[^]]*\\])?>', '', $this->_docContent); 
        
        $parseStack = array(); 
        $stacklen = 0; 
        $currpos = 0; 
        
        while (true) { 
            
            // let's extract the next tag 
            $tag = new VFXP_Tag(); 
            
            // find the begin of the tag 
            $tag->pos_start = strpos($this->_docContent, '<', $currpos); 
            if ($tag->pos_start === FALSE) { 
                die("VFXP - Error: XML tag not found."); 
            } 
            
            // check and skip comments 
            if (substr($this->_docContent, $tag->pos_start, 4) == '<!--') { 
                // we found a comment, let's look for its end 
                $tag->pos_end = strpos($this->_docContent, '-->', $tag->pos_start + 4) + 2; 
                if ($tag->pos_end === FALSE) { 
                    die("VFXP - Error: incomplete XML comment"); 
                } 
                // skipping comment tag 
                $currpos = $tag->pos_end + 1; 
                continue; 
            } 
            
            // check and skip CDATA sections 
            if (substr($this->_docContent, $tag->pos_start, 9) == '<![CDATA[') { 
                // we found a cdata section, let's look for its end 
                $tag->pos_end = strpos($this->_docContent, ']]>', $tag->pos_start + 9) + 2; 
                if ($tag->pos_end === FALSE) { 
                    die("VFXP - Error: incomplete XML CDATA section"); 
                } 
                // skipping cdata section 
                $currpos = $tag->pos_end + 1; 
                continue; 
            } 
            
            // find the end of the tag 
            $tag->pos_end = strpos($this->_docContent, '>', $tag->pos_start); 
            if ($tag->pos_end === FALSE) { 
                die("VFXP - Error: incomplete XML tag"); 
            } 
            $currpos = $tag->pos_end + 1; 
            
            
            // We have the tag. Let's check its type. 
            if (substr($this->_docContent, $tag->pos_start + 1, 1) == '/') { 
                $tag->is_opening = false; 
                $tag->is_closing = true; 
                $tag->name = substr($this->_docContent, $tag->pos_start + 2, $tag->pos_end - $tag->pos_start - 2); 
                // $tag->attributes = array(); // will not be used 
            } else { 
                $tag->is_opening = true; 
                if (substr($this->_docContent, $tag->pos_end - 1, 1) == '/') { 
                    $tag->is_closing = true; 
                    $tag->name = substr($this->_docContent, $tag->pos_start + 1, $tag->pos_end - $tag->pos_start - 2); 
                } else { 
                    $tag->is_closing = false; 
                    $tag->name = substr($this->_docContent, $tag->pos_start + 1, $tag->pos_end - $tag->pos_start - 1); 
                } 
                // Tag attributes parsing 
                ereg("^([^ \n\r\t/]+)([ \n\r\t/](.*))?$", $tag->name, $regs); 
                $tag->name = $regs[1]; 
                
                $attr_string = $regs[3]; 
                
                $tag->attributes = array(); 
                while ($attr_string != '') { 
                    // magic regular expression for extracting the attributes one at a time 
                    if (ereg("[ \n\r\t]*([^ \n\r\t=]+)(=(\"(\"|([^\"]*[^\\])\")|[^ \n\r\t\"][^ \n\r\t]*))?([ \n\r\t].*)?$", $attr_string, $regs)) { 
                        if ($regs[5] != '') { 
                            $tag->attributes[$regs[1]] = $regs[5]; 
                        } else { 
                            $tag->attributes[$regs[1]] = $regs[3]; 
                        } 
                        $attr_string = $regs[6]; 
                    } else { 
                        break; 
                    } 
                } 
            } 
            
            // Let's check the tag against the parsing stack 
            if ($tag->is_opening) { 
                
                // building the element corresponding to the found opening tag 
                $elem =& new VFXP_Element(); 
                $elem->_name = $tag->name; 
                $elem->_value = NULL; 
                $elem->_attributes = $tag->attributes; 
                $elem->_subelements = array(); 
                
                if ($stacklen == 0) { 
                    // This is the root element 
                    $this->_docRoot =& $elem; 
                } 
                
                $tag->elem_ref =& $elem; // saving a reference to the element into the opening tag 

                array_push($parseStack, $tag); // pushing the opening tag onto the parsing stack 
                ++$stacklen; 
                
            } 
            
            if ($tag->is_closing) { 
                
                --$stacklen; 
                $tag_top = array_pop($parseStack); 
                
                if ($tag_top->name != $tag->name) { 
                    print("VFXP - Closing tag not matching. Tag:".$tag->name." Pos:".$tag->pos_start . "<br>\nParse Stack:<br>\n"); 
                    print_r($parseStack); 
                    die(); 
                } 
                
                // element complete 
                // estraction of the element value - only if non self-closing or if without subelements 
                if (!$tag->is_opening && count($tag_top->elem_ref->_subelements) == 0) { 
                    $tag_top->elem_ref->_value = substr($this->_docContent, $tag_top->pos_end + 1, $tag->pos_start - $tag_top->pos_end - 1); 
                    // processing CDATA sections 
                    if (substr($tag_top->elem_ref->_value, 0, 9) == '<![CDATA[') { 
                        $tag_top->elem_ref->_value = substr($tag_top->elem_ref->_value, 9, -3); 
                    } 
                } 
                
                // let's add the element to the list of the subelements of the parent element 
                if ($stacklen > 0) { 
                    $parseStack[$stacklen-1]->elem_ref->_subelements[] =& $tag_top->elem_ref; 
                    //print($tag_top->elem_ref->name . ' child of ' . $parseStack[count($parseStack)-1]->elem_ref->name . "<br>\n"); 
                } else { 
                    // stack exausted - elaboration finished 
                    break; 
                } 
            } 
            
        } 
        
    } 
    
    
} 
