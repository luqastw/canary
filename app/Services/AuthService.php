<?php

namespace App\Services;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Enums\TenantStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository
    ) {}

    /**
     * Register a new tenant with user.
     */
    public function registerTenant(array $data): array
    {
        // Check if tenant email already exists
        $existingTenant = $this->tenantRepository->findByEmail($data['email']);
        if ($existingTenant) {
            throw ValidationException::withMessages([
                'email' => ['A tenant with this email already exists.'],
            ]);
        }

        // Create tenant
        $tenant = $this->tenantRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => TenantStatus::ACTIVE,
        ]);

        // Create user for tenant
        $user = $this->tenantRepository->createUser($tenant, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // Generate API token
        $token = $this->generateApiToken($user);

        return [
            'tenant' => $tenant,
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user and generate API token.
     */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Check if tenant is active
        if (!$user->tenant || !$user->tenant->isActive()) {
            return null;
        }

        $token = $this->generateApiToken($user);

        return [
            'user' => $user,
            'tenant' => $user->tenant,
            'token' => $token,
        ];
    }

    /**
     * Logout user and revoke current token.
     */
    public function logout(User $user): bool
    {
        // Revoke current token
        $user->currentAccessToken()?->delete();

        return true;
    }

    /**
     * Generate new API token for user.
     */
    public function generateApiToken(User $user, string $tokenName = 'api-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke all tokens for user.
     */
    public function revokeAllTokens(User $user): bool
    {
        $user->tokens()->delete();

        return true;
    }
}
