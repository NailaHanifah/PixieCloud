<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder Paket Layanan Terlebih Dahulu
        $this->call(ServiceSeeder::class);

        // Ambil ID Pixie Plan secara dinamis agar lebih aman
        $pixieService = DB::table('services')->where('name', 'Pixie Plan')->first();
        $serviceId = $pixieService ? $pixieService->id : 1;

        // 2. Buat Data User (Admin & Petualang)
        $adminId = DB::table('users')->insertGetId([
            'username' => 'gatekeeper_elan',
            'email' => 'admin@pixiecloud.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'adventurer_clara',
            'email' => 'clara@pixiecloud.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Simulasi Langganan Otomatis (Aktivasi Instant)
        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'user_id' => $userId,
            'service_id' => $serviceId, // Menggunakan ID dinamis
            'status' => 'Active',
            'start_date' => now(),
            'end_date' => now()->addDays(30)->toDateTimeString(), 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Integrasi Pembuatan Rune Credentials (Kunci Enkripsi)
        DB::table('cloud_credentials')->insert([
            'subscription_id' => $subscriptionId,
            'ministack_access_key' => 'PCX_' . strtoupper(Str::random(16)),
            'ministack_secret_key' => Crypt::encryptString(Str::random(32)),
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Inisialisasi Vault Utama Milik Petualang
        DB::table('buckets')->insert([
            'user_id' => $userId,
            'bucket_name' => 'clara-first-vault',
            'allocated_size_mb' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log Aktivitas Awal
        DB::table('activity_logs')->insert([
            'user_id' => $userId,
            'activity' => 'Mendaftarkan akun dan mengaktifkan Pixie Plan via Instant Provisioning.',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);
    }
}