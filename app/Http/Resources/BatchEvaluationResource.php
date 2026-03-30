<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $results = [];

        foreach ($this->resource as $flagKey => $result) {
            $results[$flagKey] = [
                'enabled' => $result->enabled,
                'reason' => $result->reason,
                'variant' => $result->variant,
            ];
        }

        return $results;
    }
}
