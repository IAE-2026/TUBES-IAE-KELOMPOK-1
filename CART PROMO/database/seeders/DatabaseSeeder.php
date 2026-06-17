<?php

namespace Database\Seeders;

use App\Models\Promo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Promo::updateOrCreate(
            ['code' => 'PROMO10'],
            [
                'discount_percent' => 10,
                'minimum_transaction' => 50000,
                'max_usage' => 100,
                'used' => 0,
                'expired_at' => now()->addMonth(),
            ]
        );
    }
}