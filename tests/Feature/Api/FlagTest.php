<?php

namespace Tests\Feature\Api;

use App\Models\Flag;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlagTest extends TestCase
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

    public function test_can_list_flags(): void
    {
        Flag::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/flags');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_flag(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/flags', [
                'key' => 'new-feature',
                'name' => 'New Feature',
                'description' => 'A new feature flag',
                'is_enabled' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.key', 'new-feature')
            ->assertJsonPath('data.is_enabled', true);

        $this->assertDatabaseHas('flags', [
            'key' => 'new-feature',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_cannot_create_duplicate_key(): void
    {
        Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'existing-key',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/flags', [
                'key' => 'existing-key',
                'name' => 'Duplicate Flag',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_can_show_flag(): void
    {
        $flag = Flag::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/flags/' . $flag->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $flag->id);
    }

    public function test_cannot_show_other_tenant_flag(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherFlag = Flag::factory()->create(['tenant_id' => $otherTenant->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/flags/' . $otherFlag->id);

        $response->assertStatus(404);
    }

    public function test_can_update_flag(): void
    {
        $flag = Flag::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/flags/' . $flag->id, [
                'name' => 'Updated Name',
                'is_enabled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_toggle_flag(): void
    {
        $flag = Flag::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_enabled' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/v1/flags/' . $flag->id . '/toggle');

        $response->assertStatus(200)
            ->assertJsonPath('data.is_enabled', true);
    }

    public function test_can_delete_flag(): void
    {
        $flag = Flag::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/flags/' . $flag->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('flags', ['id' => $flag->id]);
    }
}
