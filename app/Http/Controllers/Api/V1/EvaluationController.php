<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\EvaluationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluation\BatchEvaluateRequest;
use App\Http\Requests\Evaluation\EvaluateRequest;
use App\Http\Resources\BatchEvaluationResource;
use App\Http\Resources\EvaluationResource;
use App\Services\EvaluationContext;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    public function __construct(
        private EvaluationServiceInterface $evaluationService
    ) {}

    /**
     * Evaluate a flag for a given context.
     */
    public function evaluate(EvaluateRequest $request): JsonResponse
    {
        $context = EvaluationContext::fromArray($request->input('context'));

        $result = $this->evaluationService->evaluate(
            $request->user()->tenant_id,
            $request->input('flag_key'),
            $context
        );

        return response()->json(new EvaluationResource($result));
    }

    /**
     * Evaluate multiple flags in batch.
     */
    public function evaluateBatch(BatchEvaluateRequest $request): JsonResponse
    {
        $context = EvaluationContext::fromArray($request->input('context'));

        $results = $this->evaluationService->evaluateBatch(
            $request->user()->tenant_id,
            $request->input('flag_keys'),
            $context
        );

        return response()->json(new BatchEvaluationResource($results));
    }
}
