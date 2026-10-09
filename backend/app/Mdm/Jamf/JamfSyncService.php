<?php

declare(strict_types=1);

namespace App\Mdm\Jamf;

use App\Mdm\DeviceSyncService;
use App\Mdm\SyncResult;

/**
 * Thin, provider-specific entry point over the generic DeviceSyncService -
 * exists mainly so controllers can type-hint "JamfSyncService" and have
 * Laravel's container wire up the right client + mapper (see
 * AppServiceProvider).
 *
 * Adding a second provider follows the same pattern, e.g.:
 *
 *   final class KandjiSyncService
 *   {
 *       public function __construct(
 *           private readonly KandjiApiClient $client,
 *           private readonly KandjiDeviceMapper $mapper,
 *       ) {}
 *
 *       public function sync(): SyncResult
 *       {
 *           return (new DeviceSyncService($this->client, $this->mapper))->sync();
 *       }
 *   }
 */
final class JamfSyncService
{
    public function __construct(
        private readonly JamfApiClient $client,
        private readonly JamfDeviceMapper $mapper,
    ) {
    }

    public function sync(): SyncResult
    {
        return (new DeviceSyncService($this->client, $this->mapper))->sync();
    }
}
