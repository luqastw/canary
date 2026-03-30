<?php

namespace App\Contracts\Services;

use App\Models\Tenant;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new tenant with user.
     */
    public function registerTenant(array $data): array;

    /**
     * Login user and generate API token.
     */
    public function login(string $email, string $password): ?array;

    /**
     * Logout user and revoke current token.
     */
    public function logout(User $user): bool;

    /**
     * Generate new API token for user.
     */
    public function generateApiToken(User $user, string $tokenName = 'api-token'): string;

    /**
     * Revoke all tokens for user.
     */
    public function revokeAllTokens(User $user): bool;
}
