<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\Api\V1\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return ClientResource::collection(
            Client::query()->with('branch')->orderBy('last_name')->orderBy('first_name')->orderBy('id')->paginate(),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::query()->create($request->validated());

        return (new ClientResource($client->load('branch')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): ClientResource
    {
        return new ClientResource($client->load('branch', 'memberships.membershipType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client->update($request->validated());

        return new ClientResource($client->refresh()->load('branch'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): ClientResource
    {
        $client->update(['is_active' => false]);

        return new ClientResource($client);
    }
}
