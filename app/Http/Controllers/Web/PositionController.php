<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePositionRequest;
use App\Http\Requests\Api\V1\UpdatePositionRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        return view('positions.index', [
            'positions' => Position::query()
                ->withCount('employees')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('positions.form', ['position' => null]);
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::query()->create($request->validated());

        return redirect()->route('positions.index')->with('success', 'Puesto creado correctamente.');
    }

    public function edit(Position $position): View
    {
        return view('positions.form', ['position' => $position]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()->route('positions.index')->with('success', 'Puesto actualizado correctamente.');
    }

    public function toggleStatus(Position $position): RedirectResponse
    {
        $position->update(['is_active' => ! $position->is_active]);

        return back()->with('success', 'Estado del puesto actualizado correctamente.');
    }
}
