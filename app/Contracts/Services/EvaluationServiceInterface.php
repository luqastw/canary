<?php

namespace App\Contracts\Services;

use App\Services\EvaluationContext;
use App\Services\EvaluationResult;

interface EvaluationServiceInterface
{
    /**
     * Evaluate a flag for a given context.
     */
    public function evaluate(int $tenantId, string $flagKey, EvaluationContext $context): EvaluationResult;

    /**
     * Evaluate multiple flags in batch.
     */
    public function evaluateBatch(int $tenantId, array $flagKeys, EvaluationContext $context): array;

    /**
     * Invalidate cache for a specific flag.
     */
    public function invalidateCache(int $tenantId, string $flagKey): bool;
}
