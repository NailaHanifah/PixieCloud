namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEED MASTER UTAMA (Wajib)
        
        // Akun Admin (Penjaga Gerbang)
        DB::table('users')->insert([
            'username' => 'gatekeeper_pixie',
            'email' => 'admin@pixiecloud.test',
            'password' => Hash::make('SecretGatekeeper2026'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Katalog Paket (Grove Specifications)
        DB::table('services')->insert([
            [
                'id' => 1,
                'name' => 'Pixie Plan',
                'description' => 'Alokasi ruang penyimpanan dasar untuk kebutuhan mendasar aset digital skala kecil.',
                'price' => 15000,
                'max_buckets' => 1,
                'max_storage_mb' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Griffin Plan',
                'description' => 'Ruang penyimpanan yang lebih luas, dirancang untuk kluster data yang membutuhkan performa stabil dan terorganisir.',
                'price' => 50000,
                'max_buckets' => 3,
                'max_storage_mb' => 5120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Dragon Plan',
                'description' => 'Spesifikasi ruang tertinggi setingkat inti wilayah utama untuk perlindungan dan penyimpanan data berskala masif.',
                'price' => 150000,
                'max_buckets' => 10,
                'max_storage_mb' => 51200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        // 2. SEED DATA DUMMY UNTUK SIMULASI UI & FITUR 

        // User 1: Petualang Baru (Belum langganan, untuk tes alur beli paket)
        DB::table('users')->insert([
            'id' => 2,
            'username' => 'elaina_explorer',
            'email' => 'elaina@adventurer.test',
            'password' => Hash::make('PasswordPetualang'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 2: Petualang yang Menunggu Verifikasi (Untuk tes dashboard Admin)
        DB::table('users')->insert([
            'id' => 3,
            'username' => 'frieren_mage',
            'email' => 'frieren@adventurer.test',
            'password' => Hash::make('PasswordPetualang'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Transaksi Pending milik User 2 (Frieren) agar Admin punya data untuk di-Approve saat demo
        DB::table('payments')->insert([
            'invoice_code' => 'INV-202605-' . Str::upper(Str::random(5)),
            'user_id' => 3,
            'service_id' => 3, // Ingin beli Dragon Plan
            'amount' => 150000,
            'proof_of_payment' => 'dummy_transfer_proof.jpg',
            'status' => 'Pending',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        // User 3: Petualang yang Sudah Expired (Untuk tes middleware penguncian resource)
        DB::table('users')->insert([
            'id' => 4,
            'username' => 'stark_warrior',
            'email' => 'stark@adventurer.test',
            'password' => Hash::make('PasswordPetualang'),
            'role' => 'user',
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonths(2),
        ]);

        // Paket Expired milik User 3 (Stark)
        DB::table('subscriptions')->insert([
            'user_id' => 4,
            'service_id' => 1, // Pernah beli Pixie Plan
            'status' => 'Expired',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(), // Sudah habis bulan lalu
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonth(),
        ]);
    }
}