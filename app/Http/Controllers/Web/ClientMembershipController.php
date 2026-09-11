<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientMembershipRequest;
use App\Http\Requests\Api\V1\UpdateClientMembershipRequest;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientMembershipController extends Controller
{
    public function index(): View
    {
        return view('client-memberships.index', [
            'clientMemberships' => ClientMembership::query()
                ->with(['client', 'membershipType'])
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreClientMembershipRequest $request): RedirectResponse
    {
        $clientMembership = ClientMembership::query()->create($request->validated());

        return redirect()->route('client-memberships.show', $clientMembership)->with('success', 'Membresía asignada correctamente.');
    }

    public function show(ClientMembership $clientMembership): View
    {
        return view('client-memberships.show', [
            'clientMembership' => $clientMembership->load(['client.branch', 'membershipType']),
        ]);
    }

    public function edit(ClientMembership $clientMembership): View
    {
        return $this->formView($clientMembership);
    }

    public function update(UpdateClientMembershipRequest $request, ClientMembership $clientMembership): RedirectResponse
    {
        $clientMembership->update($request->validated());

        return redirect()->route('client-memberships.show', $clientMembership)->with('success', 'Membresía actualizada correctamente.');
    }

    public function destroy(ClientMembership $clientMembership): RedirectResponse
    {
        $clientMembership->update(['status' => ClientMembershipStatus::Cancelled]);

        return redirect()->route('client-memberships.index')->with('success', 'Membresía cancelada correctamente.');
    }

    private function formView(?ClientMembership $clientMembership = null): View
    {
        return view('client-memberships.form', [
            'clientMembership' => $clientMembership,
            'clients' => Client::query()->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'membershipTypes' => MembershipType::query()->orderBy('name')->orderBy('id')->get(),
            'statuses' => ClientMembershipStatus::cases(),
        ]);
    }
}
