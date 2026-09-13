<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreQualityCertificateRequest;
use App\Models\PurchaseOrder;
use App\Models\QualityCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QualityCertificateController extends Controller
{
    public function index(): View
    {
        return view('quality-certificates.index', ['certificates' => QualityCertificate::query()->with('purchaseOrder')->latest('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('quality-certificates.form', ['purchaseOrders' => PurchaseOrder::query()->latest('id')->get()]);
    }

    public function store(StoreQualityCertificateRequest $request): RedirectResponse
    {
        QualityCertificate::query()->create($request->validated());

        return redirect()->route('quality-certificates.index')->with('success', 'Certificado registrado correctamente.');
    }
}
