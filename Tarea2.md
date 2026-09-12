# TAREA CODEX — FASE 4
## Clases + horarios + natación + boxeo + control de capacidad

## OBJETIVO

Implementar el módulo de clases y actividades del gimnasio.

La fase debe permitir:

1. administrar clases;
2. administrar horarios;
3. asociar clases a sucursales;
4. manejar actividades como natación y boxeo;
5. controlar capacidad máxima;
6. registrar participantes;
7. consultar cupos disponibles;
8. conservar historial de participación.

No implementar todavía pagos especiales, inventario, empleados completos ni reservas externas.

---

# 1. CONTEXTO ACTUAL

El proyecto ya cuenta con:

- Laravel;
- Blade;
- Tailwind;
- autenticación;
- roles y permisos;
- sucursales;
- servicios;
- clientes;
- membresías;
- pagos;
- renovaciones;
- ventas;
- comprobantes;
- control de acceso;
- entradas y salidas;
- pruebas automatizadas;
- GitHub Actions.

La aplicación debe seguir funcionando localmente.

No depender de:

- Neon;
- Supabase;
- Render.

---

# 2. INSPECCIÓN PREVIA

Antes de crear código:

- revisar Branch;
- revisar Service;
- revisar Client;
- revisar ClientMembership;
- revisar AccessRecord o entidad equivalente;
- revisar roles/permisos;
- revisar layout Blade;
- revisar dashboard;
- revisar rutas;
- revisar seeders;
- revisar pruebas existentes.

No duplicar modelos existentes.

No crear una segunda entidad de sucursal o cliente.

---

# 3. ENTIDAD CLASE / ACTIVIDAD

Crear una entidad ClassSession, GymClass, Activity o equivalente según convenciones del proyecto.

Debe representar actividades como:

- clase grupal;
- natación;
- boxeo;
- entrenamiento especial.

Campos mínimos:

- id;
- nombre;
- descripción nullable;
- tipo;
- branch_id;
- capacidad máxima;
- activo;
- timestamps.

Tipos iniciales sugeridos:

- general;
- natacion;
- boxeo.

No hardcodear toda la lógica únicamente a estos tres tipos.

---

# 4. CAPACIDAD

La clase/actividad debe manejar capacidad máxima.

Reglas:

- capacidad > 0;
- no permitir participantes por encima de la capacidad;
- mostrar cupos disponibles;
- impedir inscripción cuando capacidad = 0 disponible.

Notas del análisis:

- natación: referencia de 10 usuarios;
- boxeo: referencia de 15 usuarios.

IMPORTANTE:

No asumir que esos valores son universales para todas las sucursales.

Pueden utilizarse como valores iniciales/configurables.

La capacidad debe quedar editable.

---

# 5. HORARIOS

Crear entidad ClassSchedule, ActivitySchedule o equivalente.

Debe almacenar:

- class/activity id;
- día de semana o fecha;
- hora inicio;
- hora fin;
- activo;
- timestamps.

Validaciones:

- hora fin > hora inicio;
- clase existente;
- sucursal válida;
- horario activo.

Preparar estructura para horarios recurrentes.

No implementar calendarios externos.

---

# 6. SESIONES

Si la arquitectura lo permite, separar:

Actividad / clase
→ horario
→ sesión específica

Ejemplo:

Natación principiantes
→ lunes 8:00
→ sesión 15/09/2026

Si esto agrega demasiada complejidad para la entrega, implementar una estructura simple pero documentar la decisión.

No sobrearquitecturar.

---

# 7. PARTICIPANTES

Crear entidad de inscripción/participación.

Ejemplo:

class_enrollments
activity_participants
class_registrations

Debe almacenar:

- client_id;
- class/activity/schedule id;
- fecha;
- estado;
- timestamps.

Estados sugeridos:

- inscrito;
- asistió;
- cancelado;
- no_asistio.

No borrar historial.

---

# 8. INSCRIPCIÓN

Flujo:

Cliente
→ seleccionar actividad
→ seleccionar horario
→ validar membresía
→ validar cupo
→ registrar inscripción

Validar:

- cliente existente;
- cliente activo;
- membresía válida;
- actividad activa;
- horario activo;
- cupo disponible;
- no duplicar inscripción.

No inventar restricciones por tipo de membresía si no están definidas.

Si no existe regla que indique qué membresía permite natación o boxeo:

documentar como pendiente.

---

# 9. NATACIÓN

Implementar natación como actividad configurable.

Debe permitir:

- nombre;
- sucursal;
- horarios;
- capacidad;
- participantes.

Referencia inicial:

10 usuarios.

No asumir que todas las sucursales tienen piscina.

Solo mostrar natación en sucursales que tengan el servicio correspondiente si existe relación Branch ↔ Service.

---

# 10. BOXEO

Implementar boxeo como actividad configurable.

Debe permitir:

- nombre;
- sucursal;
- horarios;
- capacidad;
- participantes.

Referencia inicial:

15 usuarios.

No asumir que todas las sucursales tienen ring de boxeo.

Solo permitirlo en sucursales asociadas al servicio correspondiente cuando esa relación exista.

