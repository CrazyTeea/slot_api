<?php

namespace Database\Factories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlotFactory extends Factory
{
    protected $model = Slot::class;

    public function definition(): array
    {
        return [
            'capacity' => $this->faker->numberBetween(1, 50),
            'remaining' => $this->faker->numberBetween(0, 50)
        ];
    }
}
