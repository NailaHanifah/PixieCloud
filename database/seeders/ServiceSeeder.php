<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'name' => 'Pixie Plan',
                'description' => 'Alokasi ruang penyimpanan dasar untuk kebutuhan mendasar aset digital skala kecil.',
                'price' => 15000,
                'max_buckets' => 1,
                'max_storage_mb' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Griffin Plan',
                'description' => 'Ruang penyimpanan yang lebih luas, dirancang untuk kluster data yang membutuhkan performa stabil dan terorganisir.',
                'price' => 50000,
                'max_buckets' => 3,
                'max_storage_mb' => 5120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dragon Plan',
                'description' => 'Spesifikasi ruang tertinggi setingkat inti wilayah utama untuk perlindungan dan penyimpanan data berskala masif.',
                'price' => 150000,
                'max_buckets' => 10,
                'max_storage_mb' => 51200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}