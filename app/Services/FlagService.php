<?php

namespace App\Services;

use App\Contracts\Repositories\FlagRepositoryInterface;
use App\Contracts\Services\FlagServiceInterface;
use App\Models\Flag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class FlagService implements FlagServiceInterface
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository
    ) {}

    /**
     * Get all flags for current tenant.
     */
    public function getAll(int $tenantId, array $filters = []): Collection
    {
        return $this->flagRepository->getAllForTenant($tenantId, $filters);
    }

    /**
     * Get flag by ID.
     */
    public function getById(int $id): ?Flag
    {
        return $this->flagRepository->findById($id);
    }

    /**
     * Create a new flag.
     */
    public function create(int $tenantId, array $data): Flag
    {
        // Check if key is unique for tenant
        $existingFlag = $this->flagRepository->findByKey($tenantId, $data['key']);
        if ($existingFlag) {
            throw ValidationException::withMessages([
                'key' => ['A flag with this key already exists.'],
            ]);
        }

        return $this->flagRepository->create([
            'tenant_id' => $tenantId,
            'key' => $data['key'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_enabled' => $data['is_enabled'] ?? false,
        ]);
    }

    /**
     * Update a flag.
     */
    public function update(Flag $flag, array $data): bool
    {
        $result = $this->flagRepository->update($flag, $data);

        // Invalidate cache
        $this->invalidateCache($flag->tenant_id, $flag->key);

        return $result;
    }

    /**
     * Toggle flag enabled status.
     */
    public function toggle(Flag $flag): bool
    {
        $result = $this->flagRepository->update($flag, [
            'is_enabled' => !$flag->is_enabled,
        ]);

        // Invalidate cache
        $this->invalidateCache($flag->tenant_id, $flag->key);

        return $result;
    }

    /**
     * Delete a flag.
     */
    public function delete(Flag $flag): bool
    {
        $tenantId = $flag->tenant_id;
        $key = $flag->key;

        $result = $this->flagRepository->delete($flag);

        // Invalidate cache
        $this->invalidateCache($tenantId, $key);

        return $result;
    }

    /**
     * Invalidate cache for a specific flag.
     */
    private function invalidateCache(int $tenantId, string $key): void
    {
        Cache::forget("flag:{$tenantId}:{$key}");
    }
}
