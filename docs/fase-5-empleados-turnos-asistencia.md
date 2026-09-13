# Fase 5: empleados, turnos y asistencia

## Objetivo

Incorporar la administracion de personal, su asistencia laboral y la asignacion de instructores a clases sin implementar nomina ni bonos.

## Modelo de empleados

`Employee` guarda el codigo interno, datos de contacto, fecha de contratacion, estado, sucursal principal y puesto. Los empleados se desactivan para conservar su historial.

## Diferencia Employee vs User

`Employee` representa la relacion laboral. `User` representa autenticacion y roles. La relacion `employee.user_id` es opcional y unica: crear un empleado no crea una cuenta de acceso.

## Puestos

`Position` es configurable, tiene descripcion, estado y la marca `can_teach`. Esta ultima identifica los puestos que pueden ser elegidos como instructor de una clase.

## Sucursales

Cada empleado pertenece a una sucursal principal existente mediante `branch_id`. La estructura queda lista para una futura relacion laboral de multiples sucursales, que no se implementa en esta fase.

## Turnos

`WorkShift` define nombre, hora inicial, hora final y estado. Los turnos pueden cruzar medianoche: una hora de salida menor a la de entrada se interpreta como el dia siguiente solo cuando el turno asignado es nocturno.

## Asignaciones

`EmployeeShiftAssignment` relaciona empleado, turno, vigencia, dia recurrente opcional y estado. Las asignaciones activas incompatibles y superpuestas se rechazan dentro de una transaccion.

## Asistencia

`EmployeeAttendance` es independiente de cualquier control de acceso de clientes. Registra empleado, sucursal, turno aplicado, fecha, entrada, salida, metodo, estado y observaciones. En esta fase se usa el metodo `manual`; los valores `codigo` y `biometrico` quedan preparados para integraciones futuras.

La entrada valida que el empleado este activo y no tenga una asistencia abierta. Se protege con un bloqueo atomico por empleado y una transaccion. La salida actualiza el mismo registro y no crea otro.

## Horas trabajadas

La duracion se calcula al consultar el registro como salida menos entrada y se muestra con el formato `8 h 12 min`. No se calculan salarios, horas extra monetarias ni nomina.

## Instructores y clases

Una clase puede tener un `instructor_employee_id` nullable. Solo se acepta un empleado activo, perteneciente a la misma sucursal, cuyo puesto activo tenga `can_teach`. Las clases existentes pueden permanecer sin instructor.

## Permisos

Se reutiliza el sistema existente con: `employees.view`, `employees.manage`, `positions.manage`, `work-shifts.manage`, `employee-attendances.view` y `employee-attendances.register`. Administrador general tiene acceso completo; gerente, recepcionista y supervisor reciben los alcances definidos por el seeder.

## Preparacion para bonos

La informacion de asistencia, clases impartidas y periodos queda relacionada por empleado. La evaluacion de desempeno y los bonos no se calculan en esta fase.

## Pruebas

Las pruebas de caracteristica cubren puestos, empleados, usuarios asociados, codigos duplicados, turnos, asignaciones incompatibles, entrada, doble entrada, salida, horas trabajadas, turno nocturno, empleado inactivo, filtros, permisos e instructor de clase.

## Pendientes

No se implementan tolerancias de tardanza, ausencias automatizadas, biometria real, multiples sucursales laborales, nomina ni bonos. Las politicas de puntualidad requieren una definicion laboral previa.

## Proxima fase

Extender el historial operativo con la regla de negocio que corresponda, sin reutilizar asistencia laboral para calculos economicos hasta definir nomina y bonos.
