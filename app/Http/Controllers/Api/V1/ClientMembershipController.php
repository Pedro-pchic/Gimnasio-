<?php

namespace App\Http\Controllers\Api\V1;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientMembershipRequest;
use App\Http\Requests\Api\V1\UpdateClientMembershipRequest;
use App\Http\Resources\Api\V1\ClientMembershipResource;
use App\Models\ClientMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientMembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return ClientMembershipResource::collection(
            ClientMembership::query()
                ->with(['client', 'membershipType'])
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->paginate(),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientMembershipRequest $request): JsonResponse
    {
        $clientMembership = ClientMembership::query()->create($request->validated());

        return (new ClientMembershipResource($clientMembership->load(['client', 'membershipType'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientMembership $clientMembership): ClientMembershipResource
    {
        return new ClientMembershipResource($clientMembership->load(['client', 'membershipType']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientMembershipRequest $request, ClientMembership $clientMembership): ClientMembershipResource
    {
        $clientMembership->update($request->validated());

        return new ClientMembershipResource($clientMembership->refresh()->load(['client', 'membershipType']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientMembership $clientMembership): ClientMembershipResource
    {
        $clientMembership->update(['status' => ClientMembershipStatus::Cancelled]);

        return new ClientMembershipResource($clientMembership->refresh());
    }
}
