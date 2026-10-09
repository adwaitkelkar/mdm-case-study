<?php

declare(strict_types=1);

namespace App\Mdm;

use App\Mdm\Contracts\DeviceMapper;
use App\Mdm\Contracts\MdmClient;
use App\Mdm\DTO\NormalizedDevice;
use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Provider-agnostic device sync engine.
 *
 * This class enforces the MDM Sync Behaviour Rules (asset uniqueness by
 * serial code, employee uniqueness by email, skipping unassigned devices,
 * the provider being the source of truth, etc.) against a stream of
 * NormalizedDevice objects. It has no idea Jamf exists - supporting another
 * provider means writing a new MdmClient + DeviceMapper pair and handing
 * them to this same class, not touching this class.
 */
final class DeviceSyncService
{
    public function __construct(
        private readonly MdmClient $client,
        private readonly DeviceMapper $mapper,
    ) {
    }

    public function sync(): SyncResult
    {
        $result = new SyncResult($this->client->provider());
        $rawDevices = $this->client->fetchDevices();

        // The whole run is one transaction: a sync either fully applies or
        // fully rolls back, so a mid-run failure (e.g. a bad DB connection)
        // never leaves assets/employees half-updated.
        DB::transaction(function () use ($rawDevices, $result) {
            foreach ($rawDevices as $rawDevice) {
                $this->syncOne($rawDevice, $result);
            }
        });

        return $result;
    }

    /**
     * @param array<string, mixed> $rawDevice
     */
    private function syncOne(array $rawDevice, SyncResult $result): void
    {
        $device = $this->mapper->map($rawDevice);

        if ($device === null) {
            $result->skippedUnmappable++;

            return;
        }

        // Unassigned devices (no employee email) must not be imported.
        if (!$device->isAssigned()) {
            $result->skippedUnassigned++;

            return;
        }

        $employee = $this->upsertEmployee($device, $result);
        $this->upsertAsset($device, $employee, $result);
    }

    private function upsertEmployee(NormalizedDevice $device, SyncResult $result): Employee
    {
        /** @var Employee $employee */
        $employee = Employee::query()->firstOrNew(['email' => $device->employeeEmail]);
        $isNew = !$employee->exists;

        // The provider is the source of truth: refresh fields it supplies a
        // value for, but don't blank out existing local data when it sends
        // nothing for a field (e.g. phone is often null in Jamf).
        $employee->name = $device->employeeName ?? $employee->name;
        $employee->phone = $device->employeePhone ?? $employee->phone;
        $employee->save();

        if ($isNew) {
            $result->createdEmployees++;
        }

        return $employee;
    }

    private function upsertAsset(NormalizedDevice $device, Employee $employee, SyncResult $result): void
    {
        /** @var Asset $asset */
        $asset = Asset::query()->firstOrNew(['serial_code' => $device->serialCode]);
        $isNew = !$asset->exists;

        // Re-assigning an asset to a different employee, or changing its
        // attributes (e.g. RAM 8GB -> 16GB), is exactly this: overwrite
        // every synced field with what the provider reports right now.
        $asset->employee_id = $employee->id;
        $asset->device_name = $device->deviceName;
        $asset->provider = $device->provider;
        $asset->external_id = $device->externalId;
        $asset->attributes = $device->attributes;
        $asset->last_synced_at = Carbon::now();
        $asset->save();

        $isNew ? $result->createdAssets++ : $result->updatedAssets++;
    }
}
