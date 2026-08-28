INSERT INTO Clientes (ci, nombre, apellidos, direccion, telefono) VALUES
('1234567', 'Juan', 'Pérez Gómez', 'Calle Flores 123', '71234567'),
('2345678', 'María', 'Rodríguez López', 'Av. Las Américas 456', '72345678'),
('3456789', 'Carlos', 'Sánchez Martínez', 'Zona Central, Calle 4', '73456789'),
('4567890', 'Ana', 'García Fernández', 'Barrio Lindo, Pasaje A', '74567890'),
('5678901', 'Luis', 'Torres Mendoza', 'Calle Murillo 789', '75678901'),
('6789012', 'Elena', 'Ramírez Espinoza', 'Av. Bush 1011', '76789012'),
('7890123', 'Pedro', 'Flores Quispe', 'Urbanización Norte', '77890123'),
('8901234', 'Laura', 'Castro Benítez', 'Calle Arce 246', '78901234'),
('9012345', 'Diego', 'Morales Vargas', 'Av. Ejército 135', '79012345'),
('0123456', 'Sofía', 'Suárez Ortiz', 'Barrio Nuevo, Calle Ojo', '70123456'),
('1122334', 'Jorge', 'Rojas Mamani', 'Calle Sucre 555', '61223344'),
('2233445', 'Lucía', 'Guzmán Chávez', 'Av. Melchor Pinto', '62334455'),
('3344556', 'Andrés', 'Villca Condori', 'Zona Sud, Calle Ballivián', '63445566'),
('4455667', 'Camila', 'Miranda Flores', 'Calle Ingavi 888', '64556677'),
('5566778', 'Gabriel', 'Soliz Gutiérrez', 'Av. Banzer Km 5', '65667788'),
('6677889', 'Valentina', 'Pinto Alarcón', 'Barrio El Trompillo', '66778899'),
('7788990', 'Alejandro', 'Cruz Choque', 'Calle Potosí 321', '67889900'),
('8899001', 'Mariana', 'Salazar Ríos', 'Av. San Martín 99', '68990011'),
('9900112', 'Ricardo', 'Arias Peralta', 'Urbanización El Recreo', '69001122'),
('1020304', 'Gabriela', 'Medina Herrera', 'Calle Bolívar 444', '60203040');


INSERT INTO EMPLEADOS (ci, nombre, apellidos) VALUES
('5432109', 'Fernando', 'Mendoza Delgado'),
('6543210', 'Patricia', 'Luna Céspedes'),
('7654321', 'Roberto', 'Vaca Justiniano'),
('8765432', 'Daniela', 'Ribeiro Justiniano'),
('9876543', 'Gustavo', 'Paz Soldán'),
('1098765', 'Natalia', 'Siles Zuazo'),
('2109876', 'Javier', 'Prada Melgar'),
('3210987', 'Claudia', 'Arteaga Aguilera'),
('4321098', 'Mauricio', 'Suárez Landívar'),
('5432108', 'Verónica', 'Antelo Hurtado'),
('6543217', 'Sergio', 'Pinto Saucedo'),
('7654326', 'Paola', 'Roca Balcázar'),
('8765435', 'Marcelo', 'Ortiz Mercado'),
('9876544', 'Vanessa', 'Chávez Villagómez'),
('1098763', 'Hugo', 'Peña Montaño'),
('2109872', 'Tatiana', 'Ríos Justiniano'),
('3210981', 'Álvaro', 'Camacho Terrazas'),
('4321090', 'Liliana', 'Vargas Escalante'),
('5432099', 'René', 'Gutiérrez Claros'),
('6543108', 'Diana', 'Zeballos Villegas');

