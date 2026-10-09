<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * No static seed data is needed: the assets/employees tables are
     * populated by running an MDM sync (POST /api/sync), which reads the
     * Jamf mock response. See Readme.md.
     */
    public function run(): void
    {
        //
    }
}
