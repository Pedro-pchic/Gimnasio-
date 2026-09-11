<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBranchRequest;
use App\Http\Requests\Api\V1\UpdateBranchRequest;
use App\Http\Resources\Api\V1\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return BranchResource::collection(
            Branch::query()->with('services')->orderBy('name')->orderBy('id')->paginate(),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $branch = DB::transaction(function () use ($data, $serviceIds): Branch {
            $branch = Branch::query()->create($data);
            $branch->services()->sync($serviceIds);

            return $branch;
        });

        return (new BranchResource($branch->load('services')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch->load('services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): BranchResource
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

        return new BranchResource($branch->refresh()->load('services'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch): BranchResource
    {
        $branch->update(['is_active' => false]);

        return new BranchResource($branch);
    }
}
