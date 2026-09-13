<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMembershipTypeRequest;
use App\Http\Requests\Api\V1\UpdateMembershipTypeRequest;
use App\Models\Benefit;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MembershipTypeController extends Controller
{
    public function index(): View
    {
        return view('membership-types.index', [
            'membershipTypes' => MembershipType::query()
                ->with('benefits')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreMembershipTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $benefitIds = $data['benefit_ids'] ?? [];
        unset($data['benefit_ids']);

        $membershipType = DB::transaction(function () use ($data, $benefitIds): MembershipType {
            $membershipType = MembershipType::query()->create($data);
            $membershipType->benefits()->sync($benefitIds);

            return $membershipType;
        });

        return redirect()->route('membership-types.show', $membershipType)->with('success', 'Tipo de membresía creado correctamente.');
    }

    public function show(MembershipType $membershipType): View
    {
        return view('membership-types.show', ['membershipType' => $membershipType->load('benefits')]);
    }

    public function edit(MembershipType $membershipType): View
    {
        return $this->formView($membershipType->load('benefits'));
    }

    public function update(UpdateMembershipTypeRequest $request, MembershipType $membershipType): RedirectResponse
    {
        $data = $request->validated();
        $benefitIds = $data['benefit_ids'] ?? null;
        unset($data['benefit_ids']);

        DB::transaction(function () use ($membershipType, $data, $benefitIds): void {
            $membershipType->update($data);

            if ($benefitIds !== null) {
                $membershipType->benefits()->sync($benefitIds);
            }
        });

        return redirect()->route('membership-types.show', $membershipType)->with('success', 'Tipo de membresía actualizado correctamente.');
    }

    public function destroy(MembershipType $membershipType): RedirectResponse
    {
        $membershipType->update(['is_active' => false]);

        return redirect()->route('membership-types.index')->with('success', 'Tipo de membresía desactivado correctamente.');
    }

    public function toggleStatus(MembershipType $membershipType): RedirectResponse
    {
        $membershipType->update(['is_active' => ! $membershipType->is_active]);

        return back()->with('success', 'Estado del tipo de membresía actualizado correctamente.');
    }

    private function formView(?MembershipType $membershipType = null): View
    {
        return view('membership-types.form', [
            'membershipType' => $membershipType,
            'benefits' => Benefit::query()->orderBy('name')->orderBy('id')->get(),
        ]);
    }
}
