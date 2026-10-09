<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mdm\Jamf\JamfSyncService;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    /**
     * Trigger an MDM sync from Jamf.
     *
     * Jamf is the only provider wired up for this assignment, so this
     * always syncs from Jamf; a provider-selectable endpoint
     * (POST /api/sync/{provider}) would be the natural extension once a
     * second MdmClient/DeviceMapper pair exists.
     */
    public function store(JamfSyncService $syncService): JsonResponse
    {
        $result = $syncService->sync();

        return response()->json([
            'message' => 'Sync completed.',
            'result' => $result->toArray(),
        ]);
    }
}
