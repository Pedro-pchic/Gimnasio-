<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRenewalRequest;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RenewalController extends Controller
{
    public function index(): View
    {
        return view('renewals.index', [
            'renewals' => ClientMembership::query()
                ->with([
                    'client',
                    'membershipType',
                    'payments' => fn (HasMany $query): HasMany => $query->orderByDesc('payment_date'),
                ])
                ->whereHas('payments', fn (Builder $query): Builder => $query->whereNotNull('client_membership_id'))
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreRenewalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $clientMembership = DB::transaction(function () use ($data, $request): ClientMembership {
            $membershipType = MembershipType::query()->findOrFail($data['membership_type_id']);
            $clientMembership = ClientMembership::query()->create([
                'client_id' => $data['client_id'],
                'membership_type_id' => $membershipType->getKey(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'applied_price' => $membershipType->reference_price,
                'status' => ClientMembershipStatus::Active,
                'observations' => $data['observations'] ?? null,
            ]);

            Payment::query()->create([
                'client_id' => $data['client_id'],
                'user_id' => $request->user()->getKey(),
                'client_membership_id' => $clientMembership->getKey(),
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'status' => PaymentStatus::Paid,
                'observations' => $data['payment_observations'] ?? null,
            ]);

            return $clientMembership;
        });

        return redirect()->route('clients.show', $clientMembership->client_id)->with('success', 'Renovación y pago registrados correctamente.');
    }

    private function formView(): View
    {
        return view('renewals.form', [
            'clients' => Client::query()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'membershipTypes' => MembershipType::query()->where('is_active', true)->orderBy('name')->orderBy('id')->get(),
            'methods' => PaymentMethod::cases(),
        ]);
    }
}
