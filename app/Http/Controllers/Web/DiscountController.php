<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDiscountRequest;
use App\Http\Requests\Api\V1\UpdateDiscountRequest;
use App\Models\Branch;
use App\Models\Discount;
use App\Models\ThirdPartyItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function index(): View
    {
        return view('discounts.index', [
            'discounts' => Discount::query()
                ->with(['thirdPartyItems.commercialPartner', 'branches'])
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreDiscountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $thirdPartyItemIds = $data['third_party_item_ids'];
        $branchIds = $data['branch_ids'] ?? [];
        unset($data['third_party_item_ids'], $data['branch_ids']);

        $discount = Discount::query()->create($data);
        $discount->thirdPartyItems()->sync($thirdPartyItemIds);
        $discount->branches()->sync($branchIds);

        return redirect()->route('discounts.show', $discount)->with('success', 'Descuento creado correctamente.');
    }

    public function show(Discount $discount): View
    {
        return view('discounts.show', [
            'discount' => $discount->load(['thirdPartyItems.commercialPartner', 'branches']),
        ]);
    }

    public function edit(Discount $discount): View
    {
        return $this->formView($discount);
    }

    public function update(UpdateDiscountRequest $request, Discount $discount): RedirectResponse
    {
        $data = $request->validated();
        $thirdPartyItemIds = $data['third_party_item_ids'] ?? null;
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['third_party_item_ids'], $data['branch_ids']);

        $discount->update($data);

        if ($thirdPartyItemIds !== null) {
            $discount->thirdPartyItems()->sync($thirdPartyItemIds);
        }

        if ($branchIds !== null) {
            $discount->branches()->sync($branchIds);
        }

        return redirect()->route('discounts.show', $discount)->with('success', 'Descuento actualizado correctamente.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->update(['is_active' => false]);

        return redirect()->route('discounts.index')->with('success', 'Descuento desactivado correctamente.');
    }

    private function formView(?Discount $discount = null): View
    {
        return view('discounts.form', [
            'discount' => $discount,
            'thirdPartyItems' => ThirdPartyItem::query()
                ->with('commercialPartner')
                ->where('is_active', true)
                ->whereHas('commercialPartner', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedThirdPartyItemIds' => $discount?->thirdPartyItems()->pluck('third_party_items.id')->all() ?? [],
            'selectedBranchIds' => $discount?->branches()->pluck('branches.id')->all() ?? [],
        ]);
    }
}
