

INSERT INTO menu_categoria (id,nombre,imagen) VALUES (1,'Usuarios','images/codepo8.png');
INSERT INTO menu_categoria (id,nombre,imagen) VALUES (2,'Inventario','images/zip.png');
INSERT INTO menu_categoria (id,nombre,imagen) VALUES (3,'Facturación','images/compras.png');


INSERT INTO menu (menu_id,page,nombre,modulo,image,categoria_id) VALUES (2,'usuario_registro','Administracion de usuarios','usuario','images/codepo8.png',1);
INSERT INTO menu (menu_id,page,nombre,modulo,image,categoria_id) VALUES (4,'usuario_reporte','Reporte de usuarios','usuario','images/leftjust.png',1);
INSERT INTO menu (menu_id,page,nombre,modulo,image,categoria_id) VALUES (8,'inventario_marcas','Marcas','inventario','',2);


INSERT INTO usuario (userid, nickname, password, tipo, nombreCompleto, nombres, apellidos, fecha_registro, hora, active) VALUES
        (NULL, 'admin',md5('admin') ,'admin', 'Edward Acu', 'Edward', 'Acu', '2013-01-10', '00:00:00', 'Y');

INSERT INTO estados (id, nombre) VALUES
                ('1','activo'),
                ('2','anulado')
