# Dokumen Perencanaan Proyek UAS Komputasi Awan
## PixieCloud: Eco-Centric IaaS Simulation Platform

PixieCloud adalah prototipe portal penyedia layanan infrastruktur awan (Infrastructure as a Service) berbasis web bertema *Forest Academic & Cottagecore*. Sistem ini mengadopsi pendekatan arsitektur kontainer modern menggunakan Docker dan mensimulasikan penyediaan *Object Storage* AWS S3 terisolasi dengan MiniStack sebagai mesin di belakang layar.

---

## 1. Konsep Umum & Penyelarasan Analogi

Untuk menjaga profesionalisme sistem tanpa menghilangkan estetika tema, digunakan pendekatan analogi *nature-centric* yang elegan (setara dengan standarisasi penamaan wilayah pada macOS):

* **Identitas Platform:** **PixieCloud Platform** (Portal penyedia infrastruktur komputasi awan).
* **Identitas Pengguna:** **Petualang (User)** sebagai penyewa ruang, dan **Penjaga Gerbang (Admin)** sebagai entitas verifikator infrastruktur.
* **Sistem Transaksi:** **Direct Fiat Invoice**, sistem pembayaran langsung menggunakan mata uang Rupiah via transfer manual (simulasi) untuk membeli masa aktif paket secara berkala.
* **Resource (Bucket):** **Vault (Enclosure)**, ruang penyimpanan objek digital yang aman, terisolasi, dan terikat pada kredensial unik pengguna.
* **Katalog Layanan:** **Grove Specifications**, daftar kluster kapasitas ruang penyimpanan berdasarkan batas jumlah *Vault* dan ukuran gigabyte.

---

## 2. Arsitektur Data & Etika Keamanan Data (Database Schema)

Perancangan skema database ini sangat memperhatikan **Etika Data (Data Ethics)** dan **Integritas Data**. Tabel dibagi berdasarkan sifatnya: **Mutable** (data yang boleh diubah seiring waktu) dan **Immutable** (data historis yang haram diubah demi keamanan dan fungsi audit/pelacakan).

### A. DATA MUTABLE (Boleh Diperbarui)

#### 1. Tabel `users`
Menyimpan informasi akun dan kredensial cloud MiniStack.
* `id` (INT, Primary Key, Auto Increment)
* `username` (VARCHAR)
* `email` (VARCHAR, Unique)
* `password` (VARCHAR, Hashed)
* `ministack_access_key` (VARCHAR, Nullable)
* `ministack_secret_key` (TEXT, Nullable) -> *Etika Data: Wajib di-encrypt oleh backend (Crypt::encryptString) sebelum disimpan.*
* `role` (ENUM: 'user', 'admin') -> Default: 'user'
* `timestamps()` (Mengelola `created_at` dan `updated_at` untuk keperluan pembaruan kredensial/password).

#### 2. Tabel `services`
Katalog master spesifikasi paket (*Grove Specifications*). Hanya boleh diubah oleh Admin.
* `id` (INT, Primary Key, Auto Increment)
* `name` (VARCHAR) -> Pixie Plan, Elf Plan, Dragon Plan
* `description` (TEXT)
* `price` (INTEGER) -> Harga nominal langsung dalam Rupiah (Rp)
* `max_buckets` (INTEGER) -> Limit jumlah Vault
* `max_storage_mb` (INTEGER) -> Limit total kapasitas ruang dalam Megabyte
* `timestamps()`

#### 3. Tabel `subscriptions`
Mencatat paket aktif yang mengikat pengguna dengan layanan beserta masa berlakunya.
* `id` (INT, Primary Key, Auto Increment)
* `user_id` (INT, Foreign Key -> `users.id` ON DELETE CASCADE)
* `service_id` (INT, Foreign Key -> `services.id` ON DELETE CASCADE)
* `status` (ENUM: 'Active', 'Expired') -> Default: 'Active'
* `start_date` (TIMESTAMP)
* `end_date` (TIMESTAMP) -> Menentukan batas akhir masa aktif paket (misal: +30 hari sejak ACC)
* `timestamps()` -> *Diperlukan untuk memantau perubahan status sewa dari Active ke Expired.*

