<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreServiceRequest;
use App\Http\Requests\Api\V1\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::query()
                ->with('branches')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('services.form', ['service' => null]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::query()->create($request->validated());

        return redirect()->route('services.show', $service)->with('success', 'Servicio creado correctamente.');
    }

    public function show(Service $service): View
    {
        return view('services.show', ['service' => $service->load('branches')]);
    }

    public function edit(Service $service): View
    {
        return view('services.form', ['service' => $service]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('services.show', $service)->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->update(['is_active' => false]);

        return redirect()->route('services.index')->with('success', 'Servicio desactivado correctamente.');
    }

    public function toggleStatus(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('success', 'Estado del servicio actualizado correctamente.');
    }
}
