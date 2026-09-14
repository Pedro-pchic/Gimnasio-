<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAccess;
use App\Models\ClientMembership;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BiometricAccessController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->where('is_active', true)
            ->with([
                'memberships' => fn (HasMany $query): HasMany => $query
                    ->with('membershipType')
                    ->orderByDesc('end_date')
                    ->orderByDesc('id'),
                'clientAccesses' => fn (HasMany $query): HasMany => $query
                    ->orderByDesc('checked_in_at')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get();

        $selectedClient = $clients->firstWhere('id', $request->integer('client')) ?? $clients->first();

        return view('biometric-access.index', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
            'activeMembership' => $selectedClient === null ? null : $this->activeMembership($selectedClient),
            'lastAccess' => $selectedClient?->clientAccesses->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
        ]);
        $clientId = $validated['client_id'];

        $result = DB::transaction(function () use ($clientId): array {
            $client = Client::query()
                ->with(['memberships' => fn (HasMany $query): HasMany => $query
                    ->with('membershipType')
                    ->orderByDesc('end_date')
                    ->orderByDesc('id')])
                ->lockForUpdate()
                ->findOrFail($clientId);
            $activeMembership = $this->activeMembership($client);

            if (! $client->is_active || $activeMembership === null) {
                return [
                    'authorized' => false,
                    'client' => $client->first_name.' '.$client->last_name,
                    'message' => ! $client->is_active ? 'Cliente inactivo.' : 'Membresía vencida o inactiva.',
                ];
            }

            $openAccess = $client->clientAccesses()
                ->open()
                ->orderByDesc('checked_in_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($openAccess !== null) {
                $openAccess->update(['checked_out_at' => now()]);

                return $this->result($client, $activeMembership, 'SALIDA REGISTRADA');
            }

            ClientAccess::query()->create([
                'client_id' => $client->getKey(),
                'checked_in_at' => now(),
            ]);

            return $this->result($client, $activeMembership, 'ENTRADA AUTORIZADA');
        }, attempts: 3);

        return redirect()
            ->route('biometric-access.index', ['client' => $clientId])
            ->with('biometric_result', $result);
    }

    private function activeMembership(Client $client): ?ClientMembership
    {
        $today = now()->startOfDay();

        return $client->memberships->first(
            fn (ClientMembership $membership): bool => $membership->status === ClientMembershipStatus::Active
                && $membership->start_date->lessThanOrEqualTo($today)
                && $membership->end_date->greaterThanOrEqualTo($today),
        );
    }

    /**
     * @return array{authorized: true, client: string, membership: string, time: string, result: string}
     */
    private function result(Client $client, ClientMembership $membership, string $result): array
    {
        return [
            'authorized' => true,
            'client' => $client->first_name.' '.$client->last_name,
            'membership' => $membership->membershipType->name,
            'time' => now()->format('H:i'),
            'result' => $result,
        ];
    }
}
