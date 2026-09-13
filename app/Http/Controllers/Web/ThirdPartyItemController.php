<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreThirdPartyItemRequest;
use App\Http\Requests\Api\V1\UpdateThirdPartyItemRequest;
use App\Models\Branch;
use App\Models\CommercialPartner;
use App\Models\ThirdPartyItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThirdPartyItemController extends Controller
{
    public function index(): View
    {
        return view('third-party-items.index', [
            'thirdPartyItems' => ThirdPartyItem::query()
                ->with(['commercialPartner', 'branches'])
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreThirdPartyItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $branchIds = $data['branch_ids'];
        unset($data['branch_ids']);

        $thirdPartyItem = ThirdPartyItem::query()->create($data);
        $thirdPartyItem->branches()->sync($branchIds);

        return redirect()->route('third-party-items.show', $thirdPartyItem)->with('success', 'Producto o servicio externo creado correctamente.');
    }

    public function show(ThirdPartyItem $thirdPartyItem): View
    {
        return view('third-party-items.show', [
            'thirdPartyItem' => $thirdPartyItem->load(['commercialPartner', 'branches', 'discounts.branches']),
        ]);
    }

    public function edit(ThirdPartyItem $thirdPartyItem): View
    {
        return $this->formView($thirdPartyItem);
    }

    public function update(UpdateThirdPartyItemRequest $request, ThirdPartyItem $thirdPartyItem): RedirectResponse
    {
        $data = $request->validated();
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $thirdPartyItem->update($data);

        if ($branchIds !== null) {
            $thirdPartyItem->branches()->sync($branchIds);
        }

        return redirect()->route('third-party-items.show', $thirdPartyItem)->with('success', 'Producto o servicio externo actualizado correctamente.');
    }

    public function destroy(ThirdPartyItem $thirdPartyItem): RedirectResponse
    {
        $thirdPartyItem->update(['is_active' => false]);

        return redirect()->route('third-party-items.index')->with('success', 'Producto o servicio externo desactivado correctamente.');
    }

    private function formView(?ThirdPartyItem $thirdPartyItem = null): View
    {
        return view('third-party-items.form', [
            'thirdPartyItem' => $thirdPartyItem,
            'commercialPartners' => CommercialPartner::query()->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedBranchIds' => $thirdPartyItem?->branches()->pluck('branches.id')->all() ?? [],
        ]);
    }
}
