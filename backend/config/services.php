<?php

return [

    // Configuration for the Jamf MDM provider integration.
    // In "mock" mode the client reads a static JSON fixture instead of
    // calling the real Jamf Pro API, which is what this assignment uses.
    'jamf' => [
        'mode' => env('JAMF_MODE', 'mock'),
        'mock_path' => env('JAMF_MOCK_PATH', base_path('mock/jamf/api-mock-response.json')),
        'base_url' => env('JAMF_BASE_URL'),
        'client_id' => env('JAMF_CLIENT_ID'),
        'client_secret' => env('JAMF_CLIENT_SECRET'),
    ],

];
