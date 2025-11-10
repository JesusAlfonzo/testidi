<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Asegúrate de usar el modelo User correcto

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Limpiar caché de permisos y roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear Permisos para la Gestión de Usuarios
        $permissions = [
            'dashboard_acceso',          // Permite acceder al dashboard general

            // Permisos para el Módulo de Seguridad (Usuarios y Roles)
            'usuarios_ver',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_eliminar',
            'roles_ver',
            'roles_crear',
            'roles_editar',
            'roles_eliminar',

            //MÓDULOS MAESTROS - CATEGORÍAS
            'categorias_ver',
            'categorias_crear',
            'categorias_editar',
            'categorias_eliminar',

            // MÓDULOS MAESTROS - UNIDADES
            'unidades_ver',
            'unidades_crear',
            'unidades_editar',
            'unidades_eliminar',

            // MÓDULOS MAESTROS - UBICACIONES
            'ubicaciones_ver',
            'ubicaciones_crear',
            'ubicaciones_editar',
            'ubicaciones_eliminar',

            // MÓDULOS MAESTROS - MARCAS
            'marcas_ver',
            'marcas_crear',
            'marcas_editar',
            'marcas_eliminar',

            //  MÓDULOS MAESTROS - PROVEEDORES
            'proveedores_ver',
            'proveedores_crear',
            'proveedores_editar',
            'proveedores_eliminar',

            // MÓDULO INVENTARIO - PRODUCTOS
            'productos_ver',
            'productos_crear',
            'productos_editar',
            'productos_eliminar',

            // MÓDULO INVENTARIO - ENTRADAS
            'entradas_ver',
            'entradas_crear',
            'entradas_eliminar', // No se recomienda editar un movimiento de stock, solo eliminar.

            // NUEVOS PERMISOS: SOLICITUDES DE INVENTARIO
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_aprobar',

            // MÓDULO INVENTARIO - SOLICITUDES DE SALIDA
            'solicitudes_ver', // Ver listado general (pendientes, aprobadas, rechazadas)
            'solicitudes_crear', // Crear nuevas solicitudes (para el empleado)
            'solicitudes_aprobar', // Aprobar o rechazar solicitudes (para el jefe/admin)

            'reportes_ver',
            'reportes_stock',
            'reportes_movimientos',

            'kardex_ver'
        ];

        foreach ($permissions as $permission) {
            // Se usa firstOrCreate para evitar duplicados si se corre el seeder múltiples veces
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Crear Roles
        // El rol Super-Admin debe ser el más alto y tener todos los permisos.
        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $userRole = Role::firstOrCreate(['name' => 'Usuario Estandar']);

        // 3. Asignar todos los permisos al rol Super-Admin
        $allPermissions = Permission::pluck('name');
        $superAdminRole->givePermissionTo($allPermissions);

        // 4. Crear un usuario inicial (Super Administrador)
        // Esto es crucial para poder acceder al sistema y asignar roles.
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@inmuno.local'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Usa una contraseña segura en producción
            ]
        );
        $superAdmin->assignRole($superAdminRole);
    }
}