INSERT INTO productos (codBarras, descripcion, stock, precio_unitario) VALUES
('750105531001', 'Arroz Integral 1kg', 50, 12.50),
('750105531002', 'Fideos Tallarín 500g', 120, 5.80),
('750105531003', 'Aceite de Girasol 1L', 45, 18.20),
('750105531004', 'Leche Entera 1L', 85, 7.50),
('750105531005', 'Azúcar Blanca 1kg', 200, 6.00),
('750105531006', 'Café Soluble 200g', 30, 28.90),
('750105531007', 'Galletas de Agua Pack', 150, 4.20),
('750105531008', 'Detergente en Polvo 1kg', 40, 15.60),
('750105531009', 'Jabón Líquido 500ml', 65, 11.30),
('750105531010', 'Champú Anticaspa 400ml', 25, 24.50),
('750105531011', 'Papel Higiénico 4 rollos', 90, 8.50),
('750105531012', 'Esponja de Cocina 3X', 110, 3.50),
('750105531013', 'Atún en Aceite 170g', 75, 9.80),
('750105531014', 'Salsa de Tomate 400g', 95, 5.20),
('750105531015', 'Sal Yodada 1kg', 180, 2.50),
('750105531016', 'Vinagre Blanco 500ml', 40, 4.80),
('750105531017', 'Mayonesa Doypack 250g', 60, 7.20),
('750105531018', 'Mermelada de Frutilla 350g', 35, 13.40),
('750105531019', 'Refresco de Cola 2L', 100, 11.00),
('750105531020', 'Agua Mineral sin Gas 2L', 140, 5.00);

select * FROM Clientes;
SELECT * FROM empleados;
SELECT * FROM productos;

INSERT INTO PEDIDOS (cod_cliente, fecha_compra, cantidad, cod_empleado) VALUES
(1, '2026-05-10 10:30:00', 3, 1),
(2, '2026-05-11 14:15:00', 5, 2),
(3, '2026-05-12 09:00:00', 2, 3),
(4, '2026-05-12 16:45:00', 1, 4),
(5, '2026-05-13 11:20:00', 4, 5),
(6, '2026-05-14 18:00:00', 10, 6),
(7, '2026-05-15 13:10:00', 2, 7),
(8, '2026-05-16 15:35:00', 6, 8),
(9, '2026-05-17 10:05:00', 3, 9),
(10, '2026-05-17 12:50:00', 7, 10);


INSERT INTO PEDIDO_PRODUCTOS (cod_producto, cod_pedido, cantidad, precio_unitario, descuento) VALUES
(1, 1, 3, 12.50, 0.00),   -- 3 Arroz para el Pedido 1
(4, 2, 5, 7.50, 2.00),    -- 5 Leche para el Pedido 2 (con descuento)
(6, 3, 2, 28.90, 0.00),   -- 2 Café para el Pedido 3
(10, 4, 1, 24.50, 1.50),  -- 1 Champú para el Pedido 4
(3, 5, 4, 18.20, 0.00),   -- 4 Aceite para el Pedido 5
(2, 6, 10, 5.80, 5.00),   -- 10 Fideos para el Pedido 6
(19, 7, 2, 11.00, 0.00),  -- 2 Refrescos para el Pedido 7
(11, 8, 6, 8.50, 0.00),   -- 6 Papel Higiénico para el Pedido 8
(13, 9, 3, 9.80, 1.00),   -- 3 Atún para el Pedido 9
(20, 10, 7, 5.00, 0.00);  -- 7 Agua para el Pedido 10


INSERT INTO EMPLEADO_PEDIDOS (cod_pedido, cod_empleado, fecha) VALUES
(1, 1, '2026-05-10'),
(2, 2, '2026-05-11'),
(3, 3, '2026-05-12'),
(4, 4, '2026-05-12'),
(5, 5, '2026-05-13'),
(6, 6, '2026-05-14'),
(7, 7, '2026-05-15'),
(8, 8, '2026-05-16'),
(9, 9, '2026-05-17'),
(10, 10, '2026-05-17');

SELECT * FROM pedidos;
SELECT * FROM pedido_productos;
SELECT * from empleado_pedidos;

