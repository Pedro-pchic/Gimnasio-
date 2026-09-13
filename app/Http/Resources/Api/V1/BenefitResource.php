<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BenefitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'benefit_type' => $this->benefit_type,
            'value' => $this->value,
            'usage_limit' => $this->usage_limit,
            'usage_period' => $this->usage_period,
            'is_active' => $this->is_active,
            'membership_types' => MembershipTypeResource::collection($this->whenLoaded('membershipTypes')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
