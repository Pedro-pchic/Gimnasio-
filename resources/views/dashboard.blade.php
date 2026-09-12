@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-7">
        <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Resumen</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight">Dashboard</h1>
        <p class="mt-2 text-slate-600">Información actual registrada en el sistema.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statistics as $statistic)
            <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-600">{{ $statistic['label'] }}</p>
                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $statistic['value'] }}</p>
            </article>
        @endforeach
    </div>
@endsection
