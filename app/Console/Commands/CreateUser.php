<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('roxy:user {email} {--name=} {--account= : Account name; created if missing (default: the user\'s name)}')]
#[Description('Create a dashboard user (and their account) and print a password')]
class CreateUser extends Command
{
    public function handle(): int
    {
        $email = Str::lower($this->argument('email'));

        if (User::where('email', $email)->exists()) {
            $this->error("{$email} already exists.");

            return self::FAILURE;
        }

        $name = $this->option('name') ?: Str::before($email, '@');
        $account = Account::firstOrCreate(['name' => $this->option('account') ?: $name]);
        $password = Str::password(20, symbols: false);

        User::create([
            'account_id' => $account->id,
            'role' => 'owner',
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Created {$email} in account “{$account->name}”.");
        $this->line("Password: {$password}");

        return self::SUCCESS;
    }
}
