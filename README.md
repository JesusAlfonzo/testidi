# Sistema de Gestión de Inventario

Este es un sistema de gestión de inventario desarrollado con el framework Laravel. La aplicación permite administrar productos, inventario, proveedores, solicitudes y más, a través de una interfaz de administración robusta y segura.

## ✨ Características Principales

- **Gestión de Autenticación y Usuarios**: Sistema de inicio de sesión seguro. CRUD completo para usuarios.
- **Roles y Permisos**: Control de acceso granular utilizando `spatie/laravel-permission` para definir qué acciones puede realizar cada usuario.
- **Módulos Maestros**:
    - CRUD para **Categorías**
    - CRUD para **Marcas**
    - CRUD para **Unidades de Medida**
    - CRUD para **Ubicaciones** (almacenes, estanterías)
    - CRUD para **Proveedores**
- **Gestión de Inventario**:
    - CRUD completo para **Productos**.
    - Creación de **Kits de Productos**.
- **Movimientos de Inventario**:
    - **Entradas de Stock**: Registro de nuevos productos que ingresan al inventario.
    - **Solicitudes de Inventario**: Flujo de aprobación para la salida de productos, donde un administrador debe aprobar o rechazar cada solicitud.
- **Reportes**:
    - **Stock Actual**: Visualización del inventario disponible.
    - **Movimientos**: Historial de todas las solicitudes (aprobadas/rechazadas).
    - **Kardex por Producto**: Seguimiento detallado de entradas y salidas para un producto específico.
- **Interfaz de Administración**: Construida con el popular template [AdminLTE](https://adminlte.io/), ofreciendo una experiencia de usuario limpia y responsiva.

## 🛠️ Stack Tecnológico

- **Backend**: PHP 8.2, Laravel 12
- **Frontend**: Vite, JavaScript, Sass, Bootstrap 5
- **UI Admin**: [JeroenNoten/Laravel-AdminLTE](https://github.com/JeroenNoten/Laravel-AdminLTE)
- **Base de Datos**: Compatible con MySQL, PostgreSQL, SQLite (configurable en `.env`).
- **Gestión de Dependencias**: Composer (PHP), pnpm (JavaScript).

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para configurar el entorno de desarrollo local.

### Prerrequisitos

- PHP >= 8.2
- Composer
- Node.js y pnpm
- Un servidor de base de datos (ej. MySQL, MariaDB).

### Pasos de Instalación

1.  **Clonar el repositorio**:
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd <NOMBRE_DEL_DIRECTORIO>
    ```

2.  **Copiar el archivo de entorno**:
    ```bash
    cp .env.example .env
    ```

3.  **Configurar el archivo `.env`**:
    Abre el archivo `.env` y configura las credenciales de la base de datos (`DB_*`) y la URL de la aplicación (`APP_URL`).
    ```ini
    APP_NAME="Sistema de Inventario"
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nombre_de_tu_bd
    DB_USERNAME=tu_usuario_bd
    DB_PASSWORD=tu_password_bd
    ```

4.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```

5.  **Instalar dependencias de JavaScript**:
    Dado que existe un `pnpm-lock.yaml`, se recomienda usar `pnpm`.
    ```bash
    pnpm install
    ```

6.  **Generar la clave de la aplicación**:
    ```bash
    php artisan key:generate
    ```

7.  **Ejecutar las migraciones y los seeders**:
    Esto creará la estructura de la base de datos y la llenará con datos iniciales (roles, permisos y un usuario de prueba).
    ```bash
    php artisan migrate --seed
    ```
    El usuario de prueba creado es:
    - **Email**: `test@example.com`
    - **Contraseña**: `password` (o la que se defina en el `UserFactory`)

### Ejecutar la Aplicación

1.  **Iniciar el servidor de desarrollo de Laravel**:
    ```bash
    php artisan serve
    ```

2.  **Iniciar el servidor de desarrollo de Vite**:
    En una terminal separada, ejecuta:
    ```bash
    pnpm run dev
    ```

3.  **Acceder a la aplicación**:
    Abre tu navegador y visita [http://localhost:8000](http://localhost:8000).

## ✅ Pruebas

Para ejecutar el conjunto de pruebas de la aplicación, utiliza el siguiente comando:
```bash
php artisan test
```