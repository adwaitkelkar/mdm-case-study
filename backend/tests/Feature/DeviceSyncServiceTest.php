<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mdm\Contracts\MdmClient;
use App\Mdm\DeviceSyncService;
use App\Mdm\Jamf\JamfDeviceMapper;
use App\Mdm\SyncResult;
use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the provider-agnostic DeviceSyncService (with the real Jamf
 * mapper) against hand-crafted fixtures, covering sync rules that the
 * bundled mock response doesn't happen to demonstrate (e.g. there is no
 * unassigned device in the mock data).
 */
class DeviceSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_devices_are_not_imported(): void
    {
        $result = $this->syncDevices([
            $this->rawDevice(serial: 'UNASSIGNED001', email: null),
            $this->rawDevice(serial: 'ASSIGNED001', email: 'has.owner@company.test'),
        ]);

        $this->assertSame(1, $result->createdAssets);
        $this->assertSame(1, $result->skippedUnassigned);
        $this->assertDatabaseMissing('assets', ['serial_code' => 'UNASSIGNED001']);
        $this->assertDatabaseHas('assets', ['serial_code' => 'ASSIGNED001']);
    }

    public function test_devices_without_a_serial_number_are_skipped(): void
    {
        $result = $this->syncDevices([
            $this->rawDevice(serial: null, email: 'someone@company.test'),
        ]);

        $this->assertSame(0, $result->createdAssets);
        $this->assertSame(1, $result->skippedUnmappable);
        $this->assertSame(0, Employee::count());
    }

    public function test_attribute_changes_are_reflected_on_resync(): void
    {
        $this->syncDevices([
            $this->rawDevice(serial: 'RAMTEST001', email: 'ram.test@company.test', ramMb: 8192),
        ]);

        $asset = Asset::where('serial_code', 'RAMTEST001')->firstOrFail();
        $this->assertSame(8.0, $asset->attributes['ram_gb']);

        $result = $this->syncDevices([
            $this->rawDevice(serial: 'RAMTEST001', email: 'ram.test@company.test', ramMb: 16384),
        ]);

        $asset->refresh();
        $this->assertSame(16.0, $asset->attributes['ram_gb']);
        $this->assertSame(1, $result->updatedAssets);
        $this->assertSame(0, $result->createdAssets);
    }

    public function test_reassigning_a_device_to_a_different_employee_updates_the_asset(): void
    {
        $this->syncDevices([
            $this->rawDevice(serial: 'REASSIGN001', email: 'first.owner@company.test'),
        ]);

        $asset = Asset::where('serial_code', 'REASSIGN001')->firstOrFail();
        $this->assertSame('first.owner@company.test', $asset->employee->email);

        $this->syncDevices([
            $this->rawDevice(serial: 'REASSIGN001', email: 'second.owner@company.test'),
        ]);

        $asset->refresh();
        $this->assertSame('second.owner@company.test', $asset->employee->email);
        $this->assertSame(2, Employee::count());
    }

    /**
     * @param array<int, array<string, mixed>> $rawDevices
     */
    private function syncDevices(array $rawDevices): SyncResult
    {
        $client = new class($rawDevices) implements MdmClient
        {
            /**
             * @param array<int, array<string, mixed>> $devices
             */
            public function __construct(private readonly array $devices)
            {
            }

            public function fetchDevices(): array
            {
                return $this->devices;
            }

            public function provider(): string
            {
                return 'jamf';
            }
        };

        return (new DeviceSyncService($client, new JamfDeviceMapper()))->sync();
    }

    /**
     * @return array<string, mixed>
     */
    private function rawDevice(?string $serial, ?string $email, int $ramMb = 8192): array
    {
        return [
            'id' => random_int(1000, 999999),
            'general' => [
                'name' => 'Test Device',
                'platform' => 'Mac',
            ],
            'userAndLocation' => $email ? [
                'email' => $email,
                'realname' => 'Test Owner',
                'phone' => null,
            ] : [],
            'hardware' => array_filter([
                'serialNumber' => $serial,
                'model' => 'Test Model',
                'totalRamMegabytes' => $ramMb,
            ], static fn ($value) => $value !== null),
        ];
    }
}
