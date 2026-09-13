# Fase 4: clases, horarios y capacidad

## Objetivo

Administrar actividades del gimnasio, sus horarios recurrentes, participantes, capacidad y asistencia sin incorporar calendarios externos ni reservas públicas.

## Modelo de clases

`gym_classes` representa una actividad configurable por sucursal. Guarda nombre, descripción, tipo abierto, capacidad máxima y estado activo. Los valores iniciales de tipo son `general`, `natacion` y `boxeo`, pero el campo permite agregar más actividades sin una migración.

## Horarios

`gym_class_schedules` guarda el día de semana, hora de inicio, hora de fin y estado. La estructura se mantiene deliberadamente simple: los horarios son recurrentes y una inscripción indica la fecha específica en que ocurrirá la participación.

## Participantes

`gym_class_enrollments` enlaza cliente, horario y fecha. Sus estados son inscrito, asistió, cancelado y no asistió. La cancelación cambia el estado y conserva el historial.

## Control de capacidad

El cupo se calcula en backend por horario y fecha, contando inscripciones que no han sido canceladas. La inscripción se registra dentro de una transacción que bloquea la actividad y el horario, valida duplicados y compara el conteo contra la capacidad máxima. También existe una restricción única para cliente, horario y fecha.

## Natación

Natación es una actividad de tipo `natacion`, con referencia inicial de 10 cupos. Cuando existe un servicio de natación, la sucursal debe tenerlo asociado mediante la relación dinámica Branch–Service.

## Boxeo

Boxeo es una actividad de tipo `boxeo`, con referencia inicial de 15 cupos. Cuando existe un servicio de boxeo, la sucursal debe tenerlo asociado mediante Branch–Service.

## Permisos

- Administrador general y gerente: gestión completa.
- Recepcionista: consulta, inscripción y cancelación.
- Supervisor: consulta y asistencia.
- Instructor / Coach: consulta y asistencia.

## Validaciones

Se validan sucursal, capacidad positiva, compatibilidad de servicio cuando aplica, horarios coherentes, cliente activo, membresía activa en la fecha, actividad y horario activos, coincidencia entre fecha y día del horario, cupo y ausencia de duplicados.

## Pruebas

La cobertura incluye creación de clases y horarios, validaciones, servicios de natación y boxeo, inscripción, membresía, cupo completo, duplicados, cancelación, asistencia, permisos, filtros, historial del cliente y seeders.

## Pendientes

No se define todavía qué tipo de membresía habilita natación o boxeo, porque el modelo actual no contiene esa regla. Tampoco hay asignación individual de clases a instructores.

## Próxima fase

Definir elegibilidad por membresía e instructor, si el negocio lo requiere, antes de implementar reservas públicas o calendarios externos.
