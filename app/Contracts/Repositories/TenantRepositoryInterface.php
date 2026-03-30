<?php

namespace App\Contracts\Repositories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

interface TenantRepositoryInterface
{
    /**
     * Find tenant by ID.
     */
    public function findById(int $id): ?Tenant;

    /**
     * Find tenant by email.
     */
    public function findByEmail(string $email): ?Tenant;

    /**
     * Get all tenants.
     */
    public function getAll(): Collection;

    /**
     * Create a new tenant.
     */
    public function create(array $data): Tenant;

    /**
     * Update a tenant.
     */
    public function update(Tenant $tenant, array $data): bool;

    /**
     * Create a user for the tenant.
     */
    public function createUser(Tenant $tenant, array $userData): User;
}
