-- Ejercicios UNCOMPRES
SELECT apellidos,nombre,telefono 
FROM clientes
ORDER BY apellidos,nombre;
-- ejercio dos
SELECT * 
FROM productos
WHERE stock<=50;
-- 3 ejercicio
SELECT *
FROM empleados
WHERE apellidos like "a%"
-- cliente mas su pedidos
SELECT 	c.nombre,
				c.apellidos,
				p.fecha_compra,
				p.cantidad
FROM pedidos AS p, clientes as c
WHERE c.Id = p.cod_cliente
-- otra forma
SELECT 	c.nombre,
				c.apellidos,
				p.fecha_compra,
				p.cantidad
FROM pedidos AS p
INNER JOIN clientes c ON c.Id = p.cod_cliente;
-- 005.

SELECT 	e.apellidos,
				e.nombre, 
				COUNT(e.id) as TotalVendido
FROM empleados e 
INNER JOIN pedidos p ON e.id=p.cod_empleado
GROUP BY e.id

SELECT 	e.apellidos,
				e.nombre, 
				COUNT(e.id) as TotalVendido
FROM empleados e 
LEFT JOIN pedidos p ON e.id=p.cod_empleado
GROUP BY e.id

-- OFFSET
SELECT  codBarras, descripcion,precio_unitario
FROM productos
ORDER BY precio_unitario DESC

-- saber el nombre del cliente, producto que compro precio, empleado que atendio
SELECT c.ci,c.nombre as 'nombreCliente',
				c.apellidos as 'apellidoCliente',
				p.cantidad,
				pp.precio_unitario,
				prod.descripcion as 'nombreProducto',
				e.nombre as 'nombreEmpleado',
				e.apellidos as 'apellidoEmpleado'
FROM clientes c
INNER JOIN pedidos p ON p.cod_cliente=c.id
INNER JOIN pedido_productos pp on pp.cod_pedido=p.id
INNER JOIN productos prod ON prod.id=pp.cod_producto
INNER JOIN empleados e ON e.id=p.cod_empleado
WHERE c.ci='6789012'