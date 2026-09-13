<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBranchRequest;
use App\Http\Requests\Api\V1\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('branches.index', [
            'branches' => Branch::query()
                ->with('services')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $branch = DB::transaction(function () use ($data, $serviceIds): Branch {
            $branch = Branch::query()->create($data);
            $branch->services()->sync($serviceIds);

            return $branch;
        });

        return redirect()->route('branches.show', $branch)->with('success', 'Sucursal creada correctamente.');
    }

    public function show(Branch $branch): View
    {
        return view('branches.show', [
            'branch' => $branch->load('services'),
        ]);
    }

    public function edit(Branch $branch): View
    {
        return $this->formView($branch->load('services'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? null;
        unset($data['service_ids']);

        DB::transaction(function () use ($branch, $data, $serviceIds): void {
            $branch->update($data);

            if ($serviceIds !== null) {
                $branch->services()->sync($serviceIds);
            }
        });

        return redirect()->route('branches.show', $branch)->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->update(['is_active' => false]);

        return redirect()->route('branches.index')->with('success', 'Sucursal desactivada correctamente.');
    }

    public function toggleStatus(Branch $branch): RedirectResponse
    {
        $branch->update(['is_active' => ! $branch->is_active]);

        return back()->with('success', 'Estado de la sucursal actualizado correctamente.');
    }

    private function formView(?Branch $branch = null): View
    {
        return view('branches.form', [
            'branch' => $branch,
            'services' => Service::query()->orderBy('name')->orderBy('id')->get(),
        ]);
    }
}
