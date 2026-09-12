# Fase 1C: Neon PostgreSQL

> **ESTADO: POSPUESTO / NO REQUERIDO PARA LA ENTREGA ACTUAL.**

## Decisión actual

El proyecto continúa funcionando localmente con SQLite. La integración con Neon PostgreSQL, Supabase, Render y cualquier despliegue externo queda fuera del alcance de la entrega actual.

## Configuración conservada

Laravel mantiene su conexión estándar `pgsql` disponible en `config/database.php`, pero SQLite es la conexión predeterminada. No se requiere host remoto, usuario, contraseña, URL de conexión ni SSL obligatorio para ejecutar el proyecto.

GitHub Actions usa SQLite aislado para migraciones y SQLite en memoria para las pruebas automatizadas.

## Si se retoma la fase

Cuando Neon vuelva a estar dentro del alcance, las credenciales deberán configurarse exclusivamente en el `.env` privado o en las variables seguras del entorno de despliegue. No se deben versionar secretos.

## Validación actual

No se realizó ninguna conexión, migración ni seeder contra Neon. La validación vigente es local y está cubierta por `php artisan test --compact` y el workflow de GitHub Actions.
