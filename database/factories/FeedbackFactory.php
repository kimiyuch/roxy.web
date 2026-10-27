<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Reviewer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Feedback> */
class FeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reviewer_id' => Reviewer::factory(),
            'site_id' => fn (array $attributes) => Reviewer::find($attributes['reviewer_id'])->site_id,
            'body' => fake()->sentence(),
            'url' => 'https://staging.example.ch/about',
            'path' => '/about',
            'element' => ['selector' => 'main > h1', 'text' => 'About us'],
        ];
    }
}
