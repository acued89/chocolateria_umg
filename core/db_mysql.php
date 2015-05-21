<?php

class db_mysql {

    private $servidor = "127.0.0.1";
    private $dataBase = "db_wildsoft";
    private $usuario = "root";
    private $psswd = "homeland";

    private $link;
    private $stmt;
    private $array;
    static $_instance;

    /* La funci�n construct es privada para evitar que el objeto pueda ser creado mediante new */

    public function __construct() {
        $this->conectar();
    }

    /* Evitamos el clonaje del objeto. Patr�n Singleton */

    private function __clone() {

    }

    /* Funci�n encargada de crear, si es necesario, el objeto. Esta es la funci�n que debemos llamar desde fuera de la clase para instanciar el objeto, y as�, poder utilizar sus m�todos */

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /* Realiza la conexi�n a la base de datos. */

    private function conectar() {
        if (!isset($this->link)) {
            $this->link = (mysql_connect($this->servidor, $this->usuario, $this->psswd)) or die(mysql_error());
            mysql_select_db($this->dataBase, $this->link) or die(print "MySQL Error:". mysql_error());
        }

        //@mysql_query("SET NAMES 'utf8'");
    }

    public function desconectar() {
        mysql_close();
    }

    /* M�todo para ejecutar una sentencia sql */

    public function sql_ejecutar($sql) {
        $this->stmt = mysql_query($sql, $this->link);
        if (!$this->stmt) {
            print "MySQL Error: " . mysql_error();
            print global_controller::debugPHP($sql, "error mysql");
            exit;
        }
        return $this->stmt;
    }

    /* M�todo para obtener una fila de resultados de la sentencia sql */

    public function sql_fetch_array($stmt, $fila = 0) {
        if ($fila == 0) {
            $this->array = mysql_fetch_array($stmt);
        } else {
            mysql_data_seek($stmt, $fila);
            $this->array = mysql_fetch_array($stmt);
        }
        return $this->array;
    }

    /* M�todo para obtener una fila de resultados de la sentencia sql */

    public function sql_fetch_assoc($stmt) {
        if (!is_resource($stmt))
            return false;
        $this->array = mysql_fetch_assoc($stmt);
        return $this->array;
    }

    //Devuelve el �ltimo id del insert introducido
    public function sql_lastID() {
        return mysql_insert_id($this->link);
    }

    public function sql_num_rows($stmt) {
        if (!is_resource($stmt))
            return false;
        return mysql_num_rows($stmt);
    }

    public function sql_real_escape_string($strTMP) {
        return mysql_real_escape_string($strTMP);
    }

    public function sql_free_result($stmt) {
        return mysql_free_result($stmt);
    }

    /* Metodo para devolver el mysql_fetch_field */

    public function sql_get_fields($argIndex) {
        if ($field = mysql_fetch_field($argIndex)) {
            do {
                $fields[$field->name]['name'] = $field->name;
                $fields[$field->name]['table'] = $field->table;
                $fields[$field->name]['max_length'] = $field->max_length;
                $fields[$field->name]['not_null'] = $field->not_null;
            } while ($field = mysql_fetch_field($argIndex));
        }
        return $fields;
    }

    /* Metodo para devololver el mysql_num_fields */

    public function sql_num_fields($argIndex) {
        return mysql_num_fields($argIndex);
    }
}