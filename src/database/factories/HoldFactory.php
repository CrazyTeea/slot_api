<?php

namespace Database\Factories;

use App\Models\Hold;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldFactory extends Factory
{
    protected $model = Hold::class;

    public function definition(): array
    {
        return [
            'slot_id' => $this->faker->numberBetween(1, 50),
            'status' => $this->faker->randomElement([Hold::STATUS_HELD, Hold::STATUS_CONFIRMED, Hold::STATUS_CANCELLED]),
            'idempotency_key' => $this->faker->uuid(),
        ];
    }
}
