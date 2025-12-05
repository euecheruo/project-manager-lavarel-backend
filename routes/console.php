<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tokens:prune')->daily();

Schedule::command('model:prune', [
    '--model' => [\App\Models\User::class, \App\Models\Project::class],
])->daily();
