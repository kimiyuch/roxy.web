<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signing_up_creates_an_account_with_its_owner(): void
    {
        $this->get('/register')->assertInertia(fn (Assert $page) => $page->component('auth/Register'));

        $this->post('/register', ['name' => 'Anna Keller', 'email' => 'Anna@Example.ch', 'password' => 'a-long-enough-password'])
            ->assertRedirect('/app');

        $user = User::where('email', 'anna@example.ch')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('owner', $user->role);
        $this->assertSame('Anna Keller', $user->account->name);
    }

    public function test_an_email_can_only_sign_up_once(): void
    {
        User::factory()->create(['email' => 'anna@example.ch']);

        $this->post('/register', ['name' => 'Anna', 'email' => 'anna@example.ch', 'password' => 'a-long-enough-password'])
            ->assertSessionHasErrors('email');
    }

    public function test_sign_ups_are_limited_per_address(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/register', ['name' => "Person {$i}", 'email' => "p{$i}@example.ch", 'password' => 'a-long-enough-password']);
            $this->post('/logout');
        }

        $this->post('/register', ['name' => 'One too many', 'email' => 'p6@example.ch', 'password' => 'a-long-enough-password'])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'p6@example.ch']);
    }
}