---

### B. DATA IMMUTABLE / APPEND-ONLY (Haram Diubah / Riwayat Statis)

#### 4. Tabel `payments`
Mencatat riwayat transaksi konfirmasi transfer manual langsung dalam nominal Rupiah.
* `id` (INT, Primary Key, Auto Increment)
* `invoice_code` (VARCHAR, Unique) -> Format: `INV-YYYYMM-[Random]`
* `user_id` (INT, Foreign Key -> `users.id`)
* `service_id` (INT, Foreign Key -> `services.id`)
* `amount` (INTEGER) -> Nominal Rupiah yang ditransfer
* `proof_of_payment` (VARCHAR, Nullable) -> Nama file gambar bukti transfer
* `status` (ENUM: 'Pending', 'Success', 'Failed') -> Default: 'Pending'
* `timestamps()` -> *Etika Data: Kolom `updated_at` hanya berubah sekali saat Admin mengubah status pembayaran. Setelah berstatus Success, baris data ini dikunci total dan tidak boleh diubah oleh siapa pun.*

#### 5. Tabel `buckets`
Mencatat metadata kontainer penyimpanan riil yang aktif di-deploy di MiniStack.
* `id` (INT, Primary Key, Auto Increment)
* `user_id` (INT, Foreign Key -> `users.id` ON DELETE CASCADE)
* `bucket_name` (VARCHAR, Unique) -> Format: `pixie-[username]-[string_unik]`
* `allocated_size_mb` (INTEGER)
* `timestamps()` -> *Sifat: Append-Only. Data hanya bertambah saat membuat Vault baru atau terhapus total (`DELETE`) jika masa sewa habis. Tidak ada skenario pengeditan nama (`UPDATE`).*

#### 6. Tabel `activity_logs` (The Chronicler's Scroll)
Catatan jejak audit sistem untuk kepentingan keamanan (*Audit Trail*).
* `id` (INT, Primary Key, Auto Increment)
* `user_id` (INT, Foreign Key -> `users.id` ON DELETE CASCADE)
* `activity` (VARCHAR) -> Narasi tegas, contoh: "Mengajukan pembuatan Vault baru: pixie-elaina-x9z2"
* `ip_address` (VARCHAR, Length: 45) -> *Etika Data: Wajib mencatat IP pengguna untuk mendeteksi anomali akses/serangan ilegal.*
* `timestamps()` -> *Sifat: Pure Immutable. Model Laravel dikonfigurasi `public $timestamps = false;` agar record sejarah ini tidak bisa dimanipulasi setelah masuk ke database.*

---

## 3. Detail Alur Sistem & Integrasi MiniStack Berbasis Docker

Seluruh sistem berjalan di atas ekosistem kontainer Docker (Laravel App, MySQL/PostgreSQL, dan MiniStack Engine berbasis LocalStack) yang terisolasi dalam satu jaringan internal.

### Alur 1: Autentikasi Keamanan Tinggi
* User melakukan registrasi pada kontainer aplikasi web. Data disimpan ke database. Pada tahap awal ini, user belum memiliki kredensial cloud (`ministack_access_key` masih `NULL`).

### Alur 2: Proses Bisnis Pembelian Masa Aktif & Verifikasi Admin
* **Pengajuan Langganan:** User memilih paket kapasitas di halaman web (*Grove Specifications*), melihat detail nomor rekening bank simulasi, dan mengunggah gambar bukti transfer Rupiah asli. Sistem membuat baris baru di tabel `payments` dengan status `Pending`.
* **Verifikasi Admin:** Pengguna dengan `role = admin` masuk ke dashboard khusus. Admin memeriksa keabsahan berkas gambar `proof_of_payment`.
* **Eksekusi Otomatis:** Ketika Admin menekan tombol **"Approve"**, backend Laravel akan mengeksekusi 4 aksi berantai secara otomatis dalam satu waktu:
    1. Mengubah status tabel `payments` menjadi `Success`.
    2. Membuat entri masa aktif paket di tabel `subscriptions` (menghitung `start_date` dari waktu sekarang dan `end_date` otomatis bertambah 30 hari ke depan).
    3. **Integrasi Cloud (MiniStack):** Backend mengirimkan permintaan API internal ke kontainer MiniStack untuk membuat pengguna cloud baru, mengambil *Access Key & Secret Key* aslinya, lalu menyimpannya ke tabel `users` (Secret Key wajib di-encrypt).
    4. **Inisialisasi Resource:** Backend memerintahkan MiniStack untuk otomatis membuat *Vault* (isolated bucket) pertama dengan penamaan aman: `pixie-[username]-[string_unik]` dan menyimpannya di tabel `buckets`.

