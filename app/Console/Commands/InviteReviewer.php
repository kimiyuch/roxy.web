<?php

namespace App\Console\Commands;

use App\Actions\Reviewers\InviteReviewer as InviteReviewerAction;
use App\Models\Site;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

#[Signature('roxy:invite {site : Site id or key} {email} {--name=}')]
#[Description('Invite a reviewer to a site by email (prints the magic link too)')]
class InviteReviewer extends Command
{
    public function handle(InviteReviewerAction $invite): int
    {
        $site = Site::where('id', $this->argument('site'))->orWhere('key', $this->argument('site'))->firstOrFail();

        $reviewer = $invite->execute($site, $this->argument('email'), $this->option('name'));

        $this->info("Invited {$reviewer->email} to {$site->name}.");
        $this->line(URL::temporarySignedRoute('reviewers.magic-link', now()->addDays(config('roxy.invite_link_days')), ['reviewer' => $reviewer]));

        return self::SUCCESS;
    }
}
