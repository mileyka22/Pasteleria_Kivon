-- ====================================================
-- INSERCIÓN DE 5 REGISTROS POR TABLA
-- ====================================================

-- 1. CLIENTES (5 registros)
INSERT INTO CLIENTE (Nombre, Apellidos, CI, Telefono, Direccion) VALUES
('Ana', 'Mariaca Flores', '1234567 LP', '71524311', 'Av. Arce, Edif. Los Pinos Ap. 4B'),
('Carlos', 'Mendoza Quispe', '7654321 SC', '60598744', 'Calle Murillo #450, Zona Central'),
('Mariana', 'Gutiérrez Rocha', '9876542 CB', '72011223', 'Av. América, Condominio El Sol'),
('José Luis', 'Mamani Vargas', '4567891 OR', '68541255', 'Av. 6 de Agosto, Nro 120'),
('Elena', 'Torrico Suárez', '3456782 TJ', '75099881', 'Barrio San Gerónimo, Calle Los Lapachos');

-- 2. USUARIOS (5 registros - Contraseñas simuladas en hash)
INSERT INTO USUARIO (Username, Permiso, Password_hash) VALUES
('admin_pasteleria', 'Administrador', '$2y$10$e0MYzXyjpJS7Pd0RVvMReOaWz0b7S6fTjGZ1VbH7CjU6k8x6yZi1.'),
('vendedor_sofia', 'Ventas', '$2y$10$K7rX9vLMb2JgE4fR2tY8uO1wP3qZ5xS7vN9mB1vC3xD5xF7zG9sI2.'),
('vendedor_lucas', 'Ventas', '$2y$10$H8sY0wMNc3KhF5gS3uZ9vP2xQ4rA6yT8wO0nC2wD4xE6yG8zH0tJ3.'),
('pastelero_malue', 'Produccion', '$2y$10$J9tZ1xNOd4LiG6hT4va0wQ3yR5sB7zU9xP1oD3xE5yF7zH9wI1uK4.'),
('repartidor_jose', 'Entrega', '$2y$10$K0uA2yOPe5MjH7iU5wb1xR4zS6tC8vV0yQ2pE4xF6yG8zI0xJ2vL5.');

-- 3. PERSONAL (5 registros - Enlazados a los 5 usuarios creados)
INSERT INTO PERSONAL (Nombre, Cargo, Telefono, Salario, id_usuario) VALUES
('Juan Pablo', 'Gerente General', '77255443', 6500.00, 1),
('Sofía Elena', 'Cajera Principal', '65122334', 2800.00, 2),
('Lucas Mateo', 'Cajero Turno Tarde', '70144552', 2800.00, 3),
('Juan Carlos', 'Maestro Pastelero', '60211447', 4200.00, 4),
('Pedro Alí', 'Repartidor', '78566331', 2500.00, 5);

-- 4. PRODUCTOS (5 registros - Registrados por el administrador id_usuario = 1)
INSERT INTO PRODUCTO (Nombre, Precio, Cantidad, Ingrediente, id_personal) VALUES
('Torta de Tres Leches Tradicional', 120.00, 15, 'Leche evaporada, crema de leche, leche condensada, harina, huevos', 1),
('Pastel de Chocolate Supremo', 150.00, 10, 'Chocolate amargo 70%, harina, mantequilla, esencia de vainilla', 1),
('Pie de Limón Mediano', 65.00, 20, 'Zumo de limón, base de galleta, merengue italiano', 1),
('Cheesecake de Frutilla', 140.00, 8, 'Queso crema, mermelada de frutilla, base de galleta dulce', 1),
('Cupcake de Vainilla (Docena)', 45.00, 25, 'Harina, azúcar, esencia de vainilla, chispas de colores', 1);

-- 5. PEDIDOS (5 registros - Relacionados a clientes y al personal de ventas id_personal = 2 o 3)
INSERT INTO PEDIDO (Estado, Detalle, id_cliente, id_personal) VALUES
('Entregado', 'Torta de tres leches con dedicatoria: Feliz Cumpleaños Mamá', 1, 2),
('Pendiente', 'Pastel de chocolate bajo en azúcar', 2, 2),
('En proceso', 'Pie de limón y una docena de cupcakes para las 15:00', 3, 3),
('Cancelado', 'El cliente solicitó anular el pedido de cheesecake', 4, 2),
('Entregado', 'Pedido express de 2 tortas de tres leches', 5, 3);

-- 6. PRODUCTO_PEDIDO (5 registros - Detalle de los productos en los pedidos)
INSERT INTO PRODUCTO_PEDIDO (id_producto, id_pedido, Cantidad, Precio_unitario) VALUES
(1, 1, 1, 120.00), -- Pedido 1: 1 Torta de Tres Leches (Total: 120.00)
(2, 2, 1, 150.00), -- Pedido 2: 1 Pastel de Chocolate (Total: 150.00)
(3, 3, 1, 65.00),  -- Pedido 3: 1 Pie de Limón
(5, 3, 1, 45.00),  -- Pedido 3: 1 Docena de Cupcakes (Total Pedido 3: 110.00)
(1, 5, 2, 120.00); -- Pedido 5: 2 Tortas de Tres Leches (Total: 240.00)

-- 7. VENTAS (5 registros - Cobros de los pedidos válidos y ventas directas)
-- Nota: El pedido 4 no se factura por estar cancelado, así que se usa una venta libre.
INSERT INTO VENTA (Total, Metodo_de_pago, id_personal, id_pedido) VALUES
(120.00, 'Efectivo', 1, 1),
(150.00, 'Transferencia QR', 2, 2),
(110.00, 'Tarjeta de Débito', 3, 3),
(140.00, 'Efectivo', 4, NULL), -- Venta directa en mesón (sin pedido previo)
(240.00, 'Transferencia QR', 5, 5);
-- Usuario 1 (admin_pasteleria) $\rightarrow$ Contraseña real: Admin123*Usuario 2 (vendedor_sofia) $\rightarrow$ Contraseña real: Sofia2026Usuario 3 (vendedor_lucas) $\rightarrow$ Contraseña real: Lucas987Usuario 4 (pastelero_juan) $\rightarrow$ Contraseña real: Pastelero5Usuario 5 (repartidor_pedro) $\rightarrow$ Contraseña real: PedroDelivery
