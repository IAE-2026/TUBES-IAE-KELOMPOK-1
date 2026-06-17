<?php

namespace Database\Seeders;

use App\Models\LocalRole;
use Illuminate\Database\Seeder;

class LocalRoleSeeder extends Seeder
{
    /**
     * Seed the local_roles table.
     */
    public function run(): void
    {
        LocalRole::updateOrCreate(
            ['sso_email' => 'warga28@ktp.iae.id'],
            ['role' => 'admin']
        );
    }
}
