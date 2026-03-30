<?php

namespace App\Services;

use App\Contracts\Repositories\FlagRepositoryInterface;
use App\Contracts\Services\EvaluationServiceInterface;
use Illuminate\Support\Facades\Cache;

class EvaluationService implements EvaluationServiceInterface
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private FlagRepositoryInterface $flagRepository
    ) {}

    /**
     * Evaluate a flag for a given context.
     */
    public function evaluate(int $tenantId, string $flagKey, EvaluationContext $context): EvaluationResult
    {
        $cacheKey = "flag:{$tenantId}:{$flagKey}";

        // Try to get from cache
        $flag = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId, $flagKey) {
            return $this->flagRepository->findByKeyWithTargeting($tenantId, $flagKey);
        });

        // Flag not found - fail-safe return false
        if (!$flag) {
            return EvaluationResult::default();
        }

        // Check targeting rules first
        if ($flag->targetingRules && $flag->targetingRules->isNotEmpty()) {
            $enabled = $flag->targetingRules
                ->pluck('group.identifier')
                ->contains($context->role);

            return EvaluationResult::targeting($enabled);
        }

        // No targeting rules - return global status
        return EvaluationResult::global($flag->is_enabled);
    }

    /**
     * Evaluate multiple flags in batch.
     */
    public function evaluateBatch(int $tenantId, array $flagKeys, EvaluationContext $context): array
    {
        $results = [];

        foreach ($flagKeys as $flagKey) {
            $results[$flagKey] = $this->evaluate($tenantId, $flagKey, $context);
        }

        return $results;
    }

    /**
     * Invalidate cache for a specific flag.
     */
    public function invalidateCache(int $tenantId, string $flagKey): bool
    {
        return Cache::forget("flag:{$tenantId}:{$flagKey}");
    }
}
