# Lo de Carlitos — Sistema de Gestión de Ventas

Aplicación web PHP/MySQL con patrón MVC para gestionar ventas de productos, clientes, cobranzas y cheques.

---

## Requisitos

| Componente | Versión mínima |
|------------|---------------|
| PHP | 7.4+ |
| MySQL | 5.7+ (o MariaDB 10.3+) |
| Apache | con `mod_rewrite` habilitado |

---

## Instalación

### 1. Copiar archivos

Copiá la carpeta `lo_de_carlitos/` dentro del directorio raíz de tu servidor web (por ejemplo `htdocs/` en XAMPP o `www/` en WAMP).

```
/xampp/htdocs/lo_de_carlitos/
```

### 2. Importar la base de datos

1. Abrí **phpMyAdmin** (o tu cliente MySQL preferido).
2. Creá la base de datos (opcional — el script lo hace automáticamente).
3. Importá el archivo:

```bash
mysql -u root -p < sql/lo_de_carlitos.sql
```

O desde phpMyAdmin: **Importar → Seleccionar archivo** → `sql/lo_de_carlitos.sql` → Ejecutar.

El script crea la base de datos `lo_de_carlitos`, todas las tablas e inserta el usuario administrador de prueba.

### 3. Configurar la conexión

Editá `config/config.php` con los datos de tu servidor:

```php
define('DB_HOST', '127.0.0.1:3306');  // host:puerto de MySQL
define('DB_NAME', 'lo_de_carlitos');
define('DB_USER', 'root');            // usuario MySQL
define('DB_PASS', '');                // contraseña MySQL
```

### 4. Habilitar mod_rewrite en Apache

Asegurate de que el `.htaccess` esté activo. En tu `httpd.conf` o `apache2.conf`:

```apache
<Directory "/ruta/al/proyecto">
    AllowOverride All
</Directory>
```

Y que el módulo esté habilitado:

```bash
a2enmod rewrite   # en Ubuntu/Debian
```

### 5. Acceder al sistema

Abrí en tu navegador:

```
http://localhost/lo_de_carlitos/
```

---

## Usuario de prueba

| Campo    | Valor                         |
|----------|-------------------------------|
| Usuario  | `admin`                       |
| Email    | `admin@lodecarlitos.com`      |
| Password | `password`                    |
| Rol      | `admin`                       |

> **⚠️ Cambiar la contraseña del admin antes de usar en producción.**

---

## Estructura de carpetas

```
lo_de_carlitos/
├── index.php                  ← Front Controller (único punto de entrada)
├── .htaccess                  ← Redirige todo a index.php
├── README.md
├── config/
│   └── config.php             ← URL_BASE, credenciales DB, constantes
├── app/
│   ├── controllers/           ← Un archivo por módulo
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── ClienteController.php
│   │   ├── ProveedorController.php
│   │   ├── ProductoController.php
│   │   ├── MedioPagoController.php
│   │   ├── VentaController.php
│   │   ├── CobranzaController.php
│   │   ├── ChequeController.php
│   │   └── UsuarioController.php
│   ├── models/                ← Acceso a datos (PDO + Singleton)
│   │   ├── Database.php
│   │   ├── Cliente.php
│   │   ├── Proveedor.php
│   │   ├── Producto.php
│   │   ├── MedioPago.php
│   │   ├── Venta.php
│   │   ├── DetalleVenta.php
│   │   ├── VentaMedioPago.php
│   │   ├── Cheque.php
│   │   ├── CtaCteCliente.php
│   │   ├── DetalleCtaCte.php
│   │   ├── Usuario.php
│   │   └── Dashboard.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php     ← Navbar Bootstrap + head HTML
│       │   └── footer.php     ← Scripts JS + cierre body/html
│       ├── auth/login.php
│       ├── home/index.php     ← Dashboard con indicadores
│       ├── clientes/
│       ├── proveedores/
│       ├── productos/
│       ├── medios_pago/
│       ├── ventas/
│       ├── cobranzas/
│       ├── cheques/
│       └── usuarios/
├── public/
│   ├── css/app.css            ← Estilos propios (complementa Bootstrap 5)
│   ├── js/app.js              ← JS propio (ventas dinámicas, confirmaciones)
│   └── img/
└── sql/
    └── lo_de_carlitos.sql     ← Script completo de la base de datos
```

---

## Módulos del sistema

| Módulo | URL | Descripción |
|--------|-----|-------------|
| Dashboard | `/` | Indicadores generales |
| Clientes | `/clientes` | CRUD de clientes |
| Proveedores | `/proveedores` | CRUD de proveedores |
| Productos | `/productos` | CRUD de productos |
| Medios de Pago | `/medios-pago` | CRUD de medios de pago |
| Ventas | `/ventas` | Registro y listado de ventas |
| Cobranzas | `/cobranzas` | Gestión de cuentas corrientes |
| Cheques | `/cheques` | Ciclo de vida de cheques |
| Usuarios | `/usuarios` | Gestión de usuarios (solo admin) |

---

## Stack tecnológico

- **Backend:** PHP 7.4+ — patrón MVC con Front Controller
- **Base de datos:** MySQL — PDO con Singleton y prepared statements
- **Frontend:** Bootstrap 5.3, Font Awesome 6.5, JS vanilla
- **Seguridad:** `password_hash` / `password_verify`, `htmlspecialchars` en outputs, sesiones PHP
