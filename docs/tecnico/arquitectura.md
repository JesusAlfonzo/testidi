# Arquitectura Técnica - SGCI-IDI

## 1. Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|------------|---------|
| Backend | Laravel | 12.x |
| Frontend | AdminLTE | 3.x |
| CSS | Bootstrap | 5.x |
| Base de datos | MySQL/SQLite | - |
| Autenticación | Laravel Breeze/Spatie | - |
| Reportes | DomPDF, Maatwebsite | - |

## 2. Estructura de Directorios

```
app/
├── Console/
├── Events/
├── Exceptions/
├── Exports/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Controladores del panel admin
│   │   └── Auth/           # Controladores de autenticación
│   ├── Middleware/
│   ├── Requests/          # Validaciones de formularios
│   └── Resources/         # API Resources
├── Jobs/
├── Listeners/
├── Mail/
├── Models/
├── Notifications/
├── Policies/
├── Providers/
├── Rules/
├── Services/
└── Traits/
```

## 3. Patrones de Diseño

### MVC (Model-View-Controller)
- **Models**: Located in `app/Models/`
- **Views**: Located in `resources/views/admin/`
- **Controllers**: Located in `app/Http/Controllers/`

### Repository Pattern
- Servicios en `app/Services/`

### Policy Pattern
- Políticas en `app/Policies/`

## 4. Middlewares Utilizados

| Middleware | Función |
|------------|---------|
| auth | Verifica autenticación |
| role | Verifica rol del usuario |
| permission | Verifica permisos específicos |
| verified | Verifica email confirmado |

## 5. Servicios Externos

### Spatie (Roles y Permisos)
- Gestión de permisos y roles
- Documentación: https://spatie.be/docs/laravel-permission

### AdminLTE
- Tema de administración
- Documentación: https://adminlte.io/docs/3.x/

## 6. Configuración de Entorno

Variables importantes en `.env`:
```
APP_NAME="SGCI-IDI"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sgci_idi
DB_USERNAME=root
DB_PASSWORD=
```

## 7. Comandos Útiles

```bash
# Instalar dependencias
composer install
npm install

# Migrar base de datos
php artisan migrate

# Seed de datos (roles y permisos)
php artisan db:seed --class=RolesAndPermissionsSeeder

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas
php artisan route:list

# Generar clave de aplicación
php artisan key:generate
```

## 8. Dependencias Principales

```json
{
    "laravel/framework": "^12.0",
    "spatie/laravel-permission": "^6.0",
    "dompdf/dompdf": "^3.0",
    "maatwebsite/excel": "^3.1",
    "yajra/laravel-datatables-oracle": "^11.0"
}
```

---

*Última actualización: 2026*
