<?php

declare(strict_types=1);

namespace App\Mdm\Contracts;

/**
 * Retrieves raw device payloads from an MDM provider.
 *
 * Implementations own all provider-specific transport concerns
 * (authentication, pagination, HTTP vs. a local fixture, ...). They return
 * plain arrays exactly as the provider sends them - translating that shape
 * into something the sync engine understands is the DeviceMapper's job.
 */
interface MdmClient
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchDevices(): array;

    /**
     * Machine-readable provider identifier, e.g. "jamf", "kandji", "intune".
     */
    public function provider(): string;
}
