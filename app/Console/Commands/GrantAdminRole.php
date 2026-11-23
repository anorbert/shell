<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantAdminRole extends Command
{
    protected $signature = 'shell:grant-admin {email : Email address of an existing user}';

    protected $description = 'Grant administrator access to an existing Shell account';

    public function handle(): int
    {
        $user = User::where('email', strtolower($this->argument('email')))->first();

        if (! $user) {
            $this->components->error('No account exists for that email address.');

            return self::FAILURE;
        }

        if ($user->role === 'admin') {
            $this->components->info('This account is already an administrator.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Grant administrator access to {$user->email}?")) {
            $this->components->warn('No changes made.');

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();

        $this->components->info("Administrator access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
