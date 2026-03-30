<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface TargetingServiceInterface
{
    /**
     * Create targeting rules for a flag.
     */
    public function createRules(int $tenantId, int $flagId, array $groupIds): Collection;

    /**
     * Remove a specific targeting rule.
     */
    public function removeRule(int $tenantId, int $flagId, int $groupId): bool;

    /**
     * Replace all targeting rules for a flag.
     */
    public function replaceRules(int $tenantId, int $flagId, array $groupIds): Collection;

    /**
     * Get targeting rules for a flag.
     */
    public function getRulesForFlag(int $tenantId, int $flagId): Collection;
}
