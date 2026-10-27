<?php

namespace Database\Factories;

use App\Models\Reviewer;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Reviewer> */
class ReviewerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
        ];
    }
}
