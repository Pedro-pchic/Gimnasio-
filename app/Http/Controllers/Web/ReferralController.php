<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReferralRequest;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\Referral;
use App\PaymentStatus;
use App\ReferralRewardStatus;
use App\ReferralStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(): View
    {
        return view('referrals.index', [
            'referrals' => Referral::query()
                ->with(['referrer', 'referred'])
                ->withSum('rewardUses as used_reward_amount', 'amount')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('referrals.form', [
            'clients' => Client::query()
                ->where('is_active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(StoreReferralRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $referral = DB::transaction(function () use ($data): Referral {
            Client::query()->lockForUpdate()->findOrFail($data['referrer_client_id']);
            Client::query()->lockForUpdate()->findOrFail($data['referred_client_id']);

            if ((int) $data['referrer_client_id'] === (int) $data['referred_client_id']) {
                throw ValidationException::withMessages([
                    'referred_client_id' => 'Un cliente no puede referirse a sí mismo.',
                ]);
            }

            if (Referral::query()->where('referred_client_id', $data['referred_client_id'])->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'referred_client_id' => 'El cliente ya tiene un referidor registrado.',
                ]);
            }

            return Referral::query()->create([
                ...$data,
                'status' => ReferralStatus::Pending,
                'reward_amount' => 100,
                'reward_status' => ReferralRewardStatus::Pending,
            ]);
        }, attempts: 3);

        return redirect()->route('referrals.show', $referral)->with('success', 'Referido registrado. El beneficio queda pendiente hasta validar la membresía pagada.');
    }

    public function show(Referral $referral): View
    {
        return view('referrals.show', [
            'referral' => $referral->load(['referrer', 'referred', 'rewardUses.sale.receipt']),
            'usedRewardAmount' => (float) $referral->rewardUses()->sum('amount'),
        ]);
    }

    public function activate(Referral $referral): RedirectResponse
    {
        DB::transaction(function () use ($referral): void {
            $referral = Referral::query()->lockForUpdate()->with('referred')->findOrFail($referral->getKey());

            if ($referral->status === ReferralStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'referral' => 'No se puede activar un referido cancelado.',
                ]);
            }

            if ($referral->status === ReferralStatus::Validated) {
                return;
            }

            if (! $this->hasValidPaidMembership($referral)) {
                throw ValidationException::withMessages([
                    'referral' => 'El referido aún no tiene una membresía activa con pago verificable.',
                ]);
            }

            $referral->update([
                'status' => ReferralStatus::Validated,
                'reward_status' => ReferralRewardStatus::Available,
            ]);
        }, attempts: 3);

        return redirect()->route('referrals.show', $referral)->with('success', 'Referido validado y beneficio Q100 disponible.');
    }

    public function cancel(Referral $referral): RedirectResponse
    {
        DB::transaction(function () use ($referral): void {
            $referral = Referral::query()->lockForUpdate()->findOrFail($referral->getKey());

            if ($referral->rewardUses()->exists()) {
                throw ValidationException::withMessages([
                    'referral' => 'No se puede cancelar un referido con crédito ya utilizado.',
                ]);
            }

            $referral->update([
                'status' => ReferralStatus::Cancelled,
                'reward_status' => ReferralRewardStatus::Cancelled,
            ]);
        }, attempts: 3);

        return redirect()->route('referrals.show', $referral)->with('success', 'Referido cancelado.');
    }

    private function hasValidPaidMembership(Referral $referral): bool
    {
        return ClientMembership::query()
            ->where('client_id', $referral->referred_client_id)
            ->where('status', ClientMembershipStatus::Active)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->whereHas('payments', fn ($query) => $query->where('status', PaymentStatus::Paid))
            ->exists();
    }
}
