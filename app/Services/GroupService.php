<?php

namespace App\Services;

use App\Contracts\Repositories\GroupRepositoryInterface;
use App\Contracts\Services\GroupServiceInterface;
use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GroupService implements GroupServiceInterface
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {}

    /**
     * Get all groups for current tenant.
     */
    public function getAll(int $tenantId): Collection
    {
        return $this->groupRepository->getAllForTenant($tenantId);
    }

    /**
     * Get group by ID.
     */
    public function getById(int $id): ?Group
    {
        return $this->groupRepository->findById($id);
    }

    /**
     * Create a new group.
     */
    public function create(int $tenantId, array $data): Group
    {
        // Check if identifier is unique for tenant
        $existingGroup = $this->groupRepository->findByIdentifier($tenantId, $data['identifier']);
        if ($existingGroup) {
            throw ValidationException::withMessages([
                'identifier' => ['A group with this identifier already exists.'],
            ]);
        }

        return $this->groupRepository->create([
            'tenant_id' => $tenantId,
            'identifier' => $data['identifier'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update a group.
     */
    public function update(Group $group, array $data): bool
    {
        return $this->groupRepository->update($group, $data);
    }

    /**
     * Delete a group.
     */
    public function delete(Group $group): bool
    {
        return $this->groupRepository->delete($group);
    }
}
