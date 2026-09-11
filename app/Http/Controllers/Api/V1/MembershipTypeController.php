<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMembershipTypeRequest;
use App\Http\Requests\Api\V1\UpdateMembershipTypeRequest;
use App\Http\Resources\Api\V1\MembershipTypeResource;
use App\Models\MembershipType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MembershipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return MembershipTypeResource::collection(
            MembershipType::query()->with('benefits')->orderBy('name')->orderBy('id')->paginate(),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMembershipTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $benefitIds = $data['benefit_ids'] ?? [];
        unset($data['benefit_ids']);

        $membershipType = DB::transaction(function () use ($data, $benefitIds): MembershipType {
            $membershipType = MembershipType::query()->create($data);
            $membershipType->benefits()->sync($benefitIds);

            return $membershipType;
        });

        return (new MembershipTypeResource($membershipType->load('benefits')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipType $membershipType): MembershipTypeResource
    {
        return new MembershipTypeResource($membershipType->load('benefits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMembershipTypeRequest $request, MembershipType $membershipType): MembershipTypeResource
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

        return new MembershipTypeResource($membershipType->refresh()->load('benefits'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipType $membershipType): MembershipTypeResource
    {
        $membershipType->update(['is_active' => false]);

        return new MembershipTypeResource($membershipType);
    }
}
