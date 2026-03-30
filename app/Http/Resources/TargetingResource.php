<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'flag_id' => $this->flag_id,
            'group' => new GroupResource($this->whenLoaded('group')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
