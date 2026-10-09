<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Only assigned devices are imported, so every asset belongs to
            // exactly one employee. If the employee is deleted locally, the
            // asset is removed with it (see Readme "Assumptions").
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // Asset uniqueness is determined by serial code (per MDM sync rules).
            $table->string('serial_code')->unique();

            $table->string('device_name');

            // Which MDM provider this asset was synced from. Kept as a plain
            // string (rather than an enum) so new providers can be added
            // without a migration.
            $table->string('provider')->default('jamf');

            // The provider's own identifier for this device, if any. Not
            // used for matching (serial code is authoritative) but useful
            // for support/debugging and future incremental sync.
            $table->string('external_id')->nullable();

            // Free-form hardware attributes (model, ram_gb, storage_gb, ...).
            // Kept as JSON rather than fixed columns so providers that expose
            // different specs don't require schema changes.
            $table->json('attributes')->nullable();

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