### Alur 3: Dashboard & Manajemen Kredensial
* **Masking Data:** Di halaman dashboard user, *Secret Key* akan disensor secara default (`********`). Disediakan tombol *"Reveal Key"* yang mewajibkan user memasukkan ulang password akun mereka demi memenuhi asas keamanan.
* **One-Click Cloud Sync:** Menyediakan tombol *"Sync Infrastructure"* di dashboard untuk mencocokkan ulang data jika terjadi ketidaksinkronan jumlah *Vault* antara database web lokal dengan server MiniStack asli.

---

## 4. Spesifikasi Paket Layanan (Grove Specifications)

Penamaan dirancang menggunakan makhluk fiksi-fantasi yang sangat populer (*well-known*) agar urutan tingkatannya (Small, Medium, Large) langsung dipahami secara intuitif oleh dosen penguji, serta menggunakan rasio harga dan kapasitas penyimpanan yang realistis:

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

---

## 5. Distribusi Kerja Kelompok

Pengerjaan dibagi secara paralel memanfaatkan isolasi lingkungan kerja Docker agar tidak terjadi tumpang tindih fungsi:

* **Anggota 1 — Frontend & UI/UX Designer**
  * Merancang *UI Mockup* bertema *Forest Academic* (Sage green, cream, serif font) di Figma.
  * Mengonversi rancangan menjadi komponen views Laravel menggunakan Tailwind CSS (Form login/register, dashboard user dengan grafik kuota sisa hari masa aktif dan storage, halaman unggah bukti bayar, dan dashboard verifikasi transaksi milik admin).
* **Anggota 2 — Backend & Authentication Developer**
  * Membangun sistem autentikasi multi-role (User & Admin) serta enkripsi dua arah untuk *Secret Key* MiniStack.
  * Membuat *logic controller* untuk memproses pembuatan invoice, transaksi pembayaran manual, dan logika *middleware* untuk mengunci fitur cloud/menolak pembuatan bucket baru jika status langganan di tabel `subscriptions` sudah menyentuh `end_date` (*Expired*).
* **Anggota 3 — Database Engineer**
  * Menyusun struktur migrasi 6 tabel di Laravel sesuai dengan ketetapan etika data (Mutable vs Immutable).
  * Membuat file `DatabaseSeeder.php` untuk otomatisasi pengisian katalog master paket layanan (`services`) langsung menggunakan nilai nominal Rupiah serta akun admin utama agar sistem siap diuji coba langsung setelah migrasi.
* **Anggota 4 — Integration & Debugging Specialist**
  * Menghubungkan komponen visual kiriman Anggota 1 dengan API backend buatan Anggota 2.
  * Menangani logika penguncian kuota (memastikan sistem menolak pembuatan bucket di frontend jika jumlah baris di tabel `buckets` sudah menyentuh limit `max_buckets` pada paketnya).
* **Anggota 5 — Cloud Infrastructure, MiniStack & Documentation**
  * Menyusun konfigurasi file `docker-compose.yml` untuk menyatukan kontainer aplikasi web, database, dan mesin `nahuelnucera/ministack`.
  * Membuat fungsi modul penghubung (*SDK/API Connector wrapper*) di Laravel yang bertugas menembak API MiniStack untuk perintah pembuatan user cloud, pembuatan bucket, dan perintah *regenerate key*. Menyusun laporan akhir UAS.