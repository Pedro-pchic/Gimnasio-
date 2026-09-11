# Fase 1: Núcleo del sistema de gimnasios

## A. Implementado

- Migraciones para sucursales, servicios, clientes, tipos de membresía, beneficios y membresías adquiridas.
- Tablas pivote normalizadas para sucursal-servicio y beneficio-tipo de membresía.
- Usuarios administrativos existentes (`users`) vinculables con roles y permisos mediante tablas pivote.
- Roles iniciales: Administrador general, Gerente de sucursal, Recepcionista, Supervisor e Instructor / Coach.
- API versionada y autenticada con CRUD para sucursales, servicios, clientes, tipos de membresía, beneficios y membresías de clientes.
- Validación de códigos únicos, referencias existentes, correo opcional válido, precios no negativos, fechas coherentes y estados de membresía permitidos.
- Desactivación lógica en las entidades operativas; cancelar una membresía cambia su estado a `cancelled`.
- Seeders de referencia para roles, servicios iniciales y membresías Básica (Q250) y Premium (Q350).

### Endpoints

Todos los endpoints requieren el middleware `auth` y usan el prefijo `/api/v1`.

| Recurso | URI |
| --- | --- |
| Sucursales | `/branches` |
| Servicios | `/services` |
| Clientes | `/clients` |
| Tipos de membresía | `/membership-types` |
| Beneficios | `/benefits` |
| Membresías de clientes | `/client-memberships` |

Cada URI expone `index`, `store`, `show`, `update` y `destroy`. Las solicitudes de creación y actualización de sucursal aceptan `service_ids`; las de tipo de membresía aceptan `benefit_ids`.

## B. Modelo funcional

- Una sucursal tiene varios servicios y un servicio puede ofrecerse en varias sucursales.
- Un cliente pertenece a una sucursal principal y puede adquirir varias membresías.
- Un tipo de membresía puede tener varios beneficios y un beneficio puede aplicarse a varios tipos.
- Una membresía de cliente conserva el tipo elegido, sus fechas, el precio aplicado, estado y observaciones; no depende del precio de referencia actual del tipo.
- Los usuarios administrativos son entidades de autenticación distintas de los clientes y pueden recibir varios roles; los roles pueden agrupar permisos para fases posteriores.

## C. Decisiones pendientes

- Duración comercial de cada tipo de membresía y reglas de renovación.
- Catálogo definitivo, unidades y condiciones de los beneficios, incluidos parqueo, masajes, piscina, boxeo y descuentos.
- Si una sucursal puede operar con horario que cruza la medianoche.
- Matriz concreta de permisos por rol y el mecanismo de inicio de sesión o emisión de tokens que consumirá la API.
- Reglas de suspensión, vencimiento y cancelación de membresías, así como los procesos que cambian esos estados.

## D. Próxima fase

Definir la matriz de permisos y el flujo de autenticación administrativa. Con esa base se puede añadir pagos y renovaciones de membresía, conservando la trazabilidad de precios y beneficios configurables creados en esta fase.
