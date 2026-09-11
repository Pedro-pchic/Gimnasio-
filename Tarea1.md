# TAREA CODEX — FASE 1B
## Autenticación administrativa y frontend Blade + Tailwind del núcleo GYM

## OBJETIVO

Completar la FASE 1 del Sistema de Gestión Integral para Gimnasios incorporando:

1. autenticación administrativa;
2. autorización básica por roles;
3. interfaz web Blade + Tailwind;
4. navegación del sistema;
5. CRUD visual de los módulos ya creados.

NO implementar todavía pagos, renovaciones, biometría, inventario, referidos ni reportes.

El proyecto es una aplicación monolítica Laravel.

STACK:

- Laravel
- Blade
- Tailwind CSS
- PostgreSQL
- Git/GitHub
- Supabase PostgreSQL será la base remota final
- Supabase Storage se utilizará posteriormente para archivos
- Render se utilizará al final para despliegue

NO crear React, Vue o Angular.

---

# 1. INSPECCIÓN PREVIA

Antes de modificar código:

- revisar versión actual de Laravel;
- revisar autenticación existente;
- revisar routes/web.php;
- revisar routes/api.php;
- revisar modelos User, Role y Permission creados en FASE 1;
- revisar controladores existentes;
- revisar migraciones;
- revisar Blade/Tailwind instalados;
- revisar pruebas actuales.

No duplicar funcionalidades existentes.

---

# 2. AUTENTICACIÓN ADMINISTRATIVA

Implementar autenticación web para usuarios administrativos.

Debe permitir:

- iniciar sesión;
- cerrar sesión;
- proteger rutas administrativas;
- redirigir usuarios no autenticados al login;
- mostrar mensajes de error de autenticación.

No permitir login de clientes del gimnasio en esta fase.

Cliente y usuario administrativo deben mantenerse separados.

---

# 3. ROLES

Utilizar la estructura de roles/permisos ya creada en FASE 1.

Roles iniciales:

- Administrador general
- Gerente de sucursal
- Recepcionista
- Supervisor
- Instructor / Coach

Implementar autorización básica.

Como mínimo:

## Administrador general

Acceso completo a los módulos actuales.

## Gerente de sucursal

Acceso a sucursales, servicios, clientes y membresías dentro del alcance permitido.

## Recepcionista

Acceso a clientes y membresías.

## Supervisor

Acceso de consulta a clientes, sucursales y servicios.

## Instructor / Coach

Acceso limitado de consulta.

Si alguna regla exacta de permisos no está definida, documentarla como pendiente.

No inventar permisos complejos.

---

# 4. LAYOUT PRINCIPAL

Crear un layout administrativo reutilizable con Blade + Tailwind.

Debe incluir:

- encabezado;
- menú lateral o navegación principal;
- nombre del usuario autenticado;
- rol;
- opción cerrar sesión;
- mensajes de éxito/error;
- contenido central responsive.

Menú inicial:

- Dashboard
- Sucursales
- Servicios
- Clientes
- Membresías
- Beneficios
- Membresías de clientes

No mostrar módulos futuros todavía.

---

# 5. DASHBOARD

Crear un dashboard inicial sencillo.

Mostrar únicamente información disponible actualmente.

Ejemplos:

- total de sucursales activas;
- total de clientes activos;
- tipos de membresía;
- membresías activas;
- servicios registrados.

NO crear gráficos complejos.

NO crear estadísticas inventadas.

---

# 6. SUCURSALES

Crear interfaz Blade para:

- listar;
- crear;
- editar;
- consultar;
- activar/desactivar.

Mostrar:

- código;
- nombre;
- dirección;
- teléfono;
- horario;
- estado;
- servicios asociados.

Permitir asociar servicios existentes.

---

# 7. SERVICIOS

Crear interfaz Blade para:

- listar;
- crear;
- editar;
- activar/desactivar.

Campos existentes:

- nombre;
- descripción;
- estado.

Mostrar sucursales relacionadas cuando corresponda.

---

# 8. CLIENTES

Crear interfaz Blade para:

- listar;
- crear;
- editar;
- consultar;
- activar/desactivar.

Mostrar como mínimo:

