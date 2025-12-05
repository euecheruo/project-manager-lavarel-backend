<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RefreshToken;

class PruneExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan tokens:prune
     */
    protected $signature = 'tokens:prune';

    /**
     * The console command description.
     */
    protected $description = 'Flush expired refresh tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Finding expired tokens...');

        $count = RefreshToken::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$count} expired tokens.");
    }
}
