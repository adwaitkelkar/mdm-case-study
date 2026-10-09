<?php

declare(strict_types=1);

namespace App\Mdm\DTO;

/**
 * Provider-agnostic representation of a single MDM device, produced by a
 * DeviceMapper. The sync engine (DeviceSyncService) only ever works with
 * this shape, never with a raw provider payload.
 */
final class NormalizedDevice
{
    /**
     * @param array<string, mixed> $attributes Hardware/device attributes
     *        (model, ram_gb, storage_gb, ...). Shape is intentionally
     *        loose since different providers expose different specs.
     */
    public function __construct(
        public readonly string $serialCode,
        public readonly string $deviceName,
        public readonly string $provider,
        public readonly ?string $externalId,
        public readonly ?string $employeeEmail,
        public readonly ?string $employeeName,
        public readonly ?string $employeePhone,
        public readonly array $attributes = [],
    ) {
    }

    /**
     * Per the MDM Sync Behaviour Rules, only devices with an assigned
     * employee (identified by email) should be imported.
     */
    public function isAssigned(): bool
    {
        return $this->employeeEmail !== null;
    }
}
