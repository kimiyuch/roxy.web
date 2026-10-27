<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Site> */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->domainWord(),
            'allowed_origins' => ['https://staging.example.ch'],
            'start_url' => 'https://staging.example.ch/',
            'repository' => 'kimiyuch/example.web',
        ];
    }
}