- código;
- nombres;
- apellidos;
- teléfono;
- correo;
- sucursal principal;
- fecha de inscripción;
- estado.

En detalle del cliente mostrar, cuando exista:

- membresía actual;
- historial de membresías.

No implementar pagos todavía.

---

# 9. TIPOS DE MEMBRESÍA

Crear interfaz Blade para:

- listar;
- crear;
- editar;
- activar/desactivar.

Mostrar:

- nombre;
- precio;
- estado;
- beneficios relacionados.

No hardcodear Básica/Premium en las vistas.

---

# 10. BENEFICIOS

Crear interfaz Blade para:

- listar;
- crear;
- editar;
- activar/desactivar;
- asociar a tipos de membresía.

Mostrar:

- nombre;
- tipo;
- valor;
- límite;
- período;
- estado.

---

# 11. MEMBRESÍAS DE CLIENTES

Crear interfaz para asignar una membresía a un cliente.

Mostrar:

- cliente;
- tipo de membresía;
- fecha de inicio;
- fecha de vencimiento;
- precio aplicado;
- estado;
- observaciones.

Validar:

- cliente existente;
- tipo válido;
- fechas coherentes;
- precio >= 0.

No implementar todavía:

- cobro;
- pago;
- factura;
- renovación automática.

---

# 12. RUTAS WEB

Crear rutas web protegidas con autenticación.

Preferir nombres de rutas claros.

Ejemplo conceptual:

- dashboard
- branches.*
- services.*
- clients.*
- membership-types.*
- benefits.*
- client-memberships.*

Mantener /api/v1 existente sin romperlo.

No eliminar la API creada en FASE 1.

---

# 13. VALIDACIONES

Reutilizar reglas existentes cuando sea posible.

No duplicar lógica entre API y web innecesariamente.

Mantener validación del servidor como fuente de verdad.

---

# 14. UX BÁSICA

Agregar:

- mensajes flash;
- confirmación para desactivar;
- estados visibles;
- tablas legibles;
- formularios consistentes;
- errores de validación junto a los campos;
- navegación coherente.

No invertir tiempo en animaciones ni diseño avanzado.

Priorizar funcionalidad y claridad.

---

# 15. PRUEBAS

Agregar pruebas para:

- acceso al login;
- login correcto;
- login incorrecto;
- protección de rutas;
- acceso según rol;
- carga del dashboard;
- CRUD web básico;
- validaciones principales.

Mantener pasando todas las pruebas anteriores.

---

# 16. DOCUMENTACIÓN

Actualizar o crear:

docs/fase-1b-auth-blade.md

Incluir:

## Implementado

- autenticación;
- roles;
- vistas;
- rutas web;
- módulos disponibles.

## Permisos aplicados

Describir qué puede hacer cada rol.

## Pendientes

Registrar reglas aún no confirmadas.

## Próxima fase

Pagos, renovaciones, ventas y comprobantes.

---

# 17. NO IMPLEMENTAR

No implementar todavía:

- pagos;
- renovaciones automáticas;
- facturación fiscal;
- huella;
- accesos físicos;
- clases;
- aforo;
- empleados;
- inventario;
- proveedores;
- órdenes de compra;
- referidos;
- bonos;
- reportes avanzados;
- Supabase Storage;
- Render.

---

# 18. CRITERIOS DE ACEPTACIÓN

La fase se considera terminada si:

- existe login web funcional;
- las rutas administrativas están protegidas;
- se aplican roles básicos;
- existe dashboard;
- sucursales tienen CRUD Blade;
- servicios tienen CRUD Blade;
- clientes tienen CRUD Blade;
- membresías tienen CRUD Blade;
- beneficios tienen CRUD Blade;
- se pueden asignar membresías a clientes;
- la API existente sigue funcionando;
- las pruebas anteriores siguen pasando;
- las nuevas pruebas pasan;
- Blade + Tailwind funcionan correctamente.

---

# 19. SALIDA FINAL

Al finalizar responder únicamente con:

## Implementado

## Archivos principales

## Rutas web creadas

## Roles y permisos aplicados

## Pruebas ejecutadas

## Pendientes de análisis

## Próximo paso
