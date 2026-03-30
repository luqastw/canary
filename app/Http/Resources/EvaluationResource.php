<?php

namespace App\Http\Resources;

use App\Services\EvaluationResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct(EvaluationResult $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'enabled' => $this->resource->enabled,
            'reason' => $this->resource->reason,
            'variant' => $this->resource->variant,
        ];
    }
}
