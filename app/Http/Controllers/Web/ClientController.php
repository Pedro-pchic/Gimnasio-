<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Models\Branch;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('clients.index', [
            'clients' => Client::query()
                ->with('branch')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::query()->create($request->validated());

        return redirect()->route('clients.show', $client)->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client): View
    {
        return view('clients.show', [
            'client' => $client->load([
                'branch',
                'memberships' => fn (HasMany $query): HasMany => $query->with('membershipType')->orderByDesc('start_date')->orderByDesc('id'),
                'payments' => fn (HasMany $query): HasMany => $query->with(['user', 'sale.receipt', 'clientMembership.membershipType'])->orderByDesc('payment_date')->orderByDesc('id'),
                'sales' => fn (HasMany $query): HasMany => $query->with(['branch', 'user', 'receipt'])->orderByDesc('sale_date')->orderByDesc('id'),
                'gymClassEnrollments' => fn (HasMany $query): HasMany => $query->with('gymClassSchedule.gymClass.branch')->orderByDesc('enrollment_date')->orderByDesc('id'),
            ]),
        ]);
    }

    public function edit(Client $client): View
    {
        return $this->formView($client);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client)->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->update(['is_active' => false]);

        return redirect()->route('clients.index')->with('success', 'Cliente desactivado correctamente.');
    }

    public function toggleStatus(Client $client): RedirectResponse
    {
        $client->update(['is_active' => ! $client->is_active]);

        return back()->with('success', 'Estado del cliente actualizado correctamente.');
    }

    private function formView(?Client $client = null): View
    {
        return view('clients.form', [
            'client' => $client,
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
        ]);
    }
}
