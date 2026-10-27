-- ==============================================================================
-- Migración 001: Saldo a favor, filtros, links, quitar descuento y cheques rechazados
-- Base de datos: Sistema de Gestión Mercado Central
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- Sub-Task A: Quitar columna descuento de detalle_ventas
-- ------------------------------------------------------------------------------
ALTER TABLE detalle_ventas DROP COLUMN descuento;

-- ------------------------------------------------------------------------------
-- Sub-Task D: Cheque rechazado traslada deuda a cuenta corriente
-- Trazabilidad en detalle_cta_cte_cliente (concepto + vínculo al cheque)
-- ------------------------------------------------------------------------------
ALTER TABLE detalle_cta_cte_cliente
    ADD COLUMN concepto VARCHAR(100) NULL,
    ADD COLUMN id_cheque INT NULL,
    ADD CONSTRAINT fk_detalle_cta_cte_cheque FOREIGN KEY (id_cheque) REFERENCES cheque(id_cheque);

-- ------------------------------------------------------------------------------
-- Sub-Task E: Saldo a favor del cliente
-- ------------------------------------------------------------------------------
ALTER TABLE medio_pago
    MODIFY COLUMN medio_pago ENUM('Efectivo','E-cheque','Cheque','Transferencia','Mercado_pago','Cuenta Corriente','Saldo a favor')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO medio_pago (medio_pago) VALUES ('Saldo a favor');

CREATE TABLE IF NOT EXISTS saldo_cliente_movimiento (
    id_saldo_cliente_movimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('Ingreso','Uso') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    id_medio_pago INT NULL,
    id_venta INT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_medio_pago) REFERENCES medio_pago(id_medio_pago),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
);
