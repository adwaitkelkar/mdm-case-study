<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end tests driving the real HTTP API against the bundled Jamf mock
 * fixture (mock/jamf/api-mock-response.json) - the same file the backend
 * reads in the running application.
 */
class JamfSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_assigned_devices_from_the_jamf_mock_response(): void
    {
        $response = $this->postJson('/api/sync');

        $response->assertOk();
        $response->assertJsonPath('result.provider', 'jamf');
        $response->assertJsonPath('result.created_assets', 7);
        $response->assertJsonPath('result.skipped_unassigned', 0);

        $this->assertSame(7, Asset::count());

        // alex.smith owns two devices in the fixture, so 7 assets map to
        // only 6 distinct employees.
        $this->assertSame(6, Employee::count());

        $alex = Employee::where('email', 'alex.smith@company.test')->firstOrFail();
        $this->assertSame(2, $alex->assets()->count());
    }

    public function test_assets_endpoint_returns_synced_devices_with_their_employee(): void
    {
        $this->postJson('/api/sync')->assertOk();

        $response = $this->getJson('/api/assets');

        $response->assertOk();
        $response->assertJsonCount(7);
        $response->assertJsonFragment(['serial_code' => 'C02DL0XYZQ6N']);
    }

    public function test_re_running_sync_recreates_a_manually_deleted_asset(): void
    {
        $this->postJson('/api/sync')->assertOk();

        $asset = Asset::where('serial_code', 'C02DL0XYZQ6N')->firstOrFail();
        $this->deleteJson("/api/assets/{$asset->id}")->assertNoContent();

        $this->assertDatabaseMissing('assets', ['serial_code' => 'C02DL0XYZQ6N']);

        $this->postJson('/api/sync')->assertOk();

        $this->assertDatabaseHas('assets', ['serial_code' => 'C02DL0XYZQ6N']);
        $this->assertSame(7, Asset::count());
    }

    public function test_re_running_sync_is_idempotent_and_only_updates_existing_assets(): void
    {
        $this->postJson('/api/sync')->assertOk();

        $response = $this->postJson('/api/sync');

        $response->assertJsonPath('result.created_assets', 0);
        $response->assertJsonPath('result.updated_assets', 7);
        $this->assertSame(7, Asset::count());
    }

    public function test_deleting_an_employee_also_removes_their_assets_locally(): void
    {
        $this->postJson('/api/sync')->assertOk();

        $alex = Employee::where('email', 'alex.smith@company.test')->firstOrFail();
        $this->assertSame(2, Asset::where('employee_id', $alex->id)->count());

        $this->deleteJson("/api/employees/{$alex->id}")->assertNoContent();

        $this->assertDatabaseMissing('employees', ['email' => 'alex.smith@company.test']);
        $this->assertSame(0, Asset::where('employee_id', $alex->id)->count());
    }
}
