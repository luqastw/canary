<?php

namespace App\Contracts\Repositories;

use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{
    /**
     * Find group by ID.
     */
    public function findById(int $id): ?Group;

    /**
     * Find group by identifier for a specific tenant.
     */
    public function findByIdentifier(int $tenantId, string $identifier): ?Group;

    /**
     * Get all groups for a tenant.
     */
    public function getAllForTenant(int $tenantId): Collection;

    /**
     * Create a new group.
     */
    public function create(array $data): Group;

    /**
     * Update a group.
     */
    public function update(Group $group, array $data): bool;

    /**
     * Delete a group.
     */
    public function delete(Group $group): bool;
}
