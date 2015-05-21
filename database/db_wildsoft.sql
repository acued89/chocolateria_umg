CREATE SCHEMA IF NOT EXISTS db_wildsoft DEFAULT CHARACTER SET utf8 ;
USE db_wildsoft ;

-- -----------------------------------------------------
-- Table cliente
-- -----------------------------------------------------
DROP TABLE IF EXISTS cliente ;

CREATE  TABLE IF NOT EXISTS cliente (
  idcliente INT(10) NOT NULL AUTO_INCREMENT ,
  codigo VARCHAR(45) NOT NULL ,
  fecha_registro DATE NOT NULL ,
  fecha_modificacion DATE NOT NULL ,
  razon_social VARCHAR(150) NOT NULL ,
  nombre VARCHAR(150) NOT NULL ,
  nit VARCHAR(45) NOT NULL ,
  direccion TEXT NOT NULL ,
  telefono VARCHAR(200) NOT NULL ,
  facturar_a VARCHAR(150) NOT NULL ,
  email VARCHAR(100) NOT NULL ,
  PRIMARY KEY (idcliente) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table estados
-- -----------------------------------------------------
DROP TABLE IF EXISTS estados ;
CREATE  TABLE IF NOT EXISTS estados (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  nombre VARCHAR(45) NOT NULL ,
  PRIMARY KEY (id) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table pedido
-- -----------------------------------------------------
DROP TABLE IF EXISTS pedido ;

CREATE  TABLE IF NOT EXISTS pedido (
  idpedido INT(10) NOT NULL AUTO_INCREMENT ,
  fecha DATE NOT NULL DEFAULT '0000-00-00' ,
  idcliente INT(10) NULL DEFAULT NULL ,
  anombrede VARCHAR(150) NULL DEFAULT NULL ,
  total DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  estados_id INT(11) NOT NULL ,
  PRIMARY KEY (idpedido, estados_id) ,
  INDEX fk_pedido_cliente1_idx (idcliente ASC) ,
  INDEX fk_pedido_estados1_idx (estados_id ASC) ,
  CONSTRAINT fk_pedido_cliente1
    FOREIGN KEY (idcliente )
    REFERENCES cliente (idcliente )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_pedido_estados1
    FOREIGN KEY (estados_id )
    REFERENCES estados (id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table usuario
-- -----------------------------------------------------
DROP TABLE IF EXISTS usuario ;

CREATE  TABLE IF NOT EXISTS usuario (
  userid INT(10) NOT NULL AUTO_INCREMENT ,
  nickname VARCHAR(45) NOT NULL DEFAULT '' ,
  password VARCHAR(100) NOT NULL ,
  tipo ENUM('admin','inventario','cajero') NOT NULL DEFAULT 'admin' ,
  nombreCompleto VARCHAR(150) NOT NULL DEFAULT '' ,
  nombres VARCHAR(150) NOT NULL DEFAULT '' ,
  apellidos VARCHAR(150) NOT NULL DEFAULT '' ,
  fecha_registro DATE NOT NULL DEFAULT '0000-00-00' ,
  hora TIME NOT NULL DEFAULT '00:00:00' ,
  active ENUM('Y','N') NOT NULL DEFAULT 'Y' ,
  PRIMARY KEY (userid) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table factura
-- -----------------------------------------------------
DROP TABLE IF EXISTS factura ;

CREATE  TABLE IF NOT EXISTS factura (
  idfactura INT(11) NOT NULL AUTO_INCREMENT ,
  fecha DATE NOT NULL DEFAULT '0000-00-00' ,
  hora TIME NOT NULL DEFAULT '00:00:00' ,
  factura_no BIGINT(20) NOT NULL DEFAULT '0' ,
  serie VARCHAR(50) NOT NULL DEFAULT '' ,
  nit VARCHAR(45) NOT NULL DEFAULT '' ,
  total DECIMAL(10,2) NULL DEFAULT '0.00' ,
  idpedido INT(10) NULL DEFAULT NULL ,
  idcliente INT(10) NOT NULL DEFAULT '0' ,
  dias_vence SMALLINT(6) NOT NULL DEFAULT '0' ,
  userid INT(10) NOT NULL ,
  PRIMARY KEY (idfactura) ,
  INDEX fk_factura_pedido1_idx (idpedido ASC) ,
  INDEX fk_factura_cliente1_idx (idcliente ASC) ,
  INDEX fk_factura_usuario1_idx (userid ASC) ,
  CONSTRAINT fk_factura_cliente1
    FOREIGN KEY (idcliente )
    REFERENCES cliente (idcliente )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_factura_pedido1
    FOREIGN KEY (idpedido )
    REFERENCES pedido (idpedido )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_factura_usuario1
    FOREIGN KEY (userid )
    REFERENCES usuario (userid )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table laboratorio
-- -----------------------------------------------------
DROP TABLE IF EXISTS laboratorio ;

CREATE  TABLE IF NOT EXISTS laboratorio (
  idlaboratorio INT(10) NOT NULL AUTO_INCREMENT ,
  nombre VARCHAR(100) NOT NULL DEFAULT '' ,
  descripcion TEXT NOT NULL ,
  PRIMARY KEY (idlaboratorio) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table proveedor
-- -----------------------------------------------------
DROP TABLE IF EXISTS proveedor ;

CREATE  TABLE IF NOT EXISTS proveedor (
  idproveedor INT(10) NOT NULL AUTO_INCREMENT ,
  codigo VARCHAR(45) NOT NULL ,
  fecha_registro DATE NOT NULL ,
  fecha_modificacion DATE NOT NULL ,
  razon_social VARCHAR(150) NOT NULL ,
  nombre VARCHAR(100) NOT NULL ,
  nit VARCHAR(45) NOT NULL ,
  direccion TEXT NOT NULL ,
  telefono VARCHAR(200) NOT NULL ,
  nombre_comercial VARCHAR(150) NOT NULL ,
  facturar_a VARCHAR(150) NOT NULL ,
  email VARCHAR(100) NOT NULL ,
  PRIMARY KEY (idproveedor) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table producto
-- -----------------------------------------------------
DROP TABLE IF EXISTS producto ;

CREATE  TABLE IF NOT EXISTS producto (
  idproducto INT(10) NOT NULL AUTO_INCREMENT ,
  codigo VARCHAR(100) NOT NULL DEFAULT '' ,
  nombre VARCHAR(150) NOT NULL DEFAULT '' ,
  etiqueta VARCHAR(100) NOT NULL DEFAULT '' ,
  descripcion TEXT NOT NULL ,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  ofertado ENUM('Y','N') NOT NULL DEFAULT 'N' ,
  precio_oferta DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  disponibles INT(11) NULL DEFAULT '0' ,
  precio_costo DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  status ENUM('agotado','bodega','solicitado') NOT NULL DEFAULT 'solicitado' ,
  PRIMARY KEY (idproducto) ,
  UNIQUE INDEX codigo_UNIQUE (codigo ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table inventario_master
-- -----------------------------------------------------
DROP TABLE IF EXISTS inventario_master ;

CREATE  TABLE IF NOT EXISTS inventario_master (
  inv_master INT(11) NOT NULL AUTO_INCREMENT ,
  codigo_lote VARCHAR(12) NOT NULL COMMENT 'registra el codigo del lote para los barcode' ,
  idproducto INT(10) NOT NULL ,
  idlaboratorio INT(10) NULL DEFAULT '0' ,
  idproveedor INT(10) NULL DEFAULT NULL ,
  userid INT(10) NOT NULL ,
  disponibles INT(11) NOT NULL ,
  cantidad INT(11) NOT NULL COMMENT 'cantidad q ingreso inicialmente' ,
  fecha_ingreso DATE NOT NULL ,
  hora_ingreso DATE NOT NULL ,
  fecha_expiracion DATE NOT NULL ,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  precio_costo DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  precio_oferta DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  ofertado ENUM('Y','N') NOT NULL DEFAULT 'N' ,
  impreso ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'para determinar si se ha impreso las etiquetas del lote' ,
  descripcion TEXT NOT NULL ,
  PRIMARY KEY (inv_master) ,
  INDEX produto_idIndex (idproducto ASC) ,
  INDEX fk_inventario_master_laboratorio1_idx (idlaboratorio ASC) ,
  INDEX fk_inventario_master_proveedor1_idx (idproveedor ASC) ,
  INDEX fk_inventario_master_usuario1_idx (userid ASC) ,
  CONSTRAINT fk_inventario_master_laboratorio1
    FOREIGN KEY (idlaboratorio )
    REFERENCES laboratorio (idlaboratorio )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_inventario_master_proveedor1
    FOREIGN KEY (idproveedor )
    REFERENCES proveedor (idproveedor )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_inventario_master_usuario1
    FOREIGN KEY (userid )
    REFERENCES usuario (userid )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT producto_id
    FOREIGN KEY (idproducto )
    REFERENCES producto (idproducto )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table pedido_detail
-- -----------------------------------------------------
DROP TABLE IF EXISTS pedido_detail ;

CREATE  TABLE IF NOT EXISTS pedido_detail (
  idpedido_detail INT(11) NOT NULL AUTO_INCREMENT ,
  idpedido INT(10) NOT NULL ,
  inv_master INT(11) NOT NULL ,
  cantidad INT(10) NOT NULL ,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  descuento DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  PRIMARY KEY (idpedido_detail) ,
  INDEX fk_pedido_detail_pedido1_idx (idpedido ASC) ,
  INDEX fk_pedido_detail_inventario_master1_idx (inv_master ASC) ,
  CONSTRAINT fk_pedido_detail_inventario_master1
    FOREIGN KEY (inv_master )
    REFERENCES inventario_master (inv_master )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_pedido_detail_pedido1
    FOREIGN KEY (idpedido )
    REFERENCES pedido (idpedido )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table factura_detail
-- -----------------------------------------------------
DROP TABLE IF EXISTS factura_detail ;

CREATE  TABLE IF NOT EXISTS factura_detail (
  idfactura_detail INT(11) NOT NULL AUTO_INCREMENT ,
  idpedido_detail INT(11) NOT NULL ,
  idfactura INT(11) NOT NULL DEFAULT '0' ,
  cantidad INT(10) NOT NULL DEFAULT '0' ,
  monto DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
  PRIMARY KEY (idfactura_detail, idpedido_detail) ,
  INDEX fk_factura_detail_factura1_idx (idfactura ASC) ,
  INDEX fk_factura_detail_pedido_detail1_idx (idpedido_detail ASC) ,
  CONSTRAINT fk_factura_detail_factura1
    FOREIGN KEY (idfactura )
    REFERENCES factura (idfactura )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_factura_detail_pedido_detail1
    FOREIGN KEY (idpedido_detail )
    REFERENCES pedido_detail (idpedido_detail )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table inventario_transaccion
-- -----------------------------------------------------
DROP TABLE IF EXISTS inventario_transaccion ;

CREATE  TABLE IF NOT EXISTS inventario_transaccion (
  inv_trans INT(11) NOT NULL AUTO_INCREMENT ,
  inv_master INT(11) NOT NULL ,
  cantidad INT(11) NOT NULL ,
  transaccion ENUM('entrada','salida') NOT NULL DEFAULT 'entrada' ,
  idproducto INT(10) NOT NULL ,
  tipo_transaccion VARCHAR(45) NOT NULL ,
  PRIMARY KEY (inv_trans) ,
  INDEX fk_inventario_transaccion_inventario_master1_idx (inv_master ASC) ,
  INDEX fk_inventario_transaccion_producto1_idx (idproducto ASC) ,
  CONSTRAINT fk_inventario_transaccion_inventario_master1
    FOREIGN KEY (inv_master )
    REFERENCES inventario_master (inv_master )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_inventario_transaccion_producto1
    FOREIGN KEY (idproducto )
    REFERENCES producto (idproducto )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table menu_categoria
-- -----------------------------------------------------
DROP TABLE IF EXISTS menu_categoria ;

CREATE  TABLE IF NOT EXISTS menu_categoria (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  nombre VARCHAR(45) NOT NULL DEFAULT '' ,
  imagen VARCHAR(150) NOT NULL DEFAULT '' ,
  PRIMARY KEY (id) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table menu
-- -----------------------------------------------------
DROP TABLE IF EXISTS menu ;

CREATE  TABLE IF NOT EXISTS menu (
  menu_id INT(11) NOT NULL AUTO_INCREMENT ,
  page VARCHAR(45) NOT NULL DEFAULT '' ,
  nombre VARCHAR(200) NOT NULL DEFAULT '' ,
  modulo VARCHAR(45) NOT NULL DEFAULT '' ,
  image VARCHAR(155) NOT NULL DEFAULT '' ,
  categoria_id INT(11) NOT NULL ,
  PRIMARY KEY (menu_id) ,
  INDEX fk_menu_menu_categoria1_idx (categoria_id ASC) ,
  CONSTRAINT fk_menu_menu_categoria1
    FOREIGN KEY (categoria_id )
    REFERENCES menu_categoria (id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table presentacion_tipo
-- -----------------------------------------------------

DROP TABLE IF EXISTS presentacion_tipo ;

CREATE  TABLE IF NOT EXISTS presentacion_tipo (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  descripcion VARCHAR(100) NOT NULL ,
  unidades INT(11) NOT NULL DEFAULT '1' ,
  padre INT(11) NULL DEFAULT NULL ,
  cantidad INT(11) NOT NULL DEFAULT '0' ,
  isMaster ENUM('Y','N') NOT NULL DEFAULT 'Y' ,
  isPivote ENUM('Y','N') NOT NULL DEFAULT 'N' ,
  PRIMARY KEY (id) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table producto_presentacion
-- -----------------------------------------------------
DROP TABLE IF EXISTS producto_presentacion ;

CREATE  TABLE IF NOT EXISTS producto_presentacion (
  presentacion_tipo_id INT(11) NOT NULL ,
  producto_idproducto INT(10) NOT NULL ,
  precio_venta DECIMAL(10,2) NOT NULL ,
  INDEX fk_producto_presentacion_presentacion_tipo1_idx (presentacion_tipo_id ASC) ,
  PRIMARY KEY (producto_idproducto, presentacion_tipo_id) ,
  CONSTRAINT fk_producto_presentacion_presentacion_tipo1
    FOREIGN KEY (presentacion_tipo_id )
    REFERENCES presentacion_tipo (id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_producto_presentacion_producto1
    FOREIGN KEY (producto_idproducto )
    REFERENCES producto (idproducto )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COMMENT = 'tabla que registra los precios por presentacion de un produc';


-- -----------------------------------------------------
-- Table ubicacion
-- -----------------------------------------------------

DROP TABLE IF EXISTS ubicacion ;

CREATE  TABLE IF NOT EXISTS ubicacion (
  ubica_id INT(10) NOT NULL AUTO_INCREMENT ,
  nombre VARCHAR(50) NOT NULL DEFAULT '' ,
  padre INT(10) NOT NULL ,
  PRIMARY KEY (ubica_id) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table usuario_acceso
-- -----------------------------------------------------

DROP TABLE IF EXISTS usuario_acceso ;

CREATE  TABLE IF NOT EXISTS usuario_acceso (
  userid INT(10) NOT NULL ,
  menu_id INT(11) NOT NULL ,
  type_priv VARCHAR(45) NOT NULL DEFAULT '' ,
  PRIMARY KEY (userid, menu_id) ,
  INDEX fk_uaurio_acceso_usuario1_idx (userid ASC) ,
  INDEX fk_uaurio_acceso_menu1_idx (menu_id ASC) ,
  CONSTRAINT fk_uaurio_acceso_menu1
    FOREIGN KEY (menu_id )
    REFERENCES menu (menu_id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_uaurio_acceso_usuario1
    FOREIGN KEY (userid )
    REFERENCES usuario (userid )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table inventario_ubicacion
-- -----------------------------------------------------
DROP TABLE IF EXISTS inventario_ubicacion ;

CREATE  TABLE IF NOT EXISTS inventario_ubicacion (
  inv_master INT(11) NOT NULL ,
  ubica_id INT(10) NOT NULL ,
  INDEX fk_inventario_ubicacion_inventario_master1_idx (inv_master ASC) ,
  INDEX fk_inventario_ubicacion_ubicacion1_idx (ubica_id ASC) ,
  CONSTRAINT fk_inventario_ubicacion_inventario_master1
    FOREIGN KEY (inv_master )
    REFERENCES inventario_master (inv_master )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_inventario_ubicacion_ubicacion1
    FOREIGN KEY (ubica_id )
    REFERENCES ubicacion (ubica_id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

ALTER TABLE inventario_master CHANGE precio_costo precio_costo DECIMAL( 10, 5 ) NOT NULL DEFAULT '0.00';
ALTER TABLE cliente CHANGE COLUMN codigo codigo VARCHAR(45) NULL DEFAULT '';

ALTER TABLE cliente CHANGE COLUMN razon_social razon_social VARCHAR(150) NULL  ,
                    CHANGE COLUMN nit nit VARCHAR(45) NULL  ,
                    CHANGE COLUMN direccion direccion TEXT NULL  ,
                    CHANGE COLUMN telefono telefono VARCHAR(200) NULL  ,
                    CHANGE COLUMN facturar_a facturar_a VARCHAR(150) NULL  ,
                    CHANGE COLUMN email email VARCHAR(100) NULL  ;

ALTER TABLE pedido ADD descuento DECIMAL( 10, 2 ) NOT NULL; 

ALTER TABLE inventario_master ADD presentacion_ingreso INT NOT NULL ;