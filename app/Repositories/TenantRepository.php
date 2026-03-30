<?php

namespace App\Repositories;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class TenantRepository implements TenantRepositoryInterface
{
    /**
     * Find tenant by ID.
     */
    public function findById(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    /**
     * Find tenant by email.
     */
    public function findByEmail(string $email): ?Tenant
    {
        return Tenant::where('email', $email)->first();
    }

    /**
     * Get all tenants.
     */
    public function getAll(): Collection
    {
        return Tenant::all();
    }

    /**
     * Create a new tenant.
     */
    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    /**
     * Update a tenant.
     */
    public function update(Tenant $tenant, array $data): bool
    {
        return $tenant->update($data);
    }

    /**
     * Create a user for the tenant.
     */
    public function createUser(Tenant $tenant, array $userData): User
    {
        return $tenant->users()->create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);
    }
}
