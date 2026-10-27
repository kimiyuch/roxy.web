<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/** Sign-up: a new account with the person signing up as its owner. */
class CreateNewUser implements CreatesNewUsers
{
    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['email'] = Str::lower(trim($input['email'] ?? ''));

        // Sign-up is open to anyone: a few accounts per hour and address is plenty.
        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many sign-ups from here. Please try again later.']);
        }
        RateLimiter::hit($key, 3600);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::default()],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $account = Account::create(['name' => $input['name']]);

            return User::create([
                'account_id' => $account->id,
                'role' => 'owner',
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);
        });
    }
}
