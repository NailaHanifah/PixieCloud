<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key constraints (compatible SQLite + MySQL)
        Schema::disableForeignKeyConstraints();

        // Bersihkan data lama
        DB::table('activity_logs')->truncate();
        DB::table('buckets')->truncate();
        DB::table('subscriptions')->truncate();
        DB::table('users')->truncate();

        // Enable lagi foreign key constraints
        Schema::enableForeignKeyConstraints();

        // Buat user dummy utama
        $userId = DB::table('users')->insertGetId([
            'username' => 'elaina',
            'email' => 'elaina@pixie.com',
            'password' => Hash::make('rahasia123'),
            'ministack_access_key' => 'AKIAIOSFODNN7EXAMPLE',
            'ministack_secret_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Data subscription
        DB::table('subscriptions')->insert([
            'user_id' => $userId,
            'plan_name' => 'Pixie Dust Pouch',
            'max_buckets' => 1,
            'max_storage_mb' => 500,
            'status' => 'Active',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bucket default
        DB::table('buckets')->insert([
            'user_id' => $userId,
            'bucket_name' => 'pixie-elaina-init',
            'allocated_size_mb' => 100,
            'created_at' => now(),
        ]);

        // Activity logs
        DB::table('activity_logs')->insert([
            [
                'user_id' => $userId,
                'activity' => 'Melakukan registrasi akun PixieCloud.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes(10),
            ],
            [
                'user_id' => $userId,
                'activity' => 'Sistem menginisialisasi plan Pixie Dust Pouch.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes(9),
            ],
            [
                'user_id' => $userId,
                'activity' => 'Membuat bucket baru: pixie-elaina-init',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes(8),
            ],
        ]);
    }
}