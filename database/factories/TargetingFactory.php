<?php

namespace Database\Factories;

use App\Models\Flag;
use App\Models\Group;
use App\Models\Targeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Targeting>
 */
class TargetingFactory extends Factory
{
    protected $model = Targeting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flag_id' => Flag::factory(),
            'group_id' => Group::factory(),
        ];
    }
}
