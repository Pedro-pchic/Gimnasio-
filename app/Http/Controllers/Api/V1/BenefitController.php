<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBenefitRequest;
use App\Http\Requests\Api\V1\UpdateBenefitRequest;
use App\Http\Resources\Api\V1\BenefitResource;
use App\Models\Benefit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return BenefitResource::collection(
            Benefit::query()->with('membershipTypes')->orderBy('name')->orderBy('id')->paginate(),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBenefitRequest $request): JsonResponse
    {
        $data = $request->validated();
        $membershipTypeIds = $data['membership_type_ids'] ?? [];
        unset($data['membership_type_ids']);

        $benefit = DB::transaction(function () use ($data, $membershipTypeIds): Benefit {
            $benefit = Benefit::query()->create($data);
            $benefit->membershipTypes()->sync($membershipTypeIds);

            return $benefit;
        });

        return (new BenefitResource($benefit->load('membershipTypes')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Benefit $benefit): BenefitResource
    {
        return new BenefitResource($benefit->load('membershipTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBenefitRequest $request, Benefit $benefit): BenefitResource
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

        return new BenefitResource($benefit->refresh()->load('membershipTypes'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Benefit $benefit): BenefitResource
    {
        $benefit->update(['is_active' => false]);

        return new BenefitResource($benefit);
    }
}
