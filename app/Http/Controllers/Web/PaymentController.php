<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Models\Client;
use App\Models\Payment;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('payments.index', [
            'payments' => Payment::query()
                ->with(['client', 'user', 'sale'])
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = Payment::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->getKey(),
            'status' => $request->validated('status', PaymentStatus::Paid->value),
        ]);

        return redirect()->route('payments.show', $payment)->with('success', 'Pago registrado correctamente.');
    }

    public function show(Payment $payment): View
    {
        return view('payments.show', [
            'payment' => $payment->load(['client.branch', 'user', 'sale.receipt', 'clientMembership.membershipType']),
        ]);
    }

    public function cancel(Payment $payment): RedirectResponse
    {
        $payment->update(['status' => PaymentStatus::Cancelled]);

        return redirect()->route('payments.show', $payment)->with('success', 'Pago cancelado correctamente.');
    }

    private function formView(): View
    {
        return view('payments.form', [
            'clients' => Client::query()->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'methods' => PaymentMethod::cases(),
            'statuses' => [PaymentStatus::Pending, PaymentStatus::Paid],
        ]);
    }
}
