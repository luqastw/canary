<?php

namespace App\Repositories;

use App\Contracts\Repositories\TargetingRepositoryInterface;
use App\Models\Flag;
use App\Models\Group;
use App\Models\Targeting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TargetingRepository implements TargetingRepositoryInterface
{
    /**
     * Create targeting rules in batch.
     */
    public function createBatch(int $flagId, array $groupIds): Collection
    {
        $rules = collect();

        foreach ($groupIds as $groupId) {
            $rule = Targeting::create([
                'flag_id' => $flagId,
                'group_id' => $groupId,
            ]);
            $rules->push($rule);
        }

        return $rules;
    }

    /**
     * Get targeting rules for a flag.
     */
    public function getRulesForFlag(int $flagId): Collection
    {
        return Targeting::where('flag_id', $flagId)
            ->with('group')
            ->get();
    }

    /**
     * Delete all targeting rules for a flag.
     */
    public function deleteRulesForFlag(int $flagId): int
    {
        return Targeting::where('flag_id', $flagId)->delete();
    }

    /**
     * Delete a specific targeting rule.
     */
    public function deleteRule(int $flagId, int $groupId): bool
    {
        return Targeting::where('flag_id', $flagId)
            ->where('group_id', $groupId)
            ->delete() > 0;
    }

    /**
     * Validate that flag and groups belong to the same tenant.
     */
    public function validateOwnership(int $tenantId, int $flagId, array $groupIds): bool
    {
        // Verify flag belongs to tenant
        $flagExists = Flag::withoutGlobalScopes()
            ->where('id', $flagId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$flagExists) {
            return false;
        }

        // Verify all groups belong to tenant
        $groupCount = Group::withoutGlobalScopes()
            ->whereIn('id', $groupIds)
            ->where('tenant_id', $tenantId)
            ->count();

        return $groupCount === count($groupIds);
    }
}
