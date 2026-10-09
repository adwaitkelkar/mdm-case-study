<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mdm\Jamf\JamfDeviceMapper;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the Jamf-specific mapping logic - no database, no
 * HTTP, just raw payload in / NormalizedDevice out.
 */
class JamfDeviceMapperTest extends TestCase
{
    public function test_it_maps_a_well_formed_jamf_payload(): void
    {
        $device = (new JamfDeviceMapper())->map([
            'id' => '101',
            'general' => ['displayName' => 'Alex\'s MacBook Pro'],
            'userAndLocation' => [
                'email' => 'Alex.Smith@Company.test',
                'realname' => 'Alex Smith',
                'phone' => '+491234567890',
            ],
            'hardware' => [
                'serialNumber' => ' C02DL0XYZQ6N ',
                'model' => 'MacBook Pro (14-inch, M1 Pro, 2021)',
                'totalRamMegabytes' => 16384,
            ],
        ]);

        $this->assertNotNull($device);
        $this->assertSame('C02DL0XYZQ6N', $device->serialCode);
        $this->assertSame('101', $device->externalId);
        // Emails are normalized to lowercase for consistent uniqueness matching.
        $this->assertSame('alex.smith@company.test', $device->employeeEmail);
        $this->assertSame('Alex Smith', $device->employeeName);
        $this->assertSame(16.0, $device->attributes['ram_gb']);
        $this->assertTrue($device->isAssigned());
    }

    public function test_it_returns_null_when_the_serial_number_is_missing(): void
    {
        $device = (new JamfDeviceMapper())->map([
            'hardware' => ['model' => 'Unknown'],
        ]);

        $this->assertNull($device);
    }

    public function test_an_invalid_email_is_treated_as_unassigned(): void
    {
        $device = (new JamfDeviceMapper())->map([
            'hardware' => ['serialNumber' => 'ABC123'],
            'userAndLocation' => ['email' => 'not-an-email'],
        ]);

        $this->assertNotNull($device);
        $this->assertFalse($device->isAssigned());
        $this->assertNull($device->employeeEmail);
    }
}
