-- Nueva fecha de registro para pedidos creados desde la aplicación.
-- Se deja NULL para registros históricos porque no existe una fecha de creación fiable.
ALTER TABLE PEDIDO
    ADD COLUMN Fecha_registro DATETIME NULL AFTER Estado;
