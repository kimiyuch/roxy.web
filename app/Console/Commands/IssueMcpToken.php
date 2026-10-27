<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('roxy:mcp-token {email} {--name=Claude Code}')]
#[Description('Issue an MCP token for a user and print the `claude mcp add` command')]
class IssueMcpToken extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->firstOrFail();
        $token = $user->createToken($this->option('name'), ['mcp'])->plainTextToken;

        $this->info('Token (shown once):');
        $this->line("  {$token}");
        $this->newLine();
        $this->line('  claude mcp add --transport http roxy '.url('mcp').' --header "Authorization: Bearer '.$token.'"');

        return self::SUCCESS;
    }
}
