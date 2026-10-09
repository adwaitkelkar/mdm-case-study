<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $serial_code
 * @property string $device_name
 * @property string $provider
 * @property string|null $external_id
 * @property array<string, mixed>|null $attributes
 * @property \Illuminate\Support\Carbon|null $last_synced_at
 */
class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'serial_code',
        'device_name',
        'provider',
        'external_id',
        'attributes',
        'last_synced_at',
    ];

    protected $casts = [
        'attributes' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
