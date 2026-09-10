# Sistema de Gestión de Comercio de Verduras

Este repositorio contiene el diseño lógico y la documentación de la base de datos relacional orientada a un comercio minorista de verduras, 
estructurada bajo estrictas normas de normalización (3FN), control de stock por lotes, cuentas corrientes y trazabilidad de operaciones de seguridad de la información.

## Tecnologías y Arquitectura
- **Modelado DER:** Diagrams.net (Draw.io)
- **Base de Datos:** MySQL / Relacional
- **Seguridad y Auditoría:** Registro de accesos e inicios de sesión de usuarios (alineado con conceptos de ciberseguridad y control de privilegios).

## Diagrama Entidad-Relación (DER)
Puedes visualizar el diseño conceptual exportado en el repositorio:
- `docs/proyecto_puesto.drawio.svg`

## Características Principales y Ciberseguridad
- **Control de Stock y Trazabilidad:** Gestión estricta de entradas, salidas y mermas mediante lotes asociados directamente a las compras de proveedores.
- **Cuentas Corrientes y Finanzas:** Administración automatizada de saldos para clientes y proveedores, con soporte para pagos parciales, mixtos, cuentas corrientes y gestión de cheques.
- **Auditoría, Ciberseguridad y Credenciales:** Incorporación del módulo de **usuarios** y **logins**, diseñado bajo principios de control de acceso, trazabilidad de operaciones
-  y mitigación de riesgos (previniendo accesos no autorizados y manteniendo un registro de qué operador ejecuta cada venta o compra en el sistema).
