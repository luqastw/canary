<?php

namespace App\Contracts\Services;

use App\Models\Flag;
use Illuminate\Support\Collection;

interface FlagServiceInterface
{
    /**
     * Get all flags for current tenant.
     */
    public function getAll(int $tenantId, array $filters = []): Collection;

    /**
     * Get flag by ID.
     */
    public function getById(int $id): ?Flag;

    /**
     * Create a new flag.
     */
    public function create(int $tenantId, array $data): Flag;

    /**
     * Update a flag.
     */
    public function update(Flag $flag, array $data): bool;

    /**
     * Toggle flag enabled status.
     */
    public function toggle(Flag $flag): bool;

    /**
     * Delete a flag.
     */
    public function delete(Flag $flag): bool;
}
