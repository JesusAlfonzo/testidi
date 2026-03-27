# Manual - Rol Superadmin

## Descripción del Rol

El rol **Superadmin** tiene acceso total al sistema, incluyendo la gestión de usuarios, roles y permisos.

## Módulos Disponibles

Incluye todo lo del rol **Supervisor**, más:

### Seguridad
- **Usuarios**: Crear, editar, eliminar usuarios
- **Roles**: Gestionar roles y permisos

---

## Gestión de Usuarios

### Crear Usuario
1. Vaya a **Usuarios** > **Nuevo**
2. Complete los datos:
   - Nombre completo
   - Email (será el usuario de acceso)
   - Contraseña
   - Rol asignado
3. Guardar

### Editar Usuario
1. En el listado, haga clic en editar
2. Modifique los datos
3. Opcional: cambiar contraseña

### Eliminar Usuario
1. Seleccione el usuario
2. Haga clic en eliminar
3. Confirme la acción

**Nota**: No puede eliminarse a sí mismo.

---

## Gestión de Roles

### Ver Roles
1. Vaya a **Roles**
2. Observe los roles existentes:
   - Superadmin
   - Supervisor
   - Logística
   - Solicitante

### Permisos por Rol

| Permiso | Superadmin | Supervisor | Logística | Solicitante |
|---------|------------|------------|-----------|-------------|
| Gestionar usuarios | ✅ | ❌ | ❌ | ❌ |
| Gestionar roles | ✅ | ❌ | ❌ | ❌ |
| Ver auditoría | ✅ | ✅ | ❌ | ❌ |
| Reportes | ✅ | ✅ | ✅ | ❌ |
| Aprobar solicitudes | ✅ | ✅ | ✅ | ❌ |
| Crear solicitudes | ✅ | ✅ | ✅ | ✅ |

---

## Recomendaciones de Seguridad

- Mantenga mínimo el número de Superadmins
- Use contraseñas fuertes
- Revise la auditoría periódicamente
- Asigne roles según necesidad

---

*¿Necesita ayuda? Use el botón de ayuda en la esquina superior derecha.*
