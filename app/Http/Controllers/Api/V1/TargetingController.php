<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\TargetingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Targeting\CreateTargetingRequest;
use App\Http\Requests\Targeting\ReplaceTargetingRequest;
use App\Http\Resources\TargetingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TargetingController extends Controller
{
    public function __construct(
        private TargetingServiceInterface $targetingService
    ) {}

    /**
     * Store new targeting rules.
     */
    public function store(CreateTargetingRequest $request): JsonResponse
    {
        $rules = $this->targetingService->createRules(
            $request->user()->tenant_id,
            $request->input('flag_id'),
            $request->input('group_ids')
        );

        $rules->load('group');

        return response()->json([
            'message' => 'Targeting rules created successfully.',
            'data' => TargetingResource::collection($rules),
        ], 201);
    }

    /**
     * Get targeting rules for a flag.
     */
    public function index(Request $request, int $flagId): JsonResponse
    {
        $rules = $this->targetingService->getRulesForFlag(
            $request->user()->tenant_id,
            $flagId
        );

        return response()->json([
            'data' => TargetingResource::collection($rules),
            'meta' => [
                'total' => $rules->count(),
            ],
        ]);
    }

    /**
     * Replace all targeting rules for a flag.
     */
    public function replace(ReplaceTargetingRequest $request, int $flagId): JsonResponse
    {
        $rules = $this->targetingService->replaceRules(
            $request->user()->tenant_id,
            $flagId,
            $request->input('group_ids', [])
        );

        $rules->load('group');

        return response()->json([
            'message' => 'Targeting rules replaced successfully.',
            'data' => TargetingResource::collection($rules),
        ]);
    }

    /**
     * Remove a specific targeting rule.
     */
    public function destroy(Request $request, int $flagId, int $groupId): JsonResponse
    {
        $this->targetingService->removeRule(
            $request->user()->tenant_id,
            $flagId,
            $groupId
        );

        return response()->json([
            'message' => 'Targeting rule removed successfully.',
        ]);
    }
}
