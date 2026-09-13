<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Referral;
use App\ReferralRewardStatus;
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
        $client->load([
            'branch',
            'memberships' => fn (HasMany $query): HasMany => $query->with('membershipType')->orderByDesc('start_date')->orderByDesc('id'),
            'payments' => fn (HasMany $query): HasMany => $query->with(['user', 'sale.receipt', 'clientMembership.membershipType'])->orderByDesc('payment_date')->orderByDesc('id'),
            'sales' => fn (HasMany $query): HasMany => $query->with(['branch', 'user', 'receipt'])->orderByDesc('sale_date')->orderByDesc('id'),
            'gymClassEnrollments' => fn (HasMany $query): HasMany => $query->with('gymClassSchedule.gymClass.branch')->orderByDesc('enrollment_date')->orderByDesc('id'),
            'referralReceived.referrer',
            'referralsSent' => fn (HasMany $query): HasMany => $query
                ->with('referred')
                ->withSum('rewardUses as used_reward_amount', 'amount')
                ->orderByDesc('id'),
        ]);

        $usedRewardAmount = (float) $client->referralsSent->sum('used_reward_amount');
        $availableRewardAmount = (float) $client->referralsSent
            ->filter(fn (Referral $referral): bool => $referral->reward_status === ReferralRewardStatus::Available)
            ->sum(fn (Referral $referral): float => max(0, (float) $referral->reward_amount - (float) ($referral->used_reward_amount ?? 0)));

        return view('clients.show', [
            'client' => $client,
            'referralSummary' => [
                'available_reward_amount' => round($availableRewardAmount, 2),
                'used_reward_amount' => round($usedRewardAmount, 2),
            ],
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
