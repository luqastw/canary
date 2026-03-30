<?php

namespace App\Contracts\Repositories;

use App\Models\Targeting;
use Illuminate\Support\Collection;

interface TargetingRepositoryInterface
{
    /**
     * Create targeting rules in batch.
     */
    public function createBatch(int $flagId, array $groupIds): Collection;

    /**
     * Get targeting rules for a flag.
     */
    public function getRulesForFlag(int $flagId): Collection;

    /**
     * Delete all targeting rules for a flag.
     */
    public function deleteRulesForFlag(int $flagId): int;

    /**
     * Delete a specific targeting rule.
     */
    public function deleteRule(int $flagId, int $groupId): bool;

    /**
     * Validate that flag and groups belong to the same tenant.
     */
    public function validateOwnership(int $tenantId, int $flagId, array $groupIds): bool;
}
