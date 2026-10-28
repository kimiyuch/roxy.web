<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('roxy:site {user : Email of a user in the account} {name} {url : Where magic links land} {--origin=* : Extra allowed origins (the URL\'s own origin is always allowed)} {--repository= : e.g. kimiyuch/croc.web}')]
#[Description('Add a site and print the script tag')]
class CreateSite extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('user'))->firstOrFail();
        $url = $this->argument('url');
        $origin = Site::originOf($url);

        $site = Site::create([
            'account_id' => $user->account_id,
            'name' => $this->argument('name'),
            'start_url' => $url,
            'allowed_origins' => array_values(array_unique([$origin, ...$this->option('origin')])),
            'repository' => $this->option('repository'),
        ]);

        $this->info("Created {$site->name} ({$site->id}).");
        $this->line('Script tag:');
        $this->line('  '.$site->scriptTag());

        return self::SUCCESS;
    }
}
