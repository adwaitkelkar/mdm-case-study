<?php

use Illuminate\Support\Facades\Route;

// This application is an API + separate React SPA. This route only exists
// so visiting the backend root in a browser returns something useful.
Route::get('/', function () {
    return response()->json([
        'service' => 'WorkWize MDM Sync API',
        'status' => 'ok',
        'docs' => '/api',
    ]);
});