---

# 11. RELACIÓN CON SERVICIOS

Reutilizar Branch ↔ Service.

Ejemplo conceptual:

Sucursal A
→ gimnasio
→ natación

Sucursal B
→ gimnasio
→ boxeo

No crear banderas tipo:

has_pool
has_boxing

si ya existe una estructura dinámica de servicios.

---

# 12. CONTROL DE CUPO

El sistema debe calcular:

capacidad_maxima - inscritos_activos

Mostrar:

- capacidad;
- inscritos;
- disponibles;
- lleno sí/no.

El cálculo debe realizarse en backend.

No confiar únicamente en JavaScript.

---

# 13. CONCURRENCIA

Evitar sobrecupo si dos usuarios intentan registrar participantes al mismo tiempo.

Usar transacción y validación consistente.

Si quedan 1 cupo y llegan dos solicitudes simultáneas:

solo una debe poder registrarse.

---

# 14. INTERFAZ BLADE

Agregar menú:

- Clases
- Horarios
- Participantes

Crear vistas:

## Clases

- listado;
- crear;
- editar;
- ver detalle;
- activar/desactivar.

## Horarios

- listado;
- crear;
- editar;
- filtrar por sucursal.

## Participantes

- registrar cliente;
- cancelar;
- marcar asistencia;
- ver inscritos.

---

# 15. VISTA DE DETALLE DE CLASE

Mostrar:

- nombre;
- tipo;
- sucursal;
- capacidad;
- inscritos;
- cupos disponibles;
- horarios;
- participantes.

---

# 16. CLIENTE — DETALLE

Agregar historial de actividades:

- clase;
- sucursal;
- fecha;
- horario;
- estado.

No romper:

- historial financiero;
- historial de accesos;
- membresías.

---

# 17. PERMISOS

Reutilizar permisos existentes.

Propuesta:

## Administrador general
- gestión completa.

## Gerente
- administrar clases;
- horarios;
- participantes.

## Recepcionista
- consultar;
- inscribir clientes;
- cancelar inscripción.

## Supervisor
- consultar;
- controlar asistencia.

## Instructor / Coach
- consultar clases asignadas;
- ver participantes;
- marcar asistencia.

No crear un segundo sistema de autorización.

---

# 18. DASHBOARD

Agregar métricas simples:

- clases de hoy;
- participantes inscritos hoy;
- clases llenas;
- cupos disponibles.

No crear reportes avanzados todavía.

---

# 19. VALIDACIONES

Validar:

- sucursal existente;
- servicio compatible si aplica;
- capacidad > 0;
- horario coherente;
- cliente activo;
- membresía válida;
- cupo disponible;
- inscripción no duplicada;
- estado válido;
- usuario autorizado.

---

# 20. SEEDERS

Agregar datos mínimos de demostración.

Ejemplo:

- clase general;
- natación;
- boxeo;
- horarios;
- algunos participantes.

No crear datos excesivos.

---

# 21. PRUEBAS

Agregar pruebas para:

- crear clase;
- capacidad inválida;
- crear horario;
- horario inválido;
- inscribir cliente;
- rechazar cliente sin membresía;
- impedir duplicado;
- impedir sobrecupo;
- cupos disponibles;
- cancelar inscripción;
- marcar asistencia;
- natación asociada a sucursal válida;
- boxeo asociado a sucursal válida;
- permisos por rol;
- historial del cliente.

Mantener todas las pruebas anteriores.

No eliminar tests.

---

# 22. GITHUB ACTIONS

Mantener CI funcionando.

Ejecutar al final:

php artisan test --compact

npm run build

---

# 23. DOCUMENTACIÓN

Crear:

docs/fase-4-clases-horarios-capacidad.md

Incluir:

## Objetivo

## Modelo de clases

## Horarios

## Participantes

## Control de capacidad

## Natación

## Boxeo

## Permisos

## Validaciones

## Pruebas

## Pendientes

## Próxima fase

---

# 24. NO IMPLEMENTAR

No implementar todavía:

- nómina;
- empleados completos;
- turnos laborales;
- inventario;
- mantenimiento;
- proveedores;
- órdenes de compra;
- referidos;
- bonos;
- facturación fiscal;
- reservas online públicas;
- integración móvil;
- pagos externos;
- biometría real;
- reportes avanzados.

---

# 25. CRITERIOS DE ACEPTACIÓN

La Fase 4 termina si:

- existen clases/actividades;
- existen horarios;
- natación funciona como actividad configurable;
- boxeo funciona como actividad configurable;
- se pueden registrar participantes;
- se controla capacidad;
- no existe sobrecupo;
- se conserva historial;
- permisos funcionan;
- Blade funciona;
- pruebas pasan;
- frontend compila;
- GitHub Actions continúa verde.

---

# 26. SALIDA FINAL

Responder únicamente con:

## Implementado

## Modelo de clases

## Archivos principales

## Migraciones

## Rutas

## Interfaz Blade

## Horarios

## Control de capacidad

## Natación y boxeo

## Permisos

## Pruebas

## Build

## Pendientes

## Próximo paso
