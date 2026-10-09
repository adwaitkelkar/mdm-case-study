<?php

declare(strict_types=1);

namespace App\Mdm\Jamf;

use App\Mdm\Contracts\MdmClient;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Jamf Pro client.
 *
 * For this assignment only "mock" mode is implemented: it reads the
 * bundled `mock/jamf/api-mock-response.json` fixture instead of calling
 * the real Jamf Pro API. That fixture has the same shape as Jamf's
 * `GET /api/v1/computers-inventory` response (a `results` array of
 * computer-inventory objects), so a real HTTP-backed mode can be added
 * later (OAuth client-credentials + paginated HTTP calls) without
 * changing anything else in the sync pipeline - just a new `fetchDevices()`
 * implementation returning the same array shape.
 */
final class JamfApiClient implements MdmClient
{
    public function __construct(
        private readonly string $mode,
        private readonly string $mockPath,
    ) {
    }

    public function provider(): string
    {
        return 'jamf';
    }

    public function fetchDevices(): array
    {
        return match ($this->mode) {
            'mock' => $this->fetchFromMock(),
            default => throw new RuntimeException("Unsupported Jamf client mode [{$this->mode}]."),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchFromMock(): array
    {
        if (!File::exists($this->mockPath)) {
            throw new RuntimeException("Jamf mock response file not found at [{$this->mockPath}].");
        }

        $decoded = json_decode(File::get($this->mockPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Jamf mock response is not valid JSON: '.json_last_error_msg());
        }

        $results = $decoded['results'] ?? null;

        if (!is_array($results)) {
            throw new RuntimeException('Jamf mock response is missing a "results" array.');
        }

        return $results;
    }
}
