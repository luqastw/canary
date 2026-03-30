<?php

namespace App\Contracts\Repositories;

use App\Models\Flag;
use Illuminate\Support\Collection;

interface FlagRepositoryInterface
{
    /**
     * Find flag by ID.
     */
    public function findById(int $id): ?Flag;

    /**
     * Find flag by key for a specific tenant.
     */
    public function findByKey(int $tenantId, string $key): ?Flag;

    /**
     * Find flag by key with targeting rules.
     */
    public function findByKeyWithTargeting(int $tenantId, string $key): ?Flag;

    /**
     * Get all flags for a tenant.
     */
    public function getAllForTenant(int $tenantId, array $filters = []): Collection;

    /**
     * Create a new flag.
     */
    public function create(array $data): Flag;

    /**
     * Update a flag.
     */
    public function update(Flag $flag, array $data): bool;

    /**
     * Delete a flag (soft delete).
     */
    public function delete(Flag $flag): bool;
}
