<?php

declare(strict_types=1);

namespace App\Mdm;

/**
 * Tally of what happened during one sync run. Returned by
 * DeviceSyncService::sync() and serialized straight into the API response
 * so the frontend can show a summary after clicking "Sync Now".
 */
final class SyncResult
{
    public int $createdAssets = 0;
    public int $updatedAssets = 0;
    public int $createdEmployees = 0;
    public int $skippedUnassigned = 0;
    public int $skippedUnmappable = 0;

    public function __construct(
        public readonly string $provider,
    ) {
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'created_assets' => $this->createdAssets,
            'updated_assets' => $this->updatedAssets,
            'created_employees' => $this->createdEmployees,
            'skipped_unassigned' => $this->skippedUnassigned,
            'skipped_unmappable' => $this->skippedUnmappable,
            'total_synced' => $this->createdAssets + $this->updatedAssets,
        ];
    }
}
