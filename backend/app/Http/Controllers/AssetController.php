<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AssetController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $assets = Asset::query()
            ->with('employee')
            ->orderBy('device_name')
            ->get();

        return AssetResource::collection($assets);
    }

    public function show(Asset $asset): AssetResource
    {
        return new AssetResource($asset->load('employee'));
    }

    /**
     * Deleting an asset only removes it from the local database - it does
     * not touch Jamf. Re-running sync will recreate it, since Jamf remains
     * the source of truth (per the MDM Sync Behaviour Rules).
     */
    public function destroy(Asset $asset): Response
    {
        $asset->delete();

        return response()->noContent();
    }
}
