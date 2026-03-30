<?php

namespace Tests\Feature\Api;

use App\Models\Flag;
use App\Models\Group;
use App\Models\Targeting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->forTenant($this->tenant)->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_evaluates_flag_without_targeting_returns_global_status(): void
    {
        $flag = Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'test-flag',
            'is_enabled' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/evaluate', [
                'flag_key' => 'test-flag',
                'context' => [
                    'user_id' => 'user-123',
                    'role' => 'admin',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'enabled' => true,
                'reason' => 'global',
            ]);
    }

    public function test_evaluates_flag_with_targeting_returns_targeting_result(): void
    {
        $flag = Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'beta-feature',
            'is_enabled' => false,
        ]);

        $group = Group::factory()->create([
            'tenant_id' => $this->tenant->id,
            'identifier' => 'beta-users',
        ]);

        Targeting::factory()->create([
            'flag_id' => $flag->id,
            'group_id' => $group->id,
        ]);

        // User in beta group should get enabled
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/evaluate', [
                'flag_key' => 'beta-feature',
                'context' => [
                    'user_id' => 'user-123',
                    'role' => 'beta-users',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'enabled' => true,
                'reason' => 'targeting',
            ]);

        // User not in beta group should get disabled
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/evaluate', [
                'flag_key' => 'beta-feature',
                'context' => [
                    'user_id' => 'user-456',
                    'role' => 'regular-users',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'enabled' => false,
                'reason' => 'targeting',
            ]);
    }

    public function test_evaluates_nonexistent_flag_returns_default_false(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/evaluate', [
                'flag_key' => 'nonexistent-flag',
                'context' => [
                    'user_id' => 'user-123',
                    'role' => 'admin',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'enabled' => false,
                'reason' => 'default',
            ]);
    }

    public function test_evaluates_batch_of_flags(): void
    {
        Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'feature-a',
            'is_enabled' => true,
        ]);

        Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'feature-b',
            'is_enabled' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/evaluate/batch', [
                'flag_keys' => ['feature-a', 'feature-b', 'nonexistent'],
                'context' => [
                    'user_id' => 'user-123',
                    'role' => 'admin',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('feature-a.enabled', true)
            ->assertJsonPath('feature-b.enabled', false)
            ->assertJsonPath('nonexistent.enabled', false)
            ->assertJsonPath('nonexistent.reason', 'default');
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/evaluate', [
            'flag_key' => 'test-flag',
            'context' => [
                'user_id' => 'user-123',
                'role' => 'admin',
            ],
        ]);

        $response->assertStatus(401);
    }
}
