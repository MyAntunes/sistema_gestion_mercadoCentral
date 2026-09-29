-- ══════════════════════════════════════════════════════════════════
-- Lo de Carlitos — Script de base de datos
-- ══════════════════════════════════════════════════════════════════

DROP DATABASE IF EXISTS lo_de_carlitos;
CREATE DATABASE IF NOT EXISTS lo_de_carlitos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE lo_de_carlitos;

CREATE TABLE IF NOT EXISTS cliente (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    nombre_apellido VARCHAR(50) NOT NULL,
    domicilio VARCHAR(50),
    localidad VARCHAR(50),
    codigo_postal INT,
    telefono_1 VARCHAR(20) NOT NULL,
    telefono_2 VARCHAR(20) NOT NULL,
    cuil VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS proveedor (
    id_proveedor INT PRIMARY KEY AUTO_INCREMENT,
    razon_social VARCHAR(50) NOT NULL,
    direccion VARCHAR(50),
    telefono_1 VARCHAR(50) NOT NULL,
    telefono_2 VARCHAR(50) NOT NULL,
    cuit VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS medio_pago (
    id_medio_pago INT PRIMARY KEY AUTO_INCREMENT,
    medio_pago ENUM('Efectivo','E-cheque','Cheque','Transferencia','Mercado_pago','Cuenta Corriente')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
);

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL,
    estado ENUM('Activo','Inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS producto (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(30),
    especie VARCHAR(30),
    precio_costo DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    id_proveedor INT NOT NULL,
    FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
);

CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS venta_medio_pago (
    id_venta_medio_pago INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_medio_pago INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_medio_pago) REFERENCES medio_pago(id_medio_pago)
);

CREATE TABLE IF NOT EXISTS cheque (
    id_cheque INT PRIMARY KEY AUTO_INCREMENT,
    fecha_pago DATE NOT NULL,
    fecha_emision DATE NOT NULL,
    numero_cheque INT NOT NULL UNIQUE,
    banco VARCHAR(20) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo_cheque ENUM('Físico','E-cheque') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Físico',
    estado ENUM('Cartera','Depositado','Cobrado','Rechazado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    observaciones VARCHAR(255),
    id_medio_pago INT NOT NULL,
    FOREIGN KEY (id_medio_pago) REFERENCES medio_pago(id_medio_pago)
);

CREATE TABLE IF NOT EXISTS venta_medio_pago_cheque (
    id_venta_medio_pago_cheque INT PRIMARY KEY AUTO_INCREMENT,
    id_cheque INT NOT NULL,
    id_venta_medio_pago INT NOT NULL,
    FOREIGN KEY (id_cheque) REFERENCES cheque(id_cheque),
    FOREIGN KEY (id_venta_medio_pago) REFERENCES venta_medio_pago(id_venta_medio_pago)
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id_detalle_ventas INT PRIMARY KEY AUTO_INCREMENT,
    precio_unitario DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    descuento DECIMAL(10,2),
    id_producto INT NOT NULL,
    id_venta INT NOT NULL,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
);

-- NOTA: coma extra del original eliminada (línea 107 del SQL fuente)
CREATE TABLE IF NOT EXISTS cta_cte_cliente (
    id_cta_cte_cliente INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    saldo DECIMAL NOT NULL,
    id_venta INT NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
);

CREATE TABLE IF NOT EXISTS detalle_cta_cte_cliente (
    id_detalle_cta_cte_cliente INT PRIMARY KEY AUTO_INCREMENT,
    monto DECIMAL NOT NULL,
    fecha DATE NOT NULL,
    id_cta_cte_cliente INT NOT NULL,
    FOREIGN KEY (id_cta_cte_cliente) REFERENCES cta_cte_cliente(id_cta_cte_cliente)
);

