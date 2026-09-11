<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} · Iniciar sesión</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-slate-100 p-5 text-slate-900">
        <main class="w-full max-w-md rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200 sm:p-9">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Gimnasio</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight">Acceso administrativo</h1>
            <p class="mt-2 text-sm text-slate-600">Ingresa con tu cuenta administrativa para continuar.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-7 grid gap-5">
                @csrf
                <div>
                    <label for="email" class="text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <x-field-error name="email" />
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-slate-700">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <x-field-error name="password" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Recordar sesión
                </label>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Iniciar sesión</button>
            </form>
        </main>
    </body>
</html>
