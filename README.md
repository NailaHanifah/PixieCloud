# 🌲 Dokumen Perencanaan Proyek UAS Komputasi Awan
## PixieCloud: Eco-Centric IaaS Simulation Platform

PixieCloud adalah prototipe portal penyedia layanan infrastruktur awan (Infrastructure as a Service) berbasis web bertema *Forest Academic & Cottagecore*. Sistem ini mengadopsi pendekatan arsitektur kontainer modern menggunakan Docker dan mensimulasikan penyediaan *Object Storage* AWS S3 terisolasi dengan MiniStack (LocalStack) sebagai mesin di belakang layar.

Sistem ini menerapkan arsitektur *Self-Service Instant Provisioning* yang ringkas secara resource, otomatis penuh, dan mengutamakan isolasi keamanan data tingkat tinggi.

---

## 1. Konsep Umum & Penyelarasan Analogi

Untuk menjaga profesionalisme sistem tanpa menghilangkan estetika tema, digunakan pendekatan analogi *nature-centric* yang elegan (setara dengan standarisasi penamaan wilayah pada macOS):

* **Identitas Platform:** **PixieCloud Platform** (Portal penyedia infrastruktur komputasi awan).
* **Identitas Pengguna:** **Petualang (User)** sebagai penyewa ruang, dan **Penjaga Gerbang (Admin)** sebagai entitas verifikator infrastruktur.
* **Resource Storage (Bucket):** **Vault (Enclosure)**, ruang penyimpanan objek digital yang aman, terisolasi, dan terikat pada kredensial unik pengguna.
* **Kredensial Akses (IAM):** **Rune Credentials (Access Key & Secret Key)**, pasang kunci digital unik yang berfungsi sebagai paspor otentikasi aman untuk masuk dan memanipulasi data di dalam *Vault*.
* **Katalog Layanan:** **Grove Specifications**, daftar kluster kapasitas ruang penyimpanan berdasarkan batas jumlah *Vault* dan ukuran megabyte/gigabyte.

---

## 2. Arsitektur Data & Etika Keamanan Data (Database Schema)

Perancangan skema database ini sangat memperhatikan **Etika Data (Data Ethics)** dan **Integritas Data**. Tabel dibagi berdasarkan sifatnya: **Mutable** (data yang boleh diubah seiring waktu) dan **Immutable** (data historis yang haram diubah demi keamanan dan fungsi audit/pelacakan). Kolom status juga dikunci menggunakan tipe data `enum` demi menjaga validitas data dari risiko kesalahan ketik.

### Struktur Tabel Master & Transaksi

#### A. Tabel `users` (Mutable)
Menyimpan data identitas dasar para Petualang.
* `id` (int, PK, Increment)
* `username` (varchar, Unique)
* `email` (varchar, Unique)
* `password` (varchar)
* `role` (enum('user', 'admin'), default: 'user')
• created_at timestamp
• updated_at timestamp

#### B. Tabel `services` (Immutable)
Tabel master pencatat spesifikasi kluster infrastruktur (Grove Specifications) yang tersedia.
* `id` (int, PK, Increment)
* `name` (varchar) - *Pixie Dust Pouch, Grove Plan, Dragon's Hoard Plan*
* `description` (text)
* `price` (int)
* `max_buckets` (int) - *Batas maksimal Vault*
* `max_storage_mb` (int) - *Batas kapasitas penyimpanan*
• created_at timestamp
• updated_at timestamp

#### C. Tabel `subscriptions` (Mutable)
Mencatat sejarah masa aktif kontrak sewa sewa infrastruktur oleh Petualang.
* `id` (int, PK, Increment)
* `user_id` (int, FK references `users.id`)
* `service_id` (int, FK references `services.id`)
* `status` (enum('Active', 'Expired'), default: 'Active')
* `start_date` (timestamp)
* `end_date` (timestamp)
• created_at timestamp
• updated_at timestamp

#### D. Tabel `cloud_credentials` (Mutable)
Menyimpan kunci akses infrastruktur (Rune Credentials) untuk cloud simulator.
* `id` (int, PK, Increment)
* `subscription_id` (int, Unique, FK references `subscriptions.id`)
* `ministack_access_key` (varchar)
* `ministack_secret_key` (text)
* `status` (enum('Active', 'Revoked'), default: 'Active')
• created_at timestamp
• updated_at timestamp

#### E. Tabel `buckets` (Mutable)
Mencatat informasi *Vault* (ruang penyimpanan logika) milik Petualang.
* `id` (int, PK, Increment)
* `user_id` (int, FK references `users.id`)
* `bucket_name` (varchar, Unique)
* `allocated_size_mb` (int)
• created_at timestamp
• updated_at timestamp

