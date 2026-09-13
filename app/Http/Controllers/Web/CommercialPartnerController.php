<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCommercialPartnerRequest;
use App\Http\Requests\Api\V1\UpdateCommercialPartnerRequest;
use App\Models\CommercialPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommercialPartnerController extends Controller
{
    public function index(): View
    {
        return view('commercial-partners.index', [
            'commercialPartners' => CommercialPartner::query()
                ->withCount('thirdPartyItems')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('commercial-partners.form', ['commercialPartner' => null]);
    }

    public function store(StoreCommercialPartnerRequest $request): RedirectResponse
    {
        $commercialPartner = CommercialPartner::query()->create($request->validated());

        return redirect()->route('commercial-partners.show', $commercialPartner)->with('success', 'Tercero creado correctamente.');
    }

    public function show(CommercialPartner $commercialPartner): View
    {
        return view('commercial-partners.show', [
            'commercialPartner' => $commercialPartner->load('thirdPartyItems.branches'),
        ]);
    }

    public function edit(CommercialPartner $commercialPartner): View
    {
        return view('commercial-partners.form', compact('commercialPartner'));
    }

    public function update(UpdateCommercialPartnerRequest $request, CommercialPartner $commercialPartner): RedirectResponse
    {
        $commercialPartner->update($request->validated());

        return redirect()->route('commercial-partners.show', $commercialPartner)->with('success', 'Tercero actualizado correctamente.');
    }

    public function destroy(CommercialPartner $commercialPartner): RedirectResponse
    {
        $commercialPartner->update(['is_active' => false]);

        return redirect()->route('commercial-partners.index')->with('success', 'Tercero desactivado correctamente.');
    }
}
