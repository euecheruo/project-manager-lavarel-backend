<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Project Manager API Service',
        'version' => '1.0.0',
        'status' => 'active',
        'documentation' => '/api/documentation'
    ]);
});
