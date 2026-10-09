<?php

declare(strict_types=1);

namespace App\Mdm\Jamf;

use App\Mdm\Contracts\DeviceMapper;
use App\Mdm\DTO\NormalizedDevice;

/**
 * Maps one raw Jamf Pro computer-inventory object into a NormalizedDevice.
 *
 * Field lookups are deliberately defensive (several candidate paths per
 * field) because Jamf's inventory payloads have shipped slightly different
 * key casing/nesting across API versions (e.g. `realname` vs `realName`).
 */
final class JamfDeviceMapper implements DeviceMapper
{
    public function map(array $rawDevice): ?NormalizedDevice
    {
        $serial = $this->serial($rawDevice);

        // A device with no serial number can never be matched to an asset
        // (asset uniqueness is determined by serial code), so it cannot be
        // synced at all - this is a hard skip, independent of assignment.
        if ($serial === null) {
            return null;
        }

        return new NormalizedDevice(
            serialCode: $serial,
            deviceName: $this->deviceName($rawDevice),
            provider: 'jamf',
            externalId: $this->externalId($rawDevice),
            employeeEmail: $this->email($rawDevice),
            employeeName: $this->name($rawDevice),
            employeePhone: $this->phone($rawDevice),
            attributes: array_filter([
                'model' => data_get($rawDevice, 'hardware.model') ?? data_get($rawDevice, 'model'),
                'model_identifier' => data_get($rawDevice, 'hardware.modelIdentifier'),
                'processor' => data_get($rawDevice, 'hardware.processorType'),
                'core_count' => data_get($rawDevice, 'hardware.coreCount'),
                'ram_gb' => $this->ramGb($rawDevice),
                'storage_gb' => $this->storageGb($rawDevice),
                'battery_percent' => data_get($rawDevice, 'hardware.batteryCapacityPercent'),
                'platform' => data_get($rawDevice, 'general.platform'),
            ], static fn ($value) => $value !== null),
        );
    }

    /**
     * @param array<string, mixed> $device
     */
    private function serial(array $device): ?string
    {
        $serial = data_get($device, 'hardware.serialNumber');

        if (!is_string($serial)) {
            return null;
        }

        $serial = trim($serial);

        return $serial !== '' ? $serial : null;
    }

    /**
     * @param array<string, mixed> $device
     */
    private function externalId(array $device): ?string
    {
        $id = data_get($device, 'id');

        if (is_string($id) || is_numeric($id)) {
            return (string) $id;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $device
     */
    private function email(array $device): ?string
    {
        $email = data_get($device, 'userAndLocation.email')
            ?? data_get($device, 'userAndLocation.emailAddress')
            ?? data_get($device, 'username');

        if (!is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @param array<string, mixed> $device
     */
    private function name(array $device): ?string
    {
        $name = data_get($device, 'userAndLocation.realname')
            ?? data_get($device, 'userAndLocation.realName');

        if (!is_string($name)) {
            return null;
        }

        $name = trim($name);

        return $name !== '' ? $name : null;
    }

    /**
     * @param array<string, mixed> $device
     */
    private function phone(array $device): ?string
    {
        $phone = data_get($device, 'userAndLocation.phone')
            ?? data_get($device, 'userAndLocation.phoneNumber');

        if (!is_string($phone)) {
            return null;
        }

        $phone = trim($phone);

        return $phone !== '' ? $phone : null;
    }

    /**
     * @param array<string, mixed> $device
     */
    private function deviceName(array $device): string
    {
        $name = data_get($device, 'general.displayName')
            ?? data_get($device, 'general.name')
            ?? data_get($device, 'hardware.model')
            ?? data_get($device, 'model')
            ?? data_get($device, 'name');

        return is_string($name) && trim($name) !== '' ? trim($name) : 'Unknown device';
    }

    /**
     * @param array<string, mixed> $device
     */
    private function ramGb(array $device): ?float
    {
        $mb = data_get($device, 'hardware.totalRamMegabytes');

        if (!is_numeric($mb) || (float) $mb <= 0) {
            return null;
        }

        return round(((float) $mb) / 1024, 2);
    }

    /**
     * @param array<string, mixed> $device
     */
    private function storageGb(array $device): ?float
    {
        $disks = data_get($device, 'storage.disks');

        if (!is_array($disks)) {
            return null;
        }

        $totalMb = 0.0;

        foreach ($disks as $disk) {
            $size = is_array($disk) ? ($disk['sizeMegabytes'] ?? null) : null;

            if (is_numeric($size)) {
                $totalMb += (float) $size;
            }
        }

        return $totalMb > 0 ? round($totalMb / 1024, 2) : null;
    }
}
