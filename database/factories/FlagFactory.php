<?php

namespace Database\Factories;

use App\Models\Flag;
use App\Models\Group;
use App\Models\Targeting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flag>
 */
class FlagFactory extends Factory
{
    protected $model = Flag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'key' => fake()->unique()->slug(3),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_enabled' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the flag is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the flag is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Create flag with targeting rules.
     */
    public function withTargeting(Group $group): static
    {
        return $this->afterCreating(function (Flag $flag) use ($group) {
            Targeting::create([
                'flag_id' => $flag->id,
                'group_id' => $group->id,
            ]);
        });
    }
}
