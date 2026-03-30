<?php

namespace App\Contracts\Services;

use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupServiceInterface
{
    /**
     * Get all groups for current tenant.
     */
    public function getAll(int $tenantId): Collection;

    /**
     * Get group by ID.
     */
    public function getById(int $id): ?Group;

    /**
     * Create a new group.
     */
    public function create(int $tenantId, array $data): Group;

    /**
     * Update a group.
     */
    public function update(Group $group, array $data): bool;

    /**
     * Delete a group.
     */
    public function delete(Group $group): bool;
}
