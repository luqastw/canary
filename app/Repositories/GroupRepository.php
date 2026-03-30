<?php

namespace App\Repositories;

use App\Contracts\Repositories\GroupRepositoryInterface;
use App\Models\Group;
use Illuminate\Support\Collection;

class GroupRepository implements GroupRepositoryInterface
{
    /**
     * Find group by ID.
     */
    public function findById(int $id): ?Group
    {
        return Group::find($id);
    }

    /**
     * Find group by identifier for a specific tenant.
     */
    public function findByIdentifier(int $tenantId, string $identifier): ?Group
    {
        return Group::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('identifier', $identifier)
            ->first();
    }

    /**
     * Get all groups for a tenant.
     */
    public function getAllForTenant(int $tenantId): Collection
    {
        return Group::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new group.
     */
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    /**
     * Update a group.
     */
    public function update(Group $group, array $data): bool
    {
        return $group->update($data);
    }

    /**
     * Delete a group.
     */
    public function delete(Group $group): bool
    {
        // Also delete targeting rules associated with this group
        $group->targetingRules()->delete();
        
        return $group->delete();
    }
}
