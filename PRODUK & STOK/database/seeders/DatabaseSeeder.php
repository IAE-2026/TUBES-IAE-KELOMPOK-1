<?php

namespace Database\Seeders;

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
        // Seed Test User
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123')
            ]
        );

        // Seed Sample Products
        \App\Models\Product::firstOrCreate(
            ['sku' => 'SKU-001'],
            [
                'name' => 'Sepatu Compass',
                'description' => 'Sepatu canvas berkualitas tinggi buatan lokal.',
                'price' => 450000.00,
                'stock' => 35,
            ]
        );

        \App\Models\Product::firstOrCreate(
            ['sku' => 'BK-LAR-011'],
            [
                'name' => 'Buku Pemrograman Laravel',
                'description' => 'Panduan lengkap membangun REST API dan GraphQL dengan Laravel.',
                'price' => 125000.00,
                'stock' => 50,
            ]
        );

        \App\Models\Product::firstOrCreate(
            ['sku' => 'LP-ASUS-102'],
            [
                'name' => 'Laptop ASUS Vivobook',
                'description' => 'Laptop tipis dan ringan untuk produktivitas harian.',
                'price' => 8500000.00,
                'stock' => 12,
            ]
        );

        \App\Models\Product::firstOrCreate(
            ['sku' => 'MJ-WOOD-301'],
            [
                'name' => 'Meja Kerja Minimalis',
                'description' => 'Meja kerja kayu jati dengan desain modern minimalis.',
                'price' => 650000.00,
                'stock' => 8,
            ]
        );

        \App\Models\Product::firstOrCreate(
            ['sku' => 'HP-SAMS-051'],
            [
                'name' => 'Smartphone Samsung Galaxy A54',
                'description' => 'Smartphone kelas menengah dengan kamera 50MP dan layar Super AMOLED.',
                'price' => 5200000.00,
                'stock' => 15,
            ]
        );
    }
}