#### F. Tabel `objects` (Mutable)
Katalog metadata dari file digital yang berhasil dimasukkan ke dalam *Vault*.
* `id` (int, PK, Increment)
* `bucket_id` (int, FK references `buckets.id`)
* `object_key` (varchar)
* `content_type` (varchar)
* `file_size_bytes` (bigint)
* `file_url` (text)
* `object_metadata` (json)
• created_at timestamp

#### G. Tabel `activity_logs` (Immutable)
*Audit Trail* otomatis untuk merekam jejak digital demi keperluan keamanan.
* `id` (int, PK, Increment)
* `user_id` (int, FK references `users.id`)
* `activity` (varchar)
* `ip_address` (varchar)
• created_at timestamp

### Etika Keamanan Data & Kriptografi
1. **Isolasi Berbasis Kontrak (Separation of Concerns):** `cloud_credentials` sengaja tidak dihubungkan langsung ke tabel `users`, melainkan berelasi **One-to-One** dengan `subscriptions`. Hal ini memastikan jika paket sewa *Expired*, kunci otomatis *Revoked* tanpa mengganggu akun utama Petualang.
2. **Enkripsi Kriptografi Simetris:** Kolom `ministack_secret_key` disimpan ke database menggunakan algoritma enkripsi bawaan Laravel (`Crypt::encryptString`). Kunci rahasia dijamin aman dari kebocoran database fisik dan hanya didekripsi pada memori server secara *real-time* saat transaksi API berlangsung.

---

## 3. Detail Alur Sistem & Integrasi MiniStack Berbasis Docker

### Alur Siklus Hidup Pengguna (End-to-End Workflow)

1. **Registrasi Akun:** Petualang mendaftarkan diri ke platform PixieCloud.
2. **Penyediaan Instan (*Self-Service Provisioning*):** Sistem langsung mengaktifkan paket dasar di tabel `subscriptions` (Status: `Active`) secara otomatis tanpa hambatan birokrasi persetujuan manual admin.
3. **Penerbitan Rune Credentials:** Sistem men-generate `access_key` dan `secret_key` baru di tabel `cloud_credentials` terikat pada ID langganan, serta mendirikan wadah awal (*Vault*) di tabel `buckets`.
4. **Operasi IaaS Cloud:** Saat Petualang mengunggah file, Laravel bertindak sebagai kurir pengantar data yang menembak API MiniStack dengan membawa file beserta *Access Key* dan tanda tangan digital dari *Secret Key*. Jika validasi lolos, file masuk ke MiniStack dan tercatat di tabel `objects`.

### Logika Khusus Pergantian Paket / Upgrade (Alur Transisi Aman - Alur A)
Jika Petualang melakukan *upgrade* paket sebelum atau sesudah durasi waktu habis:
* **Kontrak Lama Dimatikan:** Status kontrak di tabel `subscriptions` berubah menjadi `Expired` dan kunci pasangannya di `cloud_credentials` langsung diubah menjadi `Revoked` (mati total demi keamanan standar AWS IAM).
* **Kontrak Baru Diterbitkan:** Kontrak baru di tabel `subscriptions` berstatus `Active`. Sistem melahirkan sepasang *Rune Credentials* yang **benar-benar baru** terikat pada ID kontrak yang baru.
* **Isolasi Data Aman:** Data pada tabel `buckets` dan `objects` **tidak dihapus**. Semua file lama di dalam *Vault* milik Petualang di simulator LocalStack tetap utuh, sistem hanya memodifikasi batasan angka kapasitas (`allocated_size_mb`) di database agar melonggar sesuai kuota paket baru.

---

## 4. Spesifikasi Paket Layanan (Grove Specifications)
1. **Pixie Plan (Small)**
   * *Deskripsi:* Alokasi ruang penyimpanan dasar untuk kebutuhan mendasar aset digital skala kecil.
   * *Batas Kuota:* Maksimal 1 Vault, Total Storage 500 MB.
   * *Harga Langganan:* **Rp15.000 / bulan** (Setara dengan harga cloud storage personal entry-level).

2. **Griffin Plan (Medium)**
   * *Deskripsi:* Ruang penyimpanan yang lebih luas, dirancang untuk kluster data yang membutuhkan performa stabil dan terorganisir.
   * *Batas Kuota:* Maksimal 3 Vaults, Total Storage 5 GB (5.120 MB).
   * *Harga Langganan:* **Rp50.000 / bulan** (Menerapkan strategi *volume discount* industri nyata untuk menarik minat pengguna beralih ke paket menengah).

3. **Dragon Plan (Large)**
   * *Deskripsi:* Spesifikasi ruang tertinggi setingkat inti wilayah utama untuk perlindungan dan penyimpanan data berskala masif.
   * *Batas Kuota:* Maksimal 10 Vaults, Total Storage 50 GB (51.200 MB).
   * *Harga Langganan:* **Rp150.000 / bulan** (Mencerminkan alokasi penyimpanan besar dan batas jumlah resource yang banyak).