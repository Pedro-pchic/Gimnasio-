# GitHub Actions CI

## Objetivo

El workflow valida automáticamente el backend de Laravel y la compilación de los recursos Blade, Tailwind y Vite antes de integrar cambios.

GitHub Actions se utiliza para integración continua y validación automática. No constituye el hosting de la aplicación.

## Eventos

El workflow se ejecuta en los siguientes eventos:

- `push` a `main` y `develop`.
- `pull_request` dirigido a `main` y `develop`.

## Entorno

- PHP 8.3, que satisface el requisito `^8.3` de `composer.json`.
- Node.js 22, compatible con el requisito de Vite 8.
- Extensiones de PHP `mbstring` y `pdo_sqlite`.
- Sin servidor web, credenciales externas ni conexión a Neon, Render o Supabase.

## Base de datos de CI

El workflow crea `database/database.sqlite` en el runner efímero y ejecuta las migraciones contra ese archivo. Para las pruebas, `phpunit.xml` establece SQLite en memoria, por lo que cada ejecución queda aislada de cualquier base de datos remota.

## Comandos ejecutados

```shell
composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
php artisan key:generate --no-interaction
php artisan migrate --force --no-interaction
php artisan test --compact
npm ci
npm run build
```

## Interpretación del resultado

Un workflow verde confirma que las dependencias se instalaron, las migraciones se ejecutaron, las pruebas pasaron y Vite compiló los recursos. Un workflow rojo identifica el paso que falló en el registro de Actions; debe corregirse antes de integrar el cambio.

## Resultado local

`php artisan test --compact` finalizó correctamente con 34 pruebas y 149 aserciones. También se validaron `npm ci` y `npm run build` en una copia temporal limpia del proyecto, con Vite 8.3.0.

## Seguridad

`.env` permanece ignorado por Git. El workflow genera una clave de aplicación efímera en el runner y no contiene claves, contraseñas, hosts privados, tokens ni credenciales de servicios externos.
