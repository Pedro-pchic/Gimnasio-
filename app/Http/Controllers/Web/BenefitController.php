<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBenefitRequest;
use App\Http\Requests\Api\V1\UpdateBenefitRequest;
use App\Models\Benefit;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BenefitController extends Controller
{
    public function index(): View
    {
        return view('benefits.index', [
            'benefits' => Benefit::query()
                ->with('membershipTypes')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreBenefitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $membershipTypeIds = $data['membership_type_ids'] ?? [];
        unset($data['membership_type_ids']);

        $benefit = DB::transaction(function () use ($data, $membershipTypeIds): Benefit {
            $benefit = Benefit::query()->create($data);
            $benefit->membershipTypes()->sync($membershipTypeIds);

            return $benefit;
        });

        return redirect()->route('benefits.show', $benefit)->with('success', 'Beneficio creado correctamente.');
    }

    public function show(Benefit $benefit): View
    {
        return view('benefits.show', ['benefit' => $benefit->load('membershipTypes')]);
    }

    public function edit(Benefit $benefit): View
    {
        return $this->formView($benefit->load('membershipTypes'));
    }

    public function update(UpdateBenefitRequest $request, Benefit $benefit): RedirectResponse
    {
        $data = $request->validated();
        $membershipTypeIds = $data['membership_type_ids'] ?? null;
        unset($data['membership_type_ids']);

        DB::transaction(function () use ($benefit, $data, $membershipTypeIds): void {
            $benefit->update($data);

            if ($membershipTypeIds !== null) {
                $benefit->membershipTypes()->sync($membershipTypeIds);
            }
        });

        return redirect()->route('benefits.show', $benefit)->with('success', 'Beneficio actualizado correctamente.');
    }

    public function destroy(Benefit $benefit): RedirectResponse
    {
        $benefit->update(['is_active' => false]);

        return redirect()->route('benefits.index')->with('success', 'Beneficio desactivado correctamente.');
    }

    public function toggleStatus(Benefit $benefit): RedirectResponse
    {
        $benefit->update(['is_active' => ! $benefit->is_active]);

        return back()->with('success', 'Estado del beneficio actualizado correctamente.');
    }

    private function formView(?Benefit $benefit = null): View
    {
        return view('benefits.form', [
            'benefit' => $benefit,
            'membershipTypes' => MembershipType::query()->orderBy('name')->orderBy('id')->get(),
        ]);
    }
}
