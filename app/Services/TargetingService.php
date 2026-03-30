<?php

namespace App\Services;

use App\Contracts\Repositories\FlagRepositoryInterface;
use App\Contracts\Repositories\TargetingRepositoryInterface;
use App\Contracts\Services\TargetingServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TargetingService implements TargetingServiceInterface
{
    public function __construct(
        private TargetingRepositoryInterface $targetingRepository,
        private FlagRepositoryInterface $flagRepository
    ) {}

    /**
     * Create targeting rules for a flag.
     */
    public function createRules(int $tenantId, int $flagId, array $groupIds): Collection
    {
        // Validate ownership
        if (!$this->targetingRepository->validateOwnership($tenantId, $flagId, $groupIds)) {
            throw new NotFoundHttpException('Resource not found.');
        }

        return DB::transaction(function () use ($flagId, $groupIds, $tenantId) {
            $rules = $this->targetingRepository->createBatch($flagId, $groupIds);

            // Invalidate cache
            $flag = $this->flagRepository->findById($flagId);
            if ($flag) {
                $this->invalidateCache($tenantId, $flag->key);
            }

            return $rules;
        });
    }

    /**
     * Remove a specific targeting rule.
     */
    public function removeRule(int $tenantId, int $flagId, int $groupId): bool
    {
        // Validate ownership
        if (!$this->targetingRepository->validateOwnership($tenantId, $flagId, [$groupId])) {
            throw new NotFoundHttpException('Resource not found.');
        }

        $result = $this->targetingRepository->deleteRule($flagId, $groupId);

        // Invalidate cache
        $flag = $this->flagRepository->findById($flagId);
        if ($flag) {
            $this->invalidateCache($tenantId, $flag->key);
        }

        return $result;
    }

    /**
     * Replace all targeting rules for a flag.
     */
    public function replaceRules(int $tenantId, int $flagId, array $groupIds): Collection
    {
        // Validate ownership
        if (!empty($groupIds) && !$this->targetingRepository->validateOwnership($tenantId, $flagId, $groupIds)) {
            throw new NotFoundHttpException('Resource not found.');
        }

        return DB::transaction(function () use ($flagId, $groupIds, $tenantId) {
            // Delete existing rules
            $this->targetingRepository->deleteRulesForFlag($flagId);

            // Create new rules
            $rules = collect();
            if (!empty($groupIds)) {
                $rules = $this->targetingRepository->createBatch($flagId, $groupIds);
            }

            // Invalidate cache
            $flag = $this->flagRepository->findById($flagId);
            if ($flag) {
                $this->invalidateCache($tenantId, $flag->key);
            }

            return $rules;
        });
    }

    /**
     * Get targeting rules for a flag.
     */
    public function getRulesForFlag(int $tenantId, int $flagId): Collection
    {
        // Validate flag belongs to tenant
        $flag = $this->flagRepository->findById($flagId);
        if (!$flag || $flag->tenant_id !== $tenantId) {
            throw new NotFoundHttpException('Resource not found.');
        }

        return $this->targetingRepository->getRulesForFlag($flagId);
    }

    /**
     * Invalidate cache for a specific flag.
     */
    private function invalidateCache(int $tenantId, string $key): void
    {
        Cache::forget("flag:{$tenantId}:{$key}");
    }
}
