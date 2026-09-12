# Fase 2: pagos, renovaciones y ventas

## Alcance

Esta fase registra operaciones internas del gimnasio. No integra FEL, SAT, EFact ni genera documentos fiscales.

## Modelo financiero

- `payments` guarda los cobros, su cliente cuando aplica, el usuario que lo registró, fecha, monto, método, referencia, estado y observaciones.
- `sales` registra la venta, cliente opcional, sucursal, usuario, fecha, subtotal, descuento, total y estado.
- `sale_details` conserva las líneas de cada venta con tipo de concepto (`membership`, `service` u `other`), referencia opcional, descripción, cantidad, precio unitario y subtotal.
- `receipts` conserva un comprobante interno único por venta, con formato `COMP-000001`. Incluye fecha y método de pago, sin validez fiscal.
- Las renovaciones crean un nuevo registro de `client_memberships` y un pago asociado. El historial anterior se conserva; las fechas son ingresadas por el personal y no se calculan automáticamente.

## Reglas operativas

- Los métodos disponibles son efectivo, tarjeta y transferencia, representados por un enum extensible.
- Los estados de pago son pendiente, pagado y cancelado. Las ventas pueden estar pendientes, completadas o canceladas.
- Las cancelaciones cambian el estado; no hay eliminación física de pagos ni ventas.
- El servidor calcula subtotales y total de venta. El descuento debe ser mayor o igual a cero y no puede superar el subtotal.
- La creación de venta, detalles, pago y comprobante ocurre en una sola transacción. La renovación y su pago también ocurren en una sola transacción.

## Permisos

| Rol | Operaciones financieras |
| --- | --- |
| Administrador general | Consulta y gestión completa |
| Gerente de sucursal | Consulta y gestión completa |
| Recepcionista | Consulta y registro/cancelación de pagos, ventas y renovaciones |
| Supervisor | Solo consulta |
| Instructor / Coach | Sin acceso |

## Rutas principales

- `payments.*`: consulta, registro, detalle y cancelación de pagos.
- `sales.*`: consulta, registro, detalle, cancelación y comprobante interno.
- `renewals.*`: consulta y registro de renovación con pago.
