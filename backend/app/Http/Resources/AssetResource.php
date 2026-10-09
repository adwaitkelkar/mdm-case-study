<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Asset
 */
class AssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serial_code' => $this->serial_code,
            'device_name' => $this->device_name,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'attributes' => $this->attributes ?? [],
            'last_synced_at' => optional($this->last_synced_at)->toIso8601String(),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
