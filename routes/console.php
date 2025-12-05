<?php

use Illuminate\Support\Facades\Schedule;

// 1. Refresh Token Cleanup
// Deletes expired tokens from the 'refresh_tokens' table.
// Defined in app/Console/Commands/PruneExpiredTokens.php
Schedule::command('tokens:prune')->daily();

// 2. Model Pruning (Optional)
// If you want to permanently remove Soft Deleted users after 30 days
Schedule::command('model:prune', [
    '--model' => [\App\Models\User::class, \App\Models\Project::class],
])->daily();
