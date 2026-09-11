# Fase 1B: autenticación administrativa y frontend Blade

## Implementado

- Inicio y cierre de sesión para cuentas administrativas (`users` con al menos un rol).
- Protección de rutas web mediante autenticación y permisos sembrados en las tablas existentes de roles y permisos.
- Dashboard con conteos de sucursales, clientes, tipos de membresía, membresías y servicios activos.
- Layout Blade responsive con navegación contextual, usuario, rol, cierre de sesión, mensajes flash y errores de validación.
- Interfaces Blade para sucursales, servicios, clientes, tipos de membresía, beneficios y membresías de clientes.
- Asociación de servicios a sucursales y de beneficios a tipos de membresía desde ambos módulos.
- Desactivación/reactivación de catálogos; cancelación no destructiva de membresías de clientes.

## Matriz de permisos

| Rol | Acceso |
| --- | --- |
| Administrador general | Acceso completo al dashboard y a todos los módulos de esta fase. |
| Gerente de sucursal | Consulta sucursales y beneficios; gestiona servicios, clientes, tipos de membresía y membresías de clientes. |
| Recepcionista | Gestiona clientes y membresías de clientes; consulta tipos de membresía. |
| Supervisor | Consulta sucursales, servicios, clientes, tipos de membresía y membresías de clientes. |
| Instructor / Coach | Consulta dashboard, servicios y clientes. |

Los permisos se crean y sincronizan idempotentemente con `PermissionSeeder`, que se invoca desde `DatabaseSeeder` después de los roles.

## Pendientes de análisis

- Duración definitiva de membresías y criterios de vencimiento.
- Renovación de membresías.
- Reglas definitivas de beneficios, límites y períodos de uso.
- Horarios especiales por sucursal.
- Alcance detallado de consulta para Instructor / Coach y límites por sucursal para cada rol.

## Próxima fase

Pagos, renovaciones, ventas y comprobantes.
