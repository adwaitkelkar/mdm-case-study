<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mdm\Jamf\JamfApiClient;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // JamfApiClient takes plain config values rather than injectable
        // classes, so it needs an explicit binding; JamfDeviceMapper and
        // JamfSyncService have no such dependencies and are resolved
        // automatically by the container.
        $this->app->singleton(JamfApiClient::class, function () {
            return new JamfApiClient(
                mode: (string) config('services.jamf.mode', 'mock'),
                mockPath: (string) config('services.jamf.mock_path'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Return plain arrays/objects from API Resources instead of
        // wrapping every response in a top-level "data" key - keeps the
        // frontend's API client simple.
        JsonResource::withoutWrapping();
    }
}
