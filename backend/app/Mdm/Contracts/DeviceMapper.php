<?php

declare(strict_types=1);

namespace App\Mdm\Contracts;

use App\Mdm\DTO\NormalizedDevice;

/**
 * Translates one raw provider device payload into a NormalizedDevice.
 *
 * This is the only piece of code that needs to know the shape of a given
 * provider's API response. A new MDM provider is added by implementing
 * this interface (+ MdmClient) - the sync engine itself never changes.
 */
interface DeviceMapper
{
    /**
     * @param array<string, mixed> $rawDevice
     * @return NormalizedDevice|null Null when the payload is unusable, e.g.
     *         it has no serial number and therefore cannot be matched to an
     *         asset at all.
     */
    public function map(array $rawDevice): ?NormalizedDevice;
}
