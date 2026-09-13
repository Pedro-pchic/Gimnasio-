# Fase 6: terceros, articulos externos y descuentos

## Catalogo

- Los terceros comerciales pueden vender productos, servicios o ambos.
- Cada articulo externo tiene un tercero, tipo, precio base, estado y una o mas sucursales habilitadas.
- Los descuentos se asignan a articulos y pueden limitarse a sucursales. Si no tienen sucursales asignadas, son globales para las sucursales donde el articulo este disponible.

## Calculo de descuentos

Al registrar una venta, el servidor toma el precio y la descripcion del articulo externo. No utiliza precio, descripcion ni descuento enviados desde el navegador.

Solo se consideran descuentos activos, vigentes y permitidos para la sucursal. Si varios descuentos aplican, se usa uno: el que produzca el mayor descuento en la linea; en empate se elige el de menor identificador. El importe nunca baja de cero.

El descuento de una linea se guarda en `sale_details.discount`. El `subtotal` de la venta es la suma neta de las lineas y el descuento manual de la venta se conserva por separado en `sales.discount`.

## Historial y permisos

Las ventas usan las tablas existentes de ventas, detalles, pagos y comprobantes. El detalle almacena el precio, descripcion y descuento calculados, por lo que cambios posteriores en el catalogo no cambian comprobantes anteriores.

- Administrador general: administracion completa.
- Gerente de sucursal: administracion completa del modulo.
- Recepcionista: consulta catalogos y registra ventas externas.
- Supervisor: solo consulta.
- Instructor / Coach: sin acceso al modulo.
