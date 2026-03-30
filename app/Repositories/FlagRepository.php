<?php

namespace App\Repositories;

use App\Contracts\Repositories\FlagRepositoryInterface;
use App\Models\Flag;
use Illuminate\Support\Collection;

class FlagRepository implements FlagRepositoryInterface
{
    /**
     * Find flag by ID.
     */
    public function findById(int $id): ?Flag
    {
        return Flag::find($id);
    }

    /**
     * Find flag by key for a specific tenant.
     */
    public function findByKey(int $tenantId, string $key): ?Flag
    {
        return Flag::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();
    }

    /**
     * Find flag by key with targeting rules.
     */
    public function findByKeyWithTargeting(int $tenantId, string $key): ?Flag
    {
        return Flag::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->with(['targetingRules.group' => function ($query) {
                $query->select('id', 'identifier');
            }])
            ->first();
    }

    /**
     * Get all flags for a tenant.
     */
    public function getAllForTenant(int $tenantId, array $filters = []): Collection
    {
        $query = Flag::withoutGlobalScopes()
            ->where('tenant_id', $tenantId);

        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', $filters['is_enabled']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('key', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create a new flag.
     */
    public function create(array $data): Flag
    {
        return Flag::create($data);
    }

    /**
     * Update a flag.
     */
    public function update(Flag $flag, array $data): bool
    {
        return $flag->update($data);
    }

    /**
     * Delete a flag (soft delete).
     */
    public function delete(Flag $flag): bool
    {
        // Also soft delete targeting rules
        $flag->targetingRules()->delete();
        
        return $flag->delete();
    }
}
